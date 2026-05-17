<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gallery - Ecomedge</title>
    <meta name="description" content="See Ecomedge team at work - packaging, order handling, and office operations.">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
    .gallery-filter-bar {
        display: flex;
        gap: 10px;
        justify-content: center;
        flex-wrap: wrap;
        margin-bottom: 40px;
    }
    .gal-filter-btn {
        padding: 8px 20px;
        border-radius: 50px;
        border: 2px solid var(--gray-200);
        background: white;
        color: var(--gray-700);
        font-weight: 600;
        font-size: 13px;
        cursor: pointer;
        transition: var(--transition);
    }
    .gal-filter-btn.active, .gal-filter-btn:hover {
        background: var(--primary);
        border-color: var(--primary);
        color: white;
    }
    .gallery-masonry {
        columns: 3;
        column-gap: 20px;
    }
    .gallery-masonry .gallery-item {
        break-inside: avoid;
        margin-bottom: 20px;
        aspect-ratio: unset;
    }
    .gallery-masonry .gallery-item img {
        height: auto;
        min-height: 180px;
    }
    @media (max-width: 768px) { .gallery-masonry { columns: 2; } }
    @media (max-width: 480px) { .gallery-masonry { columns: 1; } }

    /* Lightbox */
    .lightbox {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.9);
        z-index: 9999;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }
    .lightbox.open { display: flex; }
    .lightbox img { max-width: 90vw; max-height: 85vh; border-radius: 12px; object-fit: contain; }
    .lightbox-close {
        position: absolute;
        top: 20px; right: 24px;
        color: white; font-size: 32px;
        cursor: pointer; background: none; border: none;
        line-height: 1;
    }
    .lightbox-caption {
        position: absolute;
        bottom: 24px;
        color: rgba(255,255,255,0.8);
        font-size: 15px;
        text-align: center;
    }
    </style>
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
        <li><a href="gallery.php" class="active"><i class="fas fa-images"></i> Gallery</a></li>
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
    <div style="max-width:600px;margin:0 auto">
        <div class="hero-badge" style="margin:0 auto 20px;display:inline-flex">📸 Our Work</div>
        <h1 style="font-size:clamp(32px,5vw,52px);font-weight:900;color:white;margin-bottom:16px">Ecomedge Gallery</h1>
        <p style="font-size:17px;color:rgba(255,255,255,0.7);line-height:1.7">A glimpse into our daily operations — team work, packaging, and order processing.</p>
    </div>
</section>

