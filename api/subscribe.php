<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';

$email = trim($_POST['email'] ?? '');
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Please enter a valid email address.']);
    exit;
}

$db = getDB();
try {
    $stmt = $db->prepare("INSERT OR IGNORE INTO newsletter_subscribers (email) VALUES (?)");
    $stmt->execute([$email]);
    echo json_encode(['success' => true, 'message' => 'Thank you for subscribing! Your 15% welcome voucher code is WELCOME15.']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Subscription failed. Please try again.']);
}
