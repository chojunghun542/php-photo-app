<?php
/**
 * photo_new_post.php
 * - 무료 사진: Public S3 (ACL: public-read, preview 용)
 * - 유료 사진: Private S3 (기본 private, 원본 보관)
 * - 메타데이터만 RDS에 저장
 * - 트랜잭션/롤백 및 S3 정리
 * - UTF-8 헤더, MIME 검증, 크기 제한
 *
 * 사전 준비:
 *   composer require aws/aws-sdk-php
 *   환경변수: AWS_REGION, S3_PUBLIC_BUCKET, S3_PRIVATE_BUCKET, DSN, DB_USER, DB_PASS
 */

declare(strict_types=1);
if (!headers_sent()) {
    header('Content-Type: text/html; charset=UTF-8');
}

session_start();

// (프로젝트 공용 부트스트랩/함수/DB 로더를 쓰는 경우 여기에 require_* 하세요)
// require_once __DIR__ . '/../app/bootstrap.php';

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

require __DIR__ . '/vendor/autoload.php';

// --------------------------
// 1) 인증/입력 기본 검증
// --------------------------
if (empty($_SESSION['user']['id'])) {
    http_response_code(401);
    exit('로그인이 필요합니다.');
}
$uid   = (int)$_SESSION['user']['id'];
$type  = ($_POST['type'] ?? 'free') === 'paid' ? 'paid' : 'free'; // 'free' | 'paid'
$title = trim((string)($_POST['title'] ?? ''));
$desc  = trim((string)($_POST['description'] ?? ''));
$price = (float)($_POST['price'] ?? 0);

// 유료인 경우 가격 필수(서비스 정책에 맞게 조정)
if ($type === 'paid' && $price <= 0) {
    http_response_code(400);
    exit('유료 사진은 가격이 필요합니다.');
}

