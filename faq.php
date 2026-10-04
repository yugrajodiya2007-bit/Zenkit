<?php
$pageTitle = "Frequently Asked Questions (FAQ) | Zenkit Help Center";
require_once __DIR__ . '/includes/header.php';
?>

<main class="main-content">
  <div class="container" style="max-width: 880px;">
    
    <div style="text-align: center; margin-bottom: 28px;">
      <h1 style="font-size: 1.8rem; font-weight: 800; color: #1E293B;">Frequently Asked Questions</h1>
      <p style="font-size: 0.95rem; color: #64748B; margin-top: 4px;">Quick answers to common questions about orders, payments, shipping and warranty.</p>
    </div>

    <!-- Category Questions -->
    <div style="display: flex; flex-direction: column; gap: 16px;">
      
      <!-- FAQ 1 -->
      <details style="background: #FFFFFF; border: 1px solid var(--border-light); border-radius: 4px; padding: 18px 24px; box-shadow: var(--shadow-sm); cursor: pointer;" open>
        <summary style="font-weight: 700; font-size: 15px; color: #1E293B;">How fast does Zenkit ship orders and what are delivery charges?</summary>
        <div style="margin-top: 12px; font-size: 13px; color: #475569; line-height: 1.6;">
          All flagship orders placed before 3:00 PM are dispatched on the same business day. We offer <strong>FREE Delivery</strong> on all orders above $200 (₹16,700). For smaller orders, a nominal express courier fee of $15.00 applies. Most metropolitan areas receive delivery within 24 to 48 hours.
        </div>
      </details>

      <!-- FAQ 2 -->
      <details style="background: #FFFFFF; border: 1px solid var(--border-light); border-radius: 4px; padding: 18px 24px; box-shadow: var(--shadow-sm); cursor: pointer;">
        <summary style="font-weight: 700; font-size: 15px; color: #1E293B;">What payment methods are supported and can I get a GST Tax Invoice?</summary>
        <div style="margin-top: 12px; font-size: 13px; color: #475569; line-height: 1.6;">
          Yes! We support all major UPI apps (Google Pay, PhonePe, Paytm, BHIM), Credit/Debit Cards (Visa, MasterCard, RuPay, Amex), Net Banking, and Cash on Delivery (COD). Every order generates an official GST-compliant tax invoice with HSN classification codes, downloadable immediately from your order confirmation screen or tracking portal.
        </div>
      </details>

      <!-- FAQ 3 -->
      <details style="background: #FFFFFF; border: 1px solid var(--border-light); border-radius: 4px; padding: 18px 24px; box-shadow: var(--shadow-sm); cursor: pointer;">
        <summary style="font-weight: 700; font-size: 15px; color: #1E293B;">What is the Zenkit 7-Day Replacement Policy?</summary>
        <div style="margin-top: 12px; font-size: 13px; color: #475569; line-height: 1.6;">
          If your device arrives damaged, defective, or differs from the specifications shown online, you can initiate a 7-day replacement from your customer account or by calling our toll-free support helpline at <code>1800 208 9898</code>. We arrange a doorstep courier pickup with zero return transit fees.
        </div>
      </details>

      <!-- FAQ 4 -->
      <details style="background: #FFFFFF; border: 1px solid var(--border-light); border-radius: 4px; padding: 18px 24px; box-shadow: var(--shadow-sm); cursor: pointer;">
        <summary style="font-weight: 700; font-size: 15px; color: #1E293B;">How does the 1-Year Manufacturer Warranty work?</summary>
        <div style="margin-top: 12px; font-size: 13px; color: #475569; line-height: 1.6;">
          All Zenkit laptops, AMOLED smartwatches, mechanical keyboards, and audio products come with 1 Year Comprehensive Brand Warranty. Your warranty is automatically activated based on the serial number listed on your digital tax invoice.
        </div>
      </details>

      <!-- FAQ 5 -->
      <details style="background: #FFFFFF; border: 1px solid var(--border-light); border-radius: 4px; padding: 18px 24px; box-shadow: var(--shadow-sm); cursor: pointer;">
        <summary style="font-weight: 700; font-size: 15px; color: #1E293B;">Do you offer corporate or bulk discounts for IT companies?</summary>
        <div style="margin-top: 12px; font-size: 13px; color: #475569; line-height: 1.6;">
          Yes. Our B2B Corporate Procurement desk offers tiered pricing on orders of 10+ laptops or accessory kits, with custom asset tagging, GST input tax credits, and consolidated billing. Reach out to our B2B team at <code>b2b@zenkit.io</code> or via our <a href="contact.php" style="color: var(--fk-blue); font-weight: 700;">Contact Us</a> page.
        </div>
      </details>

    </div>

    <!-- Need More Help Banner -->
    <div style="background: #FFFFFF; border: 1px solid var(--border-light); border-radius: 4px; padding: 24px; text-align: center; margin-top: 30px;">
      <h3 style="font-size: 1.1rem; font-weight: 800; color: #1E293B;">Still have questions?</h3>
      <p style="font-size: 13px; color: #64748B; margin: 4px 0 16px 0;">Our technical support engineers are available round the clock.</p>
      <div style="display: flex; gap: 12px; justify-content: center;">
        <a href="contact.php" class="btn btn-fk-primary" style="font-size: 13px;">Contact Us</a>
        <a href="track-order.php" class="btn btn-outline" style="font-size: 13px;">Track a Shipment</a>
      </div>
    </div>

  </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
