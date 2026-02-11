<?php
session_start();
include_once('includes/config.php');

echo "<h2>Current Product Quantities in Database</h2>";
$getQuery = mysqli_query($con, "SELECT id, productName, Quantity FROM products ORDER BY productName ASC");
echo "<table border='1' cellpadding='10'>";
echo "<tr><th>" . addslashes(__('ID')) . "</th><th>" . addslashes(__('PRODUCT_NAME')) . "</th><th>" . addslashes(__('QUANTITY_KG')) . "</th><th>Quantity (grams)</th></tr>";
while($row = mysqli_fetch_array($getQuery)) {
    $quantityInGrams = (float)$row['Quantity'] * 1000;
    echo "<tr>";
    echo "<td>" . $row['id'] . "</td>";
    echo "<td>" . $row['productName'] . "</td>";
    echo "<td>" . formatQuantityNumber($row['Quantity']) . "</td>";
    echo "<td>" . round($quantityInGrams) . "</td>";
    echo "</tr>";
}
echo "</table>";

// Check debug log
echo "<h3>Recent Debug Log:</h3>";
if(file_exists(__DIR__ . '/restock_debug.log')) {
    $logContent = file_get_contents(__DIR__ . '/restock_debug.log');
    $logLines = array_slice(explode("\n", $logContent), -15);
    echo "<pre>" . implode("\n", $logLines) . "</pre>";
} else {
    echo "<p>" . addslashes(__('NO_DEBUG_LOG_FOUND')) . "</p>";
}
?>
