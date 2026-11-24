-- ========================================================
-- phpMyAdmin SQL Dump
-- BaiStock Sari-Sari Inventory & Storage Management System
-- Database: `baistock_db`
-- Generated: 2026-11-24 12:59:04
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
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table `products`
INSERT INTO `products` (`id`, `category_id`, `product_name`, `description`, `price`, `quantity`, `minimum_stock`, `unit`, `image_path`, `created_at`, `updated_at`) VALUES ('1', '9', 'coca cola', '', '11.00', '104', '10', 'piece', '825260869_1108376151881649_5949357854194977838_n-6afbf0422118d.jpg', '2026-11-17 06:12:18', '2026-11-17 07:24:45');
INSERT INTO `products` (`id`, `category_id`, `product_name`, `description`, `price`, `quantity`, `minimum_stock`, `unit`, `image_path`, `created_at`, `updated_at`) VALUES ('2', '8', 'Fresh Carrots', 'Fresh crisp orange carrots, rich in Vitamin A and great for everyday dishes.', '80.00', '35', '10', 'kg', 'carrot-6b05893c55d34.jpg', '2026-11-24 12:55:25', '2026-11-24 12:55:25');
INSERT INTO `products` (`id`, `category_id`, `product_name`, `description`, `price`, `quantity`, `minimum_stock`, `unit`, `image_path`, `created_at`, `updated_at`) VALUES ('3', '8', 'White Radish (Labanos)', 'Crisp white radish (labanos), essential for authentic Filipino sinigang.', '65.00', '25', '8', 'kg', 'radish-6b05893f76109.jpg', '2026-11-24 12:55:31', '2026-11-24 12:55:31');
INSERT INTO `products` (`id`, `category_id`, `product_name`, `description`, `price`, `quantity`, `minimum_stock`, `unit`, `image_path`, `created_at`, `updated_at`) VALUES ('4', '8', 'Red Onion (Sibuyas Tagalog)', 'Pungent flavorful red onions, essential culinary base for sauteing.', '120.00', '50', '15', 'kg', 'red-onion-6b058946d317e.JPG', '2026-11-24 12:55:37', '2026-11-24 12:55:37');
INSERT INTO `products` (`id`, `category_id`, `product_name`, `description`, `price`, `quantity`, `minimum_stock`, `unit`, `image_path`, `created_at`, `updated_at`) VALUES ('5', '8', 'Bell Pepper (Green & Red)', 'Sweet crisp bell peppers for stir-fries, menudo, and salads.', '140.00', '20', '6', 'kg', 'bell-pepper-6b05894bedbfd.jpg', '2026-11-24 12:55:42', '2026-11-24 12:55:42');
INSERT INTO `products` (`id`, `category_id`, `product_name`, `description`, `price`, `quantity`, `minimum_stock`, `unit`, `image_path`, `created_at`, `updated_at`) VALUES ('6', '8', 'Native Garlic (Bawang)', 'Strong aromatic garlic bulbs, primary spice for all Filipino dishes.', '130.00', '45', '12', 'kg', 'garlic-6b05894fc5dc2.jpg', '2026-11-24 12:55:47', '2026-11-24 12:55:47');
INSERT INTO `products` (`id`, `category_id`, `product_name`, `description`, `price`, `quantity`, `minimum_stock`, `unit`, `image_path`, `created_at`, `updated_at`) VALUES ('7', '8', 'String Beans (Sitaw)', 'Fresh tender green string beans (sitaw), great for adobo and kare-kare.', '35.00', '30', '8', 'bundle', 'string-beans-6b058954a9001.jpg', '2026-11-24 12:55:50', '2026-11-24 12:55:50');
INSERT INTO `products` (`id`, `category_id`, `product_name`, `description`, `price`, `quantity`, `minimum_stock`, `unit`, `image_path`, `created_at`, `updated_at`) VALUES ('8', '8', 'White Onion (Sweet Onion)', 'Mild sweet white onion, perfect for burgers, salads, and grilling.', '110.00', '28', '8', 'kg', 'white-onion-6b058958be063.jpg', '2026-11-24 12:55:55', '2026-11-24 12:55:55');
INSERT INTO `products` (`id`, `category_id`, `product_name`, `description`, `price`, `quantity`, `minimum_stock`, `unit`, `image_path`, `created_at`, `updated_at`) VALUES ('9', '8', 'Fresh Tomato (Kamatis)', 'Juicy red cooking tomatoes, staple ingredient for sauces and sinigang.', '75.00', '45', '10', 'kg', 'tomato-6b05895e7cf30.jpg', '2026-11-24 12:56:00', '2026-11-24 12:56:00');
INSERT INTO `products` (`id`, `category_id`, `product_name`, `description`, `price`, `quantity`, `minimum_stock`, `unit`, `image_path`, `created_at`, `updated_at`) VALUES ('10', '8', 'Fresh Potato (Patatas)', 'All-purpose starchy cooking potatoes for adobo, caldereta, and fries.', '95.00', '55', '15', 'kg', 'potato-6b0589625e919.jpg', '2026-11-24 12:56:06', '2026-11-24 12:56:06');
INSERT INTO `products` (`id`, `category_id`, `product_name`, `description`, `price`, `quantity`, `minimum_stock`, `unit`, `image_path`, `created_at`, `updated_at`) VALUES ('11', '8', 'Green Cucumber (Pipino)', 'Cool crisp cucumbers, refreshing in vinegar salads and side dishes.', '60.00', '30', '8', 'kg', 'cucumber-6b05896808bba.jpg', '2026-11-24 12:56:10', '2026-11-24 12:56:10');
INSERT INTO `products` (`id`, `category_id`, `product_name`, `description`, `price`, `quantity`, `minimum_stock`, `unit`, `image_path`, `created_at`, `updated_at`) VALUES ('12', '8', 'Eggplant (Talong)', 'Smooth purple eggplant, delicious for tortang talong and pinakbet.', '70.00', '32', '8', 'kg', 'eggplant-6b05896cbe0c5.JPG', '2026-11-24 12:56:15', '2026-11-24 12:56:15');
INSERT INTO `products` (`id`, `category_id`, `product_name`, `description`, `price`, `quantity`, `minimum_stock`, `unit`, `image_path`, `created_at`, `updated_at`) VALUES ('13', '8', 'Green Cabbage (Repolyo)', 'Dense crisp head of green cabbage, ideal for nilaga, pansit, and chopsuey.', '85.00', '24', '6', 'head', 'cabbage-6b058972b8d6d.jpg', '2026-11-24 12:56:24', '2026-11-24 12:56:24');
INSERT INTO `products` (`id`, `category_id`, `product_name`, `description`, `price`, `quantity`, `minimum_stock`, `unit`, `image_path`, `created_at`, `updated_at`) VALUES ('14', '8', 'Chinese Pechay (Wongbok)', 'Tender Napa cabbage / Chinese pechay for soups and stir-fried greens.', '75.00', '22', '6', 'kg', 'chinese-pechay-6b05897c1eb25.png', '2026-11-24 12:56:37', '2026-11-24 12:56:37');
INSERT INTO `products` (`id`, `category_id`, `product_name`, `description`, `price`, `quantity`, `minimum_stock`, `unit`, `image_path`, `created_at`, `updated_at`) VALUES ('15', '8', 'Native Pechay', 'Fresh green leafy native pechay bundle for pochero and beef soups.', '25.00', '40', '10', 'bundle', 'native-pechay-6b05898804ef9.jpg', '2026-11-24 12:56:43', '2026-11-24 12:56:43');
INSERT INTO `products` (`id`, `category_id`, `product_name`, `description`, `price`, `quantity`, `minimum_stock`, `unit`, `image_path`, `created_at`, `updated_at`) VALUES ('16', '8', 'Bitter Gourd (Ampalaya)', 'Fresh green bitter gourd (ampalaya), healthy for ginisang ampalaya with egg.', '90.00', '26', '6', 'kg', 'ampalaya-6b05898cd235f.jpg', '2026-11-24 12:56:47', '2026-11-24 12:56:47');
INSERT INTO `products` (`id`, `category_id`, `product_name`, `description`, `price`, `quantity`, `minimum_stock`, `unit`, `image_path`, `created_at`, `updated_at`) VALUES ('17', '7', 'Red Fuji Apple', 'Sweet crisp and juicy imported Fuji apples.', '25.00', '60', '15', 'piece', 'apple-6b0589914a200.jpg', '2026-11-24 12:56:51', '2026-11-24 12:56:51');
INSERT INTO `products` (`id`, `category_id`, `product_name`, `description`, `price`, `quantity`, `minimum_stock`, `unit`, `image_path`, `created_at`, `updated_at`) VALUES ('18', '7', 'Navel Orange', 'Sweet seedless navel orange, packed with Vitamin C and refreshing juice.', '30.00', '50', '12', 'piece', 'orange-6b05899549564.jpg', '2026-11-24 12:56:56', '2026-11-24 12:56:56');
INSERT INTO `products` (`id`, `category_id`, `product_name`, `description`, `price`, `quantity`, `minimum_stock`, `unit`, `image_path`, `created_at`, `updated_at`) VALUES ('19', '7', 'Sweet Pineapple (Pinya)', 'Fresh ripe golden sweet pineapple, sweet and tropical.', '75.00', '18', '5', 'piece', 'pineapple-6b05899ab550a.jpg', '2026-11-24 12:57:02', '2026-11-24 12:57:02');
INSERT INTO `products` (`id`, `category_id`, `product_name`, `description`, `price`, `quantity`, `minimum_stock`, `unit`, `image_path`, `created_at`, `updated_at`) VALUES ('20', '7', 'Century Pear', 'Crunchy sweet Asian century pear with refreshing juice.', '35.00', '40', '10', 'piece', 'pear-6b0589a135b15.jpg', '2026-11-24 12:57:08', '2026-11-24 12:57:08');
INSERT INTO `products` (`id`, `category_id`, `product_name`, `description`, `price`, `quantity`, `minimum_stock`, `unit`, `image_path`, `created_at`, `updated_at`) VALUES ('21', '7', 'Carabao Mango (Ripe)', 'World-famous Philippine Carabao mango, rich sweet aroma and velvety texture.', '160.00', '30', '8', 'kg', 'mango-6b0589a6012e5.jpg', '2026-11-24 12:57:11', '2026-11-24 12:57:11');
INSERT INTO `products` (`id`, `category_id`, `product_name`, `description`, `price`, `quantity`, `minimum_stock`, `unit`, `image_path`, `created_at`, `updated_at`) VALUES ('22', '7', 'Fresh Young Coconut (Buko)', 'Fresh young green coconut with sweet water and tender buko meat.', '40.00', '25', '6', 'piece', 'coconut-6b0589aa88581.jpg', '2026-11-24 12:57:17', '2026-11-24 12:57:17');
INSERT INTO `products` (`id`, `category_id`, `product_name`, `description`, `price`, `quantity`, `minimum_stock`, `unit`, `image_path`, `created_at`, `updated_at`) VALUES ('23', '7', 'Lakatan Banana', 'Golden sweet Lakatan bananas, rich in potassium and energy.', '70.00', '45', '12', 'kg', 'banana-6b0589af812ec.jpg', '2026-11-24 12:57:22', '2026-11-24 12:57:22');

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
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table `stock_movements`
INSERT INTO `stock_movements` (`id`, `product_id`, `user_id`, `movement_type`, `quantity`, `remarks`, `sale_id`, `created_at`) VALUES ('1', '1', '1', 'stock_in', '100', 'Initial Stock', NULL, '2026-11-17 06:12:18');
INSERT INTO `stock_movements` (`id`, `product_id`, `user_id`, `movement_type`, `quantity`, `remarks`, `sale_id`, `created_at`) VALUES ('2', '1', '1', 'sale', '7', 'Sale #REC-20261117-6788', '1', '2026-11-17 06:13:28');
INSERT INTO `stock_movements` (`id`, `product_id`, `user_id`, `movement_type`, `quantity`, `remarks`, `sale_id`, `created_at`) VALUES ('3', '1', '1', 'sale', '5', 'Sale #REC-20261117-E165', '2', '2026-11-17 06:14:35');
INSERT INTO `stock_movements` (`id`, `product_id`, `user_id`, `movement_type`, `quantity`, `remarks`, `sale_id`, `created_at`) VALUES ('4', '1', '1', 'sale', '4', 'Sale #REC-20261117-1EDA', '3', '2026-11-17 06:17:49');
INSERT INTO `stock_movements` (`id`, `product_id`, `user_id`, `movement_type`, `quantity`, `remarks`, `sale_id`, `created_at`) VALUES ('5', '2', '1', 'stock_in', '35', 'Initial Stock Batch', NULL, '2026-11-24 12:55:25');
INSERT INTO `stock_movements` (`id`, `product_id`, `user_id`, `movement_type`, `quantity`, `remarks`, `sale_id`, `created_at`) VALUES ('6', '3', '1', 'stock_in', '25', 'Initial Stock Batch', NULL, '2026-11-24 12:55:31');
INSERT INTO `stock_movements` (`id`, `product_id`, `user_id`, `movement_type`, `quantity`, `remarks`, `sale_id`, `created_at`) VALUES ('7', '4', '1', 'stock_in', '50', 'Initial Stock Batch', NULL, '2026-11-24 12:55:37');
INSERT INTO `stock_movements` (`id`, `product_id`, `user_id`, `movement_type`, `quantity`, `remarks`, `sale_id`, `created_at`) VALUES ('8', '5', '1', 'stock_in', '20', 'Initial Stock Batch', NULL, '2026-11-24 12:55:42');
INSERT INTO `stock_movements` (`id`, `product_id`, `user_id`, `movement_type`, `quantity`, `remarks`, `sale_id`, `created_at`) VALUES ('9', '6', '1', 'stock_in', '45', 'Initial Stock Batch', NULL, '2026-11-24 12:55:47');
INSERT INTO `stock_movements` (`id`, `product_id`, `user_id`, `movement_type`, `quantity`, `remarks`, `sale_id`, `created_at`) VALUES ('10', '7', '1', 'stock_in', '30', 'Initial Stock Batch', NULL, '2026-11-24 12:55:50');
INSERT INTO `stock_movements` (`id`, `product_id`, `user_id`, `movement_type`, `quantity`, `remarks`, `sale_id`, `created_at`) VALUES ('11', '8', '1', 'stock_in', '28', 'Initial Stock Batch', NULL, '2026-11-24 12:55:55');
INSERT INTO `stock_movements` (`id`, `product_id`, `user_id`, `movement_type`, `quantity`, `remarks`, `sale_id`, `created_at`) VALUES ('12', '9', '1', 'stock_in', '45', 'Initial Stock Batch', NULL, '2026-11-24 12:56:00');
INSERT INTO `stock_movements` (`id`, `product_id`, `user_id`, `movement_type`, `quantity`, `remarks`, `sale_id`, `created_at`) VALUES ('13', '10', '1', 'stock_in', '55', 'Initial Stock Batch', NULL, '2026-11-24 12:56:06');
INSERT INTO `stock_movements` (`id`, `product_id`, `user_id`, `movement_type`, `quantity`, `remarks`, `sale_id`, `created_at`) VALUES ('14', '11', '1', 'stock_in', '30', 'Initial Stock Batch', NULL, '2026-11-24 12:56:10');
INSERT INTO `stock_movements` (`id`, `product_id`, `user_id`, `movement_type`, `quantity`, `remarks`, `sale_id`, `created_at`) VALUES ('15', '12', '1', 'stock_in', '32', 'Initial Stock Batch', NULL, '2026-11-24 12:56:15');
INSERT INTO `stock_movements` (`id`, `product_id`, `user_id`, `movement_type`, `quantity`, `remarks`, `sale_id`, `created_at`) VALUES ('16', '13', '1', 'stock_in', '24', 'Initial Stock Batch', NULL, '2026-11-24 12:56:24');
INSERT INTO `stock_movements` (`id`, `product_id`, `user_id`, `movement_type`, `quantity`, `remarks`, `sale_id`, `created_at`) VALUES ('17', '14', '1', 'stock_in', '22', 'Initial Stock Batch', NULL, '2026-11-24 12:56:37');
INSERT INTO `stock_movements` (`id`, `product_id`, `user_id`, `movement_type`, `quantity`, `remarks`, `sale_id`, `created_at`) VALUES ('18', '15', '1', 'stock_in', '40', 'Initial Stock Batch', NULL, '2026-11-24 12:56:43');
INSERT INTO `stock_movements` (`id`, `product_id`, `user_id`, `movement_type`, `quantity`, `remarks`, `sale_id`, `created_at`) VALUES ('19', '16', '1', 'stock_in', '26', 'Initial Stock Batch', NULL, '2026-11-24 12:56:47');
INSERT INTO `stock_movements` (`id`, `product_id`, `user_id`, `movement_type`, `quantity`, `remarks`, `sale_id`, `created_at`) VALUES ('20', '17', '1', 'stock_in', '60', 'Initial Stock Batch', NULL, '2026-11-24 12:56:51');
INSERT INTO `stock_movements` (`id`, `product_id`, `user_id`, `movement_type`, `quantity`, `remarks`, `sale_id`, `created_at`) VALUES ('21', '18', '1', 'stock_in', '50', 'Initial Stock Batch', NULL, '2026-11-24 12:56:56');
INSERT INTO `stock_movements` (`id`, `product_id`, `user_id`, `movement_type`, `quantity`, `remarks`, `sale_id`, `created_at`) VALUES ('22', '19', '1', 'stock_in', '18', 'Initial Stock Batch', NULL, '2026-11-24 12:57:02');
INSERT INTO `stock_movements` (`id`, `product_id`, `user_id`, `movement_type`, `quantity`, `remarks`, `sale_id`, `created_at`) VALUES ('23', '20', '1', 'stock_in', '40', 'Initial Stock Batch', NULL, '2026-11-24 12:57:08');
INSERT INTO `stock_movements` (`id`, `product_id`, `user_id`, `movement_type`, `quantity`, `remarks`, `sale_id`, `created_at`) VALUES ('24', '21', '1', 'stock_in', '30', 'Initial Stock Batch', NULL, '2026-11-24 12:57:11');
INSERT INTO `stock_movements` (`id`, `product_id`, `user_id`, `movement_type`, `quantity`, `remarks`, `sale_id`, `created_at`) VALUES ('25', '22', '1', 'stock_in', '25', 'Initial Stock Batch', NULL, '2026-11-24 12:57:17');
INSERT INTO `stock_movements` (`id`, `product_id`, `user_id`, `movement_type`, `quantity`, `remarks`, `sale_id`, `created_at`) VALUES ('26', '23', '1', 'stock_in', '45', 'Initial Stock Batch', NULL, '2026-11-24 12:57:22');

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
