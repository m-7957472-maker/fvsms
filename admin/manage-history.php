<?php session_start();
include_once(__DIR__ . '/../includes/config.php');
include_once(__DIR__ . '/../includes/lang.php');
if(strlen( $_SESSION["aid"])==0) {   
    header('location:logout.php');
} else {

// Handle clear actions
$success = '';
$error = '';
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    if(isset($_POST['clear_usage'])){
        $q = mysqli_query($con, "TRUNCATE TABLE `usage`");
        if($q){ $success = __('USAGE_LOG_CLEARED'); file_put_contents(__DIR__ . '/manage-history.log', date('c') . " - cleared usage\n", FILE_APPEND); } else { $error = 'Failed to clear usage: '.mysqli_error($con); }
    }
    if(isset($_POST['clear_notifications'])){
        $q = mysqli_query($con, "TRUNCATE TABLE `notification`");
        if($q){ $success = __('NOTIFICATIONS_CLEARED'); file_put_contents(__DIR__ . '/manage-history.log', date('c') . " - cleared notifications\n", FILE_APPEND); } else { $error = 'Failed to clear notifications: '.mysqli_error($con); }
    }
    if(isset($_POST['clear_order_items'])){
        $q = mysqli_query($con, "TRUNCATE TABLE `order_items_flat`");
        if($q){ $success = __('ORDER_ITEMS_FLAT_CLEARED'); file_put_contents(__DIR__ . '/manage-history.log', date('c') . " - cleared order_items_flat\n", FILE_APPEND); } else { $error = 'Failed to clear order_items_flat: '.mysqli_error($con); }
    }
    if(isset($_POST['clear_inventory_orders'])){
        // delete orders with txnType = 'inventory' and related ordersdetails / flat items
        $ords = [];
        $oq = mysqli_query($con, "SELECT orderNumber FROM orders WHERE txnType='inventory'");
        if($oq){
            while($or = mysqli_fetch_assoc($oq)){
                $ords[] = mysqli_real_escape_string($con, $or['orderNumber']);
            }
        }
        if(!empty($ords)){
            $in = "'" . implode("','", $ords) . "'";
            $d1 = mysqli_query($con, "DELETE FROM ordersdetails WHERE orderNumber IN ($in)");
            $d2 = mysqli_query($con, "DELETE FROM order_items_flat WHERE orderNumber IN ($in)");
            $d3 = mysqli_query($con, "DELETE FROM orders WHERE orderNumber IN ($in) AND txnType='inventory'");
            if($d3){ $success = __('INVENTORY_ORDERS_DELETED'); file_put_contents(__DIR__ . '/manage-history.log', date('c') . " - cleared inventory orders\n", FILE_APPEND); } else { $error = 'Failed to clear inventory orders: '.mysqli_error($con); }
        } else {
            $success = __('NO_INVENTORY_ORDERS_FOUND');
        }
    }
}

