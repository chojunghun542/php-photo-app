<?php
// app/Models/Post.php (DB 스키마 반영 전체 코드)

namespace App\Models;

use App\Core\Database;

class Post
{
    private $db;

    public function __construct()
    {
        // Database 객체는 애플리케이션당 한 번만 연결되도록 내부에서 싱글톤 처리됨.
        $this->db = new Database();
    }

    /**
     * 모든 게시글 목록을 검색 조건에 따라 조회합니다. (UNION ALL 쿼리)
     * - free_photos, paid_photos, trade_posts를 통합 조회합니다.
     * @param string $query 검색어
     * @return array 게시글 배열
     */
    public function findAllPosts(?string $query = null): array
    {
        $base = <<<SQL
        SELECT id, title, body, image_path, created_at, kind
        FROM (
            -- 1. 무료 사진 (kind='free')
            SELECT 
                fp.free_id AS id, 
                fp.title, 
                fp.description AS body, 
                fp.path_preview AS image_path, 
                fp.created_at, 
                'free' AS kind 
            FROM free_photos fp
            WHERE fp.visibility = 'public' -- 공개된 사진만 표시

            UNION ALL

            -- 2. 유료 사진 (kind='paid')
            SELECT 
                pp.paid_id AS id, 
                pp.title, 
                pp.description AS body, 
                pp.path_preview AS image_path, 
                pp.created_at, 
                'paid' AS kind 
            FROM paid_photos pp
            WHERE pp.status = 'available' -- 판매 가능한 상태의 사진만 표시

            UNION ALL

            -- 3. 거래용 게시글 (kind='trade')
            SELECT 
                tp.post_id AS id, 
                tp.title, 
                tp.body, 
                pp2.path_preview AS image_path, -- 연결된 유료 사진의 미리보기 사용
                tp.created_at, 
                'trade' AS kind 
            FROM trade_posts tp
            LEFT JOIN paid_photos pp2 ON pp2.paid_id = tp.paid_photo_id
            WHERE tp.status = 'active' -- 활성화된 게시글만 표시
        ) AS posts_union
        SQL;
        
        $sql = $base;
        $params = [];
        
        if ($query) {
            $sql .= ' WHERE posts_union.title LIKE :q_title OR posts_union.body LIKE :q_body';
            $params[':q_title'] = '%' . $query . '%';
            $params[':q_body']  = '%' . $query . '%';
        }
        
        $sql .= ' ORDER BY created_at DESC, id DESC';
        
        $stmt = $this->db->query($sql, $params);
        return $stmt->fetchAll();
    }
    
    // 이 외에 게시글 상세 조회 및 등록/수정 메서드가 추가되어야 합니다.

    // 예시: 게시글 상세 조회 메서드 (곧 Controller에서 사용될 예정)
    /**
     * 특정 게시글 (무료/유료/거래) 상세 정보를 조회합니다.
     * @param string $kind 게시글 종류 ('free', 'paid', 'trade')
     * @param int $id 게시글 ID
     * @return array|null
     */
    public function findPostById(string $kind, int $id): ?array 
    {
        // 실제 구현 시, kind에 따라 적절한 테이블에서 상세 정보를 조회하는 
        // 복잡한 쿼리가 필요합니다.
        return null;
    }
}
