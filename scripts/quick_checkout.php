<?php
session_start();
include_once('../includes/config.php');
if(strlen($_SESSION['id'])==0){
    header('Location: ../logout.php');
    exit;
}
$uid = intval($_SESSION['id']);

// Debug helper (opt-in). Call the checkout endpoint with ?debug=1 to get diagnostics written to
// scripts/quick_checkout_debug.log and (for convenience) returned in the response.
$debug = (isset($_REQUEST['debug']) && $_REQUEST['debug'] === '1');
$dbg = array();
$dbg[] = "time=".date('c');
$dbg[] = "session_id=".session_id();
$dbg[] = "session_uid=". (isset($_SESSION['id']) ? $_SESSION['id'] : 'NONE');
$dbg[] = "request_method=".($_SERVER['REQUEST_METHOD'] ?? '');
$dbg[] = "post_keys=".implode(',', array_keys($_POST));
// raw request body (may contain payload if content-type is non-form or PHP didn't populate $_POST)
$raw_input = @file_get_contents('php://input');
$dbg[] = 'raw_input=' . $raw_input;
// headers snapshot
$hdrs = function_exists('getallheaders') ? json_encode(getallheaders()) : 'getallheaders_not_available';
$dbg[] = 'headers=' . $hdrs;
// attempt to read cart for this user (may be empty)
$cartCheck = @mysqli_query($con, "SELECT id,productId,productQty,userId FROM cart WHERE userId='".intval($uid)."'");
$cartItems = array();
if($cartCheck) {
    while($r = mysqli_fetch_assoc($cartCheck)) $cartItems[] = $r;
} else {
    $dbg[] = 'cart_query_error=' . mysqli_error($con);
}
$dbg[] = 'cart_count=' . count($cartItems);
$dbg[] = 'cart_items=' . json_encode($cartItems);
// record last DB error (if any)
$dbg[] = 'last_db_error=' . mysqli_error($con);
@file_put_contents(__DIR__ . '/quick_checkout_debug.log', implode("\n", $dbg) . "\n----\n", FILE_APPEND);
if($debug) {
    header('Content-Type: text/plain');
    echo implode("\n", $dbg) . "\n";
    exit;
}

// Ensure cart has items (use row count; allow zero-priced items)
$countRes = mysqli_query($con, "SELECT COUNT(*) AS cnt FROM cart WHERE userId='".intval($uid)."'");
$countRow = mysqli_fetch_assoc($countRes);
if (intval($countRow['cnt']) === 0) {
    $isAjax = (isset($_POST['modal_checkout']) || isset($_POST['client_ts']) || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'));
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(array('success' => false, 'message' => __('YOUR_CART_IS_EMPTY')));
        exit;
    }
    echo "<script>alert('" . addslashes(__('YOUR_CART_IS_EMPTY')) . "');</script>";
    echo "<script type='text/javascript'> document.location ='../my-cart.php'; </script>";
    exit;
} 
// Compute grand total from cart (can be zero)
$res = mysqli_query($con, "SELECT SUM((products.productPrice * cart.productQty) + products.shippingCharge) AS gtotal FROM cart JOIN products ON products.id=cart.productId WHERE cart.userId='".intval($uid)."'");
if(!$res) {
    error_log('quick_checkout grand total query failed: ' . mysqli_error($con));
}
$row = mysqli_fetch_assoc($res);
$gtotal = $row['gtotal'] ? floatval($row['gtotal']) : 0.0;
// Accept optional purpose
$purpose = isset($_POST['purpose']) ? trim($_POST['purpose']) : '';
// Ensure required orders columns exist (orderNumber, txnType, txnNumber, purpose)
$requiredOrdersCols = array(
    'orderNumber' => "VARCHAR(64) DEFAULT NULL",
    'txnType' => "VARCHAR(50) DEFAULT NULL",
    'txnNumber' => "VARCHAR(100) DEFAULT NULL",
    'purpose' => "TEXT DEFAULT NULL"
);
foreach($requiredOrdersCols as $col => $definition) {
    $c = @mysqli_query($con, "SHOW COLUMNS FROM `orders` LIKE '".mysqli_real_escape_string($con,$col)."'");
    if (!$c || mysqli_num_rows($c) === 0) {
        $ok = @mysqli_query($con, "ALTER TABLE orders ADD COLUMN `".$col."` " . $definition);
        if (!$ok) {
            error_log('quick_checkout: failed to add column ' . $col . ' to orders: ' . mysqli_error($con));
        }
    }
}
// Ensure ordersdetails table exists (used by the insert statements)
$createOrdersDetails = "CREATE TABLE IF NOT EXISTS ordersdetails (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    userId INT,
    productId INT,
    quantity DECIMAL(10,4),
    orderNumber VARCHAR(64),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
