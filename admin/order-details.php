<?php session_start();
// start output buffer so we can return only the fragment for AJAX modal requests
ob_start();
// detect AJAX fragment requests via query flag
$isAjax = isset($_GET['__ajax']) && $_GET['__ajax'] === '1';
// Debug helper: visit this page with ?__debug=1 to see session/cookie info (temporary)
if (isset($_GET['__debug']) && $_GET['__debug'] === '1') {
    header('Content-Type: text/plain');
    echo "DEBUG: Session aid=" . (isset($_SESSION['aid']) ? $_SESSION['aid'] : '<none>') . "\n";
    echo "DEBUG: Cookie keys=" . implode(',', array_keys($_COOKIE)) . "\n";
    echo "DEBUG: Request URI=" . (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '') . "\n";
    echo "DEBUG: IsAjax=" . ($isAjax ? '1' : '0') . "\n";
    exit;
}
error_reporting(0);
include_once('includes/config.php');
// Ensure translation helper is available in admin context (root lang.php defines __())
include_once(__DIR__ . '/../includes/lang.php');
// Keep shutdown logger but do not display errors to the UI in production
ini_set('display_errors', 0);
error_reporting(0);
register_shutdown_function(function(){
    $err = error_get_last();
    if ($err) {
        @file_put_contents(__DIR__ . '/order_details_diag.log', date('c') . " - SHUTDOWN_ERROR: " . json_encode($err) . "\n", FILE_APPEND);
    }
});

if (!function_exists('renderStatusBadge')) {
    function renderStatusBadge($s) {
        $s = trim((string)$s);
        if ($s === '' || strcasecmp($s, 'New Order') === 0 || strcasecmp($s, 'Pending') === 0) return '<span class="badge bg-danger">'.__('STATUS_NEW_ORDER').'</span>';
        if (strcasecmp($s, 'Approved') === 0) return '<span class="badge bg-success">'.__('STATUS_APPROVED').'</span>';
        if (strcasecmp($s, 'Rejected') === 0) return '<span class="badge bg-danger">'.__('STATUS_REJECTED').'</span>';
        if (strcasecmp($s, 'Cancelled') === 0) return '<span class="badge bg-danger">'.__('STATUS_CANCELLED').'</span>';
        if (strcasecmp($s, 'Complete') === 0 || strcasecmp($s, 'Completed') === 0 || strcasecmp($s, 'Done') === 0) return '<span class="badge bg-success">'.__('STATUS_COMPLETE').'</span>';
        if (strcasecmp($s, 'Delivered') === 0) return '<span class="badge bg-success">'.__('STATUS_DELIVERED').'</span>';
        if (strcasecmp($s, 'Packed') === 0) return '<span class="badge bg-warning">'.__('STATUS_PACKED').'</span>';
        if (strcasecmp($s, 'Dispatched') === 0) return '<span class="badge bg-info">'.__('STATUS_DISPATCHED').'</span>';
        if (strcasecmp($s, 'In Transit') === 0) return '<span class="badge bg-secondary">'.__('STATUS_IN_TRANSIT').'</span>';
        if (strcasecmp($s, 'Out For Delivery') === 0) return '<span class="badge bg-dark">'.__('STATUS_OUT_FOR_DELIVERY').'</span>';
        return '<span class="badge bg-secondary">'.htmlentities($s).'</span>';
    }
} 

