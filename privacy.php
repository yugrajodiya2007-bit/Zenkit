<?php
$pageTitle = "Privacy Policy | Zenkit Compliance";
require_once __DIR__ . '/includes/header.php';
?>

<main class="main-content">
  <div class="container" style="max-width: 860px;">
    
    <div style="background: #FFFFFF; border: 1px solid var(--border-light); border-radius: var(--radius-xs); padding: 36px; box-shadow: var(--shadow-sm); line-height: 1.8; color: #334155;">
      <h1 style="font-size: 1.8rem; font-weight: 800; color: #1E293B; margin-bottom: 8px;">Zenkit Privacy Policy</h1>
      <div style="font-size: 12px; color: #64748B; margin-bottom: 24px;">Last Updated: October 2026 • Effective for all Zenkit Storefront Users</div>

      <h2 style="font-size: 1.15rem; font-weight: 800; color: #1E293B; margin-top: 20px;">1. Information We Collect</h2>
      <p>
        When you purchase or register on Zenkit, we collect your name, email address, phone number, shipping address, and order transaction history. We do not store sensitive payment card details or net-banking credentials on our servers; all payment transactions are tokenized through RBI and PCI-DSS certified payment processors.
      </p>

      <h2 style="font-size: 1.15rem; font-weight: 800; color: #1E293B; margin-top: 20px;">2. How We Use Your Data</h2>
      <p>
        We use customer data strictly to fulfill order delivery, generate statutory tax invoices, communicate dispatch tracking milestones, and service 1-year brand warranty requests. We do not sell or rent customer data to third-party marketing brokers.
      </p>

      <h2 style="font-size: 1.15rem; font-weight: 800; color: #1E293B; margin-top: 20px;">3. Data Protection & Security</h2>
      <p>
        Our servers utilize SSL/TLS encryption for all communications. Passwords are cryptographically hashed using standard Bcrypt algorithms before storage in the SQL database.
      </p>

      <h2 style="font-size: 1.15rem; font-weight: 800; color: #1E293B; margin-top: 20px;">4. Grievance Officer</h2>
      <p>
        In accordance with the Information Technology Act 2000 and consumer rules, the contact details of the Grievance Officer are:<br>
        <strong>Grievance Officer:</strong> Mr. Vikramaditya Rao<br>
        <strong>Address:</strong> <?= STORE_NAME ?>, <?= STORE_ADDRESS ?><br>
        <strong>Email:</strong> grievance@zenkit.io | <strong>Phone:</strong> <?= STORE_PHONE ?>
      </p>
    </div>

  </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
