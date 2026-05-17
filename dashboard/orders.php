<?php
// dashboard/orders.php - Admin: All Confirmed Orders
require_once '../config.php';
requireAdmin();

$db = getDB();
$filter   = $_GET['filter'] ?? 'today';
$dateFrom = $_GET['from'] ?? date('Y-m-d');
$dateTo   = $_GET['to']   ?? date('Y-m-d');
$empCode  = $_GET['emp_code'] ?? 'all';
$search   = trim($_GET['search'] ?? '');

switch ($filter) {
    case 'today':     $dateFrom = $dateTo = date('Y-m-d'); break;
    case 'yesterday': $dateFrom = $dateTo = date('Y-m-d', strtotime('-1 day')); break;
    case 'week':      $dateFrom = date('Y-m-d', strtotime('-6 days')); $dateTo = date('Y-m-d'); break;
    case 'month':     $dateFrom = date('Y-m-01'); $dateTo = date('Y-m-d'); break;
    case 'custom':    break;
}

$where = "r.report_date BETWEEN '$dateFrom' AND '$dateTo'";
if ($empCode !== 'all') {
    $where .= " AND u.employee_code = '$empCode'";
}
if ($search) {
    $where .= " AND (u.name LIKE '%$search%' OR u.employee_code LIKE '%$search%')";
}

