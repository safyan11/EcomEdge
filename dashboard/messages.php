<?php
// dashboard/messages.php - Admin: Contact Messages
require_once '../config.php';
requireAdmin();

$db = getDB();

// Mark as read
if (isset($_GET['read']) && is_numeric($_GET['read'])) {
    $db->query("UPDATE contact_messages SET is_read=1 WHERE id=" . (int)$_GET['read']);
    header('Location: messages.php');
    exit;
}

// Delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $db->query("DELETE FROM contact_messages WHERE id=" . (int)$_GET['delete']);
    header('Location: messages.php');
    exit;
}

$messages = $db->query("SELECT * FROM contact_messages ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
$unread   = count(array_filter($messages, fn($m) => !$m['is_read']));
$db->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - Ecomedge</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body class="dash-body">
<?php include 'includes/sidebar.php'; ?>
<div class="main-content">
    <div class="topbar">
        <div class="topbar-left">
            <h2>✉️ Contact Messages</h2>
            <p><?= $unread ?> unread message(s) out of <?= count($messages) ?> total</p>
        </div>
        <div class="topbar-right">
            <span class="topbar-date">📅 <?= date('D, d M Y') ?></span>
        </div>
    </div>
    <div class="page-body">
        <?php if (empty($messages)): ?>
        <div class="dash-panel">
            <div class="empty-state">
                <div class="empty-icon">📭</div>
                <h3>No Messages Yet</h3>
                <p>Messages sent from the Contact page will appear here.</p>
            </div>
        </div>
        <?php else: ?>
        <div style="display:flex;flex-direction:column;gap:12px">
            <?php foreach ($messages as $msg): ?>
            <div class="dash-panel" style="margin-bottom:0;<?= !$msg['is_read'] ? 'border-left:4px solid #1a6bff' : '' ?>">
                <div style="padding:18px 24px;display:flex;align-items:flex-start;justify-content:space-between;gap:16px;flex-wrap:wrap">
                    <div style="flex:1;min-width:0">
                        <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px;flex-wrap:wrap">
                            <div style="width:38px;height:38px;background:linear-gradient(135deg,#1a6bff,#00c6ff);border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:15px;color:white;flex-shrink:0">
                                <?= strtoupper(substr($msg['name'],0,1)) ?>
                            </div>
                            <div>
                                <strong style="color:#0f172a;font-size:15px"><?= htmlspecialchars($msg['name']) ?></strong>
                                <span style="color:#64748b;font-size:13px;margin-left:8px"><?= htmlspecialchars($msg['email']) ?></span>
                            </div>
                            <?php if (!$msg['is_read']): ?>
                            <span class="badge badge-info" style="font-size:11px">🔵 New</span>
                            <?php else: ?>
                            <span style="font-size:11px;color:#94a3b8">✓ Read</span>
                            <?php endif; ?>
                            <span style="margin-left:auto;font-size:12px;color:#94a3b8"><?= date('d M Y, h:i A', strtotime($msg['created_at'])) ?></span>
                        </div>
                        <div style="background:#f8faff;border-radius:8px;padding:12px 16px;font-size:14px;color:#334155;line-height:1.6">
                            <?= nl2br(htmlspecialchars($msg['message'])) ?>
                        </div>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:6px;flex-shrink:0">
                        <?php if (!$msg['is_read']): ?>
                        <a href="?read=<?= $msg['id'] ?>" class="filter-btn success" style="font-size:12px;padding:6px 12px">✓ Mark Read</a>
                        <?php endif; ?>
                        <a href="mailto:<?= htmlspecialchars($msg['email']) ?>?subject=Re: Your message to Ecomedge" class="filter-btn" style="font-size:12px;padding:6px 12px">📧 Reply</a>
                        <a href="?delete=<?= $msg['id'] ?>" class="filter-btn danger" style="font-size:12px;padding:6px 12px" onclick="return confirm('Delete this message?')">🗑 Delete</a>
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
