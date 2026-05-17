<?php
// dashboard/reports.php - Admin: All Reports with Date Filters
require_once '../config.php';
requireAdmin();

$db = getDB();

$filter   = $_GET['filter'] ?? 'today';
$dateFrom = $_GET['from'] ?? date('Y-m-d');
$dateTo   = $_GET['to']   ?? date('Y-m-d');

switch ($filter) {
    case 'today':     $dateFrom = $dateTo = date('Y-m-d'); break;
    case 'yesterday': $dateFrom = $dateTo = date('Y-m-d', strtotime('-1 day')); break;
    case 'week':      $dateFrom = date('Y-m-d', strtotime('-6 days')); $dateTo = date('Y-m-d'); break;
    case 'month':     $dateFrom = date('Y-m-01'); $dateTo = date('Y-m-d'); break;
    case 'custom':    break;
}

$reports = $db->query("
    SELECT r.*, u.name, u.employee_code
    FROM reports r
    JOIN users u ON u.id = r.user_id
    WHERE r.report_date BETWEEN '$dateFrom' AND '$dateTo'
    ORDER BY r.report_date DESC, r.id DESC
")->fetch_all(MYSQLI_ASSOC);

// Totals for this period
$totals = $db->query("
    SELECT COALESCE(SUM(total_orders),0) as tot,
           COALESCE(SUM(waste_orders),0) as waste,
           COALESCE(SUM(confirmed_orders),0) as conf
    FROM reports
    WHERE report_date BETWEEN '$dateFrom' AND '$dateTo'
")->fetch_assoc();

$db->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Ecomedge</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body class="dash-body">
<?php include 'includes/sidebar.php'; ?>

<div class="main-content">
    <div class="topbar">
        <div class="topbar-left">
            <h2>📋 Reports</h2>
            <p><?= date('d M Y', strtotime($dateFrom)) ?> — <?= date('d M Y', strtotime($dateTo)) ?></p>
        </div>
        <div class="topbar-right">
            <span class="topbar-date">📅 <?= date('D, d M Y') ?></span>
        </div>
    </div>

    <div class="page-body">

        <!-- FILTER BUTTONS -->
        <div class="dash-panel" style="margin-bottom:24px">
            <form method="GET" action="">
                <div class="filter-row">
                    <?php foreach(['today'=>'Today','yesterday'=>'Yesterday','week'=>'This Week','month'=>'This Month','custom'=>'Custom'] as $k=>$label): ?>
                    <button type="submit" name="filter" value="<?= $k ?>" class="filter-btn <?= $filter===$k?'active':'' ?>"><?= $label ?></button>
                    <?php endforeach; ?>
                    <div style="display:flex;gap:8px;align-items:center;margin-left:auto;flex-wrap:wrap">
                        <input type="date" name="from" value="<?= $dateFrom ?>" style="padding:6px 10px;border:1.5px solid #e2e8f0;border-radius:6px;font-size:13px;font-family:inherit">
                        <span style="color:#64748b;font-size:13px">to</span>
                        <input type="date" name="to"   value="<?= $dateTo   ?>" style="padding:6px 10px;border:1.5px solid #e2e8f0;border-radius:6px;font-size:13px;font-family:inherit">
                        <button type="submit" name="filter" value="custom" class="filter-btn active">🔍 Go</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- SUMMARY CARDS -->
        <div class="cards-grid" style="grid-template-columns:repeat(3,1fr);margin-bottom:24px">
            <div class="dash-card blue">
                <div class="card-icon blue">📦</div>
                <div class="card-val"><?= number_format($totals['tot']) ?></div>
                <div class="card-label">Total Orders</div>
            </div>
            <div class="dash-card green">
                <div class="card-icon green">✅</div>
                <div class="card-val"><?= number_format($totals['conf']) ?></div>
                <div class="card-label">Confirmed Orders</div>
                <div class="card-trend up"><?= $totals['tot']>0 ? round($totals['conf']/$totals['tot']*100) : 0 ?>% rate</div>
            </div>
            <div class="dash-card red">
                <div class="card-icon red">❌</div>
                <div class="card-val"><?= number_format($totals['waste']) ?></div>
                <div class="card-label">Waste Orders</div>
            </div>
        </div>

        <!-- REPORTS TABLE -->
        <div class="dash-panel">
            <div class="panel-header">
                <h3>📋 Report Entries (<?= count($reports) ?>)</h3>
            </div>
            <?php if (empty($reports)): ?>
            <div class="empty-state">
                <div class="empty-icon">📋</div>
                <h3>No Reports Found</h3>
                <p>No reports submitted for the selected date range.</p>
            </div>
            <?php else: ?>
            <table class="dash-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Employee</th>
                        <th>Code</th>
                        <th>Total</th>
                        <th>Waste</th>
                        <th>Confirmed</th>
                        <th>Rate</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($reports as $rep): ?>
                    <?php $rate = $rep['total_orders'] > 0 ? round($rep['confirmed_orders']/$rep['total_orders']*100) : 0; ?>
                    <tr>
                        <td><strong><?= date('d M Y', strtotime($rep['report_date'])) ?></strong></td>
                        <td><?= htmlspecialchars($rep['name']) ?></td>
                        <td><span class="badge badge-info"><?= $rep['employee_code'] ?></span></td>
                        <td><?= $rep['total_orders'] ?></td>
                        <td><span style="color:#ef4444;font-weight:600"><?= $rep['waste_orders'] ?></span></td>
                        <td><span style="color:#10b981;font-weight:700"><?= $rep['confirmed_orders'] ?></span></td>
                        <td>
                            <div style="display:flex;align-items:center;gap:8px">
                                <div style="width:60px;height:7px;background:#f0f4ff;border-radius:4px;overflow:hidden">
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
