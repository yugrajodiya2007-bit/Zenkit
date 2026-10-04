<?php
$pageTitle = "Track Your Order | Zenkit Express Logistics";
require_once __DIR__ . '/includes/header.php';

$query = trim($_GET['tracking'] ?? $_POST['tracking'] ?? '');
$order = null;
$searched = false;

if (!empty($query)) {
    $searched = true;
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM orders WHERE order_number = ? OR tracking_code = ? LIMIT 1");
    $stmt->execute([$query, $query]);
    $order = $stmt->fetch();
}
?>

<main class="main-content">
  <div class="container" style="max-width: 860px;">
    
    <!-- Tracking Search Card -->
    <div style="background: #FFFFFF; border: 1px solid var(--border-light); border-radius: var(--radius-xs); padding: 32px; box-shadow: var(--shadow-sm); margin-bottom: 24px;">
      <div style="text-align: center; max-width: 600px; margin: 0 auto;">
        <span style="font-size: 38px;">🚚</span>
        <h1 style="font-size: 1.6rem; font-weight: 800; color: #1E293B; margin-top: 8px;">Zenkit Express Shipment Tracking</h1>
        <p style="font-size: 0.9rem; color: #64748B; margin: 6px 0 20px 0;">
          Enter your Order Number (e.g. <code>ZK-748921</code>) or Tracking AWB code to check live dispatch milestones.
        </p>

        <form action="track-order.php" method="GET" style="display: flex; gap: 10px;">
          <input 
            type="text" 
            name="tracking" 
            class="form-control" 
            placeholder="Enter Order ID (e.g. ZK-748921) or Tracking Code..." 
            value="<?= htmlspecialchars($query) ?>"
            required
            style="padding: 12px 16px; font-size: 14px;"
          >
          <button type="submit" class="btn btn-fk-primary" style="padding: 12px 28px; font-size: 14px; font-weight: 800;">
            Track Now
          </button>
        </form>

        <div style="font-size: 11px; color: #94A3B8; margin-top: 8px;">
          💡 Try demo tracking code: <a href="track-order.php?tracking=TRK-DEL-994821" style="color: var(--fk-blue); font-weight: 700;">TRK-DEL-994821</a> or order ID: <a href="track-order.php?tracking=ZK-748921" style="color: var(--fk-blue); font-weight: 700;">ZK-748921</a>
        </div>
      </div>
    </div>

    <!-- Tracking Results -->
    <?php if ($searched && !$order): ?>
      <div style="background: #FFFFFF; border: 1px solid var(--border-light); border-radius: var(--radius-xs); padding: 40px; text-align: center; box-shadow: var(--shadow-sm);">
        <div style="font-size: 2.5rem; margin-bottom: 12px;">⚠️</div>
        <h3 style="font-size: 1.15rem; font-weight: 800; color: #1E293B;">Shipment Not Found</h3>
        <p style="color: #64748B; font-size: 0.88rem; margin: 6px 0 16px 0;">We could not find an active dispatch matching "<strong><?= htmlspecialchars($query) ?></strong>". Please verify the ID or contact 24/7 customer support.</p>
        <a href="contact.php" class="btn btn-outline" style="font-size: 12px;">Contact Support Helpline</a>
      </div>
    <?php elseif ($order): 
      $orderDate = strtotime($order['created_at']);
      $isDelivered = ($order['order_status'] === 'Delivered');
      $isShipped = ($order['order_status'] === 'Shipped' || $isDelivered);
      $isPacked = ($order['order_status'] === 'Packed' || $isShipped);
    ?>
      <div style="background: #FFFFFF; border: 1px solid var(--border-light); border-radius: var(--radius-xs); padding: 28px; box-shadow: var(--shadow-sm);">
        
        <!-- Header Info -->
        <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid var(--border-light); padding-bottom: 16px; margin-bottom: 24px; flex-wrap: wrap; gap: 12px;">
          <div>
            <div style="font-size: 12px; color: #878787;">Shipment Reference</div>
            <div style="font-size: 1.3rem; font-weight: 800; color: var(--fk-blue); font-family: monospace;">
              <?= htmlspecialchars($order['order_number']) ?>
            </div>
            <div style="font-size: 12px; color: #64748B;">AWB Code: <strong style="font-family: monospace;"><?= htmlspecialchars($order['tracking_code']) ?></strong></div>
          </div>

          <div style="text-align: right;">
            <div style="font-size: 12px; color: #878787;">Carrier Partner</div>
            <div style="font-weight: 800; color: #1E293B;">BlueDart Express Air</div>
            <div style="font-size: 12px; color: #16A34A; font-weight: 700;">Status: <?= htmlspecialchars($order['order_status']) ?></div>
          </div>
        </div>

        <!-- Milestones Timeline -->
        <div style="padding: 20px 0;">
          <h3 style="font-size: 1rem; font-weight: 800; color: #1E293B; margin-bottom: 20px;">Dispatch Milestones</h3>

          <div style="display: flex; flex-direction: column; gap: 24px; position: relative; padding-left: 32px;">
            <div style="position: absolute; left: 11px; top: 8px; bottom: 8px; width: 2px; background: #E2E8F0;"></div>

            <!-- Checkpoint 1 -->
            <div style="position: relative;">
              <div style="position: absolute; left: -32px; top: 0; width: 24px; height: 24px; border-radius: 50%; background: #16A34A; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 800;">✓</div>
              <div style="font-weight: 700; color: #1E293B;">Order Placed & Payment Confirmed</div>
              <div style="font-size: 12px; color: #64748B;">Zenkit Central Fulfillment Hub, Electronic City, Bengaluru</div>
              <div style="font-size: 11px; color: #94A3B8;"><?= date('M d, Y - h:i A', $orderDate) ?></div>
            </div>

            <!-- Checkpoint 2 -->
            <div style="position: relative;">
              <div style="position: absolute; left: -32px; top: 0; width: 24px; height: 24px; border-radius: 50%; background: <?= $isPacked ? '#16A34A' : '#CBD5E1' ?>; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 800;"><?= $isPacked ? '✓' : '○' ?></div>
              <div style="font-weight: 700; color: #1E293B;">Quality Inspection Passed & Sealed for Air Transit</div>
              <div style="font-size: 12px; color: #64748B;">Package scanned at Sorting Facility (Air Cargo Hub)</div>
              <div style="font-size: 11px; color: #94A3B8;"><?= date('M d, Y - h:i A', $orderDate + 14400) ?></div>
            </div>

            <!-- Checkpoint 3 -->
            <div style="position: relative;">
              <div style="position: absolute; left: -32px; top: 0; width: 24px; height: 24px; border-radius: 50%; background: <?= $isShipped ? '#2874F0' : '#CBD5E1' ?>; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 800;"><?= $isShipped ? '⚡' : '○' ?></div>
              <div style="font-weight: 700; color: #1E293B;">In Transit to Destination City Hub (<?= htmlspecialchars($order['city']) ?>)</div>
              <div style="font-size: 12px; color: #64748B;">Handed over to Courier Logistics Unit (Vehicle Scan ID: BLR-EXP-902)</div>
              <div style="font-size: 11px; color: #94A3B8;"><?= date('M d, Y - h:i A', $orderDate + 36000) ?></div>
            </div>

            <!-- Checkpoint 4 -->
            <div style="position: relative;">
              <div style="position: absolute; left: -32px; top: 0; width: 24px; height: 24px; border-radius: 50%; background: <?= $isDelivered ? '#16A34A' : '#CBD5E1' ?>; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 800;"><?= $isDelivered ? '✓' : '○' ?></div>
              <div style="font-weight: 700; color: #1E293B;">
                <?= $isDelivered ? 'Delivered with Customer OTP Verification' : 'Out for Delivery to Customer' ?>
              </div>
              <div style="font-size: 12px; color: #64748B;">Delivery Agent assigned • Destination: <?= htmlspecialchars($order['shipping_address']) ?>, <?= htmlspecialchars($order['city']) ?></div>
              <div style="font-size: 11px; color: #94A3B8;">Expected: <?= $isDelivered ? date('M d, Y - h:i A', $orderDate + 86400) : 'Today by 6:00 PM' ?></div>
            </div>

          </div>
        </div>

        <!-- Action Links -->
        <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--border-light); padding-top: 18px; margin-top: 20px;">
          <a href="invoice.php?id=<?= $order['id'] ?>" target="_blank" class="btn btn-outline" style="font-size: 12px;">
            📄 Download Official GST Invoice
          </a>
          <a href="contact.php" style="color: var(--fk-blue); font-size: 13px; font-weight: 700;">
            Need help with this shipment? Chat with Agent &rarr;
          </a>
        </div>

      </div>
    <?php endif; ?>

  </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
