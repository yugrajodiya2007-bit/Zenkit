<?php
$pageTitle = "Contact Us & Business Help | Zenkit";
require_once __DIR__ . '/includes/header.php';

$submitted = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_inquiry'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $subject = trim($_POST['subject'] ?? 'General Inquiry');
    $message = trim($_POST['message'] ?? '');

    if (empty($name) || empty($email) || empty($message)) {
        $error = "Please fill in all mandatory fields.";
    } else {
        $db = getDB();
        $ins = $db->prepare("INSERT INTO contact_inquiries (name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?)");
        $ins->execute([$name, $email, $phone, $subject, $message]);
        $submitted = true;
        setFlash('success', 'Thank you! Your ticket has been logged. An executive will respond within 4 business hours.');
    }
}
?>

<main class="main-content">
  <div class="container" style="max-width: 1040px;">
    
    <div style="text-align: center; margin-bottom: 30px;">
      <h1 style="font-size: 1.8rem; font-weight: 800; color: #1E293B;">Get in Touch with Zenkit</h1>
      <p style="font-size: 0.95rem; color: #64748B; margin-top: 4px;">
        Have a question about an order, warranty claim, or corporate bulk procurement? We're here 24/7.
      </p>
    </div>

    <div style="display: grid; grid-template-columns: 360px 1fr; gap: 24px; align-items: start;">
      
      <!-- Left: Office & Contact Info -->
      <div style="display: flex; flex-direction: column; gap: 16px;">
        
        <div style="background: #FFFFFF; border: 1px solid var(--border-light); border-radius: var(--radius-xs); padding: 24px; box-shadow: var(--shadow-sm);">
          <h3 style="font-size: 1.05rem; font-weight: 800; color: #1E293B; margin-bottom: 16px;">Corporate Head Office</h3>
          
          <div style="display: flex; flex-direction: column; gap: 14px; font-size: 13px; color: #475569;">
            <div style="display: flex; gap: 10px;">
              <span style="font-size: 18px;">🏢</span>
              <div>
                <strong><?= STORE_NAME ?></strong><br>
                <?= STORE_ADDRESS ?><br>
                CIN: <?= STORE_CIN ?><br>
                GSTIN: <strong><?= STORE_GSTIN ?></strong>
              </div>
            </div>

            <div style="display: flex; gap: 10px;">
              <span style="font-size: 18px;">📞</span>
              <div>
                <strong>Customer Toll-Free:</strong><br>
                <?= STORE_PHONE ?> (24 Hours)<br>
                Direct: +91 80 4561 4700
              </div>
            </div>

            <div style="display: flex; gap: 10px;">
              <span style="font-size: 18px;">✉️</span>
              <div>
                <strong>Email Support:</strong><br>
                <?= STORE_EMAIL ?><br>
                Enterprise / B2B: b2b@zenkit.io
              </div>
            </div>

            <div style="display: flex; gap: 10px;">
              <span style="font-size: 18px;">⏱️</span>
              <div>
                <strong>Operational Hours:</strong><br>
                Monday – Sunday: 24x7 Customer Care<br>
                Warehouse Dispatch: Mon – Sat (9am - 8pm)
              </div>
            </div>
          </div>
        </div>

        <!-- Quick Help Cards -->
        <div style="background: #EBF3FF; border: 1px solid #BFDBFE; border-radius: var(--radius-xs); padding: 18px;">
          <h4 style="font-size: 0.9rem; font-weight: 800; color: var(--fk-blue); margin-bottom: 6px;">Need Instant Tracking?</h4>
          <p style="font-size: 12px; color: #1E3A8A; margin-bottom: 10px;">
            Check the real-time location of your courier without waiting for email replies.
          </p>
          <a href="track-order.php" class="btn btn-fk-primary" style="font-size: 11px; padding: 6px 14px;">
            Track Order Now &rarr;
          </a>
        </div>

      </div>

      <!-- Right: Interactive Contact Form -->
      <div style="background: #FFFFFF; border: 1px solid var(--border-light); border-radius: var(--radius-xs); padding: 32px; box-shadow: var(--shadow-sm);">
        <h2 style="font-size: 1.25rem; font-weight: 800; color: #1E293B; margin-bottom: 6px;">Submit Support Request</h2>
        <p style="font-size: 0.85rem; color: #64748B; margin-bottom: 20px;">Average response time is under 15 minutes.</p>

        <?php if ($submitted): ?>
          <div style="background: #DCFCE7; border: 1px solid #86EFAC; color: #166534; padding: 18px; border-radius: 4px; margin-bottom: 20px;">
            <div style="font-weight: 800; font-size: 14px; margin-bottom: 4px;">✓ Inquiry Received Successfully</div>
            <div style="font-size: 13px;">Our senior customer satisfaction representative has been notified and will reach out via email or phone shortly.</div>
          </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
          <div style="background: #FEE2E2; border-left: 4px solid #EF4444; color: #DC2626; padding: 10px 14px; font-weight: 600; font-size: 13px; margin-bottom: 16px;">
            <?= htmlspecialchars($error) ?>
          </div>
        <?php endif; ?>

        <form action="contact.php" method="POST">
          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
            <div class="form-group">
              <label class="form-label">Full Name *</label>
              <input type="text" name="name" class="form-control" placeholder="Your name" required value="<?= htmlspecialchars($_SESSION['user_name'] ?? '') ?>">
            </div>

            <div class="form-group">
              <label class="form-label">Email Address *</label>
              <input type="email" name="email" class="form-control" placeholder="name@domain.com" required value="<?= htmlspecialchars($_SESSION['user_email'] ?? '') ?>">
            </div>
          </div>

          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
            <div class="form-group">
              <label class="form-label">Mobile Number</label>
              <input type="tel" name="phone" class="form-control" placeholder="10-digit mobile number">
            </div>

            <div class="form-group">
              <label class="form-label">Inquiry Reason *</label>
              <select name="subject" class="form-control">
                <option value="Order Tracking & Logistics">Order Tracking & Logistics</option>
                <option value="1-Year Brand Warranty Claim">1-Year Brand Warranty Claim</option>
                <option value="Return / Replacement Request">Return / Replacement Request</option>
                <option value="Corporate / Bulk Order Procurement">Corporate / Bulk Order Procurement</option>
                <option value="Payment / Invoice Inquiry">Payment / Invoice Inquiry</option>
                <option value="General Question">General Question</option>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Detailed Message *</label>
            <textarea name="message" class="form-control" rows="5" placeholder="Please describe your inquiry, order number (if any), and preferred contact time..." required></textarea>
          </div>

          <button type="submit" name="submit_inquiry" class="btn btn-fk-buy" style="padding: 12px 32px; font-size: 14px; font-weight: 800;">
            Send Inquiry Message &rarr;
          </button>
        </form>

      </div>

    </div>

  </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
