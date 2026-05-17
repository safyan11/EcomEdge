<?php
// dashboard/user_dashboard.php
require_once '../config.php';
requireLogin();

$db = getDB();
$uid = $_SESSION['user_id'];
$code = $_SESSION['employee_code'];

$todayDate = date('Y-m-d');

// My Today's report
$todayReport = $db->query("SELECT * FROM reports WHERE user_id=$uid AND report_date='$todayDate' ORDER BY id DESC LIMIT 1")->fetch_assoc();

// My all-time stats
$allStats = $db->query("SELECT 
    COALESCE(SUM(total_orders),0) as tot, 
    COALESCE(SUM(waste_orders),0) as waste, 
    COALESCE(SUM(confirmed_orders),0) as conf,
    COALESCE(SUM(post_office_orders),0) as po_tot,
    COALESCE(SUM(leopard_orders),0) as leo_tot,
    COALESCE(SUM(tcs_orders),0) as tcs_tot,
    COALESCE(SUM(local_parcel_orders),0) as lp_tot
FROM reports WHERE user_id=$uid")->fetch_assoc();

// My recent reports (last 7)
$myReports = $db->query("SELECT * FROM reports WHERE user_id=$uid ORDER BY report_date DESC LIMIT 7")->fetch_all(MYSQLI_ASSOC);

// My today's orders count
$todayOrders = $db->query("SELECT COUNT(*) as c FROM confirmed_orders WHERE uploaded_by=$uid AND order_date='$todayDate'")->fetch_assoc()['c'];

$db->close();
$today = date('D, d M Y');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard - Ecomedge</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body class="dash-body">

<?php include 'includes/sidebar.php'; ?>

<div class="main-content">
    <div class="topbar">
        <div class="topbar-left">
            <h2>My Dashboard</h2>
            <p>Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?> (<?= $code ?>) 👋</p>
        </div>
        <div class="topbar-right">
            <span class="topbar-date">📅 <?= $today ?></span>
            <a href="upload.php" class="filter-btn success">📤 Upload Orders</a>
        </div>
    </div>

    <div class="page-body">

        <!-- TODAY'S STATUS -->
        <?php if ($todayReport): ?>
        <div style="background:linear-gradient(135deg,#0d1b4b,#1a3a7a);border-radius:14px;padding:24px;margin-bottom:24px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px;">
            <div>
                <div style="color:rgba(255,255,255,0.6);font-size:13px;margin-bottom:4px">Today's Report Status</div>
                <div style="color:white;font-size:20px;font-weight:700">✅ Report Submitted</div>
            </div>
            <div style="display:flex;gap:20px;flex-wrap:wrap">
                <div style="text-align:center"><div style="color:#00c6ff;font-size:24px;font-weight:800"><?= $todayReport['total_orders'] ?></div><div style="color:rgba(255,255,255,0.6);font-size:12px">Total</div></div>
                <div style="text-align:center"><div style="color:#10b981;font-size:24px;font-weight:800"><?= $todayReport['confirmed_orders'] ?></div><div style="color:rgba(255,255,255,0.6);font-size:12px">Confirmed</div></div>
                <div style="text-align:center"><div style="color:#f87171;font-size:24px;font-weight:800"><?= $todayReport['waste_orders'] ?></div><div style="color:rgba(255,255,255,0.6);font-size:12px">Waste</div></div>
            </div>
            <div style="width:100%; height:1px; background:rgba(255,255,255,0.1); margin:10px 0"></div>
            <div style="width:100%; display:flex; gap:15px; flex-wrap:wrap; color:white; font-size:12px">
                <span style="opacity:0.7">Breakdown:</span>
                <span>📬 PO: <strong><?= $todayReport['post_office_orders'] ?></strong></span>
                <span>🐆 Leo: <strong><?= $todayReport['leopard_orders'] ?></strong></span>
                <span>📦 TCS: <strong><?= $todayReport['tcs_orders'] ?></strong></span>
                <span>📍 Local: <strong><?= $todayReport['local_parcel_orders'] ?></strong></span>
            </div>
        </div>
        <?php else: ?>
        <div style="background:#fef3c7;border:1px solid #fcd34d;border-radius:14px;padding:20px 24px;margin-bottom:24px;display:flex;align-items:center;gap:16px;">
            <span style="font-size:32px">⚠️</span>
            <div>
                <strong style="color:#92400e;font-size:15px">Today's Report Not Submitted</strong>
                <div style="color:#b45309;font-size:13px">Please upload your confirmed orders and report for today.</div>
            </div>
            <a href="upload.php" style="margin-left:auto" class="filter-btn success">Upload Now →</a>
        </div>
        <?php endif; ?>

        <!-- MY STATS CARDS -->
        <div class="cards-grid">
            <div class="dash-card blue">
                <div class="card-icon blue">📦</div>
                <div class="card-val"><?= number_format($allStats['tot']) ?></div>
                <div class="card-label">Total Orders (All Time)</div>
            </div>
            <div class="dash-card green">
                <div class="card-icon green">✅</div>
                <div class="card-val"><?= number_format($allStats['conf']) ?></div>
                <div class="card-label">Total Confirmed</div>
                <div class="card-trend up"><?= $allStats['tot'] > 0 ? round($allStats['conf']/$allStats['tot']*100) : 0 ?>% success rate</div>
            </div>
            <div class="dash-card red">
                <div class="card-icon red">❌</div>
                <div class="card-val"><?= number_format($allStats['waste']) ?></div>
                <div class="card-label">Total Waste</div>
            </div>
            <div class="dash-card purple">
                <div class="card-icon purple">🚚</div>
                <div class="card-val"><?= $todayOrders ?></div>
                <div class="card-label">Orders Uploaded Today</div>
            </div>
        </div>

        <!-- COURIER STATS CARDS -->
        <div style="margin: 0 0 12px 4px; font-size: 13px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">All-Time Courier Breakdown</div>
        <div class="cards-grid" style="margin-bottom: 28px;">
            <div class="dash-card" style="border-left: 4px solid #1a6bff;">
                <div style="font-size: 12px; color: #64748b; margin-bottom: 5px;">📬 Post Office</div>
                <div style="font-size: 24px; font-weight: 800; color: #1a6bff;"><?= number_format($allStats['po_tot']) ?></div>
            </div>
            <div class="dash-card" style="border-left: 4px solid #8b5cf6;">
                <div style="font-size: 12px; color: #64748b; margin-bottom: 5px;">🐆 Leopard</div>
                <div style="font-size: 24px; font-weight: 800; color: #8b5cf6;"><?= number_format($allStats['leo_tot']) ?></div>
            </div>
            <div class="dash-card" style="border-left: 4px solid #ef4444;">
                <div style="font-size: 12px; color: #64748b; margin-bottom: 5px;">📦 TCS</div>
                <div style="font-size: 24px; font-weight: 800; color: #ef4444;"><?= number_format($allStats['tcs_tot']) ?></div>
            </div>
            <div class="dash-card" style="border-left: 4px solid #f59e0b;">
                <div style="font-size: 12px; color: #64748b; margin-bottom: 5px;">📍 Local Parcel</div>
                <div style="font-size: 24px; font-weight: 800; color: #f59e0b;"><?= number_format($allStats['lp_tot']) ?></div>
            </div>
        </div>

        <!-- RECENT REPORTS TABLE -->
        <div class="dash-panel">
            <div class="panel-header">
                <h3>📋 My Recent Reports (Last 7 Days)</h3>
                <a href="my-reports.php" style="font-size:13px;color:#1a6bff;text-decoration:none;">View All →</a>
            </div>
            <?php if (empty($myReports)): ?>
            <div class="empty-state">
                <div class="empty-icon">📋</div>
                <h3>No Reports Yet</h3>
                <p>Upload your first confirmed orders to see your stats here.</p>
                <a href="upload.php" class="filter-btn success" style="margin-top:16px;display:inline-block">📤 Upload Now</a>
            </div>
            <?php else: ?>
            <table class="dash-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Total Orders</th>
                        <th>Waste</th>
                        <th>Confirmed</th>
                        <th>Confirm Rate</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($myReports as $rep): ?>
                    <?php $rate = $rep['total_orders'] > 0 ? round($rep['confirmed_orders']/$rep['total_orders']*100) : 0; ?>
                    <tr>
                        <td><strong><?= date('D, d M Y', strtotime($rep['report_date'])) ?></strong></td>
                        <td><?= $rep['total_orders'] ?></td>
                        <td><span style="color:#ef4444;font-weight:600"><?= $rep['waste_orders'] ?></span></td>
                        <td><span style="color:#10b981;font-weight:700"><?= $rep['confirmed_orders'] ?></span></td>
                        <td>
                            <div style="display:flex;align-items:center;gap:8px">
                                <div style="width:70px;height:7px;background:#f0f4ff;border-radius:4px;overflow:hidden">
                                    <div style="width:<?= $rate ?>%;height:100%;background:linear-gradient(90deg,#1a6bff,#00c6ff);border-radius:4px"></div>
                                </div>
                                <span style="font-size:13px;font-weight:700;color:#<?= $rate>=80?'10b981':($rate>=50?'f59e0b':'ef4444') ?>"><?= $rate ?>%</span>
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
