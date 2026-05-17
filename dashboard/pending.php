<?php
// dashboard/pending.php - Admin: Approve/Reject pending users
require_once '../config.php';
requireAdmin();

$db = getDB();
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = (int)($_POST['user_id'] ?? 0);
    $action = $_POST['action'] ?? '';

    if ($userId && in_array($action, ['approved','rejected'])) {
        $stmt = $db->prepare("UPDATE users SET status=? WHERE id=?");
        $stmt->bind_param('si', $action, $userId);
        $stmt->execute();
        $msg = $action === 'approved' ? '✅ Account approved successfully!' : '❌ Account rejected.';
    }
}

$pendingUsers = $db->query("SELECT * FROM users WHERE status='pending' ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
$db->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Users - Ecomedge</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body class="dash-body">
<?php include 'includes/sidebar.php'; ?>

<div class="main-content">
    <div class="topbar">
        <div class="topbar-left">
            <h2>⏳ Pending User Approvals</h2>
            <p><?= count($pendingUsers) ?> user(s) waiting for approval</p>
        </div>
        <div class="topbar-right">
            <span class="topbar-date">📅 <?= date('D, d M Y') ?></span>
        </div>
    </div>

    <div class="page-body">
        <?php if ($msg): ?>
        <div style="background:#d1fae5;border:1px solid #6ee7b7;border-radius:10px;padding:14px 20px;margin-bottom:20px;color:#065f46;font-size:14px;font-weight:600"><?= $msg ?></div>
        <?php endif; ?>

        <?php if (empty($pendingUsers)): ?>
        <div class="dash-panel">
            <div class="empty-state">
                <div class="empty-icon">🎉</div>
                <h3>All Clear!</h3>
                <p>No pending user approvals at the moment.</p>
            </div>
        </div>
        <?php else: ?>

        <div style="display:flex;flex-direction:column;gap:16px">
        <?php foreach ($pendingUsers as $user): ?>
        <div class="dash-panel" style="margin-bottom:0">
            <div style="padding:20px 24px;display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap">
                <div style="display:flex;align-items:center;gap:16px">
                    <div style="width:50px;height:50px;background:linear-gradient(135deg,#1a6bff,#00c6ff);border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:20px;color:white;flex-shrink:0">
                        <?= strtoupper(substr($user['name'],0,1)) ?>
                    </div>
                    <div>
                        <strong style="font-size:16px;color:#0f172a;display:block"><?= htmlspecialchars($user['name']) ?></strong>
                        <span style="font-size:13px;color:#64748b"><?= htmlspecialchars($user['email']) ?></span>
                        <div style="margin-top:6px;display:flex;gap:8px;flex-wrap:wrap">
                            <span class="badge badge-info">Code: <?= $user['employee_code'] ?></span>
                            <?php if($user['role'] === 'admin'): ?><span class="badge" style="background:#fef3c7; color:#92400e;">⚠️ ADMIN REQUEST</span><?php endif; ?>
                            <span class="badge badge-warning">⏳ Pending</span>
                            <span style="font-size:12px;color:#94a3b8">Registered: <?= date('d M Y, h:i A', strtotime($user['created_at'])) ?></span>
                        </div>
                    </div>
                </div>
                <div style="display:flex;gap:10px">
                    <form method="POST" action="" style="display:inline">
                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                        <input type="hidden" name="action" value="approved">
                        <button type="submit" class="filter-btn success" onclick="return confirm('Approve <?= htmlspecialchars($user['name']) ?>?')">
                            ✅ Approve
                        </button>
                    </form>
                    <form method="POST" action="" style="display:inline">
                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                        <input type="hidden" name="action" value="rejected">
                        <button type="submit" class="filter-btn danger" onclick="return confirm('Reject this user?')">
                            ❌ Reject
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        </div>

        <?php endif; ?>
    </div>
</div>
</body>
</html>