// Ensure tables exist where applicable
mysqli_query($con, "CREATE TABLE IF NOT EXISTS `notification` (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    userId INT NOT NULL,
    productId INT NOT NULL,
    qty DECIMAL(10,4) DEFAULT NULL,
    unit VARCHAR(10) DEFAULT 'kg',
    action VARCHAR(50) DEFAULT 'unknown',
    meta TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

mysqli_query($con, "CREATE TABLE IF NOT EXISTS order_items_flat (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    orderNumber VARCHAR(64),
    productId INT,
    productName VARCHAR(255),
    quantity DECIMAL(10,4),
    unit VARCHAR(10) DEFAULT 'kg',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

?>
<!DOCTYPE html>
<html lang="ms">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>GHP INVENTORY MANAGEMENT SYSTEM | <?php echo __('MANAGE_HISTORY'); ?></title>
        <link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" />
        <link href="css/styles.css" rel="stylesheet" />
        <script src="js/all.min.js" crossorigin="anonymous"></script>
        <script src="js/jquery-3.5.1.min.js"></script>
    </head>
    <body class="sb-nav-fixed">
        <?php include_once('includes/header.php');?>
        <div id="layoutSidenav">
            <?php include_once('includes/sidebar.php');?>
            <div id="layoutSidenav_content">
                <main>
                    <div class="container-fluid px-4">
                        <h1 class="mt-4"><?php echo __('MANAGE_HISTORY_LOGS'); ?></h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="dashboard.php"><?php echo __('DASHBOARD'); ?></a></li>
                            <li class="breadcrumb-item active"><?php echo __('MANAGE_HISTORY'); ?></li>
                        </ol>

                        <?php if($success): ?>
                            <div class="alert alert-success"><?php echo htmlentities($success); ?></div>
                        <?php endif; ?>
                        <?php if($error): ?>
                            <div class="alert alert-danger"><?php echo htmlentities($error); ?></div>
                        <?php endif; ?>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <i class="fas fa-bell me-1"></i>
                                        <?php echo __('NOTIFICATIONS_LOG'); ?>
                                    </div>
                                    <div class="card-body">
                                        <form method="post" onsubmit="return confirm('<?php echo __('CONFIRM_CLEAR_NOTIFICATIONS'); ?>');">
                                            <button type="submit" name="clear_notifications" class="btn btn-danger mb-3"><?php echo __('CLEAR_NOTIFICATIONS'); ?></button>
                                        </form>
                                        <div class="table-responsive">
                                            <table class="table table-striped table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th><?php echo __('ID'); ?></th>
                                                        <th><?php echo __('USER'); ?></th>
                                                        <th><?php echo __('PRODUCT'); ?></th>
                                                        <th><?php echo __('QUANTITY'); ?></th>
                                                        <th><?php echo __('UNIT'); ?></th>
                                                        <th><?php echo __('ACTION'); ?></th>
                                                        <th><?php echo __('META'); ?></th>
                                                        <th><?php echo __('CREATED'); ?></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    $nq = mysqli_query($con, "SELECT n.*, p.productName FROM notification n LEFT JOIN products p ON p.id = n.productId ORDER BY n.created_at DESC LIMIT 200");
                                                    if(mysqli_num_rows($nq)>0){
                                                        while($nr = mysqli_fetch_assoc($nq)){
                                                            echo "<tr>";
                                                            echo "<td>".intval($nr['id'])."</td>";
                                                            echo "<td>".intval($nr['userId'])."</td>";
                                                            echo "<td>".htmlentities($nr['productName'])."</td>";
                                                            echo "<td>".htmlentities($nr['qty'])."</td>";
                                                            echo "<td>".htmlentities($nr['unit'])."</td>";
                                                            // map action to Malay labels when possible
                                                            $actionLabel = htmlentities($nr['action']);
                                                            if ($nr['action'] === 'checkout_cart') $actionLabel = htmlentities(__('NOTIF_CHECKOUT_CART'));
                                                            if ($nr['action'] === 'checkout_single') $actionLabel = htmlentities(__('NOTIF_CHECKOUT_SINGLE'));
                                                            echo "<td>". $actionLabel ."</td>";
                                                            // meta (try to decode JSON and show orderNumber/purpose)
                                                            $metaDisplay = '';
                                                            if (!empty($nr['meta'])) {
                                                                $m = json_decode($nr['meta'], true);
                                                                if (is_array($m)) {
                                                                    if (!empty($m['orderNumber'])) $metaDisplay .= htmlentities(__('ORDER_NUMBER')) . ': ' . htmlentities($m['orderNumber']) . '<br/>';
                                                                    if (!empty($m['purpose'])) $metaDisplay .= htmlentities(__('PURPOSE')) . ': ' . htmlentities($m['purpose']) . '<br/>';
                                                                    if (!empty($m['requestedBy'])) $metaDisplay .= htmlentities(__('REQUESTED_BY')) . ': #' . intval($m['requestedBy']);
                                                                } else {
                                                                    $metaDisplay = htmlentities($nr['meta']);
                                                                }
                                                            }
                                                            echo "<td>". $metaDisplay ."</td>";
                                                            echo "<td>".htmlentities($nr['created_at'])."</td>";
                                                            echo "</tr>";
                                                        }
                                                    } else {
                                                        echo "<tr><td colspan='8' class='text-center'>".htmlentities(__('NO_NOTIFICATIONS'))."</td></tr>";
                                                    }
                                                    ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <div class="card mb-4">
                                    <div class="card-header">
                                        <i class="fas fa-list me-1"></i>
                                        <?php echo __('ORDER_ITEMS_FLAT'); ?>
                                    </div>
                                    <div class="card-body">
                                        <form method="post" onsubmit="return confirm('<?php echo __('CONFIRM_CLEAR_ORDER_ITEMS'); ?>');">
                                            <button type="submit" name="clear_order_items" class="btn btn-danger mb-3"><?php echo __('CLEAR_ORDER_ITEMS'); ?></button>
                                        </form>
                                        <form method="post" onsubmit="return confirm('<?php echo __('CONFIRM_CLEAR_INVENTORY_ORDERS'); ?>');">
                                            <button type="submit" name="clear_inventory_orders" class="btn btn-warning mb-3"><?php echo __('CLEAR_INVENTORY_ORDERS'); ?></button>
                                        </form>
                                        <div class="table-responsive">
                                            <table class="table table-striped table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th><?php echo __('ORDER_NUMBER'); ?></th>
                                                        <th><?php echo __('PRODUCT'); ?></th>
                                                        <th><?php echo __('QUANTITY'); ?></th>
                                                        <th><?php echo __('UNIT'); ?></th>
                                                        <th><?php echo __('CREATED'); ?></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    $oq = mysqli_query($con, "SELECT * FROM order_items_flat ORDER BY created_at DESC LIMIT 200");
                                                    if(mysqli_num_rows($oq)>0){
                                                        while($or = mysqli_fetch_assoc($oq)){
                                                            echo "<tr>";
                                                            echo "<td>".htmlentities($or['orderNumber'])."</td>";
                                                            echo "<td>".htmlentities($or['productName'])."</td>";
                                                            echo "<td>".formatQuantityNumber($or['quantity'])."</td>";
                                                            echo "<td>".htmlentities($or['unit'])."</td>";
                                                            echo "<td>".htmlentities($or['created_at'])."</td>";
                                                            echo "</tr>";
                                                        }
                                                    } else {
                                                        echo "<tr><td colspan='5' class='text-center'>".htmlentities(__('NO_ORDER_ITEMS_RECORDED'))."</td></tr>";
                                                    }
                                                    ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <div class="card mb-4">
                                    <div class="card-header">
                                        <i class="fas fa-history me-1"></i>
                                        <?php echo __('MISC_USAGE_LOGS'); ?>
                                    </div>
                                    <div class="card-body">
                                        <form method="post" onsubmit="return confirm('<?php echo __('CONFIRM_CLEAR_USAGE_LOG'); ?>');">
                                            <button type="submit" name="clear_usage" class="btn btn-danger mb-3"><?php echo __('CLEAR_USAGE_LOG'); ?></button>
                                        </form>
                                        <div class="table-responsive">
                                            <table class="table table-striped table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th><?php echo __('CREATED'); ?></th>
                                                        <th><?php echo __('PRODUCT'); ?></th>
                                                        <th><?php echo __('QUANTITY'); ?></th>
                                                        <th><?php echo __('UNIT'); ?></th>
                                                        <th><?php echo __('ACTION'); ?></th>
                                                        <th><?php echo __('USER'); ?></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    $query = "SELECT u.id, u.usedAt, u.qty, u.unit, u.action, u.notes, p.productName, u.usedBy FROM `usage` u 
                                                            JOIN products p ON u.productId = p.id ORDER BY u.usedAt DESC LIMIT 200";
                                                    $usageQuery = mysqli_query($con, $query);
                                                    if(mysqli_num_rows($usageQuery) > 0) {
                                                        while($row = mysqli_fetch_array($usageQuery)) {
                                                            echo "<tr>";
                                                            echo "<td>".htmlentities($row['usedAt'])."</td>";
                                                            echo "<td>".htmlentities($row['productName'])."</td>";
                                                            echo "<td>".formatQuantityNumber($row['qty'])."</td>";
                                                            echo "<td>".htmlentities($row['unit'])."</td>";
                                                            echo "<td>".htmlentities($row['action'])."</td>";
                                                            echo "<td>".intval($row['usedBy'])."</td>";
                                                            echo "</tr>";
                                                        }
                                                    } else {
                                                        echo "<tr><td colspan='6' class='text-center'>".htmlentities(__('NO_USAGE_RECORDS_FOUND'))."</td></tr>";
                                                    }
                                                    ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>

                    </div>
                </main>
                <?php include_once('includes/footer.php');?>
            </div>
        </div>
        <script src="js/bootstrap.bundle.min.js"></script>
        <script src="js/scripts.js"></script>
    </body>
</html>
<?php } ?>