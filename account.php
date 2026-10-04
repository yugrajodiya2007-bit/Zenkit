<?php
require_once __DIR__ . '/includes/helpers.php';
requireAuth('login.php');

$user = currentUser();
$db = getDB();

// Fetch orders for current user
$stmt = $db->prepare("SELECT * FROM orders WHERE user_id = ? OR customer_email = ? ORDER BY id DESC");
$stmt->execute([$user['id'], $user['email']]);
$orders = $stmt->fetchAll();

$pageTitle = "My Account & Orders | Zenkit";
require_once __DIR__ . '/includes/header.php';
?>

<main class="main-content">
  <div class="container" style="max-width: 1040px;">
    
    <div style="display: grid; grid-template-columns: 280px 1fr; gap: 20px; align-items: start;">
      
      <!-- Left Profile Card -->
      <div style="background: #FFFFFF; border: 1px solid var(--border-light); border-radius: var(--radius-xs); padding: 24px; box-shadow: var(--shadow-sm);">
        <div style="text-align: center; margin-bottom: 20px;">
          <div style="width: 72px; height: 72px; background: var(--fk-blue-light); color: var(--fk-blue); border-radius: 50%; font-size: 32px; display: flex; align-items: center; justify-content: center; margin: 0 auto 12px auto; font-weight: 800;">
            <?= strtoupper(substr($user['name'], 0, 1)) ?>
          </div>
          <h2 style="font-size: 1.15rem; font-weight: 800; color: #1E293B;"><?= htmlspecialchars($user['name']) ?></h2>
          <div style="font-size: 13px; color: #64748B;"><?= htmlspecialchars($user['email']) ?></div>
          <span style="display: inline-block; background: #EBF3FF; color: var(--fk-blue); font-size: 11px; font-weight: 800; padding: 2px 8px; border-radius: 2px; margin-top: 8px;">
            Zenkit Plus Member ✦
          </span>
        </div>

        <div style="border-top: 1px solid var(--border-subtle); padding-top: 16px; display: flex; flex-direction: column; gap: 10px; font-size: 13px;">
          <a href="account.php" style="color: var(--fk-blue); font-weight: 700;">📦 My Orders (<?= count($orders) ?>)</a>
          <a href="cart.php" style="color: #475569;">🛒 My Cart (<?= getCartCount() ?>)</a>
          <?php if (isAdmin()): ?>
            <a href="admin/index.php" style="color: #D97706; font-weight: 700;">⚙️ Admin Dashboard</a>
          <?php endif; ?>
          <a href="logout.php" style="color: #DC2626; font-weight: 600;">🚪 Log Out</a>
        </div>
      </div>

      <!-- Right Orders List -->
      <div>
        <div style="background: #FFFFFF; border: 1px solid var(--border-light); border-radius: var(--radius-xs); padding: 16px 20px; margin-bottom: 16px; box-shadow: var(--shadow-sm);">
          <h1 style="font-size: 1.25rem; font-weight: 800; color: #1E293B; display: inline;">Order History</h1>
          <span style="font-size: 12px; color: #878787; margin-left: 8px;">(<?= count($orders) ?> recorded orders)</span>
        </div>

        <?php if (empty($orders)): ?>
          <div style="background: #FFFFFF; border: 1px solid var(--border-light); border-radius: var(--radius-xs); padding: 60px 20px; text-align: center; box-shadow: var(--shadow-sm);">
            <div style="font-size: 3rem; margin-bottom: 12px;">📦</div>
            <h3 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 6px;">No orders found yet</h3>
            <p style="color: #64748B; font-size: 0.9rem; margin-bottom: 20px;">Place your first order and track it in real-time here.</p>
            <a href="shop.php" class="btn btn-fk-primary">Explore Products</a>
          </div>
        <?php else: ?>
          <div style="display: flex; flex-direction: column; gap: 16px;">
            <?php foreach ($orders as $ord): 
              $ordItemStmt = $db->prepare("SELECT * FROM order_items WHERE order_id = ?");
              $ordItemStmt->execute([$ord['id']]);
              $ordItems = $ordItemStmt->fetchAll();
            ?>
              <div style="background: #FFFFFF; border: 1px solid var(--border-light); border-radius: var(--radius-xs); padding: 20px; box-shadow: var(--shadow-sm);">
                
                <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid var(--border-subtle); padding-bottom: 12px; margin-bottom: 14px;">
                  <div>
                    <span style="font-size: 12px; color: #878787;">Order </span>
                    <strong style="font-family: monospace; color: var(--fk-blue); font-size: 14px;"><?= htmlspecialchars($ord['order_number']) ?></strong>
                    <span style="font-size: 12px; color: #94A3B8; margin-left: 10px;"><?= date('M d, Y', strtotime($ord['created_at'])) ?></span>
                  </div>

                  <span class="fk-rating-pill" style="background: <?= $ord['order_status'] === 'Delivered' ? '#16A34A' : 'var(--fk-blue)' ?>;">
                    ● <?= htmlspecialchars($ord['order_status']) ?>
                  </span>
                </div>

                <!-- Items list -->
                <div style="display: flex; flex-direction: column; gap: 10px; margin-bottom: 14px;">
                  <?php foreach ($ordItems as $it): ?>
                    <div style="display: flex; justify-content: space-between; font-size: 13px;">
                      <span style="color: #1E293B; font-weight: 600;"><?= htmlspecialchars($it['product_name']) ?> <span style="color: #64748B;">x <?= $it['quantity'] ?></span></span>
                      <span style="font-weight: 700;"><?= formatPrice($it['subtotal']) ?></span>
                    </div>
                  <?php endforeach; ?>
                </div>

                <div style="display: flex; align-items: center; justify-content: space-between; border-top: 1px dashed var(--border-subtle); padding-top: 12px; font-size: 13px;">
                  <div>
                    <span style="color: #64748B;">Payment: <strong><?= htmlspecialchars($ord['payment_method']) ?></strong></span>
                    <span style="color: #64748B; margin-left: 12px;">Tracking: <code style="color: #1E293B;"><?= htmlspecialchars($ord['tracking_code']) ?></code></span>
                  </div>
                  <div>
                    <span style="color: #64748B; margin-right: 6px;">Total Paid:</span>
                    <strong style="font-size: 1.1rem; color: #1E293B;"><?= formatPrice($ord['final_amount']) ?></strong>
                  </div>
                </div>

                <div style="display: flex; gap: 8px; justify-content: flex-end; margin-top: 12px;">
                  <a href="invoice.php?id=<?= $ord['id'] ?>" target="_blank" class="btn btn-outline" style="font-size: 11px; padding: 4px 12px; font-weight: 700; color: var(--fk-blue); border-color: var(--fk-blue);">
                    📄 Download Tax Invoice
                  </a>
                  <a href="track-order.php?tracking=<?= urlencode($ord['tracking_code']) ?>" class="btn btn-outline" style="font-size: 11px; padding: 4px 12px; font-weight: 700;">
                    🚚 Track Shipment
                  </a>
                </div>

              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

      </div>

    </div>

  </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
