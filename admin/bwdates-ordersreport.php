<?php session_start();
include_once(__DIR__ . '/../includes/config.php');
if(strlen( $_SESSION["aid"])==0)
{   
header('location:logout.php');
} else {

$autoRun = false;
$fdate = '';
$tdate = '';
if (isset($_GET['from']) && isset($_GET['to'])) {
    $gfrom = $_GET['from'];
    $gto = $_GET['to'];
    $df = date_create_from_format('Y-m-d', $gfrom);
    $dt = date_create_from_format('Y-m-d', $gto);
    if ($df && $dt) {
        $fdate = $gfrom;
        $tdate = $gto;
        $autoRun = true;
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
        <title>GHP INVENTORY MANAGEMENT SYSTEM | <?php echo __('BWDATES_ORDERS_REPORT'); ?></title>
      <link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" />
        <link href="css/styles.css" rel="stylesheet" />
        <script src="js/all.min.js" crossorigin="anonymous"></script>
    </head>
    <body>
   <?php include_once('includes/header.php');?>
        <div id="layoutSidenav">
   <?php include_once('includes/sidebar.php');?>
            <div id="layoutSidenav_content">
                <main>
                    <div class="container-fluid px-4">
                        <h1 class="mt-4"><?php echo __('BWDATES_ORDERS_REPORT'); ?></h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="dashboard.php"><?php echo __('DASHBOARD'); ?></a></li>
                            <li class="breadcrumb-item active"><?php echo __('BWDATES_ORDERS_REPORT'); ?></li>
                        </ol>
                        <div class="card mb-4">
                            <div class="card-body">
<form  method="post">                                
<div class="row">
<div class="col-2"><?php echo __('FROM_DATE'); ?></div>
<div class="col-4"><input type="date"  name="fromdate" class="form-control" required value="<?php echo htmlentities($fdate); ?>"></div>
</div>

<div class="row" style="margin-top:1%;">
<div class="col-2"><?php echo __('TO_DATE'); ?></div>
<div class="col-4"><input type="date"  name="todate" class="form-control" required value="<?php echo htmlentities($tdate); ?>"></div>
</div>

<div class="row" style="margin-top:1%;">
<div class="col-6" align="center"><button type="submit" name="submit" class="btn btn-primary"><?php echo __('SUBMIT'); ?></button></div>
</div>

</form>
                            </div>
                        </div>
                    </div>
<?php if (isset($_POST['submit']) || $autoRun) { 
if (!$autoRun) { $fdate = $_POST['fromdate']; $tdate = $_POST['todate']; }
?>

<div class="card-body">
<h4 align="center" style="color:blue"><?php echo sprintf(__('ORDERS_REPORT_FOR'), $fdate, $tdate); ?></h4>
<hr />
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
                        
                                    <tbody>
<?php
$fdate = mysqli_real_escape_string($con, $fdate);
$tdate = mysqli_real_escape_string($con, $tdate);
// Build a safe ORDER BY to support older schemas without created_at
$hasCreatedAt = false;
$c3 = @mysqli_query($con, "SHOW COLUMNS FROM `orders` LIKE 'created_at'");
if ($c3 && mysqli_num_rows($c3)) { $hasCreatedAt = true; }
$orderExpr = $hasCreatedAt ? "COALESCE(orders.created_at, orders.orderDate, orders.id)" : "COALESCE(orders.orderDate, orders.id)";

$sql = "SELECT orders.id,orderNumber,totalAmount,orderStatus,orderDate,users.name,users.contactno 
    FROM `orders` join users on users.id=orders.userId 
    WHERE orderDate between '" . $fdate . "' and '" . $tdate . "' 
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
                                                                                        
                                    <td><?php $ostatus=$row['orderStatus'];
                                               if($ostatus==''): ?>
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
           <?php elseif($ostatus=='Cancelled'):?>
        <span class="badge bg-danger"><?php echo __('STATUS_CANCELLED'); ?></span>
        <?php endif;?>

                                            </td>
                                            <td>
                                            <a href="order-details.php?orderid=<?php echo $row['id']?>" target="_self">
                                                <i class="fas fa-file fa-2x" title="View Order Details"></i></a></td>
                                        </tr>
                                        <?php $cnt=$cnt+1; } ?>
                                       
                                    </tbody>
                                </table>
                            </div>
<?php } ?>

                </main>
          <?php include_once('includes/footer.php');?>
            </div>
        </div>
        <script src="js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="js/scripts.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" crossorigin="anonymous"></script>
        <script src="js/datatables-simple-demo.js"></script>
    </body>
</html>
<?php } ?>
