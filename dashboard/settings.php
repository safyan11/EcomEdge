<?php
// dashboard/settings.php - Admin Settings
require_once '../config.php';
requireAdmin();

$db  = getDB();
$uid = $_SESSION['user_id'];
$msg = $err = '';
$admin = $db->query("SELECT * FROM users WHERE id=$uid")->fetch_assoc();
$whatsappRes = $db->query("SELECT setting_value FROM system_settings WHERE setting_key='whatsapp_number'");
$whatsappNum = ($whatsappRes && $whatsappRes->num_rows > 0) ? $whatsappRes->fetch_assoc()['setting_value'] : '923001234567';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $currentPass = $_POST['current_password'] ?? '';
    $newPass     = $_POST['new_password'] ?? '';

    if ($name && $name !== $admin['name']) {
        $stmt = $db->prepare("UPDATE users SET name=? WHERE id=?");
        $stmt->bind_param('si', $name, $uid);
        $stmt->execute();
        $_SESSION['user_name'] = $name;
        $admin['name'] = $name;
        $msg = 'Admin name updated!';
    }

    if ($currentPass && $newPass) {
        if (!password_verify($currentPass, $admin['password'])) {
            $err = 'Current password is incorrect.';
        } elseif (strlen($newPass) < 6) {
            $err = 'New password must be at least 6 characters.';
        } else {
            $hashed = password_hash($newPass, PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE users SET password=? WHERE id=?");
            $stmt->bind_param('si', $hashed, $uid);
            $stmt->execute();
            $msg = 'Password changed successfully!';
        }
    }

    if (isset($_POST['whatsapp_number'])) {
        $wnum = preg_replace('/[^0-9]/', '', $_POST['whatsapp_number']);
        $stmt = $db->prepare("INSERT INTO system_settings (setting_key, setting_value) VALUES ('whatsapp_number', ?) ON DUPLICATE KEY UPDATE setting_value=?");
        $stmt->bind_param('ss', $wnum, $wnum);
        $stmt->execute();
        $whatsappNum = $wnum;
        $msg = 'WhatsApp number updated successfully!';
    }
}

// System stats
$totalUsers    = $db->query("SELECT COUNT(*) as c FROM users WHERE role='user'")->fetch_assoc()['c'];
$totalReports  = $db->query("SELECT COUNT(*) as c FROM reports")->fetch_assoc()['c'];
$totalOrders   = $db->query("SELECT COUNT(*) as c FROM confirmed_orders")->fetch_assoc()['c'];
$totalMessages = $db->query("SELECT COUNT(*) as c FROM contact_messages")->fetch_assoc()['c'];

