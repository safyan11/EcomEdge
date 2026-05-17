<?php
// dashboard/admin_dashboard.php
require_once '../config.php';
requireAdmin();

$db = getDB();

$todayDate = date('Y-m-d');

// Totals
$totalOrders    = $db->query("SELECT COALESCE(SUM(total_orders),0) as t FROM reports WHERE report_date = '$todayDate'")->fetch_assoc()['t'];
$totalConfirmed = $db->query("SELECT COALESCE(SUM(confirmed_orders),0) as t FROM reports WHERE report_date = '$todayDate'")->fetch_assoc()['t'];
$totalWaste     = $db->query("SELECT COALESCE(SUM(waste_orders),0) as t FROM reports WHERE report_date = '$todayDate'")->fetch_assoc()['t'];
$totalUsers     = $db->query("SELECT COUNT(*) as t FROM users WHERE status='approved' AND role='user'")->fetch_assoc()['t'];
$pendingUsers   = $db->query("SELECT COUNT(*) as t FROM users WHERE status='pending'")->fetch_assoc()['t'];

// Today's Courier Breakdown Sums
$poTotalToday = $db->query("SELECT COALESCE(SUM(post_office_orders),0) as t FROM reports WHERE report_date = '$todayDate'")->fetch_assoc()['t'];
$leoTotalToday = $db->query("SELECT COALESCE(SUM(leopard_orders),0) as t FROM reports WHERE report_date = '$todayDate'")->fetch_assoc()['t'];
$tcsTotalToday = $db->query("SELECT COALESCE(SUM(tcs_orders),0) as t FROM reports WHERE report_date = '$todayDate'")->fetch_assoc()['t'];
$lpTotalToday = $db->query("SELECT COALESCE(SUM(local_parcel_orders),0) as t FROM reports WHERE report_date = '$todayDate'")->fetch_assoc()['t'];

