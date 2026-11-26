<?php
declare(strict_types=1);

namespace Database\Seeders;

use PDO;
use RuntimeException;

/**
 * Seeder katalog menu kafe (45 item Kopi Nusa dengan badge & serving_tag).
 */
final class MenuSeeder
{
    /**
     * Jalankan seeder menu secara idempotent.
     *
     * @return int Jumlah menu yang diproses.
     */
    public static function run(PDO $pdo): int
    {
        $categoryMap = self::loadCategoryMap($pdo);
        $menus       = self::menuCatalog();

        $findStmt = $pdo->prepare(
            'SELECT id FROM menus WHERE name = :name AND deleted_at IS NULL LIMIT 1'
        );

        $insertStmt = $pdo->prepare(
            'INSERT INTO menus (category_id, name, description, price, image, badge, serving_tag, is_available)
             VALUES (:category_id, :name, :description, :price, :image, :badge, :serving_tag, :is_available)'
        );

        $updateStmt = $pdo->prepare(
            'UPDATE menus
             SET category_id  = :category_id,
                 description  = :description,
                 price        = :price,
                 badge        = :badge,
                 serving_tag  = :serving_tag,
                 is_available = :is_available
             WHERE id = :id'
        );

        $count = 0;
        foreach ($menus as $item) {
            $catName = $item['category'];
            if (!isset($categoryMap[$catName])) {
                throw new RuntimeException("Kategori '{$catName}' tidak ditemukan saat seeding menu.");
            }

            $categoryId = $categoryMap[$catName];
            $findStmt->execute([':name' => $item['name']]);
            $existingId = $findStmt->fetchColumn();

            if ($existingId !== false) {
                $updateStmt->execute([
                    ':id'           => (int) $existingId,
                    ':category_id'  => $categoryId,
                    ':description'  => $item['description'],
                    ':price'        => $item['price'],
                    ':badge'        => $item['badge'],
                    ':serving_tag'  => $item['serving_tag'],
                    ':is_available' => $item['is_available'],
                ]);
            } else {
                $insertStmt->execute([
                    ':category_id'  => $categoryId,
                    ':name'         => $item['name'],
                    ':description'  => $item['description'],
                    ':price'        => $item['price'],
                    ':image'        => null,
                    ':badge'        => $item['badge'],
                    ':serving_tag'  => $item['serving_tag'],
                    ':is_available' => $item['is_available'],
                ]);
            }

            $count++;
        }

        return $count;
    }

    /**
     * Ambil peta nama kategori => ID dari database.
     *
     * @return array<string, int>
     */
    private static function loadCategoryMap(PDO $pdo): array
    {
        $rows = $pdo->query('SELECT id, name FROM categories')->fetchAll(PDO::FETCH_ASSOC);
        $map  = [];

        foreach ($rows as $row) {
            $map[(string) $row['name']] = (int) $row['id'];
        }

        return $map;
    }

