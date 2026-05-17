<?php
date_default_timezone_set('Asia/Karachi');
// =====================================================
// ECOMEDGE - Database Configuration
// =====================================================

// Automatic Environment Detection
if (PHP_SAPI === 'cli' || $_SERVER['HTTP_HOST'] === 'localhost' || $_SERVER['HTTP_HOST'] === '127.0.0.1') {
    // LOCAL (XAMPP) Settings
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'ecomedge_db');
    define('SITE_URL', 'http://localhost/Ecom edge');
} else {
    // PRODUCTION (InfinityFree) Settings
    define('DB_HOST', 'sql204.infinityfree.com');
    define('DB_USER', 'u938366168_abc');
    define('DB_PASS', '7!sq:X&F85B');
    define('DB_NAME', 'u938366168_abc');
    define('SITE_URL', 'https://alibabaclothing.online/');
}
define('SITE_NAME', 'Ecomedge');

// Connect to Database
function getDB() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        die(json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]));
    }
    $conn->set_charset('utf8mb4');
    return $conn;
}

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Auth check functions
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . SITE_URL . '/login.php');
        exit;
    }
    
    // Verify user still exists in DB (Ghost session prevention)
    $db = getDB();
    $uid = $_SESSION['user_id'];
    $check = $db->query("SELECT id FROM users WHERE id = $uid");
    if ($check->num_rows === 0) {
        session_destroy();
        header('Location: ' . SITE_URL . '/login.php?error=account_deleted');
        exit;
    }
    $db->close();
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: ' . SITE_URL . '/dashboard/index.php');
        exit;
    }
}
