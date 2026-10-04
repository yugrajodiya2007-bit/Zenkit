<?php
require_once __DIR__ . '/includes/helpers.php';
$db = getDB();

$productId = isset($_GET['id']) ? (int)$_GET['id'] : 1;
$stmt = $db->prepare("SELECT p.*, c.name as category_name, c.slug as category_slug 
                      FROM products p 
                      LEFT JOIN categories c ON p.category_id = c.id 
                      WHERE p.id = ?");
$stmt->execute([$productId]);
$product = $stmt->fetch();

if (!$product) {
    safeRedirect('shop.php');
}

// Fetch Reviews
$revStmt = $db->prepare("SELECT * FROM reviews WHERE product_id = ? ORDER BY id DESC");
$revStmt->execute([$productId]);
$reviews = $revStmt->fetchAll();

// Rating breakdown calculation
$ratingCounts = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
foreach ($reviews as $r) {
    $rt = (int)$r['rating'];
    if (isset($ratingCounts[$rt])) $ratingCounts[$rt]++;
}
$totalReviews = max(1, count($reviews));

// Handle new review submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    $rName = trim($_POST['user_name'] ?? 'Verified Buyer');
    $rRating = (int)($_POST['rating'] ?? 5);
    $rComment = trim($_POST['comment'] ?? '');

    if (!empty($rComment)) {
        $ins = $db->prepare("INSERT INTO reviews (product_id, user_name, rating, comment, is_verified) VALUES (?, ?, ?, ?, 1)");
        $ins->execute([$productId, $rName, $rRating, $rComment]);
        
        // Recalculate average rating
        $avg = $db->prepare("SELECT AVG(rating), COUNT(*) FROM reviews WHERE product_id = ?");
        $avg->execute([$productId]);
        [$newAvg, $newCount] = $avg->fetch(PDO::FETCH_NUM);
        
        $up = $db->prepare("UPDATE products SET rating = ?, reviews_count = ? WHERE id = ?");
        $up->execute([round($newAvg, 2), $newCount, $productId]);

        setFlash('success', 'Thank you! Your verified review has been published.');
        safeRedirect("product.php?id=$productId#tab-reviews");
    }
}

// Parse Product Options (Colors, Variants, Add-ons, Highlights, FAQs)
$rawOptions = json_decode($product['options_json'] ?? '{}', true) ?: [];

$colors = $rawOptions['colors'] ?? [
    ['name' => 'Space Charcoal', 'hex' => '#262626', 'selected' => true],
    ['name' => 'Lunar Silver', 'hex' => '#E5E5E5', 'selected' => false],
    ['name' => 'Cyber Neon', 'hex' => '#06B6D4', 'selected' => false]
];

$variants = $rawOptions['variants'] ?? [
    ['name' => 'Standard Edition', 'price_diff' => 0, 'sku' => 'ZEN-STD', 'selected' => true],
    ['name' => 'Pro Performance Edition', 'price_diff' => 29, 'sku' => 'ZEN-PRO', 'selected' => false]
];

$addons = $rawOptions['addons'] ?? [
    ['id' => 'care_1', 'name' => '1-Year Zenkit Protection Plan', 'price' => 19, 'desc' => 'Complete coverage against accidental drops & liquid spills.'],
    ['id' => 'care_2', 'name' => 'Express Priority Replacement Guarantee', 'price' => 9, 'desc' => 'Same-day dispatch for any hardware defects.']
];

$highlights = $rawOptions['highlights'] ?? [
    'Engineered with premium aerospace-grade materials',
    'Ultra-efficient energy consumption with rapid charging',
    'Official Zenkit 1-Year Comprehensive Warranty included',
    '100% Tax Compliant with Instant GST Invoice Generation'
];

$faqs = $rawOptions['faqs'] ?? [
    ['q' => 'Is this product genuine and backed by warranty?', 'a' => 'Yes, 100% genuine with a manufacturer warranty serviceable at 2,400+ centers nationwide.'],
    ['q' => 'Can I get a GST tax invoice with company name?', 'a' => 'Yes! Enter your GSTIN and Business Name at checkout to receive an input tax credit invoice.'],
    ['q' => 'What is the return and replacement policy?', 'a' => 'Enjoy a hassle-free 7-day doorstep replacement if any defect is detected.']
];

$pageTitle = htmlspecialchars($product['name']) . " | Zenkit";
require_once __DIR__ . '/includes/header.php';

// Specs
$specs = json_decode($product['specs_json'] ?? '[]', true) ?: [];
$discPercent = round((($product['original_price'] - $product['price']) / $product['original_price']) * 100);

