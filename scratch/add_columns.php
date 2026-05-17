<?php
require_once dirname(__DIR__) . '/config.php';
$db = getDB();
$sql = "ALTER TABLE reports 
        ADD COLUMN tcs_orders INT NOT NULL DEFAULT 0, 
        ADD COLUMN local_parcel_orders INT NOT NULL DEFAULT 0";
if ($db->query($sql)) {
    echo "Columns added successfully!";
} else {
    echo "Error: " . $db->error;
}
$db->close();
?>
