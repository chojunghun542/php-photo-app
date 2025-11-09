<?php
require_once __DIR__.'/../app/bootstrap.php';
require_once __DIR__.'/../app/functions.php';
require_once __DIR__.'/../app/db.php';

// 검색어
$q = trim($_GET['q'] ?? '');

// ✅ 스키마에 맞춘 SELECT (paid_photos + users)
// - paid_id → id 별칭
// - price_amount → price 별칭
// - users.login_id 를 판매자 표시명으로 사용
$sql = 'SELECT
          pp.paid_id           AS id,
          pp.title             AS title,
          pp.description       AS description,
          pp.price_amount      AS price,
          pp.path_preview      AS path_preview,
          pp.seller_id         AS user_id,     -- 소유자(등록자)
          u.user_id            AS seller_id,   -- 판매자 ID
          u.login_id           AS seller_name  -- 판매자 표시용 이름
        FROM paid_photos pp
        JOIN users u ON u.user_id = pp.seller_id';

$params = [];
if ($q !== '') {
    $sql .= ' WHERE pp.title LIKE :q OR pp.description LIKE :q';
    $params[':q'] = '%'.$q.'%';
}
$sql .= ' ORDER BY pp.paid_id DESC';

$st = $pdo->prepare($sql);
$st->execute($params);
$rows = $st->fetchAll(PDO::FETCH_ASSOC);

// ✅ 로그인 사용자 ID는 user_id 키로 관리(이전에 그렇게 통일)
$me = $_SESSION['user']['user_id'] ?? null;
?>
<!doctype html>
<html lang="ko">
<head>
<meta charset="utf-8">
<title>사진 거래</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>.card img{height:220px;object-fit:cover}</style>
</head>
<body>
<?php require_once __DIR__.'/navbar.php'; ?>
<div class="container my-4">
  <div class="d-flex align-items-center gap-3">
    <form class="ms-auto d-flex" method="get" action="trade.php">
      <input class="form-control me-2" name="q" placeholder="검색(제목/설명)" value="<?= e($q) ?>">
      <button class="btn btn-outline-primary">검색</button>
    </form>
    <a class="btn btn-primary" href="photo_new.php">+ 판매 등록</a>
  </div>
  <hr>

  <?php if (empty($rows)): ?>
    <p class="text-muted">등록된 사진이 없습니다.</p>
  <?php else: ?>
    <div class="row g-4">
      <?php foreach ($rows as $r): ?>
        <div class="col-md-4">
          <div class="card h-100">
            <img src="<?= e($r['path_preview']) ?>" class="card-img-top" alt="">
            <div class="card-body">
              <h5 class="card-title"><?= e($r['title']) ?></h5>
              <p class="card-text" style="display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden;">
                <?= e($r['description']) ?>
              </p>
              <p class="text-primary fw-bold mb-2"><?= (int)$r['price'] ?> 포인트</p>

              <div class="d-flex gap-2">
                <a class="btn btn-outline-secondary btn-sm" href="view.php?id=<?= (int)$r['id'] ?>">상세</a>

                <?php if ($me): ?>
                  <?php
                    // 다운로드 가능 조건(예시): 무료이거나, 내가 판매자이거나, 이미 구매함
                    $can_download = ((int)$r['price'] === 0)
                                    || ((int)$me === (int)$r['seller_id'])
                                    || has_purchased($pdo, (int)$r['id'], (int)$me); // 구현체는 그대로 사용
                  ?>
                  <?php if ($can_download): ?>
                    <a class="btn btn-success btn-sm" href="download.php?id=<?= (int)$r['id'] ?>">다운로드</a>
                  <?php else: ?>
                    <form method="post" action="photo_buy.php" onsubmit="return confirm('구매하시겠습니까?');">
                      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                      <input type="hidden" name="photo_id" value="<?= (int)$r['id'] ?>">
                      <button class="btn btn-primary btn-sm">구매</button>
                    </form>
                  <?php endif; ?>
                <?php else: ?>
                  <a class="btn btn-primary btn-sm" href="login.php">로그인 후 구매</a>
                <?php endif; ?>
              </div>
            </div>

            <div class="card-footer small text-muted">
              판매자: <?= e($r['seller_name']) ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
</body>
</html>