<!-- GALLERY -->
<section class="section">
    <div class="section-inner">
        <div class="gallery-filter-bar">
            <button class="gal-filter-btn active" onclick="filterGallery('all', this)"><i class="fas fa-border-all"></i> All</button>
            <button class="gal-filter-btn" onclick="filterGallery('team', this)"><i class="fas fa-users"></i> Team</button>
            <button class="gal-filter-btn" onclick="filterGallery('packaging', this)"><i class="fas fa-box"></i> Packaging</button>
            <button class="gal-filter-btn" onclick="filterGallery('office', this)"><i class="fas fa-building"></i> Office</button>
            <button class="gal-filter-btn" onclick="filterGallery('courier', this)"><i class="fas fa-truck"></i> Courier</button>
            <button class="gal-filter-btn" onclick="filterGallery('products', this)"><i class="fas fa-shopping-cart"></i> Products</button>
        </div>

        <?php
        $db = getDB();
        
        // Fetch WhatsApp number for floating button
        $waRes = $db->query("SELECT setting_value FROM system_settings WHERE setting_key='whatsapp_number'");
        $rawNum = ($waRes && $waRes->num_rows > 0) ? $waRes->fetch_assoc()['setting_value'] : '923001234567';
        
        // Ensure strictly numbers
        $whatsappNum = preg_replace('/[^0-9]/', '', $rawNum);
        
        // Auto-fix for Pakistan: if number starts with 0 and is 11 digits, replace 0 with 92
        if (strlen($whatsappNum) === 11 && strpos($whatsappNum, '0') === 0) {
            $whatsappNum = '92' . substr($whatsappNum, 1);
        }
        
        $res = $db->query("SELECT * FROM gallery ORDER BY created_at DESC");
        $galleryItems = $res->fetch_all(MYSQLI_ASSOC);
        $db->close();
        ?>

        <!-- Floating WhatsApp Button -->
        <a href="https://wa.me/<?= $whatsappNum ?>?text=<?= urlencode('Hi Ecomedge! I have a question about your products.') ?>" 
           target="_blank" 
           style="position:fixed; bottom:30px; right:30px; width:60px; height:60px; background:#25d366; color:white; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:30px; box-shadow:0 10px 25px rgba(37,211,102,0.4); z-index:999; transition:0.3s; text-decoration:none;"
           onmouseover="this.style.transform='scale(1.1)'"
           onmouseout="this.style.transform='scale(1)'"
           title="Chat with us on WhatsApp">
            <i class="fab fa-whatsapp"></i>
            <span style="position:absolute; right:70px; background:white; color:#334155; padding:6px 15px; border-radius:10px; font-size:14px; font-weight:700; white-space:nowrap; box-shadow:0 5px 15px rgba(0,0,0,0.1); pointer-events:none;">Chat with us!</span>
        </a>

        <div class="gallery-masonry" id="galleryGrid">
            <?php if (empty($galleryItems)): ?>
                <div style="grid-column: 1/-1; text-align: center; padding: 40px; color: var(--gray-500);">
                    <p>No products in the gallery yet.</p>
                </div>
            <?php else: ?>
                <?php foreach ($galleryItems as $item): ?>
                <div class="gallery-item" data-cat="<?= $item['category'] ?>">
                    <div style="position: relative; overflow: hidden; border-radius: var(--radius) var(--radius) 0 0; cursor: pointer;" onclick="openLightbox('<?= $item['image_path'] ?>', '<?= htmlspecialchars($item['title']) ?>')">
                        <!-- Discount Badge -->
                        <div style="position: absolute; top: 12px; left: 12px; background: #ef4444; color: white; padding: 4px 10px; border-radius: 6px; font-size: 12px; font-weight: 800; z-index: 10; box-shadow: 0 4px 10px rgba(239,68,68,0.3);">
                            20% OFF
                        </div>
                        
                        <img src="<?= $item['image_path'] ?>" alt="<?= htmlspecialchars($item['title']) ?>" loading="lazy" style="width: 100%; display: block; transition: var(--transition);">
                        <div class="gallery-overlay" style="display: flex; flex-direction: column; justify-content: flex-end; padding: 20px; background: linear-gradient(to top, rgba(0,0,0,0.8), transparent); opacity: 0; position: absolute; inset: 0; transition: var(--transition);">
                            <h3 style="color: white; font-size: 18px; margin-bottom: 4px; text-transform: uppercase;"><?= htmlspecialchars($item['title']) ?></h3>
                            <div style="color: var(--accent); font-weight: 700; font-size: 16px;">Rs. <?= htmlspecialchars($item['price']) ?></div>
                        </div>
                    </div>
                    <div style="padding: 15px; background: white; border: 1px solid #f0f4ff; border-top: none; border-radius: 0 0 var(--radius) var(--radius);">
                        <h4 style="font-size: 17px; font-weight: 900; color: #0d1b4b; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.5px;"><?= htmlspecialchars($item['title']) ?></h4>
                        <div style="color: #10b981; font-weight: 800; font-size: 18px; margin-bottom: 12px;">Rs. <?= htmlspecialchars($item['price']) ?></div>
                        <button class="btn btn-primary" style="width: 100%; justify-content: center; padding: 10px; font-size: 14px; border-radius: 10px;" onclick="openOrderModal(<?= $item['id'] ?>, '<?= htmlspecialchars($item['title']) ?>', '<?= htmlspecialchars($item['price']) ?>')">
                            <i class="fas fa-shopping-cart"></i> Order Now
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- ORDER MODAL -->
<div class="lightbox" id="orderModal" onclick="closeOrderModal(event)">
    <div style="background:white; width:95%; max-width:500px; border-radius:20px; overflow:hidden; position:relative; animation: slideUp 0.3s ease;">
        <button class="lightbox-close" style="color:#1a1a1a; top:15px; right:20px;" onclick="closeOrderModal()">✕</button>
        <div style="padding:30px; max-height:90vh; overflow-y:auto;">
            <div style="text-align:center; margin-bottom:24px;">
                <h2 style="font-size:22px; font-weight:800; color:#0d1b4b; margin-bottom:5px;">Place Your Order</h2>
                <p style="color:#64748b; font-size:14px;">Product: <strong id="orderProductName" style="color:#1a6bff;">...</strong></p>
                <p style="color:#64748b; font-size:14px;">Price: <strong style="color:#10b981;">Rs. <span id="orderProductPrice">0</span></strong></p>
            </div>
            
            <form id="orderForm" onsubmit="submitOrder(event)">
                <input type="hidden" id="orderProductId" name="product_id">
                <input type="hidden" id="hiddenProductName">
                
                <div class="form-group" style="margin-bottom:15px;">
                    <label style="display:block; font-size:13px; font-weight:700; margin-bottom:6px; color:#334155;">Full Name *</label>
                    <input type="text" name="name" placeholder="Enter your full name" required style="width:100%; padding:12px; border:1.5px solid #e2e8f0; border-radius:10px; font-size:14px;">
                </div>
                
                <div class="form-group" style="margin-bottom:15px;">
                    <label style="display:block; font-size:13px; font-weight:700; margin-bottom:6px; color:#334155;">Mobile Number *</label>
                    <input type="tel" name="phone" placeholder="e.g. 03001234567" required style="width:100%; padding:12px; border:1.5px solid #e2e8f0; border-radius:10px; font-size:14px;">
                </div>
                
                <div class="form-group" style="margin-bottom:15px;">
                    <label style="display:block; font-size:13px; font-weight:700; margin-bottom:6px; color:#334155;">Select City *</label>
                    <select name="city" required style="width:100%; padding:12px; border:1.5px solid #e2e8f0; border-radius:10px; font-size:14px; background:white;">
                        <option value="">-- Choose Your City --</option>
                        <optgroup label="Main Cities">
                            <option value="Karachi">Karachi</option>
                            <option value="Lahore">Lahore</option>
                            <option value="Islamabad">Islamabad</option>
                            <option value="Faisalabad">Faisalabad</option>
                            <option value="Rawalpindi">Rawalpindi</option>
                            <option value="Multan">Multan</option>
                            <option value="Peshawar">Peshawar</option>
                            <option value="Quetta">Quetta</option>
                        </optgroup>
                        <optgroup label="Other Cities">
                            <option value="Gujranwala">Gujranwala</option>
                            <option value="Hyderabad">Hyderabad</option>
                            <option value="Sargodha">Sargodha</option>
                            <option value="Sialkot">Sialkot</option>
                            <option value="Bahawalpur">Bahawalpur</option>
                            <option value="Sukkur">Sukkur</option>
                            <option value="Sheikhupura">Sheikhupura</option>
                            <option value="Rahim Yar Khan">Rahim Yar Khan</option>
                            <option value="Jhang">Jhang</option>
                            <option value="Dera Ghazi Khan">Dera Ghazi Khan</option>
                            <option value="Gujrat">Gujrat</option>
                            <option value="Sahiwal">Sahiwal</option>
                            <option value="Wah Cantonment">Wah Cantonment</option>
                            <option value="Mardan">Mardan</option>
                            <option value="Kasur">Kasur</option>
                            <option value="Okara">Okara</option>
                            <option value="Mingora">Mingora</option>
                            <option value="Nawabshah">Nawabshah</option>
                            <option value="Chiniot">Chiniot</option>
                            <option value="Mirpur Khas">Mirpur Khas</option>
                            <option value="Burewala">Burewala</option>
                            <option value="Jhelum">Jhelum</option>
                        </optgroup>
                    </select>
                </div>
                
                <div class="form-group" style="margin-bottom:15px;">
                    <label style="display:block; font-size:13px; font-weight:700; margin-bottom:6px; color:#334155;">Full Address *</label>
                    <textarea name="address" placeholder="Area, Mohalla, Near landmark etc." required style="width:100%; padding:12px; border:1.5px solid #e2e8f0; border-radius:10px; font-size:14px; min-height:80px;"></textarea>
                </div>
                
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px;">
                    <div class="form-group" style="margin-bottom:15px;">
                        <label style="display:block; font-size:13px; font-weight:700; margin-bottom:6px; color:#64748b;">House No (Opt)</label>
                        <input type="text" name="house_no" placeholder="e.g. 123" style="width:100%; padding:12px; border:1.5px solid #e2e8f0; border-radius:10px; font-size:14px;">
                    </div>
                    <div class="form-group" style="margin-bottom:15px;">
                        <label style="display:block; font-size:13px; font-weight:700; margin-bottom:6px; color:#64748b;">Street No (Opt)</label>
                        <input type="text" name="street_no" placeholder="e.g. 5A" style="width:100%; padding:12px; border:1.5px solid #e2e8f0; border-radius:10px; font-size:14px;">
                    </div>
                </div>
                
                <button type="submit" id="submitBtn" class="btn btn-primary" style="width:100%; padding:14px; font-weight:700; border-radius:12px; margin-top:10px;">
                    ✅ Confirm Order & WhatsApp
                </button>
            </form>
        </div>
    </div>