    /**
     * Daftar katalog menu awal Kopi Nusa.
     *
     * @return array<int, array{category: string, name: string, description: string, price: int, badge: ?string, serving_tag: string, is_available: int}>
     */
    private static function menuCatalog(): array
    {
        return [
            // ---- KOPI SUSU ----
            [
                'category'     => 'Kopi Susu',
                'name'         => 'Es Kopi Susu Regular',
                'description'  => 'Espresso Robusta dengan gula aren asli, creamy dan manis alami.',
                'price'        => 22000,
                'badge'        => 'Best Seller',
                'serving_tag'  => 'Iced',
                'is_available' => 1,
            ],
            [
                'category'     => 'Kopi Susu',
                'name'         => 'Es Kopi Susu Premium',
                'description'  => 'Espresso Arabica single origin dengan gula aren pilihan, lebih fruity & complex.',
                'price'        => 25000,
                'badge'        => 'Best Seller',
                'serving_tag'  => 'Iced',
                'is_available' => 1,
            ],
            [
                'category'     => 'Kopi Susu',
                'name'         => 'Premium Gula Jawa',
                'description'  => 'Espresso Arabica dengan gula jawa asli, aroma karamel khas nusantara.',
                'price'        => 25000,
                'badge'        => 'Recommended',
                'serving_tag'  => 'Iced',
                'is_available' => 1,
            ],
            [
                'category'     => 'Kopi Susu',
                'name'         => 'Kopi Susu Butterscotch',
                'description'  => 'Espresso lembut dengan butterscotch caramel yang manis legit.',
                'price'        => 29000,
                'badge'        => null,
                'serving_tag'  => 'Iced',
                'is_available' => 1,
            ],
            [
                'category'     => 'Kopi Susu',
                'name'         => 'Americano',
                'description'  => 'Espresso panjang on the rock, pahit seimbang dengan after-taste yang bersih.',
                'price'        => 29500,
                'badge'        => null,
                'serving_tag'  => 'Hot',
                'is_available' => 1,
            ],
            [
                'category'     => 'Kopi Susu',
                'name'         => 'Liquid Jefferson',
                'description'  => 'Espresso on the rock dengan palm sugar & secret recipe kami.',
                'price'        => 33000,
                'badge'        => null,
                'serving_tag'  => 'Iced',
                'is_available' => 1,
            ],
            [
                'category'     => 'Kopi Susu',
                'name'         => 'Latte',
                'description'  => 'Espresso lembut dengan susu steamed micro-foam pilihan.',
                'price'        => 32000,
                'badge'        => null,
                'serving_tag'  => 'Hot',
                'is_available' => 1,
            ],
            [
                'category'     => 'Kopi Susu',
                'name'         => 'Cappuccino',
                'description'  => 'Espresso dengan susu dan foam tebal, klasik dan memuaskan.',
                'price'        => 30000,
                'badge'        => null,
                'serving_tag'  => 'Hot',
                'is_available' => 1,
            ],
            [
                'category'     => 'Kopi Susu',
                'name'         => 'Vietnam Drip',
                'description'  => 'Kopi tetes gaya Vietnam perlahan dengan susu kental manis di bawahnya.',
                'price'        => 28000,
                'badge'        => null,
                'serving_tag'  => 'Iced',
                'is_available' => 1,
            ],

            // ---- COLD BREW ----
            [
                'category'     => 'Cold Brew',
                'name'         => 'Classic Cold Brew',
                'description'  => 'Kopi hitam direndam 12 jam dalam air dingin, smooth dan tidak pahit sama sekali.',
                'price'        => 32000,
                'badge'        => 'Best Seller',
                'serving_tag'  => 'Iced',
                'is_available' => 1,
            ],
            [
                'category'     => 'Cold Brew',
                'name'         => 'Cold Brew Milk',
                'description'  => 'Cold brew dicampur susu segar, lembut dan refreshing.',
                'price'        => 35000,
                'badge'        => null,
                'serving_tag'  => 'Iced',
                'is_available' => 1,
            ],
            [
                'category'     => 'Cold Brew',
                'name'         => 'Cold Brew Oat',
                'description'  => 'Cold brew premium dengan oat milk, creamy tanpa dairy.',
                'price'        => 38000,
                'badge'        => null,
                'serving_tag'  => 'Iced',
                'is_available' => 1,
            ],
            [
                'category'     => 'Cold Brew',
                'name'         => 'Nitro Cold Brew',
                'description'  => 'Cold brew dengan nitrogen, tekstur creamy bak draft beer tanpa alkohol.',
                'price'        => 40000,
                'badge'        => 'Signature',
                'serving_tag'  => 'Iced',
                'is_available' => 1,
            ],

            // ---- SMOOTH SERIES ----
            [
                'category'     => 'Smooth Series',
                'name'         => 'Smooth Pistachio',
                'description'  => 'Es kopi susu dengan pasta pistachio Italia, kaya dan nutty.',
                'price'        => 36000,
                'badge'        => 'Best Seller',
                'serving_tag'  => 'Iced',
                'is_available' => 1,
            ],
            [
                'category'     => 'Smooth Series',
                'name'         => 'Smooth Caramel',
                'description'  => 'Smooth blend dengan caramel butterscotch yang kaya dan lembut.',
                'price'        => 36000,
                'badge'        => null,
                'serving_tag'  => 'Iced',
                'is_available' => 1,
            ],
            [
                'category'     => 'Smooth Series',
                'name'         => 'Smooth Tiramisu',
                'description'  => 'Es kopi dengan banana tiramisu cream, indulgent dan creamy.',
                'price'        => 36000,
                'badge'        => null,
                'serving_tag'  => 'Iced',
                'is_available' => 1,
            ],
            [
                'category'     => 'Smooth Series',
                'name'         => 'Magic Cheese Cream',
                'description'  => 'Kopi dengan magic cheese cream yang asin-manis, unik dan adiktif.',
                'price'        => 32000,
                'badge'        => 'Signature',
                'serving_tag'  => 'Iced',
                'is_available' => 1,
            ],
            [
                'category'     => 'Smooth Series',
                'name'         => 'Magic Coffee Cream',
                'description'  => 'Kopi dengan whipped coffee cream berlapis, bisa diaduk sendiri.',
                'price'        => 32000,
                'badge'        => null,
                'serving_tag'  => 'Iced',
                'is_available' => 1,
            ],
            [
                'category'     => 'Smooth Series',
                'name'         => 'Magic Tiramisu Cream',
                'description'  => 'Kopi dengan tiramisu cream lembut di atasnya, dessert dalam satu gelas.',
                'price'        => 32000,
                'badge'        => null,
                'serving_tag'  => 'Iced',
                'is_available' => 1,
            ],

            // ---- NON-KOPI ----
            [
                'category'     => 'Non-Kopi',
                'name'         => 'Matcha Latte',
                'description'  => 'Matcha ceremonial grade digiling halus, dikocok lalu disajikan dengan susu segar.',
                'price'        => 28000,
                'badge'        => 'Best Seller',
                'serving_tag'  => 'Iced',
                'is_available' => 1,
            ],
            [
                'category'     => 'Non-Kopi',
                'name'         => 'Taro Latte',
                'description'  => 'Taro ungu asli dengan susu segar, manis dan cantik.',
                'price'        => 28000,
                'badge'        => null,
                'serving_tag'  => 'Iced',
                'is_available' => 1,
            ],
            [
                'category'     => 'Non-Kopi',
                'name'         => 'Iced Chocolate',
                'description'  => 'Cokelat pekat premium dengan susu creamy dan es batu kristal.',
                'price'        => 28000,
                'badge'        => null,
                'serving_tag'  => 'Iced',
                'is_available' => 1,
            ],
            [
                'category'     => 'Non-Kopi',
                'name'         => 'Classic Milk Tea',
                'description'  => 'Teh hitam Ceylon dengan susu fresh, manis dan aromatic.',
                'price'        => 25000,
                'badge'        => null,
                'serving_tag'  => 'Iced',
                'is_available' => 1,
            ],
            [
                'category'     => 'Non-Kopi',
                'name'         => 'Aromatic Milk Tea',
                'description'  => 'Teh blend aromatik pilihan dengan susu segar yang kaya.',
                'price'        => 32000,
                'badge'        => 'Recommended',
                'serving_tag'  => 'Iced',
                'is_available' => 1,
            ],
            [
                'category'     => 'Non-Kopi',
                'name'         => 'Thai Milk Tea',
                'description'  => 'Teh hitam khas Thailand dengan susu evaporasi creamy.',
                'price'        => 27000,
                'badge'        => null,
                'serving_tag'  => 'Iced',
                'is_available' => 1,
            ],
            [
                'category'     => 'Non-Kopi',
                'name'         => 'Blue Pea Lemonade',
                'description'  => 'Bunga telang biru segar dengan perasan lemon, berubah warna saat dituang.',
                'price'        => 30000,
                'badge'        => null,
                'serving_tag'  => 'Iced',
                'is_available' => 1,
            ],
            [
                'category'     => 'Non-Kopi',
                'name'         => 'Strawberry Yogurt',
                'description'  => 'Yogurt plain dengan stroberi segar, asam segar dan sehat.',
                'price'        => 28000,
                'badge'        => null,
                'serving_tag'  => 'Iced',
                'is_available' => 1,
            ],
            [
                'category'     => 'Non-Kopi',
                'name'         => 'Lemon Mint Cooler',
                'description'  => 'Air lemon, daun mint segar, dan soda yang sangat menyegarkan.',
                'price'        => 22000,
                'badge'        => null,
                'serving_tag'  => 'Iced',
                'is_available' => 1,
            ],
            [
                'category'     => 'Non-Kopi',
                'name'         => 'Air Kelapa Muda',
                'description'  => 'Kelapa muda segar langsung dari buahnya, alami dan electrolyte-rich.',
                'price'        => 20000,
                'badge'        => null,
                'serving_tag'  => 'Iced',
                'is_available' => 1,
            ],

            // ---- MAKANAN ----
            [
                'category'     => 'Makanan',
                'name'         => 'Chicken Katsu Rice',
                'description'  => 'Ayam goreng tepung Jepang renyah dengan nasi dan saus katsu.',
                'price'        => 42000,
                'badge'        => 'Best Seller',
                'serving_tag'  => 'Dish',
                'is_available' => 1,
            ],
            [
                'category'     => 'Makanan',
                'name'         => 'Ayam Geprek Rice Bowl',
                'description'  => 'Ayam geprek sambal bawang pedas dengan nasi hangat.',
                'price'        => 38000,
                'badge'        => null,
                'serving_tag'  => 'Dish',
                'is_available' => 1,
            ],
            [
                'category'     => 'Makanan',
                'name'         => 'Nasi Goreng Kampung',
                'description'  => 'Nasi goreng tradisional dengan telur, acar, dan kerupuk.',
                'price'        => 32000,
                'badge'        => null,
                'serving_tag'  => 'Dish',
                'is_available' => 1,
            ],
            [
                'category'     => 'Makanan',
                'name'         => 'Pasta Carbonara',
                'description'  => 'Pasta creamy ala Italia dengan bacon, telur, dan parmesan.',
                'price'        => 45000,
                'badge'        => 'Recommended',
                'serving_tag'  => 'Dish',
                'is_available' => 1,
            ],
            [
                'category'     => 'Makanan',
                'name'         => 'Chicken Sandwich',
                'description'  => 'Ayam panggang dengan selada, tomat, keju, dan saus sriracha mayo.',
                'price'        => 38000,
                'badge'        => null,
                'serving_tag'  => 'Dish',
                'is_available' => 1,
            ],
            [
                'category'     => 'Makanan',
                'name'         => 'Nasi Uduk Ayam',
                'description'  => 'Nasi uduk gurih dengan ayam goreng, lalapan, dan sambal terasi.',
                'price'        => 35000,
                'badge'        => null,
                'serving_tag'  => 'Dish',
                'is_available' => 1,
            ],
            [
                'category'     => 'Makanan',
                'name'         => 'Club Sandwich',
                'description'  => 'Sandwich tiga lapis dengan ayam, telur, dan sayuran segar.',
                'price'        => 40000,
                'badge'        => null,
                'serving_tag'  => 'Dish',
                'is_available' => 1,
            ],
            [
                'category'     => 'Makanan',
                'name'         => 'Mie Goreng Spesial',
                'description'  => 'Mie goreng dengan telur, sayuran, bakso, dan bumbu rahasia.',
                'price'        => 28000,
                'badge'        => null,
                'serving_tag'  => 'Dish',
                'is_available' => 1,
            ],

            // ---- CAMILAN & ROTI ----
            [
                'category'     => 'Camilan & Roti',
                'name'         => 'Fudgy Brownie',
                'description'  => 'Brownies fudgy extra moist dengan chocolate chips, dense dan cokelat banget.',
                'price'        => 28000,
                'badge'        => 'Best Seller',
                'serving_tag'  => 'Snack',
                'is_available' => 1,
            ],
            [
                'category'     => 'Camilan & Roti',
                'name'         => 'Croissant Butter',
                'description'  => 'Croissant mentega berlapis-lapis, fresh from oven setiap pagi.',
                'price'        => 20000,
                'badge'        => null,
                'serving_tag'  => 'Snack',
                'is_available' => 1,
            ],
            [
                'category'     => 'Camilan & Roti',
                'name'         => 'Roti Bakar Coklat Keju',
                'description'  => 'Roti bakar tebal dengan spread coklat dan keju melt yang melimpah.',
                'price'        => 22000,
                'badge'        => null,
                'serving_tag'  => 'Snack',
                'is_available' => 1,
            ],
            [
                'category'     => 'Camilan & Roti',
                'name'         => 'Churros Cinnamon',
                'description'  => 'Churros goreng renyah taburan kayu manis dengan saus coklat dark.',
                'price'        => 25000,
                'badge'        => null,
                'serving_tag'  => 'Snack',
                'is_available' => 1,
            ],
            [
                'category'     => 'Camilan & Roti',
                'name'         => 'Kentang Wedges',
                'description'  => 'Irisan kentang panggang bumbu herbs dengan saus aioli.',
                'price'        => 24000,
                'badge'        => null,
                'serving_tag'  => 'Snack',
                'is_available' => 1,
            ],
            [
                'category'     => 'Camilan & Roti',
                'name'         => 'Dimsum Ayam (3 pcs)',
                'description'  => 'Dimsum ayam kukus lembut dengan chili oil dan soy sauce.',
                'price'        => 25000,
                'badge'        => null,
                'serving_tag'  => 'Snack',
                'is_available' => 1,
            ],
            [
                'category'     => 'Camilan & Roti',
                'name'         => 'Onion Rings',
                'description'  => 'Cincin bawang garing crispy dengan saus sriracha mayo.',
                'price'        => 22000,
                'badge'        => null,
                'serving_tag'  => 'Snack',
                'is_available' => 1,
            ],
            [
                'category'     => 'Camilan & Roti',
                'name'         => 'Waffle Original',
                'description'  => 'Waffle renyah di luar lembut di dalam, dengan maple syrup dan butter.',
                'price'        => 30000,
                'badge'        => null,
                'serving_tag'  => 'Snack',
                'is_available' => 1,
            ],
        ];
    }
}
