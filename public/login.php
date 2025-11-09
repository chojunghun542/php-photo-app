<?php
require_once __DIR__.'/../app/bootstrap.php';
require_once __DIR__.'/../app/functions.php';
?>
<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <title>로그인 - PhotoShare</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php require_once __DIR__.'/navbar.php'; ?>
<div class="container" style="max-width:420px; margin-top:80px;">
  <div class="card p-4">
    <h3 class="mb-3">로그인</h3>
    <form action="login_process.php" method="post">
      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
      <div class="mb-3">
        <label class="form-label">아이디</label>
        <input type="text" class="form-control" name="username" required>
      </div>
      <div class="mb-3">
        <label class="form-label">비밀번호</label>
        <input type="password" class="form-control" name="password" required>
      </div>
      <button class="btn btn-primary w-100">로그인</button>
    </form>
  </div>
</div>
</body>
</html>
