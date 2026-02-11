<?php
// Usage: php update_santan_to_liters.php [--apply]
// Without --apply it will perform a dry-run and show what would change.

require_once __DIR__ . '/../includes/config.php';

$apply = in_array('--apply', $argv);

// find products whose name contains 'santan' (case-insensitive)
$stmt = mysqli_query($con, "SELECT id, productName, Availablein, Quantity FROM products WHERE LOWER(productName) LIKE '%santan%'");
$rows = [];
while ($r = mysqli_fetch_assoc($stmt)) $rows[] = $r;

if (count($rows) === 0) {
    echo "No products with name containing 'santan' found.\n";
    exit(0);
}

echo "Found " . count($rows) . " product(s) matching 'santan':\n";
foreach ($rows as $r) {
    echo " - [id={$r['id']}] {$r['productName']}  (Availablein: {$r['Availablein']}, Quantity: {$r['Quantity']})\n";
}

// Summary of actions to perform:
// 1) UPDATE products SET Availablein = 'L' for matched products
// 2) Update notification, usage, order_items_flat rows for these productIds: change unit 'kg'->'L' (qty unchanged), change 'g'->'L' and convert qty = qty/1000

$productIds = array_column($rows, 'id');
$productIdsList = implode(',', array_map('intval', $productIds));

echo "\nPlanned updates (dry-run):\n";
echo " - products: set Availablein = 'L' for product ids: {$productIdsList}\n";
echo " - notification: for these product ids, convert unit 'g' -> 'L' (qty->qty/1000), set 'kg' -> 'L' (qty unchanged)\n";
echo " - `usage`: same as notification\n";
echo " - `order_items_flat`: set unit to 'L' where productId in (...) and unit in ('kg','g')\n";

if (!$apply) {
    echo "\nDry-run mode. No changes applied. To apply changes re-run with --apply\n";
    exit(0);
}

// Apply changes
mysqli_autocommit($con, false);
$ok = true;

// Update products
$updSql = "UPDATE products SET Availablein = 'L' WHERE id IN ({$productIdsList})";
if (!mysqli_query($con, $updSql)) {
    echo "Failed updating products: " . mysqli_error($con) . "\n";
    $ok = false;
}

// Helper to convert g->L qty (divide by 1000), and set unit
// notifications
$queries = [];
$queries[] = "UPDATE notification SET qty = qty/1000.0, unit = 'L' WHERE productId IN ({$productIdsList}) AND unit = 'g'";
$queries[] = "UPDATE notification SET unit = 'L' WHERE productId IN ({$productIdsList}) AND unit = 'kg'";

// usage
$queries[] = "UPDATE `usage` SET qty = qty/1000.0, unit = 'L' WHERE productId IN ({$productIdsList}) AND unit = 'g'";
$queries[] = "UPDATE `usage` SET unit = 'L' WHERE productId IN ({$productIdsList}) AND unit = 'kg'";

// order_items_flat
$queries[] = "UPDATE order_items_flat SET quantity = quantity/1000.0, unit = 'L' WHERE productId IN ({$productIdsList}) AND unit = 'g'";
$queries[] = "UPDATE order_items_flat SET unit = 'L' WHERE productId IN ({$productIdsList}) AND unit = 'kg'";

foreach ($queries as $q) {
    if (!mysqli_query($con, $q)) {
        echo "Failed query: " . $q . " -- " . mysqli_error($con) . "\n";
        $ok = false;
    }
}

if ($ok) {
    mysqli_commit($con);
    echo "All updates applied successfully.\n";
} else {
    mysqli_rollback($con);
    echo "Errors occurred; rolled back changes. Check output above.\n";
}

?>