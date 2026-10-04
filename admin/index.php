<?php
$pageTitle = "Admin Dashboard | Zenkit Marketplace";
require_once __DIR__ . '/includes/admin_header.php';
$db = getDB();

$totalSales = $db->query("SELECT SUM(final_amount) FROM orders WHERE payment_status = 'Paid'")->fetchColumn() ?: 0.00;
$totalOrders = $db->query("SELECT COUNT(*) FROM orders")->fetchColumn() ?: 0;
$totalProducts = $db->query("SELECT COUNT(*) FROM products")->fetchColumn() ?: 0;
$totalUsers = $db->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn() ?: 0;

$recentOrders = $db->query("SELECT * FROM orders ORDER BY id DESC LIMIT 6")->fetchAll();
?>

<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px;">
  <div>
    <h1 style="font-size: 1.6rem; font-weight: 800; color: #1E293B; margin-bottom: 4px;">Marketplace Analytics</h1>
    <p style="color: #64748B; font-size: 0.88rem;">Overview of active orders, sales volume, and catalog inventory.</p>
  </div>
  <a href="product_edit.php" class="btn btn-fk-buy" style="font-size: 0.85rem; padding: 8px 16px;">+ Add New Product</a>
</div>

<!-- 4 Stats Cards Grid -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
  <div style="background: #FFFFFF; border: 1px solid var(--border-light); border-radius: var(--radius-xs); padding: 18px; box-shadow: var(--shadow-sm);">
    <div style="font-size: 0.78rem; text-transform: uppercase; font-weight: 800; color: #64748B;">Total Settled Revenue</div>
    <div style="font-size: 1.8rem; font-weight: 900; color: #16A34A; margin-top: 4px;"><?= formatPrice($totalSales) ?></div>
    <div style="font-size: 0.75rem; color: #94A3B8; margin-top: 4px;">Across all completed payment channels</div>
  </div>

  <div style="background: #FFFFFF; border: 1px solid var(--border-light); border-radius: var(--radius-xs); padding: 18px; box-shadow: var(--shadow-sm);">
    <div style="font-size: 0.78rem; text-transform: uppercase; font-weight: 800; color: #64748B;">Total Orders</div>
    <div style="font-size: 1.8rem; font-weight: 900; color: var(--fk-blue); margin-top: 4px;"><?= $totalOrders ?></div>
    <div style="font-size: 0.75rem; color: #94A3B8; margin-top: 4px;">Orders logged in database</div>
  </div>

  <div style="background: #FFFFFF; border: 1px solid var(--border-light); border-radius: var(--radius-xs); padding: 18px; box-shadow: var(--shadow-sm);">
    <div style="font-size: 0.78rem; text-transform: uppercase; font-weight: 800; color: #64748B;">Active Products</div>
    <div style="font-size: 1.8rem; font-weight: 900; color: #D97706; margin-top: 4px;"><?= $totalProducts ?></div>
    <div style="font-size: 0.75rem; color: #94A3B8; margin-top: 4px;">Consumer tech products in store</div>
  </div>

  <div style="background: #FFFFFF; border: 1px solid var(--border-light); border-radius: var(--radius-xs); padding: 18px; box-shadow: var(--shadow-sm);">
    <div style="font-size: 0.78rem; text-transform: uppercase; font-weight: 800; color: #64748B;">Registered Customers</div>
    <div style="font-size: 1.8rem; font-weight: 900; color: #1E293B; margin-top: 4px;"><?= $totalUsers ?></div>
    <div style="font-size: 0.75rem; color: #94A3B8; margin-top: 4px;">Customer accounts created</div>
  </div>
</div>

<!-- Recent Orders Table -->
<div style="background: #FFFFFF; border: 1px solid var(--border-light); border-radius: var(--radius-xs); padding: 20px; box-shadow: var(--shadow-sm);">
  <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; border-bottom: 1px solid var(--border-light); padding-bottom: 12px;">
    <h3 style="font-size: 1.1rem; font-weight: 800; color: #1E293B;">Recent Orders</h3>
    <a href="orders.php" style="color: var(--fk-blue); font-size: 0.85rem; font-weight: 700;">View All Orders &rarr;</a>
  </div>

  <div class="table-responsive">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Order ID</th>
          <th>Customer</th>
          <th>Payment</th>
          <th>Amount</th>
          <th>Status</th>
          <th>Date</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($recentOrders as $ord): ?>
          <tr>
            <td>
              <strong style="color: var(--fk-blue); font-family: monospace;"><?= htmlspecialchars($ord['order_number']) ?></strong>
            </td>
            <td>
              <div style="font-weight: 700; color: #1E293B;"><?= htmlspecialchars($ord['customer_name']) ?></div>
              <div style="font-size: 0.75rem; color: #64748B;"><?= htmlspecialchars($ord['customer_phone']) ?></div>
            </td>
            <td><?= htmlspecialchars($ord['payment_method']) ?></td>
            <td style="font-weight: 800; color: #1E293B;"><?= formatPrice($ord['final_amount']) ?></td>
            <td>
              <span class="fk-rating-pill" style="background: <?= $ord['order_status'] === 'Delivered' ? '#16A34A' : 'var(--fk-blue)' ?>;">
                ● <?= htmlspecialchars($ord['order_status']) ?>
              </span>
            </td>
            <td style="font-size: 0.8rem; color: #64748B;"><?= date('M d, H:i', strtotime($ord['created_at'])) ?></td>
            <td>
              <a href="orders.php" class="btn btn-outline btn-sm" style="font-size: 0.75rem; padding: 4px 8px;">
                Manage
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
