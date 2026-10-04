<?php
$pageTitle = "Categories Management | Zenkit Admin";
require_once __DIR__ . '/includes/admin_header.php';
$db = getDB();

// Handle Add Category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $name = trim($_POST['name'] ?? '');
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
    $desc = trim($_POST['description'] ?? '');
    $icon = trim($_POST['icon'] ?? 'tag');

    if (!empty($name)) {
        $ins = $db->prepare("INSERT INTO categories (name, slug, description, icon) VALUES (?, ?, ?, ?)");
        $ins->execute([$name, $slug, $desc, $icon]);
        setFlash('success', "Category '$name' created.");
        header("Location: categories.php");
        exit;
    }
}

// Handle Delete Category
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $catId = (int)$_GET['id'];
    $del = $db->prepare("DELETE FROM categories WHERE id = ?");
    $del->execute([$catId]);
    setFlash('info', "Category removed.");
    header("Location: categories.php");
    exit;
}

$categories = $db->query("SELECT c.*, COUNT(p.id) as product_count FROM categories c LEFT JOIN products p ON c.id = p.category_id GROUP BY c.id ORDER BY c.id ASC")->fetchAll();
?>

<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px;">
  <div>
    <h1 style="font-size: 1.6rem; font-weight: 800; color: #1E293B; margin-bottom: 4px;">Product Categories</h1>
    <p style="color: #64748B; font-size: 0.88rem;">Manage storefront product taxonomies and department filters.</p>
  </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 340px; gap: 24px; align-items: start;">
  
  <!-- Left Categories Table -->
  <div style="background: #FFFFFF; border: 1px solid var(--border-light); border-radius: var(--radius-xs); padding: 20px; box-shadow: var(--shadow-sm);">
    <table class="admin-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Category Name</th>
          <th>Slug</th>
          <th>Products</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($categories as $cat): ?>
          <tr>
            <td><?= $cat['id'] ?></td>
            <td>
              <div style="font-weight: 700; color: #1E293B;"><?= htmlspecialchars($cat['name']) ?></div>
              <div style="font-size: 11px; color: #64748B;"><?= htmlspecialchars($cat['description']) ?></div>
            </td>
            <td><code><?= htmlspecialchars($cat['slug']) ?></code></td>
            <td>
              <span style="font-weight: 700; color: var(--fk-blue);"><?= $cat['product_count'] ?> products</span>
            </td>
            <td>
              <?php if ($cat['product_count'] == 0): ?>
                <a href="categories.php?action=delete&id=<?= $cat['id'] ?>" onclick="return confirm('Delete this category?')" style="color: #DC2626; font-size: 12px; font-weight: 700;">Delete</a>
              <?php else: ?>
                <span style="font-size: 11px; color: #94A3B8;">In Use</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- Right Add Category Form -->
  <div style="background: #FFFFFF; border: 1px solid var(--border-light); border-radius: var(--radius-xs); padding: 20px; box-shadow: var(--shadow-sm);">
    <h3 style="font-size: 1.1rem; font-weight: 800; color: #1E293B; margin-bottom: 16px;">Add New Category</h3>
    
    <form action="categories.php" method="POST">
      <input type="hidden" name="add_category" value="1">
      
      <div class="form-group">
        <label class="form-label">Category Name *</label>
        <input type="text" name="name" class="form-control" placeholder="e.g. Smart Monitors" required>
      </div>

      <div class="form-group">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="2" placeholder="Brief department summary"></textarea>
      </div>

      <div class="form-group">
        <label class="form-label">Icon Identifier</label>
        <input type="text" name="icon" class="form-control" value="tag" placeholder="e.g. laptop, watch, headphones">
      </div>

      <button type="submit" class="btn btn-fk-buy" style="width: 100%; font-size: 13px; padding: 10px;">
        + Save Category
      </button>
    </form>
  </div>

</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