if(!@mysqli_query($con, $createOrdersDetails)) {
    error_log('quick_checkout: could not create ordersdetails table: ' . mysqli_error($con));
}
// Create order
$orderno = mt_rand(100000000,999999999);
// Mark quick checkout orders created from cart as inventory / bulk orders
$txntype = 'inventory';
$txnno = '';
// Address nullable
$addressValue = 'NULL';
$insertOrder = "INSERT INTO orders (orderNumber,userId,addressId,totalAmount,txnType,txnNumber" . (strlen($purpose) ? ',purpose' : '') . ") VALUES ('".mysqli_real_escape_string($con,$orderno)."', $uid, $addressValue, '".mysqli_real_escape_string($con,$gtotal)."', '".mysqli_real_escape_string($con,$txntype)."', '".mysqli_real_escape_string($con,$txnno)."'" . (strlen($purpose) ? ",'".mysqli_real_escape_string($con,$purpose)."'" : '') . ")";
$dryRun = (isset($_REQUEST['debug']) && $_REQUEST['debug'] === '2');

// Dry-run (diagnostic): attempt full sequence inside transaction and then roll back
if ($dryRun) {
    $dbgOut = array();
    $dbgOut[] = 'DRY-RUN: start';
    $useTx = @mysqli_query($con, 'START TRANSACTION');
    $dbgOut[] = 'START TRANSACTION -> ' . ($useTx ? 'OK' : 'FAILED');

    $dbgOut[] = 'SQL: insertOrder => ' . $insertOrder;
    $r1 = @mysqli_query($con, $insertOrder);
    $dbgOut[] = 'insertOrder_ok=' . ($r1 ? '1' : '0') . ' err=' . addslashes(mysqli_error($con));

    $dbgOut[] = 'SQL: insertDetails => ' . $insertDetails;
    $r2 = @mysqli_query($con, $insertDetails);
    $dbgOut[] = 'insertDetails_ok=' . ($r2 ? '1' : '0') . ' err=' . addslashes(mysqli_error($con));

    $dbgOut[] = 'SQL: insertFlat => ' . $insertFlat;
    $r3 = @mysqli_query($con, $insertFlat);
    $dbgOut[] = 'insertFlat_ok=' . ($r3 ? '1' : '0') . ' err=' . addslashes(mysqli_error($con));

    $dbgOut[] = 'SQL: delCart => ' . $delCart;
    $r4 = @mysqli_query($con, $delCart);
    $dbgOut[] = 'delCart_ok=' . ($r4 ? '1' : '0') . ' err=' . addslashes(mysqli_error($con));

    $chkO = @mysqli_query($con, "SELECT COUNT(*) AS cnt FROM orders WHERE orderNumber='".mysqli_real_escape_string($con,$orderno)."'");
    $cntO = $chkO ? intval(mysqli_fetch_assoc($chkO)['cnt']) : 0;
    $dbgOut[] = 'orders_count=' . $cntO;
    $chkD = @mysqli_query($con, "SELECT COUNT(*) AS cnt FROM ordersdetails WHERE orderNumber='".mysqli_real_escape_string($con,$orderno)."'");
    $cntD = $chkD ? intval(mysqli_fetch_assoc($chkD)['cnt']) : 0;
    $dbgOut[] = 'ordersdetails_count=' . $cntD;

    $rb = @mysqli_query($con, 'ROLLBACK');
    $dbgOut[] = 'ROLLBACK -> ' . ($rb ? 'OK' : 'FAILED');

    header('Content-Type: text/plain');
    echo implode("\n", $dbgOut);
    exit;
}

// Normal execution (non-dry-run)
$ok = mysqli_query($con, $insertOrder);
if (!$ok) {
    $err = mysqli_error($con);
    error_log('quick_checkout: order insert failed: ' . $err);
    $isAjax = (isset($_POST['modal_checkout']) || isset($_POST['client_ts']) || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'));
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(array('success' => false, 'message' => 'Could not create order. DB error: ' . $err));
        exit;
    }
    echo "<script>alert('Could not create order. DB error: " . addslashes($err) . "');</script>";
    echo "<script type='text/javascript'> document.location ='../my-cart.php'; </script>";
    exit;
} 
// Create flat items table if missing
$createFlat = "CREATE TABLE IF NOT EXISTS order_items_flat (
        id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        orderNumber VARCHAR(64),
        productId INT,
        productName VARCHAR(255),
        quantity DECIMAL(10,4),
        unit VARCHAR(10) DEFAULT 'kg',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
