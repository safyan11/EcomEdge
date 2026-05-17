<?php
// dashboard/users.php - Admin: All Users Management
require_once '../config.php';
requireAdmin();

$db = getDB();
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = (int)($_POST['user_id'] ?? 0);
    $action = $_POST['action'] ?? '';
    if ($userId && $userId !== $_SESSION['user_id']) {
        if (in_array($action, ['approved','rejected','pending'])) {
            $stmt = $db->prepare("UPDATE users SET status=? WHERE id=?");
            $stmt->bind_param('si', $action, $userId);
            $stmt->execute();
            $msg = 'User status updated!';
        } elseif ($action === 'delete') {
            $db->query("DELETE FROM users WHERE id=$userId");
            $msg = 'Account deleted permanently!';
        }
    }
}

$filter = $_GET['status'] ?? 'all';
$current_id = $_SESSION['user_id'];
$where  = $filter !== 'all' ? "WHERE id != $current_id AND status='$filter'" : "WHERE id != $current_id";
$users  = $db->query("SELECT u.*, (SELECT COUNT(*) FROM reports r WHERE r.user_id=u.id) as report_count FROM users u $where ORDER BY u.created_at DESC")->fetch_all(MYSQLI_ASSOC);
$db->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users - Ecomedge</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body class="dash-body">
<?php include 'includes/sidebar.php'; ?>
<div class="main-content">
    <div class="topbar">
        <div class="topbar-left"><h2>👥 All Users</h2><p>Manage employee accounts</p></div>
        <div class="topbar-right"><span class="topbar-date">Total: <?= count($users) ?> users</span></div>
    </div>
    <div class="page-body">
        <?php if ($msg): ?>
        <div style="background:#d1fae5;border:1px solid #6ee7b7;border-radius:10px;padding:12px 20px;margin-bottom:20px;color:#065f46;font-size:14px;font-weight:600">✅ <?= $msg ?></div>
        <?php endif; ?>

        <div class="dash-panel">
            <div class="filter-row">
                <?php foreach(['all'=>'All Users','approved'=>'Approved','pending'=>'Pending','rejected'=>'Rejected'] as $k=>$lbl): ?>
                <a href="?status=<?= $k ?>" class="filter-btn <?= $filter===$k?'active':'' ?>"><?= $lbl ?></a>
                <?php endforeach; ?>
            </div>
            <?php if (empty($users)): ?>
            <div class="empty-state"><div class="empty-icon">👥</div><h3>No Users Found</h3></div>
            <?php else: ?>
            <table class="dash-table">
                <thead><tr><th>Employee</th><th>Email</th><th>Code</th><th>Reports</th><th>Status</th><th>Joined</th><th>Actions</th></tr></thead>
                <tbody>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px">
                            <div style="width:36px;height:36px;background:linear-gradient(135deg,#1a6bff,#00c6ff);border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:14px;color:white;flex-shrink:0;overflow:hidden">
                                <?php if ($u['profile_image']): ?>
                                    <img src="../<?= $u['profile_image'] ?>" style="width:100%;height:100%;object-fit:cover">
                                <?php else: ?>
                                    <?= strtoupper(substr($u['name'],0,1)) ?>
                                <?php endif; ?>
                            </div>
                            <strong><?= htmlspecialchars($u['name']) ?></strong>
                        </div>
                    </td>
                    <td style="color:#64748b"><?= htmlspecialchars($u['email']) ?></td>
                    <td>
                        <span class="badge badge-info"><?= $u['employee_code'] ?></span>
                        <?php if($u['role'] === 'admin'): ?><span class="badge" style="background:#fef3c7; color:#92400e; font-size:10px; margin-left:4px">ADMIN</span><?php endif; ?>
                    </td>
                    <td><?= $u['report_count'] ?></td>
                    <td>
                        <?php if ($u['status']==='approved'): ?><span class="badge badge-success">✅ Approved</span>
                        <?php elseif ($u['status']==='pending'):  ?><span class="badge badge-warning">⏳ Pending</span>
                        <?php else: ?><span class="badge badge-danger">❌ Rejected</span><?php endif; ?>
                    </td>
                    <td style="color:#94a3b8;font-size:12px"><?= date('d M Y', strtotime($u['created_at'])) ?></td>
                    <td>
                        <div style="display:flex;gap:6px">
                            <a href="user_history.php?id=<?= $u['id'] ?>" class="filter-btn" title="View History" style="padding:5px 12px;font-size:12px;display:flex;align-items:center;justify-content:center;color:#1a6bff;border-color:#1a6bff;">👁️</a>
                            <form method="POST" style="display:flex;gap:6px">
                                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                <?php if ($u['status']!=='approved'): ?>
                                <button name="action" value="approved" class="filter-btn success" style="padding:5px 12px;font-size:12px">✅</button>
                                <?php endif; ?>
                                <?php if ($u['status']!=='rejected'): ?>
                                <button name="action" value="rejected" class="filter-btn danger" style="padding:5px 12px;font-size:12px" title="Reject" onclick="return confirm('Reject this user?')">❌</button>
                                <?php endif; ?>
                                <button name="action" value="delete" class="filter-btn danger" style="padding:5px 12px;font-size:12px; background: #fee2e2;" title="Delete Permanently" onclick="return confirm('⚠️ PERMANENT DELETE: Are you sure you want to delete this user? All their records will be lost!')">🗑️</button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
