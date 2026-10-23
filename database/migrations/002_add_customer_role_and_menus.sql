-- ============================================================
-- Migration: Add 'customer' role + 20 new menu items
-- Run once against the live foodease database
-- ============================================================

-- 1. Add 'customer' to users.role ENUM
ALTER TABLE users
  MODIFY COLUMN role ENUM('admin','cashier','customer') NOT NULL DEFAULT 'cashier';

-- 2. Add two new categories
INSERT INTO categories (name, sort_order) VALUES
  ('Minuman Segar',  5),
  ('Paket & Promo',  6)
ON DUPLICATE KEY UPDATE sort_order = VALUES(sort_order);

-- 3. Add 20 new menu items
--    cat 1=Kopi, 2=Non-Kopi, 3=Makanan Berat, 4=Camilan
--    cat 5=Minuman Segar, 6=Paket & Promo (new)

INSERT INTO menus (category_id, name, description, price, is_available) VALUES
-- Kopi
(1, 'Cold Brew',           'Kopi hitam dingin direndam 12 jam, lembut dan tidak pahit',          32000, 1),
(1, 'Vietnam Drip',        'Kopi tetes gaya Vietnam dengan susu kental manis',                   28000, 1),
(1, 'Latte',               'Espresso lembut dengan susu steamed yang creamy',                    35000, 1),
(1, 'Flat White',          'Double ristretto dengan micro-foam susu segar pilihan',              38000, 1),
-- Non-Kopi
(2, 'Thai Tea',            'Teh hitam khas Thailand dengan susu evaporasi yang creamy',          27000, 1),
(2, 'Blue Pea Lemonade',   'Minuman bunga telang biru segar dengan perasan lemon',              30000, 1),
(2, 'Strawberry Smoothie', 'Smoothie stroberi segar tanpa pemanis tambahan',                    33000, 1),
(2, 'Milo Dinosaur',       'Milo panas dengan topping Milo bubuk berlimpah',                    28000, 1),
-- Makanan Berat
(3, 'Nasi Uduk Ayam',      'Nasi uduk gurih dengan ayam goreng, lalapan, dan sambal terasi',    38000, 1),
(3, 'Pasta Carbonara',     'Pasta creamy dengan bacon, telur, dan parmesan',                    45000, 1),
(3, 'Sandwich Club',       'Lapis tiga dengan ayam panggang, selada, tomat, dan mayo',          42000, 1),
(3, 'Chicken Steak',       'Dada ayam panggang dengan saus mushroom dan kentang wedges',        52000, 1),
(3, 'Nasi Lemak',          'Nasi santan dengan ikan teri, kacang, telur rebus, dan sambal',     35000, 1),
-- Camilan
(4, 'Churros',             'Churros goreng garing tabur kayu manis dengan saus coklat',         25000, 1),
(4, 'Onion Ring',          'Cincin bawang garing dengan saus sriracha mayo',                    22000, 1),
(4, 'Croissant',           'Croissant mentega lapis-lapis, fresh from oven',                    20000, 1),
-- Minuman Segar (cat 5)
(5, 'Es Jeruk Segar',      'Perasan jeruk nipis asli tanpa pemanis buatan',                     15000, 1),
(5, 'Lemon Mint Cooler',   'Air lemon, mint segar, dan soda yang menyegarkan',                  22000, 1),
(5, 'Alpukat Kocok',       'Alpukat kocok creamy dengan susu dan coklat',                       30000, 1),
(5, 'Air Kelapa Muda',     'Kelapa muda segar langsung dari buahnya',                           20000, 1);
