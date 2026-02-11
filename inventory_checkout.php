<?php
session_start();
include_once('includes/config.php');
include_once('includes/lang.php');

if(strlen($_SESSION['id'])==0){
    echo "<script>alert('".addslashes(__('PLEASE_LOGIN_CHECKOUT'))."'); window.location='login.php';</script>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pid'])) {
    $uid = intval($_SESSION['id']);
    $pid = intval($_POST['pid']);
    $qty = floatval($_POST['qty']);
    $unit = isset($_POST['unit']) ? $_POST['unit'] : 'kg';
    // Optional purpose (used in notifications/usage)
    $purpose = isset($_POST['purpose']) ? trim($_POST['purpose']) : '';

    if ($qty <= 0) {
        echo "<script>alert('".addslashes(__('INVALID_QUANTITY'))."'); window.location='shop.php';</script>";
        exit;
    }

    // convert to kilograms
    if ($unit === 'g') {
        $qtyKg = $qty / 1000.0;
    } else {
        $qtyKg = $qty;
    }

    // fetch current stock
    $res = mysqli_query($con, "SELECT Quantity, productName FROM products WHERE id='$pid' LIMIT 1");
    if (!$res || mysqli_num_rows($res) == 0) {
        echo "<script>alert('".addslashes(__('PRODUCT_NOT_FOUND'))."'); window.location='shop.php';</script>";
        exit;
    }

    $row = mysqli_fetch_assoc($res);
    $current = floatval($row['Quantity']);

    if ($qtyKg > $current) {
        $msg = sprintf(__('INSUFFICIENT_STOCK'), $qtyKg, 'kg', $current, 'kg');
        echo "<script>alert('".addslashes($msg)."'); window.location='shop.php';</script>";
        exit;
    }

    $newQty = $current - $qtyKg;
    // limit to 4 decimal places
    $newQty = round($newQty, 4);

    $update = @mysqli_query($con, "UPDATE products SET Quantity='$newQty' WHERE id='$pid'");
    if ($update) {
        // ensure NOTIFICATION table exists (include action and meta for richer notifications)
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
        // Add columns if older installations do not have them
        $colCheck = mysqli_query($con, "SHOW COLUMNS FROM notification LIKE 'action'");
        if (!$colCheck || mysqli_num_rows($colCheck) === 0) {
            @mysqli_query($con, "ALTER TABLE notification ADD COLUMN action VARCHAR(50) DEFAULT 'unknown'");
        }
        $metaCheck = mysqli_query($con, "SHOW COLUMNS FROM notification LIKE 'meta'");
        if (!$metaCheck || mysqli_num_rows($metaCheck) === 0) {
            @mysqli_query($con, "ALTER TABLE notification ADD COLUMN meta TEXT DEFAULT NULL");
        }

        // ensure USAGE table exists for tracking
        $createUsageTbl = "CREATE TABLE IF NOT EXISTS `usage` (
            id INT AUTO_INCREMENT PRIMARY KEY,
            productId INT NOT NULL,
            qty DECIMAL(10,4),
            unit VARCHAR(10),
            usedBy INT,
            action VARCHAR(50),
            notes TEXT,
            usedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (productId) REFERENCES products(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        @mysqli_query($con, $createUsageTbl);

        // insert notification entry with qty and unit (use lowercase table name)
        $safeUnit = mysqli_real_escape_string($con, $unit);
        // Store notification qty in the same unit as the user provided (grams or kg)
        if ($unit === 'g') {
            $notifQty = floatval($qty);
        } else {
            $notifQty = $qtyKg;
        }
        // user-visible notification (single-product checkout)
        $metaUser = json_encode(['requestedBy' => intval($uid), 'purpose' => (strlen($purpose) ? $purpose : null)]);
        $insSql = "INSERT INTO notification(userId,productId,qty,unit,action,meta) VALUES('".intval($uid)."','".intval($pid)."','".$notifQty."','".$safeUnit."','checkout_single','".mysqli_real_escape_string($con,$metaUser)."')";
        $ins = @mysqli_query($con, $insSql);
        if (!$ins) {
            @file_put_contents(__DIR__ . '/inventory_errors.log', date('c') . " - NOTIFICATION insert failed: " . mysqli_error($con) . "\n", FILE_APPEND);
        }

        // Also insert a notification for admin (userId = 0) so admin clients see usage events — include meta with requester id and purpose
        if ($unit === 'g') {
            $adminNotifQty = floatval($qty);
        } else {
            $adminNotifQty = $qtyKg;
        }
        $metaArr = ['requestedBy' => intval($uid), 'purpose' => (strlen($purpose) ? $purpose : null)];
        $meta = json_encode($metaArr);
        @mysqli_query($con, "INSERT INTO notification(userId,productId,qty,unit,action,meta) VALUES('0','".intval($pid)."','".$adminNotifQty."','".$safeUnit."','checkout_single','".mysqli_real_escape_string($con,$meta)."')");

        // also log to usage table (include user-provided purpose when present)
        $notes = mysqli_real_escape_string($con, __('USER_CHECKOUT_NOTE'));
        if (strlen($purpose)) $notes .= ' - ' . mysqli_real_escape_string($con, $purpose);
        $usageLogSql = "INSERT INTO `usage`(productId, qty, unit, usedBy, action, notes) VALUES('".intval($pid)."', '".floatval($qtyKg)."', '".$safeUnit."', '".intval($uid)."', 'checkout', '".$notes."')";
        $usageLog = @mysqli_query($con, $usageLogSql);
        if (!$usageLog) {
            @file_put_contents(__DIR__ . '/inventory_errors.log', date('c') . " - USAGE insert failed: " . mysqli_error($con) . "\n", FILE_APPEND);
        }

        // Create a lightweight order record so it appears in checkout history
        try {
            $orderno = mt_rand(100000000,999999999);
            $safeOrderNo = mysqli_real_escape_string($con, $orderno);
            $safeUnitType = mysqli_real_escape_string($con, $safeUnit);

            // Accept optional purpose
            $purpose = isset($_POST['purpose']) ? trim($_POST['purpose']) : '';
            // Ensure orders table has a purpose column
            $c3 = @mysqli_query($con, "SHOW COLUMNS FROM `orders` LIKE 'purpose'");
            if (!$c3 || mysqli_num_rows($c3) === 0) { @mysqli_query($con, "ALTER TABLE orders ADD COLUMN purpose TEXT DEFAULT NULL"); }
            // Build INSERT dynamically depending on which columns exist in `orders` table
            $cols = array('orderNumber','userId','addressId','totalAmount','txnType','txnNumber');
            $vals = array("'".mysqli_real_escape_string($con,$safeOrderNo)."'", intval($uid), 0, 0, "'inventory'", "''");

            $hasStatus = false;
            $hasOrderStatus = false;
            $c = @mysqli_query($con, "SHOW COLUMNS FROM `orders` LIKE 'status'");
            if ($c && mysqli_num_rows($c)) { $hasStatus = true; }
            $c2 = @mysqli_query($con, "SHOW COLUMNS FROM `orders` LIKE 'orderStatus'");
            if ($c2 && mysqli_num_rows($c2)) { $hasOrderStatus = true; }

            // Add purpose if provided
            if (strlen($purpose)) { $cols[] = 'purpose'; $vals[] = "'".mysqli_real_escape_string($con,$purpose)."'"; }

            // New inventory checkouts require admin verification. Mark as 'Pending' (if columns exist),
            // do NOT auto-complete here — admin must confirm in admin UI.
            if ($hasStatus) { $cols[] = 'status'; $vals[] = "'Pending'"; }
            if ($hasOrderStatus) { $cols[] = 'orderStatus'; $vals[] = "'Pending'"; }

            $insertOrderSql = "INSERT INTO orders (".implode(',', $cols).") VALUES (".implode(',', $vals).")";
            $insOrder = @mysqli_query($con, $insertOrderSql);
            if ($insOrder) {
                // insert into ordersdetails (single item). Some installations expect orderNumber field.
                $insDetailsSql = "INSERT INTO ordersdetails (userId, productId, quantity, orderNumber) VALUES('".intval($uid)."','".intval($pid)."','".floatval($qtyKg)."', '".mysqli_real_escape_string($con,$safeOrderNo)."')";
                @mysqli_query($con, $insDetailsSql);
            } else {
                @file_put_contents(__DIR__ . '/inventory_errors.log', date('c') . " - ORDER insert failed: " . mysqli_error($con) . " -- SQL: " . $insertOrderSql . "\n", FILE_APPEND);
            }
        } catch (Exception $e) {
            @file_put_contents(__DIR__ . '/inventory_errors.log', date('c') . " - ORDER exception: " . $e->getMessage() . "\n", FILE_APPEND);
        }

        // mark order as Complete if orderStatus column exists (some schemas use different names)
        if (isset($safeOrderNo) && $safeOrderNo!='') {
            // Do not auto-complete orders here. Admin must verify/confirm orders in admin interface.
            // (admin/new-order.php provides a Confirm button which sets status/orderStatus to 'Complete')

            // Create and populate a flat order items table (stores name + amount)
            $createFlat = "CREATE TABLE IF NOT EXISTS order_items_flat (
                id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                orderNumber VARCHAR(64),
                productId INT,
                productName VARCHAR(255),
                quantity DECIMAL(10,4),
                unit VARCHAR(10),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            @mysqli_query($con, $createFlat);

            // get product name
            $pRes = mysqli_query($con, "SELECT productName FROM products WHERE id='".intval($pid)."' LIMIT 1");
            $pname = '';
            if ($pRes && mysqli_num_rows($pRes)) {
                $prow = mysqli_fetch_assoc($pRes);
                $pname = $prow['productName'];
            }
            $pnameEsc = mysqli_real_escape_string($con, $pname);
            $insertFlat = "INSERT INTO order_items_flat (orderNumber, productId, productName, quantity, unit) VALUES('".mysqli_real_escape_string($con,$safeOrderNo)."', '".intval($pid)."', '".$pnameEsc."', '".floatval($qtyKg)."', '".mysqli_real_escape_string($con,$safeUnit)."')";
            @mysqli_query($con, $insertFlat);
        }

        // success
        echo "<script>alert('".addslashes(__('CHECKOUT_RECORDED'))."'); window.location='shop.php';</script>";
        exit;
    } else {
        $err = mysqli_error($con);
        @file_put_contents(__DIR__ . '/inventory_errors.log', date('c') . " - UPDATE failed: " . $err . "\n", FILE_APPEND);
        echo "<script>alert('".addslashes(__('ERROR_UPDATING_STOCK'))."'); window.location='shop.php';</script>";
        exit;
    }

} else {
    header('Location: shop.php');
    exit;
}

?>
