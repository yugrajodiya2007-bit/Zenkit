<?php
$pageTitle = "Order Confirmed | Zenkit";
require_once __DIR__ . '/includes/header.php';

$orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$db = getDB();

$stmt = $db->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$orderId]);
$order = $stmt->fetch();

if (!$order) {
    header("Location: index.php");
    exit;
}

$itemStmt = $db->prepare("SELECT * FROM order_items WHERE order_id = ?");
$itemStmt->execute([$orderId]);
$items = $itemStmt->fetchAll();
?>

<main class="main-content">
  <div class="container" style="max-width: 860px;">
    
    <!-- Success Banner -->
    <div style="background: #FFFFFF; border-radius: var(--radius-xs); border: 1px solid var(--border-light); padding: 32px; text-align: center; margin-bottom: 20px; box-shadow: var(--shadow-sm);">
      <div style="width: 64px; height: 64px; background: #DCFCE7; color: #16A34A; font-size: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px auto;">
        ✓
      </div>
      <h1 style="font-size: 1.6rem; font-weight: 800; color: #1E293B; margin-bottom: 6px;">Order Confirmed!</h1>
      <p style="font-size: 0.95rem; color: #64748B; margin-bottom: 12px;">
        Thank you for shopping on Zenkit. An order confirmation email has been sent to <strong><?= htmlspecialchars($order['customer_email']) ?></strong>.
      </p>
      <div style="display: inline-block; background: #F1F5F9; padding: 6px 16px; border-radius: 4px; font-size: 13px; font-weight: 700; color: #334155;">
        Order Reference: <span style="color: var(--fk-blue); font-family: monospace;"><?= htmlspecialchars($order['order_number']) ?></span> • Tracking: <span style="font-family: monospace;"><?= htmlspecialchars($order['tracking_code']) ?></span>
      </div>
    </div>

    <!-- Live Delivery Stepper -->
    <div style="background: #FFFFFF; border-radius: var(--radius-xs); border: 1px solid var(--border-light); padding: 24px; margin-bottom: 20px; box-shadow: var(--shadow-sm);">
      <div style="font-size: 0.9rem; font-weight: 800; color: #1E293B; text-transform: uppercase; margin-bottom: 20px;">
        Estimated Delivery Progress
      </div>
      
      <div style="display: flex; align-items: center; justify-content: space-between; position: relative;">
        <!-- Track Step 1 -->
        <div style="display: flex; flex-direction: column; align-items: center; z-index: 2;">
          <div style="width: 32px; height: 32px; border-radius: 50%; background: #16A34A; color: #FFFFFF; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 14px;">✓</div>
          <div style="font-size: 12px; font-weight: 700; margin-top: 6px; color: #1E293B;">Ordered</div>
          <div style="font-size: 10px; color: #94A3B8;"><?= date('M d') ?></div>
        </div>

        <div style="flex: 1; height: 4px; background: #16A34A; margin: 0 -10px; transform: translateY(-12px);"></div>

        <!-- Track Step 2 -->
        <div style="display: flex; flex-direction: column; align-items: center; z-index: 2;">
          <div style="width: 32px; height: 32px; border-radius: 50%; background: #16A34A; color: #FFFFFF; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 14px;">✓</div>
          <div style="font-size: 12px; font-weight: 700; margin-top: 6px; color: #1E293B;">Packed</div>
          <div style="font-size: 10px; color: #94A3B8;">Ready</div>
        </div>

        <div style="flex: 1; height: 4px; background: var(--fk-blue); margin: 0 -10px; transform: translateY(-12px);"></div>

        <!-- Track Step 3 -->
        <div style="display: flex; flex-direction: column; align-items: center; z-index: 2;">
          <div style="width: 32px; height: 32px; border-radius: 50%; background: var(--fk-blue); color: #FFFFFF; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 14px;">⚡</div>
          <div style="font-size: 12px; font-weight: 700; margin-top: 6px; color: var(--fk-blue);">Shipped</div>
          <div style="font-size: 10px; color: #94A3B8;">In Transit</div>
        </div>

        <div style="flex: 1; height: 4px; background: #E2E8F0; margin: 0 -10px; transform: translateY(-12px);"></div>

        <!-- Track Step 4 -->
        <div style="display: flex; flex-direction: column; align-items: center; z-index: 2;">
          <div style="width: 32px; height: 32px; border-radius: 50%; background: #E2E8F0; color: #64748B; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 14px;">○</div>
          <div style="font-size: 12px; font-weight: 700; margin-top: 6px; color: #64748B;">Delivery</div>
          <div style="font-size: 10px; color: #94A3B8;">Tomorrow</div>
        </div>
      </div>
    </div>

    <!-- Order Items & Shipping Address Details -->
    <div style="background: #FFFFFF; border-radius: var(--radius-xs); border: 1px solid var(--border-light); padding: 24px; margin-bottom: 24px; box-shadow: var(--shadow-sm);">
      
      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 24px; padding-bottom: 20px; border-bottom: 1px solid var(--border-subtle);">
        <div>
          <div style="font-size: 12px; font-weight: 700; color: #878787; text-transform: uppercase; margin-bottom: 6px;">Delivery Address</div>
          <div style="font-weight: 700; color: #1E293B;"><?= htmlspecialchars($order['customer_name']) ?></div>
          <div style="font-size: 13px; color: #475569; margin: 2px 0;"><?= htmlspecialchars($order['shipping_address']) ?></div>
          <div style="font-size: 13px; color: #475569;"><?= htmlspecialchars($order['city']) ?>, <?= htmlspecialchars($order['postal_code']) ?></div>
          <div style="font-size: 13px; color: #475569; margin-top: 4px;">Phone: <strong><?= htmlspecialchars($order['customer_phone']) ?></strong></div>
        </div>

        <div>
          <div style="font-size: 12px; font-weight: 700; color: #878787; text-transform: uppercase; margin-bottom: 6px;">Payment Summary</div>
          <div style="font-size: 13px; color: #475569;">Method: <strong><?= htmlspecialchars($order['payment_method']) ?></strong></div>
          <div style="font-size: 13px; color: #475569; margin: 2px 0;">Payment Status: <span style="color: <?= $order['payment_status'] === 'Paid' ? '#16A34A' : '#D97706' ?>; font-weight: 700;"><?= htmlspecialchars($order['payment_status']) ?></span></div>
          <div style="font-size: 1.15rem; font-weight: 900; color: #1E293B; margin-top: 8px;">Total Paid: <?= formatPrice($order['final_amount']) ?></div>
        </div>
      </div>

      <!-- Itemized Table -->
      <table class="admin-table">
        <thead>
          <tr>
            <th>Item</th>
            <th>Unit Price</th>
            <th>Quantity</th>
            <th style="text-align: right;">Total</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($items as $item): ?>
            <tr>
              <td style="font-weight: 600; color: #1E293B;"><?= htmlspecialchars($item['product_name']) ?></td>
              <td><?= formatPrice($item['price']) ?></td>
              <td><?= $item['quantity'] ?></td>
              <td style="text-align: right; font-weight: 700;"><?= formatPrice($item['subtotal']) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <!-- Buttons -->
      <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 24px; flex-wrap: wrap;">
        <a href="invoice.php?id=<?= $order['id'] ?>" target="_blank" class="btn btn-outline" style="font-size: 13px; font-weight: 700; border-color: var(--fk-blue); color: var(--fk-blue);">
          📄 View Official GST Tax Invoice
        </a>
        <a href="track-order.php?tracking=<?= urlencode($order['tracking_code']) ?>" class="btn btn-outline" style="font-size: 13px; font-weight: 700;">
          🚚 Live Delivery Tracker
        </a>
        <a href="shop.php" class="btn btn-fk-primary" style="font-size: 13px;">
          Continue Shopping &rarr;
        </a>
      </div>

    </div>

  </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
