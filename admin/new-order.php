<?php session_start();
include_once(__DIR__ . '/../includes/config.php');
if(strlen( $_SESSION["aid"])==0)
{   
header('location:logout.php');
} else {

// Handle admin confirm action (confirm checkout)
if (isset($_GET['confirm']) && is_numeric($_GET['confirm'])) {
    $oid = intval($_GET['confirm']);
    $actionby = isset($_SESSION['aid']) ? intval($_SESSION['aid']) : 0;
    // set order status to Complete (only update columns that exist)
    $hasStatus = false;
    $hasOrderStatus = false;
    $c = @mysqli_query($con, "SHOW COLUMNS FROM `orders` LIKE 'status'");
    if ($c && mysqli_num_rows($c)) { $hasStatus = true; }
    $c2 = @mysqli_query($con, "SHOW COLUMNS FROM `orders` LIKE 'orderStatus'");
    if ($c2 && mysqli_num_rows($c2)) { $hasOrderStatus = true; }
    $sets = [];
    if ($hasOrderStatus) $sets[] = "orderStatus='Complete'";
    if ($hasStatus) $sets[] = "status='Complete'";
    if (!empty($sets)) {
        $sqlu = "UPDATE orders SET " . implode(',', $sets) . " WHERE id='".$oid."'";
        @mysqli_query($con, $sqlu);
    }
    // ensure order_items_flat exists
    @mysqli_query($con, "CREATE TABLE IF NOT EXISTS order_items_flat (
        id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        orderNumber VARCHAR(64),
        productId INT,
        productName VARCHAR(255),
        quantity DECIMAL(10,4),
        unit VARCHAR(10),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    // get order number and purpose (if exists)
    $hasPurpose = false;
    $pc = @mysqli_query($con, "SHOW COLUMNS FROM `orders` LIKE 'purpose'");
    if ($pc && mysqli_num_rows($pc)) { $hasPurpose = true; }
    $r = mysqli_query($con, "SELECT orderNumber" . ($hasPurpose ? ", purpose" : "") . " FROM orders WHERE id='".$oid."' LIMIT 1");
    if ($r && $or = mysqli_fetch_assoc($r)) {
        $ordno = $or['orderNumber'];
        $ordPurpose = ($hasPurpose && isset($or['purpose'])) ? $or['purpose'] : '';
        // copy items into flat table
        $qitems = mysqli_query($con, "SELECT od.productId, od.quantity, p.productName FROM ordersdetails od JOIN products p ON p.id=od.productId WHERE od.orderNumber='".mysqli_real_escape_string($con,$ordno)."'");
        if ($qitems) {
            while ($it = mysqli_fetch_assoc($qitems)) {
                $pid = intval($it['productId']);
                $qty = floatval($it['quantity']);
                $pname = mysqli_real_escape_string($con,$it['productName']);
                mysqli_query($con, "INSERT INTO order_items_flat (orderNumber, productId, productName, quantity, unit) VALUES('".mysqli_real_escape_string($con,$ordno)."', '$pid', '$pname', '$qty', 'kg')");
            }
        }
        // log acceptance in ordertrackhistory with purpose included
        $remark = 'Order confirmed by Admin';
        if (strlen(trim($ordPurpose))) $remark .= ' - Purpose: ' . trim($ordPurpose);
        @mysqli_query($con, "INSERT INTO ordertrackhistory(orderId,status,remark,actionBy) VALUES('".intval($oid)."','Complete','".mysqli_real_escape_string($con,$remark)."','".$actionby."')");
    }
    // refresh to avoid resubmission
    echo "<script>window.location.href='new-order.php';</script>";
    exit;
}

// Handle admin cancel action
if (isset($_GET['cancel']) && is_numeric($_GET['cancel'])) {
    $oid = intval($_GET['cancel']);
    $actionby = isset($_SESSION['aid']) ? intval($_SESSION['aid']) : 0;

    // Fetch current order and prevent double-reversion if already cancelled
    // Only select columns that actually exist to avoid SQL errors on older schemas
    $hasOrderStatus = false;
    $hasStatus = false;
    $c1 = @mysqli_query($con, "SHOW COLUMNS FROM `orders` LIKE 'orderStatus'");
    if ($c1 && mysqli_num_rows($c1)) { $hasOrderStatus = true; }
    $c2 = @mysqli_query($con, "SHOW COLUMNS FROM `orders` LIKE 'status'");
    if ($c2 && mysqli_num_rows($c2)) { $hasStatus = true; }

    $selectCols = 'orderNumber';
    if ($hasOrderStatus) $selectCols .= ', orderStatus';
    if ($hasStatus) $selectCols .= ', status';
    // include purpose column if present
    $hasPurpose = false;
    $pc = @mysqli_query($con, "SHOW COLUMNS FROM `orders` LIKE 'purpose'");
    if ($pc && mysqli_num_rows($pc)) { $hasPurpose = true; $selectCols .= ', purpose'; }

    $oRes = @mysqli_query($con, "SELECT $selectCols FROM orders WHERE id='".intval($oid)."' LIMIT 1");
    $ordno = '';
    $curStatus = '';
    $ordPurpose = '';
    if ($oRes && $oRow = mysqli_fetch_assoc($oRes)) {
        $ordno = isset($oRow['orderNumber']) ? $oRow['orderNumber'] : '';
        if ($hasOrderStatus && isset($oRow['orderStatus'])) {
            $curStatus = $oRow['orderStatus'];
        } else if ($hasStatus && isset($oRow['status'])) {
            $curStatus = $oRow['status'];
        }
        if ($hasPurpose && isset($oRow['purpose'])) $ordPurpose = $oRow['purpose'];
    }

    if (strtolower($curStatus) === 'cancelled') {
        // already cancelled - just redirect
        echo "<script>window.location.href='new-order.php';</script>";
        exit;
    }

    // include purpose in remark
    $remark = 'Cancelled by Admin';
    if (strlen(trim($ordPurpose))) $remark .= ' - Purpose: ' . trim($ordPurpose);

    // Begin a transaction so stock and order status stay consistent
    @mysqli_autocommit($con, false);
    $ok = true;

    if ($ordno !== '') {
        // track how much we've restored per product to avoid double-restores
        $restored = array();

        // restore quantities for each order item (source of truth)
        $itRes = @mysqli_query($con, "SELECT productId, quantity FROM ordersdetails WHERE orderNumber='".mysqli_real_escape_string($con,$ordno)."'");
        if ($itRes) {
            while ($it = mysqli_fetch_assoc($itRes)) {
                $pid = intval($it['productId']);
                $qty = floatval($it['quantity']);

                // fetch current product quantity
                $pRes = @mysqli_query($con, "SELECT Quantity FROM products WHERE id='".$pid."' LIMIT 1");
                if ($pRes && $pRow = mysqli_fetch_assoc($pRes)) {
                    $current = floatval($pRow['Quantity']);
                    $toAdd = $qty; // restore exactly what was in ordersdetails
                    $newQty = round($current + $toAdd, 4);
                    $u = @mysqli_query($con, "UPDATE products SET Quantity='".mysqli_real_escape_string($con,$newQty)."' WHERE id='".$pid."'");
                    if (!$u) {
                        $ok = false;
                        @file_put_contents(__DIR__ . '/../inventory_errors.log', date('c') . " - Failed to restore product $pid quantity: " . mysqli_error($con) . "\n", FILE_APPEND);
                        break;
                    }

                    // record restored amount to avoid duplicate restores
                    if (!isset($restored[$pid])) $restored[$pid] = 0;
                    $restored[$pid] += $toAdd;

                    // Log usage/restock and notify admin
                    $usageSql = "INSERT INTO `usage` (productId, qty, unit, usedBy, action, notes) VALUES ('".$pid."', '".floatval($toAdd)."', 'kg', '0', 'restock', 'Reverted by admin on order cancellation #".intval($oid)."')";
                    @mysqli_query($con, $usageSql);

                    $meta = json_encode(array('orderId' => intval($oid), 'actionBy' => intval($actionby)));
                    @mysqli_query($con, "INSERT INTO notification (userId,productId,qty,unit,action,meta) VALUES ('0','".intval($pid)."','".floatval($toAdd)."','kg','restock','".mysqli_real_escape_string($con,$meta)."')");
                } else {
                    // product missing - log and continue
                    @file_put_contents(__DIR__ . '/../inventory_errors.log', date('c') . " - Product not found when restoring for order $ordno (productId $pid)\n", FILE_APPEND);
                }
            }
        }

        // also revert any flat items table (if present) but avoid double-restoring
        $itRes2 = @mysqli_query($con, "SELECT productId, quantity FROM order_items_flat WHERE orderNumber='".mysqli_real_escape_string($con,$ordno)."'");
        if ($itRes2) {
            while ($it2 = mysqli_fetch_assoc($itRes2)) {
                $pid2 = intval($it2['productId']);
                $q2 = floatval($it2['quantity']);

                $already = isset($restored[$pid2]) ? floatval($restored[$pid2]) : 0;
                $toAdd2 = max(0, $q2 - $already);
                if ($toAdd2 <= 0) continue; // nothing to add

                $pRes2 = @mysqli_query($con, "SELECT Quantity FROM products WHERE id='".$pid2."' LIMIT 1");
                if ($pRes2 && $prow2 = mysqli_fetch_assoc($pRes2)) {
                    $current2 = floatval($prow2['Quantity']);
                    $new2 = round($current2 + $toAdd2, 4);
                    @mysqli_query($con, "UPDATE products SET Quantity='".mysqli_real_escape_string($con,$new2)."' WHERE id='".$pid2."'");
                    @mysqli_query($con, "INSERT INTO `usage` (productId, qty, unit, usedBy, action, notes) VALUES ('".$pid2."', '".floatval($toAdd2)."', 'kg', '0', 'restock', 'Reverted by admin on order cancellation #".intval($oid)."')");

                    // notify admin clients about the revert (flat items)
                    $meta2 = json_encode(array('orderId' => intval($oid), 'actionBy' => intval($actionby)));
                    @mysqli_query($con, "INSERT INTO notification (userId,productId,qty,unit,action,meta) VALUES ('0','".intval($pid2)."','".floatval($toAdd2)."','kg','restock','".mysqli_real_escape_string($con,$meta2)."')");

                    if (!isset($restored[$pid2])) $restored[$pid2] = 0;
                    $restored[$pid2] += $toAdd2;
                }
            }
        }
    }

    // finalize cancel: update history, mark order cancelled and commit/rollback
    if ($ok) {
        $ins = "INSERT INTO ordertrackhistory(orderId,status,remark,actionBy,canceledBy) VALUES('".intval($oid)."','Cancelled','".mysqli_real_escape_string($con,$remark)."','".$actionby."','Admin')";
        @mysqli_query($con, $ins);

        // update orders status column(s)
        $hasOrderStatus = false;
        $hasStatus = false;
        $c1 = @mysqli_query($con, "SHOW COLUMNS FROM `orders` LIKE 'orderStatus'");
        if ($c1 && mysqli_num_rows($c1)) { $hasOrderStatus = true; }
        $c2 = @mysqli_query($con, "SHOW COLUMNS FROM `orders` LIKE 'status'");
        if ($c2 && mysqli_num_rows($c2)) { $hasStatus = true; }
        $sets = [];
        if ($hasOrderStatus) $sets[] = "orderStatus='Cancelled'";
        if ($hasStatus) $sets[] = "status='Cancelled'";
        if (!empty($sets)) {
            $sqlu = "UPDATE orders SET " . implode(',', $sets) . " WHERE id='".intval($oid)."'";
            @mysqli_query($con, $sqlu);
        }

        @mysqli_commit($con);
    } else {
        @mysqli_rollback($con);
    }

    @mysqli_autocommit($con, true);

    echo "<script>window.location.href='new-order.php';</script>";
    exit;
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
        <title>GHP INVENTORY MANAGEMENT SYSTEM | <?php echo __('MANAGE_NEW_ORDERS'); ?></title>
        <link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" />
        <link href="css/styles.css" rel="stylesheet" />
        <style>
            /* Balanced admin table styles (improved visual hierarchy) */
            :root{--admin-font: 15px; --admin-padding-y: .65rem;}
            body{font-size:var(--admin-font)}
            .badge{font-size:.95rem;padding:.45em .6em}
            table#datatablesSimple{width:100%;}
            table#datatablesSimple thead th{font-weight:700; font-size:0.95rem; padding:.6rem .8rem}
            table#datatablesSimple tbody td{vertical-align:middle; padding:var(--admin-padding-y) .8rem}
            table#datatablesSimple tbody tr{height:56px}
            h1{font-size:1.6rem;margin-bottom:.8rem}
            .admin-card .card-body{padding:1.25rem}
            /* Buttons */
            .btn-smooth, .btn-sm{padding:.45rem .7rem;font-size:.95rem}
            .btn-primary{border-radius:6px}
            /* Purpose truncation + tooltip */
            .purpose-cell{max-width:380px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
            /* make datatable more readable */
            table#datatablesSimple tbody tr:hover{background:#f8f9fb}
        </style>
        <script src="js/all.min.js" crossorigin="anonymous"></script>
    </head>
    <body class="sb-nav-fixed">
 <?php include_once('includes/header.php');?>
        <div id="layoutSidenav">
       <?php include_once('includes/sidebar.php');?>
            <div id="layoutSidenav_content">
                <main>
                    <div class="container-fluid px-4">
                        <h1 class="mt-4"><?php echo __('MANAGE_NEW_ORDERS'); ?></h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="dashboard.php"><?php echo __('DASHBOARD'); ?></a></li>
                            <li class="breadcrumb-item active"><?php echo __('MANAGE_NEW_ORDERS'); ?></li>
                        </ol>
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-table me-1"></i>
                               <?php echo __('ALL_ORDER_DETAILS'); ?>
                            </div>
                            <div class="card-body">
                                <table id="datatablesSimple" class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th><?php echo __('ORDER_NUMBER'); ?></th>
                                            <th><?php echo __('ORDER_BY'); ?></th>
                                            <th><?php echo __('PURPOSE'); ?></th>
                                            <th><?php echo __('ORDER_AMOUNT'); ?></th>
                                            <th><?php echo __('ORDER_DATE'); ?></th>
                                            <th><?php echo __('ORDER_STATUS'); ?></th>
                                            <th><?php echo __('ACTION'); ?></th>
                                        </tr>
                                    </thead>
                           
                                    <tbody>
<?php
// Determine which status column exists and build a query that shows new/pending orders
$hasOrderStatus = false;
$hasStatus = false;
$c1 = @mysqli_query($con, "SHOW COLUMNS FROM `orders` LIKE 'orderStatus'");
if ($c1 && mysqli_num_rows($c1)) { $hasOrderStatus = true; }
$c2 = @mysqli_query($con, "SHOW COLUMNS FROM `orders` LIKE 'status'");
if ($c2 && mysqli_num_rows($c2)) { $hasStatus = true; }

// Choose a safe ORDER BY expression depending on whether orders.created_at exists
$hasCreatedAt = false;
$c3 = @mysqli_query($con, "SHOW COLUMNS FROM `orders` LIKE 'created_at'");
if ($c3 && mysqli_num_rows($c3)) { $hasCreatedAt = true; }
$orderExpr = $hasCreatedAt ? "COALESCE(orders.created_at, orders.orderDate, orders.id)" : "COALESCE(orders.orderDate, orders.id)";

if ($hasOrderStatus) {
    $sql = "SELECT orders.id, orderNumber, totalAmount, orderStatus AS ostatus, orderDate, orders.purpose, users.name, users.contactno 
        FROM `orders` JOIN users ON users.id=orders.userId 
        WHERE (orderStatus IS NULL OR orderStatus='' OR orderStatus='Pending') ORDER BY " . $orderExpr . " DESC";
} elseif ($hasStatus) {
    $sql = "SELECT orders.id, orderNumber, totalAmount, status AS ostatus, orderDate, orders.purpose, users.name, users.contactno 
        FROM `orders` JOIN users ON users.id=orders.userId 
        WHERE (status IS NULL OR status='' OR status='Pending') ORDER BY " . $orderExpr . " DESC";
} else {
    // No known status columns — nothing to show to avoid unexpected behavior
    $sql = "SELECT orders.id, orderNumber, totalAmount, '' AS ostatus, orderDate, users.name, users.contactno 
        FROM `orders` JOIN users ON users.id=orders.userId WHERE 1=0";
}

$query = mysqli_query($con, $sql);
$cnt=1;
while($row=mysqli_fetch_array($query))
{
?>  

                                <tr data-orderid="<?php echo intval($row['id']); ?>">
                                            <td><?php echo htmlentities($cnt);?></td>
                                            <td><?php echo htmlentities($row['orderNumber']);?></td>
                                            <td><?php echo htmlentities($row['name']);?></td>
                                            <td class="purpose-cell" title="<?php echo htmlspecialchars(isset($row['purpose']) ? $row['purpose'] : ''); ?>"><?php echo htmlentities(isset($row['purpose']) ? (strlen($row['purpose']) > 40 ? substr($row['purpose'],0,40) . '...' : $row['purpose']) : ''); ?></td>
                                            <td> <?php echo htmlentities($row['totalAmount']);?></td>
                                            <td><?php echo htmlentities($row['orderDate']);?></td>
                                    <td class="order-status-cell"><?php $ostatus = isset($row['ostatus']) ? $row['ostatus'] : '';
                                               if($ostatus=='' || $ostatus=='Pending'): ?>
       <span class="badge bg-danger"><?php echo __('STATUS_NEW_ORDER'); ?></span>
    <?php elseif($ostatus=='Packed'):?>
<span class="badge bg-warning"><?php echo __('STATUS_PACKED'); ?></span>
   <?php elseif($ostatus=='Dispatched'):?>
<span class="badge bg-info"><?php echo __('STATUS_DISPATCHED'); ?></span>
    <?php elseif($ostatus=='In Transit'):?>
<span class="badge bg-secondary"><?php echo __('STATUS_IN_TRANSIT'); ?></span>
    <?php elseif($ostatus=='Out For Delivery'):?>
        <span class="badge bg-dark"><?php echo __('STATUS_OUT_FOR_DELIVERY'); ?></span>
          <?php elseif($ostatus=='Delivered'):?>
        <span class="badge bg-success"><?php echo __('STATUS_DELIVERED'); ?></span>
        <?php endif;?>


                                        </td>
                                                                                        <td>
                                                                                        <a href="order-details.php?orderid=<?php echo $row['id']?>" target="_self" class="btn btn-primary btn-sm btn-smooth" rel="noopener noreferrer"><?php echo __('VIEW_DETAILS'); ?></a>
                                                                                        <?php if($ostatus=='' || $ostatus=='Pending'): ?>

                                                                                            &nbsp;<a href="new-order.php?confirm=<?php echo $row['id'];?>" class="btn btn-success btn-sm" onclick="return confirm('<?php echo addslashes(sprintf( __( 'CONFIRM_CHECKOUT' ), isset($row['purpose']) ? $row['purpose'] : '' )); ?>');"><?php echo __('CONFIRM'); ?></a>
                                                                                            &nbsp;<a href="new-order.php?cancel=<?php echo $row['id'];?>" class="btn btn-danger btn-sm" onclick="return confirm('<?php echo addslashes(sprintf( __( 'CANCEL_ORDER_CONFIRM' ), isset($row['purpose']) ? $row['purpose'] : '' )); ?>');"><?php echo __('CANCEL_ORDER'); ?></a>
                                                                                        <?php endif; ?>
                                                                                        </td>
                                        </tr>
                                        <?php $cnt=$cnt+1; } ?>
                                       
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </main>
<?php include_once('includes/footer.php');?>
                </footer>
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
                    '<strong class="me-auto">Status</strong>' +
                    '<small class="text-muted ms-2"></small>' +
                    '<button type="button" class="btn-close btn-close-white ms-2 mb-1" data-bs-dismiss="toast" aria-label="Close"></button>' +
                    '</div>' +
                    '<div class="toast-body">'+msg+'</div>' +
                    '</div>';
                cont.insertAdjacentHTML('beforeend', toastHtml);
                var tEl = document.getElementById(toastId);
                try { var bsToast = new bootstrap.Toast(tEl); bsToast.show(); } catch(ex) { setTimeout(function(){ tEl.remove(); }, 3000); }
                tEl.addEventListener('hidden.bs.toast', function(){ tEl.remove(); });
            }

            if (window.BroadcastChannel) {
                try {
                    var bc = new BroadcastChannel('fvsms_orders');
                    bc.addEventListener('message', function(e){
                        var data = e.data || {};
                        if (!data || data.type !== 'order-updated') return;
                        var tr = document.querySelector('tr[data-orderid="'+data.orderId+'"]');
                        if (!tr) return;
                        var statusCell = tr.querySelector('.order-status-cell');
                        if (statusCell && data.statusBadge) statusCell.innerHTML = data.statusBadge;
                        var actionCell = tr.querySelector('td:last-child');
                        if (actionCell) {
                            var confirmBtn = actionCell.querySelector('a.btn-success');
                            var cancelBtn = actionCell.querySelector('a.btn-danger');
                            if (confirmBtn) confirmBtn.remove();
                            if (cancelBtn) cancelBtn.remove();
                        }
                        showTempAlert('Order ' + data.orderId + ' updated: ' + (data.status || ''), 'success');
                    });
                } catch(e) { /* ignore */ }
            }
        })();
        </script>
    </body>
</html>
<?php } ?>
