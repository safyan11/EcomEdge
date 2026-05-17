<?php
require_once 'config.php';
$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = htmlspecialchars(trim($_POST['name'] ?? ''));
    $email   = htmlspecialchars(trim($_POST['email'] ?? ''));
    $message = htmlspecialchars(trim($_POST['message'] ?? ''));

    if ($name && $email && $message) {
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO contact_messages (name, email, message) VALUES (?,?,?)");
        $stmt->bind_param('sss', $name, $email, $message);
        $success = $stmt->execute() ? 'Message sent! We will get back to you soon.' : 'Failed to send. Please try again.';
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
    <title>Contact Us - Ecomedge</title>
    <meta name="description" content="Get in touch with Ecomedge - We are here to help with your eCommerce management needs.">
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
        <li><a href="about.php"><i class="fas fa-info-circle"></i> About</a></li>
        <li><a href="gallery.php"><i class="fas fa-images"></i> Gallery</a></li>
        <li><a href="contact.php" class="active"><i class="fas fa-envelope"></i> Contact</a></li>
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
    <div style="max-width:600px;margin:0 auto">
        <div class="hero-badge" style="margin:0 auto 20px;display:inline-flex">📞 Contact</div>
        <h1 style="font-size:clamp(32px,5vw,52px);font-weight:900;color:white;margin-bottom:16px">Get In Touch</h1>
        <p style="font-size:17px;color:rgba(255,255,255,0.7);line-height:1.7">Have questions? We'd love to hear from you. Send us a message and we'll respond as soon as possible.</p>
    </div>
</section>

<!-- CONTACT SECTION -->
<section class="section">
    <div class="section-inner">
        <div class="contact-grid">
            <!-- Contact Info -->
            <div class="contact-info">
                <h3>Contact Information</h3>
                <p>Reach out to us through any of these channels. We're available to assist you with all your eCommerce management needs.</p>

                <div class="contact-detail">
                    <div class="icon"><i class="fab fa-whatsapp"></i></div>
                    <div>
                        <strong>WhatsApp</strong>
                        <span>+92 300 0000000</span>
                    </div>
                </div>
                <div class="contact-detail">
                    <div class="icon"><i class="fas fa-envelope"></i></div>
                    <div>
                        <strong>Email</strong>
                        <span>info@ecomedge.com</span>
                    </div>
                </div>
                <div class="contact-detail">
                    <div class="icon"><i class="fas fa-location-dot"></i></div>
                    <div>
                        <strong>Office Address</strong>
                        <span>Lahore, Punjab, Pakistan</span>
                    </div>
                </div>
                <div class="contact-detail">
                    <div class="icon"><i class="fas fa-clock"></i></div>
                    <div>
                        <strong>Working Hours</strong>
                        <span>Mon–Sat: 9AM – 6PM PKT</span>
                    </div>
                </div>

                <!-- Info Cards -->
                <div style="margin-top:32px;display:flex;flex-direction:column;gap:12px">
                    <div style="background:var(--primary-light);border-radius:12px;padding:16px 20px;border:1px solid rgba(26,107,255,0.15)">
                        <div style="font-weight:700;color:var(--primary);margin-bottom:4px">🚀 Quick Response</div>
                        <div style="font-size:14px;color:var(--gray-500)">We typically respond within 2-4 hours on business days.</div>
                    </div>
                    <div style="background:#f0fdf4;border-radius:12px;padding:16px 20px;border:1px solid #bbf7d0">
                        <div style="font-weight:700;color:#065f46;margin-bottom:4px">🔐 Secure Access</div>
                        <div style="font-size:14px;color:#047857">Employee access requires admin approval after signup.</div>
                    </div>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="contact-form">
                <h3 style="font-size:22px;font-weight:800;color:var(--gray-900);margin-bottom:6px">Send a Message</h3>
                <p style="color:var(--gray-500);font-size:14px;margin-bottom:24px">Fill out the form and we'll get back to you shortly.</p>

                <?php if ($success): ?>
                <div class="alert alert-success">✅ <?= $success ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                <div class="alert alert-error">⚠️ <?= $error ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="form-group">
                        <label>Your Name</label>
                        <input type="text" name="name" placeholder="Enter your full name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="email" placeholder="your@email.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Message</label>
                        <textarea name="message" placeholder="Write your message here..." required><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg" style="width:100%;justify-content:center">
                        📩 Send Message
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

<footer class="footer">
    <div class="footer-grid">
        <div class="footer-brand"><div class="logo-text">Ecomedge</div><p>Pakistan's trusted eCommerce order management system.</p></div>
        <div class="footer-col"><h4>Pages</h4><ul><li><a href="index.php">Home</a></li><li><a href="about.php">About</a></li><li><a href="gallery.php">Gallery</a></li><li><a href="contact.php">Contact</a></li></ul></div>
        <div class="footer-col"><h4>System</h4><ul><li><a href="login.php">Login</a></li><li><a href="signup.php">Signup</a></li></ul></div>
        <div class="footer-col"><h4>Contact</h4><ul><li><a href="#">📱 WhatsApp</a></li><li><a href="#">✉️ Email</a></li></ul></div>
    </div>
    <div class="footer-bottom"><span>© 2024 Ecomedge. All rights reserved.</span></div>
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
