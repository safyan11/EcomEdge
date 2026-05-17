<?php
// dashboard/upload.php - User uploads confirmed orders
require_once '../config.php';
requireLogin();
if (isAdmin()) { header('Location: index.php'); exit; }

$db = getDB();
$uid  = $_SESSION['user_id'];
$code = $_SESSION['employee_code'];

$success = $error = '';
$inserted = 0;

$couriers = ['POST OFFICE', 'LEOPARD', 'TCS', 'LOCAL PARCEL'];

$today = date('Y-m-d');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $totalOrders = (int)($_POST['total_orders'] ?? 0);
    $wasteOrders = (int)($_POST['waste_orders'] ?? 0);
    
    $allValidOrders = [];
    $confirmedCount = 0;
    $poCount = 0;
    $leoCount = 0;
    $tcsCount = 0;
    $lpCount = 0;

    // Clean code for flexible matching (e.g., EE-02 becomes EE02)
    $cleanCode = preg_replace('/[^A-Za-z0-9]/', '', $code);

    foreach ($couriers as $c) {
        $key = str_replace(' ', '_', strtolower($c)) . '_text';
        $rawText = trim($_POST[$key] ?? '');
        if ($rawText) {
            $lines = array_filter(array_map('trim', explode("\n", $rawText)));
            foreach ($lines as $l) {
                // Check if line contains the code (leniently)
                $cleanLine = preg_replace('/[^A-Za-z0-9]/', '', $l);
                if (stripos($cleanLine, $cleanCode) !== false) {
                    $confirmedCount++;
                    
                    if ($c === 'POST OFFICE') $poCount++;
                    elseif ($c === 'LEOPARD') $leoCount++;
                    elseif ($c === 'TCS') $tcsCount++;
                    elseif ($c === 'LOCAL PARCEL') $lpCount++;
                    
                    // Extract potential Order ID: 
                    // 1. Look for sequences of 8-20 alphanumeric characters
                    // 2. Prioritize ones containing digits
                    $orderId = '';
                    if (preg_match('/([A-Z0-9]{8,20})/', strtoupper($l), $matches)) {
                        foreach ($matches as $m) {
                            if (preg_match('/\d/', $m)) { // must contain at least one digit
                                $orderId = $m;
                                break;
                            }
                        }
                    }
                    
                    if (!$orderId) $orderId = $l; // fallback to whole line
                    
                    $orderData = [
                        'order_id' => trim($orderId),
                        'courier'  => $c,
                        'name'     => '',
                        'phone'    => '',
                        'city'     => '',
                        'amount'   => 0
                    ];

                    // Extract phone (any 11 digits starting with 03)
                    if (preg_match('/(03\d{9})/', $l, $m)) $orderData['phone'] = $m[1];
                    
                    // Extract amount (3-5 digits not part of order ID)
                    $tempLine = str_replace($orderId, '', $l);
                    if (preg_match('/\b(\d{3,5})\b/', $tempLine, $m)) $orderData['amount'] = $m[1];

                    $allValidOrders[] = $orderData;
                }
            }
        }
    }

    if ($confirmedCount === 0 && !$totalOrders && !$wasteOrders) {
        $error = 'Please enter some data (Total, Waste, or Paste Orders).';
    } elseif ($totalOrders > 0 && $wasteOrders > $totalOrders) {
        $error = 'Waste orders cannot be more than total orders.';
    } else {
        // Insert individual orders with details (using IGNORE to avoid error on duplicates but still process all)
        $stmt = $db->prepare("INSERT IGNORE INTO confirmed_orders (order_id, courier, employee_code, uploaded_by, order_date, consignee_name, consignee_phone, destination, cod_amount) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        foreach ($allValidOrders as $ord) {
            $stmt->bind_param('sssissssd', $ord['order_id'], $ord['courier'], $code, $uid, $today, $ord['name'], $ord['phone'], $ord['city'], $ord['amount']);
            $stmt->execute();
        }

        // Save/update daily report with courier counts (INCREMENT raw detected counts)
        $existingReport = $db->query("SELECT id FROM reports WHERE user_id=$uid AND report_date='$today'")->fetch_assoc();
        if ($existingReport) {
            $repStmt = $db->prepare("UPDATE reports SET total_orders = total_orders + ?, waste_orders = waste_orders + ?, confirmed_orders = confirmed_orders + ?, post_office_orders = post_office_orders + ?, leopard_orders = leopard_orders + ?, tcs_orders = tcs_orders + ?, local_parcel_orders = local_parcel_orders + ? WHERE user_id=? AND report_date=?");
            $repStmt->bind_param('iiiiiiiis', $totalOrders, $wasteOrders, $confirmedCount, $poCount, $leoCount, $tcsCount, $lpCount, $uid, $today);
            $repStmt->execute();
        } else {
            $repStmt = $db->prepare("INSERT INTO reports (user_id, total_orders, waste_orders, confirmed_orders, post_office_orders, leopard_orders, tcs_orders, local_parcel_orders, report_date) VALUES (?,?,?,?,?,?,?,?,?)");
            $repStmt->bind_param('iiiiiiiis', $uid, $totalOrders, $wasteOrders, $confirmedCount, $poCount, $leoCount, $tcsCount, $lpCount, $today);
            $repStmt->execute();
        }

        $successMsg = "✅ Report Updated Successfully!";
        $successMsg .= "<br>• System detected and added <strong>$confirmedCount</strong> confirmed orders.";
        $successMsg .= "<br>• Breakdown: PO: $poCount, Leo: $leoCount, TCS: $tcsCount, Local: $lpCount";
        
        $_SESSION['upload_success'] = $successMsg;
        header("Location: upload.php");
        exit;
    }
    $db->close();
}

