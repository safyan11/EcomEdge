<?php
// dashboard/includes/sidebar.php
require_once __DIR__ . '/../../config.php';
requireLogin();
$current = basename($_SERVER['PHP_SELF']);
$role = $_SESSION['role'];
$name = $_SESSION['user_name'];
$initial = strtoupper(substr($name, 0, 1));

// Pending users count for admin
$pendingCount = 0;
if ($role === 'admin') {
    $_db_side = getDB();
    $_r_side = $_db_side->query("SELECT COUNT(*) as c FROM users WHERE status='pending'");
    $pendingCount = $_r_side->fetch_assoc()['c'];
    $_db_side->close();
}
?>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-logo">
        <div class="logo-icon">E</div>
        <span class="logo-text">Ecomedge</span>
    </div>
    <div class="sidebar-user">
        <div class="sidebar-avatar">
            <?php if (isset($_SESSION['user_image']) && $_SESSION['user_image']): ?>
                <img src="../<?= $_SESSION['user_image'] ?>" style="width:100%;height:100%;border-radius:50%;object-fit:cover">
            <?php else: ?>
                <?= $initial ?>
            <?php endif; ?>
        </div>
        <div class="sidebar-user-info">
            <strong><?= htmlspecialchars($name) ?></strong>
            <span><?= $role === 'admin' ? '⭐ Admin' : '👤 ' . $_SESSION['employee_code'] ?></span>
        </div>
    </div>
    <nav class="sidebar-nav">
        <?php if ($role === 'admin'): ?>
        <div class="nav-section-title">Main</div>
        <a href="index.php"     class="nav-item <?= $current==='index.php'?'active':'' ?>"><span class="nav-icon">📊</span> Dashboard</a>
        <a href="users.php" class="nav-item <?= $current==='users.php'?'active':'' ?>"><span class="nav-icon">👥</span> Users</a>
        <a href="customer_orders.php" class="nav-item <?= $current==='customer_orders.php'?'active':'' ?>"><span class="nav-icon">🛒</span> Customer Orders</a>
        <a href="gallery_manage.php" class="nav-item <?= $current==='gallery_manage.php'?'active':'' ?>"><span class="nav-icon">🖼️</span> Manage Gallery</a>
        <a href="team_manage.php"    class="nav-item <?= $current==='team_manage.php'?'active':'' ?>"><span class="nav-icon">👥</span> Manage Team</a>
        <a href="pending.php"   class="nav-item <?= $current==='pending.php'?'active':'' ?>">
            <span class="nav-icon">⏳</span> Pending Users
            <?php if ($pendingCount > 0): ?><span class="nav-badge"><?= $pendingCount ?></span><?php endif; ?>
        </a>
        <div class="nav-section-title">Orders</div>
        <a href="orders.php"    class="nav-item <?= $current==='orders.php'?'active':'' ?>"><span class="nav-icon">📦</span> Confirm Orders</a>
        <a href="reports.php"   class="nav-item <?= $current==='reports.php'?'active':'' ?>"><span class="nav-icon">📋</span> Reports</a>
        <a href="analytics.php" class="nav-item <?= $current==='analytics.php'?'active':'' ?>"><span class="nav-icon">📈</span> Analytics</a>
        <div class="nav-section-title">System</div>
        <a href="messages.php"  class="nav-item <?= $current==='messages.php'?'active':'' ?>"><span class="nav-icon">✉️</span> Messages</a>
        <a href="settings.php"  class="nav-item <?= $current==='settings.php'?'active':'' ?>"><span class="nav-icon">⚙️</span> Settings</a>
        <?php else: ?>
        <div class="nav-section-title">My Work</div>
        <a href="index.php"   class="nav-item <?= $current==='index.php'?'active':'' ?>"><span class="nav-icon">🏠</span> Dashboard</a>
        <a href="upload.php"  class="nav-item <?= $current==='upload.php'?'active':'' ?>"><span class="nav-icon">📤</span> Upload Confirms</a>
        <a href="my-reports.php" class="nav-item <?= $current==='my-reports.php'?'active':'' ?>"><span class="nav-icon">📋</span> My Reports</a>
        <div class="nav-section-title">Account</div>
        <a href="profile.php" class="nav-item <?= $current==='profile.php'?'active':'' ?>"><span class="nav-icon">👤</span> Profile</a>
        <?php endif; ?>
    </nav>
    <div class="sidebar-footer">
        <a href="../index.php">🌐 Public Site</a>
        <a href="../logout.php" style="margin-top:4px;">🚪 Logout</a>
    </div>
    <!-- Mobile Close Button -->
    <button id="dashCloseBtn" class="dash-close-btn"><i class="fas fa-times" style="font-style:normal;">✕</i></button>
</aside>

<!-- Mobile Overlay -->
<div class="dash-sidebar-overlay" id="dashSidebarOverlay"></div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const topbarLeft = document.querySelector('.topbar-left');
    if (topbarLeft) {
        const toggleBtn = document.createElement('button');
        toggleBtn.className = 'dash-mobile-toggle';
        toggleBtn.innerHTML = '☰';
        
        topbarLeft.insertBefore(toggleBtn, topbarLeft.firstChild);
        
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('dashSidebarOverlay');
        const closeBtn = document.getElementById('dashCloseBtn');
        
        function toggleSidebar() {
            sidebar.classList.toggle('open');
            overlay.classList.toggle('active');
        }
        
        toggleBtn.addEventListener('click', toggleSidebar);
        closeBtn.addEventListener('click', toggleSidebar);
        overlay.addEventListener('click', toggleSidebar);
    }
});
</script>
