<?php
// Simulate an admin restock action for testing (run from CLI)
include __DIR__ . '/../includes/config.php';
$adminId = 1; // assume admin id 1
$productId = 10; // Alphonso Mango
$restockQty = 10.0; // kg (changed to 10 kg for this run)
$restockUnit = 'kg';
$restockNotes = 'CLI test restock';

// update products
$restockQtyKg = ($restockUnit === 'g') ? ($restockQty/1000.0) : $restockQty;
$restockQtyKg = round($restockQtyKg, 4);
$upd = mysqli_query($con, "UPDATE products SET Quantity = Quantity + $restockQtyKg WHERE id = '$productId'");
if(!$upd){ echo "Update failed: " . mysqli_error($con) . "\n"; exit(1); }
file_put_contents(__DIR__ . '/../admin/restock_debug.log', date('c') . " SIMULATE: Updated product $productId by $restockQtyKg kg\n", FILE_APPEND);
// insert into usage
$logSQL = "INSERT INTO `usage`(productId, qty, unit, usedBy, action, notes, usedAt) VALUES('$productId', '$restockQtyKg', '$restockUnit', '$adminId', 'restock', '".mysqli_real_escape_string($con,$restockNotes)."', NOW())";
$log = mysqli_query($con, $logSQL);
if(!$log){ echo "Usage insert failed: " . mysqli_error($con) . "\n"; }
else { echo "Usage logged.\n"; }

// ensure notification table exists with action/meta columns
$createNotifTbl = "CREATE TABLE IF NOT EXISTS notification (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    userId INT NOT NULL,
    productId INT NOT NULL,
    qty DECIMAL(10,4) DEFAULT NULL,
    unit VARCHAR(10) DEFAULT 'kg',
    action VARCHAR(50) DEFAULT 'unknown',
    meta TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
@mysqli_query($con, $createNotifTbl);
// add columns if missing
$colCheck = mysqli_query($con, "SHOW COLUMNS FROM notification LIKE 'action'");
if (!$colCheck || mysqli_num_rows($colCheck) === 0) {
    @mysqli_query($con, "ALTER TABLE notification ADD COLUMN action VARCHAR(50) DEFAULT 'unknown'");
}
$metaCheck = mysqli_query($con, "SHOW COLUMNS FROM notification LIKE 'meta'");
if (!$metaCheck || mysqli_num_rows($metaCheck) === 0) {
    @mysqli_query($con, "ALTER TABLE notification ADD COLUMN meta TEXT DEFAULT NULL");
}

<?php
// This simulation script has been disabled.
echo "simulate_restock.php has been disabled.\n";
exit;
?>