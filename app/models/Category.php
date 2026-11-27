<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\BaseModel;

/**
 * Model untuk tabel categories.
 */
final class Category extends BaseModel
{
    private const BASE_SELECT = "SELECT c.id, c.name, c.sort_order, c.created_at, c.updated_at,
               COALESCE(c.subtitle, 'UNGGULAN') AS subtitle,
               COUNT(m.id) AS menu_count
        FROM categories c
        LEFT JOIN menus m ON m.category_id = c.id AND m.deleted_at IS NULL";

    /**
     * Ambil semua kategori beserta jumlah menu aktif (tidak terhapus).
     *
     * @return array<int, array<string, mixed>>
     */
    public static function all(): array
    {
        return self::fetchAll(
            self::BASE_SELECT . ' GROUP BY c.id ORDER BY c.sort_order ASC, c.name ASC'
        );
    }

    /**
     * Cari kategori berdasarkan ID.
     */
    public static function find(int $id): ?array
    {
        return self::fetchOne(
            self::BASE_SELECT . ' WHERE c.id = :id GROUP BY c.id LIMIT 1',
            [':id' => $id]
        );
    }

    /**
     * Cari kategori berdasarkan nama (case-insensitive), opsional mengecualikan ID tertentu.
     */
    public static function findByName(string $name, ?int $excludeId = null): ?array
    {
        $sql    = 'SELECT * FROM categories WHERE LOWER(name) = LOWER(:name)';
        $params = [':name' => trim($name)];

        if ($excludeId !== null) {
            $sql .= ' AND id != :exclude_id';
            $params[':exclude_id'] = $excludeId;
        }

        return self::fetchOne($sql . ' LIMIT 1', $params);
    }

    /**
     * Hitung total seluruh menu yang mengaitkan kategori ini (termasuk soft-deleted, untuk BR-14).
     */
    public static function countMenus(int $id): int
    {
        return (int) self::fetchValue(
            'SELECT COUNT(*) FROM menus WHERE category_id = :id',
            [':id' => $id]
        );
    }

    public static function create(array $data): int
    {
        $pdo  = self::db();
        $stmt = $pdo->prepare(
            'INSERT INTO categories (name, sort_order) VALUES (:name, :sort_order)'
        );
        $stmt->execute([
            ':name'       => trim((string) $data['name']),
            ':sort_order' => (int) ($data['sort_order'] ?? 0),
        ]);

        return (int) $pdo->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $stmt = self::db()->prepare(
            'UPDATE categories SET name = :name, sort_order = :sort_order WHERE id = :id'
        );

        return $stmt->execute([
            ':id'         => $id,
            ':name'       => trim((string) $data['name']),
            ':sort_order' => (int) ($data['sort_order'] ?? 0),
        ]);
    }

    public static function delete(int $id): bool
    {
        $stmt = self::db()->prepare('DELETE FROM categories WHERE id = :id');
        return $stmt->execute([':id' => $id]);
    }
}