@mysqli_query($con, $createFlat);
$ordNoEsc = mysqli_real_escape_string($con, $orderno);

// Begin transactional checkout to ensure stock is sufficient and operations are atomic
$txOk = @mysqli_query($con, 'START TRANSACTION');
if (!$txOk) {
    @file_put_contents(__DIR__ . '/quick_checkout_debug.log', date('c') . " - START TRANSACTION failed: " . mysqli_error($con) . "\n", FILE_APPEND);
}

// Fetch cart rows with product info (FOR UPDATE to lock rows)
$cartQ = mysqli_query($con, "SELECT c.id AS cartId, c.productId, c.productQty, p.Quantity AS availableQty, p.productName FROM cart c JOIN products p ON p.id=c.productId WHERE c.userId='".intval($uid)."' FOR UPDATE");
if (!$cartQ) {
    $err = mysqli_error($con);
    @file_put_contents(__DIR__ . '/quick_checkout_debug.log', date('c') . " - CART SELECT failed: " . $err . "\n", FILE_APPEND);
    @mysqli_query($con, 'ROLLBACK');
    $isAjax = (isset($_POST['modal_checkout']) || isset($_POST['client_ts']) || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'));
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(array('success' => false, 'message' => __('SOMETHING_WENT_WRONG')));
        exit;
    }
    echo "<script>alert('" . addslashes(__('SOMETHING_WENT_WRONG')) . "'); window.location='../my-cart.php';</script>";
    exit;
} 
$cartItems = array();
$problems = array();
while ($r = mysqli_fetch_assoc($cartQ)) {
    $cid = intval($r['cartId']);
    $pid = intval($r['productId']);
    $qty = floatval($r['productQty']);
    $avail = floatval($r['availableQty']);
    $pname = $r['productName'];
    $cartItems[] = array('cartId'=>$cid,'productId'=>$pid,'qty'=>$qty,'avail'=>$avail,'pname'=>$pname);
    if ($qty > $avail) {
        $problems[] = sprintf('%s: requested %s kg, available %s kg', $pname, $qty, $avail);
    }
}
if (!empty($problems)) {
    $msg = implode("\\n", $problems);
    @mysqli_query($con, 'ROLLBACK');
    $isAjax = (isset($_POST['modal_checkout']) || isset($_POST['client_ts']) || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'));
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(array('success' => false, 'message' => $msg));
        exit;
    }
    echo "<script>alert('" . addslashes($msg) . "'); window.location='../my-cart.php';</script>";
    exit;
} 

// Apply stock changes, insert usage and notifications, insert order details and flat rows
$insOrderDetailsStmt = null;
$insFlatStmt = null;
$errors = array();
foreach ($cartItems as $it) {
    $pid = intval($it['productId']);
    $qty = floatval($it['qty']);
    $pname = $it['pname'];

    // Deduct stock
    $upd = mysqli_query($con, "UPDATE products SET Quantity = Quantity - " . floatval($qty) . " WHERE id = " . intval($pid));
    if (!$upd) {
        $errors[] = "Failed updating product $pid: " . mysqli_error($con);
        break;
    }

    // Log to `usage`
    $notes = mysqli_real_escape_string($con, __('USER_CHECKOUT_NOTE'));
    if (strlen($purpose)) $notes .= ' - ' . mysqli_real_escape_string($con, $purpose);
    $uSql = "INSERT INTO `usage` (productId, qty, unit, usedBy, action, notes) VALUES ('".intval($pid)."', '".floatval($qty)."', 'kg', '".intval($uid)."', 'checkout', '".$notes."')";
    if (!@mysqli_query($con, $uSql)) {
        // non-fatal but log it
        @file_put_contents(__DIR__ . '/quick_checkout_debug.log', date('c') . " - USAGE insert failed: " . mysqli_error($con) . " -- SQL: " . $uSql . "\n", FILE_APPEND);
    }

    // Notifications (mark as cart checkout and include order meta)
    $metaUser = json_encode(array('orderNumber' => $orderno, 'purpose' => (strlen($purpose) ? $purpose : null)));
    @mysqli_query($con, "INSERT INTO notification (userId,productId,qty,unit,action,meta) VALUES ('".intval($uid)."', '".$pid."', '".$qty."', 'kg', 'checkout_cart', '".mysqli_real_escape_string($con,$metaUser)."')");

    $metaAdmin = json_encode(array('requestedBy' => intval($uid), 'purpose' => (strlen($purpose) ? $purpose : null), 'orderNumber' => $orderno, 'numItems' => count($cartItems)));
    @mysqli_query($con, "INSERT INTO notification (userId,productId,qty,unit,action,meta) VALUES ('0','".$pid."','".$qty."','kg','checkout_cart','".mysqli_real_escape_string($con,$metaAdmin)."')");

    // Insert order details for this item
    $dSql = "INSERT INTO ordersdetails (userId, productId, quantity, orderNumber) VALUES ('".intval($uid)."', '".$pid."', '".$qty."', '".$ordNoEsc."')";
    if (!@mysqli_query($con, $dSql)) {
        $errors[] = "ordersdetails insert failed for $pid: " . mysqli_error($con);
        break;
    }

    // Insert flat item
    $insFlat = "INSERT INTO order_items_flat (orderNumber, productId, productName, quantity, unit) VALUES ('".$ordNoEsc."', '".$pid."', '".mysqli_real_escape_string($con,$pname)."', '".$qty."', 'kg')";
    if (!@mysqli_query($con, $insFlat)) {
        $errors[] = "order_items_flat insert failed for $pid: " . mysqli_error($con);
        break;
    }
}

