<?php
// /var/www/html/public/index.php (Front Controller) - Fixed .php suffix

declare(strict_types=1);

// ----------------------------------------------------
// 1. 시스템 초기화 및 Autoloading (수동 로딩)
// ----------------------------------------------------

// 모든 핵심 파일 로드 (Composer가 없으므로 수동으로 모두 로드)
require_once __DIR__ . '/../app/Core/Database.php'; 
require_once __DIR__ . '/../app/Core/View.php';     
require_once __DIR__ . '/../app/Models/Post.php';
require_once __DIR__ . '/../app/Models/User.php';
require_once __DIR__ . '/../app/Controllers/BaseController.php';
require_once __DIR__ . '/../app/Controllers/PostController.php';
require_once __DIR__ . '/../app/Controllers/UserController.php';

// app/bootstrap.php 내용 통합 (session과 security headers)
if (session_status() === PHP_SESSION_NONE) {
    // secure 옵션은 HTTPS 환경에서만 'true'로 설정해야 합니다.
    session_set_cookie_params([
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off', 
        'httponly' => true,
        'samesite' => 'Lax',
        'path' => '/',
    ]);
    session_start();
}
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');

// ----------------------------------------------------
// 2. 라우팅 (Routing) - 요청을 Controller/Action으로 연결
// ----------------------------------------------------

$uri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '', '/');
$segments = explode('/', $uri);
$method = $_SERVER['REQUEST_METHOD']; 

// 기본 설정: 루트 요청은 PostController의 index 메서드로 연결
$controllerClass = 'app\\Controllers\\PostController';
$actionName = 'index'; 
$params = []; 

// 첫 번째 세그먼트를 기반으로 Controller와 Action 분석
if (!empty($segments[0])) {
    // [수정된 로직] 세그먼트에서 '.php' 접미사를 제거
    $segment0 = str_replace('.php', '', $segments[0]); 
    $firstSegment = ucfirst($segment0); // Controller 이름 유추에 사용
    $candidateController = "app\\Controllers\\{$firstSegment}Controller";
    
    // 1. URL이 Controller 이름과 일치하는 경우 (예: /Users/index)
    if (class_exists($candidateController)) {
        $controllerClass = $candidateController;
        // 다음 세그먼트가 Action 이름이 되며, 없으면 'index'. 여기서도 .php 제거
        $actionName = str_replace('.php', '', $segments[1] ?? 'index'); 
        $params = array_slice($segments, 2); 
    } 
    // 2. Controller 이름이 아닌 Action 이름으로 시작하는 경우 (예: /login, /create, /view)
    else {
        // PostController로 기본 설정하고 segment0을 Action으로 사용
        $controllerClass = 'app\\Controllers\\PostController';
        $actionName = $segment0; // '.php'가 제거된 값 사용
        $params = array_slice($segments, 1);
        
        // User 관련 요청은 UserController로 명시적 매핑
        if ($segment0 === 'login' || $segment0 === 'register' || $segment0 === 'logout' || $segment0 === 'home') {
             $controllerClass = 'app\\Controllers\\UserController';
             
             // HTTP 메서드에 따른 Action 매핑
             if ($segment0 === 'login') {
                $actionName = ($method === 'POST') ? 'login' : 'showLoginForm';
             } elseif ($segment0 === 'register') {
                $actionName = ($method === 'POST') ? 'register' : 'showRegisterForm';
             }
        }
    }
}

// ----------------------------------------------------
// 3. Controller 호출 및 실행 (디버그 코드 유지)
// ----------------------------------------------------

if (!class_exists($controllerClass)) {
    http_response_code(404);
    exit("404 Not Found: 요청 경로를 처리할 수 없습니다. 클래스: {$controllerClass}"); 
}

$controller = new $controllerClass();

if (!method_exists($controller, $actionName)) {
    http_response_code(404);
    exit("404 Not Found: 요청 경로를 처리할 수 없습니다. 메서드: {$controllerClass}::{$actionName}"); 
}

// 메서드 실행
call_user_func_array([$controller, $actionName], $params);

exit;
