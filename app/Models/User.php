<?php
// app/Models/User.php

namespace App\Models;

use App\Core\Database;
use Exception;

class User
{
    private $db;
    private $table = 'users';

    public function __construct()
    {
        $this->db = new Database();
    }

    // -------------------------
    // 1. 사용자 조회 및 로그인 (READ)
    // -------------------------

    /**
     * login_id를 사용하여 사용자 정보 (비밀번호 제외)를 가져옵니다.
     */
    public function findUserByLoginId(string $loginId): ?array
    {
        $query = "SELECT user_id, login_id, email, name, role, phone_num, id_time, points 
                  FROM " . $this->table . " 
                  WHERE login_id = :login_id";
        
        $stmt = $this->db->query($query, ['login_id' => $loginId]);
        return $stmt->fetch() ?: null;
    }

    /**
     * 로그인 시 ID와 해시된 PW를 비교하기 위해 사용자 정보를 가져옵니다.
     */
    public function getUserForLogin(string $loginId): ?array
    {
        // password 필드를 포함하여 조회
        $query = "SELECT user_id, login_id, password, role FROM " . $this->table . " WHERE login_id = :login_id";
        $stmt = $this->db->query($query, ['login_id' => $loginId]);
        return $stmt->fetch() ?: null;
    }

    // -------------------------
    // 2. 새 사용자 생성 (CREATE)
    // -------------------------

    /**
     * 새로운 사용자를 DB에 등록합니다.
     * [수정] email과 phoneNum 필드를 추가했습니다.
     */
    public function createUser(string $loginId, string $password, string $name, string $email, string $phoneNum): bool
    {
        // **비즈니스 로직**: 비밀번호 해싱 (Model의 역할)
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // passq, passas, id_time, role, points는 기본값이 있다고 가정
        $query = "INSERT INTO " . $this->table . " 
                  (login_id, email, password, phone_num, name, role, points)
                  VALUES (:login_id, :email, :password, :phone_num, :name, :role, :points)";
        
        $params = [
            'login_id'  => $loginId,
            'email'     => $email,
            'password'  => $hashed_password,
            'phone_num' => $phoneNum,
            'name'      => $name,
            'role'      => 'general', // 기본 역할
            'points'    => 100        // 초기 포인트
        ];

        return $this->db->execute($query, $params);
    }
    
    // -------------------------
    // 3. 포인트 업데이트 (UPDATE)
    // -------------------------
    
    /**
     * 특정 사용자의 포인트를 업데이트합니다.
     */
    public function updatePoints(string $userId, int $amount): bool
    {
        // user_id는 PK이므로 user_id를 기준으로 업데이트하는 것이 안전합니다.
        $query = "UPDATE " . $this->table . " SET points = points + :amount WHERE user_id = :user_id";
        
        $params = [
            'amount' => $amount,
            'user_id' => $userId // 세션에서 가져온 user_id (PK) 사용
        ];
        
        return $this->db->execute($query, $params);
    }
}
