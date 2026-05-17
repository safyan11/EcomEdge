<?php
// dashboard/profile.php - User Profile
require_once '../config.php';
requireLogin();

$db  = getDB();
$uid = $_SESSION['user_id'];
$msg = $err = '';

// Get user
$user = $db->query("SELECT * FROM users WHERE id=$uid")->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $currentPass = $_POST['current_password'] ?? '';
    $newPass     = $_POST['new_password'] ?? '';
    
    // Handle Name Update
    if ($name && $name !== $user['name']) {
        $stmt = $db->prepare("UPDATE users SET name=? WHERE id=?");
        $stmt->bind_param('si', $name, $uid);
        $stmt->execute();
        $_SESSION['user_name'] = $name;
        $msg = 'Name updated successfully!';
        $user['name'] = $name;
    }

    // Handle Profile Image Upload
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $filename = $_FILES['profile_image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $newName = $uid . '_' . time() . '.' . $ext;
            $uploadDir = '../assets/images/profiles/';
            $dest = $uploadDir . $newName;
            
            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $dest)) {
                // Delete old image if exists
                if ($user['profile_image'] && file_exists('../' . $user['profile_image'])) {
                    @unlink('../' . $user['profile_image']);
                }
                
                $dbPath = 'assets/images/profiles/' . $newName;
                $stmt = $db->prepare("UPDATE users SET profile_image=? WHERE id=?");
                $stmt->bind_param('si', $dbPath, $uid);
                $stmt->execute();
                $msg = 'Profile image updated successfully!';
                $user['profile_image'] = $dbPath;
                $_SESSION['user_image'] = $dbPath;
            } else {
                $err = 'Failed to upload image.';
            }
        } else {
            $err = 'Invalid file type. Allowed: ' . implode(', ', $allowed);
        }
    }

    // Handle Password Update
    if ($currentPass && $newPass) {
        if (!password_verify($currentPass, $user['password'])) {
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
}

// Stats
$stats = $db->query("SELECT COUNT(*) as days, COALESCE(SUM(total_orders),0) as tot, COALESCE(SUM(confirmed_orders),0) as conf FROM reports WHERE user_id=$uid")->fetch_assoc();
$db->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Ecomedge</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body class="dash-body">
<?php include 'includes/sidebar.php'; ?>
<div class="main-content">
    <div class="topbar">
        <div class="topbar-left"><h2>👤 My Profile</h2><p>Manage your account information</p></div>
    </div>
    <div class="page-body">

        <?php if ($msg): ?>
        <div style="background:#d1fae5;border:1px solid #6ee7b7;border-radius:10px;padding:12px 20px;margin-bottom:20px;color:#065f46;font-size:14px;font-weight:600">✅ <?= $msg ?></div>
        <?php endif; ?>
        <?php if ($err): ?>
        <div style="background:#fee2e2;border:1px solid #fca5a5;border-radius:10px;padding:12px 20px;margin-bottom:20px;color:#991b1b;font-size:14px;font-weight:600">⚠️ <?= $err ?></div>
        <?php endif; ?>

        <div class="grid-2">
            <!-- Profile Card -->
            <div class="dash-panel">
                <div style="background:linear-gradient(135deg,#0d1b4b,#1a3a7a);border-radius:14px 14px 0 0;padding:32px;text-align:center">
                    <div style="width:100px;height:100px;margin:0 auto 16px;position:relative">
                        <?php if ($user['profile_image']): ?>
                            <img src="../<?= $user['profile_image'] ?>" style="width:100%;height:100%;border-radius:50%;object-fit:cover;border:4px solid rgba(255,255,255,0.2)">
                        <?php else: ?>
                            <div style="width:100%;height:100%;background:linear-gradient(135deg,#1a6bff,#00c6ff);border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:900;font-size:32px;color:white;border:4px solid rgba(255,255,255,0.2)">
                                <?= strtoupper(substr($user['name'],0,1)) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <h3 style="color:white;font-size:20px;font-weight:700;margin-bottom:4px"><?= htmlspecialchars($user['name']) ?></h3>
                    <p style="color:rgba(255,255,255,0.6);font-size:14px"><?= htmlspecialchars($user['email']) ?></p>
                    <div style="margin-top:12px;display:flex;gap:8px;justify-content:center;flex-wrap:wrap">
                        <span style="background:rgba(255,255,255,0.12);color:#00c6ff;padding:4px 14px;border-radius:20px;font-size:12px;font-weight:700"><?= $user['employee_code'] ?></span>
                        <span style="background:rgba(16,185,129,0.2);color:#6ee7b7;padding:4px 14px;border-radius:20px;font-size:12px;font-weight:700">✅ Approved</span>
                    </div>
                </div>
                <div style="padding:24px">
                    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;text-align:center">
                        <div style="background:#f8faff;border-radius:10px;padding:16px">
                            <div style="font-size:24px;font-weight:800;color:#1a6bff"><?= $stats['days'] ?></div>
                            <div style="font-size:12px;color:#64748b;margin-top:2px">Days Reported</div>
                        </div>
                        <div style="background:#f8faff;border-radius:10px;padding:16px">
                            <div style="font-size:24px;font-weight:800;color:#f59e0b"><?= number_format($stats['tot']) ?></div>
                            <div style="font-size:12px;color:#64748b;margin-top:2px">Total Orders</div>
                        </div>
                        <div style="background:#f8faff;border-radius:10px;padding:16px">
                            <div style="font-size:24px;font-weight:800;color:#10b981"><?= number_format($stats['conf']) ?></div>
                            <div style="font-size:12px;color:#64748b;margin-top:2px">Confirmed</div>
                        </div>
                    </div>
                    <div style="margin-top:16px;padding:14px;background:#f8faff;border-radius:10px">
                        <div style="display:flex;justify-content:space-between;margin-bottom:6px">
                            <span style="font-size:13px;color:#64748b">Member since</span>
                            <span style="font-size:13px;font-weight:600;color:#334155"><?= date('d M Y', strtotime($user['created_at'])) ?></span>
                        </div>
                        <div style="display:flex;justify-content:space-between">
                            <span style="font-size:13px;color:#64748b">Role</span>
                            <span style="font-size:13px;font-weight:600;color:#334155"><?= ucfirst($user['role']) ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Edit Form -->
            <div class="dash-panel">
                <div class="panel-header"><h3>✏️ Edit Profile</h3></div>
                <div class="panel-body">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="dash-form-group">
                            <label>Profile Image</label>
                            <input type="file" name="profile_image" accept="image/*" style="padding:10px;background:#f8faff;border:2px dashed #e2e8f0;border-radius:8px;cursor:pointer">
                            <p style="font-size:11px;color:#64748b;margin-top:4px">JPG, PNG or WebP. Max 2MB.</p>
                        </div>
                        <div class="dash-form-group">
                            <label>Full Name</label>
                            <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" placeholder="Your full name">
                        </div>
                        <div class="dash-form-group">
                            <label>Email Address <span style="color:#94a3b8;font-weight:400">(cannot be changed)</span></label>
                            <input type="email" value="<?= htmlspecialchars($user['email']) ?>" disabled style="background:#f0f4ff;color:#94a3b8">
                        </div>
                        <div class="dash-form-group">
                            <label>Employee Code <span style="color:#94a3b8;font-weight:400">(cannot be changed)</span></label>
                            <input type="text" value="<?= $user['employee_code'] ?>" disabled style="background:#f0f4ff;color:#94a3b8">
                        </div>

                        <div style="border-top:1px solid #e2e8f0;margin:20px 0;padding-top:20px">
                            <div style="font-size:14px;font-weight:700;color:#334155;margin-bottom:14px">🔐 Change Password</div>
                            <div class="dash-form-group">
                                <label>Current Password</label>
                                <input type="password" name="current_password" placeholder="Enter current password">
                            </div>
                            <div class="dash-form-group">
                                <label>New Password</label>
                                <input type="password" name="new_password" placeholder="Min. 6 characters">
                            </div>
                        </div>

                        <button type="submit" class="filter-btn success" style="width:100%;padding:12px;font-size:14px;border-radius:8px">
                            💾 Save Changes
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
