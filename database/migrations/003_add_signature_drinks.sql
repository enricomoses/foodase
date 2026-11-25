-- ============================================================
-- Migration: Signature drinks for FoodEase landing page
-- Run once against the live foodease database.
-- Idempotent: existing rows are left untouched except a missing photo.
-- ============================================================

USE foodease;

SET @kopi_id    = (SELECT id FROM categories WHERE name = 'Kopi'     ORDER BY id LIMIT 1);
SET @nonkopi_id = (SELECT id FROM categories WHERE name = 'Non-Kopi' ORDER BY id LIMIT 1);

INSERT INTO menus (category_id, name, description, price, image, is_available)
SELECT @kopi_id, 'Iced Latte', 'Espresso ganda dengan susu segar dan es batu, lembut dan seimbang.', 35000, 'menu_iced_latte.jpg', 1
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM menus WHERE name = 'Iced Latte' AND deleted_at IS NULL);

INSERT INTO menus (category_id, name, description, price, image, is_available)
SELECT @nonkopi_id, 'Iced Chocolate', 'Cokelat dingin pekat dengan susu creamy dan topping krim lembut.', 28000, 'menu_iced_chocolate.jpg', 1
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM menus WHERE name = 'Iced Chocolate' AND deleted_at IS NULL);

INSERT INTO menus (category_id, name, description, price, image, is_available)
SELECT @nonkopi_id, 'Iced Matcha', 'Matcha ceremonial digiling halus, dikocok hingga creamy lalu disajikan dingin.', 28000, 'menu_iced_matcha.jpg', 1
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM menus WHERE name = 'Iced Matcha' AND deleted_at IS NULL);

INSERT INTO menus (category_id, name, description, price, image, is_available)
SELECT @nonkopi_id, 'Iced Lemon Tea', 'Teh hitam seduh dingin dengan irisan lemon segar dan madu.', 18000, 'menu_iced_lemon_tea.jpg', 1
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM menus WHERE name = 'Iced Lemon Tea' AND deleted_at IS NULL);

UPDATE menus SET image = 'menu_iced_latte.jpg'
WHERE name = 'Iced Latte' AND deleted_at IS NULL AND (image IS NULL OR image = '');

UPDATE menus SET image = 'menu_iced_chocolate.jpg'
WHERE name = 'Iced Chocolate' AND deleted_at IS NULL AND (image IS NULL OR image = '');

UPDATE menus SET image = 'menu_iced_matcha.jpg'
WHERE name = 'Iced Matcha' AND deleted_at IS NULL AND (image IS NULL OR image = '');

UPDATE menus SET image = 'menu_iced_lemon_tea.jpg'
WHERE name = 'Iced Lemon Tea' AND deleted_at IS NULL AND (image IS NULL OR image = '');
