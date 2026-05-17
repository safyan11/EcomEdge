<?php
// =====================================================
// ECOMEDGE - Auto Database Installer
// Run once: http://localhost/Ecom%20edge/install.php
// =====================================================
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = 'localhost';
$user = 'root';
$pass = '';
$dbName = 'ecomedge_db';

$steps = [];
$hasError = false;

// Step 1: Connect to MySQL (without selecting DB)
$conn = new mysqli($host, $user, $pass);
if ($conn->connect_error) {
    $hasError = true;
    $steps[] = ['error', 'MySQL Connection Failed: ' . $conn->connect_error];
} else {
    $steps[] = ['success', 'MySQL connection successful ✅'];

    // Step 2: Create Database
    if ($conn->query("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci")) {
        $steps[] = ['success', "Database '$dbName' created/verified ✅"];
    } else {
        $hasError = true;
        $steps[] = ['error', 'Failed to create database: ' . $conn->error];
    }

    // Step 3: Select Database
    $conn->select_db($dbName);
    $conn->set_charset('utf8mb4');

    // Step 4: Create Tables
    $tables = [

        // USERS TABLE
        "CREATE TABLE IF NOT EXISTS `users` (
            `id`            INT AUTO_INCREMENT PRIMARY KEY,
            `name`          VARCHAR(100) NOT NULL,
            `email`         VARCHAR(150) NOT NULL UNIQUE,
            `password`      VARCHAR(255) NOT NULL,
            `employee_code` VARCHAR(20) NOT NULL,
            `role`          ENUM('admin','user') DEFAULT 'user',
            `status`        ENUM('pending','approved','rejected') DEFAULT 'pending',
            `profile_image` VARCHAR(255) DEFAULT NULL,
            `created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        // REPORTS TABLE
        "CREATE TABLE IF NOT EXISTS `reports` (
            `id`               INT AUTO_INCREMENT PRIMARY KEY,
            `user_id`          INT NOT NULL,
            `total_orders`     INT NOT NULL DEFAULT 0,
            `waste_orders`     INT NOT NULL DEFAULT 0,
            `confirmed_orders` INT NOT NULL DEFAULT 0,
            `report_date`      DATE NOT NULL,
            `notes`            TEXT DEFAULT NULL,
            `created_at`       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        // CONFIRMED ORDERS TABLE
        "CREATE TABLE IF NOT EXISTS `confirmed_orders` (
            `id`            INT AUTO_INCREMENT PRIMARY KEY,
            `order_id`      VARCHAR(50) NOT NULL,
            `courier`       VARCHAR(100) NOT NULL,
            `employee_code` VARCHAR(20) NOT NULL,
            `uploaded_by`   INT NOT NULL,
            `order_date`    DATE NOT NULL,
            `created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY `unique_order` (`order_id`, `order_date`),
            FOREIGN KEY (`uploaded_by`) REFERENCES `users`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        // CONTACT MESSAGES TABLE
        "CREATE TABLE IF NOT EXISTS `contact_messages` (
            `id`         INT AUTO_INCREMENT PRIMARY KEY,
            `name`       VARCHAR(100) NOT NULL,
            `email`      VARCHAR(150) NOT NULL,
            `message`    TEXT NOT NULL,
            `is_read`    TINYINT(1) DEFAULT 0,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        // GALLERY TABLE
        "CREATE TABLE IF NOT EXISTS `gallery` (
            `id`         INT AUTO_INCREMENT PRIMARY KEY,
            `image_path` VARCHAR(255) NOT NULL,
            `caption`    VARCHAR(255) DEFAULT NULL,
            `category`   VARCHAR(50) DEFAULT 'general',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    ];

    $tableNames = ['users', 'reports', 'confirmed_orders', 'contact_messages', 'gallery'];

    foreach ($tables as $i => $sql) {
        if ($conn->query($sql)) {
            $steps[] = ['success', "Table '{$tableNames[$i]}' created ✅"];
        } else {
            $hasError = true;
            $steps[] = ['error', "Table '{$tableNames[$i]}' failed: " . $conn->error];
        }
    }

    // Step 5: Insert Default Admin (only if not exists)
    $check = $conn->query("SELECT id FROM users WHERE email='admin@ecomedge.com'")->num_rows;
    if ($check === 0) {
        $hashedPass = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, employee_code, role, status) VALUES (?, ?, ?, ?, 'admin', 'approved')");
        $adminName = 'Admin';
        $adminEmail = 'admin@ecomedge.com';
        $adminCode = 'EE-01';
        $stmt->bind_param('ssss', $adminName, $adminEmail, $hashedPass, $adminCode);
        if ($stmt->execute()) {
            $steps[] = ['success', 'Default Admin account created ✅'];
        } else {
            $hasError = true;
            $steps[] = ['error', 'Admin insert failed: ' . $conn->error];
        }
    } else {
        $steps[] = ['info', 'Admin account already exists — skipped ⚡'];
    }

    // Step 6: Insert Sample Employee Users (for demo)
    $sampleUsers = [
        ['Hasan Ali',    'hasan@ecomedge.com',   'EE-02'],
        ['Ali Raza',     'ali@ecomedge.com',     'EE-03'],
        ['Sara Khan',    'sara@ecomedge.com',    'EE-10'],
    ];
    $samplePass = password_hash('user123', PASSWORD_DEFAULT);
    $insertedSamples = 0;
    foreach ($sampleUsers as $u) {
        $chk = $conn->query("SELECT id FROM users WHERE email='{$u[1]}'")->num_rows;
        if ($chk === 0) {
            $stmt = $conn->prepare("INSERT INTO users (name, email, password, employee_code, role, status) VALUES (?, ?, ?, ?, 'user', 'approved')");
            $stmt->bind_param('ssss', $u[0], $u[1], $samplePass, $u[2]);
            if ($stmt->execute()) $insertedSamples++;
        }
    }
    if ($insertedSamples > 0)
        $steps[] = ['success', "$insertedSamples sample employee account(s) created ✅"];
    else
        $steps[] = ['info', 'Sample employees already exist — skipped ⚡'];

    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ecomedge Installer</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            background: linear-gradient(135deg, #0d1b4b 0%, #1a3a7a 50%, #1a6bff 100%);
            display: flex; align-items: center; justify-content: center;
            padding: 24px;
        }
        .installer-box {
            background: white;
            border-radius: 20px;
            padding: 48px;
            max-width: 600px;
            width: 100%;
            box-shadow: 0 32px 80px rgba(0,0,0,0.3);
        }
        .logo {
            display: flex; align-items: center; gap: 12px;
            margin-bottom: 32px;
        }
        .logo-icon {
            width: 48px; height: 48px;
            background: linear-gradient(135deg, #1a6bff, #00c6ff);
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-weight: 900; font-size: 22px; color: white;
        }
        .logo-text { font-size: 26px; font-weight: 800; color: #0d1b4b; }
        h1 { font-size: 22px; font-weight: 800; color: #0f172a; margin-bottom: 6px; }
        .subtitle { font-size: 14px; color: #64748b; margin-bottom: 28px; }
        .step {
            display: flex; align-items: flex-start; gap: 12px;
            padding: 12px 14px; border-radius: 8px; margin-bottom: 8px;
            font-size: 14px; font-weight: 600;
        }
        .step.success { background: #d1fae5; color: #065f46; }
        .step.error   { background: #fee2e2; color: #991b1b; }
        .step.info    { background: #eff6ff; color: #1e40af; }
        .step-icon { font-size: 18px; flex-shrink: 0; margin-top: 1px; }
        .result-box {
            margin-top: 28px;
            border-radius: 14px;
            padding: 24px;
            text-align: center;
        }
        .result-box.done   { background: linear-gradient(135deg,#d1fae5,#a7f3d0); border: 1px solid #6ee7b7; }
        .result-box.failed { background: #fee2e2; border: 1px solid #fca5a5; }
        .result-box h2 { font-size: 20px; font-weight: 800; margin-bottom: 8px; }
        .result-box p  { font-size: 14px; margin-bottom: 16px; line-height: 1.6; }
        .cred-table { width:100%; border-collapse:collapse; margin: 14px 0; text-align:left; }
        .cred-table td { padding: 8px 12px; border-bottom: 1px solid rgba(0,0,0,0.06); font-size: 13px; }
        .cred-table td:first-child { color:#64748b; font-weight:600; width:140px; }
        .cred-table td:last-child  { font-weight:700; color:#0f172a; font-family:monospace; }
        .btn-group { display:flex; gap:10px; justify-content:center; flex-wrap:wrap; margin-top:16px; }
        .btn {
            display:inline-flex; align-items:center; gap:6px;
            padding:11px 22px; border-radius:8px; font-weight:700;
            font-size:14px; text-decoration:none; transition: all 0.2s;
        }
        .btn-primary { background:linear-gradient(135deg,#1a6bff,#0047cc); color:white; }
        .btn-primary:hover { transform:translateY(-2px); box-shadow:0 6px 20px rgba(26,107,255,0.4); }
        .btn-outline { border:2px solid #1a6bff; color:#1a6bff; background:transparent; }
        .btn-outline:hover { background:#1a6bff; color:white; }
        .warning-note {
            background:#fef3c7; border:1px solid #fcd34d; border-radius:10px;
            padding:14px 16px; font-size:13px; color:#92400e; margin-top:16px; line-height:1.5;
        }
    </style>
</head>
<body>
<div class="installer-box">
    <div class="logo">
        <div class="logo-icon">E</div>
        <span class="logo-text">Ecomedge</span>
    </div>
    <h1>🛠️ Auto Installer</h1>
    <p class="subtitle">Setting up your database automatically...</p>

    <!-- STEPS LOG -->
    <div>
        <?php foreach ($steps as $step): ?>
        <div class="step <?= $step[0] ?>">
            <span class="step-icon">
                <?= $step[0]==='success' ? '✅' : ($step[0]==='error' ? '❌' : 'ℹ️') ?>
            </span>
            <?= htmlspecialchars($step[1]) ?>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- RESULT -->
    <?php if (!$hasError): ?>
    <div class="result-box done">
        <h2 style="color:#065f46">🎉 Installation Complete!</h2>
        <p style="color:#047857">Database <strong>ecomedge_db</strong> is ready with all tables. You can now login to the system.</p>

        <table class="cred-table">
            <tr><td>👑 Admin Email</td><td>admin@ecomedge.com</td></tr>
            <tr><td>🔐 Admin Password</td><td>admin123</td></tr>
            <tr><td>👤 User Email</td><td>hasan@ecomedge.com</td></tr>
            <tr><td>🔐 User Password</td><td>user123</td></tr>
        </table>

        <div class="btn-group">
            <a href="login.php" class="btn btn-primary">🔐 Go to Login</a>
            <a href="index.php" class="btn btn-outline">🌐 Public Website</a>
        </div>

        <div class="warning-note">
            ⚠️ <strong>Security:</strong> After logging in, please delete <code>install.php</code> or rename it. یہ file صرف ایک بار چلائیں!
        </div>
    </div>
    <?php else: ?>
    <div class="result-box failed">
        <h2 style="color:#991b1b">❌ Installation Failed</h2>
        <p style="color:#b91c1c">کچھ errors آئی ہیں۔ اوپر دیکھیں اور XAMPP میں MySQL چل رہا ہے یہ confirm کریں۔</p>
        <a href="install.php" class="btn btn-primary" style="margin-top:8px">🔄 Try Again</a>
    </div>
    <?php endif; ?>
</div>
</body>
</html>
