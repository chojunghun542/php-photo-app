<?php
// app/Controllers/UserController.php

namespace app\Controllers;

use app\Models\User;
use app\Core\View; // View 클래스를 네임스페이스로 가져와 사용

class UserController
{
    private $userModel;

    public function __construct()
    {
        // User 모델 객체 생성. User 모델 내부에서 Database 연결을 처리합니다.
        $this->userModel = new User();
    }
    
    // -------------------------
    // 기본 페이지 (루트 요청 시 PostController의 index가 호출되므로, 
    // 여기서는 '/home' 요청 시에만 사용)
    // -------------------------
    public function index()
    {
        // 세션에 로그인 정보가 있다면 사용자 login_id를 가져와서 View에 전달합니다.
        $isLoggedIn = isset($_SESSION['login_id']);
        $loginId = $isLoggedIn ? $_SESSION['login_id'] : '게스트';
        
        $data = [
            'pageTitle' => '홈페이지',
            'loginId' => $loginId, // 로그인 ID로 변경
            'isLoggedIn' => $isLoggedIn
        ];
        // resource/views/home.php 뷰를 로드
        View::render('home', $data); 
    }

    // -------------------------
    // 1. 회원가입 페이지 표시 (GET /register)
    // -------------------------

    public function showRegisterForm()
    {
        // 이미 로그인한 경우 홈으로 리다이렉트
        if (isset($_SESSION['login_id'])) {
            header('Location: /');
            exit;
        }

        $data = ['pageTitle' => '회원가입'];
        // resource/views/user/register.php 뷰를 로드
        View::render('user/register', $data); 
    }

    // -------------------------
    // 2. 실제 회원가입 처리 (POST /register)
    // -------------------------

    public function register()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /register');
            exit;
        }

        // DB 칼럼명에 맞춰 login_id와 password로 변수명 변경
        $loginId = $_POST['login_id'] ?? '';
        $password = $_POST['password'] ?? '';
        $name = $_POST['name'] ?? '';
        // 새로운 입력 필드 추가 (DB 스키마에 포함된 email, phone_num 가정)
        $email = $_POST['email'] ?? ''; 
        $phoneNum = $_POST['phone_num'] ?? '';

        if (empty($loginId) || empty($password) || empty($name)) {
            // 필드 누락 시 View를 다시 렌더링하고 오류 메시지 표시
            View::render('user/register', ['error' => '필수 항목(ID, 비밀번호, 이름)을 입력해야 합니다.']);
            return;
        }

        // ID 중복 검사 (Model 호출)
        if ($this->userModel->findUserByLoginId($loginId)) {
            View::render('user/register', ['error' => '이미 존재하는 ID입니다.']);
            return;
        }
        
        // 사용자 생성 (Model에 처리를 위임)
        // User 모델에서 비밀번호 해싱 처리
        $success = $this->userModel->createUser($loginId, $password, $name, $email, $phoneNum);

        // 결과에 따른 응답
        if ($success) {
            header('Location: /login?message=' . urlencode('회원가입이 완료되었습니다! 로그인하세요.'));
            exit;
        } else {
            View::render('user/register', ['error' => '회원가입 중 DB 오류가 발생했습니다.']);
        }
    }
    
    // -------------------------
    // 3. 로그인 페이지 표시 (GET /login)
    // -------------------------
    
    public function showLoginForm()
    {
        // 이미 로그인한 경우 홈으로 리다이렉트
        if (isset($_SESSION['login_id'])) {
            header('Location: /');
            exit;
        }

        // GET 쿼리 파라미터로 넘어온 메시지나 오류를 받아 View에 전달
        $message = $_GET['message'] ?? null;
        $error = $_GET['error'] ?? null;
        
        $data = [
            'pageTitle' => '로그인',
            'message' => $message,
            'error' => $error
        ];
        // resource/views/user/login.php 뷰를 로드
        View::render('user/login', $data); 
    }

    // -------------------------
    // 4. 실제 로그인 처리 (POST /login)
    // -------------------------
    
    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /login');
            exit;
        }
        
        // 입력 필드를 login_id, password로 변경
        $loginId = $_POST['login_id'] ?? '';
        $password = $_POST['password'] ?? '';

        // 1. Model을 통해 사용자 정보 조회 (해시된 비밀번호 포함)
        $user = $this->userModel->getUserForLogin($loginId);

        if (!$user) {
            // ID가 없는 경우
            View::render('user/login', ['error' => '존재하지 않는 ID입니다.']);
            return;
        }

        // 2. 비밀번호 해시 값과 사용자가 입력한 비밀번호 비교 (DB의 password 칼럼 사용)
        if (password_verify($password, $user['password'])) {
            
            // 3. 로그인 성공: 세션에 사용자 정보 저장
            $_SESSION['user_id'] = $user['user_id']; // PK를 저장 (DB에서 참조할 때 사용)
            $_SESSION['login_id'] = $user['login_id']; // 화면에 표시할 ID 저장
            $_SESSION['user_role'] = $user['role'] ?? 'general'; // 기본 역할 지정
            
            // 홈으로 리다이렉트
            header('Location: /');
            exit;

        } else {
            // 비밀번호 불일치
            View::render('user/login', ['error' => '비밀번호가 일치하지 않습니다.']);
        }
    }
    
    // -------------------------
    // 5. 로그아웃 기능 (GET /logout)
    // -------------------------
    
    public function logout()
    {
        // 세션 파괴
        session_unset();
        session_destroy();
        
        // 홈으로 리다이렉트 (로그아웃 성공 메시지 포함)
        header('Location: /?message=' . urlencode('로그아웃되었습니다.'));
        exit;
    }
}
