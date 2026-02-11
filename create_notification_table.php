<?php
// Run this file once (open in browser) to create the NOTIFICATION table if it doesn't exist.
include_once('includes/config.php');

$createTbl = "CREATE TABLE IF NOT EXISTS notification (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    userId INT NOT NULL,
    productId INT NOT NULL,
    qty DECIMAL(10,4) DEFAULT NULL,
    unit VARCHAR(10) DEFAULT 'kg',
    action VARCHAR(50) DEFAULT 'unknown',
    meta TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if (mysqli_query($con, $createTbl)) {
    echo "NOTIFICATION table is present or created successfully.";
} else {
    echo "Error creating NOTIFICATION table: " . mysqli_error($con);
}

?>