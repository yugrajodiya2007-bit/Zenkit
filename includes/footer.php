<!-- Flipkart Multi-Column Footer & Value Badges -->
<footer class="fk-footer">
  <div class="container">
    
    <!-- Trust Badges Strip -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; padding-bottom: 24px; margin-bottom: 24px; border-bottom: 1px solid #28354A;">
      <div style="display: flex; align-items: center; gap: 12px;">
        <span style="font-size: 28px;">🛡️</span>
        <div>
          <div style="font-weight: 700; font-size: 13px;">100% Genuine Products</div>
          <div style="font-size: 11px; color: #878787;">Sourced directly from verified brands</div>
        </div>
      </div>
      <div style="display: flex; align-items: center; gap: 12px;">
        <span style="font-size: 28px;">🔄</span>
        <div>
          <div style="font-weight: 700; font-size: 13px;">7-Day Easy Replacement</div>
          <div style="font-size: 11px; color: #878787;">Doorstep reverse pickup guarantee</div>
        </div>
      </div>
      <div style="display: flex; align-items: center; gap: 12px;">
        <span style="font-size: 28px;">⚡</span>
        <div>
          <div style="font-weight: 700; font-size: 13px;">Express Air Delivery</div>
          <div style="font-size: 11px; color: #878787;">Real-time shipment milestones tracking</div>
        </div>
      </div>
      <div style="display: flex; align-items: center; gap: 12px;">
        <span style="font-size: 28px;">🎧</span>
        <div>
          <div style="font-weight: 700; font-size: 13px;">24x7 Support Helpline</div>
          <div style="font-size: 11px; color: #878787;">Toll-free: <?= STORE_PHONE ?></div>
        </div>
      </div>
    </div>

    <!-- Newsletter Subscription Bar -->
    <div style="background: #1E293B; border: 1px solid #334155; border-radius: var(--radius-xs); padding: 20px 24px; margin-bottom: 30px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
      <div>
        <h4 style="font-size: 1rem; font-weight: 800; color: #FFFFFF; margin-bottom: 2px;">
          Subscribe to Zenkit VIP Tech Drops
        </h4>
        <p style="font-size: 12px; color: #94A3B8;">Get secret promo codes, device release announcements, and exclusive 15% discount vouchers.</p>
      </div>

      <form id="newsletter-form" onsubmit="handleNewsletter(event)" style="display: flex; gap: 8px; flex: 1; max-width: 440px;">
        <input 
          type="email" 
          id="newsletter-email" 
          placeholder="Enter corporate or personal email..." 
          required 
          class="form-control" 
          style="background: #0F172A; border-color: #334155; color: #FFFFFF; font-size: 13px;"
        >
        <button type="submit" class="btn btn-fk-buy" style="font-size: 12px; font-weight: 800; padding: 8px 18px; white-space: nowrap;">
          Get Voucher
        </button>
      </form>
    </div>

    <!-- Links Columns -->
    <div class="fk-footer-top">
      <div class="fk-footer-col">
        <h4>ABOUT ZENKIT</h4>
        <ul>
          <li><a href="about.php">About Us & Heritage</a></li>
          <li><a href="about.php">Quality Assurance & QC</a></li>
          <li><a href="contact.php">Corporate Careers</a></li>
          <li><a href="contact.php">Wholesale & B2B Orders</a></li>
          <li><a href="contact.php">Media & Press Kit</a></li>
        </ul>
      </div>

      <div class="fk-footer-col">
        <h4>HELP & SUPPORT</h4>
        <ul>
          <li><a href="track-order.php">Track Your Shipment</a></li>
          <li><a href="faq.php">Payments, UPI & Invoices</a></li>
          <li><a href="returns.php">7-Day Replacement Policy</a></li>
          <li><a href="faq.php">Warranty Activation</a></li>
          <li><a href="contact.php">Contact Support Desk</a></li>
        </ul>
      </div>

      <div class="fk-footer-col">
        <h4>CONSUMER POLICIES</h4>
        <ul>
          <li><a href="returns.php">Return & Refund Policy</a></li>
          <li><a href="terms.php">Terms of Commercial Sale</a></li>
          <li><a href="privacy.php">Privacy & Data Protection</a></li>
          <li><a href="track-order.php">Shipping & Delivery Logistics</a></li>
          <li><a href="faq.php">Grievance Redressal</a></li>
        </ul>
      </div>

      <div class="fk-footer-col">
        <h4>REGISTERED CORPORATE OFFICE</h4>
        <p style="font-size: 12px; color: #FFFFFF; line-height: 1.6;">
          <strong><?= STORE_NAME ?></strong><br>
          <?= STORE_ADDRESS ?><br>
          CIN: <?= STORE_CIN ?><br>
          GSTIN: <span style="color: var(--fk-yellow); font-weight: 700;"><?= STORE_GSTIN ?></span><br>
          Telephone: <span style="color: var(--fk-blue-light);"><?= STORE_PHONE ?></span><br>
          Corporate Email: <span style="color: var(--fk-blue-light);"><?= STORE_EMAIL ?></span>
        </p>
      </div>
    </div>

    <div class="fk-footer-bottom">
      <div>
        <a href="about.php" style="color: #94A3B8;">About Us</a> • 
        <a href="contact.php" style="color: #94A3B8;">Become a Partner</a> • 
        <a href="track-order.php" style="color: #94A3B8;">Track Order</a> • 
        <a href="faq.php" style="color: #94A3B8;">Help Center</a>
      </div>
      <div>
        &copy; <?= date('Y') ?> <?= STORE_NAME ?>. All Rights Reserved.
      </div>
      <div>
        <span style="color: #94A3B8;">Official Payment Channels: UPI • RuPay • Visa • MasterCard • NetBanking • COD</span>
      </div>
    </div>

  </div>
