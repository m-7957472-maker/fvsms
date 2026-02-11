<?php
session_start();
include_once(__DIR__ . '/../includes/config.php');

echo "<h2>Normalizing Product Quantities</h2>";

// First, check current Quantity column type
$checkQuery = mysqli_query($con, "DESCRIBE products");
echo "<p>Current table structure:</p>";
echo "<pre>";
while($row = mysqli_fetch_array($checkQuery)) {
    if($row['Field'] == 'Quantity') {
        echo "Field: " . $row['Field'] . " | Type: " . $row['Type'] . "\n";
    }
}
echo "</pre>";

// Mapping of current values to kg
$conversions = array(
    '250 gm' => 0.25,
    '1 KG' => 1,
    '100 gm' => 0.1,
    '50' => 0.05,
    '500 gm' => 0.5,
    '250 gm' => 0.25,
    '6 pcs' => 6,
    '6 pcs' => 6,
    '400' => 0.4,
    '100' => 0.1
);

// Get all products and convert
$getQuery = mysqli_query($con, "SELECT id, Quantity FROM products");
echo "<h3>Converting quantities:</h3>";
while($row = mysqli_fetch_array($getQuery)) {
    $id = $row['id'];
    $currentQty = trim($row['Quantity']);
    
    // Parse and convert
    $newQty = 0;
    if(stripos($currentQty, 'gm') !== false) {
        $num = (float)str_ireplace('gm', '', $currentQty);
        $newQty = $num / 1000;
    } elseif(stripos($currentQty, 'kg') !== false) {
        $newQty = (float)str_ireplace('kg', '', $currentQty);
    } elseif(stripos($currentQty, 'pcs') !== false) {
        $newQty = (float)str_ireplace('pcs', '', $currentQty);
    } else {
        $newQty = (float)$currentQty / 1000; // Assume grams
    }
    
    $newQty = round($newQty, 4);
    
    $updateQuery = mysqli_query($con, "UPDATE products SET Quantity = $newQty WHERE id = $id");
    echo "ID: $id | Old: '$currentQty' → New: $newQty kg | ";
    echo ($updateQuery ? "✓ Updated" : "✗ Failed") . "<br>";
}

echo "<p><a href='test-products.php'>" . __('BACK_TO') . " " . __('MANAGE_RESTOCK') . "</a></p>";
?>
