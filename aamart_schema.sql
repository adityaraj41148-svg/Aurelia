-- ==============================================================================
-- AA Mart - E-Commerce Production Database Schema
-- Target Database Server: MySQL 8.0+
-- Storage Engine: InnoDB
-- Character Set: utf8mb4 / Collation: utf8mb4_unicode_ci
-- Created At: 2026-08-05
-- Description: Fully normalized, production-ready relational database schema
--              including Primary Keys, Foreign Keys, Indexes, Constraints,
--              Table Comments, and Sample Seed Data.
-- ==============================================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET TIME_ZONE = "+00:00";

-- Create Database if not exists
CREATE DATABASE IF NOT EXISTS `aamart_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `aamart_db`;

-- ------------------------------------------------------------------------------
-- 1. Table: admin
-- Description: Stores system administrator credentials, access roles, and permissions.
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `admin`;
CREATE TABLE `admin` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Unique primary key for admin user',
  `full_name` VARCHAR(150) NOT NULL COMMENT 'Full display name of the administrator',
  `email` VARCHAR(150) NOT NULL COMMENT 'Unique login email address',
  `password` VARCHAR(255) NOT NULL COMMENT 'Bcrypt/Argon2 password hash',
  `role` ENUM('superadmin', 'admin', 'manager') NOT NULL DEFAULT 'admin' COMMENT 'Role access level',
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active' COMMENT 'Account status',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Record creation timestamp',
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Record last update timestamp',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_admin_email` (`email`),
  KEY `idx_admin_status` (`status`),
  KEY `idx_admin_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='System Administrators & Management Staff';

-- ------------------------------------------------------------------------------
-- 2. Table: categories
-- Description: Product categorization tree and hierarchy metadata.
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Unique primary key for category',
  `category_name` VARCHAR(100) NOT NULL COMMENT 'Human-readable category title',
  `slug` VARCHAR(120) NOT NULL COMMENT 'URL-friendly unique identifier',
  `image` VARCHAR(255) DEFAULT NULL COMMENT 'Category banner or thumbnail URL',
  `description` TEXT DEFAULT NULL COMMENT 'Detailed category overview',
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active' COMMENT 'Category publication status',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Record creation timestamp',
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Record last update timestamp',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_category_slug` (`slug`),
  KEY `idx_category_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Product Categories & Classification';

-- ------------------------------------------------------------------------------
-- 3. Table: users
-- Description: Registered customer accounts and profile information.
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Unique primary key for customer user',
  `full_name` VARCHAR(150) NOT NULL COMMENT 'Customer full name',
  `email` VARCHAR(150) NOT NULL COMMENT 'Unique email address for login and communication',
  `phone` VARCHAR(20) DEFAULT NULL COMMENT 'Customer contact telephone number',
  `password` VARCHAR(255) NOT NULL COMMENT 'Hashed password credential',
  `address` TEXT DEFAULT NULL COMMENT 'Default street address line',
  `city` VARCHAR(100) DEFAULT NULL COMMENT 'City / Municipality',
  `state` VARCHAR(100) DEFAULT NULL COMMENT 'State / Region / Province',
  `pincode` VARCHAR(20) DEFAULT NULL COMMENT 'ZIP or Postal code',
  `status` ENUM('active', 'inactive', 'banned') NOT NULL DEFAULT 'active' COMMENT 'Account standing status',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Account registration timestamp',
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Account update timestamp',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_user_email` (`email`),
  KEY `idx_user_phone` (`phone`),
  KEY `idx_user_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Registered Customers & User Profiles';

-- ------------------------------------------------------------------------------
-- 4. Table: products
-- Description: Inventory catalog containing product pricing, stock, and descriptions.
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Unique primary key for product',
  `category_id` INT UNSIGNED NOT NULL COMMENT 'Foreign key referencing categories table',
  `product_name` VARCHAR(255) NOT NULL COMMENT 'Product title',
  `slug` VARCHAR(255) NOT NULL COMMENT 'URL slug for product detail page',
  `description` TEXT DEFAULT NULL COMMENT 'Full product description',
  `price_usd` DECIMAL(10,2) NOT NULL DEFAULT '0.00' COMMENT 'Original price in USD',
  `price_inr` DECIMAL(10,2) NOT NULL DEFAULT '0.00' COMMENT 'Converted price in INR (₹)',
  `discount_price` DECIMAL(10,2) DEFAULT NULL COMMENT 'Promotional or sale price in INR',
  `stock` INT NOT NULL DEFAULT '0' COMMENT 'Available units in inventory',
  `SKU` VARCHAR(100) NOT NULL COMMENT 'Unique Stock Keeping Unit code',
  `brand` VARCHAR(100) DEFAULT NULL COMMENT 'Manufacturer or brand name',
  `image` VARCHAR(255) DEFAULT NULL COMMENT 'Primary product cover image path/URL',
  `gallery_images` JSON DEFAULT NULL COMMENT 'JSON array of additional gallery image URLs',
  `featured` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '1 = Featured product on home page, 0 = Standard',
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active' COMMENT 'Visibility status in store',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Product listing timestamp',
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Product details last modified timestamp',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_product_slug` (`slug`),
  UNIQUE KEY `uk_product_sku` (`SKU`),
  KEY `idx_product_category` (`category_id`),
  KEY `idx_product_status` (`status`),
  KEY `idx_product_featured` (`featured`),
  KEY `idx_product_brand` (`brand`),
  KEY `idx_product_price_inr` (`price_inr`),
  CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='E-Commerce Product Catalog & Inventory';

