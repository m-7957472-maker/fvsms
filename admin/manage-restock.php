<?php
session_start();
include_once(__DIR__ . '/../includes/config.php');
if(strlen($_SESSION['aid'])==0) {
    header('location:logout.php');
    exit;
}

// Handle restock submission -- use REQUEST_METHOD POST so disabled submit button won't prevent processing
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    file_put_contents(__DIR__ . '/restock_debug.log', date('c') . " POST RECEIVED: " . print_r($_POST, true) . "\n", FILE_APPEND);

    // If admin requested to clear restock history
    if (isset($_POST['clear_history'])) {
        // ensure only admin can do this
        $delSql = "DELETE FROM `usage` WHERE action = 'restock'";
        file_put_contents(__DIR__ . '/restock_debug.log', date('c') . " Executing DELETE: $delSql\n", FILE_APPEND);
        $delRes = mysqli_query($con, $delSql);
        if ($delRes) {
            $success = 'Restock history cleared.';
            file_put_contents(__DIR__ . '/restock_debug.log', date('c') . " SUCCESS: Cleared restock history\n", FILE_APPEND);
        } else {
            $error = 'Failed to clear restock history: ' . mysqli_error($con);
            file_put_contents(__DIR__ . '/restock_debug.log', date('c') . " DELETE failed: " . mysqli_error($con) . "\n", FILE_APPEND);
        }
        // stop further processing for this request
        exit;
    }

    $productId = intval($_POST['productId'] ?? 0);
    $restockQty = floatval($_POST['restockQty'] ?? 0);
    $restockUnit = isset($_POST['restockUnit']) ? $_POST['restockUnit'] : 'kg';
    $restockNotes = isset($_POST['restockNotes']) ? mysqli_real_escape_string($con, $_POST['restockNotes']) : '';
    $restockedBy = intval($_SESSION['aid']);

    file_put_contents(__DIR__ . '/restock_debug.log', date('c') . " Parsed: productId=$productId, qty=$restockQty, unit=$restockUnit, admin=$restockedBy\n", FILE_APPEND);

    if($productId <= 0) {
        $error = __('PLEASE_ENTER_QUANTITY_GREATER_THAN_ZERO');
        file_put_contents(__DIR__ . '/restock_debug.log', date('c') . " Error: Invalid productId=$productId\n", FILE_APPEND);
    } else if($restockQty <= 0) {
        $error = __('PLEASE_ENTER_QUANTITY_GREATER_THAN_ZERO');
        file_put_contents(__DIR__ . '/restock_debug.log', date('c') . " Error: Invalid qty=$restockQty\n", FILE_APPEND);
    } else {
        $restockQtyKg = ($restockUnit === 'g') ? ($restockQty / 1000.0) : $restockQty;
        $restockQtyKg = round($restockQtyKg, 4);

        $updateSQL = "UPDATE products SET Quantity = Quantity + $restockQtyKg WHERE id = '$productId'";
        file_put_contents(__DIR__ . '/restock_debug.log', date('c') . " Executing UPDATE: $updateSQL\n", FILE_APPEND);
        
        $updateQuery = mysqli_query($con, $updateSQL);
        if($updateQuery) {
            $logSQL = "INSERT INTO `usage`(productId, qty, unit, usedBy, action, notes, usedAt) VALUES('$productId', '$restockQtyKg', '$restockUnit', '$restockedBy', 'restock', '$restockNotes', NOW())";
            file_put_contents(__DIR__ . '/restock_debug.log', date('c') . " Executing INSERT: $logSQL\n", FILE_APPEND);
            
            $logQuery = mysqli_query($con, $logSQL);
            if($logQuery) {
                $success = 'Stock added successfully! Item quantity updated.';
                file_put_contents(__DIR__ . '/restock_debug.log', date('c') . " SUCCESS: Restock recorded\n", FILE_APPEND);

                // Ensure notification table exists
                $createNotifTbl = "CREATE TABLE IF NOT EXISTS notification (
                    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    userId INT NOT NULL,
                    productId INT NOT NULL,
                    qty DECIMAL(10,4) DEFAULT NULL,
                    unit VARCHAR(10) DEFAULT 'kg',
                    action VARCHAR(50) DEFAULT 'unknown',
                    meta TEXT DEFAULT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
                @mysqli_query($con, $createNotifTbl);
                // ensure columns exist on older installs
                $colCheck = mysqli_query($con, "SHOW COLUMNS FROM notification LIKE 'action'");
                if (!$colCheck || mysqli_num_rows($colCheck) === 0) {
                    @mysqli_query($con, "ALTER TABLE notification ADD COLUMN action VARCHAR(50) DEFAULT 'unknown'");
                }
                $metaCheck = mysqli_query($con, "SHOW COLUMNS FROM notification LIKE 'meta'");
                if (!$metaCheck || mysqli_num_rows($metaCheck) === 0) {
                    @mysqli_query($con, "ALTER TABLE notification ADD COLUMN meta TEXT DEFAULT NULL");
                }

                // Notify users who have this product in their wishlist
                $pRes = mysqli_query($con, "SELECT productName FROM products WHERE id='$productId' LIMIT 1");
                $productName = '';
                if($pRes && mysqli_num_rows($pRes)>0) {
                    $pr = mysqli_fetch_assoc($pRes);
                    $productName = $pr['productName'];
                }

                $wishRes = mysqli_query($con, "SELECT userId FROM wishlist WHERE productId='$productId'");
                if($wishRes && mysqli_num_rows($wishRes) > 0) {
                        while($w = mysqli_fetch_assoc($wishRes)) {
                            $uid = intval($w['userId']);
                            // store notification qty in the original unit (grams or kg) for user-friendly display
                            if ($restockUnit === 'g') {
                                $notifQty = floatval($_POST['restockQty'] ?? $restockQty);
                            } else {
                                $notifQty = $restockQtyKg;
                            }
                            $safeUnit = mysqli_real_escape_string($con, $restockUnit);
                            $ins = @mysqli_query($con, "INSERT INTO notification(userId,productId,qty,unit,action) VALUES('".$uid."','".$productId."','".$notifQty."','".$safeUnit."','restock')");
                        if(!$ins) {
                            @file_put_contents(__DIR__ . '/restock_debug.log', date('c') . " - NOTIFICATION insert failed for user $uid: " . mysqli_error($con) . "\n", FILE_APPEND);
                        }

                        // send email to user if email exists
                        $uRes = mysqli_query($con, "SELECT name, email FROM users WHERE id='".$uid."' LIMIT 1");
                        if($uRes && mysqli_num_rows($uRes)>0) {
                            $u = mysqli_fetch_assoc($uRes);
                            $to = $u['email'];
                            if(filter_var($to, FILTER_VALIDATE_EMAIL)) {
                                $subject = "Product restocked: " . $productName;
                                $msg = "Hello " . $u['name'] . ",\n\nThe product '$productName' has been restocked. Quantity added: " . $restockQtyKg . " " . $restockUnit . ".\n\nVisit the product page to purchase.\n\nRegards,\nGHP Inventory";
                                $headers = "From: no-reply@fruitkha.com\r\n";
                                $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
                                @mail($to, $subject, $msg, $headers);
                                @file_put_contents(__DIR__ . '/restock_debug.log', date('c') . " - Email sent to $to about product $productId\n", FILE_APPEND);
                            }
                        }
                    }
                } else {
                    file_put_contents(__DIR__ . '/restock_debug.log', date('c') . " - No wishlist entries for product $productId\n", FILE_APPEND);
                }

                // Notify admin email (fallback to support address)
                $adminEmail = 'support@fruitkha.com';
                $adminSub = "Stock restocked: " . $productName;
                $adminMsg = "Admin ID $restockedBy added $restockQtyKg $restockUnit to product '$productName' (ID: $productId) on " . date('Y-m-d H:i:s') . ".\nNotes: $restockNotes";
                @mail($adminEmail, $adminSub, $adminMsg, "From: no-reply@fruitkha.com\r\nContent-Type: text/plain; charset=UTF-8\r\n");

                // add an admin-visible notification row (userId = 0 reserved for admin) storing qty in original unit
                if ($restockUnit === 'g') {
                    $adminNotifQty = floatval($_POST['restockQty'] ?? $restockQty);
                } else {
                    $adminNotifQty = $restockQtyKg;
                }
                @mysqli_query($con, "INSERT INTO notification(userId,productId,qty,unit,action,meta) VALUES('0','$productId','$adminNotifQty','".mysqli_real_escape_string($con,$restockUnit)."','restock','".mysqli_real_escape_string($con,json_encode(['restockedBy'=>$restockedBy]))."')");
            } else {
                $error = 'Stock updated but logging failed: ' . mysqli_error($con);
                file_put_contents(__DIR__ . '/restock_debug.log', date('c') . " Insert error: " . mysqli_error($con) . "\n", FILE_APPEND);
            }
        } else {
            $error = 'Error updating stock: ' . mysqli_error($con);
            file_put_contents(__DIR__ . '/restock_debug.log', date('c') . " UPDATE failed: " . mysqli_error($con) . "\n", FILE_APPEND);
        }
    }
}

