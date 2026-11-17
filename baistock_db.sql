-- ========================================================
-- phpMyAdmin SQL Dump
-- BaiStock Sari-Sari Inventory & Storage Management System
-- Database: `baistock_db`
-- Generated: 2026-11-17 07:59:27
-- ========================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

CREATE DATABASE IF NOT EXISTS `baistock_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `baistock_db`;

-- --------------------------------------------------------
-- Table structure for table `categories`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `category_name` varchar(100) NOT NULL,
  `description` text,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `category_name` (`category_name`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table `categories`
INSERT INTO `categories` (`id`, `category_name`, `description`, `created_at`) VALUES ('2', 'Snacks', 'Chips, crackers, and packed snacks', '2026-11-17 05:44:36');
INSERT INTO `categories` (`id`, `category_name`, `description`, `created_at`) VALUES ('5', 'Canned Goods', 'Canned fish, meat, and processed food', '2026-11-17 05:44:36');
INSERT INTO `categories` (`id`, `category_name`, `description`, `created_at`) VALUES ('7', 'Fruits', 'Fruits Category', '2026-11-17 07:24:45');
INSERT INTO `categories` (`id`, `category_name`, `description`, `created_at`) VALUES ('8', 'Vegetables', 'Vegetables Category', '2026-11-17 07:24:45');
INSERT INTO `categories` (`id`, `category_name`, `description`, `created_at`) VALUES ('9', 'Groceries', 'Groceries Category', '2026-11-17 07:24:45');

-- --------------------------------------------------------
-- Table structure for table `deliveries`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `deliveries`;
CREATE TABLE `deliveries` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tracking_number` varchar(50) NOT NULL,
  `supplier` varchar(255) NOT NULL,
  `expected_date` datetime NOT NULL,
  `received_date` datetime DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'Pending',
  `is_received` tinyint(1) NOT NULL DEFAULT '0',
  `total_cost` decimal(10,2) NOT NULL DEFAULT '0.00',
  `notes` text,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tracking_number` (`tracking_number`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table `deliveries`
INSERT INTO `deliveries` (`id`, `tracking_number`, `supplier`, `expected_date`, `received_date`, `status`, `is_received`, `total_cost`, `notes`, `created_at`) VALUES ('1', 'DEL-20261117-5768', 'AMSLMASDL', '2026-11-19 06:34:30', '2026-11-17 06:35:20', 'Delivered', '0', '300.00', '5 CASE SOFTDRINKS', '2026-11-17 06:34:30');
INSERT INTO `deliveries` (`id`, `tracking_number`, `supplier`, `expected_date`, `received_date`, `status`, `is_received`, `total_cost`, `notes`, `created_at`) VALUES ('2', 'DEL-20261117-07D6', '121NKQWJN', '2026-11-19 06:35:40', '2026-11-17 06:35:50', 'Delivered', '0', '1000.00', 'AS DM,NASKLDNLKASMDLJASKDJAOSJDOAJSKLDNASKJDNKJASNDKJASNDKJASNDKJNASKJDNKJASNDKJASNDKJNASKJNDKJASNDKJASNDKJNASKJDNASKJNDKJASND', '2026-11-17 06:35:40');
INSERT INTO `deliveries` (`id`, `tracking_number`, `supplier`, `expected_date`, `received_date`, `status`, `is_received`, `total_cost`, `notes`, `created_at`) VALUES ('3', 'DEL-20261117-5189', 'ASD,LMALSDMA,SD', '2026-11-19 06:38:29', NULL, 'Pending', '0', '1000.00', 'AA', '2026-11-17 06:38:29');
INSERT INTO `deliveries` (`id`, `tracking_number`, `supplier`, `expected_date`, `received_date`, `status`, `is_received`, `total_cost`, `notes`, `created_at`) VALUES ('4', 'DEL-20261117-EA86', 'Universal Robina Corp', '2026-11-19 06:46:38', '2026-11-17 06:46:38', 'Delivered', '1', '500.00', 'Weekly shipment batch 401', '2026-11-17 06:46:38');

-- --------------------------------------------------------
-- Table structure for table `delivery_items`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `delivery_items`;
CREATE TABLE `delivery_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `delivery_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned DEFAULT NULL,
  `product_name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `unit_cost` decimal(10,2) NOT NULL DEFAULT '0.00',
  `subtotal` decimal(10,2) NOT NULL DEFAULT '0.00',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `delivery_id` (`delivery_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `fk_delivery_items_delivery` FOREIGN KEY (`delivery_id`) REFERENCES `deliveries` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_delivery_items_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `delivery_items`
INSERT INTO `delivery_items` (`id`, `delivery_id`, `product_id`, `product_name`, `quantity`, `unit_cost`, `subtotal`, `created_at`) VALUES ('1', '4', '1', 'coca cola', '20', '20.00', '400.00', '2026-11-17 06:46:38');

-- --------------------------------------------------------
-- Table structure for table `notifications`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `action_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `action_text` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `notifications`
INSERT INTO `notifications` (`id`, `title`, `message`, `type`, `action_url`, `action_text`, `is_read`, `created_at`) VALUES ('1', 'Shipment #DEL-20261117-5768 (Delivered)', 'Supplier: AMSLMASDL — Total Invoice: ₱300.00 (Delivered, awaiting inventory check-in)', 'delivery', '/delivery', 'View Shipment', '1', '2026-11-17 07:39:37');
INSERT INTO `notifications` (`id`, `title`, `message`, `type`, `action_url`, `action_text`, `is_read`, `created_at`) VALUES ('2', 'Shipment #DEL-20261117-07D6 (Delivered)', 'Supplier: 121NKQWJN — Total Invoice: ₱1,000.00 (Delivered, awaiting inventory check-in)', 'delivery', '/delivery', 'View Shipment', '1', '2026-11-17 07:39:37');
INSERT INTO `notifications` (`id`, `title`, `message`, `type`, `action_url`, `action_text`, `is_read`, `created_at`) VALUES ('3', 'Shipment #DEL-20261117-5189 (Pending)', 'Supplier: ASD,LMALSDMA,SD — Total Invoice: ₱1,000.00', 'delivery', '/delivery', 'View Shipment', '1', '2026-11-17 07:39:37');
INSERT INTO `notifications` (`id`, `title`, `message`, `type`, `action_url`, `action_text`, `is_read`, `created_at`) VALUES ('4', 'Shipment #DEL-20261117-5768 (Delivered)', 'Supplier: AMSLMASDL — Total Invoice: ₱300.00 (Delivered, awaiting inventory check-in)', 'delivery', '/delivery', 'View Shipment', '1', '2026-11-17 07:39:39');
INSERT INTO `notifications` (`id`, `title`, `message`, `type`, `action_url`, `action_text`, `is_read`, `created_at`) VALUES ('5', 'Shipment #DEL-20261117-07D6 (Delivered)', 'Supplier: 121NKQWJN — Total Invoice: ₱1,000.00 (Delivered, awaiting inventory check-in)', 'delivery', '/delivery', 'View Shipment', '1', '2026-11-17 07:39:39');
INSERT INTO `notifications` (`id`, `title`, `message`, `type`, `action_url`, `action_text`, `is_read`, `created_at`) VALUES ('6', 'Shipment #DEL-20261117-5189 (Pending)', 'Supplier: ASD,LMALSDMA,SD — Total Invoice: ₱1,000.00', 'delivery', '/delivery', 'View Shipment', '1', '2026-11-17 07:39:39');
INSERT INTO `notifications` (`id`, `title`, `message`, `type`, `action_url`, `action_text`, `is_read`, `created_at`) VALUES ('7', 'Shipment #DEL-20261117-5768 (Delivered)', 'Supplier: AMSLMASDL — Total Invoice: ₱300.00 (Delivered, awaiting inventory check-in)', 'delivery', '/delivery', 'View Shipment', '1', '2026-11-17 07:39:46');
INSERT INTO `notifications` (`id`, `title`, `message`, `type`, `action_url`, `action_text`, `is_read`, `created_at`) VALUES ('8', 'Shipment #DEL-20261117-07D6 (Delivered)', 'Supplier: 121NKQWJN — Total Invoice: ₱1,000.00 (Delivered, awaiting inventory check-in)', 'delivery', '/delivery', 'View Shipment', '1', '2026-11-17 07:39:46');
INSERT INTO `notifications` (`id`, `title`, `message`, `type`, `action_url`, `action_text`, `is_read`, `created_at`) VALUES ('9', 'Shipment #DEL-20261117-5189 (Pending)', 'Supplier: ASD,LMALSDMA,SD — Total Invoice: ₱1,000.00', 'delivery', '/delivery', 'View Shipment', '1', '2026-11-17 07:39:46');
INSERT INTO `notifications` (`id`, `title`, `message`, `type`, `action_url`, `action_text`, `is_read`, `created_at`) VALUES ('10', 'Shipment #DEL-20261117-5768 (Delivered)', 'Supplier: AMSLMASDL — Total Invoice: ₱300.00 (Delivered, awaiting inventory check-in)', 'delivery', '/delivery', 'View Shipment', '1', '2026-11-17 07:39:48');
INSERT INTO `notifications` (`id`, `title`, `message`, `type`, `action_url`, `action_text`, `is_read`, `created_at`) VALUES ('11', 'Shipment #DEL-20261117-07D6 (Delivered)', 'Supplier: 121NKQWJN — Total Invoice: ₱1,000.00 (Delivered, awaiting inventory check-in)', 'delivery', '/delivery', 'View Shipment', '1', '2026-11-17 07:39:48');
INSERT INTO `notifications` (`id`, `title`, `message`, `type`, `action_url`, `action_text`, `is_read`, `created_at`) VALUES ('12', 'Shipment #DEL-20261117-5189 (Pending)', 'Supplier: ASD,LMALSDMA,SD — Total Invoice: ₱1,000.00', 'delivery', '/delivery', 'View Shipment', '1', '2026-11-17 07:39:48');
INSERT INTO `notifications` (`id`, `title`, `message`, `type`, `action_url`, `action_text`, `is_read`, `created_at`) VALUES ('13', 'Shipment #DEL-20261117-5768 (Delivered)', 'Supplier: AMSLMASDL — Total Invoice: ₱300.00 (Delivered, awaiting inventory check-in)', 'delivery', '/delivery', 'View Shipment', '1', '2026-11-17 07:39:49');
INSERT INTO `notifications` (`id`, `title`, `message`, `type`, `action_url`, `action_text`, `is_read`, `created_at`) VALUES ('14', 'Shipment #DEL-20261117-07D6 (Delivered)', 'Supplier: 121NKQWJN — Total Invoice: ₱1,000.00 (Delivered, awaiting inventory check-in)', 'delivery', '/delivery', 'View Shipment', '1', '2026-11-17 07:39:49');
INSERT INTO `notifications` (`id`, `title`, `message`, `type`, `action_url`, `action_text`, `is_read`, `created_at`) VALUES ('15', 'Shipment #DEL-20261117-5189 (Pending)', 'Supplier: ASD,LMALSDMA,SD — Total Invoice: ₱1,000.00', 'delivery', '/delivery', 'View Shipment', '1', '2026-11-17 07:39:49');

-- --------------------------------------------------------
-- Table structure for table `products`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `category_id` bigint unsigned DEFAULT NULL,
  `product_name` varchar(150) NOT NULL,
  `description` text,
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `quantity` int unsigned NOT NULL DEFAULT '0',
  `minimum_stock` int unsigned NOT NULL DEFAULT '0',
  `unit` varchar(30) NOT NULL DEFAULT 'piece',
  `image_path` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_products_name` (`product_name`),
  KEY `idx_products_category` (`category_id`),
  KEY `idx_products_stock` (`quantity`,`minimum_stock`),
  CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table `products`
INSERT INTO `products` (`id`, `category_id`, `product_name`, `description`, `price`, `quantity`, `minimum_stock`, `unit`, `image_path`, `created_at`, `updated_at`) VALUES ('1', '9', 'coca cola', '', '11.00', '104', '10', 'piece', '825260869_1108376151881649_5949357854194977838_n-6afbf0422118d.jpg', '2026-11-17 06:12:18', '2026-11-17 07:24:45');

-- --------------------------------------------------------
-- Table structure for table `sale_items`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `sale_items`;
CREATE TABLE `sale_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `sale_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned DEFAULT NULL,
  `quantity` int unsigned NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_sale_items_sale` (`sale_id`),
  KEY `idx_sale_items_product` (`product_id`),
  CONSTRAINT `fk_sale_items_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_sale_items_sale` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table `sale_items`
INSERT INTO `sale_items` (`id`, `sale_id`, `product_id`, `quantity`, `unit_price`, `subtotal`) VALUES ('1', '1', '1', '7', '11.00', '77.00');
INSERT INTO `sale_items` (`id`, `sale_id`, `product_id`, `quantity`, `unit_price`, `subtotal`) VALUES ('2', '2', '1', '5', '11.00', '55.00');
INSERT INTO `sale_items` (`id`, `sale_id`, `product_id`, `quantity`, `unit_price`, `subtotal`) VALUES ('3', '3', '1', '4', '11.00', '44.00');

-- --------------------------------------------------------
-- Table structure for table `sales`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `sales`;
CREATE TABLE `sales` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `receipt_number` varchar(50) NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `amount_received` decimal(10,2) NOT NULL DEFAULT '0.00',
  `change_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `receipt_number` (`receipt_number`),
  KEY `fk_sales_user` (`user_id`),
  KEY `idx_sales_created_at` (`created_at`),
  CONSTRAINT `fk_sales_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table `sales`
INSERT INTO `sales` (`id`, `receipt_number`, `user_id`, `total_amount`, `amount_received`, `change_amount`, `created_at`) VALUES ('1', 'REC-20261117-6788', '1', '77.00', '77.00', '0.00', '2026-11-17 06:13:28');
INSERT INTO `sales` (`id`, `receipt_number`, `user_id`, `total_amount`, `amount_received`, `change_amount`, `created_at`) VALUES ('2', 'REC-20261117-E165', '1', '55.00', '100.00', '45.00', '2026-11-17 06:14:35');
INSERT INTO `sales` (`id`, `receipt_number`, `user_id`, `total_amount`, `amount_received`, `change_amount`, `created_at`) VALUES ('3', 'REC-20261117-1EDA', '1', '44.00', '99.00', '55.00', '2026-11-17 06:17:49');

-- --------------------------------------------------------
-- Table structure for table `settings`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table `settings`
INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `updated_at`) VALUES ('1', 'store_name', 'BaiStock Neighborhood Store', '2026-11-17 05:44:35');
INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `updated_at`) VALUES ('2', 'currency', 'PHP', '2026-11-17 05:44:35');
INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `updated_at`) VALUES ('3', 'currency_symbol', '₱', '2026-11-17 05:44:35');
INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `updated_at`) VALUES ('4', 'store_address', 'Corner Rizal & Luna St., Cebu City', '2026-11-17 05:44:35');
INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `updated_at`) VALUES ('5', 'store_contact', '+63 917 123 4567', '2026-11-17 05:44:35');
INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `updated_at`) VALUES ('6', 'default_min_stock', '10', '2026-11-17 05:44:35');
INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `updated_at`) VALUES ('7', 'receipt_footer', 'Salamat sa pagpalit! Mabuhay!', '2026-11-17 05:44:35');

