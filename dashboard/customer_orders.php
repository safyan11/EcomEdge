<?php
// dashboard/customer_orders.php - Admin: View Customer Orders from Gallery
require_once '../config.php';
requireAdmin();

$db = getDB();
$msg = '';

// Handle Filter Date
$filterDate = $_GET['filter_date'] ?? date('Y-m-d');
$filterLabel = 'Orders';

if ($filterDate === date('Y-m-d')) $filterLabel = "Today's Orders";
elseif ($filterDate === date('Y-m-d', strtotime('-1 day'))) $filterLabel = "Yesterday's Orders";
else $filterLabel = "Orders on " . date('d M Y', strtotime($filterDate));

// Handle Status Update or Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderId = (int)($_POST['order_id'] ?? 0);
    $action  = $_POST['action'] ?? '';
    
    if ($orderId) {
        if ($action === 'delete') {
            $db->query("DELETE FROM product_orders WHERE id=$orderId");
            $msg = 'Order deleted permanently.';
        } else {
            $stmt = $db->prepare("UPDATE product_orders SET status=? WHERE id=?");
            $stmt->bind_param('si', $action, $orderId);
            $stmt->execute();
            $msg = 'Order status updated to ' . $action;
        }
    }
}

$orders = $db->query("
    SELECT o.*, g.title as product_name, g.price 
    FROM product_orders o 
    LEFT JOIN gallery g ON g.id = o.product_id 
    WHERE DATE(o.created_at) = '$filterDate'
    ORDER BY o.created_at DESC
")->fetch_all(MYSQLI_ASSOC);

$db->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Orders - Ecomedge</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
        .order-card { background: white; border-radius: 12px; border: 1px solid #e2e8f0; margin-bottom: 12px; overflow: hidden; transition: 0.2s; display: flex; align-items: stretch; }
        .order-card:hover { border-color: #1a6bff; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
        .card-checkbox { padding: 15px; background: #f8faff; border-right: 1px solid #e2e8f0; display: flex; align-items: center; }
        .card-checkbox input { width: 18px; height: 18px; cursor: pointer; }
        .card-main { flex: 1; }
        .order-header { padding: 10px 15px; background: #f8faff; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; }
        .order-body { padding: 12px 15px; display: grid; grid-template-columns: 1.2fr 1fr 1.5fr; gap: 15px; align-items: center; }
        .info-group label { display: block; font-size: 10px; font-weight: 700; color: #94a3b8; text-transform: uppercase; margin-bottom: 2px; }
        .info-group p { font-size: 13px; font-weight: 600; color: #1e293b; line-height: 1.2; }
        .badge { padding: 4px 10px; font-size: 11px; }
        .quick-filter { display: flex; gap: 10px; margin-bottom: 20px; }
        .q-btn { padding: 8px 16px; border-radius: 8px; background: white; border: 1px solid #e2e8f0; color: #64748b; text-decoration: none; font-size: 13px; font-weight: 600; }
        .q-btn.active { background: #1a6bff; color: white; border-color: #1a6bff; }
        @media (max-width: 768px) { .order-body { grid-template-columns: 1fr; gap: 10px; } }
    </style>
</head>
<body class="dash-body">
<?php include 'includes/sidebar.php'; ?>

<div class="main-content">
    <div class="topbar">
        <div class="topbar-left">
            <h2>🛒 <?= $filterLabel ?></h2>
            <p>Manage orders received for the selected date</p>
        </div>
        <div class="topbar-right" style="display:flex; gap:15px; align-items:center;">
            <a href="export_orders_pdf.php?date=<?= $filterDate ?>" class="filter-btn success" style="padding:10px 20px; font-size:13px;">
                <i class="fas fa-file-pdf"></i> Download PDF
            </a>
            <span class="topbar-date">Found: <?= count($orders) ?></span>
        </div>
    </div>

    <div class="page-body">
        <div class="quick-filter">
            <a href="?filter_date=<?= date('Y-m-d') ?>" class="q-btn <?= $filterDate === date('Y-m-d') ? 'active' : '' ?>">Today</a>
            <a href="?filter_date=<?= date('Y-m-d', strtotime('-1 day')) ?>" class="q-btn <?= $filterDate === date('Y-m-d', strtotime('-1 day')) ? 'active' : '' ?>">Yesterday</a>
            <form style="display:flex; gap:8px; align-items:center;">
                <input type="date" name="filter_date" value="<?= $filterDate ?>" class="q-btn" style="padding:6px; outline:none;" onchange="this.form.submit()">
            </form>
        </div>

        <?php if ($msg): ?>
        <div style="background:#d1fae5; border:1px solid #6ee7b7; border-radius:10px; padding:12px 20px; margin-bottom:20px; color:#065f46; font-size:14px; font-weight:600">✅ <?= $msg ?></div>
        <?php endif; ?>

        <?php if (empty($orders)): ?>
        <div class="dash-panel">
            <div class="empty-state">
                <div class="empty-icon">📅</div>
                <h3>No Orders Found</h3>
                <p>There are no gallery orders recorded for <?= date('d M Y', strtotime($filterDate)) ?>.</p>
            </div>
        </div>
        <?php else: ?>
        
        <div style="margin-bottom:15px; display:flex; align-items:center; gap:10px;">
            <input type="checkbox" id="selectAll" style="width:18px; height:18px; cursor:pointer;">
            <label for="selectAll" style="font-size:13px; font-weight:700; color:#475569; cursor:pointer;">Select All Orders</label>
        </div>

        <?php foreach ($orders as $o): ?>
        <div class="order-card">
            <div class="card-checkbox">
                <input type="checkbox" class="order-select" data-id="<?= $o['id'] ?>">
            </div>
            <div class="card-main">
                <div class="order-header">
                    <div>
                        <span style="font-size:12px; color:#64748b;">Order #<?= $o['id'] ?></span>
                        <span style="margin-left:10px; font-size:12px; color:#94a3b8;"><?= date('h:i A', strtotime($o['created_at'])) ?></span>
                    </div>
                    <div>
                        <?php if($o['status'] === 'pending'): ?>
                            <span class="badge badge-warning">⏳ Pending</span>
                        <?php elseif($o['status'] === 'completed'): ?>
                            <span class="badge badge-success">✅ Completed</span>
                        <?php else: ?>
                            <span class="badge badge-danger">❌ Cancelled</span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="order-body">
                    <div class="info-group">
                        <label>Product</label>
                        <p style="color:#1a6bff;"><?= htmlspecialchars($o['product_name'] ?? 'Deleted') ?></p>
                        <p style="font-size:11px; color:#10b981;">Rs. <?= htmlspecialchars($o['price'] ?? '0') ?></p>
                    </div>
                    <div class="info-group">
                        <label>Customer</label>
                        <p><?= htmlspecialchars($o['customer_name']) ?></p>
                        <p style="color:#64748b; font-size:12px;"><?= htmlspecialchars($o['phone']) ?></p>
                    </div>
                    <div class="info-group">
                        <label>Shipping Info</label>
                        <p><strong><?= htmlspecialchars($o['city']) ?></strong></p>
                        <p style="font-size:11px; color:#64748b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 250px;">
                            <?= htmlspecialchars($o['address']) ?>
                        </p>
                    </div>
                </div>
                <div style="padding:8px 15px; border-top:1px solid #f1f5f9; display:flex; justify-content:space-between; align-items:center; background:#fafbff;">
                    <a href="https://wa.me/<?= preg_replace('/[^0-9]/','',$o['phone']) ?>" target="_blank" style="color:#10b981; font-size:11px; font-weight:700; text-decoration:none;">💬 Chat</a>
                    
                    <form method="POST" style="display:flex; gap:8px;">
                        <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                        <?php if($o['status'] === 'pending'): ?>
                            <button name="action" value="completed" class="filter-btn success" style="font-size:10px; padding:4px 8px;">Mark Done</button>
                        <?php endif; ?>
                        <button name="action" value="delete" class="filter-btn danger" style="font-size:10px; padding:4px 8px;" onclick="return confirm('Delete?')">🗑️</button>
                    </form>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        
        <?php endif; ?>
    </div>
</div>

<script>
    document.getElementById('selectAll').addEventListener('change', function() {
        document.querySelectorAll('.order-select').forEach(cb => cb.checked = this.checked);
    });
</script>
</body>
</html>

</body>
</html>
