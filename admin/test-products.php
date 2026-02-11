<?php
session_start();
include_once('includes/config.php');

echo "<h2>Database Diagnostics</h2>";

// Test 1: Check if products table exists
$tablesQuery = mysqli_query($con, "SHOW TABLES LIKE 'products'");
if(mysqli_num_rows($tablesQuery) > 0) {
    echo "<p style='color:green'>✓ Products table EXISTS</p>";
} else {
    echo "<p style='color:red'>✗ Products table NOT FOUND</p>";
    exit;
}

// Test 2: Count products
$countQuery = mysqli_query($con, "SELECT COUNT(*) as cnt FROM products");
$countResult = mysqli_fetch_array($countQuery);
echo "<p>Total products in database: <strong>" . $countResult['cnt'] . "</strong></p>";

if($countResult['cnt'] == 0) {
    echo "<p style='color:orange'>⚠ WARNING: Products table is EMPTY. You need to add products first.</p>";
    exit;
}

// Test 3: List all products
echo "<h3>Products List:</h3>";
$listQuery = mysqli_query($con, "SELECT id, productName, Quantity FROM products ORDER BY productName ASC");
echo "<table border='1' cellpadding='10'>";
echo "<tr><th>" . addslashes(__('ID')) . "</th><th>" . addslashes(__('PRODUCT_NAME')) . "</th><th>" . addslashes(__('QUANTITY_KG')) . "</th></tr>";
while($row = mysqli_fetch_array($listQuery)) {
    echo "<tr>";
    echo "<td>" . $row['id'] . "</td>";
    echo "<td>" . $row['productName'] . "</td>";
    echo "<td>" . formatQuantityNumber($row['Quantity']) . "</td>";
    echo "</tr>";
}
echo "</table>";

echo "<p><a href='manage-restock.php'>" . __('BACK_TO') . " " . __('MANAGE_RESTOCK') . "</a></p>";
?>