// Employee Performance Today
$perfQuery = $db->query("
    SELECT u.id, u.name, u.employee_code,
           COALESCE(r.total_orders,0) as total,
           COALESCE(r.waste_orders,0) as waste,
           COALESCE(r.confirmed_orders,0) as confirmed,
           COALESCE(r.post_office_orders,0) as post_office,
           COALESCE(r.leopard_orders,0) as leopard,
           COALESCE(r.tcs_orders,0) as tcs,
           COALESCE(r.local_parcel_orders,0) as local_parcel
    FROM users u
    LEFT JOIN reports r ON r.user_id=u.id AND r.report_date='$todayDate'
    WHERE u.role='user' AND u.status='approved'
    GROUP BY u.id
    ORDER BY confirmed DESC
");
$employees = $perfQuery->fetch_all(MYSQLI_ASSOC);

// Recent Orders
$recentOrders = $db->query("
    SELECT co.order_id, co.courier, u.name as emp_name, u.employee_code, co.created_at
    FROM confirmed_orders co
    JOIN users u ON u.id = co.uploaded_by
    WHERE co.order_date = '$todayDate'
    ORDER BY co.created_at DESC
    LIMIT 10
")->fetch_all(MYSQLI_ASSOC);

// Courier breakdown
$courierStats = $db->query("
    SELECT courier, COUNT(*) as cnt
    FROM confirmed_orders
    WHERE order_date = '$todayDate'
    GROUP BY courier
    ORDER BY cnt DESC
")->fetch_all(MYSQLI_ASSOC);

$db->close();

$today = date('D, d M Y');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Ecomedge</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body class="dash-body">

<?php include 'includes/sidebar.php'; ?>

<div class="main-content">
    <!-- TOPBAR -->
    <div class="topbar">
        <div class="topbar-left">
            <h2>Admin Dashboard</h2>
            <p>Welcome back, <?= htmlspecialchars($_SESSION['user_name']) ?> 👋</p>
        </div>
        <div class="topbar-right">
            <span class="topbar-date">📅 <?= $today ?></span>
            <?php if ($pendingUsers > 0): ?>
            <a href="pending.php" style="background:#fef3c7;color:#92400e;padding:6px 14px;border-radius:6px;font-size:13px;font-weight:600;text-decoration:none;border:1px solid #fcd34d;">
                ⏳ <?= $pendingUsers ?> Pending
            </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="page-body">

        <!-- STAT CARDS -->
        <div class="cards-grid">
            <div class="dash-card blue">
                <div class="card-icon blue">📦</div>
                <div class="card-val"><?= number_format($totalOrders) ?></div>
                <div class="card-label">Total Orders Today</div>
                <div class="card-trend up">↑ Today's entries</div>
            </div>
            <div class="dash-card green">
                <div class="card-icon green">✅</div>
                <div class="card-val"><?= number_format($totalConfirmed) ?></div>
                <div class="card-label">Confirmed Orders</div>
                <div class="card-trend up">
                    <?= $totalOrders > 0 ? round($totalConfirmed/$totalOrders*100) : 0 ?>% confirm rate
                </div>
            </div>
            <div class="dash-card red">
                <div class="card-icon red">❌</div>
                <div class="card-val"><?= number_format($totalWaste) ?></div>
                <div class="card-label">Waste Orders</div>
                <div class="card-trend down">
                    <?= $totalOrders > 0 ? round($totalWaste/$totalOrders*100) : 0 ?>% waste rate
                </div>
            </div>
            <div class="dash-card purple">
                <div class="card-icon purple">👥</div>
                <div class="card-val"><?= $totalUsers ?></div>
                <div class="card-label">Active Employees</div>
                <?php if ($pendingUsers > 0): ?>
                <div class="card-trend" style="color:#f59e0b">⏳ <?= $pendingUsers ?> pending</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- TODAY'S COURIER TOTALS -->
        <div style="margin: 20px 0 12px 4px; font-size: 13px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Today's Courier Performance</div>
        <div class="cards-grid" style="margin-bottom: 28px;">
            <div class="dash-card" style="border-left: 4px solid #1a6bff;">
                <div style="font-size: 12px; color: #64748b; margin-bottom: 5px;">📬 Post Office Total</div>
                <div style="font-size: 24px; font-weight: 800; color: #1a6bff;"><?= number_format($poTotalToday) ?></div>
            </div>
            <div class="dash-card" style="border-left: 4px solid #8b5cf6;">
                <div style="font-size: 12px; color: #64748b; margin-bottom: 5px;">🐆 Leopard Total</div>
                <div style="font-size: 24px; font-weight: 800; color: #8b5cf6;"><?= number_format($leoTotalToday) ?></div>
            </div>
            <div class="dash-card" style="border-left: 4px solid #ef4444;">
                <div style="font-size: 12px; color: #64748b; margin-bottom: 5px;">📦 TCS Total</div>
                <div style="font-size: 24px; font-weight: 800; color: #ef4444;"><?= number_format($tcsTotalToday) ?></div>
            </div>
            <div class="dash-card" style="border-left: 4px solid #f59e0b;">
                <div style="font-size: 12px; color: #64748b; margin-bottom: 5px;">📍 Local Parcel Total</div>
                <div style="font-size: 24px; font-weight: 800; color: #f59e0b;"><?= number_format($lpTotalToday) ?></div>
            </div>
        </div>

        <div class="grid-2">
            <!-- EMPLOYEE PERFORMANCE -->
            <div class="dash-panel">
                <div class="panel-header">
                    <h3>👥 Employee Performance — Today</h3>
                    <a href="reports.php" style="font-size:13px;color:#1a6bff;text-decoration:none;">View All →</a>
                </div>
                <?php if (empty($employees)): ?>
                <div class="empty-state"><div class="empty-icon">👥</div><h3>No Data Yet</h3><p>No reports submitted today.</p></div>
                <?php else: ?>
                <table class="dash-table">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Code</th>
                            <th>Total</th>
                            <th>Waste</th>
                            <th>Confirmed</th>
                            <th>Post Office</th>
                            <th>Leopard</th>
                            <th>TCS</th>
                            <th>Local Parcel</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($employees as $emp): ?>
                        <tr>
                            <td><a href="user_history.php?id=<?= $emp['id'] ?>" style="text-decoration:none;color:inherit;border-bottom:1px solid #e2e8f0;"><strong><?= htmlspecialchars($emp['name']) ?></strong></a></td>
                            <td><span class="badge badge-info"><?= $emp['employee_code'] ?></span></td>
                            <td><?= $emp['total'] ?></td>
                            <td><span style="color:#ef4444;font-weight:600"><?= $emp['waste'] ?></span></td>
                            <td><span style="color:#10b981;font-weight:700"><?= $emp['confirmed'] ?></span></td>
                            <td style="font-weight:600;color:#1a6bff"><?= $emp['post_office'] ?></td>
                            <td style="font-weight:600;color:#8b5cf6"><?= $emp['leopard'] ?></td>
                            <td style="font-weight:600;color:#ef4444"><?= $emp['tcs'] ?></td>
                            <td style="font-weight:600;color:#f59e0b"><?= $emp['local_parcel'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>

            <!-- COURIER BREAKDOWN -->
            <div class="dash-panel">
                <div class="panel-header">
                    <h3>🚚 Courier Breakdown — Today</h3>
                </div>
                <div class="panel-body">
                <?php if (empty($courierStats)): ?>
                    <div class="empty-state"><div class="empty-icon">🚚</div><h3>No orders yet</h3><p>No confirmed orders uploaded today.</p></div>
                <?php else: ?>
                    <?php
                    $maxCnt = max(array_column($courierStats, 'cnt'));
                    $colors = ['#1a6bff','#10b981','#f59e0b','#8b5cf6','#ef4444'];
                    foreach ($courierStats as $i => $c):
                        $pct = round($c['cnt']/$maxCnt*100);
                        $col = $colors[$i % count($colors)];
                    ?>
                    <div style="margin-bottom:18px">
                        <div style="display:flex;justify-content:space-between;margin-bottom:6px">
                            <span style="font-weight:600;font-size:14px;color:#334155"><?= htmlspecialchars($c['courier']) ?></span>
                            <span style="font-weight:700;font-size:14px;color:<?= $col ?>"><?= $c['cnt'] ?> orders</span>
                        </div>
                        <div style="height:10px;background:#f0f4ff;border-radius:5px;overflow:hidden">
                            <div style="width:<?= $pct ?>%;height:100%;background:<?= $col ?>;border-radius:5px;transition:width 1s ease"></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- RECENT ORDERS TABLE -->
        <div class="dash-panel">
            <div class="panel-header">
                <h3>📦 Recent Confirmed Orders — Today</h3>
                <a href="orders.php" style="font-size:13px;color:#1a6bff;text-decoration:none;">View All →</a>
            </div>
            <?php if (empty($recentOrders)): ?>
            <div class="empty-state"><div class="empty-icon">📦</div><h3>No orders yet</h3><p>No confirmed orders uploaded today.</p></div>
            <?php else: ?>
            <table class="dash-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Order ID</th>
                        <th>Courier</th>
                        <th>Employee</th>
                        <th>Code</th>
                        <th>Time</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($recentOrders as $i => $ord): ?>
                    <tr>
                        <td style="color:#94a3b8"><?= $i+1 ?></td>
                        <td><strong style="font-family:monospace"><?= htmlspecialchars($ord['order_id']) ?></strong></td>
                        <td><span class="badge badge-info"><?= htmlspecialchars($ord['courier']) ?></span></td>
                        <td><?= htmlspecialchars($ord['emp_name']) ?></td>
                        <td><span class="badge badge-purple"><?= $ord['employee_code'] ?></span></td>
                        <td style="color:#94a3b8;font-size:12px"><?= date('h:i A', strtotime($ord['created_at'])) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>

    </div><!-- /page-body -->
</div><!-- /main-content -->
</body>
</html>
