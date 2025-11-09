<?php
$code          = isset($code) && is_numeric($code) ? (int)$code : null;
$pageTitle     = $pageTitle    ?? (($code ? $code . ' ' : '') . '오류');
$errorMessage  = $errorMessage ?? '예상치 못한 오류가 발생했습니다.';
$errorCode     = $code ?? 'ERR';

ob_start();
?>
<p><?= htmlspecialchars($errorMessage) ?></p>
<?php if (!empty($hint)): ?>
  <p class="muted"><?= htmlspecialchars($hint) ?></p>
<?php endif; ?>
<?php
$content = ob_get_clean();

require __DIR__ . '/../layouts/error_layout.php';
