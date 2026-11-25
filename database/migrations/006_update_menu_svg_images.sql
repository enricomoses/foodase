-- ============================================================
-- Migration 006: Update Menu Images to Crisp Vector SVGs
-- Mengganti gambar-gambar webp duplikat dengan ilustrasi SVG tajam anti-pecah
-- ============================================================

USE foodease;

-- ---- 1. KOPI SUSU ----
UPDATE menus SET image = 'es-kopi-susu-premium.svg' WHERE name = 'Es Kopi Susu Premium';
UPDATE menus SET image = 'es-kopi-susu-regular.svg' WHERE name = 'Es Kopi Susu Regular';
UPDATE menus SET image = 'kopi-susu-can.svg'         WHERE name = 'Kopi Susu Can 330ml';
UPDATE menus SET image = 'butterscotch-macchiato.svg' WHERE name = 'Butterscotch Macchiato';
UPDATE menus SET image = 'caramel-macchiato.svg'     WHERE name = 'Caramel Macchiato';
UPDATE menus SET image = 'es-kopi-susu-premium.svg' WHERE name = 'Premium Oat Milk Kopi';
UPDATE menus SET image = 'es-kopi-susu-regular.svg' WHERE name = 'Kopi Susu Gula Kelapa';
UPDATE menus SET image = 'es-kopi-susu-regular.svg' WHERE name = 'Kopi Susu Gula Jawa';
UPDATE menus SET image = 'kopi-susu-botol-1l.svg'    WHERE name = 'Kopi Susu Botol 1 Liter';

-- ---- 2. SPECIALTY COFFEE ----
UPDATE menus SET image = 'long-black-iced.svg'      WHERE name = 'Long Black Iced';
UPDATE menus SET image = 'long-black-hot.svg'       WHERE name = 'Long Black Hot';
UPDATE menus SET image = 'dirty-latte.svg'          WHERE name = 'Ice White';
UPDATE menus SET image = 'dirty-latte.svg'          WHERE name = 'Dirty Latte';
UPDATE menus SET image = 'cappuccino.svg'           WHERE name = 'Magic Latte';
UPDATE menus SET image = 'cappuccino.svg'           WHERE name = 'Cappuccino';
UPDATE menus SET image = 'long-black-hot.svg'       WHERE name = 'Piccolo';
UPDATE menus SET image = 'affogato.svg'             WHERE name = 'Affogato';
UPDATE menus SET image = 'long-black-hot.svg'       WHERE name = 'Espresso Double Shot';

-- ---- 3. BLACK SIGNATURE ----
UPDATE menus SET image = 'long-black-iced.svg'      WHERE name = 'Berry Shot';
UPDATE menus SET image = 'long-black-iced.svg'      WHERE name = 'Cold Brew Peach';
UPDATE menus SET image = 'long-black-iced.svg'      WHERE name = 'Fusion Black';
UPDATE menus SET image = 'cappuccino.svg'           WHERE name = 'Mont Blanc';

-- ---- 4. SMOOTH & MAGIC CREAM ----
UPDATE menus SET image = 'magic-cheese-butter-cream.svg' WHERE name = 'Magic Cheese Cream';
UPDATE menus SET image = 'smooth-cup.svg'                WHERE name = 'Smooth Caramel Butterscotch';
UPDATE menus SET image = 'smooth-cup.svg'                WHERE name = 'Smooth Pistachio';
UPDATE menus SET image = 'sweet-strawberry.svg'          WHERE name = 'Smooth Strawberry Sunkist';
UPDATE menus SET image = 'smooth-cup.svg'                WHERE name = 'Smooth Banana Tiramisu';

-- ---- 5. NON-KOPI & MILK SERIES ----
UPDATE menus SET image = 'matcha-latte.svg'         WHERE name = 'New Matcha Latte';
UPDATE menus SET image = 'iced-chocolate.svg'       WHERE name = 'Iced Chocolate';
UPDATE menus SET image = 'classic-milk-tea.svg'     WHERE name = 'Classic Milk Tea';
UPDATE menus SET image = 'taro-latte.svg'           WHERE name = 'Taro Latte';
UPDATE menus SET image = 'orange-coconut.svg'       WHERE name = 'Orange Coconut';
UPDATE menus SET image = 'matcha-latte.svg'         WHERE name = 'Sweet Matcha Cookies';
UPDATE menus SET image = 'sweet-strawberry.svg'     WHERE name = 'Sweet Strawberry';
UPDATE menus SET image = 'iced-chocolate.svg'       WHERE name = 'Sweet Toblerone';

-- ---- 6. MAKANAN BERAT ----
UPDATE menus SET image = 'chicken-rice-bowl.svg'    WHERE name = 'Chicken Rice Creamy Garlic';
UPDATE menus SET image = 'chicken-rice-bowl.svg'    WHERE name = 'Nasi Ayam Sambal Balado';
UPDATE menus SET image = 'shortplate-beef.svg'      WHERE name = 'Shortplate Rice Creamy Garlic';
UPDATE menus SET image = 'chicken-rice-bowl.svg'    WHERE name = 'Nasi Ayam Sambal Hijau';
UPDATE menus SET image = 'shortplate-beef.svg'      WHERE name = 'Shortplate Rice Mushroom Truffle';
UPDATE menus SET image = 'shortplate-beef.svg'      WHERE name = 'Nasi Shortplate Sambal Hijau';
UPDATE menus SET image = 'shortplate-beef.svg'      WHERE name = 'Shortplate Rice Korean Sauce';
UPDATE menus SET image = 'chicken-rice-bowl.svg'    WHERE name = 'Chicken Rice Mushroom Truffle';
UPDATE menus SET image = 'nasi-goreng.svg'          WHERE name = 'Nasi Goreng Spesial';
UPDATE menus SET image = 'pasta-carbonara.svg'      WHERE name = 'Pasta Carbonara';

-- ---- 7. DIMSUM ----
UPDATE menus SET image = 'dimsum-mentai.svg'        WHERE name = 'Dimsum Mentai';
UPDATE menus SET image = 'dimsum-original.svg'      WHERE name = 'Dimsum Original';
UPDATE menus SET image = 'dimsum-mentai.svg'        WHERE name = 'Dimsum Mozzarella';
UPDATE menus SET image = 'dimsum-mentai.svg'        WHERE name = 'Dimsum Goreng Mentai';
UPDATE menus SET image = 'dimsum-original.svg'      WHERE name = 'Dimsum Nori';
UPDATE menus SET image = 'dimsum-original.svg'      WHERE name = 'Dimsum Tartar';

-- ---- 8. CAMILAN & BAKERY ----
UPDATE menus SET image = 'brownies-fudgy.svg'       WHERE name = 'Brownies Fudgy';
UPDATE menus SET image = 'toast-bread.svg'          WHERE name = 'Roti Pandan Coklat Keju';
UPDATE menus SET image = 'toast-bread.svg'          WHERE name = 'Srikaya Toast Butter';
UPDATE menus SET image = 'pisang-goreng.svg'        WHERE name = 'Pisang Goreng Crispy';
UPDATE menus SET image = 'chicken-karage.svg'       WHERE name = 'Mix Platter';
UPDATE menus SET image = 'chicken-karage.svg'       WHERE name = 'Chicken Karage';
UPDATE menus SET image = 'french-fries.svg'         WHERE name = 'Cheezy French Fries';
UPDATE menus SET image = 'french-fries.svg'         WHERE name = 'Bolognaise French Fries';
UPDATE menus SET image = 'pisang-goreng.svg'        WHERE name = 'Pisang Coklat Lumer';
