<?php
$pageTitle = "My Wishlist | Zenkit";
require_once __DIR__ . '/includes/header.php';

$db = getDB();
$userId = $_SESSION['user_id'] ?? null;
$products = [];

if ($userId) {
    $stmt = $db->prepare("SELECT p.*, c.name as category_name 
                          FROM wishlist w 
                          JOIN products p ON w.product_id = p.id 
                          LEFT JOIN categories c ON p.category_id = c.id 
                          WHERE w.user_id = ? 
                          ORDER BY w.id DESC");
    $stmt->execute([$userId]);
    $products = $stmt->fetchAll();
} else {
    $guestIds = $_SESSION['guest_wishlist'] ?? [];
    if (!empty($guestIds)) {
        $placeholders = str_repeat('?,', count($guestIds) - 1) . '?';
        $stmt = $db->prepare("SELECT p.*, c.name as category_name 
                              FROM products p 
                              LEFT JOIN categories c ON p.category_id = c.id 
                              WHERE p.id IN ($placeholders)");
        $stmt->execute($guestIds);
        $products = $stmt->fetchAll();
    }
}
?>

<main class="main-content">
  <div class="container" style="max-width: 1040px;">
    
    <div style="background: #FFFFFF; border: 1px solid var(--border-light); border-radius: var(--radius-xs); padding: 18px 24px; margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between; box-shadow: var(--shadow-sm);">
      <div>
        <h1 style="font-size: 1.3rem; font-weight: 800; color: #1E293B; display: inline;">My Wishlist</h1>
        <span style="font-size: 13px; color: #878787; margin-left: 8px;">(<?= count($products) ?> items saved)</span>
      </div>
      <a href="shop.php" style="color: var(--fk-blue); font-weight: 700; font-size: 13px;">Continue Shopping &rarr;</a>
    </div>

    <?php if (empty($products)): ?>
      <div style="background: #FFFFFF; border: 1px solid var(--border-light); border-radius: var(--radius-xs); padding: 60px 20px; text-align: center; box-shadow: var(--shadow-sm);">
        <div style="font-size: 3.5rem; margin-bottom: 12px;">💙</div>
        <h2 style="font-size: 1.25rem; font-weight: 800; color: #1E293B; margin-bottom: 6px;">Your Wishlist is Empty!</h2>
        <p style="color: #64748B; font-size: 0.9rem; margin-bottom: 20px;">Save products you're interested in and monitor price drops anytime.</p>
        <a href="shop.php" class="btn btn-fk-primary" style="padding: 10px 28px;">Explore Deals</a>
      </div>
    <?php else: ?>
      <div style="display: flex; flex-direction: column; gap: 14px;">
        <?php foreach ($products as $p): 
          $discPercent = round((($p['original_price'] - $p['price']) / $p['original_price']) * 100);
        ?>
          <div style="background: #FFFFFF; border: 1px solid var(--border-light); border-radius: var(--radius-xs); padding: 20px; box-shadow: var(--shadow-sm); display: flex; align-items: center; justify-content: space-between; gap: 20px; flex-wrap: wrap;">
            
            <div style="display: flex; align-items: center; gap: 18px;">
              <a href="product.php?id=<?= $p['id'] ?>">
                <img src="<?= htmlspecialchars($p['image_url']) ?>" alt="<?= htmlspecialchars($p['name']) ?>" style="width: 80px; height: 80px; object-fit: contain; border: 1px solid #E2E8F0; border-radius: 4px; padding: 6px;">
              </a>
              <div>
                <a href="product.php?id=<?= $p['id'] ?>" style="font-size: 1rem; font-weight: 700; color: #1E293B; text-decoration: none;">
                  <?= htmlspecialchars($p['name']) ?>
                </a>
                <div style="font-size: 12px; color: #878787; margin: 4px 0;"><?= htmlspecialchars($p['subtitle'] ?? '') ?></div>
                <div style="display: flex; align-items: baseline; gap: 8px;">
                  <span style="font-weight: 800; font-size: 1.15rem; color: #1E293B;"><?= formatPrice($p['price']) ?></span>
                  <span style="font-size: 12px; color: #878787; text-decoration: line-through;"><?= formatPrice($p['original_price']) ?></span>
                  <span style="font-size: 12px; color: var(--fk-green); font-weight: 700;"><?= $discPercent ?>% off</span>
                </div>
              </div>
            </div>

            <div style="display: flex; align-items: center; gap: 12px;">
              <button onclick="handleAddToCart(<?= $p['id'] ?>, 1, true)" class="btn btn-fk-buy" style="font-size: 12px; padding: 10px 20px;">
                Move to Cart
              </button>
              <button onclick="toggleWishlist(<?= $p['id'] ?>)" class="btn btn-outline" style="font-size: 12px; padding: 10px 14px; color: #DC2626; border-color: #FCA5A5;">
                Remove
              </button>
            </div>

          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

  </div>
</main>

<script>
function toggleWishlist(productId) {
  fetch('api/wishlist.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'product_id=' + productId
  })
  .then(r => r.json())
  .then(data => {
    window.location.reload();
  });
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
