<?php
$pageTitle = "All Tech & Gadgets Catalog | Zenkit";
require_once __DIR__ . '/includes/header.php';

$db = getDB();

// Query Filters
$catSlug = trim($_GET['category'] ?? '');
$searchQuery = trim($_GET['q'] ?? '');
$minPrice = isset($_GET['min_price']) && is_numeric($_GET['min_price']) ? (float)$_GET['min_price'] : 0;
$maxPrice = isset($_GET['max_price']) && is_numeric($_GET['max_price']) ? (float)$_GET['max_price'] : 999999;
$minRating = isset($_GET['rating']) && is_numeric($_GET['rating']) ? (float)$_GET['rating'] : 0;
$sortBy = trim($_GET['sort'] ?? 'featured');

// Base query
$sql = "SELECT p.*, c.name as category_name, c.slug as category_slug 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE 1=1";
$params = [];

if (!empty($catSlug)) {
    $sql .= " AND c.slug = ?";
    $params[] = $catSlug;
}

if (!empty($searchQuery)) {
    $sql .= " AND (p.name LIKE ? OR p.description LIKE ? OR p.subtitle LIKE ?)";
    $term = "%$searchQuery%";
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
}

if ($minPrice > 0) {
    $sql .= " AND p.price >= ?";
    $params[] = $minPrice;
}
if ($maxPrice < 999999) {
    $sql .= " AND p.price <= ?";
    $params[] = $maxPrice;
}
if ($minRating > 0) {
    $sql .= " AND p.rating >= ?";
    $params[] = $minRating;
}

// Sorting
switch ($sortBy) {
    case 'price_low':
        $sql .= " ORDER BY p.price ASC";
        break;
    case 'price_high':
        $sql .= " ORDER BY p.price DESC";
        break;
    case 'rating':
        $sql .= " ORDER BY p.rating DESC";
        break;
    default:
        $sql .= " ORDER BY p.is_featured DESC, p.id ASC";
        break;
}

$stmt = $db->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

$allCategories = $db->query("SELECT * FROM categories ORDER BY id ASC")->fetchAll();
?>