-- ------------------------------------------------------------------------------
-- 5. Table: orders
-- Description: Customer transaction orders, payment records, and shipping state.
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Unique primary key for order',
  `order_number` VARCHAR(50) NOT NULL COMMENT 'Unique customer order tracking reference',
  `user_id` INT UNSIGNED DEFAULT NULL COMMENT 'Foreign key referencing customer account (NULL for guest checkout)',
  `total_amount` DECIMAL(10,2) NOT NULL DEFAULT '0.00' COMMENT 'Grand total amount payable',
  `payment_method` VARCHAR(50) NOT NULL DEFAULT 'COD' COMMENT 'Payment gateway or method (e.g., COD, UPI, CARD)',
  `payment_status` ENUM('pending', 'paid', 'failed', 'refunded') NOT NULL DEFAULT 'pending' COMMENT 'Payment confirmation status',
  `order_status` ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') NOT NULL DEFAULT 'pending' COMMENT 'Fulfillment lifecycle status',
  `shipping_address` TEXT NOT NULL COMMENT 'Complete delivery address snippet',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Order placement timestamp',
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Order status last updated timestamp',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_order_number` (`order_number`),
  KEY `idx_orders_user` (`user_id`),
  KEY `idx_orders_order_status` (`order_status`),
  KEY `idx_orders_payment_status` (`payment_status`),
  KEY `idx_orders_created_at` (`created_at`),
  CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Customer Orders & Transaction History';

