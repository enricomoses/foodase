<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\BaseModel;

/**
 * Model untuk tabel menus (mendukung soft-delete via deleted_at).
 */
final class Menu extends BaseModel
{
    private const BASE_SELECT = "SELECT m.id, m.category_id, m.name, m.description, m.price,
               m.image, m.is_available, m.deleted_at, m.created_at, m.updated_at,
               COALESCE(m.badge, '') AS badge,
               COALESCE(m.serving_tag, 'Iced') AS serving_tag,
               c.name AS category_name,
               COALESCE(c.subtitle, 'UNGGULAN') AS category_subtitle
        FROM menus m
        LEFT JOIN categories c ON c.id = m.category_id";

    /**
     * Ambil daftar menu dengan filter + paginasi.
     *
     * @return array{data: array<int, array<string, mixed>>, total: int, page: int, per_page: int, pages: int}
     */
    public static function list(array $filters = []): array
    {
        $q              = trim((string) ($filters['q'] ?? ''));
        $categoryId     = isset($filters['category_id']) && $filters['category_id'] !== ''
            ? (int) $filters['category_id'] : null;
        $includeUnavail = (bool) ($filters['include_unavailable'] ?? false);
        $ids            = isset($filters['ids']) && is_array($filters['ids']) ? $filters['ids'] : [];
        $page           = (int) ($filters['page'] ?? 1);
        $perPage        = (int) ($filters['per_page'] ?? 20);

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
            foreach (array_values($ids) as $i => $id) {
                $key            = ':id_' . $i;
                $placeholders[] = $key;
                $params[$key]   = (int) $id;
            }
            $where[] = 'm.id IN (' . implode(',', $placeholders) . ')';
        }

        $whereClause = 'WHERE ' . implode(' AND ', $where);

        return self::paginate(
            self::BASE_SELECT . " {$whereClause} ORDER BY c.sort_order ASC, c.name ASC, m.name ASC",
            "SELECT COUNT(*) FROM menus m {$whereClause}",
            $params,
            $page,
            $perPage
        );
    }

    /**
     * Cari satu menu berdasarkan ID.
     */
    public static function find(int $id, bool $includeSoftDeleted = false): ?array
    {
        $sql = self::BASE_SELECT . ' WHERE m.id = :id';
        if (!$includeSoftDeleted) {
            $sql .= ' AND m.deleted_at IS NULL';
        }

        return self::fetchOne($sql . ' LIMIT 1', [':id' => $id]);
    }

    /**
     * Ambil banyak menu aktif sekaligus berdasarkan daftar ID (menghindari N+1 query).
     *
     * @param int[] $ids
     * @return array<int, array<string, mixed>> Diindeks berdasarkan menu.id
     */
    public static function findByIds(array $ids): array
    {
        $cleanIds = array_values(array_unique(array_filter(array_map('intval', $ids), static fn(int $id): bool => $id > 0)));
        if (empty($cleanIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($cleanIds), '?'));
        $rows = self::fetchAll(
            self::BASE_SELECT . " WHERE m.deleted_at IS NULL AND m.id IN ({$placeholders})",
            $cleanIds
        );

        $indexed = [];
        foreach ($rows as $row) {
            $indexed[(int) $row['id']] = $row;
        }
        return $indexed;
    }

    public static function create(array $data): int
    {
        $pdo  = self::db();
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

    public static function update(int $id, array $data): bool
    {
        $sets = [
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

        $stmt = self::db()->prepare(
            'UPDATE menus SET ' . implode(', ', $sets) . ' WHERE id = :id AND deleted_at IS NULL'
        );

        return $stmt->execute($params);
    }

    public static function softDelete(int $id): bool
    {
        $stmt = self::db()->prepare(
            'UPDATE menus SET deleted_at = NOW() WHERE id = :id AND deleted_at IS NULL'
        );
        $stmt->execute([':id' => $id]);

        return $stmt->rowCount() > 0;
    }

    public static function toggleAvailability(int $id): ?bool
    {
        $pdo  = self::db();
        $stmt = $pdo->prepare(
            'UPDATE menus SET is_available = 1 - is_available WHERE id = :id AND deleted_at IS NULL'
        );
        $stmt->execute([':id' => $id]);

        if ($stmt->rowCount() === 0) {
            return null;
        }

        $val = self::fetchValue('SELECT is_available FROM menus WHERE id = :id', [':id' => $id], $pdo);
        return $val !== false ? (bool) $val : null;
    }

    public static function hasOrders(int $id): bool
    {
        return (int) self::fetchValue(
            'SELECT COUNT(*) FROM order_details WHERE menu_id = :id LIMIT 1',
            [':id' => $id]
        ) > 0;
    }
}
