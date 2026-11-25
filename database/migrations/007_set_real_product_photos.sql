-- ============================================================
-- Migration 007: Set Real Authentic Product Photos
-- Mengganti gambar menu ke foto produk real yang tersedia
-- ============================================================

USE foodease;

-- ---- 1. KOPI SUSU ----
UPDATE menus SET image = 'premium.webp'                  WHERE name = 'Es Kopi Susu Premium';
UPDATE menus SET image = 'reguler.webp'                  WHERE name = 'Es Kopi Susu Regular';
UPDATE menus SET image = 'menu_3_kopi_susu_gula_aren.jpg' WHERE name = 'Kopi Susu Can 330ml';
UPDATE menus SET image = 'sweet-calf-tiramisu.webp'      WHERE name = 'Butterscotch Macchiato';
UPDATE menus SET image = 'sweet-calf-cookies.webp'       WHERE name = 'Caramel Macchiato';
UPDATE menus SET image = 'menu_iced_latte.jpg'           WHERE name = 'Premium Oat Milk Kopi';
UPDATE menus SET image = 'menu_3_kopi_susu_gula_aren.jpg' WHERE name = 'Kopi Susu Gula Kelapa';
UPDATE menus SET image = 'reguler.webp'                  WHERE name = 'Kopi Susu Gula Jawa';
UPDATE menus SET image = 'botol-1l.webp'                 WHERE name = 'Kopi Susu Botol 1 Liter';

-- ---- 2. SPECIALTY COFFEE ----
UPDATE menus SET image = 'long-black.webp'         WHERE name = 'Long Black Iced';
UPDATE menus SET image = 'menu_2_americano.jpg'    WHERE name = 'Long Black Hot';
UPDATE menus SET image = 'iced-white.webp'         WHERE name = 'Ice White';
UPDATE menus SET image = 'dirty-latte.webp'        WHERE name = 'Dirty Latte';
UPDATE menus SET image = 'magic.webp'              WHERE name = 'Magic Latte';
UPDATE menus SET image = 'menu_4_cappuccino.jpg'   WHERE name = 'Cappuccino';
UPDATE menus SET image = 'split.webp'              WHERE name = 'Piccolo';
UPDATE menus SET image = 'affogatto.webp'          WHERE name = 'Affogato';
UPDATE menus SET image = 'menu_1_espresso.jpg'     WHERE name = 'Espresso Double Shot';

-- ---- 3. BLACK SIGNATURE ----
UPDATE menus SET image = 'mont-blanc.webp'         WHERE name = 'Berry Shot';
UPDATE menus SET image = 'menu_35_cold_brew.jpg'   WHERE name = 'Cold Brew Peach';
UPDATE menus SET image = 'mont-blanc.webp'         WHERE name = 'Fusion Black';
UPDATE menus SET image = 'mont-blanc.webp'         WHERE name = 'Mont Blanc';

-- ---- 4. SMOOTH & MAGIC CREAM ----
UPDATE menus SET image = 'magic-cheese-butter-cream.webp' WHERE name = 'Magic Cheese Cream';
UPDATE menus SET image = 'smooth-cup.webp'                WHERE name = 'Smooth Caramel Butterscotch';
UPDATE menus SET image = 'smooth-cup.webp'                WHERE name = 'Smooth Pistachio';
UPDATE menus SET image = 'sweet-calf-strawnberry.webp'    WHERE name = 'Smooth Strawberry Sunkist';
UPDATE menus SET image = 'sweet-calf-tiramisu.webp'       WHERE name = 'Smooth Banana Tiramisu';

-- ---- 5. NON-KOPI & MILK SERIES ----
UPDATE menus SET image = 'matcha.webp'               WHERE name = 'New Matcha Latte';
UPDATE menus SET image = 'iced-chocolate.webp'       WHERE name = 'Iced Chocolate';
UPDATE menus SET image = 'classic-milk-tea.webp'     WHERE name = 'Classic Milk Tea';
UPDATE menus SET image = 'taro-latte.webp'           WHERE name = 'Taro Latte';
UPDATE menus SET image = 'orange-coconut.webp'       WHERE name = 'Orange Coconut';
UPDATE menus SET image = 'sweet-calf-matcha.webp'    WHERE name = 'Sweet Matcha Cookies';
UPDATE menus SET image = 'sweet-calf-strawnberry.webp' WHERE name = 'Sweet Strawberry';
UPDATE menus SET image = 'sweet-calf-toblerone.webp' WHERE name = 'Sweet Toblerone';

-- ---- 6. MAKANAN BERAT ----
UPDATE menus SET image = 'chicken-rice.webp'             WHERE name = 'Chicken Rice Creamy Garlic';
UPDATE menus SET image = 'nasi-ayam-balado.webp'         WHERE name = 'Nasi Ayam Sambal Balado';
UPDATE menus SET image = 'shortplate-beef.webp'          WHERE name = 'Shortplate Rice Creamy Garlic';
UPDATE menus SET image = 'menu_10_ayam_geprek_rice_bowl.jpg' WHERE name = 'Nasi Ayam Sambal Hijau';
UPDATE menus SET image = 'shortplate-beef.webp'          WHERE name = 'Shortplate Rice Mushroom Truffle';
UPDATE menus SET image = 'shortplate-beef.webp'          WHERE name = 'Nasi Shortplate Sambal Hijau';
UPDATE menus SET image = 'shortplate-beef.webp'          WHERE name = 'Shortplate Rice Korean Sauce';
UPDATE menus SET image = 'chicken-rice.webp'             WHERE name = 'Chicken Rice Mushroom Truffle';
UPDATE menus SET image = 'nasi-goreng.webp'              WHERE name = 'Nasi Goreng Spesial';
UPDATE menus SET image = 'pasta-carbonara.webp'          WHERE name = 'Pasta Carbonara';

-- ---- 7. DIMSUM ----
UPDATE menus SET image = 'dimsum-mentai.webp'        WHERE name = 'Dimsum Mentai';
UPDATE menus SET image = 'dimsum-original.webp'      WHERE name = 'Dimsum Original';
UPDATE menus SET image = 'dimsum-mozzarella.webp'    WHERE name = 'Dimsum Mozzarella';
UPDATE menus SET image = 'dimsum-mentai.webp'        WHERE name = 'Dimsum Goreng Mentai';
UPDATE menus SET image = 'dimsum-original.webp'      WHERE name = 'Dimsum Nori';
UPDATE menus SET image = 'dimsum-saus-tartar.webp'   WHERE name = 'Dimsum Tartar';

-- ---- 8. CAMILAN & BAKERY ----
UPDATE menus SET image = 'fudgy-brownies.webp'            WHERE name = 'Brownies Fudgy';
UPDATE menus SET image = 'roti-kukus-chocolate-keju.webp' WHERE name = 'Roti Pandan Coklat Keju';
UPDATE menus SET image = 'toast-bread.webp'               WHERE name = 'Srikaya Toast Butter';
UPDATE menus SET image = 'pisang-goreng.webp'             WHERE name = 'Pisang Goreng Crispy';
UPDATE menus SET image = 'mix-platter.webp'               WHERE name = 'Mix Platter';
UPDATE menus SET image = 'chicken-karage.webp'            WHERE name = 'Chicken Karage';
UPDATE menus SET image = 'french-fries.webp'              WHERE name = 'Cheezy French Fries';
UPDATE menus SET image = 'french-fries.webp'              WHERE name = 'Bolognaise French Fries';
UPDATE menus SET image = 'roti-chocolate.webp'            WHERE name = 'Pisang Coklat Lumer';
