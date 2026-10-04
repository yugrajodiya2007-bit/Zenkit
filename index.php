<?php
$pageTitle = "Zenkit | India's Biggest Online Tech & Gadget Marketplace";
require_once __DIR__ . '/includes/header.php';

$db = getDB();
$user = currentUser();

// Strict Deduplication Engine: Track every product ID shown to guarantee zero duplicate cards
$displayedIds = [1]; // Product 1 (Zenkit ProBook) is showcased in the Hero Banner

// 1. Deals of the Day: Select top 4 highest discount deals excluding hero
$dealsStmt = $db->prepare("SELECT * FROM products WHERE id NOT IN (1) ORDER BY ((original_price - price) / original_price) DESC LIMIT 4");
$dealsStmt->execute();
$deals = $dealsStmt->fetchAll();

foreach ($deals as $d) {
    $displayedIds[] = (int)$d['id'];
}

// 2. Flagship Tech & New Arrivals: Select remaining distinct products (NO duplicates)
$excludePlaceholders = implode(',', array_fill(0, count($displayedIds), '?'));
$flagshipStmt = $db->prepare("SELECT * FROM products WHERE id NOT IN ($excludePlaceholders) ORDER BY is_featured DESC, id ASC");
$flagshipStmt->execute($displayedIds);
$featuredProducts = $flagshipStmt->fetchAll();
?>

<!-- Set authenticated status on body for first-visit modal logic -->
<script>
  document.body.dataset.authenticated = "<?= $user ? 'true' : 'false' ?>";
</script>

