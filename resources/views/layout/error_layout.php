<?php
// 레이아웃에서 사용할 공통 변수 기본값
$pageTitle    = $pageTitle    ?? '오류';
$errorCode    = $errorCode    ?? null; // 404, 500 등
$homeUrl      = $homeUrl      ?? '/';
$showHomeLink = $showHomeLink ?? true;

// $content 는 개별 에러 뷰에서 ob_get_clean()으로 만들어 전달됨
?>
<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1.0" />
  <title><?= htmlspecialchars($pageTitle) ?></title>
  <style>
    :root { --fg:#2b2f33; --muted:#6c757d; --bg:#f8f9fa; --accent:#0d6efd; }
    * { box-sizing: border-box; }
    html, body { height: 100%; }
    body {
      margin: 0; font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Noto Sans KR", Arial, "Apple SD Gothic Neo", sans-serif;
      color: var(--fg); background: var(--bg);
      display: grid; place-items: center;
    }
    .wrap {
      width: min(880px, 92vw);
      background: #fff; border-radius: 16px; padding: 28px 28px 32px;
      box-shadow: 0 10px 30px rgba(20,25,30,.06), 0 2px 10px rgba(20,25,30,.04);
    }
    .head {
      display:flex; align-items:center; gap:14px; margin-bottom:14px;
    }
    .badge {
      display:inline-flex; align-items:center; justify-content:center;
      width:42px; height:42px; border-radius:12px; background:#eef4ff; color:#1849aa;
      font-weight:700; font-size:18px;
    }
    .title { font-size:24px; margin:0; }
    .muted { color: var(--muted); margin: 6px 0 18px; }
    .content { font-size:16px; line-height:1.65; }
    .actions { margin-top:22px; display:flex; gap:10px; flex-wrap:wrap; }
    a.btn, button.btn {
      appearance:none; border:1px solid var(--accent); color:var(--accent);
      background:#fff; padding:10px 16px; border-radius:10px; text-decoration:none; cursor:pointer;
    }
    a.btn.primary, button.btn.primary { background: var(--accent); color:#fff; }
    .small { font-size:13px; color:var(--muted); margin-top:10px; }
    code.k { background:#f3f4f6; padding:2px 6px; border-radius:6px; font-family:ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; }
  </style>
</head>
<body>
  <main class="wrap" role="main" aria-live="polite">
    <header class="head">
      <div class="badge"><?= htmlspecialchars($errorCode ?? 'ERR') ?></div>
      <h1 class="title"><?= htmlspecialchars($pageTitle) ?></h1>
    </header>

    <section class="content">
      <?= $content ?? '' ?>
    </section>

    <?php if ($showHomeLink): ?>
    <div class="actions">
      <a class="btn primary" href="<?= htmlspecialchars($homeUrl) ?>">홈으로 돌아가기</a>
      <button class="btn" onclick="history.back()">이전 페이지</button>
    </div>
    <?php endif; ?>

    <p class="small">문제가 반복되면 관리자에게 문의해 주세요.</p>
  </main>
</body>
</html>
