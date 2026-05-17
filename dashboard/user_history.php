<?php
// dashboard/user_history.php - View specific employee's report history
require_once '../config.php';
requireAdmin();

$db = getDB();
$targetUid = (int)($_GET['id'] ?? 0);

if (!$targetUid) {
    header('Location: index.php');
    exit;
}

$userRes = $db->query("SELECT * FROM users WHERE id = $targetUid");
$user = $userRes->fetch_assoc();

if (!$user) {
    die("User not found.");
}

// Stats
$allTimeStats = $db->query("
    SELECT 
        SUM(total_orders) as total,
        SUM(waste_orders) as waste,
        SUM(confirmed_orders) as confirmed,
        SUM(post_office_orders) as po,
        SUM(leopard_orders) as leo
    FROM reports 
    WHERE user_id = $targetUid
")->fetch_assoc();

// Daily History
$historyRes = $db->query("
    SELECT * FROM reports 
    WHERE user_id = $targetUid 
    ORDER BY report_date DESC
");
$history = $historyRes->fetch_all(MYSQLI_ASSOC);

$db->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee History - <?= htmlspecialchars($user['name']) ?></title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="dash-body">
    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <header class="topbar">
            <div class="topbar-left">
                <h2>Employee History</h2>
                <p>Detailed performance logs for <?= htmlspecialchars($user['name']) ?> (<?= $user['employee_code'] ?>)</p>
            </div>
            <div class="topbar-right">
                <a href="index.php" class="filter-btn">← Back to Dashboard</a>
            </div>
        </header>

        <div class="page-body">
            <!-- ALL TIME STATS -->
            <div class="cards-grid">
                <div class="dash-card blue">
                    <div class="card-icon blue">📦</div>
                    <div class="card-val"><?= number_format($allTimeStats['total'] ?? 0) ?></div>
                    <div class="card-label">Total Submissions</div>
                </div>
                <div class="dash-card green">
                    <div class="card-icon green">✅</div>
                    <div class="card-val"><?= number_format($allTimeStats['confirmed'] ?? 0) ?></div>
                    <div class="card-label">Total Confirmed</div>
                </div>
                <div class="dash-card orange">
                    <div class="card-icon orange">📮</div>
                    <div class="card-val"><?= number_format($allTimeStats['po'] ?? 0) ?></div>
                    <div class="card-label">Post Office (Total)</div>
                </div>
                <div class="dash-card purple">
                    <div class="card-icon purple">🐆</div>
                    <div class="card-val"><?= number_format($allTimeStats['leo'] ?? 0) ?></div>
                    <div class="card-label">Leopard (Total)</div>
                </div>
            </div>

            <div class="dash-panel">
                <div class="panel-header">
                    <h3>📅 Daily Performance Log</h3>
                </div>
                <div class="panel-body" style="padding:0">
                    <table class="dash-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Submission Total</th>
                                <th>Waste</th>
                                <th>Confirmed</th>
                                <th style="color:#1a6bff">Post Office</th>
                                <th style="color:#8b5cf6">Leopard</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($history)): ?>
                                <tr><td colspan="6" style="text-align:center; padding:40px;">No reports found for this employee.</td></tr>
                            <?php else: ?>
                                <?php foreach ($history as $rep): ?>
                                <tr>
                                    <td><strong><?= date('d M, Y', strtotime($rep['report_date'])) ?></strong></td>
                                    <td><?= $rep['total_orders'] ?></td>
                                    <td style="color:#ef4444"><?= $rep['waste_orders'] ?></td>
                                    <td style="color:#10b981; font-weight:700"><?= $rep['confirmed_orders'] ?></td>
                                    <td style="font-weight:600"><?= $rep['post_office_orders'] ?></td>
                                    <td style="font-weight:600"><?= $rep['leopard_orders'] ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
