<?php
require_once __DIR__.'/../app/bootstrap.php';
require_once __DIR__.'/../app/functions.php';
require_once __DIR__.'/../app/db.php';

require_login();
?>
<!doctype html><html lang="ko"><head>
<meta charset="utf-8"><title>판매 등록</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head><body>
<?php require_once __DIR__.'/navbar.php'; ?>
<div class="container" style="max-width:720px; margin-top:30px;">
  <h3>사진 판매 등록</h3>
  <form class="card p-4" method="post" action="photo_new_post.php" enctype="multipart/form-data">
    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
    <div class="mb-3">
      <label class="form-label">제목</label>
      <input name="title" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">설명</label>
      <textarea name="description" class="form-control" rows="4"></textarea>
    </div>
    <div class="mb-3">
      <label class="form-label">가격(포인트)</label>
      <input type="number" name="price" min="0" step="1" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">원본 이미지(유료 다운로드 대상)</label>
      <input type="file" name="original" accept=".jpg,.jpeg,.png,.gif" required class="form-control">
    </div>
    <div class="mb-3">
      <label class="form-label">프리뷰 이미지(공개 노출용)</label>
      <input type="file" name="preview" accept=".jpg,.jpeg,.png,.gif" required class="form-control">
    </div>
    <button class="btn btn-primary">등록</button>
  </form>
</div>
</body></html>
