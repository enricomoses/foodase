-- ============================================================
-- Seed 005: Isi badge, serving_tag untuk semua menu
-- Jalankan setelah 005_menu_ui_enhancements.sql
-- ============================================================

USE foodease;

-- ---- KOPI SUSU (badge + serving_tag) ----
UPDATE menus SET badge = 'Best Seller', serving_tag = 'Iced'  WHERE name = 'Es Kopi Susu Regular'   AND deleted_at IS NULL;
UPDATE menus SET badge = 'Best Seller', serving_tag = 'Iced'  WHERE name = 'Es Kopi Susu Premium'   AND deleted_at IS NULL;
UPDATE menus SET badge = 'Recommended', serving_tag = 'Iced'  WHERE name = 'Premium Gula Jawa'      AND deleted_at IS NULL;
UPDATE menus SET badge = NULL,          serving_tag = 'Iced'  WHERE name = 'Kopi Susu Butterscotch' AND deleted_at IS NULL;
UPDATE menus SET badge = NULL,          serving_tag = 'Hot'   WHERE name = 'Americano'              AND deleted_at IS NULL;
UPDATE menus SET badge = NULL,          serving_tag = 'Iced'  WHERE name = 'Liquid Jefferson'       AND deleted_at IS NULL;
UPDATE menus SET badge = NULL,          serving_tag = 'Hot'   WHERE name = 'Latte'                  AND deleted_at IS NULL;
UPDATE menus SET badge = NULL,          serving_tag = 'Hot'   WHERE name = 'Cappuccino'             AND deleted_at IS NULL;
UPDATE menus SET badge = NULL,          serving_tag = 'Iced'  WHERE name = 'Vietnam Drip'           AND deleted_at IS NULL;

-- ---- COLD BREW ----
UPDATE menus SET badge = 'Best Seller', serving_tag = 'Iced'  WHERE name = 'Classic Cold Brew'  AND deleted_at IS NULL;
UPDATE menus SET badge = NULL,          serving_tag = 'Iced'  WHERE name = 'Cold Brew Milk'     AND deleted_at IS NULL;
UPDATE menus SET badge = NULL,          serving_tag = 'Iced'  WHERE name = 'Cold Brew Oat'      AND deleted_at IS NULL;
UPDATE menus SET badge = 'Signature',   serving_tag = 'Iced'  WHERE name = 'Nitro Cold Brew'    AND deleted_at IS NULL;

-- ---- SMOOTH SERIES ----
UPDATE menus SET badge = 'Best Seller', serving_tag = 'Iced'  WHERE name = 'Smooth Pistachio'      AND deleted_at IS NULL;
UPDATE menus SET badge = NULL,          serving_tag = 'Iced'  WHERE name = 'Smooth Caramel'        AND deleted_at IS NULL;
UPDATE menus SET badge = NULL,          serving_tag = 'Iced'  WHERE name = 'Smooth Tiramisu'       AND deleted_at IS NULL;
UPDATE menus SET badge = 'Signature',   serving_tag = 'Iced'  WHERE name = 'Magic Cheese Cream'    AND deleted_at IS NULL;
UPDATE menus SET badge = NULL,          serving_tag = 'Iced'  WHERE name = 'Magic Coffee Cream'    AND deleted_at IS NULL;
UPDATE menus SET badge = NULL,          serving_tag = 'Iced'  WHERE name = 'Magic Tiramisu Cream'  AND deleted_at IS NULL;

-- ---- NON-KOPI ----
UPDATE menus SET badge = 'Best Seller', serving_tag = 'Iced'    WHERE name = 'Matcha Latte'       AND deleted_at IS NULL;
UPDATE menus SET badge = NULL,          serving_tag = 'Iced'    WHERE name = 'Taro Latte'         AND deleted_at IS NULL;
UPDATE menus SET badge = NULL,          serving_tag = 'Iced'    WHERE name = 'Iced Chocolate'     AND deleted_at IS NULL;
UPDATE menus SET badge = NULL,          serving_tag = 'Iced'    WHERE name = 'Classic Milk Tea'   AND deleted_at IS NULL;
UPDATE menus SET badge = 'Recommended', serving_tag = 'Iced'    WHERE name = 'Aromatic Milk Tea'  AND deleted_at IS NULL;
UPDATE menus SET badge = NULL,          serving_tag = 'Iced'    WHERE name = 'Thai Milk Tea'      AND deleted_at IS NULL;
UPDATE menus SET badge = NULL,          serving_tag = 'Iced'    WHERE name = 'Blue Pea Lemonade'  AND deleted_at IS NULL;
UPDATE menus SET badge = NULL,          serving_tag = 'Iced'    WHERE name = 'Strawberry Yogurt'  AND deleted_at IS NULL;
UPDATE menus SET badge = NULL,          serving_tag = 'Iced'    WHERE name = 'Lemon Mint Cooler'  AND deleted_at IS NULL;
UPDATE menus SET badge = NULL,          serving_tag = 'Iced'    WHERE name = 'Air Kelapa Muda'    AND deleted_at IS NULL;

-- ---- MAKANAN ----
UPDATE menus SET badge = 'Best Seller', serving_tag = 'Dish' WHERE name = 'Chicken Katsu Rice'    AND deleted_at IS NULL;
UPDATE menus SET badge = NULL,          serving_tag = 'Dish' WHERE name = 'Ayam Geprek Rice Bowl'  AND deleted_at IS NULL;
UPDATE menus SET badge = NULL,          serving_tag = 'Dish' WHERE name = 'Nasi Goreng Kampung'    AND deleted_at IS NULL;
UPDATE menus SET badge = 'Recommended', serving_tag = 'Dish' WHERE name = 'Pasta Carbonara'        AND deleted_at IS NULL;
UPDATE menus SET badge = NULL,          serving_tag = 'Dish' WHERE name = 'Chicken Sandwich'       AND deleted_at IS NULL;
UPDATE menus SET badge = NULL,          serving_tag = 'Dish' WHERE name = 'Nasi Uduk Ayam'         AND deleted_at IS NULL;
UPDATE menus SET badge = NULL,          serving_tag = 'Dish' WHERE name = 'Club Sandwich'          AND deleted_at IS NULL;
UPDATE menus SET badge = NULL,          serving_tag = 'Dish' WHERE name = 'Mie Goreng Spesial'     AND deleted_at IS NULL;

-- ---- CAMILAN & ROTI ----
UPDATE menus SET badge = 'Best Seller', serving_tag = 'Snack' WHERE name = 'Fudgy Brownie'          AND deleted_at IS NULL;
UPDATE menus SET badge = NULL,          serving_tag = 'Snack' WHERE name = 'Croissant Butter'        AND deleted_at IS NULL;
UPDATE menus SET badge = NULL,          serving_tag = 'Snack' WHERE name = 'Roti Bakar Coklat Keju'  AND deleted_at IS NULL;
UPDATE menus SET badge = NULL,          serving_tag = 'Snack' WHERE name = 'Churros Cinnamon'        AND deleted_at IS NULL;
UPDATE menus SET badge = NULL,          serving_tag = 'Snack' WHERE name = 'Kentang Wedges'          AND deleted_at IS NULL;
UPDATE menus SET badge = NULL,          serving_tag = 'Snack' WHERE name = 'Dimsum Ayam (3 pcs)'     AND deleted_at IS NULL;
UPDATE menus SET badge = NULL,          serving_tag = 'Snack' WHERE name = 'Onion Rings'             AND deleted_at IS NULL;
UPDATE menus SET badge = NULL,          serving_tag = 'Snack' WHERE name = 'Waffle Original'         AND deleted_at IS NULL;
