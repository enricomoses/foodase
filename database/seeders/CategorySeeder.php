<?php
declare(strict_types=1);

namespace Database\Seeders;

use PDO;

/**
 * Seeder kategori menu kafe (Kopi Nusa).
 */
final class CategorySeeder
{
    /**
     * Jalankan seeder kategori secara idempotent.
     *
     * @return int Jumlah kategori yang diproses.
     */
    public static function run(PDO $pdo): int
    {
        $categories = [
            ['name' => 'Kopi Susu',      'sort_order' => 1, 'subtitle' => 'UNGGULAN'],
            ['name' => 'Cold Brew',      'sort_order' => 2, 'subtitle' => 'SPESIAL'],
            ['name' => 'Smooth Series',  'sort_order' => 3, 'subtitle' => 'SIGNATURE'],
            ['name' => 'Non-Kopi',       'sort_order' => 4, 'subtitle' => 'FAVORITE'],
            ['name' => 'Makanan',        'sort_order' => 5, 'subtitle' => 'TERSEDIA'],
            ['name' => 'Camilan & Roti', 'sort_order' => 6, 'subtitle' => 'CAMILAN'],
        ];

        $stmt = $pdo->prepare(
            'INSERT INTO categories (name, sort_order, subtitle)
             VALUES (:name, :sort_order, :subtitle)
             ON DUPLICATE KEY UPDATE
               sort_order = VALUES(sort_order),
               subtitle   = VALUES(subtitle)'
        );

        $count = 0;
        foreach ($categories as $cat) {
            $stmt->execute([
                ':name'       => $cat['name'],
                ':sort_order' => $cat['sort_order'],
                ':subtitle'   => $cat['subtitle'],
            ]);
            $count++;
        }

        return $count;
    }
}
