<?php
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    if (session_status() === PHP_SESSION_NONE) session_start();
    require_once __DIR__ . '/../includes/helpers.php';
    requireAdmin('login.php');

    $db = getDB();
    $rows = $db->query("SELECT * FROM orders ORDER BY id DESC")->fetchAll();

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=zenkit_orders_' . date('Ymd_His') . '.csv');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['Order_Number', 'Date', 'Customer_Name', 'Email', 'Phone', 'Address', 'City', 'Postal_Code', 'Payment_Method', 'Payment_Status', 'Subtotal', 'Discount', 'Final_Amount', 'Order_Status', 'Tracking_Code']);

    foreach ($rows as $r) {
        fputcsv($output, [
            $r['order_number'],
            $r['created_at'],
            $r['customer_name'],
            $r['customer_email'],
            $r['customer_phone'],
            $r['shipping_address'],
            $r['city'],
            $r['postal_code'],
            $r['payment_method'],
            $r['payment_status'],
            $r['total_amount'],
            $r['discount_amount'],
            $r['final_amount'],
            $r['order_status'],
            $r['tracking_code']
        ]);
    }
    fclose($output);
    exit;
}

$pageTitle = "Orders Management | Zenkit Admin";
require_once __DIR__ . '/includes/admin_header.php';
$db = getDB();

// Handle Status or Tracking Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_order'])) {
    $orderId = (int)$_POST['order_id'];
    $newStatus = trim($_POST['order_status']);
    $newTracking = trim($_POST['tracking_code']);

    $up = $db->prepare("UPDATE orders SET order_status = ?, tracking_code = ? WHERE id = ?");
    $up->execute([$newStatus, $newTracking, $orderId]);
    setFlash('success', "Order #$orderId updated to $newStatus.");
    header("Location: orders.php");
    exit;
}

$stmt = $db->query("SELECT * FROM orders ORDER BY id DESC");
$orders = $stmt->fetchAll();
?>

<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px;">
  <div>
    <h1 style="font-size: 1.6rem; font-weight: 800; color: #1E293B; margin-bottom: 4px;">Orders & Fulfillment</h1>
    <p style="color: #64748B; font-size: 0.88rem;">Track customer orders, dispatch status, and update logistics tracking numbers.</p>
  </div>
  <a href="orders.php?export=csv" class="btn btn-outline" style="font-size: 0.85rem; font-weight: 700; background: #FFFFFF; border-color: var(--fk-blue); color: var(--fk-blue);">
    📊 Export Orders to CSV (Excel)
  </a>
</div>

<div style="background: #FFFFFF; border: 1px solid var(--border-light); border-radius: var(--radius-xs); padding: 20px; box-shadow: var(--shadow-sm);">
  <div class="table-responsive">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Order ID</th>
          <th>Customer Details</th>
          <th>Shipping Address</th>
          <th>Items Ordered</th>
          <th>Amount</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($orders as $ord): 
          $itemStmt = $db->prepare("SELECT * FROM order_items WHERE order_id = ?");
          $itemStmt->execute([$ord['id']]);
          $items = $itemStmt->fetchAll();
        ?>
          <tr>
            <td>
              <strong style="color: var(--fk-blue); font-family: monospace; font-size: 13px;"><?= htmlspecialchars($ord['order_number']) ?></strong>
              <div style="font-size: 11px; color: #94A3B8; margin-top: 2px;"><?= date('M d, H:i', strtotime($ord['created_at'])) ?></div>
              <div style="font-size: 11px; color: #64748B; font-family: monospace; margin-top: 2px;">Track: <?= htmlspecialchars($ord['tracking_code']) ?></div>
            </td>
            <td>
              <div style="font-weight: 700; color: #1E293B;"><?= htmlspecialchars($ord['customer_name']) ?></div>
              <div style="font-size: 12px; color: #64748B;"><?= htmlspecialchars($ord['customer_email']) ?></div>
              <div style="font-size: 12px; color: #64748B;">📞 <?= htmlspecialchars($ord['customer_phone']) ?></div>
            </td>
            <td style="font-size: 12px; max-width: 220px; color: #475569;">
              <?= htmlspecialchars($ord['shipping_address']) ?>, <?= htmlspecialchars($ord['city']) ?> - <?= htmlspecialchars($ord['postal_code']) ?>
            </td>
            <td style="font-size: 12px;">
              <?php foreach ($items as $it): ?>
                <div style="margin-bottom: 2px;">
                  • <?= htmlspecialchars($it['product_name']) ?> <strong style="color: #1E293B;">x<?= $it['quantity'] ?></strong>
                </div>
              <?php endforeach; ?>
            </td>
            <td>
              <div style="font-weight: 900; color: #1E293B; font-size: 14px;"><?= formatPrice($ord['final_amount']) ?></div>
              <div style="font-size: 11px; color: #16A34A;"><?= htmlspecialchars($ord['payment_method']) ?></div>
              <div style="font-size: 11px; font-weight: 700; color: <?= $ord['payment_status'] === 'Paid' ? '#16A34A' : '#D97706' ?>;">
                <?= htmlspecialchars($ord['payment_status']) ?>
              </div>
            </td>
            <td>
              <form action="orders.php" method="POST" style="display: flex; flex-direction: column; gap: 6px;">
                <input type="hidden" name="order_id" value="<?= $ord['id'] ?>">
                <input type="hidden" name="update_order" value="1">

                <select name="order_status" class="form-control" style="font-size: 11px; padding: 4px 6px;">
                  <option value="Processing" <?= $ord['order_status'] === 'Processing' ? 'selected' : '' ?>>Processing</option>
                  <option value="Packed" <?= $ord['order_status'] === 'Packed' ? 'selected' : '' ?>>Packed</option>
                  <option value="Shipped" <?= $ord['order_status'] === 'Shipped' ? 'selected' : '' ?>>Shipped</option>
                  <option value="Delivered" <?= $ord['order_status'] === 'Delivered' ? 'selected' : '' ?>>Delivered</option>
                  <option value="Cancelled" <?= $ord['order_status'] === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                </select>

                <input type="text" name="tracking_code" value="<?= htmlspecialchars($ord['tracking_code']) ?>" class="form-control" style="font-size: 11px; padding: 4px 6px;" placeholder="Tracking ID">

                <button type="submit" class="btn btn-outline" style="font-size: 10px; padding: 4px; font-weight: 700;">Save Update</button>
              </form>
            </td>
            <td>
              <a href="../order-confirmation.php?id=<?= $ord['id'] ?>" target="_blank" class="btn btn-outline" style="font-size: 11px; padding: 4px 8px;">
                View Receipt
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
