<?php
require_once 'config.php';
$db = getDB();

// 1. Create product_orders table
$db->query("CREATE TABLE IF NOT EXISTS product_orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT,
    customer_name VARCHAR(100),
    phone VARCHAR(20),
    city VARCHAR(100),
    address TEXT,
    house_no VARCHAR(50),
    street_no VARCHAR(50),
    status ENUM('pending', 'completed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// 2. Create system_settings table
$db->query("CREATE TABLE IF NOT EXISTS system_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(50) UNIQUE,
    setting_value TEXT
)");

// 3. Insert default WhatsApp number
$db->query("INSERT IGNORE INTO system_settings (setting_key, setting_value) VALUES ('whatsapp_number', '923001234567')");

echo "Database successfully updated!";
?>