</footer>

<!-- Floating WhatsApp / Support Desk Widget -->
<div id="zenkit-support-widget" style="position: fixed; bottom: 24px; right: 24px; z-index: 9999;">
  <div id="support-popup" style="display: none; position: absolute; bottom: 60px; right: 0; width: 300px; background: #FFFFFF; border-radius: 8px; box-shadow: var(--shadow-xl); border: 1px solid var(--border-light); overflow: hidden; animation: slideInRight 0.25s ease;">
    <div style="background: #172337; color: #FFFFFF; padding: 14px 16px; display: flex; align-items: center; justify-content: space-between;">
      <div style="display: flex; align-items: center; gap: 8px;">
        <span style="font-size: 20px;">💬</span>
        <div>
          <div style="font-size: 13px; font-weight: 800;">Zenkit Support Desk</div>
          <div style="font-size: 10px; color: #10B981;">● Online (Avg reply: 2 mins)</div>
        </div>
      </div>
      <button onclick="toggleSupportWidget()" style="background: none; border: none; color: #94A3B8; font-size: 16px; cursor: pointer;">✕</button>
    </div>
    <div style="padding: 16px; font-size: 12px; color: #334155; line-height: 1.5;">
      Hello! Need help choosing a laptop, checking pin code delivery, or tracking your order?
      <div style="margin-top: 12px; display: flex; flex-direction: column; gap: 6px;">
        <a href="track-order.php" class="btn btn-outline" style="font-size: 11px; padding: 6px; text-align: left;">🚚 Track My Active Order</a>
        <a href="faq.php" class="btn btn-outline" style="font-size: 11px; padding: 6px; text-align: left;">❓ Check Return & Warranty FAQ</a>
        <a href="contact.php" class="btn btn-fk-primary" style="font-size: 11px; padding: 6px; text-align: left;">📞 Connect to Specialist</a>
      </div>
    </div>
  </div>

  <button onclick="toggleSupportWidget()" style="width: 50px; height: 50px; border-radius: 50%; background: #2874F0; color: #FFFFFF; border: none; box-shadow: 0 4px 14px rgba(40,116,240,0.4); font-size: 24px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.08)'" onmouseout="this.style.transform='scale(1)'">
    💬
  </button>
</div>

