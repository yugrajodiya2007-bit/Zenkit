<?php
require_once __DIR__ . '/includes/helpers.php';

if (isLoggedIn()) {
    safeRedirect('index.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        $error = "Please enter both email and password.";
    } else {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];

            setFlash('success', "Welcome back, {$user['name']}!");
            if ($user['role'] === 'admin') {
                safeRedirect('admin/index.php');
            } else {
                safeRedirect('index.php');
            }
        } else {
            $error = "Invalid credentials. Please check your email or password.";
        }
    }
}

$pageTitle = "Login | Zenkit";
require_once __DIR__ . '/includes/header.php';
?>

<main class="main-content" style="display: flex; align-items: center; justify-content: center; min-height: 80vh;">
  <div class="container" style="max-width: 840px;">
    
    <div class="fk-auth-card" style="box-shadow: var(--shadow-lg); border: 1px solid var(--border-light);">
      
      <!-- Left Split Banner -->
      <div class="fk-auth-left">
        <div>
          <h2 class="fk-auth-left-title">Login</h2>
          <p class="fk-auth-left-desc">
            Get access to your Orders, Wishlist, Saved Addresses and personalized tech recommendations.
          </p>
        </div>

        <div class="fk-auth-left-art">
          <div style="font-size: 64px;">🔐</div>
          <div style="font-size: 13px; font-weight: 700; margin-top: 8px;">Zenkit Safe & Secure Auth</div>
        </div>
      </div>

      <!-- Right Form -->
      <div class="fk-auth-right">
        <div>
          <h3 style="font-size: 1.3rem; font-weight: 800; margin-bottom: 6px; color: #1E293B;">Account Sign In</h3>
          <p style="font-size: 0.85rem; color: #64748B; margin-bottom: 20px;">Use demo credentials below for instant 1-click access.</p>

          <?php if (!empty($error)): ?>
            <div style="background: #FEE2E2; border-left: 4px solid #EF4444; color: #DC2626; padding: 10px 14px; border-radius: 2px; font-size: 13px; font-weight: 600; margin-bottom: 16px;">
              <?= htmlspecialchars($error) ?>
            </div>
          <?php endif; ?>

          <form action="login.php" method="POST">
            <div class="form-group">
              <label class="form-label">Email Address</label>
              <input type="email" name="email" class="form-control" placeholder="name@example.com" required value="demo@zenkit.io">
            </div>

            <div class="form-group">
              <label class="form-label">Password</label>
              <input type="password" name="password" class="form-control" placeholder="Enter password" required value="demo123">
            </div>

            <p class="fk-auth-terms">
              By logging in, you agree to Zenkit's <a href="#">Terms of Service</a> & <a href="#">Privacy Policy</a>.
            </p>

            <button type="submit" class="btn btn-fk-buy" style="width: 100%; padding: 12px; font-size: 14px;">
              LOG IN
            </button>

            <!-- 1-Click Fast Fill Buttons -->
            <div style="margin-top: 14px; display: flex; gap: 8px;">
              <button 
                type="button" 
                onclick="fillDemoUser('demo@zenkit.io', 'demo123')" 
                class="btn btn-outline" 
                style="flex: 1; font-size: 11px; padding: 8px;"
              >
                ⚡ 1-Click Customer (demo@zenkit.io)
              </button>
              <button 
                type="button" 
                onclick="fillDemoUser('admin@zenkit.io', 'admin123')" 
                class="btn btn-outline" 
                style="flex: 1; font-size: 11px; padding: 8px;"
              >
                ⚡ 1-Click Admin (admin@zenkit.io)
              </button>
            </div>
          </form>
        </div>

        <div class="fk-auth-footer-toggle">
          <span style="color: #878787; font-size: 13px;">New to Zenkit? </span>
          <a href="register.php">Create an account</a>
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