<main class="main-content">
  <div class="container">

    <!-- 1. Hero 3D Interactive Showcase Banner -->
    <div class="fk-hero-banner">
      <div class="fk-hero-content">
        <div class="fk-hero-tag">
          ✦ Zenkit Mega Electronics Festival
        </div>
        <h1 class="fk-hero-title">
          Next-Gen Tech & Premium Gadgets
        </h1>
        <p class="fk-hero-desc">
          Up to <strong>45% Off</strong> on Intel Core i7 Ultrabooks, Hybrid ANC Earbuds, AMOLED Smartwatches & Ergonomic Mechanical Keyboards.
        </p>
        <div class="fk-hero-btns">
          <a href="shop.php" class="btn btn-fk-buy" style="padding: 14px 28px; font-size: 15px;">
            Shop Big Deals &rarr;
          </a>
          <button onclick="openProduct3D('laptop', 'Zenkit ProBook Ultrathin 15 (16GB RAM / 512GB SSD)')" class="btn btn-outline" style="color: #FFFFFF; border-color: rgba(255,255,255,0.4); padding: 14px 24px;">
            ⬡ Inspect in 3D
          </button>
        </div>
      </div>

      <!-- 3D WebGL Canvas Interactive Container -->
      <div class="fk-hero-canvas-wrap">
        <div id="hero-3d-canvas"></div>
        <div class="fk-hero-3d-badge">
          <span>● 3D WebGL Live Render</span>
        </div>
      </div>
    </div>

    <!-- 2. Deals of the Day (Flipkart Deal Rail with Timer - Distinct Products) -->
    <section class="fk-section">
      <div class="fk-section-header">
        <div class="fk-section-title-wrap">
          <h2 class="fk-section-title">Deals of the Day</h2>
          <div class="fk-countdown-timer">
            <span>⏱️</span>
            <span class="fk-timer-pill" id="deal-timer">14 : 42 : 19 Left</span>
          </div>
        </div>
        <a href="shop.php" class="btn-fk-view-all">VIEW ALL</a>
      </div>

      <div class="product-grid">
        <?php foreach ($deals as $prod): 
          $discPercent = round((($prod['original_price'] - $prod['price']) / $prod['original_price']) * 100);
        ?>
          <div class="fk-card">
            <span class="fk-card-badge"><?= $discPercent ?>% OFF</span>
            <button 
              type="button" 
              class="fk-card-wish-btn" 
              onclick="handleToggleWishlist(<?= $prod['id'] ?>, this)" 
              title="Add to Wishlist" 
              style="position: absolute; top: 12px; right: 54px; background: #FFFFFF; border: 1px solid var(--border-light); width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 13px; cursor: pointer; z-index: 2;"
            >
              <?= isProductInWishlist($prod['id']) ? '❤️' : '🤍' ?>
            </button>
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
              <?= htmlspecialchars($prod['subtitle'] ?? 'Zenkit Certified Genuine') ?>
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
    </section>

    <!-- 3. Promotional Category Focus Banners (No Product Clashing) -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 16px; margin-bottom: 16px;">
      
      <div style="background: linear-gradient(135deg, #EFF6FF, #DBEAFE); border: 1px solid #BFDBFE; border-radius: var(--radius-xs); padding: 24px; display: flex; align-items: center; justify-content: space-between;">
        <div>
          <div style="font-size: 11px; font-weight: 800; color: var(--fk-blue); text-transform: uppercase;">Professional Acoustics</div>
          <h3 style="font-size: 1.25rem; font-weight: 800; color: #1E3A8A; margin: 4px 0 8px 0;">Studio & Spatial Audio</h3>
          <p style="font-size: 13px; color: #475569; margin-bottom: 14px;">Lossless Over-Ear ANC & Hybrid True Wireless Gear.</p>
          <a href="shop.php?category=audio-earbuds" class="btn btn-fk-primary" style="font-size: 12px; padding: 8px 18px;">Explore Audio Hub &rarr;</a>
        </div>
        <img src="assets/images/products/zen_headset.jpg" alt="Audio Hub" style="width: 110px; height: 110px; object-fit: contain;">
      </div>

      <div style="background: linear-gradient(135deg, #FEF3C7, #FDE68A); border: 1px solid #FCD34D; border-radius: var(--radius-xs); padding: 24px; display: flex; align-items: center; justify-content: space-between;">
        <div>
          <div style="font-size: 11px; font-weight: 800; color: #B45309; text-transform: uppercase;">Desk & Ambient Atmosphere</div>
          <h3 style="font-size: 1.25rem; font-weight: 800; color: #78350F; margin: 4px 0 8px 0;">Smart Ambient Illumination</h3>
          <p style="font-size: 13px; color: #78350F; margin-bottom: 14px;">Touch Stepless Dimmable Lamps & 16M Color Dynamic Orbs.</p>
          <a href="shop.php?category=smart-lamps" class="btn btn-fk-buy" style="font-size: 12px; padding: 8px 18px;">Explore Ambient Hub &rarr;</a>
        </div>
        <img src="assets/images/products/zen_orb.jpg" alt="Ambient Hub" style="width: 110px; height: 110px; object-fit: contain;">
      </div>

    </div>

    <!-- 4. Flagship Tech & New Arrivals (Strictly Non-Duplicate Products) -->
    <section class="fk-section">
      <div class="fk-section-header">
        <div>
          <h2 class="fk-section-title">Flagship Tech & New Arrivals</h2>
          <p style="font-size: 12px; color: #878787; margin-top: 2px;">Handpicked performance computing, tactile mechanical gear, and accessories</p>
        </div>
        <a href="shop.php" class="btn-fk-view-all">EXPLORE ALL</a>
      </div>

      <div class="product-grid">
        <?php foreach ($featuredProducts as $prod): 
          $discPercent = round((($prod['original_price'] - $prod['price']) / $prod['original_price']) * 100);
        ?>
          <div class="fk-card">
            <?php if ($prod['is_bestseller']): ?>
              <span class="fk-card-badge" style="background: #D97706;">BESTSELLER</span>
            <?php else: ?>
              <span class="fk-card-badge"><?= $discPercent ?>% OFF</span>
            <?php endif; ?>

            <button 
              type="button" 
              class="fk-card-wish-btn" 
              onclick="handleToggleWishlist(<?= $prod['id'] ?>, this)" 
              title="Add to Wishlist" 
              style="position: absolute; top: 12px; right: 54px; background: #FFFFFF; border: 1px solid var(--border-light); width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 13px; cursor: pointer; z-index: 2;"
            >
              <?= isProductInWishlist($prod['id']) ? '❤️' : '🤍' ?>
            </button>
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
              <?= htmlspecialchars($prod['subtitle'] ?? '') ?>
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
    </section>

  </div>