if (!empty($errors)) {
    @file_put_contents(__DIR__ . '/quick_checkout_debug.log', date('c') . " - TRANSACTION ERRORS: " . implode(' | ', $errors) . "\n", FILE_APPEND);
    @mysqli_query($con, 'ROLLBACK');
    $isAjax = (isset($_POST['modal_checkout']) || isset($_POST['client_ts']) || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'));
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(array('success' => false, 'message' => 'Something went wrong while finalizing order.'));
        exit;
    }
    echo "<script>alert('Something went wrong while finalizing order. Please contact admin.');</script>";
    echo "<script type='text/javascript'> document.location ='../my-cart.php'; </script>";
    exit;
} 

// All DB operations OK — delete cart and commit
$delCart = mysqli_query($con, "DELETE FROM cart WHERE userId='".intval($uid)."'");
if (!$delCart) {
    @file_put_contents(__DIR__ . '/quick_checkout_debug.log', date('c') . " - DELETE CART failed: " . mysqli_error($con) . "\n", FILE_APPEND);
    @mysqli_query($con, 'ROLLBACK');
    $isAjax = (isset($_POST['modal_checkout']) || isset($_POST['client_ts']) || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'));
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(array('success' => false, 'message' => 'Could not clear cart.'));
        exit;
    }
    echo "<script>alert('Could not clear cart. Please contact admin.');</script>";
    echo "<script type='text/javascript'> document.location ='../my-cart.php'; </script>";
    exit;
} 

$commit = @mysqli_query($con, 'COMMIT');
if (!$commit) {
    @file_put_contents(__DIR__ . '/quick_checkout_debug.log', date('c') . " - COMMIT failed: " . mysqli_error($con) . "\n", FILE_APPEND);
    @mysqli_query($con, 'ROLLBACK');
    $isAjax = (isset($_POST['modal_checkout']) || isset($_POST['client_ts']) || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'));
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(array('success' => false, 'message' => 'Could not finalize order.'));
        exit;
    }
    echo "<script>alert('Could not finalize order. Please contact admin.');</script>";
    echo "<script type='text/javascript'> document.location ='../my-cart.php'; </script>";
    exit;
} 

// success
unset($_SESSION['address']);
unset($_SESSION['gtotal']);
@file_put_contents(__DIR__ . '/quick_checkout_debug.log', date('c') . " - ORDER COMPLETED: " . $ordNoEsc . " by uid=" . intval($uid) . "\n", FILE_APPEND);
$isAjax = (isset($_POST['modal_checkout']) || isset($_POST['client_ts']) || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'));
if ($isAjax) {
    header('Content-Type: application/json');
    $msg = sprintf(__('ORDER_PLACED'), $orderno);
    echo json_encode(array('success' => true, 'message' => $msg, 'orderNumber' => $orderno));
    exit;
}

$msgPlain = sprintf(__('ORDER_PLACED'), $orderno);
echo '<script>alert("'. addslashes($msgPlain) .'")</script>';
echo "<script type='text/javascript'> document.location ='../my-orders.php'; </script>";
exit;
