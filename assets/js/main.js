/**
 * Zenkit Main Interactivity Script
 * Manages Cart Drawer, Audio Haptics, First-Visit Registration Modal & AJAX
 */

// Web Audio API Micro-Haptics
const audioCtx = (window.AudioContext || window.webkitAudioContext) ? new (window.AudioContext || window.webkitAudioContext)() : null;

function playAudioHaptic(type = 'click') {
  if (!audioCtx) return;
  try {
    if (audioCtx.state === 'suspended') {
      audioCtx.resume();
    }
    const osc = audioCtx.createOscillator();
    const gain = audioCtx.createGain();
    osc.connect(gain);
    gain.connect(audioCtx.destination);

    const now = audioCtx.currentTime;
    if (type === 'click') {
      osc.type = 'sine';
      osc.frequency.setValueAtTime(600, now);
      osc.frequency.exponentialRampToValueAtTime(800, now + 0.05);
      gain.gain.setValueAtTime(0.04, now);
      gain.gain.exponentialRampToValueAtTime(0.001, now + 0.05);
      osc.start(now);
      osc.stop(now + 0.05);
    } else if (type === 'success') {
      osc.type = 'triangle';
      osc.frequency.setValueAtTime(523.25, now); // C5
      osc.frequency.setValueAtTime(659.25, now + 0.08); // E5
      gain.gain.setValueAtTime(0.06, now);
      gain.gain.exponentialRampToValueAtTime(0.001, now + 0.2);
      osc.start(now);
      osc.stop(now + 0.2);
    }
  } catch (e) {
    // Ignore audio autoplay restrictions
  }
}

// Toast Notifications
function showToast(message, type = 'success') {
  playAudioHaptic(type === 'success' ? 'success' : 'click');
  const container = document.getElementById('zen-toast-container') || createToastContainer();
  const toast = document.createElement('div');
  toast.className = 'zen-toast';
  toast.style.borderLeft = type === 'error' ? '4px solid #EF4444' : '4px solid #10B981';
  toast.innerHTML = `
    <span style="color: ${type === 'error' ? '#EF4444' : '#10B981'}; font-weight: 800;">
      ${type === 'error' ? '✕' : '✓'}
    </span>
    <span>${message}</span>
  `;
  container.appendChild(toast);
  setTimeout(() => {
    toast.style.opacity = '0';
    setTimeout(() => toast.remove(), 300);
  }, 3500);
}

function createToastContainer() {
  const c = document.createElement('div');
  c.id = 'zen-toast-container';
  c.style.cssText = 'position: fixed; top: 74px; right: 20px; z-index: 99999; display: flex; flex-direction: column; gap: 8px;';
  document.body.appendChild(c);
  return c;
}

// Cart Drawer Handlers
function openCartDrawer() {
  playAudioHaptic('click');
  document.getElementById('cart-drawer-overlay')?.classList.add('active');
  document.getElementById('cart-drawer')?.classList.add('active');
  loadCartDrawerContent();
}

function closeCartDrawer() {
  document.getElementById('cart-drawer-overlay')?.classList.remove('active');
  document.getElementById('cart-drawer')?.classList.remove('active');
}