</main>

<!-- ==========================================================================
     Flipkart-Style Split Auth Modal (Registration & Login on First Open)
     Shows on First Visit or when clicking Login/Register
     ========================================================================== -->
<div class="fk-modal-overlay" id="fk-auth-modal">
  <div class="fk-auth-card">
    
    <button class="fk-modal-close" onclick="closeAuthModal()" title="Skip / Close">✕</button>

    <!-- Left Blue Flipkart Brand Banner -->
    <div class="fk-auth-left">
      <div>
        <h2 class="fk-auth-left-title" id="modal-auth-title">
          Looks like you're new here!
        </h2>
        <p class="fk-auth-left-desc" id="modal-auth-desc">
          Sign up with your details to unlock exclusive discounts, 3D interactive previews and fast delivery.
        </p>
      </div>

      <div class="fk-auth-left-art">
        <div style="font-size: 54px; margin-bottom: 8px;">🛍️</div>
        <div style="font-size: 11px; opacity: 0.85; font-weight: 600;">Zenkit Verified Marketplace</div>
      </div>
    </div>

    <!-- Right Form Section -->
    <div class="fk-auth-right">
      <form id="modal-auth-form" action="register.php" method="POST">
        
        <div class="form-group" id="modal-name-group">
          <label class="form-label">Full Name</label>
          <input type="text" name="name" class="form-control" placeholder="Enter your full name" value="Rahul Sharma">
        </div>

        <div class="form-group">
          <label class="form-label">Email or Mobile Number</label>
          <input type="text" name="email" class="form-control" placeholder="Enter email address" required value="demo@zenkit.io">
        </div>

        <div class="form-group">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control" placeholder="Enter password (e.g. demo123)" required value="demo123">
        </div>

        <p class="fk-auth-terms">
          By continuing, you agree to Zenkit's <a href="shop.php">Terms of Use</a> and <a href="shop.php">Privacy Policy</a>.
        </p>

        <button type="submit" id="modal-submit-btn" class="btn btn-fk-buy" style="width: 100%; padding: 12px; font-size: 14px;">
          Continue & Register
        </button>

        <!-- 1-Click Demo Logins & Skip -->
        <div style="display: flex; gap: 8px; margin-top: 10px;">
          <button 
            type="button" 
            onclick="fillDemoUser('demo@zenkit.io', 'demo123', 'Rahul Sharma')" 
            class="btn btn-outline" 
            style="flex: 1; font-size: 11px; padding: 6px;"
          >
            ⚡ Customer Demo
          </button>
          <button 
            type="button" 
            onclick="fillDemoUser('admin@zenkit.io', 'admin123', 'Zenkit Admin')" 
            class="btn btn-outline" 
            style="flex: 1; font-size: 11px; padding: 6px;"
          >
            ⚡ Admin Demo
          </button>
        </div>

      </form>

      <!-- Bottom Toggle & Guest Skip -->
      <div class="fk-auth-footer-toggle">
        <span id="modal-toggle-text" style="color: #878787; font-size: 13px;">Existing User? </span>
        <a href="javascript:void(0)" id="modal-toggle-btn" onclick="openAuthModal('login')">Log in</a>
        <div style="margin-top: 10px;">
          <a href="javascript:void(0)" onclick="closeAuthModal()" style="color: #64748B; font-size: 12px; text-decoration: underline;">
            Skip for now & explore as Guest &rarr;
          </a>
        </div>
      </div>

    </div>

  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
