<?php
require_once 'config.php';
if (isLoggedIn()) { header('Location: dashboard/index.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';

    if ($email && $pass) {
        $db = getDB();
        $stmt = $db->prepare("SELECT id, name, password, role, status, employee_code, profile_image FROM users WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($user = $result->fetch_assoc()) {
            if ($user['status'] === 'pending') {
                $error = 'pending';
            } elseif ($user['status'] === 'rejected') {
                $error = 'Your account has been rejected. Contact admin.';
            } elseif (!password_verify($pass, $user['password'])) {
                $error = 'Incorrect password. Please try again.';
            } else {
                $_SESSION['user_id']       = $user['id'];
                $_SESSION['user_name']     = $user['name'];
                $_SESSION['role']          = $user['role'];
                $_SESSION['employee_code'] = $user['employee_code'];
                $_SESSION['user_image']    = $user['profile_image'];
                header('Location: dashboard/index.php');
                exit;
            }
        } else {
            $error = 'No account found with this email.';
        }
        $db->close();
    } else {
        $error = 'Please enter your email and password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Ecomedge</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/auth.css">
</head>
<body class="auth-page">
<div class="auth-container">
    <div class="auth-left">
        <div class="auth-brand">
            <a href="index.php" class="nav-logo">
                <div class="logo-icon">E</div>
                <span class="logo-text" style="-webkit-text-fill-color:white">Ecomedge</span>
            </a>
        </div>
        <div class="auth-left-content">
            <h2>Welcome Back!</h2>
            <p>Login to your Ecomedge account and manage your daily confirmed orders, reports, and performance analytics.</p>
            <ul class="auth-features">
                <li>📊 View your daily performance stats</li>
                <li>📦 Upload confirmed orders instantly</li>
                <li>📈 Access date-wise reports</li>
                <li>🔍 Detect duplicate orders automatically</li>
            </ul>
        </div>
        <div style="position:relative;z-index:1;margin-top:auto;">
            <p style="color:rgba(255,255,255,0.4);font-size:13px;">© 2024 Ecomedge. All rights reserved.</p>
        </div>
    </div>
    <div class="auth-right">
        <div class="auth-form-wrap">
            <h1>Login to Ecomedge</h1>
            <p class="auth-sub">Enter your credentials to access your dashboard</p>

            <?php if ($error === 'pending'): ?>
            <div class="pending-box">
                <div class="pending-icon">⏳</div>
                <h3>Account Pending Approval</h3>
                <p>Your account is waiting for admin approval. You will be able to login once approved.</p>
            </div>
            <?php elseif ($error): ?>
                <div class="alert alert-error">⚠️ <?= $error ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" placeholder="your@email.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required autofocus>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <div class="input-icon-wrap">
                        <input type="password" name="password" id="password" placeholder="Enter your password" required>
                        <button type="button" class="eye-btn" onclick="togglePass()">👁</button>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-lg" style="width:100%;justify-content:center;margin-top:8px;">
                    🔐 Login to Dashboard
                </button>
            </form>

            <p class="auth-switch">Don't have an account? <a href="signup.php">Request Access →</a></p>
            <p class="auth-switch" style="margin-top:8px;"><a href="index.php">← Back to Website</a></p>
        </div>
    </div>
</div>
<script>
function togglePass() {
    const p = document.getElementById('password');
    p.type = p.type === 'password' ? 'text' : 'password';
}
</script>
</body>
</html>
