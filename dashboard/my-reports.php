<?php
// dashboard/my-reports.php - User: My Reports
require_once '../config.php';
requireLogin();
if (isAdmin()) { header('Location: reports.php'); exit; }

$db  = getDB();
$uid = $_SESSION['user_id'];

$filter   = $_GET['filter'] ?? 'week';
$dateFrom = date('Y-m-d', strtotime('-6 days'));
$dateTo   = date('Y-m-d');

switch ($filter) {
    case 'today':     $dateFrom = $dateTo = date('Y-m-d'); break;
    case 'week':      $dateFrom = date('Y-m-d', strtotime('-6 days')); $dateTo = date('Y-m-d'); break;
    case 'month':     $dateFrom = date('Y-m-01'); $dateTo = date('Y-m-d'); break;
    case 'all':       $dateFrom = '2020-01-01'; $dateTo = date('Y-m-d'); break;
}

$reports = $db->query("SELECT * FROM reports WHERE user_id=$uid AND report_date BETWEEN '$dateFrom' AND '$dateTo' ORDER BY report_date DESC")->fetch_all(MYSQLI_ASSOC);
$totals  = $db->query("SELECT COALESCE(SUM(total_orders),0) as tot, COALESCE(SUM(waste_orders),0) as waste, COALESCE(SUM(confirmed_orders),0) as conf FROM reports WHERE user_id=$uid AND report_date BETWEEN '$dateFrom' AND '$dateTo'")->fetch_assoc();
$db->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Reports - Ecomedge</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body class="dash-body">
<?php include 'includes/sidebar.php'; ?>
<div class="main-content">
    <div class="topbar">
        <div class="topbar-left"><h2>📋 My Reports</h2><p>Your personal performance history</p></div>
        <div class="topbar-right"><a href="upload.php" class="filter-btn success">📤 Upload New</a></div>
    </div>
    <div class="page-body">
        <div class="cards-grid" style="grid-template-columns:repeat(3,1fr);margin-bottom:24px">
            <div class="dash-card blue"><div class="card-icon blue">📦</div><div class="card-val"><?= number_format($totals['tot']) ?></div><div class="card-label">Total Orders</div></div>
            <div class="dash-card green"><div class="card-icon green">✅</div><div class="card-val"><?= number_format($totals['conf']) ?></div><div class="card-label">Confirmed</div><div class="card-trend up"><?= $totals['tot']>0?round($totals['conf']/$totals['tot']*100):0 ?>% rate</div></div>
            <div class="dash-card red"><div class="card-icon red">❌</div><div class="card-val"><?= number_format($totals['waste']) ?></div><div class="card-label">Waste</div></div>
        </div>

        <div class="dash-panel">
            <div class="filter-row">
                <?php foreach(['today'=>'Today','week'=>'This Week','month'=>'This Month','all'=>'All Time'] as $k=>$lbl): ?>
                <a href="?filter=<?= $k ?>" class="filter-btn <?= $filter===$k?'active':'' ?>"><?= $lbl ?></a>
                <?php endforeach; ?>
            </div>
            <?php if (empty($reports)): ?>
            <div class="empty-state"><div class="empty-icon">📋</div><h3>No Reports Yet</h3><p>Upload your orders to generate reports.</p></div>
            <?php else: ?>
            <table class="dash-table">
                <thead><tr><th>Date</th><th>Total Orders</th><th>Waste</th><th>Confirmed</th><th>Confirm Rate</th></tr></thead>
                <tbody>
                <?php foreach ($reports as $rep): ?>
                    <?php $rate = $rep['total_orders']>0 ? round($rep['confirmed_orders']/$rep['total_orders']*100) : 0; ?>
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
