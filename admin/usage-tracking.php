<?php session_start();
include_once(__DIR__ . '/../includes/config.php');
if(strlen( $_SESSION["aid"])==0) {   
    header('location:logout.php');
} else {

// Create usage table if it doesn't exist
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

// Helper: get a sensible display name for a user id (tries name, username, then email)
function getUserDisplayName($con, $uid) {
    $uid = intval($uid);
    if ($uid <= 0) return '';
    // Try 'name' column
    $has = mysqli_query($con, "SHOW COLUMNS FROM users LIKE 'name'");
    if ($has && mysqli_num_rows($has)) {
        $q = mysqli_query($con, "SELECT name FROM users WHERE id = $uid LIMIT 1");
        if ($r = mysqli_fetch_assoc($q)) return $r['name'];
    }
    // Try 'username' column
    $has = mysqli_query($con, "SHOW COLUMNS FROM users LIKE 'username'");
    if ($has && mysqli_num_rows($has)) {
        $q = mysqli_query($con, "SELECT username FROM users WHERE id = $uid LIMIT 1");
        if ($r = mysqli_fetch_assoc($q)) return $r['username'];
    }
    // Try 'email' column
    $has = mysqli_query($con, "SHOW COLUMNS FROM users LIKE 'email'");
    if ($has && mysqli_num_rows($has)) {
        $q = mysqli_query($con, "SELECT email FROM users WHERE id = $uid LIMIT 1");
        if ($r = mysqli_fetch_assoc($q)) return $r['email'];
    }
    return '';
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
        <title>GHP INVENTORY MANAGEMENT SYSTEM | <?php echo __('USAGE_TRACKING'); ?></title>
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
                        <h1 class="mt-4"><?php echo __('USAGE_TRACKING'); ?></h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="dashboard.php"><?php echo __('DASHBOARD'); ?></a></li>
                            <li class="breadcrumb-item active"><?php echo __('USAGE_TRACKING'); ?></li>
                        </ol>

                        <!-- Penapis -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-filter me-1"></i>
                                <?php echo __('FILTER_USAGE'); ?>
                            </div>
                            <div class="card-body">
                                <form method="get" class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label"><?php echo __('PRODUCT'); ?></label>
                                        <select name="productId" class="form-control">
                                            <option value=""><?php echo __('ALL') . ' ' . __('PRODUCTS'); ?></option>
                                            <?php 
                                            $filterProduct = isset($_GET['productId']) ? intval($_GET['productId']) : '';
                                            $productQuery = mysqli_query($con, "SELECT id, productName FROM products ORDER BY productName ASC");
                                            while($prow = mysqli_fetch_array($productQuery)) {
                                                $selected = ($prow['id'] == $filterProduct) ? 'selected' : '';
                                                echo "<option value='" . $prow['id'] . "' $selected>" . htmlentities($prow['productName']) . "</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label"><?php echo __('TRANSACTION_TYPE'); ?></label>
                                        <select name="action" class="form-control">
                                            <option value=""><?php echo __('ALL') . ' ' . __('ACTION'); ?></option>
                                            <option value="checkout" <?php echo (isset($_GET['action']) && $_GET['action'] == 'checkout') ? 'selected' : ''; ?>><?php echo __('ACTION_CHECKOUT'); ?></option>
                                            <option value="restock" <?php echo (isset($_GET['action']) && $_GET['action'] == 'restock') ? 'selected' : ''; ?>><?php echo __('ACTION_RESTOCK'); ?></option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">&nbsp;</label>
                                        <button type="submit" class="btn btn-primary form-control"><?php echo __('FILTER'); ?></button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Usage Table -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-table me-1"></i>
                                Usage History
                            </div>
                            <div class="card-body">
                                <table class="table table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th><?php echo __('DATE_TIME'); ?></th>
                                            <th><?php echo __('PRODUCT'); ?></th>
                                            <th><?php echo __('QUANTITY'); ?></th>
                                            <th><?php echo __('UNIT'); ?></th>
                                            <th><?php echo __('ACTION'); ?></th>
                                            <th><?php echo __('USER'); ?> / <?php echo __('ADMIN'); ?></th>
                                            <th><?php echo __('NOTES'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $query = "SELECT u.id, u.usedAt, u.qty, u.unit, u.action, u.notes, p.productName, u.usedBy FROM `usage` u 
                                                JOIN products p ON u.productId = p.id WHERE 1=1";
                                        
                                        if(isset($_GET['productId']) && !empty($_GET['productId'])) {
                                            $query .= " AND u.productId = " . intval($_GET['productId']);
                                        }
                                        
                                        if(isset($_GET['action']) && !empty($_GET['action'])) {
                                            $query .= " AND u.action = '" . mysqli_real_escape_string($con, $_GET['action']) . "'";
                                        }
                                        
                                        $query .= " ORDER BY u.usedAt DESC LIMIT 200";
                                        
                                        $usageQuery = mysqli_query($con, $query);
                                        $count = 0;
                                        
                                        if(mysqli_num_rows($usageQuery) > 0) {
                                            while($row = mysqli_fetch_array($usageQuery)) {
                                                $count++;
                                                $userName = '';
                                                
                                                // Get user or admin display name (robust across different DB schemas)
                                                if($row['action'] == 'restock') {
                                                    $adminName = '';
                                                    $adminId = intval($row['usedBy']);
                                                    $admCol = mysqli_query($con, "SHOW COLUMNS FROM tbladmin LIKE 'username'");
                                                    if ($admCol && mysqli_num_rows($admCol)) {
                                                        $adminQ = mysqli_query($con, "SELECT username FROM tbladmin WHERE id = " . $adminId);
                                                        if ($adminQ && ($ar = mysqli_fetch_array($adminQ))) {
                                                            $adminName = htmlentities($ar['username']) . " (Admin)";
                                                        }
                                                    }
                                                    if ($adminName === '') $adminName = "Admin #" . $adminId;
                                                    $userName = $adminName;
                                                } else if($row['action'] == 'checkout') {
                                                    $u = getUserDisplayName($con, intval($row['usedBy']));
                                                    if ($u !== '') {
                                                        $userName = htmlentities($u);
                                                    } else {
                                                        $userName = "User #" . intval($row['usedBy']);
                                                    }
                                                }
                                                
                                                echo "<tr>";
                                                echo "<td>" . date('M d, Y H:i:s', strtotime($row['usedAt'])) . "</td>";
                                                echo "<td>" . htmlentities($row['productName']) . "</td>";
                                                // Show human-friendly quantity depending on unit
                                                if (stripos($row['unit'], 'kg') !== false || stripos($row['unit'], 'g') !== false) {
                                                    echo "<td>" . formatQuantity($row['qty']) . "</td>";
                                                } else {
                                                    echo "<td>" . intval(round($row['qty'])) . "</td>";
                                                }
                                                echo "<td>" . htmlentities($row['unit']) . "</td>";
                                                $actionKey = 'ACTION_' . strtoupper($row['action']);
                                                $actionLabel = function_exists('__') ? __($actionKey) : ucfirst($row['action']);
                                                echo "<td><span class='badge " . ($row['action'] == 'restock' ? 'bg-success' : 'bg-warning') . "'>" . htmlentities($actionLabel) . "</span></td>";
                                                echo "<td>" . $userName . "</td>";
                                                echo "<td>" . htmlentities($row['notes']) . "</td>";
                                                echo "</tr>";
                                            }
                                        } else {
                                            echo "<tr><td colspan='7' class='text-center'>" . addslashes(__('NO_USAGE_RECORDS_FOUND')) . "</td></tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Summary Statistics -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo __('TOTAL_CHECKOUTS'); ?></h5>
                                        <?php 
                                        $checkoutQuery = mysqli_query($con, "SELECT COUNT(*) as total FROM `usage` WHERE action = 'checkout'");
                                        $checkoutRow = mysqli_fetch_array($checkoutQuery);
                                        echo "<h3>" . $checkoutRow['total'] . "</h3>";
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo __('TOTAL_RESTOCKS'); ?></h5>
                                        <?php 
                                        $restockQuery = mysqli_query($con, "SELECT COUNT(*) as total FROM `usage` WHERE action = 'restock'");
                                        $restockRow = mysqli_fetch_array($restockQuery);
                                        echo "<h3>" . $restockRow['total'] . "</h3>";
                                        ?>
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
