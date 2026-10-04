<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../includes/helpers.php';

if (isAdmin()) {
    header("Location: index.php");
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM users WHERE email = ? AND role = 'admin'");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = 'admin';

        setFlash('success', "Welcome back, Admin!");
        header("Location: index.php");
        exit;
    } else {
        $error = "Invalid administrator credentials or unauthorized role.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Portal Sign In | Zenkit</title>
  <link rel="icon" type="image/svg+xml" href="../assets/images/brand/zenkit-icon.svg">
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body style="background: #0F172A; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px;">

  <div style="width: 100%; max-width: 440px; background: #1E293B; border: 1px solid #334155; border-radius: 8px; padding: 32px; box-shadow: 0 20px 40px rgba(0,0,0,0.4); color: #FFFFFF;">
    
    <div style="text-align: center; margin-bottom: 24px;">
      <div style="font-size: 32px; margin-bottom: 8px;">⚙️</div>
      <h1 style="font-size: 1.5rem; font-weight: 800; color: #FFFFFF; letter-spacing: -0.5px;">Zenkit Admin Portal</h1>
      <p style="font-size: 12px; color: #94A3B8; margin-top: 4px;">Authorized Personnel & Management Login</p>
    </div>

    <?php if (!empty($error)): ?>
      <div style="background: #7F1D1D; border-left: 4px solid #EF4444; color: #FCA5A5; padding: 10px 14px; border-radius: 4px; font-size: 12px; font-weight: 600; margin-bottom: 16px;">
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <form action="login.php" method="POST">
      <div class="form-group">
        <label class="form-label" style="color: #CBD5E1;">Admin Email</label>
        <input type="email" id="admin-email" name="email" class="form-control" style="background: #0F172A; border-color: #334155; color: #FFFFFF;" value="admin@zenkit.io" required>
      </div>

      <div class="form-group">
        <label class="form-label" style="color: #CBD5E1;">Password</label>
        <input type="password" id="admin-password" name="password" class="form-control" style="background: #0F172A; border-color: #334155; color: #FFFFFF;" value="admin123" required>
      </div>

      <button type="submit" class="btn btn-fk-buy" style="width: 100%; padding: 12px; font-size: 14px; font-weight: 800; margin-top: 10px;">
        ENTER ADMIN DASHBOARD &rarr;
      </button>

      <button 
        type="button" 
        onclick="document.getElementById('admin-email').value='admin@zenkit.io'; document.getElementById('admin-password').value='admin123';" 
        class="btn btn-outline" 
        style="width: 100%; margin-top: 10px; font-size: 11px; padding: 8px; color: #38BDF8; border-color: #334155;"
      >
        ⚡ Auto-Fill Credentials (admin@zenkit.io / admin123)
      </button>
    </form>

    <div style="text-align: center; margin-top: 24px; border-top: 1px solid #334155; padding-top: 16px;">
      <a href="../index.php" style="color: #94A3B8; font-size: 12px; text-decoration: underline;">
        &larr; Return to Customer Marketplace
      </a>
    </div>

  </div>

</body>
</html>
