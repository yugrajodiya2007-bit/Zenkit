<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/helpers.php';

$query = trim($_GET['q'] ?? '');
if (strlen($query) < 2) {
    echo json_encode(['results' => []]);
    exit;
}

$db = getDB();
$stmt = $db->prepare("SELECT p.id, p.name, p.price, p.image_url, c.name as category_name 
                      FROM products p 
                      LEFT JOIN categories c ON p.category_id = c.id 
                      WHERE p.name LIKE ? OR p.description LIKE ? OR c.name LIKE ? 
                      LIMIT 6");
$term = "%$query%";
$stmt->execute([$term, $term, $term]);
$results = $stmt->fetchAll();

$formatted = [];
foreach ($results as $r) {
    $formatted[] = [
        'id' => $r['id'],
        'name' => $r['name'],
        'price_formatted' => formatPrice($r['price']),
        'image_url' => $r['image_url'],
        'category' => $r['category_name'],
        'url' => 'product.php?id=' . $r['id']
    ];
}

echo json_encode(['results' => $formatted]);
