<?php
require_once __DIR__.'/../app/bootstrap.php';
require_once __DIR__.'/../app/functions.php';
require_once __DIR__.'/../app/db.php';

require_login(); // 로그인 필수

// ───── 관리자 확인: user_num = 1 인 계정만 허용 ─────
$currentUid = $_SESSION['user']['user_id'] ?? null;
if (!$currentUid) { http_response_code(401); exit('로그인 필요'); }

$st = $pdo->prepare('SELECT user_num FROM users WHERE user_id = :uid');
$st->execute([':uid' => $currentUid]);
$adminRow = $st->fetch(PDO::FETCH_ASSOC);
if (!$adminRow || (int)$adminRow['user_num'] !== 1) {
    http_response_code(403); exit('권한 없음');
}

// POST 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['csrf'] ?? '')) { http_response_code(403); exit('CSRF'); }

    $rawTarget = trim($_POST['user_id'] ?? '');
    $amount    = (int)($_POST['amount'] ?? 0);

    if ($rawTarget === '' || $amount <= 0) { echo '입력 오류'; exit; }

    // 입력이 숫자면 user_id로, 아니면 login_id로 사용자 조회
    if (ctype_digit($rawTarget)) {
        $find = $pdo->prepare('SELECT user_id FROM users WHERE user_id = :v');
        $find->execute([':v' => (int)$rawTarget]);
    } else {
        $find = $pdo->prepare('SELECT user_id FROM users WHERE login_id = :v');
        $find->execute([':v' => $rawTarget]);
    }
    $targetRow = $find->fetch(PDO::FETCH_ASSOC);
    if (!$targetRow) { echo '대상 사용자를 찾을 수 없습니다.'; exit; }
    $targetUserId = (int)$targetRow['user_id'];

    try {
        $pdo->beginTransaction();

        // users 테이블에 points 컬럼이 있다고 가정
        $u = $pdo->prepare('UPDATE users SET points = COALESCE(points,0) + :amt WHERE user_id = :uid');
        $u->execute([':amt' => $amount, ':uid' => $targetUserId]);

        // transactions 테이블: user_id, type, amount, reason, created_at(있으면)
        $t = $pdo->prepare('
            INSERT INTO transactions (user_id, type, amount, reason, created_at)
            VALUES (:uid, :type, :amt, :reason, NOW())
        ');
        $t->execute([
            ':uid'    => $targetUserId,
            ':type'   => 'credit',
            ':amt'    => $amount,
            ':reason' => 'admin_topup'
        ]);

        $pdo->commit();
        echo '충전 완료';
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        http_response_code(500);
        echo '오류: '.$e->getMessage(); // 운영환경에선 사용자에게 일반 메시지로 교체
    }
    exit;
}
?>
<!doctype html>
<html lang="ko">
<head>
  <meta charset="utf-8">
  <title>포인트 충전(관리자)</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php require_once __DIR__.'/navbar.php'; ?>
<div class="container" style="max-width:480px; margin-top:40px;">
  <div class="card p-4">
    <h3>관리자 포인트 충전</h3>
    <form method="post">
      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
      <div class="mb-2">
        <label class="form-label">유저 식별자</label>
        <input name="user_id" class="form-control" placeholder="user_id 또는 login_id" required>
      </div>
      <div class="mb-2">
        <label class="form-label">금액</label>
        <input type="number" name="amount" min="1" class="form-control" required>
      </div>
      <button class="btn btn-primary w-100">충전</button>
    </form>
  </div>
</div>
</body>
</html>
