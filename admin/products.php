<?php
$pageTitle = "Products Management | Zenkit Admin";
require_once __DIR__ . '/includes/admin_header.php';
$db = getDB();

if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $id = (int)($_GET['id'] ?? 0);
    if ($id > 0) {
        $del = $db->prepare("DELETE FROM products WHERE id = ?");
        $del->execute([$id]);
        setFlash('success', 'Product removed from store.');
        header('Location: products.php');
        exit;
    }
}

$stmt = $db->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.id ASC");
$products = $stmt->fetchAll();
?>

<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px;">
  <div>
    <h1 style="font-size: 1.6rem; font-weight: 800; color: #1E293B; margin-bottom: 4px;">Products Inventory</h1>
    <p style="color: #64748B; font-size: 0.88rem;">Manage, update pricing, inventory stock, and 3D models.</p>
  </div>
  <a href="product_edit.php" class="btn btn-fk-buy" style="font-size: 0.85rem; padding: 8px 16px;">+ Add New Product</a>
</div>

<div style="background: #FFFFFF; border: 1px solid var(--border-light); border-radius: var(--radius-xs); padding: 20px; box-shadow: var(--shadow-sm);">
  <div class="table-responsive">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Image</th>
          <th>Product Name</th>
          <th>Category</th>
          <th>Price</th>
          <th>Stock</th>
          <th>3D Model</th>
          <th>Featured</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($products as $p): ?>
          <tr>
            <td style="width: 50px;">
              <img src="../<?= htmlspecialchars($p['image_url']) ?>" alt="" style="width: 44px; height: 44px; object-fit: contain; border: 1px solid var(--border-light); border-radius: 4px; padding: 2px;">
            </td>
            <td>
              <div style="font-weight: 700; color: #1E293B; font-size: 0.9rem;"><?= htmlspecialchars($p['name']) ?></div>
              <div style="font-size: 0.75rem; color: #64748B;"><?= htmlspecialchars($p['slug']) ?></div>
            </td>
            <td>
              <span class="fk-assured-pill" style="font-size: 0.72rem;"><?= htmlspecialchars($p['category_name']) ?></span>
            </td>
            <td style="font-weight: 800; color: #1E293B;"><?= formatPrice($p['price']) ?></td>
            <td>
              <span style="font-weight: 700; color: <?= $p['stock'] > 10 ? '#16A34A' : '#DC2626' ?>;">
                <?= $p['stock'] ?> in stock
              </span>
            </td>
            <td>
              <span style="font-family: monospace; font-size: 0.8rem; background: #F1F5F9; padding: 2px 6px; border-radius: 4px;">
                <?= htmlspecialchars($p['model_3d_type'] ?? 'orb') ?>
              </span>
            </td>
            <td><?= $p['is_featured'] ? '★ Yes' : '—' ?></td>
            <td>
              <div style="display: flex; gap: 6px;">
                <a href="product_edit.php?id=<?= $p['id'] ?>" class="btn btn-outline btn-sm" style="font-size: 0.75rem; padding: 4px 8px;">
                  Edit
                </a>
                <a href="products.php?action=delete&id=<?= $p['id'] ?>" onclick="return confirm('Remove product from store?')" class="btn btn-sm" style="background: #FEE2E2; color: #DC2626; border: none; font-size: 0.75rem; padding: 4px 8px;">
                  Delete
                </a>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
