<?php
require_once __DIR__ . '/includes/helpers.php';

// Handle Buy Now parameter
if (isset($_GET['buy_now'])) {
    $buyId = (int)$_GET['buy_now'];
    addToCart($buyId, 1);
}

$cart = getCart();
if (empty($cart)) {
    safeRedirect('cart.php');
}

$user = currentUser();
$db = getDB();

$subtotal = getCartSubtotal();
$discount = getCartDiscount();
$shipping = getShippingFee();
$finalTotal = getCartFinalTotal();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $custName = trim($_POST['customer_name'] ?? '');
    $custEmail = trim($_POST['customer_email'] ?? '');
    $custPhone = trim($_POST['customer_phone'] ?? '');
    $shipAddress = trim($_POST['shipping_address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $postalCode = trim($_POST['postal_code'] ?? '');
    $paymentMethod = trim($_POST['payment_method'] ?? 'UPI (Google Pay / PhonePe)');

    if (empty($custName) || empty($custEmail) || empty($custPhone) || empty($shipAddress) || empty($city) || empty($postalCode)) {
        $error = "Please fill in all shipping address fields.";
    } else {
        $orderNumber = 'ZK-' . strtoupper(substr(uniqid(), 7)) . rand(100, 999);
        $trackingCode = 'TRK-' . strtoupper(substr(md5(uniqid()), 0, 8));
        $userId = $user['id'] ?? null;
        $paymentStatus = ($paymentMethod === 'Cash on Delivery') ? 'Pending' : 'Paid';

        // Insert Order
        $stmt = $db->prepare("INSERT INTO orders 
            (order_number, user_id, customer_name, customer_email, customer_phone, shipping_address, city, postal_code, payment_method, payment_status, total_amount, discount_amount, final_amount, order_status, tracking_code)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Processing', ?)");
        
        $stmt->execute([
            $orderNumber,
            $userId,
            $custName,
            $custEmail,
            $custPhone,
            $shipAddress,
            $city,
            $postalCode,
            $paymentMethod,
            $paymentStatus,
            $subtotal,
            $discount,
            $finalTotal,
            $trackingCode
        ]);
        $orderId = $db->lastInsertId();

        // Insert Order Items & decrease stock
        $itemStmt = $db->prepare("INSERT INTO order_items (order_id, product_id, product_name, price, quantity, subtotal) VALUES (?, ?, ?, ?, ?, ?)");
        $stockStmt = $db->prepare("UPDATE products SET stock = MAX(0, stock - ?) WHERE id = ?");

        foreach ($cart as $item) {
            $itemSubtotal = $item['price'] * $item['quantity'];
            $productName = $item['name'];
            if (!empty($item['options'])) {
                $optParts = [];
                if (!empty($item['options']['color'])) $optParts[] = "Color: " . $item['options']['color'];
                if (!empty($item['options']['variant'])) $optParts[] = "Config: " . $item['options']['variant'];
                if (!empty($item['options']['addons']) && is_array($item['options']['addons'])) {
                    $optParts[] = "Addons: " . implode(', ', $item['options']['addons']);
                }
                if (!empty($optParts)) {
                    $productName .= " (" . implode(' | ', $optParts) . ")";
                }
            }
            $itemStmt->execute([$orderId, $item['id'], $productName, $item['price'], $item['quantity'], $itemSubtotal]);
            $stockStmt->execute([$item['quantity'], $item['id']]);
        }

        clearCart();
        setFlash('success', "Order placed successfully! Order ID: $orderNumber");
        safeRedirect("order-confirmation.php?id=$orderId");
    }
}

$pageTitle = "Checkout | Zenkit Marketplace";
require_once __DIR__ . '/includes/header.php';
?>

<main class="main-content">
  <div class="container" style="max-width: 1080px;">
    
    <div style="font-size: 1.4rem; font-weight: 800; color: #1E293B; margin-bottom: 20px;">
      Checkout & Order Review
    </div>

    <?php if (!empty($error)): ?>
      <div style="background: #FEE2E2; border-left: 4px solid #EF4444; color: #DC2626; padding: 12px 16px; border-radius: 2px; font-weight: 600; margin-bottom: 16px;">
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <form action="checkout.php" method="POST">
      <div style="display: grid; grid-template-columns: 1fr 380px; gap: 24px; align-items: start;">
        
        <!-- Left: Steps / Address & Payment -->
        <div style="display: flex; flex-direction: column; gap: 16px;">
          
          <!-- Step 1: Login Status -->
          <div style="background: #FFFFFF; border: 1px solid var(--border-light); border-radius: var(--radius-xs); padding: 18px 24px; display: flex; align-items: center; justify-content: space-between; box-shadow: var(--shadow-sm);">
            <div>
              <span style="background: #F1F5F9; color: #64748B; font-weight: 800; padding: 2px 8px; border-radius: 2px; font-size: 12px; margin-right: 8px;">1</span>
              <strong style="color: #1E293B;">ACCOUNT DETAILS</strong>
              <div style="font-size: 13px; color: #64748B; margin-top: 4px; margin-left: 28px;">
                <?= $user ? "Signed in as <strong>{$user['name']}</strong> ({$user['email']})" : "Checking out as Guest (or Sign in for order sync)" ?>
              </div>
            </div>
            <?php if (!$user): ?>
              <button type="button" onclick="openAuthModal('login')" class="btn btn-outline" style="font-size: 12px; padding: 6px 14px;">Sign In</button>
            <?php endif; ?>
          </div>

          <!-- Step 2: Delivery Address -->
          <div style="background: #FFFFFF; border: 1px solid var(--border-light); border-radius: var(--radius-xs); padding: 24px; box-shadow: var(--shadow-sm);">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 18px; border-bottom: 1px solid var(--border-subtle); padding-bottom: 12px;">
              <div>
                <span style="background: var(--fk-blue); color: #FFFFFF; font-weight: 800; padding: 2px 8px; border-radius: 2px; font-size: 12px; margin-right: 8px;">2</span>
                <strong style="color: #1E293B; font-size: 1.05rem;">DELIVERY ADDRESS</strong>
              </div>
              <button type="button" onclick="fillShippingAddress()" class="btn btn-outline" style="font-size: 11px; padding: 4px 10px;">
                ⚡ Fast Fill Demo Address
              </button>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
              <div class="form-group">
                <label class="form-label">Full Name *</label>
                <input type="text" id="ship-name" name="customer_name" class="form-control" placeholder="Receiver's name" required value="<?= htmlspecialchars($user['name'] ?? 'Rahul Sharma') ?>">
              </div>

              <div class="form-group">
                <label class="form-label">Mobile Number *</label>
                <input type="tel" id="ship-phone" name="customer_phone" class="form-control" placeholder="10-digit mobile number" required value="9810123456">
              </div>
            </div>

            <div class="form-group">
              <label class="form-label">Email Address *</label>
              <input type="email" id="ship-email" name="customer_email" class="form-control" placeholder="For order invoices & tracking" required value="<?= htmlspecialchars($user['email'] ?? 'demo@zenkit.io') ?>">
            </div>

            <div class="form-group">
              <label class="form-label">Street Address / House No / Apartment *</label>
              <textarea id="ship-address" name="shipping_address" class="form-control" rows="2" placeholder="e.g. Flat 402, Green Valley Apartments, Outer Ring Road" required>Flat 402, Green Valley Apartments, Outer Ring Road</textarea>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
              <div class="form-group">
                <label class="form-label">City *</label>
                <input type="text" id="ship-city" name="city" class="form-control" placeholder="e.g. Bangalore" required value="Bangalore">
              </div>

              <div class="form-group">
                <label class="form-label">Pincode *</label>
                <input type="text" id="ship-pincode" name="postal_code" class="form-control" placeholder="6 digits" required value="560103">
              </div>
            </div>
          </div>

          <!-- Step 3: Payment Options -->
          <div style="background: #FFFFFF; border: 1px solid var(--border-light); border-radius: var(--radius-xs); padding: 24px; box-shadow: var(--shadow-sm);">
            <div style="margin-bottom: 18px; border-bottom: 1px solid var(--border-subtle); padding-bottom: 12px;">
              <span style="background: var(--fk-blue); color: #FFFFFF; font-weight: 800; padding: 2px 8px; border-radius: 2px; font-size: 12px; margin-right: 8px;">3</span>
              <strong style="color: #1E293B; font-size: 1.05rem;">PAYMENT OPTIONS</strong>
            </div>

            <div style="display: flex; flex-direction: column; gap: 12px;">
              <label style="border: 1px solid var(--border-light); border-radius: 4px; padding: 14px; display: flex; align-items: center; gap: 12px; cursor: pointer; background: #F8FAFC;">
                <input type="radio" name="payment_method" value="UPI (Google Pay / PhonePe)" checked>
                <div>
                  <div style="font-weight: 700; color: #1E293B;">UPI / QR Code (Google Pay, PhonePe, Paytm)</div>
                  <div style="font-size: 12px; color: #64748B;">Instant seamless checkout with zero transaction fees</div>
                </div>
              </label>

              <label style="border: 1px solid var(--border-light); border-radius: 4px; padding: 14px; display: flex; align-items: center; gap: 12px; cursor: pointer;">
                <input type="radio" name="payment_method" value="Credit / Debit Card">
                <div>
                  <div style="font-weight: 700; color: #1E293B;">Credit / Debit / ATM Card</div>
                  <div style="font-size: 12px; color: #64748B;">Visa, MasterCard, RuPay, American Express</div>
                </div>
              </label>

              <label style="border: 1px solid var(--border-light); border-radius: 4px; padding: 14px; display: flex; align-items: center; gap: 12px; cursor: pointer;">
                <input type="radio" name="payment_method" value="Cash on Delivery">
                <div>
                  <div style="font-weight: 700; color: #1E293B;">Cash on Delivery (COD)</div>
                  <div style="font-size: 12px; color: #64748B;">Pay with cash or UPI QR scan upon courier arrival</div>
                </div>
              </label>
            </div>

            <button type="submit" name="place_order" class="btn btn-fk-buy" style="width: 100%; margin-top: 24px; padding: 16px; font-size: 16px; font-weight: 900;">
              CONFIRM & PAY <?= formatPrice($finalTotal) ?> &rarr;
            </button>
          </div>

        </div>

        <!-- Right: Order Summary Sidebar -->
        <div style="position: sticky; top: 76px;">
          <div style="background: #FFFFFF; border: 1px solid var(--border-light); border-radius: var(--radius-xs); box-shadow: var(--shadow-sm);">
            
            <div style="padding: 16px 20px; border-bottom: 1px solid var(--border-light); font-weight: 800; color: #878787; text-transform: uppercase; font-size: 13px;">
              Order Summary (<?= getCartCount() ?> Items)
            </div>

            <!-- Mini items preview -->
            <div style="padding: 16px 20px; border-bottom: 1px solid var(--border-subtle); max-height: 220px; overflow-y: auto; display: flex; flex-direction: column; gap: 10px;">
              <?php foreach ($cart as $item): ?>
                <div style="display: flex; justify-content: space-between; font-size: 12px; gap: 8px;">
                  <span style="color: #333333; line-height: 1.3;"><?= htmlspecialchars($item['name']) ?> <strong>x <?= $item['quantity'] ?></strong></span>
                  <span style="font-weight: 700; color: #212121;"><?= formatPrice($item['price'] * $item['quantity']) ?></span>
                </div>
              <?php endforeach; ?>
            </div>

            <div style="padding: 20px; display: flex; flex-direction: column; gap: 12px; font-size: 14px;">
              <div style="display: flex; justify-content: space-between;">
                <span>Subtotal</span>
                <span><?= formatPrice($subtotal) ?></span>
              </div>

              <?php if ($discount > 0): ?>
                <div style="display: flex; justify-content: space-between; color: var(--fk-green); font-weight: 700;">
                  <span>Coupon Savings</span>
                  <span>- <?= formatPrice($discount) ?></span>
                </div>
              <?php endif; ?>

              <div style="display: flex; justify-content: space-between;">
                <span>Delivery Charges</span>
                <span><?= $shipping == 0 ? '<span style="color: var(--fk-green); font-weight: 700;">FREE</span>' : formatPrice($shipping) ?></span>
              </div>

              <div style="border-top: 1px dashed var(--border-light); padding-top: 14px; margin-top: 4px; display: flex; justify-content: space-between; font-size: 1.15rem; font-weight: 900; color: #1E293B;">
                <span>Total Payable</span>
                <span><?= formatPrice($finalTotal) ?></span>
              </div>
            </div>

            <div style="padding: 14px 20px; background: #F8FAFC; border-top: 1px solid var(--border-light); font-size: 11px; color: #64748B; line-height: 1.4;">
              Zenkit Buyer Protection guarantees that your item arrives as described or your money back.
            </div>

          </div>
        </div>

      </div>
    </form>

  </div>
</main>

<script>
function fillShippingAddress() {
  document.getElementById('ship-name').value = "Rahul Sharma";
  document.getElementById('ship-phone').value = "9810123456";
  document.getElementById('ship-email').value = "demo@zenkit.io";
  document.getElementById('ship-address').value = "Flat 402, Green Valley Apartments, Outer Ring Road";
  document.getElementById('ship-city').value = "Bangalore";
  document.getElementById('ship-pincode').value = "560103";
  showToast("Address pre-filled with demo coordinates!");
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
