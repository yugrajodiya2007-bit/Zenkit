<?php
$pageTitle = "Configure Product | Zenkit Admin";
require_once __DIR__ . '/includes/admin_header.php';
$db = getDB();

$id = (int)($_GET['id'] ?? 0);
$product = null;

if ($id > 0) {
    $stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch();
}

$categories = $db->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    if (empty($slug)) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
    }
    $subtitle = trim($_POST['subtitle'] ?? '');
    $categoryId = (int)($_POST['category_id'] ?? 1);
    $price = (float)($_POST['price'] ?? 0);
    $originalPrice = !empty($_POST['original_price']) ? (float)$_POST['original_price'] : null;
    $stock = (int)($_POST['stock'] ?? 0);
    $imageUrl = trim($_POST['image_url'] ?? 'assets/images/products/zen_laptop.jpg');
    $model3d = trim($_POST['model_3d_type'] ?? 'orb');
    $description = trim($_POST['description'] ?? '');
    $specsJson = trim($_POST['specs_json'] ?? '{}');
    $optionsJson = trim($_POST['options_json'] ?? '{}');
    $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
    $isBestseller = isset($_POST['is_bestseller']) ? 1 : 0;

    if (empty($name) || $price <= 0) {
        setFlash('error', 'Product name and price are required.');
    } else {
        if ($product) {
            $sql = "UPDATE products SET category_id = ?, name = ?, slug = ?, subtitle = ?, description = ?, specs_json = ?, options_json = ?, price = ?, original_price = ?, stock = ?, is_featured = ?, is_bestseller = ?, image_url = ?, model_3d_type = ? WHERE id = ?";
            $upd = $db->prepare($sql);
            $upd->execute([$categoryId, $name, $slug, $subtitle, $description, $specsJson, $optionsJson, $price, $originalPrice, $stock, $isFeatured, $isBestseller, $imageUrl, $model3d, $id]);
            setFlash('success', 'Product updated successfully.');
        } else {
            $sql = "INSERT INTO products (category_id, name, slug, subtitle, description, specs_json, options_json, price, original_price, stock, is_featured, is_bestseller, image_url, model_3d_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $ins = $db->prepare($sql);
            $ins->execute([$categoryId, $name, $slug, $subtitle, $description, $specsJson, $optionsJson, $price, $originalPrice, $stock, $isFeatured, $isBestseller, $imageUrl, $model3d]);
            setFlash('success', 'New product added to store.');
        }
        header('Location: products.php');
        exit;
    }
}
?>

