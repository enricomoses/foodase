CREATE DATABASE IF NOT EXISTS foodease
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE foodease;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS payments, order_details, orders, menus, categories, users;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name          VARCHAR(100) NOT NULL,
  username      VARCHAR(50)  NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  role          ENUM('admin','cashier') NOT NULL DEFAULT 'cashier',
  is_active     TINYINT(1) NOT NULL DEFAULT 1,
  last_login_at DATETIME NULL,
  created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_users_username (username)
) ENGINE=InnoDB;

CREATE TABLE categories (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name       VARCHAR(60) NOT NULL,
  sort_order SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_categories_name (name)
) ENGINE=InnoDB;

CREATE TABLE menus (
  id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  category_id  INT UNSIGNED NOT NULL,
  name         VARCHAR(100) NOT NULL,
  description  TEXT NULL,
  price        INT UNSIGNED NOT NULL,
  image        VARCHAR(255) NULL,
  is_available TINYINT(1) NOT NULL DEFAULT 1,
  deleted_at   DATETIME NULL,
  created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_menus_category (category_id),
  KEY idx_menus_active (deleted_at, is_available),
  CONSTRAINT fk_menus_category FOREIGN KEY (category_id)
    REFERENCES categories (id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

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
  KEY idx_orders_status (status),
  KEY idx_orders_payment_status (payment_status),
  KEY idx_orders_created_at (created_at),
  CONSTRAINT fk_orders_cancelled_by FOREIGN KEY (cancelled_by) REFERENCES users (id) ON DELETE SET NULL,
  CONSTRAINT fk_orders_created_by   FOREIGN KEY (created_by)   REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB;

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
  CONSTRAINT fk_details_order FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE CASCADE,
  CONSTRAINT fk_details_menu  FOREIGN KEY (menu_id)  REFERENCES menus (id)  ON DELETE RESTRICT
) ENGINE=InnoDB;

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
  KEY idx_payments_paid_at (paid_at),
  KEY idx_payments_cashier (cashier_id),
  CONSTRAINT fk_payments_order    FOREIGN KEY (order_id)  REFERENCES orders (id) ON DELETE RESTRICT,
  CONSTRAINT fk_payments_cashier  FOREIGN KEY (cashier_id) REFERENCES users (id) ON DELETE RESTRICT,
  CONSTRAINT fk_payments_voidedby FOREIGN KEY (voided_by)  REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB;