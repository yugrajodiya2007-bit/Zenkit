<?php
$user = currentUser();
$db = getDB();
$categories = $db->query("SELECT * FROM categories ORDER BY id ASC")->fetchAll();
$wishlistCount = getWishlistCount();
?>

<!-- Corporate Business Utility Announcement Bar -->
<div style="background: #172337; color: #94A3B8; font-size: 11px; padding: 6px 0; border-bottom: 1px solid rgba(255,255,255,0.08);">
  <div class="container" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px;">
    <div style="display: flex; align-items: center; gap: 14px;">
      <span style="color: #FFFFFF; font-weight: 700;">⚡ 24-Hour Express Dispatch</span>
      <span>•</span>
      <span>100% Genuine Certified Gear</span>
      <span>•</span>
      <span style="color: #38BDF8;">GST Invoicing Available</span>
    </div>
    <div style="display: flex; align-items: center; gap: 16px;">
      <a href="track-order.php" style="color: #E2E8F0; text-decoration: none; font-weight: 600;">📍 Track Shipment</a>
      <a href="faq.php" style="color: #CBD5E1; text-decoration: none;">Help & FAQ</a>
      <a href="contact.php" style="color: #CBD5E1; text-decoration: none;">📞 Support: <?= STORE_PHONE ?></a>
      <a href="contact.php" style="color: var(--fk-yellow); font-weight: 700; text-decoration: none;">💼 B2B / Bulk Desk</a>
    </div>
  </div>
</div>

<!-- Flipkart Classic Header -->
<header class="fk-header">
  <div class="container">
    
    <!-- Brand Logo -->
    <a href="index.php" class="fk-brand-col">
      <div class="fk-brand-title">
        Zenkit<span>✦</span>
      </div>
      <div class="fk-brand-sub">
        Explore <span>Plus ✦</span>
      </div>
    </a>

    <!-- Global Search with Live Autocomplete -->
    <div style="flex: 1; max-width: 600px; position: relative;">
      <form action="shop.php" method="GET" class="fk-search-box" autocomplete="off">
        <input 
          type="text" 
          id="global-search-input"
          name="q" 
          class="fk-search-input" 
          placeholder="Search for laptops, ANC earbuds, AMOLED smartwatches, mechanical keyboards..."
          value="<?= htmlspecialchars($_GET['q'] ?? '') ?>"
          oninput="handleLiveSearch(this.value)"
        >
        <button type="submit" class="fk-search-btn" title="Search">
          🔍
        </button>
      </form>

      <!-- Live Search Autocomplete Popup -->
      <div id="search-autocomplete-dropdown" style="display: none; position: absolute; top: 100%; left: 0; right: 0; background: #FFFFFF; border: 1px solid var(--border-light); border-radius: 0 0 4px 4px; box-shadow: var(--shadow-lg); z-index: 2000; max-height: 380px; overflow-y: auto;"></div>
    </div>

    <!-- Nav Actions -->
    <div class="fk-nav-actions">
      
      <?php if ($user): ?>
        <div class="fk-user-menu">
          <div class="fk-nav-link" style="cursor: pointer;">
            👤 <?= htmlspecialchars(explode(' ', $user['name'])[0]) ?> ▾
          </div>
          <div class="fk-user-menu-content">
            <a href="account.php">👤 My Profile & Orders</a>
            <a href="wishlist.php">💙 My Wishlist (<?= $wishlistCount ?>)</a>
            <a href="track-order.php">🚚 Track Shipment</a>
            <?php if (isAdmin()): ?>
              <a href="admin/index.php" style="color: #D97706; font-weight: 700;">⚙️ Admin Dashboard</a>
            <?php endif; ?>
            <a href="logout.php" style="color: #DC2626;">🚪 Logout</a>
          </div>
        </div>
      <?php else: ?>
        <button class="fk-login-btn" onclick="openAuthModal('login')">
          Login
        </button>
      <?php endif; ?>

      <!-- Wishlist Link -->
      <a href="wishlist.php" class="fk-nav-link" title="My Wishlist">
        💙 Wishlist
        <span class="fk-wishlist-badge" id="header-wishlist-badge" style="background: rgba(255,255,255,0.25); color: #FFFFFF; font-size: 11px; font-weight: 800; padding: 2px 6px; border-radius: 999px; margin-left: 2px; display: <?= $wishlistCount > 0 ? 'inline-block' : 'none' ?>;">
          <?= $wishlistCount ?>
        </span>
      </a>

      <a href="shop.php" class="fk-nav-link">
        All Deals
      </a>

      <?php if (isAdmin()): ?>
        <a href="admin/index.php" class="fk-nav-link" style="color: var(--fk-yellow);">
          Admin Panel
        </a>
      <?php else: ?>
        <a href="admin/login.php" class="fk-nav-link" title="Vendor / Store Admin">
          Seller Hub
        </a>
      <?php endif; ?>

      <!-- Cart Button with Counter -->
      <a href="javascript:void(0)" class="fk-cart-link" onclick="openCartDrawer()">
        🛒 Cart
        <span class="fk-cart-badge" style="display: <?= $cartCount > 0 ? 'inline-block' : 'none' ?>;">
          <?= $cartCount ?>
        </span>
      </a>

    </div>

  </div>
