<?php

namespace app\Core;

use PDO;
use PDOException;

class Database
{
    /** @var ?PDO */
    private static ?PDO $pdo = null;

    /** @var string */
    private string $DB_DSN  = "mysql:host=database-rookiesphoto.crc0gq2e6kq5.ap-northeast-2.rds.amazonaws.com;port=3306;dbname=rookiesphoto;charset=utf8mb4";
    /** @var string */
    private string $DB_USER = "admin";
    /** @var string */
    private string $DB_PASS = "rookiesphoto";

    /** @var array */
    private array $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    // ----------------------------------------------------
    // 싱글톤: DB 연결은 한 번만 생성
    // ----------------------------------------------------
    public function __construct()
    {
        if (self::$pdo === null) {
            try {
                // ✅ 반드시 $this-> 로 접근해야 함
                self::$pdo = new PDO(
                    $this->DB_DSN,
                    $this->DB_USER,
                    $this->DB_PASS,
                    $this->options
                );
            } catch (PDOException $e) {
                http_response_code(500);
                exit("DB 연결 실패: " . $e->getMessage());
            }
        }
    }

    /**
     * PDO 객체 반환
     */
    public function getConnection(): PDO
    {
        return self::$pdo;
    }

    /**
     * 단일 쿼리 실행 (SELECT용)
     */
    public function query(string $query, array $params = []): \PDOStatement
    {
        $stmt = self::$pdo->prepare($query);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * INSERT/UPDATE/DELETE 실행
     */
    public function execute(string $query, array $params = []): bool
    {
        $stmt = self::$pdo->prepare($query);
        return $stmt->execute($params);
    }
}
