<?php
require_once __DIR__ . '/includes/helpers.php';

$cart = getCart();

// Handle Coupon Application
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply_coupon'])) {
    $code = strtoupper(trim($_POST['coupon_code'] ?? ''));
    $db = getDB();
    $cStmt = $db->prepare("SELECT * FROM coupons WHERE code = ?");
    $cStmt->execute([$code]);
    $coupon = $cStmt->fetch();

    if ($coupon) {
        $subtotal = getCartSubtotal();
        if ($subtotal >= $coupon['min_spend']) {
            $_SESSION['applied_coupon'] = $coupon;
            setFlash('success', "Coupon '{$code}' applied! {$coupon['discount_percent']}% discount activated.");
        } else {
            setFlash('error', "Minimum spend of " . formatPrice($coupon['min_spend']) . " required for this coupon.");
        }
    } else {
        setFlash('error', "Invalid coupon code.");
    }
    safeRedirect('cart.php');
}

if (isset($_GET['remove_coupon'])) {
    unset($_SESSION['applied_coupon']);
    setFlash('info', 'Coupon removed.');
    safeRedirect('cart.php');
}

$pageTitle = "Shopping Cart | Zenkit";
require_once __DIR__ . '/includes/header.php';

$subtotal = getCartSubtotal();
$discount = getCartDiscount();
$shipping = getShippingFee();
$finalTotal = getCartFinalTotal();
?>

