<?php
declare(strict_types=1);

namespace Database\Seeders;

use PDO;

/**
 * Seeder akun pengguna default (admin dan kasir).
 */
final class UserSeeder
{
    /**
     * Jalankan seeder akun pengguna secara idempotent.
     *
     * @return int Jumlah baris yang diproses.
     */
    public static function run(PDO $pdo): int
    {
        $users = [
            ['Administrator', 'admin',  'admin12345', 'admin',   1],
            ['Kasir Satu',    'kasir1', 'kasir12345', 'cashier', 1],
        ];

        // INSERT IGNORE agar menjalankan ulang --seed tidak menimpa password custom yang sudah diubah
        $stmt = $pdo->prepare(
            'INSERT IGNORE INTO users (name, username, password_hash, role, is_active)
             VALUES (:name, :username, :password_hash, :role, :is_active)'
        );

        $count = 0;
        foreach ($users as [$name, $username, $password, $role, $isActive]) {
            $stmt->execute([
                ':name'          => $name,
                ':username'      => $username,
                ':password_hash' => password_hash($password, PASSWORD_DEFAULT),
                ':role'          => $role,
                ':is_active'     => $isActive,
            ]);
            $count++;
        }

        return $count;
    }
}
