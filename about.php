<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Ecomedge</title>
    <meta name="description" content="Learn about Ecomedge - 6 years of trusted eCommerce management services in Pakistan.">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<!-- SIDEBAR OVERLAY -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<nav class="navbar" id="navbar">
    <a href="index.php" class="nav-logo">
        <div class="logo-icon">E</div>
        <span class="logo-text">Ecomedge</span>
    </a>
    <ul class="nav-links" id="navLinks">
        <li><a href="index.php"><i class="fas fa-home"></i> Home</a></li>
        <li><a href="about.php" class="active"><i class="fas fa-info-circle"></i> About</a></li>
        <li><a href="gallery.php"><i class="fas fa-images"></i> Gallery</a></li>
        <li><a href="contact.php"><i class="fas fa-envelope"></i> Contact</a></li>
    </ul>
    <div class="nav-cta">
        <a href="login.php" class="btn btn-outline">Login</a>
        <a href="signup.php" class="btn btn-primary">Get Access</a>
    </div>
    <div class="hamburger" id="hamburger">
        <span></span><span></span><span></span>
    </div>
</nav>

<!-- PAGE HERO -->
<section style="background:linear-gradient(135deg,#0d1b4b,#1a3a7a);padding:140px 5% 80px;text-align:center">
    <div style="max-width:700px;margin:0 auto">
        <div class="hero-badge" style="margin:0 auto 20px;display:inline-flex">🏢 Our Story</div>
        <h1 style="font-size:clamp(32px,5vw,52px);font-weight:900;color:white;margin-bottom:16px">About Ecomedge</h1>
        <p style="font-size:17px;color:rgba(255,255,255,0.7);line-height:1.7">6 years of building trust, delivering excellence, and managing eCommerce operations with dedication.</p>
    </div>
</section>

<!-- MISSION / VISION -->
<section class="section">
    <div class="section-inner">
        <div class="about-grid">
            <div>
                <div class="section-badge">🎯 Our Journey</div>
                <h2 class="section-title">6 Years of Delivering Quality</h2>
                <p style="color:#64748b;font-size:16px;line-height:1.8;margin-bottom:20px">
                    Founded 6 years ago, Ecomedge started with a simple vision: to bring trendy and high-quality products to every doorstep in Pakistan. From natural hair oils to premium earbuds, we have curated a collection that meets the needs of modern lifestyles.
                </p>
                <p style="color:#64748b;font-size:16px;line-height:1.8">
                    Our commitment to customer satisfaction and product integrity has made us a trusted name in the multi-product eCommerce space.
                </p>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
                <?php $cards = [
                    ['fas fa-bullseye','Our Goal','To provide the best quality trendy products with a seamless shopping experience.'],
                    ['fas fa-eye','Our Vision','To expand our range to 50+ trendy categories and serve 1M+ customers.'],
                    ['fas fa-lightbulb','Core Values','Quality First, Customer Trust, and Transparent Business Practices.'],
                    ['fas fa-rocket','Experience','6+ years of expertise in sourcing and delivering multi-product solutions.'],
                ]; ?>
                <?php foreach ($cards as $c): ?>
                <div class="service-card" style="padding:24px">
                    <div class="service-icon" style="width:48px;height:48px;font-size:22px"><i class="<?= $c[0] ?>"></i></div>
                    <h3 style="font-size:16px"><?= $c[1] ?></h3>
                    <p style="font-size:14px"><?= $c[2] ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<!-- STATS -->
<section class="stats-section">
    <div class="stats-grid">
        <div class="stat-item"><span class="number">6+</span><div class="label">Years in Business</div></div>
        <div class="stat-item"><span class="number">50K+</span><div class="label">Orders Managed</div></div>
        <div class="stat-item"><span class="number">98%</span><div class="label">Customer Satisfaction</div></div>
        <div class="stat-item"><span class="number">5+</span><div class="label">Courier Partners</div></div>
    </div>
</section>