function loadCartDrawerContent() {
  const body = document.getElementById('cart-drawer-body');
  if (!body) return;
  body.innerHTML = '<div style="text-align: center; padding: 40px; color: #878787;">Loading your cart...</div>';

  fetch('api/cart.php?action=get')
    .then(r => r.json())
    .then(data => {
      updateCartBadge(data.cart_count);
      if (!data.items || Object.keys(data.items).length === 0) {
        body.innerHTML = `
          <div style="text-align: center; padding: 60px 20px;">
            <div style="font-size: 3rem; margin-bottom: 12px;">🛒</div>
            <h3 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 6px;">Your Cart is Empty</h3>
            <p style="color: #878787; font-size: 0.85rem; margin-bottom: 20px;">Explore our electronics & gadgets!</p>
            <a href="shop.php" class="btn btn-fk-primary" onclick="closeCartDrawer()">Shop Now</a>
          </div>
        `;
        document.getElementById('cart-drawer-subtotal').innerText = '₹0';
        return;
      }

      let html = '<div style="display: flex; flex-direction: column; gap: 14px;">';
      for (const id in data.items) {
        const item = data.items[id];
        const itemKey = item.cart_key || item.id;
        let optionsBadge = '';
        if (item.options) {
          const opts = [];
          if (item.options.color) opts.push(`🎨 ${item.options.color}`);
          if (item.options.variant) opts.push(`⚙️ ${item.options.variant}`);
          if (item.options.addons && item.options.addons.length) {
            opts.push(`🛡️ ${item.options.addons.length} Add-on(s)`);
          }
          if (opts.length > 0) {
            optionsBadge = `<div style="font-size: 11px; color: #64748B; margin: 2px 0 4px 0;">${opts.join(' • ')}</div>`;
          }
        }

        html += `
          <div style="display: flex; gap: 12px; border-bottom: 1px solid #F0F0F0; padding-bottom: 12px;">
            <img src="${item.image_url}" alt="${item.name}" style="width: 60px; height: 60px; object-fit: contain; border: 1px solid #E2E8F0; border-radius: 4px; padding: 4px;">
            <div style="flex: 1;">
              <h4 style="font-size: 0.85rem; font-weight: 600; line-height: 1.3; margin-bottom: 2px;">${item.name}</h4>
              ${optionsBadge}
              <div style="font-weight: 800; color: #212121; font-size: 0.95rem;">₹${Math.round(item.price).toLocaleString('en-IN')}</div>
              <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 8px;">
                <div style="display: flex; align-items: center; border: 1px solid #E0E0E0; border-radius: 2px;">
                  <button onclick="updateDrawerQty('${itemKey}', ${item.quantity - 1})" style="padding: 2px 8px; border: none; background: #F8FAFC; cursor: pointer; font-weight: 700;">-</button>
                  <span style="padding: 2px 10px; font-size: 12px; font-weight: 700;">${item.quantity}</span>
                  <button onclick="updateDrawerQty('${itemKey}', ${item.quantity + 1})" style="padding: 2px 8px; border: none; background: #F8FAFC; cursor: pointer; font-weight: 700;">+</button>
                </div>
                <button onclick="removeDrawerItem('${itemKey}')" style="background: none; border: none; color: #DC2626; font-size: 11px; font-weight: 700; cursor: pointer;">Remove</button>
              </div>
            </div>
          </div>
        `;
      }
      html += '</div>';
      body.innerHTML = html;
      document.getElementById('cart-drawer-subtotal').innerText = '₹' + Math.round(data.subtotal).toLocaleString('en-IN');
    })
    .catch(() => {
      body.innerHTML = '<div style="color: red; padding: 20px;">Failed to load cart.</div>';
    });
}

function updateDrawerQty(cartKey, newQty) {
  fetch(`api/cart.php?action=update&cart_key=${encodeURIComponent(cartKey)}&quantity=${newQty}`)
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        loadCartDrawerContent();
      } else {
        showToast(data.message, 'error');
      }
    });
}

function removeDrawerItem(cartKey) {
  fetch(`api/cart.php?action=remove&cart_key=${encodeURIComponent(cartKey)}`)
    .then(r => r.json())
    .then(data => {
      showToast('Item removed from cart');
      loadCartDrawerContent();
    });
}

// Global AJAX Add to Cart
function handleAddToCart(productId, qty = 1, openDrawer = true, options = null) {
  playAudioHaptic('click');
  const formData = new FormData();
  formData.append('product_id', productId);
  formData.append('quantity', qty);
  if (options) {
    formData.append('options', typeof options === 'string' ? options : JSON.stringify(options));
  }

  fetch('api/cart.php?action=add', {
    method: 'POST',
    body: formData
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      showToast(data.message, 'success');
      updateCartBadge(data.cart_count);
      if (openDrawer) {
        setTimeout(openCartDrawer, 300);
      }
    } else {
      showToast(data.message, 'error');
    }
  })
  .catch(err => {
    showToast('Could not add to cart. Try again.', 'error');
  });
}

function updateCartBadge(count) {
  const badges = document.querySelectorAll('.fk-cart-badge');
  badges.forEach(b => {
    b.innerText = count;
    b.style.display = count > 0 ? 'inline-block' : 'none';
  });
}

// First-Visit Flipkart Split Registration Modal
function initFirstVisitModal() {
  const modal = document.getElementById('fk-auth-modal');
  if (!modal) return;

  // Check if user is already logged in (checked via data attribute or class)
  const isAuth = document.body.dataset.authenticated === 'true';
  const hasSeenModal = sessionStorage.getItem('zenkit_auth_prompt_shown');

  if (!isAuth && !hasSeenModal) {
    // Show after slight delay for smooth entry
    setTimeout(() => {
      modal.classList.add('active');
      sessionStorage.setItem('zenkit_auth_prompt_shown', 'true');
    }, 700);
  }
}

