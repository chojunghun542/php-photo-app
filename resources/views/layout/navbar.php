<?php
// 세션 시작 유지
if (session_status() === PHP_SESSION_NONE) session_start();

// 현재 메뉴 활성화 표시
function set_active_class($page_file) {
    return (strpos($_SERVER['PHP_SELF'], $page_file) !== false) ? 'active' : '';
}

// 로그인 사용자의 포인트를 표시하기 위해(로그인 상태에서만) DB에서 조회
$points = null;
if (!empty($_SESSION['user'])) {
    try {
        // navbar.php는 public/ 아래에 있으므로 app 경로는 ../app/...
        require_once __DIR__.'/../app/db.php';
        // 로그인 세션 구조에 따라 사용자 ID 키를 맞춰주세요.
        // 1) 만약 $_SESSION['user']가 문자열 ID(로그인 ID)라면:
        $currentUserId = is_array($_SESSION['user']) ? ($_SESSION['user']['id'] ?? null) : $_SESSION['user'];

        if ($currentUserId) {
            $st = $pdo->prepare('SELECT points, role FROM users WHERE id = ?');
            $st->execute([$currentUserId]);
            $row = $st->fetch();
            if ($row) {
                $points = (int)$row['points'];
                $currentUserRole = $row['role'] ?? null;
            }
        }
    } catch (Exception $e) {
        // 포인트 표시 실패는 무시 (네비게이션은 계속 표시)
    }
}
?>
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="index.php">📸 PhotoShare</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" 
                data-bs-target="#navbarNav" aria-controls="navbarNav" 
                aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">

                <li class="nav-item">
                    <a class="nav-link <?php echo set_active_class('index.php'); ?>" href="index.php">홈</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?php echo set_active_class('trade.php'); ?>" href="trade.php">사진 거래</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?php echo set_active_class('gallery.php'); ?>" href="upload_page.php">업로드된 사진</a>
                </li>

                <?php if (!empty($_SESSION['user'])): ?>
                    <!-- 판매 등록: 거래용 업로드 폼 -->
                    <li class="nav-item">
                        <a class="nav-link <?php echo set_active_class('photo_new.php'); ?>" href="photo_new.php">판매 등록</a>
                    </li>

                    <!-- 포인트 표시 -->
                    <?php if ($points !== null): ?>
                        <li class="nav-item">
                            <span class="nav-link">포인트: <strong><?= (int)$points ?></strong></span>
                        </li>
                    <?php endif; ?>

                    <!-- (선택) 관리자 포인트 충전 페이지 노출: role이 ADMIN인 경우 -->
                    <?php if (!empty($currentUserRole) && strtoupper($currentUserRole) === 'ADMIN'): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo set_active_class('admin_topup.php'); ?>" href="admin_topup.php">충전(관리자)</a>
                        </li>
                    <?php endif; ?>

                    <li class="nav-item">
                        <a class="nav-link <?php echo set_active_class('profile.php'); ?>" href="profile.php">내 프로필</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">로그아웃</a>
                    </li>

                <?php else: ?>
                    <li class="nav-item"><a class="nav-link btn" href="login.php">로그인</a></li>
                    <li class="nav-item"><a class="nav-link btn" href="register.html">회원가입</a></li>
                <?php endif; ?>

            </ul>
        </div>
    </div>
</nav>