if(strlen( $_SESSION["aid"])==0)
{   
    if (isset($isAjax) && $isAjax) {
        // for AJAX modal requests, return a friendly message instead of redirecting
        header('HTTP/1.1 401 Unauthorized');
        echo '<div class="alert alert-warning">Sila log masuk sebagai pentadbir untuk melihat butiran pesanan.</div>';
        // flush buffer and exit
        ob_end_flush();
        exit;
    }

    // For direct GET navigation with no session, show a clear Malay message instead of redirecting to logout
    header('HTTP/1.1 401 Unauthorized');
    echo '<!doctype html><html><head><meta charset="utf-8"><title>Sila Log Masuk</title>';
    echo '<link rel="stylesheet" href="/fvsms/assets/bootstrap/css/bootstrap.min.css">';
    echo '</head><body style="font-family:Arial,Helvetica,sans-serif;padding:24px; background:#f8f9fa;">';
    echo '<div class="container"><div class="row"><div class="col-md-8 offset-md-2" style="margin-top:40px;">';
    echo '<div class="alert alert-warning" role="alert">Sila <a href="login.php">log masuk</a> sebagai pentadbir untuk melihat butiran pesanan.</div>';
    echo '<p><a href="login.php" class="btn btn-primary">Log Masuk</a> <a href="dashboard.php" class="btn btn-secondary">Kembali</a></p>';
    echo '</div></div></div></body></html>';
    exit;
} else {
    // Diagnostics: write request/session info to a temporary log to help track blank-page issues
    @file_put_contents(__DIR__ . '/order_details_diag.log', date('c') . " - REACHED: REQUEST_URI=" . (isset($_SERVER['REQUEST_URI'])? $_SERVER['REQUEST_URI'] : '') . " SESS_AID=" . (isset($_SESSION['aid'])? $_SESSION['aid'] : '<none>') . " GET_orderid=" . (isset($_GET['orderid']) ? $_GET['orderid'] : '<none>') . "\n", FILE_APPEND);
    // (diagnostic banner removed)
 

// If this is an AJAX fragment GET request, return a compact fragment for the modal
@file_put_contents(__DIR__ . '/order_details_diag.log', date('c') . " - START_MAIN_HANDLER\n", FILE_APPEND);
// (diagnostic banner removed)

$oid = isset($_GET['orderid']) ? intval($_GET['orderid']) : 0;
if (isset($isAjax) && $isAjax && $_SERVER['REQUEST_METHOD'] === 'GET') {
    // fetch order summary
    $q = @mysqli_query($con, "SELECT orders.id,orderNumber,totalAmount,IFNULL(orderStatus,'') as orderStatus,orderDate,COALESCE(orders.purpose,'') as purpose, users.name, users.email, users.contactno, billingAddress, biilingCity, billingState, billingPincode, billingCountry, shippingAddress, shippingCity, shippingState, shippingPincode, shippingCountry, orders.txnType, orders.txnNumber FROM `orders` LEFT JOIN users on users.id=orders.userId LEFT JOIN addresses on addresses.id=orders.addressId WHERE orders.id='".intval($oid)."' LIMIT 1");
    if (!$q || !($row = mysqli_fetch_assoc($q))) {
        @file_put_contents(__DIR__ . '/order_details_diag.log', date('c') . " - ORDER_NOT_FOUND or QUERY_FAILED\nSQL: SELECT ... WHERE orders.id='".intval($oid)."'\n", FILE_APPEND);
        header('HTTP/1.1 404 Not Found');
        echo '<div class="alert alert-warning">Pesanan tidak dijumpai.</div>';
        exit;
    } else {
        @file_put_contents(__DIR__ . '/order_details_diag.log', date('c') . " - ORDER_FETCHED id=" . intval($oid) . "\n", FILE_APPEND);
        // (diagnostic banner removed)

    }
    echo '<div class="p-2">';
    echo '<h5>#'.htmlentities($row['orderNumber']).' - '.__('ORDER_DETAILS').'</h5>';
    echo '<p>'.__('ORDER_DATE').': '.htmlentities($row['orderDate']).'</p>';
    $ostatus_txt = $row['orderStatus'] ? renderStatusBadge($row['orderStatus']) : '<span class="badge bg-danger">'.__('STATUS_NEW_ORDER').'</span>';
    echo '<p>'.__('ORDER_STATUS').': '.$ostatus_txt.'</p>';
    echo '<hr/><h6>'.__('PRODUCTS_ITEMS_DETAILS').'</h6>';
    echo '<table class="table table-sm">';
    $prodRes = @mysqli_query($con, "SELECT products.id as pid,products.productName,products.productImage1, ordersdetails.quantity FROM `ordersdetails` JOIN orders on orders.orderNumber=ordersdetails.orderNumber join products on products.id=ordersdetails.productId where (orders.id='".intval($oid)."')");
    while($pr = mysqli_fetch_assoc($prodRes)) {
        echo '<tr><td><img src="../productimages/'.htmlentities($pr['productImage1']).'" style="max-width:80px"/></td><td>'.htmlentities($pr['productName']).'</td><td>'.formatQuantityNumber($pr['quantity']).'</td></tr>';
    }
    echo '</table>';
    $histRes = @mysqli_query($con, "SELECT remark,status,postingDate,tbladmin.username FROM `ordertrackhistory` join tbladmin on tbladmin.id=ordertrackhistory.actionBy where (ordertrackhistory.orderId='".intval($oid)."') ORDER BY postingDate DESC");
    if ($histRes && mysqli_num_rows($histRes)) {
        echo '<hr/><h6>'.__('ORDER_HISTORY').'</h6><table class="table table-sm"><thead><tr><th>'.__('REMARK').'</th><th>'.__('STATUS').'</th><th>'.__('REMARK_BY').'</th><th>'.__('ACTION_DATE').'</th></tr></thead><tbody>';
        while($hrow = mysqli_fetch_assoc($histRes)) {
            echo '<tr><td>'.htmlentities($hrow['remark']).'</td><td>'.renderStatusBadge($hrow['status']).'</td><td>'.htmlentities($hrow['username']).'</td><td>'.htmlentities($hrow['postingDate']).'</td></tr>';
        }
        echo '</tbody></table>';
    } else {
        echo '<div class="small-note">'.__('NO_ORDER_ITEMS_RECORDED').'</div>';
    }
    echo '</div>';
    exit;
}

// Code for Take Action
if(isset($_POST['takeaction']))
{
    // detect AJAX request
    $isAjax = (isset($_POST['ajax']) && $_POST['ajax'] == '1') || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

    // helper to return JSON for AJAX
    function ajaxResponse($data) {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    // renderStatusBadge already defined above; not redefining it here to avoid redeclaration errors
    // (kept here for backward-compatibility marker)

    $oid = isset($_GET['orderid']) ? intval($_GET['orderid']) : 0;
    $status = isset($_POST['ostatus']) ? $_POST['ostatus'] : '';
    $remark = isset($_POST['remark']) ? $_POST['remark'] : '';
    $actionby = isset($_SESSION['aid']) ? intval($_SESSION['aid']) : 0;
    $canceledBy = 'Admin';

    // Log incoming takeaction requests for debugging
    @file_put_contents(__DIR__ . '/order_details_diag.log', date('c') . " - TAKEACTION_REQUEST method=" . (isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : '') . " isAjax=" . ($isAjax ? '1' : '0') . " POST_keys=" . implode(',', array_keys($_POST)) . "\n", FILE_APPEND);

    // fetch order number and purpose (if column exists) so we can attach purpose to history entries
    $hasPurpose = false;
    $pc = @mysqli_query($con, "SHOW COLUMNS FROM `orders` LIKE 'purpose'");
    if ($pc && mysqli_num_rows($pc)) { $hasPurpose = true; }
    $ordPurpose = '';
    $ordno = '';
    $r3 = @mysqli_query($con, "SELECT orderNumber" . ($hasPurpose ? ", purpose" : "") . " FROM orders WHERE id='".intval($oid)."' LIMIT 1");
    if ($r3 && $or3 = mysqli_fetch_assoc($r3)) { $ordno = isset($or3['orderNumber']) ? $or3['orderNumber'] : ''; if ($hasPurpose && isset($or3['purpose'])) $ordPurpose = $or3['purpose']; }

    if ($status === 'Cancelled') {
        // avoid double-reversion if already cancelled
        // Only select columns that exist (orderStatus or status) to avoid SQL errors on different schemas
        $hasOrderStatus = false;
        $hasStatus = false;
        $c1 = @mysqli_query($con, "SHOW COLUMNS FROM `orders` LIKE 'orderStatus'");
        if ($c1 && mysqli_num_rows($c1)) { $hasOrderStatus = true; }
        $c2 = @mysqli_query($con, "SHOW COLUMNS FROM `orders` LIKE 'status'");
        if ($c2 && mysqli_num_rows($c2)) { $hasStatus = true; }

        $selectCols = 'orderNumber';
        if ($hasOrderStatus) $selectCols .= ', orderStatus';
        if ($hasStatus) $selectCols .= ', status';

        $oRes = @mysqli_query($con, "SELECT $selectCols FROM orders WHERE id='".intval($oid)."' LIMIT 1");
        $ordno = '';
        $curStatus = '';
        if ($oRes && $oRow = mysqli_fetch_assoc($oRes)) {
            $ordno = isset($oRow['orderNumber']) ? $oRow['orderNumber'] : '';
            if ($hasOrderStatus && isset($oRow['orderStatus'])) {
                $curStatus = $oRow['orderStatus'];
            } else if ($hasStatus && isset($oRow['status'])) {
                $curStatus = $oRow['status'];
            }
        }

        if (strtolower($curStatus) !== 'cancelled') {
            @mysqli_autocommit($con, false);
            $ok = true;

            if ($ordno !== '') {
                // track restored amounts to avoid double-restores when flat table also contains same items
                $restored = array();

                $itRes = @mysqli_query($con, "SELECT productId, quantity FROM ordersdetails WHERE orderNumber='".mysqli_real_escape_string($con,$ordno)."'");
                if ($itRes) {
                    while ($it = mysqli_fetch_assoc($itRes)) {
                        $pid = intval($it['productId']);
                        $qty = floatval($it['quantity']);

                        $pRes = @mysqli_query($con, "SELECT Quantity FROM products WHERE id='".$pid."' LIMIT 1");
                        if ($pRes && $pRow = mysqli_fetch_assoc($pRes)) {
                            $current = floatval($pRow['Quantity']);
                            $toAdd = $qty; // restore amount from ordersdetails
                            $newQty = round($current + $toAdd, 4);
                            $u = @mysqli_query($con, "UPDATE products SET Quantity='".mysqli_real_escape_string($con,$newQty)."' WHERE id='".$pid."'");
                            if (!$u) {
                                $ok = false;
                                @file_put_contents(__DIR__ . '/../inventory_errors.log', date('c') . " - Failed to restore product $pid quantity: " . mysqli_error($con) . "\n", FILE_APPEND);
                                break;
                            }

                            if (!isset($restored[$pid])) $restored[$pid] = 0;
                            $restored[$pid] += $toAdd;

                            $usageSql = "INSERT INTO `usage` (productId, qty, unit, usedBy, action, notes) VALUES ('".$pid."', '".floatval($toAdd)."', 'kg', '0', 'restock', 'Reverted by admin on order cancellation #".intval($oid)."')";
                            @mysqli_query($con, $usageSql);

                            // notify admin clients about the restored stock
                            $meta = json_encode(array('orderId' => intval($oid), 'actionBy' => intval($actionby)));
                            @mysqli_query($con, "INSERT INTO notification (userId,productId,qty,unit,action,meta) VALUES ('0','".intval($pid)."','".floatval($toAdd)."','kg','restock','".mysqli_real_escape_string($con,$meta)."')");
                        } else {
                            @file_put_contents(__DIR__ . '/../inventory_errors.log', date('c') . " - Product not found when restoring for order $ordno (productId $pid)\n", FILE_APPEND);
                        }
                    }
                }
            }

            if ($ok) {
                $finalRemark = trim($remark);
                if (strlen(trim($ordPurpose))) $finalRemark .= ' - Purpose: ' . trim($ordPurpose);
                $ins = "INSERT INTO ordertrackhistory(orderId,status,remark,actionBy,canceledBy) VALUES('".intval($oid)."','Cancelled','".mysqli_real_escape_string($con,$finalRemark)."','".$actionby."',' $canceledBy')";
                @mysqli_query($con, $ins);

                @mysqli_query($con, "UPDATE orders SET orderStatus='Cancelled' WHERE id='".intval($oid)."'");

                // also revert flat items if present (only the missing amount — don't double-restore)
                if ($ordno !== '') {
                    $rd2 = @mysqli_query($con, "SELECT productId, quantity FROM order_items_flat WHERE orderNumber='".mysqli_real_escape_string($con,$ordno)."'");
                    if ($rd2) {
                        while ($it2 = mysqli_fetch_assoc($rd2)) {
                            $pid2 = intval($it2['productId']);
                            $q2 = floatval($it2['quantity']);
                            $already = isset($restored[$pid2]) ? floatval($restored[$pid2]) : 0;
                            $toAdd2 = max(0, $q2 - $already);
                            if ($toAdd2 <= 0) continue;
                            @mysqli_query($con, "UPDATE products SET Quantity = Quantity + $toAdd2 WHERE id='$pid2'");
                            @mysqli_query($con, "INSERT INTO `usage` (productId, qty, unit, usedBy, action, notes) VALUES ('".$pid2."', '".floatval($toAdd2)."', 'kg', '0', 'restock', 'Reverted by admin on order cancellation #".intval($oid)."')");
                            $meta2 = json_encode(array('orderId' => intval($oid), 'actionBy' => intval($actionby)));
                            @mysqli_query($con, "INSERT INTO notification (userId,productId,qty,unit,action,meta) VALUES ('0','".intval($pid2)."','".floatval($toAdd2)."','kg','restock','".mysqli_real_escape_string($con,$meta2)."')");
                        }
                    }
                }

                @mysqli_commit($con);
                @mysqli_autocommit($con, true);

                if ($isAjax) {
                    $adminRes = @mysqli_query($con, "SELECT username FROM tbladmin WHERE id='".intval($actionby)."' LIMIT 1");
                    $adminName = ($adminRes && $ar = mysqli_fetch_assoc($adminRes)) ? $ar['username'] : 'Admin';
                    $badge = renderStatusBadge('Cancelled');
                    $historyRow = '<tr><td>'.htmlentities($finalRemark).'</td><td>'.$badge.'</td><td>'.htmlentities($adminName).'</td><td>'.date('Y-m-d H:i:s').'</td></tr>';
                    ajaxResponse(array('success' => true, 'orderId' => intval($oid), 'status' => 'Cancelled', 'statusBadge' => $badge, 'historyRow' => $historyRow));
                } else {
                    echo '<script>alert("'.addslashes(__('ACTION_UPDATED')).'")</script>';
                    echo "<script>window.location.href ='all-orders.php'</script>";
                    exit;
                }
            } else {
                @mysqli_rollback($con);
                @mysqli_autocommit($con, true);
                if ($isAjax) {
                    ajaxResponse(array('success' => false, 'message' => __('SOMETHING_WENT_WRONG')));
                } else {
                    echo '<script>alert("'.__('SOMETHING_WENT_WRONG').'")</script>'; 
                    exit;
                }
            }
        } else {
            // already cancelled
            if ($isAjax) {
                ajaxResponse(array('success' => false, 'message' => __('ORDER_ALREADY_CANCELLED')));
            } else {
                echo '<script>alert("'. addslashes(__('ORDER_ALREADY_CANCELLED')) .'")</script>';
                echo "<script>window.location.href ='all-orders.php'</script>";
                exit;
            }
        }
    } else {
        // regular status update — use separate queries for reliability and error visibility
        $finalRemark = trim($remark);
        if (strlen(trim($ordPurpose))) $finalRemark .= ' - Purpose: ' . trim($ordPurpose);
        $ins = "INSERT INTO ordertrackhistory(orderId,status,remark,actionBy) VALUES('".intval($oid)."','".mysqli_real_escape_string($con,$status)."','".mysqli_real_escape_string($con,$finalRemark)."','".$actionby."')";
        $resIns = mysqli_query($con, $ins);
        $up = "UPDATE orders SET orderStatus='".mysqli_real_escape_string($con,$status)."' WHERE id='".intval($oid)."'";
        $resUp = mysqli_query($con, $up);
        if ($resIns && $resUp) {
            if ($isAjax) {
                $adminRes = @mysqli_query($con, "SELECT username FROM tbladmin WHERE id='".intval($actionby)."' LIMIT 1");
                $adminName = ($adminRes && $ar = mysqli_fetch_assoc($adminRes)) ? $ar['username'] : 'Admin';
                $badge = renderStatusBadge($status);
                $historyRow = '<tr><td>'.htmlentities($finalRemark).'</td><td>'.$badge.'</td><td>'.htmlentities($adminName).'</td><td>'.date('Y-m-d H:i:s').'</td></tr>';
                ajaxResponse(array('success' => true, 'orderId' => intval($oid), 'status' => "".mysqli_real_escape_string($con,$status)."", 'statusBadge' => $badge, 'historyRow' => $historyRow));
            } else {
                echo '<script>alert("'.addslashes(__('ACTION_UPDATED')).'")</script>';
                echo "<script>window.location.href ='all-orders.php'</script>";
                exit;
            }
        } else {
            @file_put_contents(__DIR__ . '/../order_errors.log', date('c') . " - STATUS update failed: " . mysqli_error($con) . " -- INS: " . addslashes($ins) . " -- UP: " . addslashes($up) . "\n", FILE_APPEND);
            if ($isAjax) {
                ajaxResponse(array('success' => false, 'message' => __('SOMETHING_WENT_WRONG') . ' (see logs)')); 
            } else {
                echo '<script>alert("'.__('SOMETHING_WENT_WRONG').' (see logs)")</script>'; 
                exit;
            }
        }
    }
}


?>
<!DOCTYPE html>
<html lang="ms">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title><?php echo __('SITE_TITLE') . ' | ' . __('ORDER_DETAILS'); ?></title>
        <link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" />
        <link href="css/styles.css" rel="stylesheet" />
        <script src="js/all.min.js" crossorigin="anonymous"></script>
        <style>
        /* Improved, balanced admin order details */
        :root{--admin-font:15px}
        body{font-size:var(--admin-font)}
        .admin-card .card-body{padding:1.25rem}
        h1.mt-4{font-size:1.6rem;margin-bottom:.6rem}
        .order-table img{max-width:110px;height:auto;border-radius:6px}
        .products-table img{max-width:110px;border-radius:6px}
        .table-sm td, .table-sm th{padding:.65rem .85rem}
        .table-borderless td, .table-borderless th{border:0}
        .products-table th{text-align:left}
        .products-table td{font-size:0.98rem}
        #print .card-body{padding:1.25rem}
        .badge{font-size:.92rem;padding:.45em .6em}
        .btn-smooth, .btn-lg{padding:.5rem .85rem;font-size:.98rem}
        .order-table th{width:36%}
        .text-break{word-break:break-word;white-space:normal}
        .order-history td{vertical-align:middle}
        .take-action-row{margin-top:10px;margin-bottom: 8px}
        .modal-lg{max-width:820px}
        .purpose-display{font-size:0.95rem;color:#333;padding:.25rem .5rem;border-radius:6px;background:#f7f7f9}
        .small-note{color:#6c757d;font-size:.9rem}
        .products-table th{font-weight:600}
        .products-table td{padding:.5rem}
        .action-buttons .btn{margin-right:.4rem}
        
        </style>
    </head>
    <body class="sb-nav-fixed">
 <?php include_once('includes/header.php');?>
<?php // DIAG: header included
@file_put_contents(__DIR__ . '/order_details_diag.log', date('c') . " - HEADER_INCLUDED\n", FILE_APPEND);
// (diagnostic banner removed)

?>
        <div id="layoutSidenav">
       <?php include_once('includes/sidebar.php');?>
<?php @file_put_contents(__DIR__ . '/order_details_diag.log', date('c') . " - SIDEBAR_INCLUDED\n", FILE_APPEND); // (diagnostic banner removed) ?>
            <div id="layoutSidenav_content">
                <main>
<tbody>
<?php 
$oid = isset($_GET['orderid']) ? intval($_GET['orderid']) : 0;
$ostatus = '';
// Fetch a single order (use LEFT JOIN so missing address rows don't hide the order)
$q = mysqli_query($con, "SELECT
    orders.id,
    orders.orderNumber,
    IFNULL(orders.totalAmount,0) AS totalAmount,
    IFNULL(orders.orderStatus,'') AS orderStatus,
    orders.orderDate,
    COALESCE(orders.purpose,'') AS purpose,
    users.name AS customer_name,
    users.email AS customer_email,
    users.contactno AS customer_contact,
    addresses.billingAddress AS billingAddress,
    addresses.biilingCity AS billingCity,
    addresses.billingState AS billingState,
    addresses.billingPincode AS billingPincode,
    addresses.billingCountry AS billingCountry,
    addresses.shippingAddress AS shippingAddress,
    addresses.shippingCity AS shippingCity,
    addresses.shippingState AS shippingState,
    addresses.shippingPincode AS shippingPincode,
    addresses.shippingCountry AS shippingCountry,
    orders.txnType, orders.txnNumber
    FROM `orders`
    LEFT JOIN users ON users.id = orders.userId
    LEFT JOIN addresses ON addresses.id = orders.addressId
    WHERE orders.id='".intval($oid)."' LIMIT 1");

if (!$q || !($row = mysqli_fetch_assoc($q))) {
    echo '<div class="container mt-4"><div class="alert alert-warning">' . __('ORDER_NOT_FOUND') . '</div></div>';
    include_once('includes/footer.php');
    exit;
}
?>  

                        

                    <div class="container-fluid px-4" >
                        <h1 class="mt-4">#<?php echo htmlentities($row['orderNumber']);?> <?php echo __('ORDER_DETAILS'); ?></h1>
<?php @file_put_contents(__DIR__ . '/order_details_diag.log', date('c') . " - RENDERED_MAIN_H1 id=" . intval(
    (isset($row['id']) ? $row['id'] : 0)
) . "\n", FILE_APPEND); // (diagnostic banner removed) ?>

                        <div class="card mb-4 admin-card">
                            <div class="card-header">
                                <i class="fas fa-table me-1"></i>
                                <?php echo __('ORDER_DETAILS'); ?>
                            </div>
                            <div class="card-body" id="print">


                                <div class="row">
                                    <div class="col-5">
                                <table class="table table-sm table-borderless order-table">

                                        <tr>
                                            <th colspan="2" style="text-align:center;"><?php echo __('ORDER_DETAILS'); ?></th>
                                        </tr>
                                        <tr>
                                            <th><?php echo __('ORDER_NUMBER'); ?></th>
                                            <td><?php echo htmlentities($row['orderNumber']);?></td>
                                            </tr>
                                            <tr>
                                            <th><?php echo __('ORDER_DATE'); ?></th>
                                            <td><?php echo htmlentities($row['orderDate']);?></td>
                                        </tr>
                                        <tr>
                                            <th><?php echo __('PURPOSE'); ?></th>
                                            <td><p class="mb-0 purpose-display text-break"><?php echo nl2br(htmlentities(isset($row['purpose']) ? $row['purpose'] : ''));?></p></td>
                                        </tr>
                                        <tr>
                                            <th><?php echo __('ORDER_STATUS'); ?></th>
                                               <td id="current-order-status"><?php $ostatus=$row['orderStatus'];
                                               if($ostatus==''):
                                                echo '<span class="badge bg-danger">'.__('STATUS_NEW_ORDER').'</span>';
                                            else:
                                                echo renderStatusBadge($ostatus);
                                            endif;

                                           ?></td>
                                           </tr>
                                             <tr>
                                            <th><?php echo __('TRANSACTION_TYPE'); ?></th>
                                               <td><?php echo htmlentities($row['txnType']);?></td>
                                           </tr>
   <tr>
                                            <th><?php echo __('TXN_NUMBER'); ?></th>
                                               <td><?php echo htmlentities($row['txnNumber']);?></td>
                                           </tr>

                                    </tbody>
                                </table></div>

<!--Cutomer /Users Details --->
 <div class="col-7">
     <table class="table table-sm table-borderless">
                                        <tr>
                                            <th colspan="2" style="text-align:center;"><?php echo __('CUSTOMER_DETAILS'); ?></th>
                                        </tr>
                                        <tr>
                                            <th><?php echo __('CUSTOMER_NAME'); ?></th>
                                            <td><?php echo htmlentities($row['customer_name'] ?: $row['name']);?></td>
                                            </tr>
                                            <tr>
                                            <th><?php echo __('EMAIL_ID'); ?></th>
                                            <td> <?php echo htmlentities($row['customer_email'] ?: $row['email']);?></td>
                                            </tr>
                                            <tr>
                                            <th><?php echo __('CONTACT_NO'); ?></th>
                                            <td><?php echo htmlentities((string)($row['customer_contact'] ?? $row['contactno'] ?? ''));?></td>
                                        </tr>

                                    
                                    </tbody>
                                </table></div>

<!-- Products / Item Details --->
 <div class="col-12">
        <div class="table-responsive">
        <table class="table products-table table-striped table-hover table-sm">
                                        <tr>
                                            <th colspan="3" style="text-align:center;"><?php echo __('PRODUCTS_ITEMS_DETAILS'); ?></th>
                                        </tr>
                                        <tr>
                                            <th><?php echo __('PRODUCT_IMAGE'); ?></th>
                                            <th><?php echo __('PRODUCT_NAME'); ?></th>
                                            <th><?php echo __('QUANTITY'); ?></th>
                                        </tr>
<?php 
$oid = intval($_GET['orderid']);
$prodRes = mysqli_query($con, "SELECT products.id as pid, products.productName, products.productImage1, products.productPrice as pprice, ordersdetails.quantity FROM `ordersdetails` JOIN orders on orders.orderNumber=ordersdetails.orderNumber JOIN products on products.id=ordersdetails.productId WHERE orders.id='".$oid."'");
$cnt=1;
$itemsTotal = 0.0;
while($prow = mysqli_fetch_assoc($prodRes))
{
    $linePrice = isset($prow['pprice']) ? floatval($prow['pprice']) : 0.0;
    $lineQty = isset($prow['quantity']) ? floatval($prow['quantity']) : 0.0;
    $lineTotal = $linePrice * $lineQty;
    $itemsTotal += $lineTotal;
?>  

             <tr>
                    <td><img src="productimages/<?php echo htmlentities($prow['productImage1']);?>" alt="<?php echo htmlentities($prow['productName']);?>" class="img-fluid" width="100"></td>
                    <td>
                       <a href="edit-product.php?id=<?php echo htmlentities($pd=$prow['pid']);?>" target="_blank"><?php echo htmlentities($prow['productName']);?></a>
        </td>
<td><?php echo formatQuantityNumber($prow['quantity']);?></td>
             
                </tr>
<?php 
} ?>
</table>
        </div>

<!-- Order Track/Action History --->
<?php 
$query=mysqli_query($con,"SELECT remark,status,postingDate,tbladmin.username FROM `ordertrackhistory`
join tbladmin on tbladmin.id=ordertrackhistory.actionBy
    where (ordertrackhistory.orderId='$oid')");
$count=mysqli_num_rows($query);
if($count>0){
     ?>
 <div class="col-12">
        <table class="table table-bordered" border="1" width="100%">
                                        <tr>
                                            <th colspan="6" style="text-align:center;"><?php echo __('ORDER_HISTORY'); ?></th>
                                        </tr>
                                        <tr>
                                            <th><?php echo __('REMARK'); ?></th>
                                            <th><?php echo __('STATUS'); ?></th>
                                            <th><?php echo __('REMARK_BY'); ?></th>
                                            <th><?php echo __('ACTION_DATE'); ?></th>
                                        </tr>
<tbody id="order-history-body">
<?php 
while($row=mysqli_fetch_array($query))
{
?>  

<tr>
<td><?php echo htmlentities($row['remark']);?></td>
                                        <td><?php $ostatus=$row['status']; echo renderStatusBadge($ostatus); ?></td>
<td><?php echo htmlentities($row['username']);?></td>
<td><?php echo htmlentities($row['postingDate']);?></td>
             
</tr>
</tbody>
<?php } ?>

</table></div>
<?php } ?>



<?php if($ostatus==''|| $ostatus=='Packed' || $ostatus=='Dispatched' || $ostatus=='In Transit' || $ostatus=='Out For Delivery'): ?>
<div class="d-flex justify-content-between align-items-center mb-2 take-action-row">
    <div>
        <button class="btn btn-primary btn-lg" type="button" data-bs-toggle="modal" data-bs-target="#exampleModal"><?php echo __('TAKE_ACTION'); ?></button>
    </div>
    <div class="action-buttons">
        <form method="post" id="cancel-order-form" style="display:inline;" data-confirm="<?php echo addslashes(sprintf(__('CANCEL_ORDER_CONFIRM'), (isset($row['purpose']) ? $row['purpose'] : ''))); ?>">
            <input type="hidden" name="ostatus" value="Cancelled">
            <input type="hidden" name="remark" value="Cancelled by Admin">
            <button type="submit" name="takeaction" class="btn btn-danger btn-smooth"><?php echo __('CANCEL_ORDER'); ?></button>
        </form>
        &nbsp;
        <button class="btn btn-secondary btn-smooth" style="cursor: pointer;"  OnClick="CallPrint(this.value)" ><?php echo __('PRINT'); ?></button>
    </div>
 </div>
<?php else: ?>
    <div class="text-end mb-2">
        <button class="btn btn-secondary" style="cursor: pointer;"  OnClick="CallPrint(this.value)" ><?php echo __('PRINT'); ?></button>
    </div>
<?php endif;?>
                            </div>
                            </div>
                        </div>
                    </div>
                </main>
<?php include_once('includes/footer.php');?>
                </footer>
            </div>
        </div>

<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
<form method="post" name="takeaction">

        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel"><?php echo __('UPDATE_ORDER_STATUS'); ?></h5>
                <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
<p><select name="ostatus" class="form-control" required>
    <option value=""><?php echo __('SELECT'); ?></option>
    <option value="Approved"><?php echo __('STATUS_APPROVED'); ?></option>
    <option value="Rejected"><?php echo __('STATUS_REJECTED'); ?></option>
</select></p>
<p>
<textarea class="form-control" required name="remark" placeholder="<?php echo __('REMARK_PLACEHOLDER'); ?>" rows="4"></textarea></p>
            </div>
            <div class="modal-footer"><button class="btn btn-secondary" type="button" data-bs-dismiss="modal"><?php echo __('CLOSE'); ?></button>
                <button class="btn btn-primary" type="submit" name="takeaction"><?php echo __('SAVE_CHANGES'); ?></button></div>
        </div>
    </form>
    </div>
</div>
</div>

        <script src="js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="js/scripts.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" crossorigin="anonymous"></script>
        <script src="js/datatables-simple-demo.js"></script>
               <script>
(function(){
  function showTempAlert(msg, type) {
    type = type || 'success';
    var cont = document.getElementById('toast-container');
    if (!cont) {
      cont = document.createElement('div');
      cont.id = 'toast-container';
      cont.style.position = 'fixed';
      cont.style.top = '10px';
      cont.style.right = '10px';
      cont.style.zIndex = 2147483647;
      document.body.appendChild(cont);
    }
    var toastId = 't' + Date.now() + Math.floor(Math.random()*1000);
    var bgClass = (type === 'success') ? 'bg-success text-white' : (type === 'danger' ? 'bg-danger text-white' : 'bg-secondary text-white');
    var toastHtml = '<div class="toast" id="'+toastId+'" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="3000">' +
      '<div class="toast-header '+bgClass+'">' +
      '<strong class="me-auto"><?php echo addslashes(__('STATUS')); ?></strong>' +
      '<small class="text-muted ms-2"></small>' +
      '<button type="button" class="btn-close btn-close-white ms-2 mb-1" data-bs-dismiss="toast" aria-label="Close"></button>' +
      '</div>' +
      '<div class="toast-body">'+msg+'</div>' +
      '</div>';
    cont.insertAdjacentHTML('beforeend', toastHtml);
    var tEl = document.getElementById(toastId);
    try { var bsToast = new bootstrap.Toast(tEl); bsToast.show(); } catch(ex) { /* fallback */ setTimeout(function(){ tEl.remove(); }, 3000); }
    tEl.addEventListener('hidden.bs.toast', function(){ tEl.remove(); });
  }

  var takeForm = document.querySelector('form[name="takeaction"]');
  if (takeForm) {
    takeForm.addEventListener('submit', function(e){
      e.preventDefault();
      var fd = new FormData(takeForm);
      fd.append('ajax','1');
      fd.append('takeaction','1');
      fetch(window.location.href, {
        method: 'POST',
        body: fd,
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        credentials: 'same-origin'
      }).then(function(r){ return r.json(); })
      .then(function(resp){
        if (resp && resp.success) {
          var statusEl = document.getElementById('current-order-status');
          if (statusEl && resp.statusBadge) statusEl.innerHTML = resp.statusBadge;
          var tbody = document.getElementById('order-history-body');
          if (tbody && resp.historyRow) tbody.insertAdjacentHTML('afterbegin', resp.historyRow);
          var modalEl = document.getElementById('exampleModal');
          if (modalEl) {
            try { var bs = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl); bs.hide(); } catch (ex) {}
          }          // broadcast to other admin pages (if supported)
          if (window.BroadcastChannel && resp.orderId) {
            try { (new BroadcastChannel('fvsms_orders')).postMessage({type:'order-updated', orderId: resp.orderId, statusBadge: resp.statusBadge, status: resp.status || ''}); } catch(e) {}
          }          showTempAlert('<?php echo addslashes(__('ACTION_UPDATED')); ?>', 'success');
        } else {
          showTempAlert(resp && resp.message ? resp.message : '<?php echo addslashes(__('SOMETHING_WENT_WRONG')); ?>', 'danger');
        }
      }).catch(function(err){
        showTempAlert('<?php echo addslashes(__('NETWORK_ERROR')); ?>', 'danger');
        console.error(err);
      });
    });
  }

  var cancelForm = document.getElementById('cancel-order-form');
  if (cancelForm) {
    cancelForm.addEventListener('submit', function(e){
      e.preventDefault();
      var confirmMsg = cancelForm.getAttribute('data-confirm') || 'Are you sure you want to cancel this order?';
      if (!confirm(confirmMsg)) {
        return;
      }
      var fd = new FormData(cancelForm);
      fd.append('ajax','1');
      fd.append('takeaction','1');
      fetch(window.location.href, {
        method: 'POST',
        body: fd,
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        credentials: 'same-origin'
      }).then(function(r){ return r.json(); })
      .then(function(resp){
        if (resp && resp.success) {
          var statusEl = document.getElementById('current-order-status');
          if (statusEl && resp.statusBadge) statusEl.innerHTML = resp.statusBadge;
          var tbody = document.getElementById('order-history-body');
          if (tbody && resp.historyRow) tbody.insertAdjacentHTML('afterbegin', resp.historyRow);
          if (window.BroadcastChannel && resp.orderId) {
            try { (new BroadcastChannel('fvsms_orders')).postMessage({type:'order-updated', orderId: resp.orderId, statusBadge: resp.statusBadge, status: resp.status || ''}); } catch(e) {}
          }
          showTempAlert('<?php echo addslashes(__('ORDER_CANCELLED')); ?>', 'success');
        } else {
          showTempAlert(resp && resp.message ? resp.message : 'Error cancelling order', 'danger');
        }
      }).catch(function(err){
        showTempAlert('<?php echo addslashes(__('NETWORK_ERROR')); ?>', 'danger');
        console.error(err);
      });
    });
  }

})();
function CallPrint(strid) {
var prtContent = document.getElementById("print");
var WinPrint = window.open('', '', 'left=0,top=0,width=800,height=900,toolbar=0,scrollbars=0,status=0');
WinPrint.document.write(prtContent.innerHTML);
WinPrint.document.close();
WinPrint.focus();
WinPrint.print();
}

</script>
    </body>
</html>
<?php } ?>
