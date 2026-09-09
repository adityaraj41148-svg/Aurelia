-- AA Mart Production E-Commerce Database Schema & Seed Data
-- Created: 2026-08-04

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS newsletter, contact_messages, addresses, coupons, reviews, payments, order_items, orders, wishlist, cart, product_images, products, subcategories, categories, admin_users, users, hero_slides, settings;
SET FOREIGN_KEY_CHECKS = 1;

-- 1. Users Table
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(30) DEFAULT NULL,
  `role` ENUM('user', 'admin') DEFAULT 'user',
  `status` ENUM('active', 'inactive', 'banned') DEFAULT 'active',
  `remember_token` VARCHAR(100) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_users_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Admin Users Table
CREATE TABLE IF NOT EXISTS `admin_users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('superadmin', 'admin', 'editor') DEFAULT 'admin',
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_admin_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Categories Table
CREATE TABLE IF NOT EXISTS `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(120) NOT NULL UNIQUE,
  `icon` VARCHAR(50) DEFAULT 'fa-folder',
  `image` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_categories_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Subcategories Table
CREATE TABLE IF NOT EXISTS `subcategories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `category_id` INT NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(120) NOT NULL UNIQUE,
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE,
  INDEX `idx_subcategories_category` (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Products Table
CREATE TABLE IF NOT EXISTS `products` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `category_id` INT NOT NULL,
  `subcategory_id` INT DEFAULT NULL,
  `name` VARCHAR(200) NOT NULL,
  `slug` VARCHAR(220) NOT NULL UNIQUE,
  `description` TEXT DEFAULT NULL,
  `price` DECIMAL(10,2) NOT NULL,
  `sale_price` DECIMAL(10,2) DEFAULT NULL,
  `stock` INT DEFAULT 100,
  `is_featured` TINYINT(1) DEFAULT 0,
  `is_trending` TINYINT(1) DEFAULT 1,
  `image` VARCHAR(500) NOT NULL,
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`subcategory_id`) REFERENCES `subcategories`(`id`) ON DELETE SET NULL,
  INDEX `idx_products_featured` (`is_featured`),
  INDEX `idx_products_category` (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Product Images Table
CREATE TABLE IF NOT EXISTS `product_images` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT NOT NULL,
  `image_path` VARCHAR(500) NOT NULL,
  `is_primary` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Cart Table
CREATE TABLE IF NOT EXISTS `cart` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT DEFAULT NULL,
  `session_id` VARCHAR(100) DEFAULT NULL,
  `product_id` INT NOT NULL,
  `quantity` INT NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_cart_session` (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Wishlist Table
CREATE TABLE IF NOT EXISTS `wishlist` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `unique_user_product` (`user_id`, `product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Orders Table
CREATE TABLE IF NOT EXISTS `orders` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_number` VARCHAR(50) NOT NULL UNIQUE,
  `user_id` INT DEFAULT NULL,
  `total_amount` DECIMAL(10,2) NOT NULL,
  `discount_amount` DECIMAL(10,2) DEFAULT 0.00,
  `shipping_fee` DECIMAL(10,2) DEFAULT 0.00,
  `grand_total` DECIMAL(10,2) NOT NULL,
  `payment_method` VARCHAR(50) DEFAULT 'COD',
  `payment_status` ENUM('pending', 'paid', 'failed') DEFAULT 'pending',
  `order_status` ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
  `shipping_name` VARCHAR(100) NOT NULL,
  `shipping_email` VARCHAR(150) NOT NULL,
  `shipping_phone` VARCHAR(30) NOT NULL,
  `shipping_address` TEXT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  INDEX `idx_orders_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. Order Items Table
CREATE TABLE IF NOT EXISTS `order_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT NOT NULL,
  `product_id` INT DEFAULT NULL,
  `quantity` INT NOT NULL,
  `unit_price` DECIMAL(10,2) NOT NULL,
  `subtotal` DECIMAL(10,2) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. Payments Table
CREATE TABLE IF NOT EXISTS `payments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT NOT NULL,
  `transaction_id` VARCHAR(100) DEFAULT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `payment_method` VARCHAR(50) NOT NULL,
  `status` ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. Reviews Table
CREATE TABLE IF NOT EXISTS `reviews` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT NOT NULL,
  `user_id` INT DEFAULT NULL,
  `customer_name` VARCHAR(100) NOT NULL,
  `customer_role` VARCHAR(100) DEFAULT 'Customer',
  `avatar` VARCHAR(500) DEFAULT NULL,
  `rating` INT NOT NULL DEFAULT 5,
  `comment` TEXT NOT NULL,
  `status` ENUM('approved', 'pending', 'rejected') DEFAULT 'approved',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 13. Coupons Table
CREATE TABLE IF NOT EXISTS `coupons` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `code` VARCHAR(50) NOT NULL UNIQUE,
  `discount_type` ENUM('percentage', 'fixed') DEFAULT 'percentage',
  `discount_value` DECIMAL(10,2) NOT NULL,
  `min_order_amount` DECIMAL(10,2) DEFAULT 0.00,
  `expiry_date` DATE DEFAULT NULL,
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 14. Addresses Table
CREATE TABLE IF NOT EXISTS `addresses` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `address_line1` VARCHAR(255) NOT NULL,
  `address_line2` VARCHAR(255) DEFAULT NULL,
  `city` VARCHAR(100) NOT NULL,
  `state` VARCHAR(100) NOT NULL,
  `postal_code` VARCHAR(20) NOT NULL,
  `country` VARCHAR(100) DEFAULT 'India',
  `is_default` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 15. Contact Messages Table
CREATE TABLE IF NOT EXISTS `contact_messages` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `message` TEXT NOT NULL,
  `status` ENUM('unread', 'read', 'replied') DEFAULT 'unread',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 16. Newsletter Table
CREATE TABLE IF NOT EXISTS `newsletter` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `status` ENUM('subscribed', 'unsubscribed') DEFAULT 'subscribed',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 17. Hero Slides Table
CREATE TABLE IF NOT EXISTS `hero_slides` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(200) NOT NULL,
  `subtitle` VARCHAR(255) NOT NULL,
  `button_text` VARCHAR(50) DEFAULT 'Shop Now',
  `button_url` VARCHAR(255) DEFAULT '#products',
  `button_class` VARCHAR(100) DEFAULT 'bg-indigo-600 hover:bg-indigo-700',
  `bg_image` VARCHAR(500) NOT NULL,
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 18. Site Settings Table
CREATE TABLE IF NOT EXISTS `settings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `setting_key` VARCHAR(100) NOT NULL UNIQUE,
  `setting_value` TEXT DEFAULT NULL,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- SEED DATA
-- =========================================================

-- Admin Users (Password: admin123)
INSERT INTO `admin_users` (`name`, `email`, `password`, `role`, `status`) VALUES
('AA Mart Admin', 'admin@aamart.com', '$2y$10$yC7xa1LEUFwQfCUxybmrlOtDesQ/4HsxAij497FWyGoUChsE8Ehiq', 'superadmin', 'active');

-- Sample Users (Password: user123)
INSERT INTO `users` (`name`, `email`, `password`, `phone`, `role`, `status`) VALUES
('Aditya Kumar', 'user@aamart.com', '$2y$10$DNdaUx7VoT.IO1DH5lvk6.F2ZTZRG/WBLk.7bGyJeTQbT5dP6VLK2', '9876543210', 'user', 'active');

-- Categories
INSERT INTO `categories` (`id`, `name`, `slug`, `icon`) VALUES
(1, 'Electronics', 'electronics', 'fa-laptop'),
(2, 'Audio & Accessories', 'audio-accessories', 'fa-headphones'),
(3, 'Smart Devices', 'smart-devices', 'fa-mobile-alt'),
(4, 'Office & Desk', 'office-desk', 'fa-desktop');

-- Subcategories
INSERT INTO `subcategories` (`id`, `category_id`, `name`, `slug`) VALUES
(1, 1, 'Cameras & Lenses', 'cameras-lenses'),
(2, 2, 'Headphones & Earbuds', 'headphones-earbuds'),
(3, 3, 'Smartwatches & Fitness', 'smartwatches-fitness'),
(4, 4, 'Keyboards & Mice', 'keyboards-mice');

-- Products (AA MART Store Collection)
INSERT INTO `products` (`id`, `category_id`, `subcategory_id`, `name`, `slug`, `description`, `price`, `stock`, `is_featured`, `is_trending`, `image`) VALUES
(1, 2, NULL, 'Handcrafted Ethnic Mojaris & Juttis', 'handcrafted-ethnic-mojaris-juttis', 'Handcrafted traditional ethnic mojaris & juttis featuring genuine leather, intricate embroidery, and cushioned soles.', 999.00, 50, 1, 1, 'https://images.unsplash.com/photo-1543163521-1bf539c55dd2?auto=format&fit=crop&w=900&q=80'),
(2, 3, NULL, 'AA MART Lavender Air Cushion Sneakers', 'aamart-lavender-air-cushion-sneakers', 'Lightweight and stylish air cushion sneakers designed for maximum comfort and modern lifestyle fashion.', 1299.00, 40, 1, 1, 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=900&q=80'),
(3, 1, NULL, 'Kerala Kasavu Pure Zari Silk Saree', 'kerala-kasavu-pure-zari-silk-saree', 'Handcrafted traditional Kasavu saree featuring rich gold zari borders and tissue silk drape.', 2399.00, 30, 1, 1, 'https://images.unsplash.com/photo-1610030469983-98e550d6193c?auto=format&fit=crop&w=900&q=80'),
(4, 4, NULL, 'Royal Silk Mandarin Kurta & Pyjama Set', 'royal-silk-mandarin-kurta-pyjama-set', 'Classic raw silk Mandarin collar kurta paired with tailored pyjama trousers.', 1999.00, 65, 1, 1, 'https://images.unsplash.com/photo-1617137984095-74e4e5e3613f?auto=format&fit=crop&w=900&q=80'),
(5, 3, NULL, 'Titan Royal Gold Dial Leather Watch', 'titan-royal-gold-dial-leather-watch', 'Festive special edition Titan analog wristwatch with genuine leather strap.', 4495.00, 25, 1, 1, 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=900&q=80'),
(6, 2, NULL, 'Handcrafted Zari Embroidered Velvet Potli Bag', 'handcrafted-zari-embroidered-velvet-potli-bag', 'Vibrant velvet potli bag with gold pearl tassel drawstring.', 799.00, 45, 0, 1, 'https://images.unsplash.com/photo-1584917865442-de89df76afd3?auto=format&fit=crop&w=900&q=80');


-- Hero Slides
INSERT INTO `hero_slides` (`title`, `subtitle`, `button_text`, `button_url`, `button_class`, `bg_image`) VALUES
('SIGMA-Fujifilm-X-Mount', 'Trendy, stylish, and made just for you.', 'Shop Now', '#products', 'bg-indigo-600 hover:bg-indigo-700', 'https://shop16381.hstatic.dk/upload_dir/pics/Banner-SIGMA-Fujifilm-X-Mount-1240x440px-Vefa-2.jpg'),
('Up to 50% Off', 'Don\'t miss out on our limited-time sale event.', 'View Deals', '#products', 'bg-red-500 hover:bg-red-600', 'https://t3.ftcdn.net/jpg/01/25/23/72/360_F_125237254_guu01Do2IbEeCpBg3tyjuzpMST4uwklS.jpg'),
('Free Shipping On All Orders', 'Get your favorite products delivered to your door, for free.', 'Start Shopping', '#products', 'bg-blue-500 hover:bg-blue-600', 'https://www.shutterstock.com/image-illustration/grocery-products-isolated-on-white-260nw-241859578.jpg');

-- Reviews / Testimonials
INSERT INTO `reviews` (`product_id`, `customer_name`, `customer_role`, `avatar`, `rating`, `comment`, `status`) VALUES
(1, 'Jane Doe', 'Happy Customer', 'https://placehold.co/80x80/6366f1/ffffff?text=AV', 5, 'The quality is outstanding, and the customer service was fantastic. I\'ll definitely be shopping here again!', 'approved'),
(2, 'John Smith', 'Tech Enthusiast', 'https://placehold.co/80x80/34d399/ffffff?text=AV', 5, 'My order arrived so quickly! Everything was packaged perfectly. Highly recommend MyShop.', 'approved'),
(3, 'Emily Johnson', 'Designer', 'https://placehold.co/80x80/f59e0b/ffffff?text=AV', 5, 'Found exactly what I was looking for at a great price. The website is so easy to navigate.', 'approved'),
(4, 'Michael Brown', 'Verified Buyer', 'https://placehold.co/80x80/ef4444/ffffff?text=AV', 5, 'A seamless shopping experience from start to finish. Five stars!', 'approved');

-- Coupons
INSERT INTO `coupons` (`code`, `discount_type`, `discount_value`, `min_order_amount`, `expiry_date`, `status`) VALUES
('WELCOME10', 'percentage', 10.00, 4150.00, '2027-12-31', 'active'),
('FLAT50', 'fixed', 4150.00, 16600.00, '2027-12-31', 'active');

-- Settings
INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('site_name', '𝔸𝔸 𝕄𝕒𝕣𝕥'),
('contact_email', 'support@aamart.com'),
('contact_phone', '+91 98765 43210'),
('footer_credit', 'created By Aditya kumar,Adarsh Raj,Anikesh and Aditya');
