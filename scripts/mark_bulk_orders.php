<?php
// One-off script to mark orders that include 'Apel' as inventory (bulk) orders.
// Usage: visit this script in browser once: /scripts/mark_bulk_orders.php
session_start();
include_once('../includes/config.php');
if(strlen($_SESSION['id'])==0){
    echo "Please login as a user to run this script."; exit;
}
// Update orders where ordersdetails contain products with name like 'Apel'
$q1 = "UPDATE orders o JOIN ordersdetails od ON od.orderNumber=o.orderNumber JOIN products p ON p.id=od.productId SET o.txnType='inventory' WHERE p.productName LIKE '%Apel%'";
$r1 = mysqli_query($con, $q1);
$affected1 = mysqli_affected_rows($con);
// Update orders where flat items table contains 'Apel'
$q2 = "UPDATE orders o JOIN order_items_flat f ON f.orderNumber=o.orderNumber SET o.txnType='inventory' WHERE f.productName LIKE '%Apel%'";
$r2 = mysqli_query($con, $q2);
$affected2 = mysqli_affected_rows($con);
// Also update a specific order number if provided via GET for convenience
$affected3 = 0;
if (isset($_GET['orderno'])) {
    $ord = mysqli_real_escape_string($con, $_GET['orderno']);
    $q3 = "UPDATE orders SET txnType='inventory' WHERE orderNumber='".$ord."'";
    mysqli_query($con, $q3);
    $affected3 = mysqli_affected_rows($con);
}
?>
<!doctype html>
<html><head><meta charset="utf-8"><title>Mark Bulk Orders</title></head><body>
<h3>Mark Bulk Orders — Report</h3>
<p>Orders updated via orderdetails join: <?php echo intval($affected1);?></p>
<p>Orders updated via flat items table: <?php echo intval($affected2);?></p>
<?php if($affected3>0) echo "<p>Specific order updated: $affected3</p>"; ?>
<p><a href="../my-wishlist.php"><?php echo __('BACK_TO'); ?> <?php echo __('CHECKOUT_HISTORY'); ?></a></p>
</body></html>