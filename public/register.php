<?php
require_once __DIR__.'/../app/bootstrap.php';
require_once __DIR__.'/../app/db.php';
require_once __DIR__.'/../app/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}
if (function_exists('csrf_check') && !csrf_check($_POST['csrf'] ?? '')) {
    http_response_code(403);
    exit('Invalid CSRF token');
}

$login_id = trim($_POST['id'] ?? '');
$pw       = $_POST['pw'] ?? '';
$email    = trim($_POST['email'] ?? '');
$phone    = trim($_POST['phone'] ?? '');
$passq    = trim($_POST['passq'] ?? '');
$passas   = trim($_POST['passas'] ?? '');

if ($login_id === '' || $pw === '' || $email === '') {
    exit('아이디, 비밀번호, 이메일은 필수입니다.');
}

/* ✅ 이메일 형식 검증 (RFC 표준 방식)
   PHP 내장 필터를 사용하면 정규식보다 정확하고 안전합니다.
   예시: test@example.com, a.b-c@domain.co.kr 허용 */
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    exit('잘못된 이메일 형식입니다. 예: user@example.com');
}

/* 추가로 도메인 형식이 매우 제한적이어야 하면 다음 정규식 예시 사용 가능:
   if (!preg_match('/^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/', $email)) {
       exit('이메일 형식이 올바르지 않습니다.');
   }
*/

$check = $pdo->prepare('SELECT login_id FROM users WHERE login_id = ?');
$check->execute([$login_id]);
if ($check->fetch()) exit('이미 존재하는 아이디입니다.');

$hashed = password_hash($pw, PASSWORD_DEFAULT);

$stmt = $pdo->prepare('
  INSERT INTO users (login_id, email, password, phone_num, passq, passas, id_time)
  VALUES (?, ?, ?, ?, ?, ?, NOW())
');
$stmt->execute([$login_id, $email, $hashed, $phone, $passq, $passas]);

header('Location: login.php');
exit;
