<?php
// dashboard/team_manage.php - Admin manages team members
require_once '../config.php';
requireAdmin();

$db = getDB();
$success = $error = '';

// Handle Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $res = $db->query("SELECT image_path FROM team WHERE id=$id");
    if ($row = $res->fetch_assoc()) {
        if ($row['image_path']) {
            $path = '../' . $row['image_path'];
            if (file_exists($path)) unlink($path);
        }
        $db->query("DELETE FROM team WHERE id=$id");
        $success = "Team member removed successfully!";
    }
}

// Handle Add
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_member'])) {
    $name = $db->real_escape_string($_POST['name']);
    $role = $db->real_escape_string($_POST['role']);
    $desc = $db->real_escape_string($_POST['description']);
    $isFounder = isset($_POST['is_founder']) ? 1 : 0;
    
    $imagePath = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $targetDir = "../assets/images/team/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
        
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $fileName = time() . '_' . uniqid() . '.' . $ext;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetDir . $fileName)) {
            $imagePath = "assets/images/team/" . $fileName;
        }
    }

    $stmt = $db->prepare("INSERT INTO team (name, role, description, image_path, is_founder) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param('ssssi', $name, $role, $desc, $imagePath, $isFounder);
    if ($stmt->execute()) {
        $success = "Team member added successfully!";
    } else {
        $error = "Error adding member: " . $db->error;
    }
}

$teamRes = $db->query("SELECT * FROM team ORDER BY created_at DESC");
$teamMembers = $teamRes->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Team - Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="dash-body">
    <div class="dashboard-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="main-content">
            <header class="topbar">
                <div class="topbar-left">
                    <h2>Manage Team Members</h2>
                    <p>Add or remove members from the public About page</p>
                </div>
            </header>

            <div class="page-body">
                <?php if ($success): ?>
                    <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= $success ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div>
                <?php endif; ?>

                <div class="dash-panel">
                    <div class="panel-header">
                        <h3>Add New Team Member</h3>
                    </div>
                    <div class="panel-body">
                        <form action="" method="POST" enctype="multipart/form-data">
                            <div class="grid-2">
                                <div class="dash-form-group">
                                    <label>Member Name</label>
                                    <input type="text" name="name" required placeholder="e.g. Rana Hassan">
                                </div>
                                <div class="dash-form-group">
                                    <label>Role / Position</label>
                                    <input type="text" name="role" required placeholder="e.g. Customer Confirmation">
                                </div>
                            </div>
                            <div class="dash-form-group">
                                <label>Short Description</label>
                                <textarea name="description" rows="2" placeholder="Describe their responsibilities..."></textarea>
                            </div>
                            <div class="grid-2">
                                <div class="dash-form-group">
                                    <label>Profile Image</label>
                                    <input type="file" name="image" accept="image/*">
                                </div>
                                <div class="dash-form-group">
                                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer; margin-top:35px;">
                                        <input type="checkbox" name="is_founder" value="1" style="width:20px; height:20px;">
                                        <span>Mark as Founder / CEO (Bigger Card)</span>
                                    </label>
                                </div>
                            </div>
                            <div style="margin-top:20px; text-align:right">
                                <button type="submit" name="add_member" class="btn-primary" style="padding: 12px 30px; border-radius: 8px; border:none; cursor:pointer; font-weight:700">Add Member</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="dash-panel" style="margin-top:30px">
                    <div class="panel-header">
                        <h3>Current Team Members</h3>
                    </div>
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table class="dash-table">
                                <thead>
                                    <tr>
                                        <th>Image</th>
                                        <th>Name</th>
                                        <th>Role</th>
                                        <th style="text-align:right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($teamMembers)): ?>
                                        <tr><td colspan="4" style="text-align:center; padding: 40px;">No team members added yet.</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($teamMembers as $m): ?>
                                        <tr>
                                            <td>
                                                <?php if ($m['image_path']): ?>
                                                    <img src="../<?= $m['image_path'] ?>" width="45" height="45" style="border-radius:50%; object-fit:cover; border: 2px solid #eee;">
                                                <?php else: ?>
                                                    <div style="width:45px; height:45px; border-radius:50%; background:#f0f4ff; display:flex; align-items:center; justify-content:center; color:#1a6bff;"><i class="fas fa-user"></i></div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <strong><?= htmlspecialchars($m['name']) ?></strong>
                                                <?php if ($m['is_founder']): ?>
                                                    <span class="badge badge-success" style="font-size:10px; padding:2px 6px; margin-left:5px">Founder</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><span class="badge badge-info"><?= htmlspecialchars($m['role']) ?></span></td>
                                            <td style="text-align:right">
                                                <a href="?delete=<?= $m['id'] ?>" style="color:#ef4444; font-size:18px;" onclick="return confirm('Remove this member?')"><i class="fas fa-trash"></i></a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
<?php $db->close(); ?>
