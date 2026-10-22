<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

/**
 * Model untuk tabel menus.
 * Soft delete: baris dengan deleted_at != NULL tidak ditampilkan.
 */
final class Menu
{
    /**
     * Ambil daftar menu dengan filter + paginasi.
     *
     * @param array $filters
     * @return array{data: array, total: int, page: int, per_page: int, pages: int}
     */
    public static function list(array $filters = []): array
    {
        $pdo = Database::connection();

        $q              = trim((string) ($filters['q'] ?? ''));
        $categoryId     = isset($filters['category_id']) && $filters['category_id'] !== ''
                            ? (int) $filters['category_id'] : null;
        $includeUnavail = (bool) ($filters['include_unavailable'] ?? false);
        $ids            = isset($filters['ids']) && is_array($filters['ids']) ? $filters['ids'] : [];
        $page           = max(1, (int) ($filters['page'] ?? 1));
        $perPage        = max(1, min(100, (int) ($filters['per_page'] ?? 20)));

        $where  = ['m.deleted_at IS NULL'];
        $params = [];

        if (!$includeUnavail) {
            $where[] = 'm.is_available = 1';
        }

        if ($q !== '') {
            $where[]       = '(m.name LIKE :q OR m.description LIKE :q2)';
            $params[':q']  = '%' . $q . '%';
            $params[':q2'] = '%' . $q . '%';
        }

        if ($categoryId !== null) {
            $where[]           = 'm.category_id = :cat_id';
            $params[':cat_id'] = $categoryId;
        }

        if (!empty($ids)) {
            $placeholders = [];
            foreach ($ids as $i => $id) {
                $key            = ':id_' . $i;
                $placeholders[] = $key;
                $params[$key]   = (int) $id;
            }
            $where[] = 'm.id IN (' . implode(',', $placeholders) . ')';
        }

        $whereClause = 'WHERE ' . implode(' AND ', $where);

        // Hitung total
        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM menus m {$whereClause}");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        // Ambil data
        $offset  = ($page - 1) * $perPage;
        $dataSql = "SELECT m.id, m.category_id, m.name, m.description, m.price,
                           m.image, m.is_available, m.deleted_at, m.created_at, m.updated_at,
                           c.name AS category_name
                    FROM menus m
                    LEFT JOIN categories c ON c.id = m.category_id
                    {$whereClause}
                    ORDER BY c.sort_order ASC, c.name ASC, m.name ASC
                    LIMIT :limit OFFSET :offset";

        $dataStmt = $pdo->prepare($dataSql);
        foreach ($params as $k => $v) {
            $dataStmt->bindValue($k, $v);
        }
        $dataStmt->bindValue(':limit',  $perPage, PDO::PARAM_INT);
        $dataStmt->bindValue(':offset', $offset,  PDO::PARAM_INT);
        $dataStmt->execute();
        $rows = $dataStmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'data'     => $rows,
            'total'    => $total,
            'page'     => $page,
            'per_page' => $perPage,
            'pages'    => $total > 0 ? (int) ceil($total / $perPage) : 1,
        ];
    }

    /**
     * Cari menu berdasarkan ID.
     */
    public static function find(int $id, bool $includeSoftDeleted = false): ?array
    {
        $pdo = Database::connection();
        $sql = 'SELECT m.id, m.category_id, m.name, m.description, m.price,
                       m.image, m.is_available, m.deleted_at, m.created_at, m.updated_at,
                       c.name AS category_name
                FROM menus m
                LEFT JOIN categories c ON c.id = m.category_id
                WHERE m.id = :id';

        if (!$includeSoftDeleted) {
            $sql .= ' AND m.deleted_at IS NULL';
        }

        $sql .= ' LIMIT 1';

        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ?: null;
    }

    /**
     * Buat menu baru dan kembalikan ID yang dibuat.
     */
    public static function create(array $data): int
    {
        $pdo  = Database::connection();
        $stmt = $pdo->prepare(
            'INSERT INTO menus (category_id, name, description, price, image, is_available)
             VALUES (:category_id, :name, :description, :price, :image, :is_available)'
        );

        $stmt->execute([
            ':category_id'  => (int) $data['category_id'],
            ':name'         => trim((string) $data['name']),
            ':description'  => isset($data['description']) && $data['description'] !== ''
                                    ? trim((string) $data['description']) : null,
            ':price'        => (int) $data['price'],
            ':image'        => $data['image'] ?? null,
            ':is_available' => isset($data['is_available']) ? (int) $data['is_available'] : 1,
        ]);

        return (int) $pdo->lastInsertId();
    }

    /**
     * Perbarui data menu.
     */
    public static function update(int $id, array $data): bool
    {
        $pdo    = Database::connection();
        $sets   = [
            'category_id = :category_id',
            'name = :name',
            'description = :description',
            'price = :price',
            'is_available = :is_available',
        ];
        $params = [
            ':id'           => $id,
            ':category_id'  => (int) $data['category_id'],
            ':name'         => trim((string) $data['name']),
            ':description'  => isset($data['description']) && $data['description'] !== ''
                                    ? trim((string) $data['description']) : null,
            ':price'        => (int) $data['price'],
            ':is_available' => isset($data['is_available']) ? (int) $data['is_available'] : 1,
        ];

        if (array_key_exists('image', $data)) {
            $sets[]           = 'image = :image';
            $params[':image'] = $data['image'];
        }

        $stmt = $pdo->prepare(
            'UPDATE menus SET ' . implode(', ', $sets) . ' WHERE id = :id AND deleted_at IS NULL'
        );

        return $stmt->execute($params);
    }

    /**
     * Soft-delete menu (BR-13): set deleted_at = NOW().
     */
    public static function softDelete(int $id): bool
    {
        $pdo  = Database::connection();
        $stmt = $pdo->prepare(
            'UPDATE menus SET deleted_at = NOW() WHERE id = :id AND deleted_at IS NULL'
        );
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Toggle is_available untuk menu tertentu.
     * Mengembalikan nilai is_available baru, atau null jika tidak ditemukan.
     */
    public static function toggleAvailability(int $id): ?bool
    {
        $pdo  = Database::connection();
        $stmt = $pdo->prepare(
            'UPDATE menus SET is_available = 1 - is_available WHERE id = :id AND deleted_at IS NULL'
        );
        $stmt->execute([':id' => $id]);

        if ($stmt->rowCount() === 0) {
            return null;
        }

        $check = $pdo->prepare('SELECT is_available FROM menus WHERE id = :id');
        $check->execute([':id' => $id]);
        $row = $check->fetch(PDO::FETCH_ASSOC);

        return $row !== false ? (bool) $row['is_available'] : null;
    }

    /**
     * Cek apakah menu pernah dipesan (ada di order_details) — untuk BR-13.
     */
    public static function hasOrders(int $id): bool
    {
        $pdo  = Database::connection();
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM order_details WHERE menu_id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        return (int) $stmt->fetchColumn() > 0;
    }
}