</header>

<!-- Circular Category Navigation Strip (Flipkart Classic Bar) -->
<nav class="fk-categories-bar">
  <div class="container">
    <div class="fk-cat-list">
      
      <a href="shop.php" class="fk-cat-item">
        <div class="fk-cat-icon-wrap" style="background: #EFF6FF;">
          <span style="font-size: 26px;">🔥</span>
        </div>
        <span class="fk-cat-name">Top Offers</span>
      </a>

      <a href="shop.php?category=laptops-pcs" class="fk-cat-item">
        <div class="fk-cat-icon-wrap">
          <img src="assets/images/products/zen_laptop.jpg" alt="Laptops">
        </div>
        <span class="fk-cat-name">Laptops & PCs</span>
      </a>

      <a href="shop.php?category=audio-earbuds" class="fk-cat-item">
        <div class="fk-cat-icon-wrap">
          <img src="assets/images/products/zen_buds.jpg" alt="Audio">
        </div>
        <span class="fk-cat-name">Audio & ANC</span>
      </a>

      <a href="shop.php?category=smartwatches" class="fk-cat-item">
        <div class="fk-cat-icon-wrap">
          <img src="assets/images/products/zen_watch.jpg" alt="Watches">
        </div>
        <span class="fk-cat-name">Smartwatches</span>
      </a>

      <a href="shop.php?category=keyboards-gaming" class="fk-cat-item">
        <div class="fk-cat-icon-wrap">
          <img src="assets/images/products/zen_key.jpg" alt="Keyboards">
        </div>
        <span class="fk-cat-name">Keyboards & Gear</span>
      </a>

      <a href="shop.php?category=smart-lamps" class="fk-cat-item">
        <div class="fk-cat-icon-wrap">
          <img src="assets/images/products/zen_lamp.jpg" alt="Smart Lamps">
        </div>
        <span class="fk-cat-name">Smart Ambient Lamps</span>
      </a>

    </div>
  </div>
</nav>

<script>
let searchDebounceTimer = null;
function handleLiveSearch(val) {
  clearTimeout(searchDebounceTimer);
  const dropdown = document.getElementById('search-autocomplete-dropdown');
  if (!dropdown) return;

  if (val.trim().length < 2) {
    dropdown.style.display = 'none';
    dropdown.innerHTML = '';
    return;
  }

  searchDebounceTimer = setTimeout(() => {
    fetch('api/search.php?q=' + encodeURIComponent(val))
      .then(r => r.json())
      .then(data => {
        if (!data.results || data.results.length === 0) {
          dropdown.innerHTML = '<div style="padding: 12px 16px; color: #878787; font-size: 13px;">No direct matches found. Press Enter to search catalog.</div>';
          dropdown.style.display = 'block';
          return;
        }

        let html = '<div style="display: flex; flex-direction: column;">';
        data.results.forEach(item => {
          html += `
            <a href="${item.url}" style="display: flex; align-items: center; gap: 12px; padding: 10px 14px; text-decoration: none; border-bottom: 1px solid #F1F5F9; transition: background 0.15s;" onmouseover="this.style.background='#F8FAFC'" onmouseout="this.style.background='#FFFFFF'">
              <img src="${item.image_url}" alt="${item.name}" style="width: 40px; height: 40px; object-fit: contain; border: 1px solid #E2E8F0; border-radius: 4px; padding: 2px;">
              <div style="flex: 1;">
                <div style="font-size: 13px; font-weight: 600; color: #1E293B; line-height: 1.3;">${item.name}</div>
                <div style="font-size: 11px; color: #64748B;">in <span style="color: var(--fk-blue); font-weight: 600;">${item.category}</span></div>
              </div>
              <div style="font-weight: 800; color: #1E293B; font-size: 13px;">${item.price_formatted}</div>
            </a>
          `;
        });
        html += `
          <a href="shop.php?q=${encodeURIComponent(val)}" style="display: block; text-align: center; padding: 10px; background: #F8FAFC; color: var(--fk-blue); font-weight: 700; font-size: 12px; text-decoration: none;">
            View all results for "${val}" &rarr;
          </a>
        </div>`;
        dropdown.innerHTML = html;
        dropdown.style.display = 'block';
      })
      .catch(() => {
        dropdown.style.display = 'none';
      });
  }, 250);
}

document.addEventListener('click', (e) => {
  const dropdown = document.getElementById('search-autocomplete-dropdown');
  const searchInput = document.getElementById('global-search-input');
  if (dropdown && !dropdown.contains(e.target) && e.target !== searchInput) {
    dropdown.style.display = 'none';
  }
});
</script>
