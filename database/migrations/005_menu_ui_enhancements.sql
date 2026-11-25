-- ============================================================
-- Migration 005: Menu UI Enhancements (Kopi Calf Style)
-- Tambah kolom badge, serving_tag, svg_illustration ke menus
-- Tambah kolom subtitle ke categories
-- Jalankan sekali pada database foodease yang aktif.
-- ============================================================

USE foodease;

-- 1. Tambah kolom ke tabel menus
ALTER TABLE menus
  ADD COLUMN IF NOT EXISTS badge VARCHAR(50) NULL COMMENT 'e.g. Best Seller, Recommended',
  ADD COLUMN IF NOT EXISTS serving_tag VARCHAR(30) NOT NULL DEFAULT 'Iced' COMMENT 'e.g. Iced, Hot, Bottle, Snack, Dish',
  ADD COLUMN IF NOT EXISTS svg_illustration TEXT NULL COMMENT 'Inline SVG code or path string';

-- 2. Tambah kolom ke tabel categories
ALTER TABLE categories
  ADD COLUMN IF NOT EXISTS subtitle VARCHAR(50) NULL COMMENT 'e.g. UNGGULAN, FAVORITE, SIGNATURE';

-- 3. Update subtitle untuk setiap kategori
UPDATE categories SET subtitle = 'UNGGULAN'  WHERE name = 'Kopi Susu';
UPDATE categories SET subtitle = 'SPESIAL'   WHERE name = 'Cold Brew';
UPDATE categories SET subtitle = 'SIGNATURE' WHERE name = 'Smooth Series';
UPDATE categories SET subtitle = 'FAVORITE'  WHERE name = 'Non-Kopi';
UPDATE categories SET subtitle = 'TERSEDIA'  WHERE name = 'Makanan';
UPDATE categories SET subtitle = 'CAMILAN'   WHERE name LIKE 'Camilan%';
