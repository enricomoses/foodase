-- ============================================================
-- Migration 004: Kopi Nusa – Rebrand & Full Menu Overhaul
-- Menghapus menu lama dan menggantinya dengan menu Kopi Calf
-- + makanan dan beverages tambahan.
-- Jalankan sekali pada database foodease yang aktif.
-- ============================================================

USE foodease;

-- 1. Hapus menu lama (soft delete terlebih dulu agar FK tidak masalah)
UPDATE menus SET deleted_at = NOW() WHERE deleted_at IS NULL;

-- 2. Hapus kategori lama & tambah kategori baru
SET FOREIGN_KEY_CHECKS = 0;
DELETE FROM categories;
SET FOREIGN_KEY_CHECKS = 1;

ALTER TABLE categories AUTO_INCREMENT = 1;

INSERT INTO categories (id, name, sort_order) VALUES
  (1, 'Kopi Susu',       1),
  (2, 'Cold Brew',       2),
  (3, 'Smooth Series',   3),
  (4, 'Non-Kopi',        4),
  (5, 'Makanan',         5),
  (6, 'Camilan & Roti',  6);

-- 3. Insert menu baru (tidak soft-deleted, is_available = 1)
-- ---- KOPI SUSU (cat 1) ----
INSERT INTO menus (category_id, name, description, price, image, is_available, deleted_at) VALUES
(1, 'Es Kopi Susu Regular',   'Espresso Robusta dengan gula aren asli, creamy dan manis alami.',                   22000, NULL, 1, NULL),
(1, 'Es Kopi Susu Premium',   'Espresso Arabica single origin dengan gula aren pilihan, lebih fruity & complex.',  25000, NULL, 1, NULL),
(1, 'Premium Gula Jawa',      'Espresso Arabica dengan gula jawa asli, aroma karamel khas nusantara.',             25000, NULL, 1, NULL),
(1, 'Kopi Susu Butterscotch', 'Espresso lembut dengan butterscotch caramel yang manis legit.',                     29000, NULL, 1, NULL),
(1, 'Americano',              'Espresso panjang on the rock, pahit seimbang dengan after-taste yang bersih.',      29500, NULL, 1, NULL),
(1, 'Liquid Jefferson',       'Espresso on the rock dengan palm sugar & secret recipe kami.',                      33000, NULL, 1, NULL),
(1, 'Latte',                  'Espresso lembut dengan susu steamed micro-foam pilihan.',                           32000, NULL, 1, NULL),
(1, 'Cappuccino',             'Espresso dengan susu dan foam tebal, klasik dan memuaskan.',                        30000, NULL, 1, NULL),
(1, 'Vietnam Drip',           'Kopi tetes gaya Vietnam perlahan dengan susu kental manis di bawahnya.',            28000, NULL, 1, NULL),
-- ---- COLD BREW (cat 2) ----
(2, 'Classic Cold Brew',      'Kopi hitam direndam 12 jam dalam air dingin, smooth dan tidak pahit sama sekali.',  32000, NULL, 1, NULL),
(2, 'Cold Brew Milk',         'Cold brew dicampur susu segar, lembut dan refreshing.',                             35000, NULL, 1, NULL),
(2, 'Cold Brew Oat',          'Cold brew premium dengan oat milk, creamy tanpa dairy.',                            38000, NULL, 1, NULL),
(2, 'Nitro Cold Brew',        'Cold brew dengan nitrogen, tekstur creamy bak draft beer tanpa alkohol.',           40000, NULL, 1, NULL),
-- ---- SMOOTH SERIES (cat 3) ----
(3, 'Smooth Pistachio',       'Es kopi susu dengan pasta pistachio Italia, kaya dan nuttty.',                     36000, NULL, 1, NULL),
(3, 'Smooth Caramel',         'Smooth blend dengan caramel butterscotch yang kaya dan lembut.',                    36000, NULL, 1, NULL),
(3, 'Smooth Tiramisu',        'Es kopi dengan banana tiramisu cream, indulgent dan creamy.',                       36000, NULL, 1, NULL),
(3, 'Magic Cheese Cream',     'Kopi dengan magic cheese cream yang asin-manis, unik dan adiktif.',                 32000, NULL, 1, NULL),
(3, 'Magic Coffee Cream',     'Kopi dengan whipped coffee cream berlapis, bisa diaduk sendiri.',                   32000, NULL, 1, NULL),
(3, 'Magic Tiramisu Cream',   'Kopi dengan tiramisu cream lembut di atasnya, dessert dalam satu gelas.',           32000, NULL, 1, NULL),
-- ---- NON-KOPI (cat 4) ----
(4, 'Matcha Latte',           'Matcha ceremonial grade digiling halus, dikocok lalu disajikan dengan susu segar.', 28000, NULL, 1, NULL),
(4, 'Taro Latte',             'Taro ungu asli dengan susu segar, manis dan cantik.',                               28000, NULL, 1, NULL),
(4, 'Iced Chocolate',         'Cokelat pekat premium dengan susu creamy dan es batu kristal.',                     28000, NULL, 1, NULL),
(4, 'Classic Milk Tea',       'Teh hitam Ceylon dengan susu fresh, manis dan aromatic.',                           25000, NULL, 1, NULL),
(4, 'Aromatic Milk Tea',      'Teh blend aromatik pilihan dengan susu segar yang kaya.',                           32000, NULL, 1, NULL),
(4, 'Thai Milk Tea',          'Teh hitam khas Thailand dengan susu evaporasi creamy.',                             27000, NULL, 1, NULL),
(4, 'Blue Pea Lemonade',      'Bunga telang biru segar dengan perasan lemon, berubah warna saat dituang.',         30000, NULL, 1, NULL),
(4, 'Strawberry Yogurt',      'Yogurt plain dengan stroberi segar, asam segar dan sehat.',                         28000, NULL, 1, NULL),
(4, 'Lemon Mint Cooler',      'Air lemon, daun mint segar, dan soda yang sangat menyegarkan.',                     22000, NULL, 1, NULL),
(4, 'Air Kelapa Muda',        'Kelapa muda segar langsung dari buahnya, alami dan electrolyte-rich.',              20000, NULL, 1, NULL),
-- ---- MAKANAN (cat 5) ----
(5, 'Chicken Katsu Rice',     'Ayam goreng tepung Jepang renyah dengan nasi dan saus katsu.',                      42000, NULL, 1, NULL),
(5, 'Ayam Geprek Rice Bowl',  'Ayam geprek sambal bawang pedas dengan nasi hangat.',                               38000, NULL, 1, NULL),
(5, 'Nasi Goreng Kampung',    'Nasi goreng tradisional dengan telur, acar, dan kerupuk.',                          32000, NULL, 1, NULL),
(5, 'Pasta Carbonara',        'Pasta creamy ala Italia dengan bacon, telur, dan parmesan.',                        45000, NULL, 1, NULL),
(5, 'Chicken Sandwich',       'Ayam panggang dengan selada, tomat, keju, dan saus sriracha mayo.',                 38000, NULL, 1, NULL),
(5, 'Nasi Uduk Ayam',         'Nasi uduk gurih dengan ayam goreng, lalapan, dan sambal terasi.',                   35000, NULL, 1, NULL),
(5, 'Club Sandwich',          'Sandwich tiga lapis dengan ayam, telur, dan sayuran segar.',                        40000, NULL, 1, NULL),
(5, 'Mie Goreng Spesial',     'Mie goreng dengan telur, sayuran, bakso, dan bumbu rahasia.',                       28000, NULL, 1, NULL),
-- ---- CAMILAN & ROTI (cat 6) ----
(6, 'Fudgy Brownie',          'Brownies fudgy extra moist dengan chocolate chips, dense dan cokelat banget.',      28000, NULL, 1, NULL),
(6, 'Croissant Butter',       'Croissant mentega berlapis-lapis, fresh from oven setiap pagi.',                    20000, NULL, 1, NULL),
(6, 'Roti Bakar Coklat Keju', 'Roti bakar tebal dengan spread coklat dan keju melt yang melimpah.',               22000, NULL, 1, NULL),
(6, 'Churros Cinnamon',       'Churros goreng renyah taburan kayu manis dengan saus coklat dark.',                 25000, NULL, 1, NULL),
(6, 'Kentang Wedges',         'Irisan kentang panggang bumbu herbs dengan saus aioli.',                            24000, NULL, 1, NULL),
(6, 'Dimsum Ayam (3 pcs)',    'Dimsum ayam kukus lembut dengan chili oil dan soy sauce.',                          25000, NULL, 1, NULL),
(6, 'Onion Rings',            'Cincin bawang garing crispy dengan saus sriracha mayo.',                            22000, NULL, 1, NULL),
(6, 'Waffle Original',        'Waffle renyah di luar lembut di dalam, dengan maple syrup dan butter.',             30000, NULL, 1, NULL);