-- ------------------------------------------------------------------------------
-- 6. Table: reviews
-- Description: Product reviews, ratings (1-5 stars), and moderation workflow.
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `reviews`;
CREATE TABLE `reviews` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Unique primary key for review',
  `product_id` INT UNSIGNED NOT NULL COMMENT 'Foreign key referencing reviewed product',
  `user_id` INT UNSIGNED NOT NULL COMMENT 'Foreign key referencing reviewer customer account',
  `rating` TINYINT UNSIGNED NOT NULL COMMENT 'Rating score from 1 to 5 stars',
  `review` TEXT DEFAULT NULL COMMENT 'Detailed customer feedback text',
  `status` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'approved' COMMENT 'Moderation status',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Review submission timestamp',
  PRIMARY KEY (`id`),
  KEY `idx_reviews_product` (`product_id`),
  KEY `idx_reviews_user` (`user_id`),
  KEY `idx_reviews_status` (`status`),
  KEY `idx_reviews_rating` (`rating`),
  CONSTRAINT `chk_reviews_rating` CHECK ((`rating` >= 1 and `rating` <= 5)),
  CONSTRAINT `fk_reviews_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_reviews_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Product Reviews & Customer Ratings';

-- ------------------------------------------------------------------------------
-- 7. Table: coupons
-- Description: Promotional discount codes and cart qualification criteria.
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `coupons`;
CREATE TABLE `coupons` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Unique primary key for coupon',
  `coupon_code` VARCHAR(50) NOT NULL COMMENT 'Unique promotional code entered by user',
  `discount_type` ENUM('percentage', 'fixed') NOT NULL DEFAULT 'percentage' COMMENT 'Discount calculation mode',
  `discount_value` DECIMAL(10,2) NOT NULL DEFAULT '0.00' COMMENT 'Percentage off or fixed currency value off',
  `minimum_order` DECIMAL(10,2) NOT NULL DEFAULT '0.00' COMMENT 'Minimum cart total required to apply coupon',
  `expiry_date` DATE DEFAULT NULL COMMENT 'Expiration threshold date',
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active' COMMENT 'Coupon active status',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Coupon creation timestamp',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_coupon_code` (`coupon_code`),
  KEY `idx_coupons_status_expiry` (`status`, `expiry_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Promotional Coupons & Special Offers';

-- ------------------------------------------------------------------------------
-- 8. Table: messages
-- Description: Contact Us inquiries and customer support messages.
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `messages`;
CREATE TABLE `messages` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Unique primary key for message',
  `name` VARCHAR(100) NOT NULL COMMENT 'Sender full name',
  `email` VARCHAR(150) NOT NULL COMMENT 'Sender contact email',
  `subject` VARCHAR(255) DEFAULT NULL COMMENT 'Subject of the query',
  `message` TEXT NOT NULL COMMENT 'Message message content',
  `is_read` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '0 = Unread, 1 = Read by admin',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Message submission timestamp',
  PRIMARY KEY (`id`),
  KEY `idx_messages_is_read` (`is_read`),
  KEY `idx_messages_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Contact Inquiries & Support Messages';

-- ------------------------------------------------------------------------------
-- 9. Table: store_settings
-- Description: Site-wide configuration, store details, and localization settings.
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `store_settings`;
CREATE TABLE `store_settings` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Unique primary key',
  `store_name` VARCHAR(150) NOT NULL DEFAULT 'AA Mart' COMMENT 'Store public brand name',
  `support_email` VARCHAR(150) NOT NULL DEFAULT 'support@aamart.com' COMMENT 'Primary customer support email',
  `support_phone` VARCHAR(20) DEFAULT '+91 98765 43210' COMMENT 'Primary support phone number',
  `footer_credit` VARCHAR(255) DEFAULT 'Created By Aditya Kumar, Adarsh Raj, Anikesh, and Aditya' COMMENT 'Footer copyright & attribution line',
  `currency` VARCHAR(10) NOT NULL DEFAULT 'INR' COMMENT 'Default store currency symbol/code',
  `timezone` VARCHAR(50) NOT NULL DEFAULT 'Asia/Kolkata' COMMENT 'Store server timezone offset setting',
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Last configuration change timestamp',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Global Store Settings & Site Configuration';

-- ==============================================================================
-- SAMPLE SEED DATA INSERTS
-- ==============================================================================

-- Seed Data: Admin Users (Password: Admin@123 -> $2y$10$yC7xa1LEUFwQfCUxybmrlOtDesQ/4HsxAij497FWyGoUChsE8Ehiq)
INSERT INTO `admin` (`id`, `full_name`, `email`, `password`, `role`, `status`) VALUES
(1, 'Super Admin', 'admin@aamart.com', '$2y$10$yC7xa1LEUFwQfCUxybmrlOtDesQ/4HsxAij497FWyGoUChsE8Ehiq', 'superadmin', 'active'),
(2, 'Aditya Kumar', 'aditya@aamart.com', '$2y$10$yC7xa1LEUFwQfCUxybmrlOtDesQ/4HsxAij497FWyGoUChsE8Ehiq', 'admin', 'active');

-- Seed Data: Categories
INSERT INTO `categories` (`id`, `category_name`, `slug`, `image`, `description`, `status`) VALUES
(1, 'Electronics & Gadgets', 'electronics-gadgets', 'https://images.unsplash.com/photo-1516321497487-e288fb19713f?auto=format&fit=crop&w=600&q=80', 'Latest smart devices, webcams, audio gear and computer peripherals.', 'active'),
(2, 'Audio & Accessories', 'audio-accessories', 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&w=600&q=80', 'High-definition wireless headphones, earbuds, and home speakers.', 'active'),
(3, 'Wearable Tech', 'wearable-tech', 'https://images.unsplash.com/photo-1546868871-7041f2a55e12?auto=format&fit=crop&w=600&q=80', 'Smartwatches, fitness bands, and wearable health trackers.', 'active'),
(4, 'Computer Peripherals', 'computer-peripherals', 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?auto=format&fit=crop&w=600&q=80', 'Ergonomic keyboards, gaming mice, USB hubs, and aluminum desk accessories.', 'active');

-- Seed Data: Users (Password: User@123 -> $2y$10$DNdaUx7VoT.IO1DH5lvk6.F2ZTZRG/WBLk.7bGyJeTQbT5dP6VLK2)
INSERT INTO `users` (`id`, `full_name`, `email`, `phone`, `password`, `address`, `city`, `state`, `pincode`, `status`) VALUES
(1, 'Adarsh Raj', 'user@aamart.com', '9876543210', '$2y$10$DNdaUx7VoT.IO1DH5lvk6.F2ZTZRG/WBLk.7bGyJeTQbT5dP6VLK2', 'Flat 402, Green Park Apartments', 'Patna', 'Bihar', '800001', 'active'),
(2, 'Anikesh Singh', 'anikesh@gmail.com', '9123456789', '$2y$10$DNdaUx7VoT.IO1DH5lvk6.F2ZTZRG/WBLk.7bGyJeTQbT5dP6VLK2', 'House #12, MG Road', 'Ranchi', 'Jharkhand', '834001', 'active');

-- Seed Data: Products
INSERT INTO `products` (`id`, `category_id`, `product_name`, `slug`, `description`, `price_usd`, `price_inr`, `discount_price`, `stock`, `SKU`, `brand`, `image`, `gallery_images`, `featured`, `status`) VALUES
(1, 2, 'Noise Cancelling Headphones', 'noise-cancelling-headphones', 'Immersive acoustics with industry-leading active noise cancellation.', 99.99, 9533.05, 8999.00, 50, 'AAM-HD-001', 'Acoustics', 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&w=900&q=80', '["https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&w=900&q=80"]', 1, 'active'),
(2, 3, 'Smartwatch Pro', 'smartwatch-pro', 'Advanced health metrics, AMOLED touchscreen, and long battery life.', 199.50, 19020.33, 17999.00, 30, 'AAM-SW-002', 'FitTech', 'https://images.unsplash.com/photo-1546868871-7041f2a55e12?auto=format&fit=crop&w=900&q=80', '["https://images.unsplash.com/photo-1546868871-7041f2a55e12?auto=format&fit=crop&w=900&q=80"]', 1, 'active'),
(3, 4, 'Ergonomic Keyboard', 'ergonomic-keyboard', 'Designed for ultimate wrist comfort and silent tactile typing.', 75.00, 7150.50, 6499.00, 40, 'AAM-KB-003', 'TypeMaster', 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?auto=format&fit=crop&w=900&q=80', '["https://images.unsplash.com/photo-1517336714731-489689fd1ca8?auto=format&fit=crop&w=900&q=80"]', 1, 'active'),
(4, 1, '4K Webcam', '4k-webcam', 'Ultra HD crisp resolution webcam ideal for streaming and video calls.', 59.99, 5719.45, 4999.00, 65, 'AAM-WC-004', 'CamPro', 'https://images.unsplash.com/photo-1516321497487-e288fb19713f?auto=format&fit=crop&w=900&q=80', '["https://images.unsplash.com/photo-1516321497487-e288fb19713f?auto=format&fit=crop&w=900&q=80"]', 1, 'active');

-- Seed Data: Orders
INSERT INTO `orders` (`id`, `order_number`, `user_id`, `total_amount`, `payment_method`, `payment_status`, `order_status`, `shipping_address`) VALUES
(1, 'ORD-AAM1001', 1, 9533.05, 'UPI', 'paid', 'delivered', 'Flat 402, Green Park Apartments, Patna, Bihar - 800001'),
(2, 'ORD-AAM1002', 2, 7150.50, 'COD', 'pending', 'processing', 'House #12, MG Road, Ranchi, Jharkhand - 834001');

-- Seed Data: Reviews
INSERT INTO `reviews` (`id`, `product_id`, `user_id`, `rating`, `review`, `status`) VALUES
(1, 1, 1, 5, 'The active noise cancellation works like magic! Very comfortable padding.', 'approved'),
(2, 3, 2, 4, 'Great tactile feedback and sleek build quality. Worth the money.', 'approved');

-- Seed Data: Coupons
INSERT INTO `coupons` (`id`, `coupon_code`, `discount_type`, `discount_value`, `minimum_order`, `expiry_date`, `status`) VALUES
(1, 'WELCOME10', 'percentage', 10.00, 500.00, '2027-12-31', 'active'),
(2, 'FLAT500', 'fixed', 500.00, 2000.00, '2027-12-31', 'active');

-- Seed Data: Messages
INSERT INTO `messages` (`id`, `name`, `email`, `subject`, `message`, `is_read`) VALUES
(1, 'Rohan Sharma', 'rohan@example.com', 'Inquiry about bulk order', 'Hello AA Mart team, do you offer discounts on bulk orders of keypads?', 0);

-- Seed Data: Store Settings
INSERT INTO `store_settings` (`id`, `store_name`, `support_email`, `support_phone`, `footer_credit`, `currency`, `timezone`) VALUES
(1, 'AA Mart', 'support@aamart.com', '+91 98765 43210', 'Created By Aditya Kumar, Adarsh Raj, Anikesh, and Aditya', 'INR', 'Asia/Kolkata');

SET FOREIGN_KEY_CHECKS = 1;
COMMIT;
