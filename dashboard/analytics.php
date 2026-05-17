<?php
// dashboard/analytics.php - Admin: Analytics
require_once '../config.php';
requireAdmin();

$db = getDB();

// Last 7 days daily data
$daily = $db->query("
    SELECT report_date,
           SUM(total_orders) as total,
           SUM(waste_orders) as waste,
           SUM(confirmed_orders) as confirmed
    FROM reports
    WHERE report_date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
    GROUP BY report_date
    ORDER BY report_date ASC
")->fetch_all(MYSQLI_ASSOC);

// Courier stats (all time)
$courierStats = $db->query("
    SELECT courier, COUNT(*) as cnt
    FROM confirmed_orders
    GROUP BY courier
    ORDER BY cnt DESC
")->fetch_all(MYSQLI_ASSOC);

// Employee all-time performance
$empPerf = $db->query("
    SELECT u.name, u.employee_code,
           COALESCE(SUM(r.total_orders),0) as total,
           COALESCE(SUM(r.waste_orders),0) as waste,
           COALESCE(SUM(r.confirmed_orders),0) as confirmed
    FROM users u
    LEFT JOIN reports r ON r.user_id = u.id
    WHERE u.status='approved'
    GROUP BY u.id
    ORDER BY confirmed DESC
")->fetch_all(MYSQLI_ASSOC);

// Monthly overview (last 6 months)
$monthly = $db->query("
    SELECT DATE_FORMAT(report_date,'%b %Y') as month,
           DATE_FORMAT(report_date,'%Y-%m') as ym,
           SUM(total_orders) as total,
           SUM(confirmed_orders) as confirmed
    FROM reports
    WHERE report_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(report_date,'%Y-%m')
    ORDER BY ym ASC
")->fetch_all(MYSQLI_ASSOC);

// Totals
$allTime = $db->query("SELECT COALESCE(SUM(total_orders),0) as tot, COALESCE(SUM(waste_orders),0) as waste, COALESCE(SUM(confirmed_orders),0) as conf FROM reports")->fetch_assoc();
$totalCouriers = $db->query("SELECT COUNT(DISTINCT courier) as c FROM confirmed_orders")->fetch_assoc()['c'];
$db->close();

// Prepare chart data
$chartLabels   = array_column($daily, 'report_date');
$chartTotal    = array_column($daily, 'total');
$chartConfirmed = array_column($daily, 'confirmed');
$chartWaste    = array_column($daily, 'waste');

$courierLabels = array_column($courierStats, 'courier');
$courierCounts = array_column($courierStats, 'cnt');
$courierTotal  = array_sum($courierCounts);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics - Ecomedge</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body class="dash-body">
<?php include 'includes/sidebar.php'; ?>
<div class="main-content">
    <div class="topbar">
        <div class="topbar-left"><h2>📈 Analytics</h2><p>Performance overview and insights</p></div>
        <div class="topbar-right"><span class="topbar-date">📅 <?= date('D, d M Y') ?></span></div>
    </div>

    <div class="page-body">

        <!-- All-Time Cards -->
        <div class="cards-grid" style="margin-bottom:28px">
            <div class="dash-card blue">
                <div class="card-icon blue">📦</div>
                <div class="card-val"><?= number_format($allTime['tot']) ?></div>
                <div class="card-label">All-Time Total Orders</div>
            </div>
            <div class="dash-card green">
                <div class="card-icon green">✅</div>
                <div class="card-val"><?= number_format($allTime['conf']) ?></div>
                <div class="card-label">All-Time Confirmed</div>
                <div class="card-trend up"><?= $allTime['tot']>0 ? round($allTime['conf']/$allTime['tot']*100) : 0 ?>% success</div>
            </div>
            <div class="dash-card red">
                <div class="card-icon red">❌</div>
                <div class="card-val"><?= number_format($allTime['waste']) ?></div>
                <div class="card-label">All-Time Waste</div>
            </div>
            <div class="dash-card purple">
                <div class="card-icon purple">🚚</div>
                <div class="card-val"><?= $totalCouriers ?></div>
                <div class="card-label">Active Couriers</div>
            </div>
        </div>

        <div class="grid-2">
            <!-- 7-Day Line Chart -->
            <div class="dash-panel">
                <div class="panel-header"><h3>📊 Last 7 Days — Orders Trend</h3></div>
                <div class="panel-body">
                    <?php if (empty($daily)): ?>
                    <div class="empty-state"><div class="empty-icon">📊</div><h3>No Data Yet</h3></div>
                    <?php else: ?>
                    <canvas id="lineChart" height="220"></canvas>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Courier Doughnut -->
            <div class="dash-panel">
                <div class="panel-header"><h3>🚚 Courier Distribution</h3></div>
                <div class="panel-body">
                    <?php if (empty($courierStats)): ?>
                    <div class="empty-state"><div class="empty-icon">🚚</div><h3>No Data Yet</h3></div>
                    <?php else: ?>
                    <div style="display:flex;align-items:center;gap:24px;flex-wrap:wrap">
                        <div style="flex:0 0 220px"><canvas id="doughnutChart" height="220"></canvas></div>
                        <div style="flex:1;min-width:140px">
                            <?php
                            $dColors = ['#1a6bff','#10b981','#f59e0b','#8b5cf6','#ef4444'];
                            foreach ($courierStats as $ci => $c):
                                $pct = $courierTotal > 0 ? round($c['cnt']/$courierTotal*100) : 0;
                                $col = $dColors[$ci % count($dColors)];
                            ?>
                            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
                                <div style="display:flex;align-items:center;gap:8px">
                                    <div style="width:10px;height:10px;border-radius:50%;background:<?= $col ?>"></div>
                                    <span style="font-size:13px;font-weight:600;color:#334155"><?= htmlspecialchars($c['courier']) ?></span>
                                </div>
                                <span style="font-size:13px;font-weight:700;color:<?= $col ?>"><?= $pct ?>%</span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Employee All-Time Performance -->
        <div class="dash-panel">
            <div class="panel-header"><h3>👥 All-Time Employee Performance</h3></div>
            <?php if (empty($empPerf)): ?>
            <div class="empty-state"><div class="empty-icon">👥</div><h3>No Data Yet</h3></div>
            <?php else: ?>
            <table class="dash-table">
                <thead><tr><th>Rank</th><th>Employee</th><th>Code</th><th>Total Orders</th><th>Waste</th><th>Confirmed</th><th>Success Rate</th></tr></thead>
                <tbody>
                <?php foreach ($empPerf as $rank => $emp):
                    $rate = $emp['total'] > 0 ? round($emp['confirmed']/$emp['total']*100) : 0;
                    $medals = ['🥇','🥈','🥉'];
                ?>
                <tr>
                    <td style="font-size:20px"><?= $medals[$rank] ?? ($rank+1) ?></td>
                    <td><strong><?= htmlspecialchars($emp['name']) ?></strong></td>
                    <td><span class="badge badge-info"><?= $emp['employee_code'] ?></span></td>
                    <td><?= number_format($emp['total']) ?></td>
                    <td><span style="color:#ef4444;font-weight:600"><?= number_format($emp['waste']) ?></span></td>
                    <td><span style="color:#10b981;font-weight:700"><?= number_format($emp['confirmed']) ?></span></td>
                    <td>
                        <div style="display:flex;align-items:center;gap:8px">
                            <div style="width:80px;height:8px;background:#f0f4ff;border-radius:4px;overflow:hidden">
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

<script>
<?php if (!empty($daily)): ?>
// Line Chart
new Chart(document.getElementById('lineChart'), {
    type: 'line',
    data: {
        labels: <?= json_encode(array_map(fn($d) => date('d M', strtotime($d)), $chartLabels)) ?>,
        datasets: [
            {
                label: 'Total',
                data: <?= json_encode(array_map('intval', $chartTotal)) ?>,
                borderColor: '#1a6bff', backgroundColor: 'rgba(26,107,255,0.08)',
                borderWidth: 2.5, tension: 0.4, fill: true, pointRadius: 5, pointBackgroundColor: '#1a6bff'
            },
            {
                label: 'Confirmed',
                data: <?= json_encode(array_map('intval', $chartConfirmed)) ?>,
                borderColor: '#10b981', backgroundColor: 'rgba(16,185,129,0.06)',
                borderWidth: 2.5, tension: 0.4, fill: true, pointRadius: 5, pointBackgroundColor: '#10b981'
            },
            {
                label: 'Waste',
                data: <?= json_encode(array_map('intval', $chartWaste)) ?>,
                borderColor: '#ef4444', backgroundColor: 'rgba(239,68,68,0.05)',
                borderWidth: 2, tension: 0.4, fill: true, pointRadius: 4, pointBackgroundColor: '#ef4444'
            }
        ]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { labels: { font: { family: 'Inter', size: 12 } } } },
        scales: {
            x: { grid: { color: '#f0f4ff' }, ticks: { font: { family: 'Inter', size: 11 } } },
            y: { grid: { color: '#f0f4ff' }, ticks: { font: { family: 'Inter', size: 11 } }, beginAtZero: true }
        }
    }
});
<?php endif; ?>

<?php if (!empty($courierStats)): ?>
// Doughnut Chart
new Chart(document.getElementById('doughnutChart'), {
    type: 'doughnut',
    data: {
        labels: <?= json_encode($courierLabels) ?>,
        datasets: [{
            data: <?= json_encode(array_map('intval', $courierCounts)) ?>,
            backgroundColor: ['#1a6bff','#10b981','#f59e0b','#8b5cf6','#ef4444'],
            borderWidth: 3, borderColor: '#fff',
            hoverOffset: 8
        }]
    },
    options: {
        responsive: true,
        cutout: '65%',
        plugins: {
            legend: { display: false },
            tooltip: { callbacks: {
                label: function(ctx) {
                    const total = ctx.dataset.data.reduce((a,b) => a+b, 0);
                    return ` ${ctx.label}: ${ctx.raw} (${Math.round(ctx.raw/total*100)}%)`;
                }
            }}
        }
    }
});
<?php endif; ?>
</script>
</body>
</html>
