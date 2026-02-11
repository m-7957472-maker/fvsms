<?php session_start();
include_once(__DIR__ . '/../includes/config.php');
if(strlen( $_SESSION["aid"])==0)
{   
header('location:logout.php');
} else {



?>
<!DOCTYPE html>
<html lang="ms">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title><?php echo __('SITE_TITLE') . ' | ' . __('MANAGE_CANCELLED_ORDERS'); ?></title>
        <link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" />
        <link href="css/styles.css" rel="stylesheet" />
        <script src="js/all.min.js" crossorigin="anonymous"></script>
    </head>
    <body class="sb-nav-fixed">
 <?php include_once('includes/header.php');?>
        <div id="layoutSidenav">
       <?php include_once('includes/sidebar.php');?>
            <div id="layoutSidenav_content">
                <main>
                    <div class="container-fluid px-4">
                        <h1 class="mt-4"><?php echo __('MANAGE_CANCELLED_ORDERS'); ?></h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="dashboard.php"><?php echo __('DASHBOARD'); ?></a></li>
                            <li class="breadcrumb-item active"><?php echo __('MANAGE_CANCELLED_ORDERS'); ?></li>
                        </ol>
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-table me-1"></i>
                               <?php echo __('ALL_ORDER_DETAILS'); ?>
                            </div>
                            <div class="card-body">
                                <table id="datatablesSimple">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th><?php echo __('ORDER_NUMBER'); ?></th>
                                            <th><?php echo __('ORDER_BY'); ?></th>
                                            <th><?php echo __('ORDER_AMOUNT'); ?></th>
                                            <th><?php echo __('ORDER_DATE'); ?></th>
                                            <th><?php echo __('ORDER_STATUS'); ?></th>
                                            <th><?php echo __('ACTION'); ?></th>
                                        </tr>
                                    </thead>
                                    <tfoot>
                                        <tr>
                                            <th>#</th>
                                            <th><?php echo __('ORDER_NUMBER'); ?></th>
                                            <th><?php echo __('ORDER_BY'); ?></th>
                                            <th><?php echo __('ORDER_AMOUNT'); ?></th>
                                            <th><?php echo __('ORDER_DATE'); ?></th>
                                            <th><?php echo __('ORDER_STATUS'); ?></th>
                                            <th><?php echo __('ACTION'); ?></th>
                                        </tr>
                                    </tfoot>
                                    <tbody>
<?php
// Build a safe ORDER BY to support older schemas without created_at
$hasCreatedAt = false;
$c3 = @mysqli_query($con, "SHOW COLUMNS FROM `orders` LIKE 'created_at'");
if ($c3 && mysqli_num_rows($c3)) { $hasCreatedAt = true; }
$orderExpr = $hasCreatedAt ? "COALESCE(orders.created_at, orders.orderDate, orders.id)" : "COALESCE(orders.orderDate, orders.id)";

$sql = "SELECT orders.id,orderNumber,totalAmount,orderStatus,orderDate,users.name,users.contactno 
    FROM `orders` join users on users.id=orders.userId 
    WHERE (orderStatus='Cancelled') 
    ORDER BY " . $orderExpr . " DESC";
$query = mysqli_query($con, $sql);
$cnt=1;
while($row=mysqli_fetch_array($query))
{
?>  

                                <tr>
                                            <td><?php echo htmlentities($cnt);?></td>
                                            <td><?php echo htmlentities($row['orderNumber']);?></td>
                                            <td><?php echo htmlentities($row['name']);?></td>
                                            <td> <?php echo htmlentities($row['totalAmount']);?></td>
                                            <td><?php echo htmlentities($row['orderDate']);?></td>
                                            <td>     <span class="badge bg-danger"><?php echo __('STATUS_CANCELLED'); ?></span></td>
                                            <td>
                                            <a href="order-details.php?orderid=<?php echo $row['id']?>" target="_self">
                                                <i class="fas fa-file fa-2x" title="View Order Details"></i></a></td>
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
    </body>
</html>
<?php } ?>
