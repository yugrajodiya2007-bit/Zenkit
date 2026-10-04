<?php
// Zenkit Helper Utilities, Session Management & Cart System

// Output Buffering to prevent header injection errors
if (!ob_get_level()) {
    ob_start();
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';

// Safe HTTP and JavaScript fallback redirection
function safeRedirect($url) {
    if (!headers_sent()) {
        header("Location: $url");
        exit;
    } else {
        echo "<script>window.location.href=" . json_encode($url) . ";</script>";
        echo "<noscript><meta http-equiv='refresh' content='0;url=" . htmlspecialchars($url) . "'></noscript>";
        exit;
    }
}

// Corporate Business Information & Tax Constants
define('STORE_NAME', 'Zenkit Retail Technologies Pvt. Ltd.');
define('STORE_BRAND', 'Zenkit');
define('STORE_GSTIN', '29AABCU9603R1Z7');
define('STORE_CIN', 'U51109KA2012PTC066107');
define('STORE_EMAIL', 'support@zenkit.io');
define('STORE_PHONE', '+91 1800 208 9898');
define('STORE_ADDRESS', 'Embassy Tech Village, Outer Ring Road, Bengaluru, Karnataka 560103, India');

// Format currency in Indian Rupees (₹ / Rs.)
function formatPrice($amount) {
    return '₹' . number_format((float)$amount, 0, '.', ',');
}

function formatPriceINR($amount) {
    return formatPrice($amount);
}


// Flash messages
function setFlash($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type, // success, error, info, warning
        'message' => $message
    ];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// User Authentication Helpers
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function currentUser() {
    if (!isLoggedIn()) return null;
    return [
        'id' => $_SESSION['user_id'] ?? null,
        'name' => $_SESSION['user_name'] ?? 'User',
        'email' => $_SESSION['user_email'] ?? '',
        'role' => $_SESSION['user_role'] ?? 'customer'
    ];
}

function isAdmin() {
    return isLoggedIn() && (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin');
}

function requireAuth($redirect = 'login.php') {
    if (!isLoggedIn()) {
        setFlash('warning', 'Please sign in to access this page.');
        safeRedirect($redirect);
    }
}

function requireAdmin($redirect = '../login.php') {
    if (!isAdmin()) {
        setFlash('error', 'Administrator clearance required.');
        safeRedirect($redirect);
    }
}

// Cart System (Stored in Session with DB Product Sync)
function getCart() {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    return $_SESSION['cart'];
}

function addToCart($productId, $quantity = 1, $options = []) {
    $db = getDB();
    $stmt = $db->prepare("SELECT id, name, price, image_url, stock, slug FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();

    if (!$product) {
        return ['success' => false, 'message' => 'Product not found.'];
    }

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Determine item price based on variant price_diff & addons
    $itemPrice = (float)$product['price'];
    if (!empty($options['price_diff'])) {
        $itemPrice += (float)$options['price_diff'];
    }
    if (!empty($options['addons_total'])) {
        $itemPrice += (float)$options['addons_total'];
    }

    $cartKey = !empty($options) ? $productId . '_' . substr(md5(json_encode($options)), 0, 8) : (string)$productId;

    $currentQty = $_SESSION['cart'][$cartKey]['quantity'] ?? 0;
    $newQty = $currentQty + (int)$quantity;

    if ($newQty > $product['stock']) {
        return ['success' => false, 'message' => "Only {$product['stock']} units available in stock."];
    }

    $_SESSION['cart'][$cartKey] = [
        'cart_key' => $cartKey,
        'id' => (int)$product['id'],
        'name' => $product['name'],
        'price' => round($itemPrice, 2),
        'image_url' => $product['image_url'],
        'slug' => $product['slug'],
        'quantity' => $newQty,
        'options' => $options
    ];

    return ['success' => true, 'message' => "Added to your Cart!"];
}

function updateCartQty($cartKey, $quantity) {
    $quantity = (int)$quantity;
    if ($quantity <= 0) {
        removeFromCart($cartKey);
        return ['success' => true, 'message' => 'Item removed from cart.'];
    }

    if (isset($_SESSION['cart'][$cartKey])) {
        $productId = $_SESSION['cart'][$cartKey]['id'];
        $db = getDB();
        $stmt = $db->prepare("SELECT stock FROM products WHERE id = ?");
        $stmt->execute([$productId]);
        $prod = $stmt->fetch();

        if ($prod && $quantity > $prod['stock']) {
            $quantity = $prod['stock'];
        }

        $_SESSION['cart'][$cartKey]['quantity'] = $quantity;
    }

    return ['success' => true, 'message' => 'Cart updated.'];
}

function removeFromCart($cartKey) {
    if (isset($_SESSION['cart'][$cartKey])) {
        unset($_SESSION['cart'][$cartKey]);
    }
    return ['success' => true, 'message' => 'Item removed from cart.'];
}

function clearCart() {
    $_SESSION['cart'] = [];
    unset($_SESSION['applied_coupon']);
}

function getCartCount() {
    $cart = getCart();
    $count = 0;
    foreach ($cart as $item) {
        $count += $item['quantity'];
    }
    return $count;
}

function getCartSubtotal() {
    $cart = getCart();
    $subtotal = 0;
    foreach ($cart as $item) {
        $subtotal += ($item['price'] * $item['quantity']);
    }
    return (float)$subtotal;
}

function getCartDiscount() {
    $subtotal = getCartSubtotal();
    if (!isset($_SESSION['applied_coupon'])) {
        return 0.00;
    }
    $coupon = $_SESSION['applied_coupon'];
    if ($subtotal < $coupon['min_spend']) {
        unset($_SESSION['applied_coupon']);
        return 0.00;
    }
    $calcDiscount = ($subtotal * $coupon['discount_percent']) / 100;
    return min($calcDiscount, (float)$coupon['max_discount']);
}

function getCartFinalTotal() {
    $subtotal = getCartSubtotal();
    $discount = getCartDiscount();
    $shipping = ($subtotal >= 499 || $subtotal == 0) ? 0 : 49.00;
    return max(0, $subtotal - $discount + $shipping);
}

function getShippingFee() {
    $subtotal = getCartSubtotal();
    if ($subtotal == 0) return 0;
    return ($subtotal >= 499) ? 0 : 49.00;
}

// Wishlist Helpers
function getWishlistCount() {
    if (!isLoggedIn()) {
        return isset($_SESSION['guest_wishlist']) ? count($_SESSION['guest_wishlist']) : 0;
    }
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT COUNT(*) FROM wishlist WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        return (int)$stmt->fetchColumn();
    } catch (Exception $e) {
        return 0;
    }
}

function isProductInWishlist($productId) {
    if (!isLoggedIn()) {
        return isset($_SESSION['guest_wishlist']) && in_array($productId, $_SESSION['guest_wishlist']);
    }
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT COUNT(*) FROM wishlist WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$_SESSION['user_id'], $productId]);
        return $stmt->fetchColumn() > 0;
    } catch (Exception $e) {
        return false;
    }
}

