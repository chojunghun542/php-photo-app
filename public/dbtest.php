<?php
require_once __DIR__ . '/../app/db.php';

$stmt = $pdo->query("SELECT NOW() as ts, @@hostname as host");
$row = $stmt->fetch();

//echo "✅ DB 연결 성공!<br>";
//echo "시간: {$row['ts']}<br>";
//echo "호스트: {$row['host']}<br>";
