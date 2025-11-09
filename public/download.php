<?php
require_once __DIR__.'/../app/bootstrap.php';
require_once __DIR__.'/../app/functions.php';
require_once __DIR__.'/../app/db.php';
require_login();

$id = (int)($_GET['id'] ?? 0);
$me = $_SESSION['user']['id'];

$st = $pdo->prepare('SELECT p.*, u.id seller_id FROM photos p JOIN users u ON u.id=p.user_id WHERE p.id=?');
$st->execute([$id]); $p = $st->fetch();
if (!$p) { http_response_code(404); exit('없음'); }

$allowed = ($me === $p['seller_id']) || $p['price']==0 || has_purchased($pdo, $id, $me);
if (!$allowed) { http_response_code(403); exit('구매 필요'); }

$abs = realpath(__DIR__.'/../'.$p['path_private']);
if (!$abs || !is_file($abs)) { http_response_code(410); exit('파일 없음'); }

$fname = basename($abs);
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="'.rawurlencode($fname).'"');
header('Content-Length: '.filesize($abs));
readfile($abs);