// Ensure usage table exists
$createTableQuery = "CREATE TABLE IF NOT EXISTS `usage` (
    id INT AUTO_INCREMENT PRIMARY KEY,
    productId INT NOT NULL,
    qty DECIMAL(10,4),
    unit VARCHAR(10),
    usedBy INT,
    action VARCHAR(50),
    notes TEXT,
    usedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (productId) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
mysqli_query($con, $createTableQuery);

// Debug: check if products table has data
$testQuery = mysqli_query($con, "SELECT COUNT(*) as cnt FROM products");
$testResult = mysqli_fetch_array($testQuery);
@file_put_contents(__DIR__ . '/restock_debug.log', date('c') . " Products count: " . $testResult['cnt'] . "\n", FILE_APPEND);
?>
<!DOCTYPE html>
<html lang="ms">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title><?php echo __('SITE_TITLE') . ' | ' . __('MANAGE_RESTOCK'); ?></title>
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
                        <h1 class="mt-4"><?php echo __('MANAGE_RESTOCK'); ?></h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="dashboard.php"><?php echo __('DASHBOARD'); ?></a></li>
                            <li class="breadcrumb-item active"><?php echo __('MANAGE_RESTOCK'); ?></li>
                        </ol> 

                        <?php if(isset($success)): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php endif; ?>

                        <?php if(isset($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php endif; ?>
                        
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-plus me-1"></i>
                                <?php echo __('ADD_STOCK_TO_PRODUCT'); ?>
                            </div> 
                            <div class="card-body">
                                <form method="post" enctype="multipart/form-data" id="restockForm">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label"><?php echo __('SELECT_PRODUCT'); ?></label>
                                                <select name="productId" id="productSelect" class="form-control" required>
                                                    <option value=""><?php echo __('SELECT_PRODUCT_PLACEHOLDER'); ?></option> 
                                                    <?php 
                                                    $productQuery = mysqli_query($con, "SELECT id, productName, Quantity FROM products ORDER BY productName ASC");
                                                    $queryLog = "SELECT executed; ";
                                                    if(!$productQuery) {
                                                        $queryLog .= "Query failed: " . mysqli_error($con);
                                                        @file_put_contents(__DIR__ . '/inventory_errors.log', date('c') . " - SELECT failed: " . mysqli_error($con) . "\n", FILE_APPEND);
                                                        echo "<option value=\"\">" . __('UNABLE_LOAD_PRODUCTS') . "</option>";
                                                    } else {
                                                        $rowCount = mysqli_num_rows($productQuery);
                                                        $queryLog .= "Rows: $rowCount";
                                                        @file_put_contents(__DIR__ . '/restock_debug.log', date('c') . " DROPDOWN: " . $queryLog . "\n", FILE_APPEND);
                                                        if($rowCount == 0) {
                                                            echo "<option value=\"\">" . __('NO_PRODUCTS_AVAILABLE') . "</option>";
                                                        } else {
                                                            while($prow = mysqli_fetch_array($productQuery)) {
                                                                $grams = floatval($prow['Quantity']) * 1000;
                                                                if ($grams >= 1000 && fmod($grams, 1000) == 0) {
                                                                    $displayQty = round($grams / 1000) . " kg";
                                                                } else {
                                                                    $displayQty = round($grams) . " gram";
                                                                }
                                                                echo "<option value='" . $prow['id'] . "'>" . htmlentities($prow['productName']) . " (" . __('CURRENT') . ": " . $displayQty . ")</option>"; 
                                                            }
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label class="form-label"><?php echo __('QUANTITY'); ?></label>
                                                <input type="number" name="restockQty" id="restockQty" class="form-control" step="any" min="0.001" placeholder="<?php echo __('ENTER_QUANTITY'); ?>" required> 
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label class="form-label">Unit</label>
                                                <select name="restockUnit" class="form-control" required>
                                                    <option value="kg">kg</option>
                                                    <option value="g">g</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label class="form-label"><?php echo __('NOTES_OPTIONAL'); ?></label>
                                                <textarea name="restockNotes" class="form-control" rows="3" placeholder="<?php echo __('ADD_RESTOCK_NOTES'); ?>"></textarea> 
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <button type="submit" name="submit" id="submitBtn" class="btn btn-primary"><?php echo __('ADD_STOCK'); ?></button> 
                                </form>
                            </div>
                        </div>

                        <!-- Restock History -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-history me-1"></i>
                                    Restock History
                                    <form method="post" style="display:inline-block;float:right;margin-top:-6px;">
                                        <button type="submit" name="clear_history" class="btn btn-sm btn-danger" onclick="return confirm('<?php echo __('CONFIRM_CLEAR_RESTOCK_HISTORY'); ?>');"><?php echo __('CLEAR_RESTOCK_HISTORY'); ?></button>   
                                    </form>
                            </div>
                            <div class="card-body">
                                <table id="restockTable" class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th><?php echo __('DATE'); ?></th>
                                            <th><?php echo __('PRODUCT'); ?></th>
                                            <th><?php echo __('QUANTITY_ADDED'); ?></th>
                                            <th><?php echo __('UNIT'); ?></th>
                                            <th><?php echo __('RESTOCKED_BY'); ?></th>
                                            <th><?php echo __('NOTES'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $historyQuery = mysqli_query($con, "SELECT u.id, u.usedAt, u.qty, u.unit, u.notes, p.productName, t.username 
                                                                            FROM `usage` u 
                                                                            JOIN products p ON u.productId = p.id 
                                                                            JOIN tbladmin t ON u.usedBy = t.id 
                                                                            WHERE u.action = 'restock' 
                                                                            ORDER BY u.usedAt DESC LIMIT 50");
                                        while($hrow = mysqli_fetch_array($historyQuery)) {
                                            echo "<tr>";
                                            echo "<td>" . date('M d, Y H:i', strtotime($hrow['usedAt'])) . "</td>";
                                            echo "<td>" . htmlentities($hrow['productName']) . "</td>";
                                            if (stripos($hrow['unit'], 'kg') !== false || stripos($hrow['unit'], 'g') !== false) {
                                                echo "<td>" . formatQuantity($hrow['qty']) . "</td>";
                                            } else {
                                                echo "<td>" . intval(round($hrow['qty'])) . "</td>";
                                            }
                                            echo "<td>" . htmlentities($hrow['unit']) . "</td>";
                                            echo "<td>" . htmlentities($hrow['username']) . "</td>";
                                            echo "<td>" . htmlentities($hrow['notes']) . "</td>";
                                            echo "</tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </main>
                <?php include_once('includes/footer.php');?>
            </div>
        </div>
        <script src="js/bootstrap.bundle.min.js"></script>
        <script src="js/scripts.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var form = document.getElementById('restockForm');
                if (form) {
                    form.addEventListener('submit', function(e) {
                        var btn = document.getElementById('submitBtn');
                        if (btn) {
                            btn.disabled = true;
                            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
                        }
                    });
                }
            });
        </script>
    </body>
</html>
