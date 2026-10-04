<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/includes/helpers.php';

// Keep cart if any, unset user session
unset($_SESSION['user_id']);
unset($_SESSION['user_name']);
unset($_SESSION['user_email']);
unset($_SESSION['user_role']);

setFlash('info', 'You have been successfully logged out.');
header('Location: index.php');
exit;