</div>

<!-- SUCCESS MODAL -->
<div class="lightbox" id="successModal" onclick="closeSuccessModal(event)">
    <div style="background:white; width:95%; max-width:400px; border-radius:20px; overflow:hidden; position:relative; text-align:center; padding:40px 30px; animation: slideUp 0.3s ease;">
        <div style="width:70px; height:70px; background:#d1fae5; color:#10b981; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:32px; margin:0 auto 20px;">
            <i class="fas fa-check"></i>
        </div>
        <h2 style="font-size:24px; font-weight:800; color:#0d1b4b; margin-bottom:10px;">Order Received!</h2>
        <p style="color:#64748b; font-size:15px; line-height:1.6; margin-bottom:25px;">
            Thank you for your order! It has been successfully recorded in our system. Our team will review it and contact you soon.
        </p>
        <button onclick="closeSuccessModal()" class="btn btn-primary" style="width:100%; justify-content:center; padding:14px; font-weight:700; border-radius:12px;">
            ✅ Got it, Thanks!
        </button>
    </div>
</div>

<!-- LIGHTBOX -->
<div class="lightbox" id="lightbox" onclick="closeLightbox(event)">
    <button class="lightbox-close" onclick="closeLightbox()">✕</button>
    <img id="lightboxImg" src="" alt="">
    <div class="lightbox-caption" id="lightboxCaption"></div>
