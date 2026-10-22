<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

/**
 * Model untuk tabel users.
 */
final class User
{
    /**
     * Cari user berdasarkan username. Mengembalikan null jika tidak ditemukan.
     */
    public static function findByUsername(string $username): ?array
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE username = :username LIMIT 1');
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch();

        return $user ?: null;
    }

    /**
     * Cari user berdasarkan ID. Mengembalikan null jika tidak ditemukan.
     */
    public static function findById(int $id): ?array
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $user = $stmt->fetch();

        return $user ?: null;
    }

    /**
     * Dapatkan daftar user, opsional difilter berdasarkan role.
     */
    public static function list(?string $role = null): array
    {
        $pdo = Database::connection();

        if ($role !== null && $role !== '') {
            $stmt = $pdo->prepare(
                'SELECT id, name, username, role, is_active, last_login_at, created_at, updated_at
                 FROM users
                 WHERE role = :role
                 ORDER BY name ASC'
            );
            $stmt->execute([':role' => $role]);
            return $stmt->fetchAll();
        }

        $stmt = $pdo->prepare(
            'SELECT id, name, username, role, is_active, last_login_at, created_at, updated_at
             FROM users
             ORDER BY name ASC'
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Tambah user baru ke database dan kembalikan ID yang baru dibuat.
     */
    public static function create(array $data): int
    {
        $pdo = Database::connection();

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

    /**
     * Perbarui data user (name, username, role, is_active).
     */
    public static function update(int $id, array $data): bool
    {
        $pdo = Database::connection();
        $fields = [];
        $params = [':id' => $id];

        if (array_key_exists('name', $data)) {
            $fields[] = 'name = :name';
            $params[':name'] = (string) $data['name'];
        }

        if (array_key_exists('username', $data)) {
            $fields[] = 'username = :username';
            $params[':username'] = (string) $data['username'];
        }

        if (array_key_exists('role', $data)) {
            $fields[] = 'role = :role';
            $params[':role'] = (string) $data['role'];
        }

        if (array_key_exists('is_active', $data)) {
            $fields[] = 'is_active = :is_active';
            $params[':is_active'] = (bool) $data['is_active'] ? 1 : 0;
        }

        if (empty($fields)) {
            return false;
        }

        $sql = 'UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = :id';
        $stmt = $pdo->prepare($sql);

        return $stmt->execute($params);
    }

    /**
     * Aktifkan atau nonaktifkan akun user.
     */
    public static function setActive(int $id, bool $isActive): bool
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare('UPDATE users SET is_active = :is_active WHERE id = :id');
        return $stmt->execute([
            ':id'        => $id,
            ':is_active' => $isActive ? 1 : 0,
        ]);
    }

    /**
     * Perbarui password user dengan hash baru.
     */
    public static function updatePassword(int $id, string $newPassword): bool
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare('UPDATE users SET password_hash = :hash WHERE id = :id');
        return $stmt->execute([
            ':id'   => $id,
            ':hash' => password_hash($newPassword, PASSWORD_DEFAULT),
        ]);
    }

    /**
     * Perbarui waktu login terakhir (last_login_at) ke waktu sekarang.
     */
    public static function touchLastLogin(int $id): bool
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare('UPDATE users SET last_login_at = NOW() WHERE id = :id');
        return $stmt->execute([':id' => $id]);
    }
}
