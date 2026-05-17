<?php
// dashboard/gallery_manage.php - Admin manages gallery items
require_once '../config.php';
requireAdmin();

$db = getDB();
$success = $error = '';

// Handle Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $res = $db->query("SELECT image_path FROM gallery WHERE id=$id");
    if ($row = $res->fetch_assoc()) {
        $path = '../' . $row['image_path'];
        if (file_exists($path)) unlink($path);
        $db->query("DELETE FROM gallery WHERE id=$id");
        $success = "Item deleted successfully!";
    }
}

// Handle Upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload'])) {
    $title = $db->real_escape_string($_POST['title']);
    $price = $db->real_escape_string($_POST['price']);
    $desc  = $db->real_escape_string($_POST['description']);
    $cat   = $db->real_escape_string($_POST['category'] ?? 'general');
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $targetDir = "../assets/images/gallery/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
        
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $fileName = time() . '_' . uniqid() . '.' . $ext;
        $targetFile = $targetDir . $fileName;
        $dbPath = "assets/images/gallery/" . $fileName;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
            $stmt = $db->prepare("INSERT INTO gallery (title, image_path, caption, price, category) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param('sssss', $title, $dbPath, $desc, $price, $cat);
            if ($stmt->execute()) {
                $success = "Product uploaded successfully!";
            } else {
                $error = "Database error: " . $db->error;
            }
        } else {
            $error = "Failed to upload image.";
        }
    } else {
        $error = "Please select an image.";
    }
}

$galleryItems = $db->query("SELECT * FROM gallery ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
$db->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Gallery - Ecomedge</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
        .gallery-admin-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px; margin-top: 30px; }
        .gallery-item-card { background: white; border-radius: 12px; overflow: hidden; border: 1px solid #e2e8f0; position: relative; }
        .gallery-item-card img { width: 100%; height: 180px; object-fit: cover; }
        .gallery-item-info { padding: 15px; }
        .gallery-item-info h4 { margin-bottom: 5px; font-size: 16px; color: #1e293b; }
        .gallery-item-info .price { color: #1a6bff; font-weight: 700; font-size: 15px; }
        .gallery-item-actions { padding: 10px 15px; border-top: 1px solid #f1f5f9; display: flex; justify-content: flex-end; }
        .delete-btn { color: #ef4444; text-decoration: none; font-size: 13px; font-weight: 600; }
        .delete-btn:hover { text-decoration: underline; }
    </style>
</head>
<body class="dash-body">
<?php include 'includes/sidebar.php'; ?>

<div class="main-content">
    <div class="topbar">
        <div class="topbar-left">
            <h2>🖼️ Manage Gallery / Products</h2>
            <p>Upload new products with prices to the public gallery</p>
        </div>
    </div>

    <div class="page-body">
        <?php if ($success): ?>
            <div style="background:#d1fae5; color:#065f46; padding:15px; border-radius:8px; margin-bottom:20px; border:1px solid #6ee7b7;"><?= $success ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div style="background:#fee2e2; color:#991b1b; padding:15px; border-radius:8px; margin-bottom:20px; border:1px solid #fca5a5;"><?= $error ?></div>
        <?php endif; ?>

        <div class="dash-panel">
            <div class="panel-header"><h3>📤 Upload New Product</h3></div>
            <div class="panel-body">
                <form method="POST" enctype="multipart/form-data" class="grid-2" style="gap:20px">
                    <div class="dash-form-group">
                        <label>Product Title / Name</label>
                        <input type="text" name="title" placeholder="e.g. Premium Earbuds Pro" required>
                    </div>
                    <div class="dash-form-group">
                        <label>Price (PKR)</label>
                        <input type="text" name="price" placeholder="e.g. 2,500" required>
                    </div>
                    <div class="dash-form-group">
                        <label>Category</label>
                        <select name="category" style="width:100%; padding:10px; border-radius:8px; border:1px solid #e2e8f0;">
                            <option value="general">General</option>
                            <option value="team">Team</option>
                            <option value="packaging">Packaging</option>
                            <option value="office">Office</option>
                            <option value="courier">Courier</option>
                            <option value="products">Products</option>
                        </select>
                    </div>
                    <div class="dash-form-group">
                        <label>Product Image</label>
                        <input type="file" name="image" accept="image/*" required>
                    </div>
                    <div class="dash-form-group" style="grid-column: span 2">
                        <label>Detail / Description</label>
                        <textarea name="description" placeholder="Short description of the product..." style="min-height:80px; width:100%; border:1px solid #e2e8f0; border-radius:8px; padding:12px;"></textarea>
                    </div>
                    <div style="grid-column: span 2">
                        <button type="submit" name="upload" class="filter-btn success" style="width:100%; padding:14px; font-weight:700;">🚀 Upload to Gallery</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="panel-header" style="margin-top:40px"><h3>🖼️ Current Gallery Items</h3></div>
        <div class="gallery-admin-grid">
            <?php foreach ($galleryItems as $item): ?>
            <div class="gallery-item-card">
                <img src="../<?= $item['image_path'] ?>" alt="<?= htmlspecialchars($item['title']) ?>">
                <div class="gallery-item-info">
                    <h4><?= htmlspecialchars($item['title']) ?></h4>
                    <div class="price">Rs. <?= htmlspecialchars($item['price']) ?></div>
                    <p style="font-size:12px; color:#64748b; margin-top:5px;"><?= htmlspecialchars($item['caption']) ?></p>
                </div>
                <div class="gallery-item-actions">
                    <a href="?delete=<?= $item['id'] ?>" class="delete-btn" onclick="return confirm('Delete this item?')">🗑️ Delete</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
</body>
</html>
