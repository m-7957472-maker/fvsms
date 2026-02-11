<?php
session_start();
include_once(__DIR__ . '/../includes/config.php');

echo "<h2>Usage Table Diagnostics</h2>";

// Check if usage table exists
$tablesQuery = mysqli_query($con, "SHOW TABLES LIKE 'usage'");
if(mysqli_num_rows($tablesQuery) > 0) {
    echo "<p style='color:green'>✓ Usage table EXISTS</p>";
} else {
    echo "<p style='color:red'>✗ Usage table NOT FOUND - creating it now...</p>";
    $createTableQuery = "CREATE TABLE IF NOT EXISTS `usage` (
        id INT AUTO_INCREMENT PRIMARY KEY,
        productId INT NOT NULL,
        qty DECIMAL(10,4),
        unit VARCHAR(10),
        usedBy INT,
        action VARCHAR(50),
        notes TEXT,
        usedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (productId) REFERENCES products(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    if(mysqli_query($con, $createTableQuery)) {
        echo "<p style='color:green'>✓ Usage table created successfully</p>";
    } else {
        echo "<p style='color:red'>✗ Failed to create: " . mysqli_error($con) . "</p>";
    }
}

// Count records
$countQuery = mysqli_query($con, "SELECT COUNT(*) as cnt FROM `usage`");
$countResult = mysqli_fetch_array($countQuery);
echo "<p>Total records in usage table: <strong>" . $countResult['cnt'] . "</strong></p>";

// Show all usage records
echo "<h3>All Usage Records:</h3>";
$listQuery = mysqli_query($con, "SELECT u.id, u.usedAt, u.qty, u.unit, u.action, p.productName, t.username FROM `usage` u JOIN products p ON u.productId = p.id JOIN tbladmin t ON u.usedBy = t.id ORDER BY u.usedAt DESC");
if(mysqli_num_rows($listQuery) == 0) {
    echo "<p style='color:orange'>" . addslashes(__('NO_RECORDS_FOUND')) . "</p>";
} else {
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>" . __('DATE_TIME') . "</th><th>" . __('PRODUCT') . "</th><th>" . __('QUANTITY') . "</th><th>" . __('UNIT') . "</th><th>" . __('ACTION') . "</th><th>" . __('ADMIN') . "</th></tr>";
    while($row = mysqli_fetch_array($listQuery)) {
        echo "<tr>";
        echo "<td>" . $row['usedAt'] . "</td>";
        echo "<td>" . $row['productName'] . "</td>";
        echo "<td>" . $row['qty'] . "</td>";
        echo "<td>" . $row['unit'] . "</td>";
        echo "<td>" . $row['action'] . "</td>";
        echo "<td>" . $row['username'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

echo "<p><a href='manage-restock.php'>" . __('BACK_TO') . " " . __('MANAGE_RESTOCK') . "</a></p>;"
?>
