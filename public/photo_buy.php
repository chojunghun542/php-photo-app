<?php
require_once __DIR__.'/../app/bootstrap.php';
require_once __DIR__.'/../app/functions.php';
require_once __DIR__.'/../app/db.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }
if (!csrf_check($_POST['csrf'] ?? '')) { http_response_code(403); exit('CSRF'); }

$photo_id = (int)($_POST['photo_id'] ?? 0);
$buyer_id = $_SESSION['user']['id'];

// 사진 정보
$st = $pdo->prepare('SELECT p.*, u.id seller_id FROM photos p JOIN users u ON u.id=p.user_id WHERE p.id=?');
$st->execute([$photo_id]); $p = $st->fetch();
if (!$p) exit('존재하지 않는 사진');

if ($p['price'] <= 0) { header('Location: /download.php?id='.$photo_id); exit; }
if (has_purchased($pdo, $photo_id, $buyer_id)) { header('Location: /download.php?id='.$photo_id); exit; }
if ($buyer_id === $p['seller_id']) exit('자신의 사진은 구매할 수 없습니다.');

// 잔액 확인
$st = $pdo->prepare('SELECT points FROM users WHERE id=?'); $st->execute([$buyer_id]);
$points = (int)$st->fetchColumn();
if ($points < (int)$p['price']) exit('포인트 부족');

// 트랜잭션(원자성) 처리
$pdo->beginTransaction();
try {
  // 1) 구매기록
  $ins = $pdo->prepare('INSERT INTO purchases (photo_id, buyer_id, price) VALUES (?,?,?)');
  $ins->execute([$photo_id, $buyer_id, (int)$p['price']]);

  // 2) 포인트 차감/가산
  $pdo->prepare('UPDATE users SET points=points-? WHERE id=?')->execute([(int)$p['price'], $buyer_id]);
  $pdo->prepare('UPDATE users SET points=points+? WHERE id=?')->execute([(int)$p['price'], $p['seller_id']]);

  // 3) 원장 기록
  $pdo->prepare('INSERT INTO transactions (user_id, type, amount, reason) VALUES (?, "debit", ?, ?)')->execute([$buyer_id, (int)$p['price'], 'purchase:photo#'.$photo_id]);
  $pdo->prepare('INSERT INTO transactions (user_id, type, amount, reason) VALUES (?, "credit", ?, ?)')->execute([$p['seller_id'], (int)$p['price'], 'sale:photo#'.$photo_id]);

  $pdo->commit();
} catch (Exception $e) {
  $pdo->rollBack();
  exit('구매 실패(중복 구매 또는 서버 오류)');
}
header('Location: /download.php?id='.$photo_id);
