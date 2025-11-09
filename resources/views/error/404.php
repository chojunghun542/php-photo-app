<?php
// /var/www/html/resources/views/error/404.php

// 출력 버퍼 시작 → 이 안의 내용이 $content에 담김
ob_start();
?>
  <p>요청하신 페이지를 찾을 수 없습니다.<br>
  입력하신 주소가 정확한지 확인해 주세요.</p>
<?php
$content = ob_get_clean();

// 레이아웃에 전달할 변수 정의
$pageTitle = '404 Not Found';
$errorCode = 404;
$homeUrl   = '/';

// 절대경로 기반 include (상대경로 문제 방지)
require __DIR__ . '/../layouts/error_layout.php';
