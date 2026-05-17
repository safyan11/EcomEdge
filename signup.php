<?php
require_once 'config.php';
if (isLoggedIn()) { header('Location: dashboard/index.php'); exit; }

$error = $success = '';
$validCodes = ['EE-ADMIN','EE-01','EE-02','EE-03','EE-04','EE-05','EE-06','EE-07','EE-08','EE-09','EE-10'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';
    $code  = trim($_POST['employee_code'] ?? '');

    if (!$name || !$email || !$pass || !$code) {
        $error = 'Please fill in all fields.';
    } elseif (!in_array($code, $validCodes)) {
        $error = 'Invalid Employee Code. Contact your admin.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($pass) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        $db = getDB();
        $chk = $db->prepare("SELECT id FROM users WHERE email = ?");
        $chk->bind_param('s', $email);
        $chk->execute();
        if ($chk->get_result()->num_rows > 0) {
            $error = 'This email is already registered.';
        } else {
            $hashed = password_hash($pass, PASSWORD_DEFAULT);
            $role = ($code === 'EE-ADMIN') ? 'admin' : 'user';
            
            $stmt = $db->prepare("INSERT INTO users (name, email, password, employee_code, role, status) VALUES (?, ?, ?, ?, ?, 'pending')");
            $stmt->bind_param('sssss', $name, $email, $hashed, $code, $role);
            
            if ($stmt->execute()) {
                $success = 'Account created! Please wait for an existing admin to approve your account before logging in.';
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
        $db->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Ecomedge</title>
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
            <h2>Join Our Team</h2>
            <p>Request access to the Ecomedge internal management system. Admin will review and approve your account.</p>
            <ul class="auth-features">
                <li>📦 Upload daily confirmed orders</li>
                <li>📊 Track your performance stats</li>
                <li>🚚 Courier-wise analysis</li>
                <li>📈 View personal reports</li>
            </ul>
        </div>
        <div class="auth-steps">
            <div class="step active"><span>1</span> Fill Form</div>
            <div class="step-line"></div>
            <div class="step"><span>2</span> Wait Approval</div>
            <div class="step-line"></div>
            <div class="step"><span>3</span> Login</div>
        </div>
    </div>
    <div class="auth-right">
        <div class="auth-form-wrap">
            <h1>Create Account</h1>
            <p class="auth-sub">Fill in your details to request system access</p>

            <?php if ($error): ?>
                <div class="alert alert-error">⚠️ <?= $error ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success">✅ <?= $success ?> <a href="login.php">Go to Login →</a></div>
            <?php endif; ?>

            <?php if (!$success): ?>
            <form method="POST" action="">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="name" placeholder="Enter your full name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" placeholder="your@email.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>Employee Code</label>
                    <select name="employee_code" required>
                        <option value="">-- Select Your Code --</option>
                        <option value="EE-ADMIN" <?= ($_POST['employee_code'] ?? '') === 'EE-ADMIN' ? 'selected' : '' ?> style="font-weight:bold; color:#1a6bff;">EE-ADMIN (Admin Access)</option>
                        <hr>
                        <?php for($i=1; $i<=10; $i++): $c = "EE-" . str_pad($i, 2, '0', STR_PAD_LEFT); ?>
                        <option value="<?= $c ?>" <?= ($_POST['employee_code'] ?? '') === $c ? 'selected' : '' ?>><?= $c ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <div class="input-icon-wrap">
                        <input type="password" name="password" id="password" placeholder="Min. 6 characters" required>
                        <button type="button" class="eye-btn" onclick="togglePass()">👁</button>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-lg" style="width:100%;justify-content:center;margin-top:8px;">
                    🚀 Request Access
                </button>
            </form>
            <?php endif; ?>

            <p class="auth-switch">Already have an account? <a href="login.php">Login here →</a></p>
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
