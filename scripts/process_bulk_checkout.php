<?php
session_start();
include_once(__DIR__ . '/../includes/config.php');
include_once(__DIR__ . '/../includes/lang.php');
if (!isset($_SESSION['id']) || intval($_SESSION['id'])<=0) {
    echo "<script>alert('".addslashes(__('LOGIN_REQUIRED_ADD_TO_CART'))."'); window.location='../login.php';</script>";
    exit;
}
$uid = intval($_SESSION['id']);
// read cart
$cartQ = mysqli_query($con, "SELECT id, productId, productQty FROM cart WHERE userId='".intval($uid)."'");
if (!$cartQ || mysqli_num_rows($cartQ) === 0) {
    echo "<script>alert('".addslashes(__('YOUR_CART_IS_EMPTY'))."'); window.location='../my-cart.php';</script>";
    exit;
}
$items = [];
$problems = [];
while ($r = mysqli_fetch_assoc($cartQ)) {
    $items[] = $r;
}
// check stock for all
foreach ($items as $it) {
    $pid = intval($it['productId']);
    $qty = floatval($it['productQty']);
    $pRes = mysqli_query($con, "SELECT Quantity, productName FROM products WHERE id='".$pid."' LIMIT 1");
    if (!$pRes || mysqli_num_rows($pRes) === 0) { $problems[] = "Product #$pid not found"; continue; }
    $prow = mysqli_fetch_assoc($pRes);
    $available = floatval($prow['Quantity']);
    if ($qty > $available) {
        $problems[] = sprintf('%s: requested %s kg, available %s kg', $prow['productName'], $qty, $available);
    }
}
if (!empty($problems)) {
    $msg = implode("\\n", $problems);
    echo "<script>alert('".addslashes($msg)."'); window.location='../my-cart.php';</script>";
    exit;
}
// all good — apply changes
$orderno = mt_rand(100000000,999999999);
$ordNoEsc = mysqli_real_escape_string($con, $orderno);
// Accept optional purpose from form
$purpose = isset($_POST['purpose']) ? trim($_POST['purpose']) : '';
// Ensure orders table has a purpose column
$c = @mysqli_query($con, "SHOW COLUMNS FROM `orders` LIKE 'purpose'");
if (!$c || mysqli_num_rows($c) === 0) {
    @mysqli_query($con, "ALTER TABLE orders ADD COLUMN purpose TEXT DEFAULT NULL");
}
// Insert order including purpose
$insOrderSql = "INSERT INTO orders (orderNumber,userId,addressId,totalAmount,txnType,txnNumber" . (strlen($purpose) ? ',purpose' : '') . ") VALUES ('".$ordNoEsc."', '".intval($uid)."', 0, 0, 'inventory', ''" . (strlen($purpose) ? ",'".mysqli_real_escape_string($con,$purpose)."'" : '') . ")";
$insOrder = mysqli_query($con, $insOrderSql);
if (!$insOrder) {
    echo "<script>alert('".addslashes(__('SOMETHING_WENT_WRONG'))."'); window.location='../my-cart.php';</script>";
    exit;
}
// create flat items and insert details, log usage, notifications, and update product quantities
foreach ($items as $it) {
    $pid = intval($it['productId']);
    $qty = floatval($it['productQty']);
    // deduct
    $upd = mysqli_query($con, "UPDATE products SET Quantity = Quantity - $qty WHERE id = $pid");
    if (!$upd) {
        @file_put_contents(__DIR__ . '/bulk_errors.log', date('c') . " - UPDATE failed for $pid: " . mysqli_error($con) . "\n", FILE_APPEND);
    }
    // usage (include purpose if provided)
    $notes = mysqli_real_escape_string($con, __('USER_CHECKOUT_NOTE'));
    if (strlen($purpose)) $notes .= ' - ' . mysqli_real_escape_string($con, $purpose);
    $uSql = "INSERT INTO `usage` (productId, qty, unit, usedBy, action, notes) VALUES ('".intval($pid)."', '".floatval($qty)."', 'kg', '".intval($uid)."', 'checkout', '".$notes."')";
    @mysqli_query($con, $uSql);
    // notification to user (cart/bulk checkout)
    $metaUser = json_encode(['orderNumber' => $ordNoEsc, 'purpose' => (strlen($purpose) ? $purpose : null)]);
    @mysqli_query($con, "INSERT INTO notification (userId,productId,qty,unit,action,meta) VALUES ('".intval($uid)."', '".$pid."', '".$qty."', 'kg', 'checkout_cart', '".mysqli_real_escape_string($con,$metaUser)."')");
    // admin notification with meta including purpose and order number
    $meta = json_encode(['requestedBy' => intval($uid), 'purpose' => (strlen($purpose) ? $purpose : null), 'orderNumber' => $ordNoEsc]);
    @mysqli_query($con, "INSERT INTO notification (userId,productId,qty,unit,action,meta) VALUES ('0','".$pid."','".$qty."','kg','checkout_cart','".mysqli_real_escape_string($con,$meta)."')");
    // insert order details and flat table
    @mysqli_query($con, "INSERT INTO ordersdetails (userId, productId, quantity, orderNumber) VALUES ('".intval($uid)."','".$pid."','".$qty."', '".$ordNoEsc."')");
    // product name
    $pname = '';
    $pRes = mysqli_query($con, "SELECT productName FROM products WHERE id='".$pid."' LIMIT 1");
    if ($pRes && mysqli_num_rows($pRes)) { $prow = mysqli_fetch_assoc($pRes); $pname = $prow['productName']; }
    @mysqli_query($con, "INSERT INTO order_items_flat (orderNumber, productId, productName, quantity, unit) VALUES ('".$ordNoEsc."','".$pid."','".mysqli_real_escape_string($con,$pname)."','".$qty."','kg')");
}
// clear cart
mysqli_query($con, "DELETE FROM cart WHERE userId='".intval($uid)."'");
// redirect
$msg = sprintf(__('ORDER_PLACED'), $ordNoEsc);
echo "<script>alert('". addslashes($msg) ."'); window.location='../my-orders.php';</script>";
exit;
?>