<div style="max-width: 800px; margin: 0 auto;">
  <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
    <h1 style="font-size: 1.5rem; font-weight: 800; color: #1E293B;">
      <?= $product ? 'Edit Product: ' . htmlspecialchars($product['name']) : 'Add New Product' ?>
    </h1>
    <a href="products.php" class="btn btn-outline btn-sm">&larr; Back to Products</a>
  </div>

  <div style="background: #FFFFFF; border: 1px solid var(--border-light); border-radius: var(--radius-xs); padding: 24px; box-shadow: var(--shadow-sm);">
    <form method="POST">
      
      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
        <div class="form-group" style="grid-column: span 2;">
          <label class="form-label">Product Title *</label>
          <input type="text" name="name" required class="form-control" value="<?= htmlspecialchars($product['name'] ?? '') ?>" placeholder="e.g. Zenkit ProBook Ultrathin 15">
        </div>

        <div class="form-group">
          <label class="form-label">URL Slug</label>
          <input type="text" name="slug" class="form-control" value="<?= htmlspecialchars($product['slug'] ?? '') ?>" placeholder="zenkit-probook-15">
        </div>

        <div class="form-group">
          <label class="form-label">Category</label>
          <select name="category_id" class="form-control">
            <?php foreach ($categories as $c): ?>
              <option value="<?= $c['id'] ?>" <?= ($product && $product['category_id'] == $c['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($c['name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group" style="grid-column: span 2;">
          <label class="form-label">Subtitle / Key Features</label>
          <input type="text" name="subtitle" class="form-control" value="<?= htmlspecialchars($product['subtitle'] ?? '') ?>" placeholder="15.6 QHD 120Hz IPS Display • Aerospace Aluminum Chassis">
        </div>

        <div class="form-group">
          <label class="form-label">Selling Price (₹) *</label>
          <input type="number" step="0.01" name="price" required class="form-control" value="<?= htmlspecialchars($product['price'] ?? '') ?>">
        </div>

        <div class="form-group">
          <label class="form-label">Original MRP (₹)</label>
          <input type="number" step="0.01" name="original_price" class="form-control" value="<?= htmlspecialchars($product['original_price'] ?? '') ?>">
        </div>

        <div class="form-group">
          <label class="form-label">Available Stock *</label>
          <input type="number" name="stock" required class="form-control" value="<?= htmlspecialchars($product['stock'] ?? 50) ?>">
        </div>

        <div class="form-group">
          <label class="form-label">3D WebGL Model</label>
          <select name="model_3d_type" class="form-control">
            <option value="keyboard" <?= ($product && $product['model_3d_type'] === 'keyboard') ? 'selected' : '' ?>>Laptop / Keyboard Matrix</option>
            <option value="buds" <?= ($product && $product['model_3d_type'] === 'buds') ? 'selected' : '' ?>>Earbuds &amp; Vault</option>
            <option value="watch" <?= ($product && $product['model_3d_type'] === 'watch') ? 'selected' : '' ?>>Smartwatch</option>
            <option value="orb" <?= ($product && $product['model_3d_type'] === 'orb') ? 'selected' : '' ?>>Smart Lamp / Orb</option>
          </select>
        </div>

        <div class="form-group" style="grid-column: span 2;">
          <label class="form-label">Image Path</label>
          <input type="text" name="image_url" required class="form-control" value="<?= htmlspecialchars($product['image_url'] ?? 'assets/images/products/zen_laptop.jpg') ?>">
          <div style="font-size: 0.75rem; color: #64748B; margin-top: 4px;">Available: assets/images/products/zen_laptop.jpg, zen_buds.jpg, zen_watch.jpg, zen_key.jpg, zen_lamp.jpg, zen_mouse.jpg</div>
        </div>

        <div class="form-group" style="grid-column: span 2;">
          <label class="form-label">Full Description</label>
          <textarea name="description" rows="4" class="form-control"><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
        </div>

        <div class="form-group" style="grid-column: span 2;">
          <label class="form-label">Specifications (JSON Key/Value)</label>
          <textarea name="specs_json" rows="3" class="form-control" style="font-family: monospace; font-size: 0.85rem;"><?= htmlspecialchars($product['specs_json'] ?? '{"Processor": "Intel i7", "RAM": "16GB"}') ?></textarea>
        </div>

        <div class="form-group" style="grid-column: span 2;">
          <label class="form-label">Product Options (Colors, Variants, Addons, Highlights, FAQs in JSON)</label>
          <textarea name="options_json" rows="6" class="form-control" style="font-family: monospace; font-size: 0.85rem;"><?= htmlspecialchars($product['options_json'] ?? '{"colors":[{"name":"Space Charcoal","hex":"#262626"}],"variants":[{"name":"Standard Edition","price_diff":0,"sku":"ZEN-STD"}],"addons":[{"id":"care_1","name":"1-Year Protection Plan","price":19,"desc":"Damage coverage."}],"highlights":["Premium build"],"faqs":[{"q":"Warranty?","a":"1 year"}]}') ?></textarea>
          <div style="font-size: 0.75rem; color: #64748B; margin-top: 4px;">Configures interactive color swatches, edition selector pills, protection plans, key highlights, and customer Q&amp;A.</div>
        </div>

        <div style="grid-column: span 2; display: flex; gap: 20px;">
          <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 0.9rem;">
            <input type="checkbox" name="is_featured" value="1" <?= (!empty($product['is_featured'])) ? 'checked' : '' ?>>
            <span>Featured Product</span>
          </label>

          <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 0.9rem;">
            <input type="checkbox" name="is_bestseller" value="1" <?= (!empty($product['is_bestseller'])) ? 'checked' : '' ?>>
            <span>Deal of the Day Shelf</span>
          </label>
        </div>
      </div>

      <div style="margin-top: 24px; display: flex; gap: 12px;">
        <button type="submit" class="btn btn-fk-buy" style="padding: 10px 24px;">
          Save Product ➔
        </button>
        <a href="products.php" class="btn btn-outline">Cancel</a>
      </div>

    </form>
  </div>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