<!-- TEAM -->
<section class="section" style="background:#f8faff">
    <div class="section-inner">
        <div class="text-center" style="margin-bottom:56px">
            <div class="section-badge">👥 Management Team</div>
            <h2 class="section-title">The Visionaries Behind Ecomedge</h2>
            <p class="section-subtitle">Leading with passion, integrity, and a commitment to excellence.</p>
        </div>
        <div class="services-grid" style="grid-template-columns:repeat(auto-fit,minmax(300px,1fr))">
            <?php
            $db = getDB();
            // Fetch Founders/CEO
            $foundersRes = $db->query("SELECT * FROM team WHERE is_founder = 1 ORDER BY id ASC");
            while($f = $foundersRes->fetch_assoc()):
            ?>
            <!-- Founder Card -->
            <div class="service-card" style="text-align:center;padding:40px 20px">
                <div class="team-img-wrap" style="width:150px;height:150px;margin:0 auto 20px;border-radius:50%;overflow:hidden;border:4px solid white;box-shadow:var(--shadow-md)">
                    <img src="<?= $f['image_path'] ?? 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=300&q=80' ?>" alt="<?= htmlspecialchars($f['name']) ?>" style="width:100%;height:100%;object-fit:cover">
                </div>
                <h3 style="font-size:22px;margin-bottom:8px"><?= htmlspecialchars($f['name']) ?></h3>
                <p style="font-size:15px;color:#1a6bff;font-weight:700;margin-bottom:12px;text-transform:uppercase;letter-spacing:1px"><?= htmlspecialchars($f['role']) ?></p>
                <p style="font-size:14px;color:#64748b;line-height:1.6"><?= htmlspecialchars($f['description']) ?></p>
            </div>
            <?php endwhile; ?>
        </div>

        <?php
        // Fetch Other Team
        $teamRes = $db->query("SELECT * FROM team WHERE is_founder = 0 ORDER BY created_at ASC");
        if ($teamRes->num_rows > 0):
        ?>
        <div class="text-center" style="margin: 56px 0 32px">
            <div class="section-badge">👥 Operations Team</div>
            <h2 class="section-title" style="font-size: 32px">Our Dedicated Workforce</h2>
        </div>

        <div class="services-grid" style="grid-template-columns:repeat(auto-fit,minmax(250px,1fr)); gap: 20px;">
            <?php 
            $i = 0;
            $placeholders = [
                'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=300&q=80',
                'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=300&q=80',
                'https://images.unsplash.com/photo-1539571696357-5a69c17a67c6?w=300&q=80',
                'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=300&q=80'
            ];
            while($m = $teamRes->fetch_assoc()): 
                $img = $m['image_path'] ?? $placeholders[$i % 4];
                $i++;
            ?>
            <!-- Member Small -->
            <div class="service-card" style="text-align:center;padding:24px 16px; border-radius: 15px;">
                <div class="team-img-wrap" style="width:100px;height:100px;margin:0 auto 16px;border-radius:50%;overflow:hidden;border:3px solid white;box-shadow:var(--shadow-sm)">
                    <img src="<?= $img ?>" alt="<?= htmlspecialchars($m['name']) ?>" style="width:100%;height:100%;object-fit:cover">
                </div>
                <h3 style="font-size:18px;margin-bottom:4px"><?= htmlspecialchars($m['name']) ?></h3>
                <p style="font-size:13px;color:#1a6bff;font-weight:700;margin-bottom:8px;text-transform:uppercase;letter-spacing:0.5px"><?= htmlspecialchars($m['role']) ?></p>
                <p style="font-size:13px;color:#64748b;line-height:1.5"><?= htmlspecialchars($m['description']) ?></p>
            </div>
            <?php endwhile; ?>
        </div>
        <?php endif; $db->close(); ?>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="section" style="background:linear-gradient(135deg,#0d1b4b,#1a3a7a);text-align:center">
    <div class="section-inner">
        <h2 class="section-title" style="color:white">Ready to Join Our Team?</h2>
        <p class="section-subtitle" style="color:rgba(255,255,255,0.7);margin:0 auto 32px">Request access and become part of the Ecomedge family.</p>
        <a href="signup.php" class="btn btn-white btn-lg">🚀 Request Access</a>
    </div>
</section>

<footer class="footer">
    <div class="footer-grid">
        <div class="footer-brand"><div class="logo-text">Ecomedge</div><p>Pakistan's trusted eCommerce order management system. 6 years of excellence.</p></div>
        <div class="footer-col"><h4>Pages</h4><ul><li><a href="index.php">Home</a></li><li><a href="about.php">About</a></li><li><a href="gallery.php">Gallery</a></li><li><a href="contact.php">Contact</a></li></ul></div>
        <div class="footer-col"><h4>System</h4><ul><li><a href="login.php">Login</a></li><li><a href="signup.php">Signup</a></li></ul></div>
        <div class="footer-col"><h4>Contact</h4><ul><li><a href="#">📱 WhatsApp</a></li><li><a href="#">✉️ Email</a></li></ul></div>
    </div>
    <div class="footer-bottom"><span>© 2024 Ecomedge. All rights reserved.</span><span>Built with ❤️</span></div>
</footer>

<script>
window.addEventListener('scroll', () => {
    document.getElementById('navbar').classList.toggle('scrolled', window.scrollY > 20);
});

// Mobile menu
const hamburger = document.getElementById('hamburger');
const navLinks = document.getElementById('navLinks');
const closeBtn = document.getElementById('closeBtn');
const sidebarOverlay = document.getElementById('sidebarOverlay');

function toggleMenu() {
    navLinks.classList.toggle('active');
    hamburger.classList.toggle('active');
    if (sidebarOverlay) sidebarOverlay.classList.toggle('active');
}

if (hamburger) {
    hamburger.addEventListener('click', toggleMenu);
}

if (closeBtn) {
    closeBtn.addEventListener('click', toggleMenu);
}

if (sidebarOverlay) {
    sidebarOverlay.addEventListener('click', toggleMenu);
}

// Close menu when clicking outside (fallback)
document.addEventListener('click', (e) => {
    if (navLinks && navLinks.classList.contains('active') && !navLinks.contains(e.target) && !hamburger.contains(e.target) && (!sidebarOverlay || !sidebarOverlay.contains(e.target))) {
        navLinks.classList.remove('active');
        hamburger.classList.remove('active');
        if (sidebarOverlay) sidebarOverlay.classList.remove('active');
    }
});
</script>
</body>
</html>