// Get success message from session
$success = $_SESSION['upload_success'] ?? '';
unset($_SESSION['upload_success']);

// Today's report (pre-fill) - ONLY if no success message was just shown
$db = getDB();
$todayRep = $db->query("SELECT * FROM reports WHERE user_id=$uid AND report_date='$today'")->fetch_assoc();
$db->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Report - Ecomedge</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
        .courier-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
        .courier-box { background: white; border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px; }
        .courier-box label { display: block; font-weight: 700; font-size: 13px; color: #1e293b; margin-bottom: 8px; text-transform: uppercase; display: flex; justify-content: space-between; }
        .courier-box textarea { width: 100%; height: 120px; border: 1.5px solid #f1f5f9; border-radius: 8px; padding: 10px; font-family: monospace; font-size: 13px; outline: none; background: #f8fafc; transition: all 0.2s; }
        .courier-box textarea:focus { border-color: #1a6bff; background: white; box-shadow: 0 0 0 3px rgba(26,107,255,0.1); }
        .count-badge { background: #1a6bff; color: white; padding: 2px 8px; border-radius: 20px; font-size: 11px; }
        @media (max-width: 768px) { .courier-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body class="dash-body">
<?php include 'includes/sidebar.php'; ?>

<div class="main-content">
    <div class="topbar">
        <div class="topbar-left">
            <h2>📤 Daily Progress Report</h2>
            <p>Enter totals and paste order IDs by courier</p>
        </div>
        <div class="topbar-right">
            <span class="topbar-date">📅 <?= date('D, d M Y') ?></span>
        </div>
    </div>

    <div class="page-body">

        <?php if ($success): ?>
        <div style="background:#d1fae5;border:1px solid #6ee7b7;border-radius:12px;padding:18px 24px;margin-bottom:24px;color:#065f46;font-size:14px;line-height:1.6">
            <?= $success ?>
        </div>
        <?php endif; ?>

        <?php if ($error): ?>
        <div style="background:#fee2e2;border:1px solid #fca5a5;border-radius:12px;padding:18px 24px;margin-bottom:24px;color:#991b1b;font-size:14px">
            ⚠️ <?= $error ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="dash-panel" style="margin-bottom: 24px;">
                <div class="panel-header"><h3>📦 Manual Totals</h3></div>
                <div class="panel-body">
                    <div class="grid-2" style="gap:20px">
                        <div class="dash-form-group">
                            <label>📦 Total Orders (Overall)</label>
                            <input type="number" name="total_orders" id="totalOrders" min="0" placeholder="e.g. 150" value="<?= htmlspecialchars($_POST['total_orders'] ?? '') ?>">
                        </div>
                        <div class="dash-form-group">
                            <label>❌ Waste Orders</label>
                            <input type="number" name="waste_orders" id="wasteOrders" min="0" placeholder="e.g. 20" value="<?= htmlspecialchars($_POST['waste_orders'] ?? '') ?>">
                        </div>
                    </div>
                </div>
            </div>

            <div class="panel-header" style="margin-bottom: 16px;">
                <h3>🚚 Paste Confirmed Order IDs (System Analysis)</h3>
            </div>

            <div class="courier-grid">
                <?php foreach ($couriers as $c): $id = str_replace(' ', '_', strtolower($c)); ?>
                <div class="courier-box">
                    <label>
                        <span>📍 <?= $c ?></span>
                        <span class="count-badge" id="count_<?= $id ?>">0</span>
                    </label>
                    <textarea name="<?= $id ?>_text" id="input_<?= $id ?>" placeholder="Paste Order IDs here... (one per line)" oninput="updateCount('<?= $id ?>')"></textarea>
                </div>
                <?php endforeach; ?>
            </div>

            <div id="summaryBox" style="background:linear-gradient(135deg,#1a6bff,#00c6ff); border-radius:14px; padding:24px; margin-bottom:24px; color:white; text-align:center;">
                <div style="font-size:14px; opacity:0.8; margin-bottom:4px">Total Confirmed Detected</div>
                <div style="font-size:42px; font-weight:900" id="totalConfirmed">0</div>
                <div style="font-size:13px; opacity:0.8; margin-top:4px">System will automatically count these as confirmed orders.</div>
            </div>

            <button type="submit" class="filter-btn success" style="width:100%; padding:18px; font-size:16px; font-weight:700; border-radius:12px; box-shadow:0 10px 20px rgba(16,185,129,0.2)">
                🚀 Submit My Daily Progress
            </button>
        </form>

    </div>
</div>

<script>
const empCode = '<?= $code ?>';

function updateCount(id) {
    const text = document.getElementById('input_' + id).value;
    const badge = document.getElementById('count_' + id);
    if (!text.trim()) {
        badge.textContent = 0;
    } else {
        const lines = text.split('\n');
        let count = 0;
        const cleanCode = empCode.toUpperCase().replace(/[^A-Z0-9]/g, '');
        lines.forEach(line => {
            const cleanLine = line.toUpperCase().replace(/[^A-Z0-9]/g, '');
            if (cleanLine.includes(cleanCode)) {
                count++;
            }
        });
        badge.textContent = count;
    }
    
    // Update total
    let grandTotal = 0;
    document.querySelectorAll('.count-badge').forEach(b => {
        grandTotal += parseInt(b.textContent) || 0;
    });
    document.getElementById('totalConfirmed').textContent = grandTotal;
}

// Initial count on load if needed
window.onload = function() {
    <?php foreach ($couriers as $c): ?>
    updateCount('<?= str_replace(' ', '_', strtolower($c)) ?>');
    <?php endforeach; ?>
};
</script>
</body>
</html>
