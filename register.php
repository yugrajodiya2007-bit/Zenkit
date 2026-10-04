<?php
require_once __DIR__ . '/includes/helpers.php';

if (isLoggedIn()) {
    safeRedirect('index.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($name) || empty($email) || empty($password)) {
        $error = "Please fill in all required fields.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        $db = getDB();
        $check = $db->prepare("SELECT id FROM users WHERE email = ?");
        $check->execute([$email]);
        if ($check->fetch()) {
            $error = "An account with this email address already exists. Please log in.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'customer')");
            $stmt->execute([$name, $email, $hash]);
            $userId = $db->lastInsertId();

            $_SESSION['user_id'] = $userId;
            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $email;
            $_SESSION['user_role'] = 'customer';

            setFlash('success', "Welcome to Zenkit, $name! Your account has been created.");
            safeRedirect('index.php');
        }
    }
}

$pageTitle = "Sign Up | Zenkit";
require_once __DIR__ . '/includes/header.php';
?>

<main class="main-content" style="display: flex; align-items: center; justify-content: center; min-height: 80vh;">
  <div class="container" style="max-width: 840px;">
    
    <div class="fk-auth-card" style="box-shadow: var(--shadow-lg); border: 1px solid var(--border-light);">
      
      <!-- Left Split Banner -->
      <div class="fk-auth-left">
        <div>
          <h2 class="fk-auth-left-title">Looks like you're new here!</h2>
          <p class="fk-auth-left-desc">
            Sign up with your details to get started with fast delivery, exclusive electronics deals, and 3D device inspections.
          </p>
        </div>

        <div class="fk-auth-left-art">
          <div style="font-size: 64px;">🎁</div>
          <div style="font-size: 13px; font-weight: 700; margin-top: 8px;">Get 20% OFF coupon ZENKIT20</div>
        </div>
      </div>

      <!-- Right Form -->
      <div class="fk-auth-right">
        <div>
          <h3 style="font-size: 1.3rem; font-weight: 800; margin-bottom: 6px; color: #1E293B;">Create Account</h3>
          <p style="font-size: 0.85rem; color: #64748B; margin-bottom: 20px;">Join thousands of tech enthusiasts on Zenkit.</p>

          <?php if (!empty($error)): ?>
            <div style="background: #FEE2E2; border-left: 4px solid #EF4444; color: #DC2626; padding: 10px 14px; border-radius: 2px; font-size: 13px; font-weight: 600; margin-bottom: 16px;">
              <?= htmlspecialchars($error) ?>
            </div>
          <?php endif; ?>

          <form action="register.php" method="POST">
            <div class="form-group">
              <label class="form-label">Full Name</label>
              <input type="text" name="name" class="form-control" placeholder="Enter your full name" required value="<?= htmlspecialchars($_POST['name'] ?? 'Rahul Sharma') ?>">
            </div>

            <div class="form-group">
              <label class="form-label">Email Address</label>
              <input type="email" name="email" class="form-control" placeholder="name@example.com" required value="<?= htmlspecialchars($_POST['email'] ?? 'demo@zenkit.io') ?>">
            </div>

            <div class="form-group">
              <label class="form-label">Create Password</label>
              <input type="password" name="password" class="form-control" placeholder="Minimum 6 characters" required value="demo123">
            </div>

            <p class="fk-auth-terms">
              By creating an account, you agree to Zenkit's <a href="#">Terms of Service</a> & <a href="#">Privacy Policy</a>.
            </p>

            <button type="submit" class="btn btn-fk-buy" style="width: 100%; padding: 12px; font-size: 14px;">
              CONTINUE & REGISTER
            </button>

            <!-- 1-Click Demo Fill -->
            <div style="margin-top: 12px;">
              <button 
                type="button" 
                onclick="fillDemoUser('newuser_' + Math.floor(Math.random()*900 + 100) + '@zenkit.io', 'demo123', 'Vikram Malhotra')" 
                class="btn btn-outline" 
                style="width: 100%; font-size: 11px; padding: 8px;"
              >
                ⚡ 1-Click Fill Random Demo User
              </button>
            </div>
          </form>
        </div>

        <div class="fk-auth-footer-toggle">
          <span style="color: #878787; font-size: 13px;">Existing User? </span>
          <a href="login.php">Log in</a>
          <div style="margin-top: 8px;">
            <a href="index.php" style="color: #64748B; font-size: 12px; text-decoration: underline;">
              &larr; Back to Marketplace
            </a>
          </div>
        </div>

      </div>

    </div>

  </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
