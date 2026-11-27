<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\BaseModel;

/**
 * Model untuk tabel users.
 */
final class User extends BaseModel
{
    public static function find(int $id): ?array
    {
        return self::fetchOne('SELECT * FROM users WHERE id = :id LIMIT 1', [':id' => $id]);
    }

    public static function findByUsername(string $username): ?array
    {
        return self::fetchOne('SELECT * FROM users WHERE username = :username LIMIT 1', [':username' => $username]);
    }

    /**
     * Daftar user, opsional difilter berdasarkan role.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function list(?string $role = null): array
    {
        $sql    = 'SELECT id, name, username, role, is_active, last_login_at, created_at, updated_at FROM users';
        $params = [];

        if ($role !== null && $role !== '') {
            $sql .= ' WHERE role = :role';
            $params[':role'] = $role;
        }

        return self::fetchAll($sql . ' ORDER BY name ASC', $params);
    }

    public static function create(array $data): int
    {
        $pdo  = self::db();
        $hash = $data['password_hash'] ?? password_hash((string) ($data['password'] ?? ''), PASSWORD_DEFAULT);

        $stmt = $pdo->prepare(
            'INSERT INTO users (name, username, password_hash, role, is_active)
             VALUES (:name, :username, :password_hash, :role, :is_active)'
        );
        $stmt->execute([
            ':name'          => (string) $data['name'],
            ':username'      => (string) $data['username'],
            ':password_hash' => $hash,
            ':role'          => (string) ($data['role'] ?? 'cashier'),
            ':is_active'     => isset($data['is_active']) ? ((bool) $data['is_active'] ? 1 : 0) : 1,
        ]);

        return (int) $pdo->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $fields = [];
        $params = [':id' => $id];

        foreach (['name', 'username', 'role'] as $col) {
            if (array_key_exists($col, $data)) {
                $fields[]        = "{$col} = :{$col}";
                $params[":{$col}"] = (string) $data[$col];
            }
        }

        if (array_key_exists('is_active', $data)) {
            $fields[]             = 'is_active = :is_active';
            $params[':is_active'] = (bool) $data['is_active'] ? 1 : 0;
        }

        if (empty($fields)) {
            return false;
        }

        $stmt = self::db()->prepare('UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = :id');
        return $stmt->execute($params);
    }

    public static function setActive(int $id, bool $isActive): bool
    {
        $stmt = self::db()->prepare('UPDATE users SET is_active = :is_active WHERE id = :id');
        return $stmt->execute([
            ':id'        => $id,
            ':is_active' => $isActive ? 1 : 0,
        ]);
    }

    public static function updatePassword(int $id, string $newPassword): bool
    {
        $stmt = self::db()->prepare('UPDATE users SET password_hash = :hash WHERE id = :id');
        return $stmt->execute([
            ':id'   => $id,
            ':hash' => password_hash($newPassword, PASSWORD_DEFAULT),
        ]);
    }

    public static function touchLastLogin(int $id): bool
    {
        $stmt = self::db()->prepare('UPDATE users SET last_login_at = CURRENT_TIMESTAMP WHERE id = :id');
        return $stmt->execute([':id' => $id]);
    }

    public static function updateSessionId(int $id, ?string $sessionId): bool
    {
        $stmt = self::db()->prepare('UPDATE users SET session_id = :sid WHERE id = :id');
        return $stmt->execute([
            ':id'  => $id,
            ':sid' => $sessionId,
        ]);
    }
}
