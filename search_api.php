<?php
/**
 * Live Search API Endpoint for AA Mart
 */

header('Content-Type: application/json');
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

$query = trim($_GET['q'] ?? '');

if (strlen($query) < 2) {
    echo json_encode([]);
    exit;
}

try {
    $db = getDB();
    $sql = "SELECT p.id, p.name, p.brand, p.price, p.image, c.name as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.status = 'active' 
              AND (p.name LIKE :q OR p.brand LIKE :q OR c.name LIKE :q) 
            ORDER BY p.id DESC 
            LIMIT 8";
    
    $stmt = $db->prepare($sql);
    $stmt->execute(['q' => '%' . $query . '%']);
    $products = $stmt->fetchAll();

    $results = [];
    foreach ($products as $p) {
        $results[] = [
            'id' => (int)$p['id'],
            'name' => sanitize($p['name']),
            'brand' => sanitize($p['brand'] ?? 'AA Mart'),
            'price' => format_price((float)$p['price']),
            'image' => get_product_image($p['image'], $p['name']),
            'category' => sanitize($p['category_name'] ?? 'Fashion'),
            'url' => BASE_URL . 'product.php?id=' . (int)$p['id']
        ];
    }

    echo json_encode($results);
} catch (Exception $e) {
    echo json_encode([]);
}
