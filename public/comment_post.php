<?php
require_once __DIR__.'/../app/bootstrap.php';
require_once __DIR__.'/../app/functions.php';
require_once __DIR__.'/../app/db.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }
if (!csrf_check($_POST['csrf'] ?? '')) { http_response_code(403); exit('CSRF'); }

$photo_id = (int)($_POST['photo_id'] ?? 0);
$body = trim($_POST['body'] ?? '');
$uid = $_SESSION['user']['id'];

if ($photo_id <= 0 || $body === '') exit('입력 오류');
$st = $pdo->prepare('INSERT INTO comments (photo_id, user_id, body) VALUES (?,?,?)');
$st->execute([$photo_id, $uid, $body]);

header('Location: /view.php?id='.$photo_id.'#comments');