-- --------------------------------------------------------
-- Table structure for table `stock_movements`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `stock_movements`;
CREATE TABLE `stock_movements` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint unsigned DEFAULT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `movement_type` enum('stock_in','stock_out','sale','adjustment') NOT NULL,
  `quantity` int unsigned NOT NULL,
  `remarks` text,
  `sale_id` bigint unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_stock_movements_user` (`user_id`),
  KEY `fk_stock_movements_sale` (`sale_id`),
  KEY `idx_stock_movements_product` (`product_id`),
  KEY `idx_stock_movements_created_at` (`created_at`),
  CONSTRAINT `fk_stock_movements_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_stock_movements_sale` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_stock_movements_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table `stock_movements`
INSERT INTO `stock_movements` (`id`, `product_id`, `user_id`, `movement_type`, `quantity`, `remarks`, `sale_id`, `created_at`) VALUES ('1', '1', '1', 'stock_in', '100', 'Initial Stock', NULL, '2026-11-17 06:12:18');
INSERT INTO `stock_movements` (`id`, `product_id`, `user_id`, `movement_type`, `quantity`, `remarks`, `sale_id`, `created_at`) VALUES ('2', '1', '1', 'sale', '7', 'Sale #REC-20261117-6788', '1', '2026-11-17 06:13:28');
INSERT INTO `stock_movements` (`id`, `product_id`, `user_id`, `movement_type`, `quantity`, `remarks`, `sale_id`, `created_at`) VALUES ('3', '1', '1', 'sale', '5', 'Sale #REC-20261117-E165', '2', '2026-11-17 06:14:35');
INSERT INTO `stock_movements` (`id`, `product_id`, `user_id`, `movement_type`, `quantity`, `remarks`, `sale_id`, `created_at`) VALUES ('4', '1', '1', 'sale', '4', 'Sale #REC-20261117-1EDA', '3', '2026-11-17 06:17:49');

-- --------------------------------------------------------
-- Table structure for table `users`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `full_name` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','owner','staff') NOT NULL DEFAULT 'staff',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table `users`
INSERT INTO `users` (`id`, `full_name`, `username`, `email`, `password`, `role`, `status`, `created_at`, `updated_at`) VALUES ('1', 'System Administrator', 'admin', 'admin@baistock.com', '$2y$13$bJOgwe/y86duRK0B/ihj4OdISPglN.MmUzZgqJHOj2SK610WTS.eq', 'admin', 'active', '2026-11-17 05:57:42', '2026-11-17 05:57:42');
INSERT INTO `users` (`id`, `full_name`, `username`, `email`, `password`, `role`, `status`, `created_at`, `updated_at`) VALUES ('2', 'kim', 'staff1@gmail.com', 'kimzkie@0978', '$2y$13$qj0EUDZSfFynzRQWmRpJFO1TpYj8dRxyeWFxCpc6iB2E7hmaSX0RK', 'staff', 'active', '2026-11-17 06:07:09', '2026-11-17 06:07:09');

SET FOREIGN_KEY_CHECKS = 1;
