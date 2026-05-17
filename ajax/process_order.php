<?php
// ajax/process_order.php - Handles customer orders
require_once '../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = getDB();
    
    $productId = (int)($_POST['product_id'] ?? 0);
    $name      = $db->real_escape_string($_POST['name'] ?? '');
    $phone     = $db->real_escape_string($_POST['phone'] ?? '');
    $city      = $db->real_escape_string($_POST['city'] ?? '');
    $address   = $db->real_escape_string($_POST['address'] ?? '');
    $house     = $db->real_escape_string($_POST['house_no'] ?? '');
    $street    = $db->real_escape_string($_POST['street_no'] ?? '');
    
    if ($productId && $name && $phone && $city && $address) {
        $stmt = $db->prepare("INSERT INTO product_orders (product_id, customer_name, phone, city, address, house_no, street_no) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('issssss', $productId, $name, $phone, $city, $address, $house, $street);
        
        if ($stmt->execute()) {
            // Get WhatsApp number from settings
            $waRes = $db->query("SELECT setting_value FROM system_settings WHERE setting_key='whatsapp_number'");
            $whatsapp = ($waRes && $waRes->num_rows > 0) ? $waRes->fetch_assoc()['setting_value'] : '923001234567';
            
            echo json_encode(['success' => true, 'whatsapp' => $whatsapp]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $db->error]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
    }
    
    $db->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}
