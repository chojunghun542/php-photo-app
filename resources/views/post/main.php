<?php 
// Controller에서 전달된 변수: $posts, $q, $createdMessage, $pageTitle
// e() 함수가 정의되어 있지 않으므로 htmlspecialchars()를 사용합니다.
function e($text) {
    return htmlspecialchars($text ?? '', ENT_QUOTES, 'UTF-8');
}
?>
<!doctype html>
<html lang="ko">
<head>
  <meta charset="utf-8">
  <title><?= e($pageTitle) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>.photo-card img { height:220px; object-fit:cover; }</style>
</head>
<body class="bg-light">
<?php // require_once __DIR__.'/navbar.php'; // 네비게이션바는 레이아웃 시스템으로 처리하는 것이 이상적입니다. ?>
<header class="bg-white border-bottom">
  <div class="container py-4 d-flex gap-3 align-items-center">
    <div class="me-auto">
      <h2 class="mb-0">📷 사진 게시판</h2>
      <p class="text-muted mb-0">제목/설명으로 검색하세요.</p>
    </div>
    <!-- 폼 액션이 이제 index.php 대신 /posts (라우팅 경로)로 요청을 보냅니다. -->
    <form class="d-flex" method="get" action="/posts">
      <input class="form-control me-2" type="search" name="q" placeholder="검색(제목·설명)" value="<?= e($q) ?>">
      <button class="btn btn-outline-primary">검색</button>
    </form>
    <a href="/create" class="btn btn-primary">+ 글쓰기</a>
  </div>
</header>

<main class="container my-4">
  <?php if ($createdMessage): ?><div class="alert alert-success">게시글 등록되었습니다.</div><?php endif; ?>

  <?php if (!$posts): ?>
    <p class="text-muted text-center py-5">게시글이 없습니다.</p>
  <?php else: ?>
    <div class="row g-4">
      <?php foreach ($posts as $p): ?>
        <div class="col-md-4">
          <div class="card photo-card shadow-sm h-100">
            <!-- URL도 MVC 라우팅 경로로 변경: view.php?id=... 대신 /view/게시글ID 또는 /posts/게시글ID -->
            <a href="/posts/<?= (int)$p['id'] ?>" class="text-decoration-none">
              <img src="<?= e($p['image_path']) ?>" class="card-img-top" alt="<?= e($p['title']) ?>">
            </a>
            <div class="card-body">
              <h5 class="card-title text-truncate"><?= e($p['title']) ?></h5>
              <p class="card-text" style="display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden;"><?= e($p['body']) ?></p>
              <a class="btn btn-sm btn-outline-secondary" href="/posts/<?= (int)$p['id'] ?>">자세히</a>
            </div>
            <div class="card-footer small text-muted"><?= e($p['created_at']) ?></div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</main>
</body>
</html>

