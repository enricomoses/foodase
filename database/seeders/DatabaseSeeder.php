<?php
declare(strict_types=1);

namespace Database\Seeders;

use App\Core\Database;
use PDO;

require_once __DIR__ . '/UserSeeder.php';
require_once __DIR__ . '/CategorySeeder.php';
require_once __DIR__ . '/MenuSeeder.php';

/**
 * Orchestrator utama seluruh seeder database.
 */
final class DatabaseSeeder
{
    /**
     * Jalankan semua seeder dalam satu transaksi database.
     *
     * @return array<string, int> Ringkasan jumlah data yang diproses per entitas.
     */
    public static function run(): array
    {
        return Database::transaction(static function (PDO $pdo): array {
            $users      = UserSeeder::run($pdo);
            $categories = CategorySeeder::run($pdo);
            $menus      = MenuSeeder::run($pdo);

            return [
                'users'      => $users,
                'categories' => $categories,
                'menus'      => $menus,
            ];
        });
    }
}