<main class="main-content">
  <div class="container">
    
    <div style="font-size: 1.3rem; font-weight: 800; color: #1E293B; margin-bottom: 20px;">
      Shopping Cart (<?= getCartCount() ?> Items)
    </div>

    <?php if (empty($cart)): ?>
      <div style="background: #FFFFFF; border-radius: var(--radius-xs); border: 1px solid var(--border-light); padding: 80px 20px; text-align: center; box-shadow: var(--shadow-sm);">
        <div style="font-size: 4rem; margin-bottom: 16px;">🛒</div>
        <h2 style="font-size: 1.3rem; font-weight: 800; color: #1E293B; margin-bottom: 8px;">Your Cart is Empty!</h2>
        <p style="color: #64748B; font-size: 0.95rem; margin-bottom: 24px;">Explore our catalog and find the best electronics deals.</p>
        <a href="shop.php" class="btn btn-fk-primary" style="padding: 12px 32px;">Shop Now</a>
      </div>
    <?php else: ?>
      
      <div style="display: grid; grid-template-columns: 1fr 380px; gap: 24px; align-items: start;">
        
        <!-- Left: Cart Items -->
        <div style="background: #FFFFFF; border-radius: var(--radius-xs); border: 1px solid var(--border-light); box-shadow: var(--shadow-sm);">
          
          <div style="padding: 16px 24px; border-bottom: 1px solid var(--border-light); display: flex; justify-content: space-between; align-items: center;">
            <span style="font-weight: 700; font-size: 14px; color: #1E293B;">From Zenkit Super Retailer</span>
            <span style="color: var(--fk-green); font-size: 12px; font-weight: 700;">✓ Free Delivery Eligible</span>
          </div>

          <!-- Items loop -->
          <div style="padding: 16px 24px;">
            <?php foreach ($cart as $id => $item): ?>
              <div style="display: flex; gap: 20px; padding: 20px 0; border-bottom: 1px solid var(--border-subtle);">
                <div style="width: 100px; height: 100px; border: 1px solid var(--border-light); border-radius: 4px; padding: 8px; display: flex; align-items: center; justify-content: center;">
                  <img src="<?= htmlspecialchars($item['image_url']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                </div>

                <div style="flex: 1;">
                  <a href="product.php?id=<?= $item['id'] ?>" style="font-size: 1rem; font-weight: 700; color: #212121; text-decoration: none;">
                    <?= htmlspecialchars($item['name']) ?>
                  </a>
                  <div style="font-size: 12px; color: #878787; margin: 4px 0 6px 0;">Seller: Zenkit Certified Tech</div>

                  <?php if (!empty($item['options'])): ?>
                    <div style="margin: 6px 0 10px 0; display: flex; flex-wrap: wrap; gap: 6px;">
                      <?php if (!empty($item['options']['color'])): ?>
                        <span style="font-size: 11px; background: #F1F5F9; color: #334155; padding: 2px 8px; border-radius: 3px; font-weight: 700; border: 1px solid #CBD5E1;">
                          🎨 Color: <?= htmlspecialchars($item['options']['color']) ?>
                        </span>
                      <?php endif; ?>
                      <?php if (!empty($item['options']['variant'])): ?>
                        <span style="font-size: 11px; background: #EFF6FF; color: #1D4ED8; padding: 2px 8px; border-radius: 3px; font-weight: 700; border: 1px solid #BFDBFE;">
                          ⚙️ Edition: <?= htmlspecialchars($item['options']['variant']) ?>
                        </span>
                      <?php endif; ?>
                      <?php if (!empty($item['options']['addons']) && is_array($item['options']['addons'])): ?>
                        <?php foreach ($item['options']['addons'] as $addonName): ?>
                          <span style="font-size: 11px; background: #ECFDF5; color: #047857; padding: 2px 8px; border-radius: 3px; font-weight: 700; border: 1px solid #A7F3D0;">
                            🛡️ <?= htmlspecialchars($addonName) ?>
                          </span>
                        <?php endforeach; ?>
                      <?php endif; ?>
                    </div>
                  <?php endif; ?>

                  <div style="display: flex; align-items: baseline; gap: 10px; margin-bottom: 14px;">
                    <span style="font-size: 1.25rem; font-weight: 800; color: #212121;"><?= formatPrice($item['price']) ?></span>
                    <span style="font-size: 13px; color: #878787;">x <?= $item['quantity'] ?></span>
                    <span style="font-size: 1rem; font-weight: 800; color: var(--fk-blue); margin-left: auto;">
                      <?= formatPrice($item['price'] * $item['quantity']) ?>
                    </span>
                  </div>

                  <!-- Qty Controls & Remove -->
                  <div style="display: flex; align-items: center; gap: 16px;">
                    <div style="display: flex; align-items: center; border: 1px solid #D0D5DD; border-radius: 2px;">
                      <button onclick="updateCartQty('<?= $id ?>', <?= $item['quantity'] - 1 ?>)" style="padding: 4px 12px; border: none; background: #F8FAFC; cursor: pointer; font-weight: 800; font-size: 14px;">-</button>
                      <span style="padding: 4px 14px; font-size: 13px; font-weight: 800;"><?= $item['quantity'] ?></span>
                      <button onclick="updateCartQty('<?= $id ?>', <?= $item['quantity'] + 1 ?>)" style="padding: 4px 12px; border: none; background: #F8FAFC; cursor: pointer; font-weight: 800; font-size: 14px;">+</button>
                    </div>

                    <button onclick="removeCartItem('<?= $id ?>')" style="background: none; border: none; color: #212121; font-weight: 700; font-size: 13px; cursor: pointer;">
                      REMOVE
                    </button>
                  </div>

                </div>
              </div>
            <?php endforeach; ?>
          </div>

          <!-- Bottom Action Bar -->
          <div style="padding: 16px 24px; background: #FFFFFF; display: flex; justify-content: flex-end; box-shadow: 0 -2px 6px rgba(0,0,0,0.05); position: sticky; bottom: 0;">
            <a href="checkout.php" class="btn btn-fk-buy" style="padding: 14px 40px; font-size: 15px; font-weight: 800;">
              PLACE ORDER &rarr;
            </a>
          </div>

        </div>

        <!-- Right: Sticky Price Details -->
        <div style="position: sticky; top: 76px;">
          
          <!-- Coupon Box -->
          <div style="background: #FFFFFF; border: 1px solid var(--border-light); border-radius: var(--radius-xs); padding: 18px; margin-bottom: 16px; box-shadow: var(--shadow-sm);">
            <div style="font-weight: 800; font-size: 13px; text-transform: uppercase; color: #1E293B; margin-bottom: 10px;">Apply Discount Coupon</div>
            
            <?php if (isset($_SESSION['applied_coupon'])): ?>
              <div style="display: flex; justify-content: space-between; align-items: center; background: #E8F5E9; border: 1px dashed var(--fk-green); padding: 10px 14px; border-radius: 4px;">
                <div>
                  <strong style="color: var(--fk-green);"><?= htmlspecialchars($_SESSION['applied_coupon']['code']) ?></strong>
                  <div style="font-size: 11px; color: #475569;">Applied (<?= $_SESSION['applied_coupon']['discount_percent'] ?>% off)</div>
                </div>
                <a href="cart.php?remove_coupon=1" style="color: #DC2626; font-size: 12px; font-weight: 700;">Remove</a>
              </div>
            <?php else: ?>
              <form action="cart.php" method="POST" style="display: flex; gap: 8px;">
                <input type="text" name="coupon_code" class="form-control" placeholder="Use 'ZENKIT20'" style="text-transform: uppercase; font-weight: 700;">
                <button type="submit" name="apply_coupon" class="btn btn-outline" style="font-weight: 700; color: var(--fk-blue);">Apply</button>
              </form>
              <div style="font-size: 11px; color: #64748B; margin-top: 6px;">
                💡 Try code <strong>ZENKIT20</strong> for 20% instant savings!
              </div>
            <?php endif; ?>
          </div>

          <!-- Price Summary Card -->
          <div style="background: #FFFFFF; border: 1px solid var(--border-light); border-radius: var(--radius-xs); box-shadow: var(--shadow-sm);">
            <div style="padding: 16px 20px; border-bottom: 1px solid var(--border-light); font-weight: 800; color: #878787; text-transform: uppercase; font-size: 13px;">
              Price Details
            </div>

            <div style="padding: 20px; display: flex; flex-direction: column; gap: 14px; font-size: 14px;">
              <div style="display: flex; justify-content: space-between;">
                <span>Price (<?= getCartCount() ?> items)</span>
                <span><?= formatPrice($subtotal) ?></span>
              </div>

              <div style="display: flex; justify-content: space-between; color: var(--fk-green);">
                <span>Discount</span>
                <span>- <?= formatPrice($discount) ?></span>
              </div>

              <div style="display: flex; justify-content: space-between;">
                <span>Delivery Charges</span>
                <span>
                  <?php if ($shipping == 0): ?>
                    <span style="color: var(--fk-green); font-weight: 700;">FREE</span>
                  <?php else: ?>
                    <?= formatPrice($shipping) ?>
                  <?php endif; ?>
                </span>
              </div>

              <div style="border-top: 1px dashed var(--border-light); padding-top: 14px; margin-top: 4px; display: flex; justify-content: space-between; font-size: 1.15rem; font-weight: 900; color: #1E293B;">
                <span>Total Amount</span>
                <span><?= formatPrice($finalTotal) ?></span>
              </div>

              <?php if ($discount > 0): ?>
                <div style="font-size: 12px; color: var(--fk-green); font-weight: 700; border-top: 1px solid var(--border-subtle); padding-top: 10px;">
                  You will save <?= formatPrice($discount) ?> on this order!
                </div>
              <?php endif; ?>
            </div>

            <div style="padding: 14px 20px; background: #F8FAFC; border-top: 1px solid var(--border-light); font-size: 12px; color: #64748B; display: flex; align-items: center; gap: 8px;">
              <span>🛡️</span>
              <span>Safe and Secure Payments. 100% Authentic Products.</span>
            </div>
          </div>

        </div>

      </div>

    <?php endif; ?>

  </div>
</main>

<script>
function updateCartQty(cartKey, qty) {
  fetch(`api/cart.php?action=update&cart_key=${encodeURIComponent(cartKey)}&quantity=${qty}`)
    .then(r => r.json())
    .then(data => {
      window.location.reload();
    });
}
function removeCartItem(cartKey) {
  fetch(`api/cart.php?action=remove&cart_key=${encodeURIComponent(cartKey)}`)
    .then(r => r.json())
    .then(data => {
      window.location.reload();
    });
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
