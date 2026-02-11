<?php session_start();
error_reporting(0);
include_once(__DIR__ . '/../includes/config.php');
include_once(__DIR__ . '/../includes/lang.php');
if(strlen($_SESSION["aid"])==0)
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
        <title>GHP INVENTORY MANAGEMENT SYSTEM | <?php echo addslashes(__('PRODUCTION_STOCK_REPORT')); ?></title>
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
                        <h1 class="mt-4"><?php echo __('PRODUCTION_STOCK_REPORT'); ?></h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="dashboard.php"><?php echo __('DASHBOARD'); ?></a></li>
                            <li class="breadcrumb-item active"><?php echo __('PRODUCTION_STOCK_REPORT'); ?></li>
                        </ol>
                        <div class="card mb-4">
                            <div class="card-body">
<form  method="post">                                
<div class="row">
<div class="col-2"><?php echo __('FROM_DATE') ?? 'From Date'; ?></div>
<div class="col-4"><input type="date"  name="fromdate" class="form-control" required></div>
</div>

<div class="row" style="margin-top:1%;">
<div class="col-2"><?php echo __('TO_DATE') ?? 'To Date'; ?></div>
<div class="col-4"><input type="date"  name="todate" class="form-control" required></div>
</div>

<div class="row" style="margin-top:1%;">
<div class="col-6" align="center"><button type="submit" name="submit" class="btn btn-primary"><?php echo __('SUBMIT') ?? 'Submit'; ?></button></div>
</div>

</form>
                            </div>
                        </div>
                    </div>
<?php if (isset($_POST['submit'])) { 
$fdate=mysqli_real_escape_string($con, $_POST['fromdate']);
$tdate=mysqli_real_escape_string($con, $_POST['todate']);
?>

<div class="card-body">
<h4 align="center" style="color:blue"><?php echo __('BWDATES_PRODUCTION_REPORT'); ?> <?php echo htmlentities($fdate);?> <?php echo __('TO_WORD'); ?> <?php echo htmlentities($tdate);?></h4>
<hr />
                                <table class="table table-bordered" border="1">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th><?php echo __('DATE_TIME'); ?></th>
                                            <th><?php echo __('ACTION'); ?></th>
                                            <th><?php echo __('PRODUCT'); ?></th>
                                            <th><?php echo __('QUANTITY'); ?></th>
                                            <th><?php echo __('UNIT'); ?></th>
                                            <th><?php echo __('PERFORMED_BY'); ?></th>
                                            <th><?php echo __('NOTES'); ?></th>
                                        </tr>
                                    </thead>
    
                                    <tbody>
<?php 
$query = mysqli_query($con, "SELECT u.id,u.usedAt,u.qty,u.unit,u.notes,u.action,p.productName, COALESCE(us.name, us.username, us.email, ta.username, CONCAT('ID:',u.usedBy)) AS performedBy
    FROM `usage` u
    LEFT JOIN products p ON u.productId = p.id
    LEFT JOIN users us ON us.id = u.usedBy
    LEFT JOIN tbladmin ta ON ta.id = u.usedBy
    WHERE u.action IN ('checkout','restock','production') AND u.usedAt BETWEEN '".$fdate." 00:00:00' AND '".$tdate." 23:59:59' ORDER BY u.usedAt DESC");
$cnt=1;
$total_by_action = [];
while($row=mysqli_fetch_array($query))
{
    $action = $row['action'];
    $qty = floatval($row['qty']);
    $total_by_action[$action] = ($total_by_action[$action] ?? 0) + $qty;
?>  

                                <tr>
                                            <td><?php echo htmlentities($cnt);?></td>
                                            <td><?php echo htmlentities($row['usedAt']);?></td>
                                            <td><?php echo htmlentities(ucfirst($action));?></td>
                                            <td><?php echo htmlentities($row['productName']);?></td>
                                            <td><?php 
                                            $displayQty = '';
                                            if (stripos($row['unit'], 'kg') !== false || stripos($row['unit'], 'g') !== false) {
                                                $qtyKg = (stripos($row['unit'], 'g') !== false) ? floatval($row['qty']) / 1000.0 : floatval($row['qty']);
                                                $displayQty = formatQuantity($qtyKg, true);
                                            } else {
                                                $displayQty = intval(round($row['qty']));
                                            }
                                            echo htmlentities($displayQty);
                                            ?></td>
                                            <td><?php echo htmlentities($row['unit']);?></td>
                                            <td><?php echo htmlentities($row['performedBy']);?></td>
                                            <td><?php echo htmlentities($row['notes']);?></td>
                                        </tr>
                                        <?php
                                         $cnt=$cnt+1; } ?>
                                       

                                    </tbody>
                                </table>

                                <h5><?php echo __('SUMMARY_TOTALS'); ?></h5>
                                <ul>
                                <?php foreach($total_by_action as $act=>$sum) { echo '<li>' . htmlentities(ucfirst($act)) . ': ' . $sum . '</li>'; } ?>
                                </ul>
                            </div>
<?php } ?>

                </main>
          <?php include_once('includes/footer.php');?>
            </div>
        </div>
        <script src="js/bootstrap.bundle.min.js"></script>
        <script src="js/scripts.js"></script>
         <script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" crossorigin="anonymous"></script>
        <script src="js/datatables-simple-demo.js"></script>
    </body>
</html>
<?php } ?>