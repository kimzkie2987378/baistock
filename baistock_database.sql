-- ============================================================
-- BAISTOCK DATABASE
-- MySQL 8.0 | Import using phpMyAdmin > SQL tab or Import tab
-- Creates the database and its seven initial tables.
-- ============================================================

CREATE DATABASE IF NOT EXISTS baistock_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE baistock_db;

-- 1. USERS: accounts that can log in to BaiStock
CREATE TABLE IF NOT EXISTS users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'owner', 'staff') NOT NULL DEFAULT 'staff',
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2. CATEGORIES: groups used to organize products
CREATE TABLE IF NOT EXISTS categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 3. PRODUCTS: product details and current inventory balance
CREATE TABLE IF NOT EXISTS products (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id BIGINT UNSIGNED NULL,
    product_name VARCHAR(150) NOT NULL,
    description TEXT NULL,
    price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    quantity INT UNSIGNED NOT NULL DEFAULT 0,
    minimum_stock INT UNSIGNED NOT NULL DEFAULT 0,
    unit VARCHAR(30) NOT NULL DEFAULT 'piece',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_products_category
      FOREIGN KEY (category_id) REFERENCES categories(id)
      ON UPDATE CASCADE ON DELETE SET NULL,
    INDEX idx_products_name (product_name),
    INDEX idx_products_category (category_id),
    INDEX idx_products_stock (quantity, minimum_stock)
) ENGINE=InnoDB;

-- 4. SALES: one record for each completed sale/receipt
CREATE TABLE IF NOT EXISTS sales (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    receipt_number VARCHAR(50) NOT NULL UNIQUE,
    user_id BIGINT UNSIGNED NULL,
    total_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    amount_received DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    change_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_sales_user
      FOREIGN KEY (user_id) REFERENCES users(id)
      ON UPDATE CASCADE ON DELETE SET NULL,
    INDEX idx_sales_created_at (created_at)
) ENGINE=InnoDB;

-- 5. SALE_ITEMS: products and quantities included in each sale
CREATE TABLE IF NOT EXISTS sale_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sale_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NULL,
    quantity INT UNSIGNED NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    CONSTRAINT fk_sale_items_sale
      FOREIGN KEY (sale_id) REFERENCES sales(id)
      ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_sale_items_product
      FOREIGN KEY (product_id) REFERENCES products(id)
      ON UPDATE CASCADE ON DELETE SET NULL,
    INDEX idx_sale_items_product (product_id)
) ENGINE=InnoDB;

-- 6. STOCK_MOVEMENTS: history of stock-ins, stock-outs, sales, and adjustments
-- quantity is stored as a positive amount; movement_type identifies the action.
CREATE TABLE IF NOT EXISTS stock_movements (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id BIGINT UNSIGNED NULL,
    user_id BIGINT UNSIGNED NULL,
    movement_type ENUM('stock_in', 'stock_out', 'sale', 'adjustment') NOT NULL,
    quantity INT UNSIGNED NOT NULL,
    remarks TEXT NULL,
    sale_id BIGINT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_stock_movements_product
      FOREIGN KEY (product_id) REFERENCES products(id)
      ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_stock_movements_user
      FOREIGN KEY (user_id) REFERENCES users(id)
      ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_stock_movements_sale
      FOREIGN KEY (sale_id) REFERENCES sales(id)
      ON UPDATE CASCADE ON DELETE SET NULL,
    INDEX idx_stock_movements_product (product_id),
    INDEX idx_stock_movements_created_at (created_at)
) ENGINE=InnoDB;

-- 7. SETTINGS: configurable store/system information
CREATE TABLE IF NOT EXISTS settings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT NULL,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Optional starter settings (safe to run again)
INSERT INTO settings (setting_key, setting_value)
VALUES
    ('store_name', 'BaiStock'),
    ('currency', 'PHP'),
    ('currency_symbol', '₱')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);

-- NOTE: No default admin account is inserted.
-- Create the first account through your Symfony application and store
-- its password using Symfony's password hasher (never plain text).

-- Verify the tables after import:
SHOW TABLES;
