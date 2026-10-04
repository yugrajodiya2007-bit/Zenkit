<?php
$pageTitle = "About Us | Zenkit Official Corporate Story";
require_once __DIR__ . '/includes/header.php';
?>

<main class="main-content">
  <div class="container" style="max-width: 1040px;">
    
    <!-- Hero Banner -->
    <div style="background: linear-gradient(135deg, #1E293B, #0F172A); color: #FFFFFF; border-radius: var(--radius-xs); padding: 50px 40px; text-align: center; margin-bottom: 24px; box-shadow: var(--shadow-md);">
      <div style="font-size: 12px; font-weight: 800; color: var(--fk-yellow); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px;">
        Pioneering Premium Consumer Tech
      </div>
      <h1 style="font-size: 2.2rem; font-weight: 900; letter-spacing: -0.5px; margin-bottom: 12px;">
        The Zenkit Story
      </h1>
      <p style="font-size: 1.05rem; color: #94A3B8; max-width: 680px; margin: 0 auto; line-height: 1.6;">
        Designed for creators, engineers, and everyday tech enthusiasts. We engineer high-performance laptops, acoustic audio gear, and ergonomic desk accessories without the exorbitant markups.
      </p>
    </div>

    <!-- Pillars Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-bottom: 30px;">
      
      <div style="background: #FFFFFF; border: 1px solid var(--border-light); border-radius: var(--radius-xs); padding: 24px; box-shadow: var(--shadow-sm);">
        <div style="font-size: 32px; margin-bottom: 10px;">🔬</div>
        <h3 style="font-size: 1.1rem; font-weight: 800; color: #1E293B; margin-bottom: 6px;">Rigorous 42-Point QC</h3>
        <p style="font-size: 13px; color: #64748B; line-height: 1.6;">
          Every Zenkit device undergoes stress testing, thermal diagnostics, and battery cycle evaluations before leaving our Bengaluru and Hyderabad fulfillment hubs.
        </p>
      </div>

      <div style="background: #FFFFFF; border: 1px solid var(--border-light); border-radius: var(--radius-xs); padding: 24px; box-shadow: var(--shadow-sm);">
        <div style="font-size: 32px; margin-bottom: 10px;">🛡️</div>
        <h3 style="font-size: 1.1rem; font-weight: 800; color: #1E293B; margin-bottom: 6px;">1-Year Replacement Warranty</h3>
        <p style="font-size: 13px; color: #64748B; line-height: 1.6;">
          Zero questions asked door-step replacement for any manufacturing defect. We partner with India’s leading courier networks for same-day reverse pickups.
        </p>
      </div>

      <div style="background: #FFFFFF; border: 1px solid var(--border-light); border-radius: var(--radius-xs); padding: 24px; box-shadow: var(--shadow-sm);">
        <div style="font-size: 32px; margin-bottom: 10px;">⚡</div>
        <h3 style="font-size: 1.1rem; font-weight: 800; color: #1E293B; margin-bottom: 6px;">Direct-to-Consumer Value</h3>
        <p style="font-size: 13px; color: #64748B; line-height: 1.6;">
          By eliminating distributor intermediaries and exorbitant retail shelf rents, we pass savings of up to 40% directly to you.
        </p>
      </div>

    </div>

    <!-- Corporate Information -->
    <div style="background: #FFFFFF; border: 1px solid var(--border-light); border-radius: var(--radius-xs); padding: 32px; box-shadow: var(--shadow-sm); line-height: 1.7; color: #475569;">
      <h2 style="font-size: 1.3rem; font-weight: 800; color: #1E293B; margin-bottom: 12px;">Corporate Identity & Compliance</h2>
      <p style="margin-bottom: 16px;">
        <strong><?= STORE_NAME ?></strong> is a registered corporate entity under the Ministry of Corporate Affairs, Government of India. Operating in compliance with the Information Technology (Intermediary Guidelines and Digital Media Ethics Code) Rules and the Consumer Protection (E-Commerce) Rules.
      </p>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; background: #F8FAFC; border: 1px solid var(--border-subtle); padding: 18px; border-radius: 4px; font-size: 13px;">
        <div>
          <div>Corporate Identification (CIN): <strong><?= STORE_CIN ?></strong></div>
          <div>Goods & Services Tax (GSTIN): <strong><?= STORE_GSTIN ?></strong></div>
        </div>
        <div>
          <div>Auditor: M/s S.K. Kapoor & Associates, Chartered Accountants</div>
          <div>Principal Bank: HDFC Bank Ltd. / ICICI Bank Ltd.</div>
        </div>
      </div>
    </div>

  </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
