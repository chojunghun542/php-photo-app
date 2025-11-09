<?php
require_once __DIR__.'/../app/bootstrap.php';
require_once __DIR__.'/../app/functions.php';
require_once __DIR__.'/../app/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        exit('이미지 필요');
    }
    $f = $_FILES['image'];
    $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
    $safe = bin2hex(random_bytes(16)).'.'.$ext;
    $dest = __DIR__.'/uploads/trade/'.$safe;
    if (!move_uploaded_file($f['tmp_name'], $dest)) exit('저장 실패');
    // You may insert trade record in DB
    echo 'OK';
}
