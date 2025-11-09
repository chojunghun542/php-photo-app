<?php

// ====================================
// 1. 초기화 및 클래스 로드
// ====================================

// 오류 보고 설정 (개발 환경에서만 사용) - 이 코드가 없으면 Fatal Error를 숨길 수 있습니다.
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 세션 시작 (로그인 기능을 위해 필수)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 모든 클래스 파일을 로드합니다. (Composer의 오토로딩이 없을 경우 필수)
// __DIR__은 현재 파일(index.php)의 디렉토리입니다.
// ----------------------------------------------------
// !!! 주의: 이 경로에 파일이 없으면 404로 위장된 Fatal Error가 발생합니다.
// ----------------------------------------------------
require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Core/View.php';
require_once __DIR__ . '/../app/Models/User.php';
require_once __DIR__ . '/../app/Models/Post.php';
require_once __DIR__ . '/../app/Controllers/UserController.php';
require_once __DIR__ . '/../app/Controllers/PostController.php';

// ====================================
// 2. 요청 분석 및 컨트롤러 객체 생성
// ====================================

// 요청 URI (예: /login, /register/1)와 HTTP 메서드 (GET, POST)를 가져옵니다.
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestUri = trim($requestUri, '/');
$requestMethod = $_SERVER['REQUEST_METHOD'];

// 모든 요청은 이 두 컨트롤러 중 하나로 전달됩니다.
$userController = new \App\Controllers\UserController();
$postController = new \App\Controllers\PostController();

// ====================================
// 3. 라우팅 로직 (요청에 따라 Controller 메서드 실행)
// ====================================

// ------------------------------------
// A. 게시판 (Post) 관련 라우팅
// ------------------------------------
if ($requestUri === '' || $requestUri === 'home' || $requestUri === 'posts') {
    // URL: / 또는 /home 또는 /posts
    // 역할: 게시판 목록 페이지 표시
    $postController->index();
} 
// ------------------------------------
// B. 사용자 (User) 관련 라우팅
// ------------------------------------
else if ($requestUri === 'register') {
    // URL: /register
    if ($requestMethod === 'GET') {
        $userController->showRegisterForm(); // 회원가입 폼 표시
    } else if ($requestMethod === 'POST') {
        $userController->register();         // 회원가입 데이터 처리
    } else {
        header("HTTP/1.0 405 Method Not Allowed");
    }
}
else if ($requestUri === 'login') {
    // URL: /login
    if ($requestMethod === 'GET') {
        $userController->showLoginForm();    // 로그인 폼 표시
    } else if ($requestMethod === 'POST') {
        $userController->login();            // 로그인 인증 처리
    } else {
        header("HTTP/1.0 405 Method Not Allowed");
    }
}
else if ($requestUri === 'logout') {
    // URL: /logout
    // 역할: 로그아웃 처리
    $userController->logout();
}
// ------------------------------------
// C. 404 처리
// ------------------------------------
else {
    // 정의되지 않은 모든 경로
    header("HTTP/1.0 404 Not Found");
    echo "<h1>404 Not Found</h1><p>요청하신 페이지를 찾을 수 없습니다.</p>";
}