<main class="main-content">
  <div class="container">
    
    <!-- Breadcrumb -->
    <div style="font-size: 12px; color: #878787; margin-bottom: 14px;">
      <a href="index.php" style="color: var(--fk-blue);">Home</a> &gt; 
      <span>Electronics & Gadgets</span>
      <?php if (!empty($catSlug)): ?>
        &gt; <span style="color: #212121; font-weight: 600; text-transform: capitalize;"><?= htmlspecialchars(str_replace('-', ' ', $catSlug)) ?></span>
      <?php endif; ?>
      <?php if (!empty($searchQuery)): ?>
        &gt; <span>Results for "<?= htmlspecialchars($searchQuery) ?>"</span>
      <?php endif; ?>
    </div>

    <!-- 2-Column Catalog Layout (Flipkart Sidebar + Results Grid) -->
    <div style="display: grid; grid-template-columns: 280px 1fr; gap: 16px; align-items: start;">
      
      <!-- Left Filters Sidebar -->
      <aside style="background: #FFFFFF; border-radius: var(--radius-xs); border: 1px solid var(--border-light); padding: 18px; box-shadow: var(--shadow-sm); position: sticky; top: 76px;">
        <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid var(--border-light); padding-bottom: 12px; margin-bottom: 16px;">
          <h3 style="font-size: 1.1rem; font-weight: 800; color: #1E293B;">Filters</h3>
          <a href="shop.php" style="font-size: 12px; color: var(--fk-blue); font-weight: 700;">CLEAR ALL</a>
        </div>

        <form action="shop.php" method="GET" id="filter-form">
          <?php if (!empty($searchQuery)): ?>
            <input type="hidden" name="q" value="<?= htmlspecialchars($searchQuery) ?>">
          <?php endif; ?>

          <!-- Categories List -->
          <div style="margin-bottom: 20px;">
            <div style="font-size: 13px; font-weight: 700; color: #1E293B; margin-bottom: 10px; text-transform: uppercase;">Category</div>
            <div style="display: flex; flex-direction: column; gap: 8px;">
              <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; cursor: pointer;">
                <input type="radio" name="category" value="" <?= empty($catSlug) ? 'checked' : '' ?> onchange="this.form.submit()">
                <span>All Categories</span>
              </label>
              <?php foreach ($allCategories as $cat): ?>
                <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; cursor: pointer;">
                  <input type="radio" name="category" value="<?= htmlspecialchars($cat['slug']) ?>" <?= $catSlug === $cat['slug'] ? 'checked' : '' ?> onchange="this.form.submit()">
                  <span><?= htmlspecialchars($cat['name']) ?></span>
                </label>
              <?php endforeach; ?>
            </div>
          </div>

          <!-- Price Range Filter -->
          <div style="margin-bottom: 20px; border-top: 1px solid var(--border-subtle); padding-top: 16px;">
            <div style="font-size: 13px; font-weight: 700; color: #1E293B; margin-bottom: 10px; text-transform: uppercase;">Price Range</div>
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 10px;">
              <input type="number" name="min_price" class="form-control" placeholder="Min ₹" value="<?= $minPrice ?: '' ?>" style="padding: 6px; font-size: 12px;">
              <span>to</span>
              <input type="number" name="max_price" class="form-control" placeholder="Max ₹" value="<?= $maxPrice < 999999 ? $maxPrice : '' ?>" style="padding: 6px; font-size: 12px;">
            </div>
            <button type="submit" class="btn btn-outline" style="width: 100%; font-size: 12px; padding: 6px;">Apply Price</button>
          </div>

          <!-- Customer Ratings Filter -->
          <div style="margin-bottom: 20px; border-top: 1px solid var(--border-subtle); padding-top: 16px;">
            <div style="font-size: 13px; font-weight: 700; color: #1E293B; margin-bottom: 10px; text-transform: uppercase;">Customer Rating</div>
            <div style="display: flex; flex-direction: column; gap: 8px;">
              <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; cursor: pointer;">
                <input type="radio" name="rating" value="4.5" <?= $minRating >= 4.5 ? 'checked' : '' ?> onchange="this.form.submit()">
                <span class="fk-rating-pill" style="font-size: 11px; padding: 1px 6px;">4.5 ★ & above</span>
              </label>
              <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; cursor: pointer;">
                <input type="radio" name="rating" value="4.0" <?= ($minRating >= 4.0 && $minRating < 4.5) ? 'checked' : '' ?> onchange="this.form.submit()">
                <span class="fk-rating-pill" style="font-size: 11px; padding: 1px 6px;">4.0 ★ & above</span>
              </label>
              <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; cursor: pointer;">
                <input type="radio" name="rating" value="0" <?= $minRating == 0 ? 'checked' : '' ?> onchange="this.form.submit()">
                <span>All Ratings</span>
              </label>
            </div>
          </div>

        </form>
      </aside>

      <!-- Main Results Column -->
      <section>
        
        <!-- Header Bar with Sort Tabs -->
        <div style="background: #FFFFFF; border-radius: var(--radius-xs); border: 1px solid var(--border-light); padding: 14px 20px; margin-bottom: 16px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; box-shadow: var(--shadow-sm);">
          <div>
            <h1 style="font-size: 1.15rem; font-weight: 800; color: #1E293B; display: inline;">
              <?= !empty($catSlug) ? ucwords(str_replace('-', ' ', $catSlug)) : 'Electronics & Gadgets' ?>
            </h1>
            <span style="font-size: 12px; color: #878787; margin-left: 8px;">
              (Showing <?= count($products) ?> items)
            </span>
          </div>

          <!-- Sort Bar -->
          <div style="display: flex; align-items: center; gap: 8px; font-size: 13px;">
            <span style="font-weight: 700; color: #1E293B;">Sort By:</span>
            <a href="shop.php?category=<?= urlencode($catSlug) ?>&q=<?= urlencode($searchQuery) ?>&sort=featured" style="padding: 4px 10px; border-radius: 2px; font-weight: <?= $sortBy === 'featured' ? '700' : '500' ?>; color: <?= $sortBy === 'featured' ? 'var(--fk-blue)' : '#565656' ?>; border-bottom: <?= $sortBy === 'featured' ? '2px solid var(--fk-blue)' : 'none' ?>;">Relevance</a>
            <a href="shop.php?category=<?= urlencode($catSlug) ?>&q=<?= urlencode($searchQuery) ?>&sort=price_low" style="padding: 4px 10px; border-radius: 2px; font-weight: <?= $sortBy === 'price_low' ? '700' : '500' ?>; color: <?= $sortBy === 'price_low' ? 'var(--fk-blue)' : '#565656' ?>; border-bottom: <?= $sortBy === 'price_low' ? '2px solid var(--fk-blue)' : 'none' ?>;">Price -- Low to High</a>
            <a href="shop.php?category=<?= urlencode($catSlug) ?>&q=<?= urlencode($searchQuery) ?>&sort=price_high" style="padding: 4px 10px; border-radius: 2px; font-weight: <?= $sortBy === 'price_high' ? '700' : '500' ?>; color: <?= $sortBy === 'price_high' ? 'var(--fk-blue)' : '#565656' ?>; border-bottom: <?= $sortBy === 'price_high' ? '2px solid var(--fk-blue)' : 'none' ?>;">Price -- High to Low</a>
            <a href="shop.php?category=<?= urlencode($catSlug) ?>&q=<?= urlencode($searchQuery) ?>&sort=rating" style="padding: 4px 10px; border-radius: 2px; font-weight: <?= $sortBy === 'rating' ? '700' : '500' ?>; color: <?= $sortBy === 'rating' ? 'var(--fk-blue)' : '#565656' ?>; border-bottom: <?= $sortBy === 'rating' ? '2px solid var(--fk-blue)' : 'none' ?>;">Customer Rating</a>
          </div>
        </div>

        <!-- Products Listing -->
        <?php if (empty($products)): ?>
          <div style="background: #FFFFFF; border-radius: var(--radius-xs); border: 1px solid var(--border-light); padding: 60px 20px; text-align: center;">
            <div style="font-size: 3rem; margin-bottom: 12px;">🔍</div>
            <h2 style="font-size: 1.2rem; font-weight: 700; color: #1E293B; margin-bottom: 6px;">No Matching Products Found</h2>
            <p style="color: #64748B; font-size: 0.9rem; margin-bottom: 20px;">Try adjusting your search criteria or price filters.</p>
            <a href="shop.php" class="btn btn-fk-primary">Reset All Filters</a>
          </div>
        <?php else: ?>
          <div class="product-grid" style="grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));">
            <?php foreach ($products as $prod): 
              $discPercent = round((($prod['original_price'] - $prod['price']) / $prod['original_price']) * 100);
            ?>
              <div class="fk-card">
                <span class="fk-card-badge"><?= $discPercent ?>% OFF</span>
                <button 
                  type="button" 
                  class="fk-card-3d-btn" 
                  onclick="openProduct3D('<?= htmlspecialchars($prod['model_3d_type'] ?? 'laptop') ?>', '<?= htmlspecialchars(addslashes($prod['name'])) ?>')"
                  title="Open 3D WebGL Inspection"
                >
                  ⬡ 3D
                </button>

                <a href="product.php?id=<?= $prod['id'] ?>" class="fk-card-img-wrap">
                  <img src="<?= htmlspecialchars($prod['image_url']) ?>" alt="<?= htmlspecialchars($prod['name']) ?>" loading="lazy">
                </a>

                <a href="product.php?id=<?= $prod['id'] ?>" class="fk-card-title">
                  <?= htmlspecialchars($prod['name']) ?>
                </a>
                
                <div class="fk-card-subtitle">
                  <?= htmlspecialchars($prod['subtitle'] ?? 'Zenkit Certified') ?>
                </div>

                <div class="fk-rating-row">
                  <span class="fk-rating-pill">
                    <?= number_format($prod['rating'], 1) ?> ★
                  </span>
                  <span class="fk-rating-count">(<?= number_format($prod['reviews_count']) ?>)</span>
                </div>

                <div class="fk-price-row">
                  <span class="fk-curr-price"><?= formatPrice($prod['price']) ?></span>
                  <span class="fk-orig-price"><?= formatPrice($prod['original_price']) ?></span>
                  <span class="fk-disc-percent"><?= $discPercent ?>% off</span>
                </div>

                <div class="fk-card-actions">
                  <button onclick="handleAddToCart(<?= $prod['id'] ?>)" class="btn-card-cart">
                    Add to Cart
                  </button>
                  <a href="product.php?id=<?= $prod['id'] ?>" class="btn-card-buy">
                    Buy Now
                  </a>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

      </section>

    </div>

  </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
