<?php
// /html/app/db.php

// 1️⃣ private/config.php 불러오기
$config = require __DIR__ . '/../private/config.php';
$db = $config['db'];  // DB 설정 배열

// 2️⃣ PDO 연결 생성
$dsn = "mysql:host={$db['host']};dbname={$db['name']};charset={$db['charset']}";
try {
    $pdo = new PDO($dsn, $db['user'], $db['pass'], [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    // 연결 실패 시 공용 에러 페이지로 이동 (error.php)
    http_response_code(500);
    header('Location: /error.php?msg=데이터베이스 연결 실패');
    exit;
}