// Handle System Reset
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_data'])) {
    $db->query("DELETE FROM confirmed_orders");
    $db->query("DELETE FROM reports");
    $db->query("ALTER TABLE confirmed_orders AUTO_INCREMENT = 1");
    $db->query("ALTER TABLE reports AUTO_INCREMENT = 1");
    $msg = 'All employee reports and order history have been cleared!';
    // Refresh stats
    $totalReports = 0;
    $totalOrders  = 0;
}
$db->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Ecomedge</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body class="dash-body">
<?php include 'includes/sidebar.php'; ?>
<div class="main-content">
    <div class="topbar">
        <div class="topbar-left"><h2>⚙️ Settings</h2><p>System configuration and admin account</p></div>
    </div>
    <div class="page-body">

        <?php if ($msg): ?>
        <div style="background:#d1fae5;border:1px solid #6ee7b7;border-radius:10px;padding:12px 20px;margin-bottom:20px;color:#065f46;font-size:14px;font-weight:600">✅ <?= $msg ?></div>
        <?php endif; ?>
        <?php if ($err): ?>
        <div style="background:#fee2e2;border:1px solid #fca5a5;border-radius:10px;padding:12px 20px;margin-bottom:20px;color:#991b1b;font-size:14px;font-weight:600">⚠️ <?= $err ?></div>
        <?php endif; ?>

        <div class="grid-2">
            <!-- Admin Account -->
            <div class="dash-panel">
                <div class="panel-header"><h3>👤 Admin Account</h3></div>
                <div class="panel-body">
                    <form method="POST">
                        <div class="dash-form-group">
                            <label>Admin Name</label>
                            <input type="text" name="name" value="<?= htmlspecialchars($admin['name']) ?>">
                        </div>
                        <div class="dash-form-group">
                            <label>Email <span style="color:#94a3b8;font-weight:400">(read only)</span></label>
                            <input type="email" value="<?= htmlspecialchars($admin['email']) ?>" disabled style="background:#f0f4ff;color:#94a3b8">
                        </div>
                        <div style="border-top:1px solid #e2e8f0;margin:18px 0;padding-top:18px">
                            <div style="font-size:13px;font-weight:700;color:#334155;margin-bottom:12px">📱 Order Notifications</div>
                            <div class="dash-form-group">
                                <label>WhatsApp Number (for Orders)</label>
                                <input type="text" name="whatsapp_number" value="<?= htmlspecialchars($whatsappNum) ?>" placeholder="e.g. 923001234567">
                                <p style="font-size:11px; color:#64748b; margin-top:4px">Enter number with country code (e.g. 92 for Pakistan) without + or 00.</p>
                            </div>
                        </div>
                        <div style="border-top:1px solid #e2e8f0;margin:18px 0;padding-top:18px">
                            <div style="font-size:13px;font-weight:700;color:#334155;margin-bottom:12px">🔐 Change Password</div>
                            <div class="dash-form-group">
                                <label>Current Password</label>
                                <input type="password" name="current_password" placeholder="Current password">
                            </div>
                            <div class="dash-form-group">
                                <label>New Password</label>
                                <input type="password" name="new_password" placeholder="Min. 6 characters">
                            </div>
                        </div>
                        <button type="submit" class="filter-btn success" style="width:100%;padding:12px;font-size:14px;border-radius:8px">💾 Save Changes</button>
                    </form>
                </div>
            </div>

            <!-- System Info -->
            <div style="display:flex;flex-direction:column;gap:20px">
                <div class="dash-panel">
                    <div class="panel-header"><h3>📊 System Stats</h3></div>
                    <div class="panel-body">
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                            <?php $syStats = [
                                ['👥','Total Users',$totalUsers,'#1a6bff'],
                                ['📋','Total Reports',$totalReports,'#10b981'],
                                ['📦','Total Orders',$totalOrders,'#f59e0b'],
                                ['✉️','Messages',$totalMessages,'#8b5cf6'],
                            ]; ?>
                            <?php foreach ($syStats as $s): ?>
                            <div style="background:#f8faff;border-radius:10px;padding:16px;text-align:center">
                                <div style="font-size:24px;margin-bottom:4px"><?= $s[0] ?></div>
                                <div style="font-size:24px;font-weight:800;color:<?= $s[3] ?>"><?= $s[2] ?></div>
                                <div style="font-size:12px;color:#64748b;margin-top:2px"><?= $s[1] ?></div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="dash-panel">
                    <div class="panel-header"><h3>ℹ️ System Info</h3></div>
                    <div class="panel-body">
                        <?php $info = [
                            ['System','Ecomedge v1.0'],
                            ['PHP Version', PHP_VERSION],
                            ['Database','MySQL (ecomedge_db)'],
                            ['Server Time', date('d M Y, h:i A')],
                            ['Employee Codes','EE-01, EE-02, EE-03, EE-10'],
                            ['Technology','PHP + MySQL'],
                        ]; ?>
                        <?php foreach ($info as $row): ?>
                        <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-bottom:1px solid #f0f4ff">
                            <span style="font-size:13px;color:#64748b"><?= $row[0] ?></span>
                            <span style="font-size:13px;font-weight:600;color:#334155"><?= $row[1] ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="dash-panel">
                    <div class="panel-header"><h3>🔗 Quick Links</h3></div>
                    <div class="panel-body" style="display:flex;flex-direction:column;gap:8px">
                        <a href="../index.php"   class="filter-btn" style="text-align:center">🌐 View Public Website</a>
                        <a href="pending.php"    class="filter-btn" style="text-align:center">⏳ Manage Pending Users</a>
                        <a href="analytics.php"  class="filter-btn" style="text-align:center">📈 View Analytics</a>
                    </div>
                </div>

                <div class="dash-panel" style="border-color:#fecaca;background:#fff5f5">
                    <div class="panel-header" style="border-bottom-color:#fecaca">
                        <h3 style="color:#b91c1c">⚠️ System Maintenance</h3>
                    </div>
                    <div class="panel-body">
                        <p style="font-size:12px;color:#991b1b;margin-bottom:15px;line-height:1.5">
                            Use this to clear all employee order history and reports (Post Office & Leopard). This action <strong>cannot be undone</strong>.
                        </p>
                        <form method="POST" onsubmit="return confirm('⚠️ CRITICAL ACTION: Are you sure you want to PERMANENTLY DELETE all employee reports and history? This cannot be undone!')">
                            <button type="submit" name="reset_data" class="filter-btn danger" style="width:100%;padding:12px;font-weight:700">🧹 Reset All Employee Data</button>
                        </form>
                        <a href="../logout.php" class="filter-btn" style="text-align:center;width:100%;margin-top:12px;color:#64748b;border-color:#e2e8f0">🚪 Logout</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
