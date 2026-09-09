<?php
/**
 * AA Mart - Database Migration & Fashion Catalog Seed Script
 */

require_once __DIR__ . '/../includes/db.php';

try {
    $db = getDB();
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Starting AA Mart Database Migration & Seeding...\n";

    // 1. Ensure subcategories table exists
    $db->exec("CREATE TABLE IF NOT EXISTS `subcategories` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `category_id` INT NOT NULL,
      `name` VARCHAR(100) NOT NULL,
      `slug` VARCHAR(120) NOT NULL UNIQUE,
      `status` ENUM('active', 'inactive') DEFAULT 'active',
      `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      INDEX `idx_subcategories_category` (`category_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

    // 2. Ensure wishlist table exists
    $db->exec("CREATE TABLE IF NOT EXISTS `wishlist` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `user_id` INT DEFAULT NULL,
      `session_id` VARCHAR(100) DEFAULT NULL,
      `product_id` INT NOT NULL,
      `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      INDEX `idx_wishlist_user` (`user_id`),
      INDEX `idx_wishlist_session` (`session_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

    // 3. Ensure order_items table exists
    $db->exec("CREATE TABLE IF NOT EXISTS `order_items` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `order_id` INT NOT NULL,
      `product_id` INT DEFAULT NULL,
      `product_name` VARCHAR(200) DEFAULT NULL,
      `quantity` INT NOT NULL DEFAULT 1,
      `unit_price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
      `size` VARCHAR(20) DEFAULT NULL,
      `color` VARCHAR(50) DEFAULT NULL,
      `subtotal` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
      `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      INDEX `idx_order_items_order` (`order_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

    // 4. Alter products table to add fashion attributes if not present
    $columns = $db->query("SHOW COLUMNS FROM `products`")->fetchAll(PDO::FETCH_COLUMN);

    if (!in_array('brand', $columns)) {
        $db->exec("ALTER TABLE `products` ADD COLUMN `brand` VARCHAR(100) DEFAULT 'AA Mart'");
    }
    if (!in_array('gender', $columns)) {
        $db->exec("ALTER TABLE `products` ADD COLUMN `gender` ENUM('Men', 'Women', 'Kids', 'Unisex') DEFAULT 'Unisex'");
    }
    if (!in_array('sizes', $columns)) {
        $db->exec("ALTER TABLE `products` ADD COLUMN `sizes` VARCHAR(100) DEFAULT 'S, M, L, XL'");
    }
    if (!in_array('colors', $columns)) {
        $db->exec("ALTER TABLE `products` ADD COLUMN `colors` VARCHAR(100) DEFAULT 'Black, White, Blue, Red'");
    }
    if (!in_array('mrp', $columns)) {
        $db->exec("ALTER TABLE `products` ADD COLUMN `mrp` DECIMAL(10,2) DEFAULT NULL");
    }
    if (!in_array('rating', $columns)) {
        $db->exec("ALTER TABLE `products` ADD COLUMN `rating` DECIMAL(3,2) DEFAULT '4.50'");
    }
    if (!in_array('review_count', $columns)) {
        $db->exec("ALTER TABLE `products` ADD COLUMN `review_count` INT DEFAULT '18'");
    }
    if (!in_array('is_new', $columns)) {
        $db->exec("ALTER TABLE `products` ADD COLUMN `is_new` TINYINT(1) DEFAULT '1'");
    }
    if (!in_array('is_sale', $columns)) {
        $db->exec("ALTER TABLE `products` ADD COLUMN `is_sale` TINYINT(1) DEFAULT '0'");
    }
    if (!in_array('is_flash_sale', $columns)) {
        $db->exec("ALTER TABLE `products` ADD COLUMN `is_flash_sale` TINYINT(1) DEFAULT '0'");
    }
    if (!in_array('is_best_seller', $columns)) {
        $db->exec("ALTER TABLE `products` ADD COLUMN `is_best_seller` TINYINT(1) DEFAULT '0'");
    }

    // 5. Seed Fashion Categories
    $categories = [
        ['name' => "Men's Wear", 'slug' => 'mens-wear', 'icon' => 'fa-user-tie', 'image' => 'https://images.unsplash.com/photo-1617137984095-74e4e5e3613f?auto=format&fit=crop&w=600&q=80'],
        ['name' => "Women's Wear", 'slug' => 'womens-wear', 'icon' => 'fa-female', 'image' => 'https://images.unsplash.com/photo-1610030469983-98e550d6193c?auto=format&fit=crop&w=600&q=80'],
        ['name' => "Kids' Wear", 'slug' => 'kids-wear', 'icon' => 'fa-child', 'image' => 'https://images.unsplash.com/photo-1596870230751-ebdfce98ec42?auto=format&fit=crop&w=600&q=80'],
        ['name' => "Footwear", 'slug' => 'footwear', 'icon' => 'fa-shoe-prints', 'image' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=600&q=80'],
        ['name' => "Accessories & Jewellery", 'slug' => 'accessories-jewellery', 'icon' => 'fa-gem', 'image' => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=600&q=80'],
    ];

    foreach ($categories as $cat) {
        $stmt = $db->prepare("INSERT INTO categories (name, slug, icon, image, status) VALUES (:name, :slug, :icon, :image, 'active') ON DUPLICATE KEY UPDATE name=VALUES(name), icon=VALUES(icon), image=VALUES(image)");
        $stmt->execute($cat);
    }

    // Get Category IDs
    $catMap = [];
    $res = $db->query("SELECT id, slug FROM categories")->fetchAll();
    foreach ($res as $r) {
        $catMap[$r['slug']] = $r['id'];
    }

    // 6. Seed Subcategories
    $subcategories = [
        ['cat' => 'mens-wear', 'name' => 'T-Shirts', 'slug' => 'mens-tshirts'],
        ['cat' => 'mens-wear', 'name' => 'Shirts', 'slug' => 'mens-shirts'],
        ['cat' => 'mens-wear', 'name' => 'Jeans & Trousers', 'slug' => 'mens-jeans'],
        ['cat' => 'mens-wear', 'name' => 'Kurtas & Ethnic', 'slug' => 'mens-kurtas'],
        ['cat' => 'womens-wear', 'name' => 'Sarees', 'slug' => 'womens-sarees'],
        ['cat' => 'womens-wear', 'name' => 'Kurtis & Suits', 'slug' => 'womens-kurtis'],
        ['cat' => 'womens-wear', 'name' => 'Dresses & Tops', 'slug' => 'womens-dresses'],
        ['cat' => 'womens-wear', 'name' => 'Lehengas & Ethnic', 'slug' => 'womens-lehengas'],
        ['cat' => 'kids-wear', 'name' => 'Boys Clothing', 'slug' => 'boys-clothing'],
        ['cat' => 'kids-wear', 'name' => 'Girls Clothing', 'slug' => 'girls-clothing'],
        ['cat' => 'kids-wear', 'name' => 'Baby Wear', 'slug' => 'baby-wear'],
    ];

    foreach ($subcategories as $sub) {
        if (isset($catMap[$sub['cat']])) {
            $stmt = $db->prepare("INSERT INTO subcategories (category_id, name, slug, status) VALUES (:cid, :name, :slug, 'active') ON DUPLICATE KEY UPDATE name=VALUES(name)");
            $stmt->execute([
                'cid' => $catMap[$sub['cat']],
                'name' => $sub['name'],
                'slug' => $sub['slug']
            ]);
        }
    }

    // 7. Seed Rich Fashion Products Catalog
    $fashion_products = [
        // Men's Products
        [
            'category_id' => $catMap['mens-wear'],
            'name' => "Men's Royal Silk Fest Kurta Set",
            'slug' => 'mens-royal-silk-kurta-set',
            'description' => "Crafted from premium jacquard silk with intricate golden zari embroidery. Perfect for Onam, Diwali, and wedding celebrations.",
            'price' => 1499.00,
            'mrp' => 2999.00,
            'sale_price' => 1499.00,
            'brand' => 'Manyavar',
            'gender' => 'Men',
            'sizes' => 'S, M, L, XL, XXL',
            'colors' => 'Gold, Maroon, White',
            'stock' => 45,
            'is_featured' => 1,
            'is_trending' => 1,
            'is_new' => 1,
            'is_sale' => 1,
            'is_flash_sale' => 1,
            'is_best_seller' => 1,
            'rating' => 4.80,
            'review_count' => 34,
            'image' => 'https://images.unsplash.com/photo-1617137984095-74e4e5e3613f?auto=format&fit=crop&w=900&q=80'
        ],
        [
            'category_id' => $catMap['mens-wear'],
            'name' => "Flying Machine Slim Fit Casual Shirt",
            'slug' => 'flying-machine-casual-shirt',
            'description' => "100% breathable cotton slim fit casual shirt with button-down collar and sleek chest pocket.",
            'price' => 899.00,
            'mrp' => 1799.00,
            'sale_price' => 899.00,
            'brand' => 'Flying Machine',
            'gender' => 'Men',
            'sizes' => 'S, M, L, XL',
            'colors' => 'Navy Blue, White, Olive Green',
            'stock' => 60,
            'is_featured' => 1,
            'is_trending' => 1,
            'is_new' => 1,
            'is_sale' => 1,
            'is_flash_sale' => 0,
            'is_best_seller' => 1,
            'rating' => 4.60,
            'review_count' => 52,
            'image' => 'https://images.unsplash.com/photo-1602810318383-e386cc2a3ccf?auto=format&fit=crop&w=900&q=80'
        ],
        [
            'category_id' => $catMap['mens-wear'],
            'name' => "Men's Kerala Kasavu Silk Mundu & Shirt",
            'slug' => 'mens-kerala-kasavu-mundu-shirt',
            'description' => "Authentic Kerala tradition golden border tissue kasavu mundu paired with matching cream silk shirt.",
            'price' => 1299.00,
            'mrp' => 2499.00,
            'sale_price' => 1299.00,
            'brand' => 'AA Mart Heritage',
            'gender' => 'Men',
            'sizes' => 'Free Size, M, L, XL',
            'colors' => 'Cream Gold, White Zari',
            'stock' => 50,
            'is_featured' => 1,
            'is_trending' => 1,
            'is_new' => 1,
            'is_sale' => 1,
            'is_flash_sale' => 1,
            'is_best_seller' => 1,
            'rating' => 4.90,
            'review_count' => 48,
            'image' => 'https://images.unsplash.com/photo-1507679799987-c73779587ccf?auto=format&fit=crop&w=900&q=80'
        ],
        [
            'category_id' => $catMap['mens-wear'],
            'name' => "Classic Denim Stretch Jeans",
            'slug' => 'classic-denim-stretch-jeans',
            'description' => "Durable stretch cotton denim engineered for all-day comfort and timeless casual styling.",
            'price' => 1199.00,
            'mrp' => 2199.00,
            'sale_price' => 1199.00,
            'brand' => 'Flying Machine',
            'gender' => 'Men',
            'sizes' => '30, 32, 34, 36',
            'colors' => 'Dark Indigo, Black, Light Wash',
            'stock' => 40,
            'is_featured' => 0,
            'is_trending' => 1,
            'is_new' => 0,
            'is_sale' => 1,
            'is_flash_sale' => 0,
            'is_best_seller' => 1,
            'rating' => 4.50,
            'review_count' => 27,
            'image' => 'https://images.unsplash.com/photo-1541099649105-f69ad21f3246?auto=format&fit=crop&w=900&q=80'
        ],

        // Women's Products
        [
            'category_id' => $catMap['womens-wear'],
            'name' => "Traditional Kerala Kasavu Golden Zari Saree",
            'slug' => 'traditional-kerala-kasavu-saree',
            'description' => "Iconic pure off-white cotton tissue saree embellished with rich golden zari borders and matching blouse piece.",
            'price' => 1899.00,
            'mrp' => 3999.00,
            'sale_price' => 1899.00,
            'brand' => 'FabIndia',
            'gender' => 'Women',
            'sizes' => 'Free Size (6.3m)',
            'colors' => 'Off-White Gold, Pure Cream',
            'stock' => 35,
            'is_featured' => 1,
            'is_trending' => 1,
            'is_new' => 1,
            'is_sale' => 1,
            'is_flash_sale' => 1,
            'is_best_seller' => 1,
            'rating' => 4.95,
            'review_count' => 76,
            'image' => 'https://images.unsplash.com/photo-1610030469983-98e550d6193c?auto=format&fit=crop&w=900&q=80'
        ],
        [
            'category_id' => $catMap['womens-wear'],
            'name' => "Embroidered Silk Anarkali Suit Set",
            'slug' => 'embroidered-silk-anarkali-suit',
            'description' => "Floor-length flared designer silk Anarkali kurta with hand-embroidered gota patti work and net dupatta.",
            'price' => 2499.00,
            'mrp' => 4999.00,
            'sale_price' => 2499.00,
            'brand' => 'Biba',
            'gender' => 'Women',
            'sizes' => 'S, M, L, XL',
            'colors' => 'Royal Emerald Green, Crimson Red, Deep Purple',
            'stock' => 25,
            'is_featured' => 1,
            'is_trending' => 1,
            'is_new' => 1,
            'is_sale' => 1,
            'is_flash_sale' => 0,
            'is_best_seller' => 1,
            'rating' => 4.70,
            'review_count' => 39,
            'image' => 'https://images.unsplash.com/photo-1583391733956-3750e0ff4e8b?auto=format&fit=crop&w=900&q=80'
        ],
        [
            'category_id' => $catMap['womens-wear'],
            'name' => "Floral Printed Chiffon Maxidress",
            'slug' => 'floral-printed-chiffon-maxidress',
            'description' => "Elegantly draped breathable chiffon floral print maxidress with smocked waist and flutter sleeves.",
            'price' => 1199.00,
            'mrp' => 2299.00,
            'sale_price' => 1199.00,
            'brand' => 'Zara',
            'gender' => 'Women',
            'sizes' => 'XS, S, M, L',
            'colors' => 'Pastel Pink, Sky Blue, Sunflower Yellow',
            'stock' => 40,
            'is_featured' => 0,
            'is_trending' => 1,
            'is_new' => 1,
            'is_sale' => 1,
            'is_flash_sale' => 0,
            'is_best_seller' => 0,
            'rating' => 4.60,
            'review_count' => 22,
            'image' => 'https://images.unsplash.com/photo-1572804013309-59a88b7e92f1?auto=format&fit=crop&w=900&q=80'
        ],

        // Kids' Products
        [
            'category_id' => $catMap['kids-wear'],
            'name' => "Girls Silk Pattu Pavadai Festive Set",
            'slug' => 'girls-silk-pattu-pavadai-set',
            'description' => "Traditional South Indian jacquard silk skirt and blouse set with golden zari borders.",
            'price' => 999.00,
            'mrp' => 1999.00,
            'sale_price' => 999.00,
            'brand' => 'AA Mart Kids',
            'gender' => 'Kids',
            'sizes' => '2-3Y, 4-5Y, 6-7Y, 8-9Y',
            'colors' => 'Pink Gold, Yellow Green, Maroon Gold',
            'stock' => 50,
            'is_featured' => 1,
            'is_trending' => 1,
            'is_new' => 1,
            'is_sale' => 1,
            'is_flash_sale' => 1,
            'is_best_seller' => 1,
            'rating' => 4.85,
            'review_count' => 31,
            'image' => 'https://images.unsplash.com/photo-1596870230751-ebdfce98ec42?auto=format&fit=crop&w=900&q=80'
        ],
        [
            'category_id' => $catMap['kids-wear'],
            'name' => "Boys Silk Kurta & Pyjama Set",
            'slug' => 'boys-silk-kurta-pyjama-set',
            'description' => "Soft cotton silk mandarin collar kurta paired with elasticated pyjamas for festive celebrations.",
            'price' => 799.00,
            'mrp' => 1599.00,
            'sale_price' => 799.00,
            'brand' => 'AA Mart Kids',
            'gender' => 'Kids',
            'sizes' => '2-3Y, 4-5Y, 6-7Y, 8-9Y, 10-11Y',
            'colors' => 'Royal Blue, Mustard Yellow, Cream',
            'stock' => 55,
            'is_featured' => 1,
            'is_trending' => 1,
            'is_new' => 1,
            'is_sale' => 1,
            'is_flash_sale' => 0,
            'is_best_seller' => 1,
            'rating' => 4.75,
            'review_count' => 29,
            'image' => 'https://images.unsplash.com/photo-1519238263530-99bdd11df2ea?auto=format&fit=crop&w=900&q=80'
        ],

        // Footwear & Accessories
        [
            'category_id' => $catMap['footwear'],
            'name' => "Handcrafted Ethnic Mojaris & Juttis",
            'slug' => 'handcrafted-ethnic-mojaris-juttis',
            'description' => "Genuine leather handcrafted juttis with golden embroidery padding for festive and wedding comfort.",
            'price' => 999.00,
            'mrp' => 1999.00,
            'sale_price' => 999.00,
            'brand' => 'AA Mart Craft',
            'gender' => 'Unisex',
            'sizes' => '6, 7, 8, 9, 10',
            'colors' => 'Tan Gold, Royal Black, Maroon',
            'stock' => 40,
            'is_featured' => 1,
            'is_trending' => 1,
            'is_new' => 1,
            'is_sale' => 1,
            'is_flash_sale' => 0,
            'is_best_seller' => 1,
            'rating' => 4.65,
            'review_count' => 19,
            'image' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=900&q=80'
        ],
        [
            'category_id' => $catMap['accessories-jewellery'],
            'name' => "Titan Analog Gold Mesh Watch",
            'slug' => 'titan-analog-gold-mesh-watch',
            'description' => "Premium stainless steel golden mesh strap analog quartz watch co-powered by Titan.",
            'price' => 3499.00,
            'mrp' => 5999.00,
            'sale_price' => 3499.00,
            'brand' => 'Titan',
            'gender' => 'Unisex',
            'sizes' => 'Standard',
            'colors' => 'Rose Gold, Metallic Gold, Silver',
            'stock' => 20,
            'is_featured' => 1,
            'is_trending' => 1,
            'is_new' => 1,
            'is_sale' => 1,
            'is_flash_sale' => 1,
            'is_best_seller' => 1,
            'rating' => 4.90,
            'review_count' => 64,
            'image' => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=900&q=80'
        ],
    ];

    foreach ($fashion_products as $p) {
        $stmt = $db->prepare("INSERT INTO products (category_id, name, slug, description, price, mrp, sale_price, brand, gender, sizes, colors, stock, is_featured, is_trending, is_new, is_sale, is_flash_sale, is_best_seller, rating, review_count, image, status) 
        VALUES (:category_id, :name, :slug, :description, :price, :mrp, :sale_price, :brand, :gender, :sizes, :colors, :stock, :is_featured, :is_trending, :is_new, :is_sale, :is_flash_sale, :is_best_seller, :rating, :review_count, :image, 'active') 
        ON DUPLICATE KEY UPDATE 
        category_id=VALUES(category_id), price=VALUES(price), mrp=VALUES(mrp), sale_price=VALUES(sale_price), brand=VALUES(brand), gender=VALUES(gender), sizes=VALUES(sizes), colors=VALUES(colors), rating=VALUES(rating), review_count=VALUES(review_count), image=VALUES(image), is_featured=VALUES(is_featured), is_trending=VALUES(is_trending)");
        $stmt->execute($p);
    }

    // 8. Seed Default Coupons
    $coupons = [
        ['code' => 'WELCOME10', 'discount_type' => 'percentage', 'discount_value' => 10.00, 'min_order_amount' => 500.00],
        ['code' => 'FLAT500', 'discount_type' => 'fixed', 'discount_value' => 500.00, 'min_order_amount' => 2000.00],
        ['code' => 'ONAM2026', 'discount_type' => 'fixed', 'discount_value' => 300.00, 'min_order_amount' => 1500.00],
        ['code' => 'ICICI10', 'discount_type' => 'percentage', 'discount_value' => 10.00, 'min_order_amount' => 1000.00],
    ];

    foreach ($coupons as $c) {
        $stmt = $db->prepare("INSERT INTO coupons (code, discount_type, discount_value, min_order_amount, status) VALUES (:code, :discount_type, :discount_value, :min_order_amount, 'active') ON DUPLICATE KEY UPDATE discount_value=VALUES(discount_value)");
        $stmt->execute($c);
    }

    echo "Database migration & seeding completed successfully!\n";

} catch (Exception $e) {
    echo "MIGRATION ERROR: " . $e->getMessage() . "\n";
}
