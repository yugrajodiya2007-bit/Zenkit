<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/helpers.php';

$productId = (int)($_POST['product_id'] ?? $_GET['product_id'] ?? 0);
if ($productId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product.']);
    exit;
}

$db = getDB();
$userId = $_SESSION['user_id'] ?? null;

if ($userId) {
    // Check if already in wishlist
    $stmt = $db->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$userId, $productId]);
    $existing = $stmt->fetch();

    if ($existing) {
        $del = $db->prepare("DELETE FROM wishlist WHERE id = ?");
        $del->execute([$existing['id']]);
        $inWishlist = false;
        $msg = "Removed from your Wishlist.";
    } else {
        $ins = $db->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)");
        $ins->execute([$userId, $productId]);
        $inWishlist = true;
        $msg = "Added to your Wishlist!";
    }
} else {
    // Session guest wishlist
    if (!isset($_SESSION['guest_wishlist'])) {
        $_SESSION['guest_wishlist'] = [];
    }
    if (in_array($productId, $_SESSION['guest_wishlist'])) {
        $_SESSION['guest_wishlist'] = array_values(array_diff($_SESSION['guest_wishlist'], [$productId]));
        $inWishlist = false;
        $msg = "Removed from your Wishlist.";
    } else {
        $_SESSION['guest_wishlist'][] = $productId;
        $inWishlist = true;
        $msg = "Added to your Wishlist!";
    }
}

echo json_encode([
    'success' => true,
    'in_wishlist' => $inWishlist,
    'count' => getWishlistCount(),
    'message' => $msg
]);