</div>

<!-- FOOTER -->
<footer class="footer">
    <div class="footer-grid">
        <div class="footer-brand"><div class="logo-text">Ecomedge</div><p>Pakistan's trusted eCommerce order management system.</p></div>
        <div class="footer-col"><h4>Pages</h4><ul><li><a href="index.php">Home</a></li><li><a href="about.php">About</a></li><li><a href="gallery.php">Gallery</a></li><li><a href="contact.php">Contact</a></li></ul></div>
        <div class="footer-col"><h4>System</h4><ul><li><a href="login.php">Login</a></li><li><a href="signup.php">Signup</a></li></ul></div>
        <div class="footer-col"><h4>Contact</h4><ul><li><a href="https://wa.me/<?= $whatsappNum ?>" target="_blank">📱 WhatsApp</a></li><li><a href="#">✉️ Email</a></li></ul></div>
    </div>
    <div class="footer-bottom"><span>© 2024 Ecomedge. All rights reserved.</span></div>
</footer>

<script>
window.addEventListener('scroll', () => {
    document.getElementById('navbar').classList.toggle('scrolled', window.scrollY > 20);
});

function filterGallery(cat, btn) {
    document.querySelectorAll('.gal-filter-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.querySelectorAll('.gallery-item').forEach(item => {
        if (cat === 'all' || item.dataset.cat === cat) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
}

function openLightbox(src, caption) {
    document.getElementById('lightboxImg').src = src;
    document.getElementById('lightboxCaption').textContent = caption;
    document.getElementById('lightbox').classList.add('open');
    document.body.style.overflow = 'hidden';
}

function closeLightbox(e) {
    if (!e || e.target === document.getElementById('lightbox') || e.target.classList.contains('lightbox-close')) {
        document.getElementById('lightbox').classList.remove('open');
        document.body.style.overflow = '';
    }
}

// Order Modal Functions
function openOrderModal(id, title, price) {
    document.getElementById('orderProductId').value = id;
    document.getElementById('hiddenProductName').value = title;
    document.getElementById('orderProductName').textContent = title;
    document.getElementById('orderProductPrice').textContent = price;
    document.getElementById('orderModal').classList.add('open');
    document.body.style.overflow = 'hidden';
}

function closeOrderModal(e) {
    if (!e || e.target === document.getElementById('orderModal') || e.target.classList.contains('lightbox-close')) {
        document.getElementById('orderModal').classList.remove('open');
        document.body.style.overflow = '';
    }
}

async function submitOrder(e) {
    e.preventDefault();
    const form = e.target;
    const btn = document.getElementById('submitBtn');
    const formData = new FormData(form);
    
    btn.disabled = true;
    btn.style.opacity = '0.7';
    btn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Placing Order...';
    
    try {
        const response = await fetch('ajax/process_order.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        
        if (result.success) {
            // Success! Hide Order Modal
            document.getElementById('orderModal').classList.remove('open');
            
            // Show Success Modal
            const successModal = document.getElementById('successModal');
            successModal.classList.add('open');
            
            // Reset original order button state
            btn.innerHTML = '✅ Order Saved!';
            
            // Reset form
            form.reset();
            setTimeout(() => {
                btn.disabled = false;
                btn.style.opacity = '1';
                btn.innerHTML = '✅ Confirm Order & WhatsApp';
            }, 3000);
            
        } else {
            alert('Error: ' + result.message);
            btn.disabled = false;
            btn.style.opacity = '1';
            btn.innerHTML = '✅ Confirm Order & WhatsApp';
        }
    } catch (error) {
        console.error(error);
        alert('Connection error. Please check your internet and try again.');
        btn.disabled = false;
        btn.style.opacity = '1';
        btn.innerHTML = '✅ Confirm Order & WhatsApp';
    }
}

function closeSuccessModal(e) {
    if (!e || e.target === document.getElementById('successModal') || e.target.tagName === 'BUTTON' || e.target.closest('button')) {
        document.getElementById('successModal').classList.remove('open');
        document.body.style.overflow = '';
    }
}

document.addEventListener('keydown', e => { 
    if (e.key === 'Escape') {
        closeLightbox({target: document.getElementById('lightbox')});
        closeOrderModal({target: document.getElementById('orderModal')});
        closeSuccessModal({target: document.getElementById('successModal')});
    }
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
