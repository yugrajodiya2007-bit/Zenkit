<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/helpers.php';
$cartCount = getCartCount();
$flash = getFlash();
$pageTitle = $pageTitle ?? 'Zenkit | Online Shopping for Laptops, Audio, Smartwatches & Gear';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle) ?></title>
  <meta name="description" content="Zenkit - Online Shopping for Laptops, Smartwatches, True Wireless Earbuds, Mechanical Keyboards and Smart Ambient Lamps with 3D Previews.">
  
  <link rel="icon" type="image/svg+xml" href="assets/images/brand/zenkit-icon.svg">
  <link rel="stylesheet" href="assets/css/style.css">

  <!-- Three.js 3D WebGL Library from CDN -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
</head>
<body>

  <?php if ($flash): ?>
    <div style="position: fixed; top: 70px; right: 20px; z-index: 9999;" id="flash-banner">
      <div class="zen-toast" style="border-left: 4px solid <?= $flash['type'] === 'error' ? '#EF4444' : ($flash['type'] === 'success' ? '#10B981' : '#2874F0') ?>;">
        <span style="font-weight: 800; color: <?= $flash['type'] === 'error' ? '#EF4444' : '#10B981' ?>;">
          <?= $flash['type'] === 'error' ? '✕' : '✓' ?>
        </span>
        <span><?= htmlspecialchars($flash['message']) ?></span>
      </div>
    </div>
    <script>
      setTimeout(() => {
        const b = document.getElementById('flash-banner');
        if (b) b.remove();
      }, 4000);
    </script>
  <?php endif; ?>

  <?php include __DIR__ . '/navbar.php'; ?>