// 파일 필드명 가정: preview(필수), original(유료 시 필수)
// - 무료: preview만 저장(원본=preview 동일 업로드도 허용 가능)
// - 유료: preview는 공개 S3(작은 썸네일), original은 private S3(원본)
if (empty($_FILES['preview']) || $_FILES['preview']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    exit('프리뷰 이미지 업로드 오류');
}
if ($type === 'paid') {
    if (empty($_FILES['original']) || $_FILES['original']['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        exit('원본 이미지 업로드 오류');
    }
}

// --------------------------
// 2) 파일 검증
// --------------------------
$maxSize = 10 * 1024 * 1024; // 10MB
$allowed = ['image/jpeg','image/png','image/webp'];

$previewTmp  = $_FILES['preview']['tmp_name'];
$previewSize = (int)$_FILES['preview']['size'];
$previewMime = (function($path) {
    // finfo_file이 가장 정확. 없으면 mime_content_type fallback
    if (function_exists('finfo_open')) {
        $fi = finfo_open(FILEINFO_MIME_TYPE);
        if ($fi) {
            $m = finfo_file($fi, $path);
            finfo_close($fi);
            if ($m) return $m;
        }
    }
    if (function_exists('mime_content_type')) {
        return mime_content_type($path);
    }
    return '';
})($previewTmp);

if ($previewSize <= 0 || $previewSize > $maxSize || !in_array($previewMime, $allowed, true)) {
    http_response_code(415);
    exit('프리뷰 파일 형식/크기 오류');
}

$originalTmp = null;
$originalSize = 0;
$originalMime = null;
if ($type === 'paid') {
    $originalTmp  = $_FILES['original']['tmp_name'];
    $originalSize = (int)$_FILES['original']['size'];
    $originalMime = (function($path) {
        if (function_exists('finfo_open')) {
            $fi = finfo_open(FILEINFO_MIME_TYPE);
            if ($fi) {
                $m = finfo_file($fi, $path);
                finfo_close($fi);
                if ($m) return $m;
            }
        }
        if (function_exists('mime_content_type')) {
            return mime_content_type($path);
        }
        return '';
    })($originalTmp);

    if ($originalSize <= 0 || $originalSize > $maxSize || !in_array($originalMime, $allowed, true)) {
        http_response_code(415);
        exit('원본 파일 형식/크기 오류');
    }
}

// (선택) 바이러스 스캔(ClamAV 등) — 운영환경에 맞춰 추가
// exec('clamscan ' . escapeshellarg($previewTmp), $out, $rc); if ($rc !== 0) { ... }

// --------------------------
// 3) S3 클라이언트 준비
// --------------------------
$region        = getenv('AWS_REGION') ?: 'ap-northeast-2';
$publicBucket  = getenv('S3_PUBLIC_BUCKET')  ?: '';
$privateBucket = getenv('S3_PRIVATE_BUCKET') ?: '';

if ($publicBucket === '' || $privateBucket === '') {
    http_response_code(500);
    exit('S3 버킷 환경변수가 설정되지 않았습니다.');
}

$s3 = new S3Client([
    'version' => 'latest',
    'region'  => $region,
    // 자격증명은 EC2/ECS 역할(인스턴스 프로파일) 또는 환경변수로 자동 주입
]);

// --------------------------
// 4) 업로드용 키 구성
// --------------------------
$today = (new DateTimeImmutable('now'))->format('Y/m/d');
$rand  = function() { return bin2hex(random_bytes(16)); };

$previewExt = strtolower(pathinfo($_FILES['preview']['name'], PATHINFO_EXTENSION));
$previewKey = "preview/{$uid}/{$today}/" . $rand() . '.' . $previewExt;

// 유료라면 원본은 private S3, 무료라면 원본=프리뷰로만 운영(필요 시 확장)
$originalKey = null;
if ($type === 'paid') {
    $origExt    = strtolower(pathinfo($_FILES['original']['name'], PATHINFO_EXTENSION));
    $originalKey= "original/{$uid}/{$today}/" . $rand() . '.' . $origExt;
}

// --------------------------
// 5) S3 업로드 (예외 처리)
// --------------------------
try {
    // 프리뷰: 공개 (public-read), SSE 적용
    $s3->putObject([
        'Bucket'               => $publicBucket,
        'Key'                  => $previewKey,
        'SourceFile'           => $previewTmp,
        'ContentType'          => $previewMime,
        'ACL'                  => 'public-read',
        'ServerSideEncryption' => 'AES256',
    ]);

    if ($type === 'paid' && $originalKey !== null) {
        // 원본: 비공개 (기본 private), SSE 적용
        $s3->putObject([
            'Bucket'               => $privateBucket,
            'Key'                  => $originalKey,
            'SourceFile'           => $originalTmp,
            'ContentType'          => $originalMime,
            'ServerSideEncryption' => 'AES256',
        ]);
    }
} catch (AwsException $e) {
    error_log('S3 업로드 실패: ' . $e->getMessage());
    http_response_code(500);
    exit('파일 저장 실패 (S3)');
}

// --------------------------
// 6) DB 저장 (트랜잭션)
// --------------------------
try {
    $pdo = new PDO(
        getenv('DSN'),
        getenv('DB_USER'),
        getenv('DB_PASS'),
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );

    $pdo->beginTransaction();

    // 공개 URL(프리뷰) / 비공개 키(원본)
    $previewUrl = "https://{$publicBucket}.s3.{$region}.amazonaws.com/{$previewKey}";

    if ($type === 'free') {
        // 무료 사진: free_photos 테이블 예시 (스키마에 맞춰 수정)
        $stmt = $pdo->prepare(
            'INSERT INTO free_photos
             (uploader_id, title, description, path_preview, path_original, visibility, download_count, created_at)
             VALUES (?, ?, ?, ?, ?, ?, 0, NOW())'
        );
        // 무료인 경우 원본은 없을 수 있으므로 previewKey를 원본으로도 저장하거나 NULL 처리(정책에 따라)
        $stmt->execute([
            $uid,
            mb_substr($title, 0, 200, 'UTF-8'),
            $desc,
            $previewUrl,
            $previewKey,          // 혹은 NULL
            'public'
        ]);
    } else {
        // 유료 사진: paid_photos 테이블 예시 (스키마에 맞춰 수정)
        $stmt = $pdo->prepare(
            'INSERT INTO paid_photos
             (seller_id, title, description, path_preview, path_original, price_amount, status, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, NOW())'
        );
        $stmt->execute([
            $uid,
            mb_substr($title, 0, 200, 'UTF-8'),
            $desc,
            $previewUrl,          // 공개 URL
            $originalKey,         // 비공개 S3 키
            $price,
            'available'
        ]);
    }

    $pdo->commit();

} catch (Throwable $e) {
    // 실패 시 S3 정리
    try {
        $s3->deleteObject(['Bucket' => $publicBucket,  'Key' => $previewKey]);
    } catch (Throwable $ee) { /* 로그만 */ error_log('S3 정리 실패(preview): '.$ee->getMessage()); }
    if ($type === 'paid' && $originalKey) {
        try {
            $s3->deleteObject(['Bucket' => $privateBucket, 'Key' => $originalKey]);
        } catch (Throwable $ee) { error_log('S3 정리 실패(original): '.$ee->getMessage()); }
    }

    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('DB 저장 실패: ' . $e->getMessage());
    http_response_code(500);
    exit('저장 중 오류');
}

// --------------------------
// 7) 성공 응답
// --------------------------
header('Location: /trade.php?created=1');
exit;
