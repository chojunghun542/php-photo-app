<?php
namespace App\Controllers;

use PDO;

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../functions.php';

class FreePhotoController
{
    // 라우터가 인자를 안 주니까 PDO 인자 없이 내부에서 db.php 로드
    public function create(): void
    {
        // DB 핸들 준비 ($pdo)
        require __DIR__ . '/../db.php';  // 여기서 $pdo 생성됨

        // ===== 로그인 체크 비활성화 =====
        /*
        if (empty($_SESSION['user']['user_id'])) {
            http_response_code(401);
            exit('로그인이 필요합니다.');
        }
        */

        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // ===== CSRF 비활성화 =====
            // if (!csrf_check($_POST['csrf'] ?? '')) $errors[] = 'CSRF invalid';

            $title = trim($_POST['title'] ?? '');
            $body  = trim($_POST['body'] ?? '');
            if ($title === '' || $body === '') $errors[] = '제목/내용 필요';

            if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
                $errors[] = '이미지 필요';
            }

            if (!$errors) {
                $f = $_FILES['image'];
                $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
                $allowExt  = ['jpg','jpeg','png','gif'];
                $allowMime = ['image/jpeg','image/png','image/gif'];

                if (!in_array($ext, $allowExt, true))  $errors[] = '확장자 오류';
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime  = finfo_file($finfo, $f['tmp_name']);
                finfo_close($finfo);
                if (!in_array($mime, $allowMime, true)) $errors[] = 'MIME 오류';

                if (!$errors) {
                    // 업로드 경로 (public/uploads/free_photos)
                    $uploadDir = dirname(__DIR__, 2) . '/public/uploads/free_photos';
                    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true)) {
                        $errors[] = '업로드 경로 생성 실패';
                    } else {
                        $safe = bin2hex(random_bytes(16)) . '.' . $ext;
                        $dest = $uploadDir . '/' . $safe;

                        if (!move_uploaded_file($f['tmp_name'], $dest)) {
                            $errors[] = '파일 저장 실패';
                        } else {
                            $relPath = 'uploads/free_photos/' . $safe;

                            $stmt = $pdo->prepare('
                                INSERT INTO free_photos
                                  (uploader_id, title, description, path_preview, path_original, visibility, download_count, created_at)
                                VALUES
                                  (:uploader_id, :title, :description, :path_preview, :path_original, :visibility, :download_count, NOW())
                            ');

                            // 세션 안 쓰는 임시값
                            $stmt->execute([
                                ':uploader_id'    => 1,
                                ':title'          => $title,
                                ':description'    => $body,
                                ':path_preview'   => $relPath,
                                ':path_original'  => $relPath,
                                ':visibility'     => 'public',
                                ':download_count' => 0,
                            ]);

                            header('Location: /index.php?created=1');
                            exit;
                        }
                    }
                }
            }
        }

        // ===== View 렌더링 =====
        // 네 View 클래스가 'free_photos/create' 형태를 받는다면:
        if (class_exists('\\App\\Core\\View')) {
            \App\Core\View::render('free_photos/create', ['errors' => $errors]);
            return;
        }

        // View 클래스 안 쓰는 경우: 직접 require
        $viewFile = dirname(__DIR__, 2) . '/resources/views/free_photos/create.php';
        if (!file_exists($viewFile)) {
            http_response_code(500);
            exit("❌ View 파일을 찾을 수 없습니다: " . $viewFile);
        }

        // 뷰에서 $errors 쓸 수 있게 변수 유지
        $errors = $errors ?? [];
        require $viewFile;
    }
}
