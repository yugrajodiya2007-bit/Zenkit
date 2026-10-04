<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../includes/helpers.php';
requireAdmin('../login.php');

$pageTitle = $pageTitle ?? 'Zenkit Admin Center';
$flash = getFlash();
$currentUser = currentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle) ?></title>
  <link rel="icon" type="image/svg+xml" href="../assets/images/brand/zenkit-icon.svg">
  <link rel="stylesheet" href="../assets/css/style.css">
  <style>
    .admin-nav {
      background: #1E293B;
      color: #FFFFFF;
      padding: 0 24px;
      height: 60px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      border-bottom: 1px solid #334155;
    }
    .admin-nav-links {
      display: flex;
      align-items: center;
      gap: 20px;
      font-size: 13px;
      font-weight: 600;
    }
    .admin-nav-links a {
      color: #CBD5E1;
      text-decoration: none;
      transition: color 0.15s;
    }
    .admin-nav-links a:hover, .admin-nav-links a.active {
      color: #38BDF8;
    }
  </style>
</head>
<body style="background-color: #F8FAFC;">

  <!-- Admin Navigation Bar -->
  <header class="admin-nav">
    <div style="display: flex; align-items: center; gap: 24px;">
      <a href="index.php" style="font-size: 18px; font-weight: 900; color: #FFFFFF; display: flex; align-items: center; gap: 6px;">
        <span style="color: #38BDF8;">Zenkit</span> Admin Portal
      </a>

      <nav class="admin-nav-links">
        <a href="index.php">📊 Overview</a>
        <a href="products.php">📦 Products</a>
        <a href="orders.php">🛒 Orders</a>
        <a href="categories.php">🏷️ Categories</a>
        <a href="inquiries.php">💬 Inquiries</a>
      </nav>
    </div>

    <div style="display: flex; align-items: center; gap: 16px;">
      <a href="../index.php" target="_blank" style="color: #94A3B8; font-size: 12px; font-weight: 600;">
        🏪 View Live Store &rarr;
      </a>
      <span style="font-size: 12px; color: #64748B;">|</span>
      <span style="font-size: 12px; color: #E2E8F0; font-weight: 700;">
        👤 <?= htmlspecialchars($currentUser['name']) ?>
      </span>
      <a href="../logout.php" style="background: #334155; color: #FFFFFF; font-size: 12px; padding: 4px 10px; border-radius: 4px; text-decoration: none;">
        Logout
      </a>
    </div>
  </header>

  <?php if ($flash): ?>
    <div style="position: fixed; top: 70px; right: 20px; z-index: 9999;" id="admin-flash-banner">
      <div class="zen-toast" style="border-left: 4px solid <?= $flash['type'] === 'error' ? '#EF4444' : '#10B981' ?>;">
        <span style="font-weight: 800; color: <?= $flash['type'] === 'error' ? '#EF4444' : '#10B981' ?>;">
          <?= $flash['type'] === 'error' ? '✕' : '✓' ?>
        </span>
        <span><?= htmlspecialchars($flash['message']) ?></span>
      </div>
    </div>
    <script>
      setTimeout(() => {
        const b = document.getElementById('admin-flash-banner');
        if (b) b.remove();
      }, 4000);
    </script>
  <?php endif; ?>

  <div class="container" style="padding-top: 24px; padding-bottom: 40px;">
