<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

/**
 * Model untuk tabel categories.
 */
final class Category
{
    /**
     * Ambil semua kategori diurutkan berdasarkan sort_order ASC, name ASC,
     * beserta jumlah menu yang terkait.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function all(): array
    {
        $pdo = Database::connection();
        $sql = 'SELECT c.id, c.name, c.sort_order, c.created_at, c.updated_at,
                       COUNT(m.id) AS menu_count
                FROM categories c
                LEFT JOIN menus m ON m.category_id = c.id
                GROUP BY c.id
                ORDER BY c.sort_order ASC, c.name ASC';

        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Cari kategori berdasarkan ID.
     */
    public static function find(int $id): ?array
    {
        $pdo = Database::connection();
        $sql = 'SELECT c.id, c.name, c.sort_order, c.created_at, c.updated_at,
                       COUNT(m.id) AS menu_count
                FROM categories c
                LEFT JOIN menus m ON m.category_id = c.id
                WHERE c.id = :id
                GROUP BY c.id
                LIMIT 1';

        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ?: null;
    }

    /**
     * Cari kategori berdasarkan nama (case-insensitive), opsional mengecualikan ID tertentu.
     */
    public static function findByName(string $name, ?int $excludeId = null): ?array
    {
        $pdo = Database::connection();
        $sql = 'SELECT * FROM categories WHERE LOWER(name) = LOWER(:name)';
        $params = [':name' => trim($name)];

        if ($excludeId !== null) {
            $sql .= ' AND id != :exclude_id';
            $params[':exclude_id'] = $excludeId;
        }

        $sql .= ' LIMIT 1';

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ?: null;
    }

    /**
     * Hitung total menu yang mengaitkan kategori ini (aktif maupun terhapus).
     */
    public static function countMenus(int $id): int
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM menus WHERE category_id = :id');
        $stmt->execute([':id' => $id]);

        return (int) $stmt->fetchColumn();
    }

    /**
     * Buat kategori baru dan kembalikan ID yang dibuat.
     */
    public static function create(array $data): int
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'INSERT INTO categories (name, sort_order)
             VALUES (:name, :sort_order)'
        );

        $stmt->execute([
            ':name'       => trim((string) $data['name']),
            ':sort_order' => (int) ($data['sort_order'] ?? 0),
        ]);

        return (int) $pdo->lastInsertId();
    }

    /**
     * Perbarui data kategori.
     */
    public static function update(int $id, array $data): bool
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'UPDATE categories
             SET name = :name, sort_order = :sort_order
             WHERE id = :id'
        );

        return $stmt->execute([
            ':id'         => $id,
            ':name'       => trim((string) $data['name']),
            ':sort_order' => (int) ($data['sort_order'] ?? 0),
        ]);
    }

    /**
     * Hapus kategori berdasarkan ID.
     */
    public static function delete(int $id): bool
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare('DELETE FROM categories WHERE id = :id');
        return $stmt->execute([':id' => $id]);
    }
}
