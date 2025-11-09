<?php
// /var/www/html/app/Controllers/PostController.php

declare(strict_types=1);

namespace app\Controllers;

use app\Core\View;
use app\Models\Post;

class PostController extends BaseController
{
    private Post $postModel;

    public function __construct()
    {
        parent::__construct();
        $this->postModel = new Post();
    }

    /**
     * 게시글 목록 페이지
     */
    public function index(): void
    {
        $q = trim($_GET['q'] ?? '');
        $createdMessage = $_GET['created'] ?? null;

        $posts = $this->postModel->findAllPosts($q);

        $data = [
            'pageTitle'      => 'PhotoShare - 게시판',
            'posts'          => $posts,
            'q'              => $q,
            'createdMessage' => $createdMessage,
        ];

        // ✅ BaseController가 생성한 View 인스턴스 사용
        $this->view->render('post/main', $data);
    }

    public function error(): void
    {
        http_response_code(404);
        $this->view->render('error/404', [
            'pageTitle'    => '404 오류',
            'errorMessage' => '페이지를 찾을 수 없습니다.',
            'homeUrl'      => '/',
            'showHomeLink' => true,
        ]);
    }

    public function serverError(): void
    {
        http_response_code(500);
        $this->view->render('error/500', [
            'pageTitle'    => '500 내부 서버 오류',
            'errorMessage' => '요청 처리 중 문제가 발생했습니다.',
            'homeUrl'      => '/',
        ]);
    }

    public function genericError(int $code = 400, string $message = '잘못된 요청입니다.', ?string $hint = null): void
    {
        http_response_code($code);
        $this->view->render('error/generic', [
            'code'         => $code,
            'pageTitle'    => $code . ' 오류',
            'errorMessage' => $message,
            'hint'         => $hint,
            'homeUrl'      => '/',
        ]);
    }

    public function view(): void
    {
        // $this->view->render('post/view', [...]);
    }

    public function create(): void
    {
        // $this->view->render('post/create', [...]);
    }
}
