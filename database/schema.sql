-- ============================================================================
-- FoodEase (Nola) — Consolidated Database Schema
-- Engine  : InnoDB (MySQL 8.0+ / MariaDB 10.4+)
-- Charset : utf8mb4 / utf8mb4_unicode_ci
-- Notes   : Portabel tanpa hardcode nama database (dikelola via .env & migrate.php).
-- ============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS payments, order_details, orders, menus, categories, users, schema_migrations;

SET FOREIGN_KEY_CHECKS = 1;

-- ----------------------------------------------------------------------------
-- 1. Schema Migrations Tracking
-- ----------------------------------------------------------------------------
CREATE TABLE schema_migrations (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  migration   VARCHAR(150) NOT NULL,
  batch       INT UNSIGNED NOT NULL DEFAULT 1,
  executed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_schema_migrations_name (migration)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 2. Users (Staff: Admin & Cashier)
-- ----------------------------------------------------------------------------
CREATE TABLE users (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name          VARCHAR(100) NOT NULL,
  username      VARCHAR(50)  NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  role          ENUM('admin','cashier') NOT NULL DEFAULT 'cashier',
  is_active     TINYINT(1) NOT NULL DEFAULT 1,
  last_login_at DATETIME NULL,
  session_id    VARCHAR(128) NULL,
  created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_users_username (username),
  KEY idx_users_role_active (role, is_active),
  KEY idx_users_session (session_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 3. Categories
-- ----------------------------------------------------------------------------
CREATE TABLE categories (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name       VARCHAR(60) NOT NULL,
  sort_order SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  subtitle   VARCHAR(50) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_categories_name (name),
  KEY idx_categories_sort (sort_order, name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 4. Menus
-- ----------------------------------------------------------------------------
CREATE TABLE menus (
  id               INT UNSIGNED NOT NULL AUTO_INCREMENT,
  category_id      INT UNSIGNED NOT NULL,
  name             VARCHAR(100) NOT NULL,
  description      TEXT NULL,
  price            INT UNSIGNED NOT NULL,
  image            VARCHAR(255) NULL,
  badge            VARCHAR(50) NULL COMMENT 'Contoh: Best Seller, Recommended, Signature',
  serving_tag      VARCHAR(30) NOT NULL DEFAULT 'Iced' COMMENT 'Contoh: Iced, Hot, Dish, Snack',
  svg_illustration TEXT NULL,
  is_available     TINYINT(1) NOT NULL DEFAULT 1,
  deleted_at       DATETIME NULL,
  created_at       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_menus_category (category_id),
  KEY idx_menus_cat_active (category_id, deleted_at, is_available),
  KEY idx_menus_active_name (deleted_at, is_available, name),
  CONSTRAINT fk_menus_category FOREIGN KEY (category_id)
    REFERENCES categories (id) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT chk_menus_price CHECK (price > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 5. Orders
-- ----------------------------------------------------------------------------
CREATE TABLE orders (
  id             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  order_code     VARCHAR(20) NOT NULL,
  customer_name  VARCHAR(50) NOT NULL,
  order_type     ENUM('dine_in','takeaway') NOT NULL,
  table_number   TINYINT UNSIGNED NULL,
  notes          VARCHAR(255) NULL,
  source         ENUM('online','cashier') NOT NULL DEFAULT 'online',
  status         ENUM('pending','processing','ready','completed','cancelled') NOT NULL DEFAULT 'pending',
  payment_status ENUM('unpaid','paid') NOT NULL DEFAULT 'unpaid',
  total          INT UNSIGNED NOT NULL DEFAULT 0,
  cancel_reason  VARCHAR(255) NULL,
  cancelled_by   INT UNSIGNED NULL,
  created_by     INT UNSIGNED NULL,
  created_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_orders_code (order_code),
  KEY idx_orders_status_created (status, created_at),
  KEY idx_orders_pay_status_created (payment_status, status, created_at),
  KEY idx_orders_created_at (created_at),
  KEY idx_orders_created_by (created_by),
  KEY idx_orders_cancelled_by (cancelled_by),
  CONSTRAINT fk_orders_cancelled_by FOREIGN KEY (cancelled_by)
    REFERENCES users (id) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_orders_created_by FOREIGN KEY (created_by)
    REFERENCES users (id) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT chk_orders_total CHECK (total >= 0),
  CONSTRAINT chk_orders_table_number CHECK (table_number IS NULL OR table_number >= 1)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 6. Order Details (Snapshot nama & harga menu saat transaksi)
-- ----------------------------------------------------------------------------
CREATE TABLE order_details (
  id        BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  order_id  BIGINT UNSIGNED NOT NULL,
  menu_id   INT UNSIGNED NOT NULL,
  menu_name VARCHAR(100) NOT NULL,
  price     INT UNSIGNED NOT NULL,
  qty       SMALLINT UNSIGNED NOT NULL,
  subtotal  INT UNSIGNED NOT NULL,
  PRIMARY KEY (id),
  KEY idx_details_order (order_id),
  KEY idx_details_menu (menu_id),
  CONSTRAINT fk_details_order FOREIGN KEY (order_id)
    REFERENCES orders (id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_details_menu FOREIGN KEY (menu_id)
    REFERENCES menus (id) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT chk_details_price CHECK (price >= 0),
  CONSTRAINT chk_details_qty CHECK (qty >= 1),
  CONSTRAINT chk_details_subtotal CHECK (subtotal >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 7. Payments (Maksimal 1 record pembayaran per order)
-- ----------------------------------------------------------------------------
CREATE TABLE payments (
  id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  payment_code  VARCHAR(20) NOT NULL,
  order_id      BIGINT UNSIGNED NOT NULL,
  method        ENUM('cash','qris') NOT NULL,
  total         INT UNSIGNED NOT NULL,
  amount_paid   INT UNSIGNED NOT NULL,
  change_amount INT UNSIGNED NOT NULL DEFAULT 0,
  status        ENUM('paid','void') NOT NULL DEFAULT 'paid',
  cashier_id    INT UNSIGNED NOT NULL,
  paid_at       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  voided_at     DATETIME NULL,
  voided_by     INT UNSIGNED NULL,
  void_reason   VARCHAR(255) NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_payments_code (payment_code),
  UNIQUE KEY uq_payments_order (order_id),
  KEY idx_payments_status_paid_at (status, paid_at),
  KEY idx_payments_cashier_status_paid (cashier_id, status, paid_at),
  KEY idx_payments_paid_at (paid_at),
  KEY idx_payments_voided_by (voided_by),
  CONSTRAINT fk_payments_order FOREIGN KEY (order_id)
    REFERENCES orders (id) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_payments_cashier FOREIGN KEY (cashier_id)
    REFERENCES users (id) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_payments_voided_by FOREIGN KEY (voided_by)
    REFERENCES users (id) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT chk_payments_total CHECK (total >= 0),
  CONSTRAINT chk_payments_amount_paid CHECK (amount_paid >= 0),
  CONSTRAINT chk_payments_change_amount CHECK (change_amount >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