// Related Products
$relStmt = $db->prepare("SELECT * FROM products WHERE category_id = ? AND id != ? LIMIT 4");
$relStmt->execute([$product['category_id'], $productId]);
$related = $relStmt->fetchAll();

// Companion bundle product
$bundleProductStmt = $db->prepare("SELECT * FROM products WHERE id != ? ORDER BY price ASC LIMIT 1");
$bundleProductStmt->execute([$productId]);
$bundleProduct = $bundleProductStmt->fetch();
?>

<main class="main-content">
  <div class="container">
    
    <!-- Breadcrumb -->
    <div style="font-size: 12px; color: #878787; margin-bottom: 16px;">
      <a href="index.php" style="color: var(--fk-blue);">Home</a> &gt; 
      <a href="shop.php?category=<?= htmlspecialchars($product['category_slug'] ?? '') ?>" style="color: var(--fk-blue);"><?= htmlspecialchars($product['category_name'] ?? 'Electronics') ?></a> &gt; 
      <span style="color: #212121;"><?= htmlspecialchars($product['name']) ?></span>
    </div>

    <!-- Flipkart 2-Column Product Detail Layout -->
    <div class="fk-detail-container">
      
      <!-- LEFT COLUMN: Sticky Image Gallery & Action CTA -->
      <div class="fk-gallery-col">
        <div class="fk-gallery-main">
          <img src="<?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" id="main-product-img">
        </div>

        <button 
          type="button" 
          onclick="openProduct3D('<?= htmlspecialchars($product['model_3d_type'] ?? 'laptop') ?>', '<?= htmlspecialchars(addslashes($product['name'])) ?>')" 
          class="btn btn-outline" 
          style="width: 100%; margin-bottom: 16px; font-size: 13px; font-weight: 700; background: #0F172A; color: #FFFFFF; border: none; padding: 12px;"
        >
          ⬡ Launch 360° Interactive 3D Inspection
        </button>

        <!-- Low Stock Urgency Indicator -->
        <div style="background: #FFFBEB; border: 1px solid #FCD34D; border-radius: 4px; padding: 10px 14px; margin-bottom: 16px; display: flex; align-items: center; gap: 10px; font-size: 12px; color: #92400E;">
          <span style="font-size: 18px;">🔥</span>
          <div>
            <strong>Hurry! Only <?= min(8, (int)$product['stock']) ?> units left in stock</strong>
            <div style="color: #B45309; font-size: 11px;">18 customers are looking at this right now</div>
          </div>
        </div>

        <!-- Quantity Stepper Controls -->
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; background: #F8FAFC; border: 1px solid var(--border-light); border-radius: 4px; padding: 10px 16px;">
          <span style="font-weight: 700; font-size: 13px; color: #1E293B;">Select Quantity:</span>
          <div class="qty-stepper-wrap">
            <button type="button" class="qty-stepper-btn" onclick="changeQty(-1)">-</button>
            <input type="number" id="product-qty-input" class="qty-stepper-input" value="1" min="1" max="<?= (int)$product['stock'] ?>" readonly>
            <button type="button" class="qty-stepper-btn" onclick="changeQty(1)">+</button>
          </div>
        </div>

        <!-- Action Buttons (Add to Cart / Buy Now) -->
        <div class="fk-gallery-buttons">
          <button type="button" onclick="addProductToCartWithOptions(false)" class="btn btn-fk-cart" style="padding: 16px; font-size: 15px; font-weight: 800;">
            🛒 ADD TO CART
          </button>
          <button type="button" onclick="addProductToCartWithOptions(true)" class="btn btn-fk-buy" style="padding: 16px; font-size: 15px; font-weight: 800;">
            ⚡ BUY NOW
          </button>
        </div>

        <!-- Trust Badges -->
        <div style="display: flex; justify-content: space-around; margin-top: 20px; font-size: 12px; color: #64748B; text-align: center; border-top: 1px solid var(--border-subtle); padding-top: 16px;">
          <div><span>🛡️</span><br>1 Year Warranty</div>
          <div><span>🔄</span><br>7 Day Return</div>
          <div><span>🚚</span><br>Free Delivery</div>
          <div><span>📑</span><br>GST Invoice</div>
        </div>

        <!-- Share Options -->
        <div style="margin-top: 20px; padding: 12px 16px; background: #FFFFFF; border: 1px solid var(--border-light); border-radius: 4px; display: flex; align-items: center; justify-content: space-between; font-size: 12px;">
          <span style="color: #64748B; font-weight: 600;">Share Product:</span>
          <div style="display: flex; gap: 8px;">
            <button type="button" onclick="copyProductLink()" class="btn btn-outline btn-sm" style="padding: 4px 10px; font-size: 11px;">📋 Copy Link</button>
            <a href="https://api.whatsapp.com/send?text=Check out <?= urlencode($product['name']) ?> on Zenkit: <?= urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>" target="_blank" class="btn btn-outline btn-sm" style="padding: 4px 10px; font-size: 11px; color: #15803D;">💬 WhatsApp</a>
          </div>
        </div>

      </div>

      <!-- RIGHT COLUMN: Details, Options, Dynamic Price, Tabs -->
      <div class="fk-detail-info">
        
        <h1 style="font-size: 1.45rem; font-weight: 800; color: #1E293B; line-height: 1.35; margin-bottom: 6px;">
          <?= htmlspecialchars($product['name']) ?>
        </h1>
        
        <?php if (!empty($product['subtitle'])): ?>
          <p style="font-size: 13px; color: #64748B; margin-bottom: 10px; line-height: 1.4;">
            <?= htmlspecialchars($product['subtitle']) ?>
          </p>
        <?php endif; ?>

        <div class="fk-rating-row" style="margin-bottom: 14px;">
          <span class="fk-rating-pill" style="font-size: 13px; padding: 3px 8px;">
            <?= number_format($product['rating'], 1) ?> ★
          </span>
          <span style="font-weight: 700; color: #878787; font-size: 13px;">
            <?= number_format($product['reviews_count']) ?> Ratings & <?= count($reviews) ?> Reviews
          </span>
          <span style="background: #EBF3FF; color: var(--fk-blue); font-weight: 800; font-size: 11px; padding: 2px 8px; border-radius: 2px;">
            Zenkit Plus ✦
          </span>
        </div>

        <!-- Dynamic Price Display Section -->
        <div style="background: #F8FAFC; border: 1px solid var(--border-subtle); padding: 18px; border-radius: 4px; margin-bottom: 20px;">
          <div style="display: flex; align-items: baseline; gap: 12px; flex-wrap: wrap;">
            <span id="display-product-price" style="font-size: 2rem; font-weight: 900; color: #212121;"><?= formatPrice($product['price']) ?></span>
            <span style="font-size: 1rem; color: #878787; text-decoration: line-through;"><?= formatPrice($product['original_price']) ?></span>
            <span class="fk-disc-percent" style="font-size: 1.1rem;"><?= $discPercent ?>% off</span>
            
            <button 
              type="button" 
              onclick="handleToggleWishlist(<?= $product['id'] ?>, this)" 
              class="btn btn-outline" 
              style="margin-left: auto; font-size: 12px; padding: 6px 14px; font-weight: 700;"
            >
              <?= isProductInWishlist($product['id']) ? '❤️ In Wishlist' : '🤍 Add to Wishlist' ?>
            </button>
          </div>
          <div style="font-size: 12px; color: var(--fk-green); font-weight: 700; margin-top: 6px;">
            ✓ In Stock (<?= $product['stock'] ?> ready for express dispatch) • Inclusive of 18% GST • Official Tax Invoice Included
          </div>
        </div>

        <!-- PRODUCT OPTIONS INTERFACE BOX -->
        <div class="product-options-box">
          
          <!-- OPTION 1: COLOR VARIANTS -->
          <?php if (!empty($colors)): ?>
            <div style="margin-bottom: 20px;">
              <div class="opt-section-title">
                <span>Select Color</span>
                <span class="opt-current" id="active-color-name"><?= htmlspecialchars($colors[0]['name']) ?></span>
              </div>
              <div class="color-swatch-list">
                <?php foreach ($colors as $idx => $c): ?>
                  <button 
                    type="button" 
                    class="color-swatch-btn <?= $idx === 0 ? 'active' : '' ?>" 
                    data-color="<?= htmlspecialchars($c['name']) ?>" 
                    onclick="selectProductColor(this, '<?= htmlspecialchars(addslashes($c['name'])) ?>')"
                  >
                    <span class="color-dot" style="background-color: <?= htmlspecialchars($c['hex']) ?>;"></span>
                    <span><?= htmlspecialchars($c['name']) ?></span>
                  </button>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endif; ?>

          <!-- OPTION 2: CONFIGURATION / SPEC EDITIONS -->
          <?php if (!empty($variants)): ?>
            <div style="margin-bottom: 20px;">
              <div class="opt-section-title">
                <span>Select Edition / Configuration</span>
                <span class="opt-current" id="active-variant-name"><?= htmlspecialchars($variants[0]['name']) ?></span>
              </div>
              <div class="variant-pill-grid">
                <?php foreach ($variants as $idx => $v): ?>
                  <button 
                    type="button" 
                    class="variant-pill-btn <?= $idx === 0 ? 'active' : '' ?>" 
                    data-variant="<?= htmlspecialchars($v['name']) ?>" 
                    data-pricediff="<?= (float)$v['price_diff'] ?>" 
                    onclick="selectProductVariant(this, '<?= htmlspecialchars(addslashes($v['name'])) ?>', <?= (float)$v['price_diff'] ?>)"
                  >
                    <span class="v-title"><?= htmlspecialchars($v['name']) ?></span>
                    <span class="v-price">
                      <?= $v['price_diff'] > 0 ? '+ ' . formatPrice($v['price_diff']) : 'Included (Base)' ?>
                    </span>
                    <span class="v-badge">SKU: <?= htmlspecialchars($v['sku'] ?? 'ZEN-STD') ?></span>
                  </button>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endif; ?>

          <!-- OPTION 3: CARE & PROTECTION ADD-ONS -->
          <?php if (!empty($addons)): ?>
            <div>
              <div class="opt-section-title">
                <span>Add Protection & Care (Optional)</span>
                <span class="opt-current" id="active-addons-count">0 Selected</span>
              </div>
              <div class="addon-list">
                <?php foreach ($addons as $addon): ?>
                  <label class="addon-card" id="addon-label-<?= htmlspecialchars($addon['id']) ?>">
                    <input 
                      type="checkbox" 
                      class="addon-checkbox" 
                      value="<?= htmlspecialchars($addon['name']) ?>" 
                      data-price="<?= (float)$addon['price'] ?>" 
                      onchange="toggleAddon(this, '<?= htmlspecialchars($addon['id']) ?>')"
                    >
                    <div class="addon-info">
                      <div class="addon-title-row">
                        <span>🛡️ <?= htmlspecialchars($addon['name']) ?></span>
                        <span class="addon-price">+ <?= formatPrice($addon['price']) ?></span>
                      </div>
                      <div class="addon-desc"><?= htmlspecialchars($addon['desc']) ?></div>
                    </div>
                  </label>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endif; ?>

          <!-- DYNAMIC REAL-TIME TOTAL SUMMARY -->
          <div class="dynamic-total-bar">
            <div>
              <div style="font-size: 11px; text-transform: uppercase; color: #64748B; font-weight: 800;">Calculated Subtotal</div>
              <div style="font-size: 12px; color: #334155;">Includes selected variant & care add-ons</div>
            </div>
            <div style="text-align: right;">
              <span id="calculated-total-display" style="font-size: 1.4rem; font-weight: 900; color: var(--fk-blue);"><?= formatPrice($product['price']) ?></span>
              <div id="calculated-inr-display" style="font-size: 11px; color: var(--fk-green); font-weight: 700;">✓ Free Delivery Eligible</div>
            </div>
          </div>

        </div>

        <!-- FREQUENTLY BOUGHT TOGETHER BUNDLE DEAL -->
        <?php if ($bundleProduct): 
          $bundleSaving = 15;
          $bundleTotal = round(($product['price'] + $bundleProduct['price']) * 0.85, 2);
        ?>
          <div class="frequently-bought-box">
            <div style="font-size: 13px; font-weight: 800; text-transform: uppercase; color: #1E293B; margin-bottom: 12px; display: flex; align-items: center; justify-content: space-between;">
              <span>🎁 Frequently Bought Together</span>
              <span style="background: #ECFDF5; color: #047857; font-size: 11px; padding: 2px 8px; border-radius: 2px;">Save 15% on Combo</span>
            </div>
            <div style="display: flex; align-items: center; gap: 14px; flex-wrap: wrap;">
              <div style="display: flex; align-items: center; gap: 10px;">
                <img src="<?= htmlspecialchars($product['image_url']) ?>" style="width: 50px; height: 50px; object-fit: contain; border: 1px solid #E2E8F0; border-radius: 4px; padding: 2px;">
                <span style="font-weight: 900; color: #64748B;">+</span>
                <img src="<?= htmlspecialchars($bundleProduct['image_url']) ?>" style="width: 50px; height: 50px; object-fit: contain; border: 1px solid #E2E8F0; border-radius: 4px; padding: 2px;">
              </div>
              <div style="flex: 1; min-width: 180px;">
                <div style="font-size: 13px; font-weight: 700; color: #1E293B;">
                  Add <?= htmlspecialchars($bundleProduct['name']) ?>
                </div>
                <div style="font-size: 12px; color: #64748B;">
                  Bundle Price: <strong style="color: var(--fk-green); font-size: 14px;"><?= formatPrice($bundleTotal) ?></strong> 
                  <span style="text-decoration: line-through; font-size: 11px;"><?= formatPrice($product['price'] + $bundleProduct['price']) ?></span>
                </div>
              </div>
              <button type="button" onclick="buyBundleCombo(<?= $product['id'] ?>, <?= $bundleProduct['id'] ?>)" class="btn btn-outline" style="font-size: 12px; font-weight: 700; color: var(--fk-blue); padding: 8px 14px;">
                + Add Both to Cart
              </button>
            </div>
          </div>
        <?php endif; ?>

        <!-- Available Offers & Bank Discounts -->
        <div style="margin-bottom: 24px;">
          <h3 style="font-size: 14px; font-weight: 700; color: #212121; margin-bottom: 10px;">Available Bank & Payment Offers</h3>
          <div style="display: flex; flex-direction: column; gap: 8px; font-size: 13px; color: #333333;">
            <div style="display: flex; align-items: flex-start; gap: 8px;">
              <span style="color: var(--fk-green); font-weight: 800;">🏷️</span>
              <span><strong>Bank Offer:</strong> 5% Unlimited Cashback on Flipkart Axis Bank Credit Card.</span>
            </div>
            <div style="display: flex; align-items: flex-start; gap: 8px;">
              <span style="color: var(--fk-green); font-weight: 800;">🏷️</span>
              <span><strong>Special Price:</strong> Get extra 20% off using coupon code <strong style="color: var(--fk-blue);">ZENKIT20</strong> at checkout.</span>
            </div>
            <div style="display: flex; align-items: flex-start; gap: 8px;">
              <span style="color: var(--fk-green); font-weight: 800;">🏷️</span>
              <span><strong>No Cost EMI:</strong> Available starting from <strong><?= formatPrice(round($product['price'] / 12, 2)) ?>/month</strong> on 12-month tenure.</span>
            </div>
          </div>
        </div>

        <!-- Delivery Pincode Checker -->
        <div style="margin-bottom: 24px; padding-bottom: 20px; border-bottom: 1px solid var(--border-subtle);">
          <h3 style="font-size: 13px; font-weight: 700; color: #878787; margin-bottom: 8px; text-transform: uppercase;">Delivery Availability & Dispatch Time</h3>
          <div style="display: flex; max-width: 360px; gap: 8px;">
            <input type="text" id="delivery-pincode" class="form-control" placeholder="Enter 6-digit Pincode (e.g. 560103)" maxlength="6">
            <button type="button" onclick="checkPincode()" class="btn btn-outline" style="font-size: 12px; padding: 8px 16px; font-weight: 700; color: var(--fk-blue);">Check</button>
          </div>
          <div id="pincode-result" style="font-size: 12px; margin-top: 6px; color: #64748B;">
            🚚 Standard delivery within 2-3 business days. Enter pincode for live tracking status.
          </div>
        </div>

        <!-- INTERACTIVE TABS COMPONENT -->
        <div class="product-tabs-header">
          <button type="button" class="product-tab-btn active" onclick="switchProductTab('tab-highlights', this)">
            ✨ Highlights & Features
          </button>
          <button type="button" class="product-tab-btn" onclick="switchProductTab('tab-specs', this)">
            ⚙️ Full Specifications
          </button>
          <button type="button" class="product-tab-btn" onclick="switchProductTab('tab-reviews', this)">
            ⭐ Ratings & Reviews (<?= count($reviews) ?>)
          </button>
          <button type="button" class="product-tab-btn" onclick="switchProductTab('tab-faqs', this)">
            ❓ Questions & Answers (<?= count($faqs) ?>)
          </button>
        </div>

        <!-- TAB 1: HIGHLIGHTS & FEATURES -->
        <div id="tab-highlights" class="product-tab-pane active">
          <div style="margin-bottom: 20px;">
            <h3 style="font-size: 1.1rem; font-weight: 800; color: #1E293B; margin-bottom: 8px;">Product Overview</h3>
            <p style="font-size: 14px; line-height: 1.7; color: #475569;">
              <?= nl2br(htmlspecialchars($product['description'])) ?>
            </p>
          </div>

          <h4 style="font-size: 14px; font-weight: 800; color: #1E293B; margin-bottom: 12px; text-transform: uppercase;">Key Engineered Highlights</h4>
          <div class="highlights-grid">
            <?php foreach ($highlights as $hl): ?>
              <div class="highlight-item">
                <span class="icon">✓</span>
                <span><?= htmlspecialchars($hl) ?></span>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- TAB 2: TECHNICAL SPECIFICATIONS -->
        <div id="tab-specs" class="product-tab-pane">
          <?php if (!empty($specs)): ?>
            <div style="margin-bottom: 20px;">
              <table class="fk-spec-table">
                <tbody>
                  <?php foreach ($specs as $key => $val): ?>
                    <tr>
                      <td><?= htmlspecialchars($key) ?></td>
                      <td><?= htmlspecialchars($val) ?></td>
                    </tr>
                  <?php endforeach; ?>
                  <tr>
                    <td>Warranty Summary</td>
                    <td>1 Year Limited On-site & Carry-in Manufacturer Hardware Warranty</td>
                  </tr>
                  <tr>
                    <td>In The Box</td>
                    <td>1x Main Unit, Rapid Charge Cable/Adapter, User Manual, Certified QC Seal</td>
                  </tr>
                </tbody>
              </table>
            </div>
          <?php else: ?>
            <p style="color: #64748B; font-size: 13px;">Full specifications sheet is packaged with the device documentation.</p>
          <?php endif; ?>
        </div>

        <!-- TAB 3: RATINGS & REVIEWS -->
        <div id="tab-reviews" class="product-tab-pane">
          <div style="background: #F8FAFC; border: 1px solid var(--border-light); border-radius: 4px; padding: 20px; margin-bottom: 24px; display: grid; grid-template-columns: 200px 1fr; gap: 24px; align-items: center;">
            <div style="text-align: center; border-right: 1px solid var(--border-light); padding-right: 20px;">
              <div style="font-size: 2.8rem; font-weight: 900; color: #1E293B; line-height: 1;">
                <?= number_format($product['rating'], 1) ?>
              </div>
              <div style="color: #F59E0B; font-size: 18px; margin: 4px 0;">★★★★★</div>
              <div style="font-size: 12px; color: #878787; font-weight: 600;">
                <?= number_format($product['reviews_count']) ?> Ratings & <?= count($reviews) ?> Reviews
              </div>
            </div>

            <!-- Rating distribution bars -->
            <div style="display: flex; flex-direction: column; gap: 6px;">
              <?php for ($star = 5; $star >= 1; $star--): 
                $count = $ratingCounts[$star] ?? 0;
                $pct = round(($count / $totalReviews) * 100);
              ?>
                <div style="display: flex; align-items: center; gap: 10px; font-size: 12px;">
                  <span style="width: 32px; font-weight: 700;"><?= $star ?> ★</span>
                  <div style="flex: 1; height: 8px; background: #E2E8F0; border-radius: 9999px; overflow: hidden;">
                    <div style="width: <?= $pct ?>%; height: 100%; background: <?= $star >= 4 ? 'var(--fk-green)' : ($star == 3 ? '#F59E0B' : '#EF4444') ?>;"></div>
                  </div>
                  <span style="width: 40px; color: #64748B; text-align: right;"><?= $count ?></span>
                </div>
              <?php endfor; ?>
            </div>
          </div>

          <!-- Reviews List -->
          <div style="display: flex; flex-direction: column; gap: 14px; margin-bottom: 30px;">
            <?php foreach ($reviews as $rev): ?>
              <div style="background: #FFFFFF; border: 1px solid var(--border-light); border-radius: 4px; padding: 16px;">
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                  <span class="fk-rating-pill" style="font-size: 11px;">
                    <?= $rev['rating'] ?> ★
                  </span>
                  <strong style="font-size: 13px; color: #1E293B;"><?= htmlspecialchars($rev['user_name']) ?></strong>
                  <?php if ($rev['is_verified']): ?>
                    <span style="font-size: 11px; color: var(--fk-green); font-weight: 700;">✓ Certified Buyer</span>
                  <?php endif; ?>
                </div>
                <p style="font-size: 13px; color: #475569; line-height: 1.5;">
                  <?= htmlspecialchars($rev['comment']) ?>
                </p>
                <div style="font-size: 11px; color: #94A3B8; margin-top: 6px;">
                  <?= date('M d, Y', strtotime($rev['created_at'])) ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>

          <!-- Add Review Form -->
          <div style="background: #F8FAFC; border: 1px solid var(--border-light); border-radius: 4px; padding: 20px;">
            <h4 style="font-size: 1rem; font-weight: 800; margin-bottom: 14px; color: #1E293B;">Write a Verified Review</h4>
            <form action="product.php?id=<?= $productId ?>" method="POST">
              <input type="hidden" name="submit_review" value="1">
              
              <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                <div>
                  <label class="form-label">Your Name</label>
                  <input type="text" name="user_name" class="form-control" placeholder="Enter your name" required value="<?= htmlspecialchars($_SESSION['user_name'] ?? 'Amit Patel') ?>">
                </div>
                <div>
                  <label class="form-label">Rating</label>
                  <select name="rating" class="form-control">
                    <option value="5">★★★★★ (5 Stars - Excellent)</option>
                    <option value="4">★★★★☆ (4 Stars - Very Good)</option>
                    <option value="3">★★★☆☆ (3 Stars - Average)</option>
                    <option value="2">★★☆☆☆ (2 Stars - Below Average)</option>
                    <option value="1">★☆☆☆☆ (1 Star - Poor)</option>
                  </select>
                </div>
              </div>

              <div class="form-group">
                <label class="form-label">Detailed Experience / Feedback</label>
                <textarea name="comment" class="form-control" rows="3" placeholder="Share your experience with build quality, battery, performance..." required></textarea>
              </div>

              <button type="submit" class="btn btn-fk-primary" style="padding: 10px 24px; font-size: 13px;">
                Submit Verified Review
              </button>
            </form>
          </div>
        </div>

        <!-- TAB 4: FREQUENTLY ASKED QUESTIONS (FAQ / Q&A) -->
        <div id="tab-faqs" class="product-tab-pane">
          <h3 style="font-size: 1.1rem; font-weight: 800; color: #1E293B; margin-bottom: 14px;">Frequently Asked Questions</h3>
          <div class="faq-accordion">
            <?php foreach ($faqs as $fIdx => $faq): ?>
              <div class="faq-card <?= $fIdx === 0 ? 'open' : '' ?>" id="faq-card-<?= $fIdx ?>">
                <div class="faq-header" onclick="toggleFaq(<?= $fIdx ?>)">
                  <span><?= htmlspecialchars($faq['q']) ?></span>
                  <span class="faq-toggle-icon">▼</span>
                </div>
                <div class="faq-body">
                  <?= htmlspecialchars($faq['a']) ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

      </div>

    </div>

    <!-- Related Products -->
    <?php if (!empty($related)): ?>
      <section class="fk-section" style="margin-top: 40px;">
        <div class="fk-section-header">
          <h2 class="fk-section-title">Similar Tech Products You Might Like</h2>
        </div>
        <div class="product-grid">
          <?php foreach ($related as $rel): 
            $relDisc = round((($rel['original_price'] - $rel['price']) / $rel['original_price']) * 100);
          ?>
            <div class="fk-card">
              <a href="product.php?id=<?= $rel['id'] ?>" class="fk-card-img-wrap">
                <img src="<?= htmlspecialchars($rel['image_url']) ?>" alt="<?= htmlspecialchars($rel['name']) ?>" loading="lazy">
              </a>
              <a href="product.php?id=<?= $rel['id'] ?>" class="fk-card-title">
                <?= htmlspecialchars($rel['name']) ?>
              </a>
              <div class="fk-price-row">
                <span class="fk-curr-price"><?= formatPrice($rel['price']) ?></span>
                <span class="fk-disc-percent"><?= $relDisc ?>% off</span>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endif; ?>

  </div>