<!-- 3D Interactive Product Inspector Modal (Three.js WebGL) -->
<div class="fk-3d-modal-overlay" id="fk-3d-modal">
  <div class="fk-3d-modal-box">
    
    <div class="fk-3d-header">
      <div class="fk-3d-title">
        <span>⬡</span>
        <span id="fk-3d-modal-title">Interactive 3D Product Inspector</span>
      </div>
      <button onclick="closeProduct3D()" style="background: none; border: none; color: #94A3B8; font-size: 24px; cursor: pointer;">✕</button>
    </div>

    <div class="fk-3d-viewport">
      <div id="product-3d-canvas"></div>
      <div style="position: absolute; top: 12px; left: 16px; background: rgba(0,0,0,0.6); padding: 4px 10px; border-radius: 4px; font-size: 11px; color: #94A3B8; pointer-events: none;">
        🖱️ Click & Drag to Rotate 360° • Auto-revolving
      </div>
    </div>

    <div class="fk-3d-controls-bar">
      <div class="fk-3d-btn-group">
        <button class="fk-3d-ctrl-btn" onclick="toggle3DWireframe()">Toggle Wireframe</button>
        <button class="fk-3d-ctrl-btn" onclick="reset3DView()">Reset Angle</button>
      </div>
      
      <div style="display: flex; align-items: center; gap: 8px;">
        <span>Color Finishes:</span>
        <button onclick="set3DColor(0x3b82f6)" title="Royal Blue" style="width: 20px; height: 20px; border-radius: 50%; background: #3b82f6; border: 2px solid #fff; cursor: pointer;"></button>
        <button onclick="set3DColor(0x10b981)" title="Emerald Green" style="width: 20px; height: 20px; border-radius: 50%; background: #10b981; border: 2px solid #fff; cursor: pointer;"></button>
        <button onclick="set3DColor(0x64748b)" title="Slate Gray" style="width: 20px; height: 20px; border-radius: 50%; background: #64748b; border: 2px solid #fff; cursor: pointer;"></button>
        <button onclick="set3DColor(0xf59e0b)" title="Amber Gold" style="width: 20px; height: 20px; border-radius: 50%; background: #f59e0b; border: 2px solid #fff; cursor: pointer;"></button>
      </div>
    </div>

  </div>
</div>

<!-- Slide-Over Cart Drawer -->
<div class="cart-drawer-overlay" id="cart-drawer-overlay" onclick="closeCartDrawer()"></div>
<div class="cart-drawer" id="cart-drawer">
  <div class="cart-drawer-header">
    <div style="font-weight: 700; font-size: 1.05rem; display: flex; align-items: center; gap: 8px;">
      🛒 Shopping Cart
    </div>
    <button onclick="closeCartDrawer()" style="background: none; border: none; color: #FFFFFF; font-size: 22px; cursor: pointer;">✕</button>
  </div>
  
  <div class="cart-drawer-body" id="cart-drawer-body">
    <!-- Populated dynamically via AJAX -->
  </div>

  <div class="cart-drawer-footer">
    <div style="display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 15px; font-weight: 700;">
      <span>Subtotal:</span>
      <span id="cart-drawer-subtotal">₹0</span>
    </div>
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
      <a href="cart.php" class="btn btn-outline" style="font-size: 12px; padding: 10px;">View Full Cart</a>
      <a href="checkout.php" class="btn btn-fk-buy" style="font-size: 12px; padding: 10px;">Checkout &rarr;</a>
    </div>
  </div>
</div>

<script>
function toggleSupportWidget() {
  const p = document.getElementById('support-popup');
  if (p) p.style.display = (p.style.display === 'none' || p.style.display === '') ? 'block' : 'none';
}

function handleNewsletter(e) {
  e.preventDefault();
  const emailInput = document.getElementById('newsletter-email');
  if (!emailInput) return;
  const email = emailInput.value.trim();

  fetch('api/subscribe.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'email=' + encodeURIComponent(email)
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      showToast(data.message, 'success');
      emailInput.value = '';
    } else {
      showToast(data.message, 'error');
    }
  })
  .catch(() => {
    showToast('Subscription request logged.', 'success');
  });
}
</script>

<!-- Core JS Assets -->
<script src="assets/js/three-scene.js"></script>
<script src="assets/js/main.js"></script>

</body>
</html>