$orders = $db->query("
    SELECT r.*, u.name as emp_name, u.employee_code
    FROM reports r
    JOIN users u ON u.id = r.user_id
    WHERE $where
    ORDER BY r.report_date DESC
")->fetch_all(MYSQLI_ASSOC);

$totalCount = count($orders);
$allEmployees = $db->query("SELECT DISTINCT name, employee_code FROM users WHERE role='user' ORDER BY employee_code")->fetch_all(MYSQLI_ASSOC);
$db->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Orders - Ecomedge</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body class="dash-body">
<?php include 'includes/sidebar.php'; ?>
<div class="main-content">
    <div class="topbar">
        <div class="topbar-left">
            <h2>📦 Confirmed Orders</h2>
            <p><?= $totalCount ?> orders found | <?= date('d M', strtotime($dateFrom)) ?><?= $dateFrom !== $dateTo ? ' – '.date('d M Y', strtotime($dateTo)) : ' '.date('Y', strtotime($dateTo)) ?></p>
        </div>
        <div class="topbar-right">
            <span class="topbar-date">📅 <?= date('D, d M Y') ?></span>
        </div>
    </div>

    <div class="page-body">
        <div class="dash-panel">
            <!-- FILTERS -->
            <div class="filter-row" style="padding: 20px; border-bottom: 1px solid #e2e8f0; background: #fff; gap: 15px; flex-wrap: wrap;">
                <div style="display:flex; gap:8px;">
                    <?php foreach(['today'=>'Today','yesterday'=>'Yesterday','week'=>'Week','month'=>'Month','custom'=>'Custom'] as $k=>$lbl): ?>
                    <a href="?filter=<?= $k ?><?= $search?'&search='.urlencode($search):'' ?>" class="filter-btn <?= $filter===$k?'active':'' ?>" style="font-size:12px; padding:6px 12px;"><?= $lbl ?></a>
                    <?php endforeach; ?>
                </div>

                <form method="GET" style="display:flex; gap:12px; align-items:center; flex-wrap:wrap; flex:1;">
                    <input type="hidden" name="filter" value="custom">
                    
                    <div style="display:flex; align-items:center; gap:6px;">
                        <span style="font-size:12px; color:#64748b; font-weight:600">📅 From:</span>
                        <input type="date" name="from" value="<?= $dateFrom ?>" style="padding:6px 10px; border:1.5px solid #e2e8f0; border-radius:6px; font-size:13px;">
                        <span style="font-size:12px; color:#64748b; font-weight:600">To:</span>
                        <input type="date" name="to" value="<?= $dateTo ?>" style="padding:6px 10px; border:1.5px solid #e2e8f0; border-radius:6px; font-size:13px;">
                    </div>

                    <div style="display:flex; align-items:center; gap:6px;">
                        <span style="font-size:12px; color:#64748b; font-weight:600">👥 Employee:</span>
                        <select name="emp_code" style="padding:6px 10px; border:1.5px solid #e2e8f0; border-radius:6px; font-size:13px; outline:none;">
                            <option value="all">All Employees</option>
                            <?php foreach ($allEmployees as $e): ?>
                            <option value="<?= $e['employee_code'] ?>" <?= $empCode === $e['employee_code'] ? 'selected' : '' ?>>
                                <?= $e['employee_code'] ?> - <?= htmlspecialchars($e['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div style="display:flex; gap:8px; align-items:center; margin-left:auto;">
                        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="🔍 Order ID / Search..." style="padding:6px 14px; border:1.5px solid #e2e8f0; border-radius:8px; font-size:13px; width:200px; outline:none">
                        <button type="submit" class="filter-btn active" style="padding:7px 16px;">Apply Filters</button>
                        <?php if ($search || $filter!=='today' || $empCode!=='all'): ?>
                        <a href="orders.php" class="filter-btn" style="color:#ef4444; border-color:#fecaca;">✕ Reset</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Courier Stats Mini -->
            <?php if (!empty($couriers) && !$search): ?>
            <div style="display:flex;gap:10px;flex-wrap:wrap;padding:14px 24px;background:#f8faff;border-bottom:1px solid #e2e8f0">
                <?php
                $courierColors = ['#1a6bff','#10b981','#f59e0b','#8b5cf6','#ef4444'];
                foreach ($couriers as $ci => $c):
                    $cnt = count(array_filter($orders, fn($o) => $o['courier'] === $c['courier']));
                    $col = $courierColors[$ci % count($courierColors)];
                ?>
                <span style="background:white;border:1px solid #e2e8f0;border-radius:20px;padding:5px 14px;font-size:12px;font-weight:700;color:<?= $col ?>">
                    <?= htmlspecialchars($c['courier']) ?>: <?= $cnt ?>
                </span>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <?php if (empty($orders)): ?>
            <div class="empty-state"><div class="empty-icon">📦</div><h3>No Orders Found</h3><p>No confirmed orders for the selected period<?= $search ? " matching '$search'" : '' ?>.</p></div>
            <?php else: ?>
            <table class="dash-table">
                <thead>
                    <tr>
                        <th>DATE</th>
                        <th>EMPLOYEE</th>
                        <th>CODE</th>
                        <th style="color:#1a6bff">POST OFFICE</th>
                        <th style="color:#8b5cf6">LEOPARD</th>
                        <th style="color:#ef4444">TCS</th>
                        <th style="color:#f59e0b">LOCAL PARCEL</th>
                        <th style="color:#ef4444">WASTE</th>
                        <th style="color:#10b981">TOTAL CONFIRMED</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($orders as $ord): ?>
                <tr>
                    <td style="font-weight:600"><?= date('d M Y', strtotime($ord['report_date'])) ?></td>
                    <td><strong><?= htmlspecialchars($ord['emp_name']) ?></strong></td>
                    <td><span class="badge badge-info"><?= $ord['employee_code'] ?></span></td>
                    <td style="font-weight:700; color:#1a6bff"><?= number_format($ord['post_office_orders']) ?></td>
                    <td style="font-weight:700; color:#8b5cf6"><?= number_format($ord['leopard_orders']) ?></td>
                    <td style="font-weight:700; color:#ef4444"><?= number_format($ord['tcs_orders']) ?></td>
                    <td style="font-weight:700; color:#f59e0b"><?= number_format($ord['local_parcel_orders']) ?></td>
                    <td style="font-weight:600; color:#ef4444"><?= number_format($ord['waste_orders']) ?></td>
                    <td style="font-weight:800; color:#10b981"><?= number_format($ord['confirmed_orders']) ?></td>
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
