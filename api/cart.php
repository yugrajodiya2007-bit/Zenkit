<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/helpers.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

$input = json_decode(file_get_contents('php://input'), true) ?? [];
if (!empty($input['action'])) {
    $action = $input['action'];
}

$response = [
    'success' => false,
    'message' => 'Invalid action',
    'cart_count' => getCartCount(),
    'subtotal' => getCartSubtotal(),
    'items' => getCart()
];

if ($action === 'get') {
    $response['success'] = true;
    $response['message'] = 'Cart retrieved';
    echo json_encode($response);
    exit;
}

if ($action === 'add') {
    $productId = (int)($input['product_id'] ?? $_POST['product_id'] ?? 0);
    $qty = (int)($input['quantity'] ?? $_POST['quantity'] ?? 1);
    
    // Parse options (color, variant, addons, price_diff, addons_total)
    $options = $input['options'] ?? $_POST['options'] ?? [];
    if (is_string($options)) {
        $options = json_decode($options, true) ?: [];
    }

    if ($productId > 0) {
        $res = addToCart($productId, $qty, $options);
        $response['success'] = $res['success'];
        $response['message'] = $res['message'];
        $response['cart_count'] = getCartCount();
        $response['subtotal'] = getCartSubtotal();
        $response['items'] = getCart();
    } else {
        $response['message'] = 'Invalid product ID';
    }
    echo json_encode($response);
    exit;
}

if ($action === 'update') {
    $key = $input['cart_key'] ?? $input['product_id'] ?? $_GET['cart_key'] ?? $_GET['id'] ?? $_POST['cart_key'] ?? $_POST['product_id'] ?? '';
    $qty = (int)($input['quantity'] ?? $_GET['quantity'] ?? $_POST['quantity'] ?? 1);
    if (!empty($key)) {
        $res = updateCartQty($key, $qty);
        $response['success'] = $res['success'];
        $response['message'] = $res['message'];
        $response['cart_count'] = getCartCount();
        $response['subtotal'] = getCartSubtotal();
        $response['items'] = getCart();
    }
    echo json_encode($response);
    exit;
}

if ($action === 'remove') {
    $key = $input['cart_key'] ?? $input['product_id'] ?? $_GET['cart_key'] ?? $_GET['id'] ?? $_POST['cart_key'] ?? $_POST['product_id'] ?? '';
    if (!empty($key)) {
        $res = removeFromCart($key);
        $response['success'] = $res['success'];
        $response['message'] = $res['message'];
        $response['cart_count'] = getCartCount();
        $response['subtotal'] = getCartSubtotal();
        $response['items'] = getCart();
    }
    echo json_encode($response);
    exit;
}

echo json_encode($response);