</main>

<script>
// State for current product options
const productState = {
  productId: <?= (int)$product['id'] ?>,
  basePrice: <?= (float)$product['price'] ?>,
  color: <?= !empty($colors) ? json_encode($colors[0]['name']) : 'null' ?>,
  variant: <?= !empty($variants) ? json_encode($variants[0]['name']) : 'null' ?>,
  variantPriceDiff: 0,
  addons: [],
  addonsTotal: 0,
  quantity: 1,
  maxStock: <?= (int)$product['stock'] ?>,
  inrRate: 83.5
};

// Update Color Selection
function selectProductColor(btn, colorName) {
  document.querySelectorAll('.color-swatch-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  productState.color = colorName;
  const lbl = document.getElementById('active-color-name');
  if (lbl) lbl.innerText = colorName;
  playAudioHaptic('click');
}

// Update Variant Selection
function selectProductVariant(btn, variantName, priceDiff) {
  document.querySelectorAll('.variant-pill-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  productState.variant = variantName;
  productState.variantPriceDiff = Number(priceDiff) || 0;
  const lbl = document.getElementById('active-variant-name');
  if (lbl) lbl.innerText = variantName;
  recalculateProductPrices();
  playAudioHaptic('click');
}

// Toggle Add-on Option
function toggleAddon(checkbox, addonId) {
  const card = document.getElementById('addon-label-' + addonId);
  const addonName = checkbox.value;
  const price = Number(checkbox.dataset.price) || 0;

  if (checkbox.checked) {
    card.classList.add('checked');
    if (!productState.addons.includes(addonName)) {
      productState.addons.push(addonName);
      productState.addonsTotal += price;
    }
  } else {
    card.classList.remove('checked');
    const idx = productState.addons.indexOf(addonName);
    if (idx > -1) {
      productState.addons.splice(idx, 1);
      productState.addonsTotal -= price;
    }
  }

  const countBadge = document.getElementById('active-addons-count');
  if (countBadge) {
    countBadge.innerText = `${productState.addons.length} Selected`;
  }
  recalculateProductPrices();
  playAudioHaptic('click');
}

// Quantity Stepper
function changeQty(delta) {
  let newQty = productState.quantity + delta;
  if (newQty < 1) newQty = 1;
  if (newQty > productState.maxStock) newQty = productState.maxStock;
  productState.quantity = newQty;
  document.getElementById('product-qty-input').value = newQty;
  recalculateProductPrices();
  playAudioHaptic('click');
}

// Real-Time Price Recalculation
function recalculateProductPrices() {
  const unitPrice = productState.basePrice + productState.variantPriceDiff + productState.addonsTotal;
  const totalPrice = unitPrice * productState.quantity;

  const unitDisp = document.getElementById('display-product-price');
  const totalDisp = document.getElementById('calculated-total-display');

  if (unitDisp) unitDisp.innerText = '₹' + Math.round(unitPrice).toLocaleString('en-IN');
  if (totalDisp) totalDisp.innerText = '₹' + Math.round(totalPrice).toLocaleString('en-IN');
}

// Add to Cart with Options
function addProductToCartWithOptions(isBuyNow = false) {
  const optionsPayload = {
    color: productState.color,
    variant: productState.variant,
    price_diff: productState.variantPriceDiff,
    addons: productState.addons,
    addons_total: productState.addonsTotal
  };

  playAudioHaptic('click');
  const formData = new FormData();
  formData.append('product_id', productState.productId);
  formData.append('quantity', productState.quantity);
  formData.append('options', JSON.stringify(optionsPayload));

  fetch('api/cart.php?action=add', {
    method: 'POST',
    body: formData
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      updateCartBadge(data.cart_count);
      if (isBuyNow) {
        window.location.href = 'checkout.php';
      } else {
        showToast(data.message, 'success');
        setTimeout(openCartDrawer, 300);
      }
    } else {
      showToast(data.message, 'error');
    }
  })
  .catch(err => {
    showToast('Failed to add to cart.', 'error');
  });
}

// Frequently Bought Together Bundle Add
function buyBundleCombo(prod1, prod2) {
  playAudioHaptic('click');
  handleAddToCart(prod1, 1, false);
  setTimeout(() => {
    handleAddToCart(prod2, 1, true);
    showToast('Combo Bundle added to cart with 15% discount!', 'success');
  }, 350);
}

// Tab Switching
function switchProductTab(tabId, btn) {
  document.querySelectorAll('.product-tab-btn').forEach(b => b.classList.remove('active'));
  document.querySelectorAll('.product-tab-pane').forEach(p => p.classList.remove('active'));
  btn.classList.add('active');
  const target = document.getElementById(tabId);
  if (target) target.classList.add('active');
  playAudioHaptic('click');
}

// FAQ Accordion Toggle
function toggleFaq(idx) {
  const card = document.getElementById('faq-card-' + idx);
  if (card) {
    card.classList.toggle('open');
  }
}

// Copy Link Tool
function copyProductLink() {
  navigator.clipboard.writeText(window.location.href).then(() => {
    showToast('Product link copied to clipboard!', 'success');
  });
}

// Real-Time Pincode Checker
function checkPincode() {
  const pinInput = document.getElementById('delivery-pincode');
  const resEl = document.getElementById('pincode-result');
  const pin = (pinInput.value || '').trim();

  if (/^\d{6}$/.test(pin)) {
    const today = new Date();
    const tomorrow = new Date(today);
    tomorrow.setDate(today.getDate() + 1);
    const dateStr = tomorrow.toLocaleDateString('en-IN', { weekday: 'short', month: 'short', day: 'numeric' });

    resEl.innerHTML = `
      <div style="color: var(--fk-green); font-weight: 700; margin-top: 4px;">
        ✓ Delivery available to <strong>${pin}</strong> by <strong>Tomorrow, 11:00 AM</strong>.
      </div>
      <div style="color: #64748B; font-size: 11px;">
        ⚡ Express Air Courier Dispatch • Cash on Delivery & UPI available
      </div>
    `;
    showToast(`Delivery confirmed to PIN ${pin}!`, 'success');
  } else {
    resEl.innerHTML = `<span style="color: #DC2626; font-weight: 700;">Please enter a valid 6-digit Indian PIN code.</span>`;
  }
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
