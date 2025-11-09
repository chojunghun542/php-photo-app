<?php
require_once __DIR__.'/../app/bootstrap.php';
require_once __DIR__.'/../app/db.php';
require_once __DIR__.'/../app/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

// CSRF 토큰 optional for login, but we check here
if (!csrf_check($_POST['csrf'] ?? '')) {
    http_response_code(403);
    exit('Invalid CSRF token');
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if ($username === '' || $password === '') {
    exit('<h3>아이디와 비밀번호를 모두 입력하세요.</h3>');
}

if (!isset($_SESSION['login_attempts'])) $_SESSION['login_attempts'] = 0;
if ($_SESSION['login_attempts'] >= 10) {
    http_response_code(429);
    exit('Too many attempts. Try later.');
}

$stmt = $pdo->prepare('
    SELECT user_id, login_id, email, password, phone_num
    FROM users
    WHERE login_id = :login_id
');
$stmt->execute([':login_id' => $loginId]);
$user = $stmt->fetch();

/* password 컬럼이 bcrypt/argon2 해시라고 가정 */
if ($user && password_verify($password, $user['password'])) {
    session_regenerate_id(true);
    $_SESSION['user'] = [
        'user_id'   => $user['user_id'],
        'login_id'  => $user['login_id'],
        'email'     => $user['email'],
        'phone_num' => $user['phone_num'] ?? null,
        'role'      => 'user', // 스키마에 role 없음 → 필요 시 기본값
    ];
    $_SESSION['login_attempts'] = 0;
    header('Location: index.php');
    exit;
} else {
    $_SESSION['login_attempts']++;
    echo '<p>로그인 실패</p><a href="login.php">Back</a>';
}