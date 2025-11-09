<?php
// /var/www/html/app/Models/FreePhoto.php
declare(strict_types=1);

namespace app\Models;

use PDO;

class FreePhoto
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /** 공개 목록 (간단 페이징 지원) */
    public function getPublicList(int $page = 1, int $perPage = 20): array
    {
        $offset = max(0, ($page - 1) * $perPage);

        $sql = "SELECT free_id, title, description, path_preview, created_at
                  FROM free_photos
                 WHERE visibility = 'public'
                 ORDER BY created_at DESC
                 LIMIT :limit OFFSET :offset";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit',  $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset,  PDO::PARAM_INT);
        $stmt->execute();

        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 총 개수
        $total = (int)$this->pdo->query(
            "SELECT COUNT(*) FROM free_photos WHERE visibility='public'"
        )->fetchColumn();

        return ['items' => $items, 'total' => $total, 'page' => $page, 'perPage' => $perPage];
    }

    /** 공개 단건 조회 */
    public function getPublicById(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            "SELECT free_id, title, description, path_original, path_preview, created_at
               FROM free_photos
              WHERE free_id = :id AND visibility='public'"
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }
}