function openAuthModal(mode = 'register') {
  playAudioHaptic('click');
  const modal = document.getElementById('fk-auth-modal');
  if (!modal) return;

  const titleEl = document.getElementById('modal-auth-title');
  const descEl = document.getElementById('modal-auth-desc');
  const submitBtn = document.getElementById('modal-submit-btn');
  const toggleText = document.getElementById('modal-toggle-text');
  const toggleBtn = document.getElementById('modal-toggle-btn');
  const nameField = document.getElementById('modal-name-group');
  const authForm = document.getElementById('modal-auth-form');

  if (mode === 'login') {
    if (titleEl) titleEl.innerText = "Login";
    if (descEl) descEl.innerText = "Get access to your Orders, Wishlist and Recommendations";
    if (submitBtn) submitBtn.innerText = "Log In";
    if (nameField) nameField.style.display = 'none';
    if (authForm) authForm.action = 'login.php';
    if (toggleText) toggleText.innerText = "New to Zenkit?";
    if (toggleBtn) {
      toggleBtn.innerText = "Create an account";
      toggleBtn.onclick = () => openAuthModal('register');
    }
  } else {
    if (titleEl) titleEl.innerText = "Looks like you're new here!";
    if (descEl) descEl.innerText = "Sign up with your details to get started with exclusive deals";
    if (submitBtn) submitBtn.innerText = "Continue & Register";
    if (nameField) nameField.style.display = 'block';
    if (authForm) authForm.action = 'register.php';
    if (toggleText) toggleText.innerText = "Existing User?";
    if (toggleBtn) {
      toggleBtn.innerText = "Log in";
      toggleBtn.onclick = () => openAuthModal('login');
    }
  }

  modal.classList.add('active');
}

function closeAuthModal() {
  const modal = document.getElementById('fk-auth-modal');
  if (modal) modal.classList.remove('active');
}

// Deals Countdown Timer
function startDealsTimer() {
  const timerEl = document.getElementById('deal-timer');
  if (!timerEl) return;

  let totalSeconds = 14 * 3600 + 42 * 60 + 19; // 14 hours 42 mins 19 secs
  setInterval(() => {
    if (totalSeconds <= 0) totalSeconds = 24 * 3600;
    totalSeconds--;
    const h = Math.floor(totalSeconds / 3600).toString().padStart(2, '0');
    const m = Math.floor((totalSeconds % 3600) / 60).toString().padStart(2, '0');
    const s = Math.floor(totalSeconds % 60).toString().padStart(2, '0');
    timerEl.innerText = `${h} : ${m} : ${s} Left`;
  }, 1000);
}

// Pincode Delivery Checker
function checkPincode() {
  const input = document.getElementById('delivery-pincode');
  const result = document.getElementById('pincode-result');
  if (!input || !result) return;

  const pin = input.value.trim();
  if (pin.length !== 6 || isNaN(pin)) {
    result.innerHTML = '<span style="color: #DC2626;">Please enter a valid 6-digit Pincode.</span>';
    return;
  }

  playAudioHaptic('click');
  result.innerHTML = '<span style="color: #64748B;">Checking logistics availability...</span>';

  setTimeout(() => {
    result.innerHTML = `
      <div style="color: #16A34A; font-weight: 700; margin-top: 6px;">
        ✓ Delivery available to <strong>${pin}</strong> by <strong>Tomorrow, 5:00 PM</strong>
      </div>
      <div style="font-size: 11px; color: #64748B; margin-top: 2px;">
        Eligible for Cash on Delivery & Free 7-Day Replacement
      </div>
    `;
  }, 400);
}

// Fill Demo Credentials in Auth Forms
function fillDemoUser(email, password, name = '') {
  playAudioHaptic('click');
  const emailInput = document.querySelector('input[name="email"]');
  const passInput = document.querySelector('input[name="password"]');
  const nameInput = document.querySelector('input[name="name"]');

  if (emailInput) emailInput.value = email;
  if (passInput) passInput.value = password;
  if (nameInput && name) nameInput.value = name;
  showToast(`Auto-filled: ${email}`);
}

// Wishlist Handler
function handleToggleWishlist(productId, btnEl) {
  playAudioHaptic('click');
  fetch('api/wishlist.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'product_id=' + productId
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      showToast(data.message, 'success');
      const badge = document.getElementById('header-wishlist-badge');
      if (badge) {
        badge.innerText = data.count;
        badge.style.display = data.count > 0 ? 'inline-block' : 'none';
      }
      if (btnEl) {
        btnEl.innerText = data.in_wishlist ? '❤️' : '🤍';
        btnEl.title = data.in_wishlist ? 'In your wishlist' : 'Add to wishlist';
      }
    } else {
      showToast(data.message, 'error');
    }
  })
  .catch(() => {
    showToast('Wishlist action failed.', 'error');
  });
}

// Initialize on DOM load
document.addEventListener('DOMContentLoaded', () => {
  initFirstVisitModal();
  startDealsTimer();
});

