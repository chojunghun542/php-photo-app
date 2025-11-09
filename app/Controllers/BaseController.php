<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;

abstract class BaseController
{
    protected View $view;
    protected ?int $userId = null;

    public function __construct()
    {
        $this->view = new View();

        if (isset($_SESSION['user_id'])) {
            $this->userId = (int)$_SESSION['user_id'];
        }

        // ✅ Fatal error 원인 해결
        $this->loadCommonData();
    }

    /**
     * 모든 컨트롤러에서 공통으로 필요한 데이터를 View에 전달합니다.
     */
    protected function loadCommonData(): void
    {
        $this->view->assign('isLoggedIn', $this->userId !== null);

        if ($this->userId !== null) {
            $this->view->assign('currentUser', [
                'id'   => $this->userId,
                'name' => $_SESSION['user_name'] ?? 'Guest',
            ]);
        }

        $this->view->assign('siteTitle', 'PhotoShare');
    }

    protected function requireAuth(): bool
    {
        if ($this->userId === null) {
            header('Location: /login');
            exit;
        }
        return true;
    }
}
