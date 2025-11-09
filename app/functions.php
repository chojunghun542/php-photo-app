<?php
// app/functions.php - helpers
function e($s) {
    return htmlspecialchars($s ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf_token'];
}
function csrf_check($t) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], (string)$t);
}
/** 로그인 확인 헬퍼 */
function require_login() {
  if (empty($_SESSION['user']['id'])) {
    header('Location: /login.php'); exit;
  }
}

/** 구매 여부 확인 */
function has_purchased(PDO $pdo, int $photo_id, string $user_id): bool {
  $st = $pdo->prepare('SELECT 1 FROM purchases WHERE photo_id=? AND buyer_id=? LIMIT 1');
  $st->execute([$photo_id, $user_id]);
  return (bool)$st->fetchColumn();
}