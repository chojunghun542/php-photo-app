<?php require_once dirname(__DIR__, 3) . '/public/navbar.php'; ?>
<?php if (!empty($errors)): ?>
  <div class="alert alert-danger"><?= htmlspecialchars(implode('<br>', $errors), ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data" class="card p-4">
  <div class="mb-3">
    <label class="form-label">제목</label>
    <input name="title" class="form-control" required>
  </div>
  <div class="mb-3">
    <label class="form-label">내용</label>
    <textarea name="body" class="form-control" rows="5" required></textarea>
  </div>
  <div class="mb-3">
    <label class="form-label">이미지</label>
    <input type="file" name="image" accept=".jpg,.jpeg,.png,.gif" required class="form-control">
  </div>
  <button class="btn btn-primary">등록</button>
</form>
