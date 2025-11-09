<?php
$pageTitle     = $pageTitle     ?? '500 내부 서버 오류';
$errorMessage  = $errorMessage  ?? '요청을 처리하는 중 문제가 발생했습니다. 잠시 후 다시 시도해 주세요.';
$errorCode     = 500;

ob_start();
?>
<p><?= htmlspecialchars($errorMessage) ?></p>
<p class="muted">오류가 지속되면 <code class="k">/var/log/apache2/error.log</code> 등 서버 로그를 확인해 주세요.</p>
<?php
$content = ob_get_clean();

require __DIR__ . '/../layouts/error_layout.php';
