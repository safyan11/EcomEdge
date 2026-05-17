<?php
require_once 'config.php';

// Handle contact form
$success = $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact_submit'])) {
    $name    = htmlspecialchars(trim($_POST['name'] ?? ''));
    $email   = htmlspecialchars(trim($_POST['email'] ?? ''));
    $message = htmlspecialchars(trim($_POST['message'] ?? ''));

    if ($name && $email && $message) {
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO contact_messages (name, email, message) VALUES (?, ?, ?)");
        $stmt->bind_param('sss', $name, $email, $message);
        if ($stmt->execute()) {
            $success = 'Your message has been sent! We will contact you soon.';
        } else {
            $error = 'Something went wrong. Please try again.';
        }
        $db->close();
    } else {
        $error = 'Please fill in all fields.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ecomedge - Confirm Order Management System</title>
    <meta name="description" content="Ecomedge is a trusted eCommerce management company with 6 years of experience in product solutions and customer satisfaction.">
    <!-- FontAwesome for Natural Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .hero-visual { display: flex; justify-content: center; }
    </style>
</head>
<body>

<!-- SIDEBAR OVERLAY -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- NAVBAR -->
<nav class="navbar" id="navbar">
    <a href="index.php" class="nav-logo">
        <div class="logo-icon">E</div>
        <span class="logo-text">Ecomedge</span>
    </a>
    <ul class="nav-links" id="navLinks">
        <li><a href="index.php" class="active"><i class="fas fa-home"></i> Home</a></li>
        <li><a href="about.php"><i class="fas fa-info-circle"></i> About</a></li>
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

<!-- HERO -->
<section class="hero" id="hero">
    <div class="hero-content">
        <div class="hero-text">
            <div class="hero-badge"><span></span> <i class="fas fa-star"></i> Pakistan's Best Multi-Product Store</div>
            <h1>
                Quality Products <br>
                <span class="gradient-text">Delivered to Your Door</span>
            </h1>
            <p>From premium earbuds and natural hair oils to the latest 10-12 trendy products, Ecomedge brings you quality and trust. We have been serving satisfied customers across Pakistan for the last 6 years.</p>
            <div class="hero-buttons">
                <a href="gallery.php" class="btn btn-white btn-lg"><i class="fas fa-shopping-bag"></i> Shop Trendy Products</a>
                <a href="about.php" class="btn btn-ghost btn-lg">Our Story <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="hero-stats">
                <div class="stat-card">
                    <span class="stat-number">6+</span>
                    <div class="stat-label"><i class="fas fa-calendar-check"></i> Years of Trust</div>
                </div>
                <div class="stat-card">
                    <span class="stat-number">12+</span>
                    <div class="stat-label"><i class="fas fa-tags"></i> Trendy Categories</div>
                </div>
                <div class="stat-card">
                    <span class="stat-number">50K+</span>
                    <div class="stat-label"><i class="fas fa-users"></i> Happy Customers</div>
                </div>
            </div>
        </div>

        <div class="hero-visual">
            <div class="dashboard-preview">
                <div class="preview-header">
                    <div class="preview-dot" style="background:#ff5f57"></div>
                    <div class="preview-dot" style="background:#febc2e"></div>
                    <div class="preview-dot" style="background:#28c840"></div>
                    <span class="preview-title">🔥 Featured Products</span>
                </div>
                <div class="preview-metrics">
                    <div class="metric-box">
                        <div class="m-val">Premium</div>
                        <div class="m-lbl">Earbuds</div>
                        <div class="m-trend">★ 4.9</div>
                    </div>
                    <div class="metric-box">
                        <div class="m-val">Organic</div>
                        <div class="m-lbl">Hair Oil</div>
                        <div class="m-trend" style="color:#00c6ff">Best Seller</div>
                    </div>
                    <div class="metric-box">
                        <div class="m-val">Smart</div>
                        <div class="m-lbl">Watches</div>
                        <div class="m-trend" style="color:#f87171">Trending</div>
                    </div>
                    <div class="metric-box">
                        <div class="m-val">New</div>
                        <div class="m-lbl">Arrivals</div>
                        <div class="m-trend">● Just In</div>
                    </div>
                </div>
                <div class="preview-bar-title">Stock Availability</div>
                <div class="preview-bar"><div class="fill" style="width:82%"></div></div>
                <div style="display:flex;justify-content:space-between;font-size:11px;color:rgba(255,255,255,0.5);margin-bottom:8px">
                    <span>Earbuds</span><span>82% In Stock</span>
                </div>
                <div class="preview-bar"><div class="fill" style="width:68%;animation-delay:0.3s"></div></div>
                <div style="display:flex;justify-content:space-between;font-size:11px;color:rgba(255,255,255,0.5)">
                    <span>Organic Oil</span><span>68% In Stock</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- SERVICES -->
<section class="section" id="services">
    <div class="section-inner">
        <div class="text-center">
            <div class="section-badge"><i class="fas fa-bolt"></i> Our Services</div>
            <h2 class="section-title">Everything You Need to <br>Run eCommerce Smoothly</h2>
            <p class="section-subtitle">From order tracking to employee performance — we handle it all with precision and speed.</p>
        </div>
        <div class="services-grid">
            <div class="service-card">
                <div class="service-icon"><i class="fas fa-box"></i></div>
                <h3>Order Management</h3>
                <p>Track confirmed and waste orders daily with real-time accuracy and automated counting systems.</p>
            </div>
            <div class="service-card">
                <div class="service-icon"><i class="fas fa-user-check"></i></div>
                <h3>Employee Tracking</h3>
                <p>Monitor individual employee performance, daily targets, waste rates, and confirmation rates.</p>
            </div>
            <div class="service-card">
                <div class="service-icon"><i class="fas fa-truck-fast"></i></div>
                <h3>Courier Analysis</h3>
                <p>Analyze performance across POSTEX, LEOPARD, TCS and other courier services intelligently.</p>
            </div>
            <div class="service-card">
                <div class="service-icon"><i class="fas fa-file-invoice"></i></div>
                <h3>Reports & Analytics</h3>
                <p>Generate date-wise reports with filters — daily, weekly, monthly and custom date ranges.</p>
            </div>
            <div class="service-card">
                <div class="service-icon"><i class="fas fa-copy"></i></div>
                <h3>Duplicate Detection</h3>
                <p>Instantly detect and flag duplicate order entries to maintain data accuracy and integrity.</p>
            </div>
            <div class="service-card">
                <div class="service-icon"><i class="fas fa-shield-halved"></i></div>
                <h3>Role-Based Access</h3>
                <p>Secure admin approval system with role-based access for employees and management.</p>
            </div>
        </div>
    </div>
</section>

<!-- STATS -->
<section class="stats-section">
    <div class="stats-grid">
        <div class="stat-item">
            <span class="number" data-target="6">0</span>
            <div class="label">Years of Excellence</div>
        </div>
        <div class="stat-item">
            <span class="number" data-target="12">0</span>
            <div class="label">Trendy Categories</div>
        </div>
        <div class="stat-item">
            <span class="number" data-target="100">0</span>
            <div class="label">% Quality Assurance</div>
        </div>
        <div class="stat-item">
            <span class="number" data-target="50000">0</span>
            <div class="label">Happy Customers</div>
        </div>
    </div>
</section>

<!-- ABOUT PREVIEW -->
<section class="section" style="background: var(--gray-50);">
    <div class="section-inner">
        <div class="about-grid">
            <div class="about-visual">
                <div class="about-img-wrap">
                    <img src="assets/images/about-team.jpg" alt="Ecomedge Team" onerror="this.src='https://images.unsplash.com/photo-1522071820081-009f0129c71c?w=600&q=80'">
                </div>
                <div class="about-badge-float">
                    <div class="icon">🏆</div>
                    <div>
                        <strong>6+ Years</strong>
                        <span>Trusted Service</span>
                    </div>
                </div>
            </div>
            <div class="about-text">
                <div class="section-badge">🏢 About Us</div>
                <h2 class="section-title">Trusted eCommerce Management Since Day One</h2>
                <p class="section-subtitle">Our goal is to provide smooth and trusted eCommerce services while maintaining professionalism, transparency, and long-term customer relationships.</p>
                <ul class="features-list">
                    <li><span class="check">✓</span> Multi-product eCommerce operations management</li>
                    <li><span class="check">✓</span> Real-time order tracking and reporting</li>
                    <li><span class="check">✓</span> Professional team of dedicated employees</li>
                    <li><span class="check">✓</span> Transparent and trusted business practices</li>
                    <li><span class="check">✓</span> Advanced analytics and performance tracking</li>
                </ul>
                <div style="margin-top: 32px;">
                    <a href="about.php" class="btn btn-primary btn-lg">Learn Our Story →</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CONTACT CTA -->
<section class="section" style="background: linear-gradient(135deg, var(--secondary), #1a3a7a); text-align: center;">
    <div class="section-inner">
        <div class="section-badge" style="background:rgba(255,255,255,0.12); color: var(--accent); margin: 0 auto 16px;">📞 Get In Touch</div>
        <h2 class="section-title" style="color: white;">Ready to Get Started?</h2>
        <p class="section-subtitle" style="color: rgba(255,255,255,0.7); margin: 0 auto 36px;">Join Ecomedge and take control of your eCommerce operations with powerful tools and insights.</p>
        <div style="display:flex; gap:16px; justify-content:center; flex-wrap:wrap;">
            <a href="contact.php" class="btn btn-white btn-lg">📩 Contact Us</a>
            <a href="signup.php" class="btn btn-ghost btn-lg">🚀 Request Access</a>
        </div>
    </div>
</section>

<!-- FOOTER -->
<footer class="footer">
    <div class="footer-grid">
        <div class="footer-brand">
            <div class="logo-text">Ecomedge</div>
            <p>Pakistan's trusted eCommerce order management system. 6 years of excellence in multi-product operations and customer satisfaction.</p>
        </div>
        <div class="footer-col">
            <h4>Quick Links</h4>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="about.php">About Us</a></li>
                <li><a href="gallery.php">Gallery</a></li>
                <li><a href="contact.php">Contact</a></li>
            </ul>
        </div>
        <div class="footer-col">
            <h4>System</h4>
            <ul>
                <li><a href="login.php">Login</a></li>
                <li><a href="signup.php">Signup</a></li>
                <li><a href="dashboard/">Dashboard</a></li>
            </ul>
        </div>
        <div class="footer-col">
            <h4>Contact</h4>
            <ul>
                <li><a href="#"><i class="fab fa-whatsapp"></i> WhatsApp Us</a></li>
                <li><a href="#"><i class="fas fa-envelope"></i> Email Us</a></li>
                <li><a href="#"><i class="fas fa-location-dot"></i> Our Location</a></li>
            </ul>
        </div>
    </div>
    <div class="footer-bottom">
        <span>© 2024 Ecomedge. All rights reserved.</span>
        <span>Built with ❤️ for eCommerce</span>
    </div>
</footer>

<script>
// Navbar scroll effect
window.addEventListener('scroll', () => {
    document.getElementById('navbar').classList.toggle('scrolled', window.scrollY > 20);
});

// Counter animation
const counters = document.querySelectorAll('[data-target]');
const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            const el = entry.target;
            const target = +el.dataset.target;
            const duration = 2000;
            const step = target / (duration / 16);
            let current = 0;
            const timer = setInterval(() => {
                current += step;
                if (current >= target) {
                    el.textContent = target >= 1000 ? target.toLocaleString() + '+' : target + (el.closest('.stat-item').querySelector('.label').textContent.includes('%') ? '%' : '+');
                    clearInterval(timer);
                } else {
                    el.textContent = Math.floor(current);
                }
            }, 16);
            observer.unobserve(el);
        }
    });
}, { threshold: 0.5 });

counters.forEach(c => observer.observe(c));

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
