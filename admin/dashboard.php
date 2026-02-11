<?php session_start();
include_once(__DIR__ . '/../includes/config.php');
include_once(__DIR__ . '/../includes/lang.php');
if(strlen( $_SESSION["aid"])==0)
{   
header('location:logout.php');
} else { 
// Dashboard Counts
$firstDay = date('Y-m-01');
$lastDay = date('Y-m-t');
$ret=mysqli_query($con,"select count(id) as totalorders,
count(if((orderStatus='' || orderStatus is null),0,null)) as neworders,
count(if(orderStatus='Cancelled', 0,null)) as cancelledorders
from orders;");
$results=mysqli_fetch_array($ret);
$torders=$results['totalorders'];
$norders=$results['neworders'];
$cancelledorders=$results['cancelledorders'];

// Monthly orders (current month)
$mres = @mysqli_query($con, "SELECT COUNT(id) as monthly_orders FROM orders WHERE orderDate BETWEEN '".mysqli_real_escape_string($con,$firstDay)."' AND '".mysqli_real_escape_string($con,$lastDay)."'");
$monthlyOrders = 0;
if ($mres && $mr = mysqli_fetch_assoc($mres)) { $monthlyOrders = intval($mr['monthly_orders']); }

// Categories count
$cr = @mysqli_query($con, "SELECT COUNT(id) AS cats FROM category");
$categories = 0;
if ($cr && $crr = mysqli_fetch_assoc($cr)) { $categories = intval($crr['cats']); }
//COde for Registered users
$ret1=mysqli_query($con,"select count(id) as totalusers from users;");
$results1=mysqli_fetch_array($ret1);
$tregusers=$results1['totalusers'];

//COde for listed products
$ret2=mysqli_query($con,"select count(id) as lsitedproducts from products;");
$results2=mysqli_fetch_array($ret2);
$lsitedproducts=$results2['lsitedproducts'];
?>

<!DOCTYPE html>
<html lang="ms">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>GHP INVENTORY MANAGEMENT SYSTEM | <?php echo __('DASHBOARD_TITLE'); ?></title>
        <link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" />
        <link href="css/styles.css" rel="stylesheet" />
        <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js" crossorigin="anonymous"></script>
    </head>
    <body class="sb-nav-fixed">
   <?php include_once('includes/header.php');?>


        <div id="layoutSidenav">
          <?php include_once('includes/sidebar.php');?>
            <div id="layoutSidenav_content">
                <main>
                    <div class="container-fluid px-4">
                        <h1 class="mt-4"><?php echo __('DASHBOARD'); ?></h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item active"><?php echo __('DASHBOARD'); ?></li>
                        </ol>
           <div class="row">
                            <div class="col-lg-6 col-xl-3 mb-4">
                                <div class="card bg-primary text-white h-100">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="me-3">
                                                <div class="text-white-75 small"><?php echo __('TOTAL_ORDERS'); ?></div>
                                                <div class="text-lg fw-bold"><?php echo $torders; ?></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-footer d-flex align-items-center justify-content-between small">
                                        <a class="text-white stretched-link" href="all-orders.php"><?php echo __('VIEW_DETAILS'); ?></a>
                              
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6 col-xl-3 mb-4">
                                <div class="card bg-danger text-white h-100">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="me-3">
                                                <div class="text-white-75 small"><?php echo __('NEW_ORDERS'); ?></div>
                                                <div class="text-lg fw-bold"><?php echo $norders; ?></div>
                                            </div>
                                 
                                        </div>
                                    </div>
                                    <div class="card-footer d-flex align-items-center justify-content-between small">
                                        <a class="text-white stretched-link" href="new-order.php"><?php echo __('VIEW_DETAILS'); ?></a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6 col-xl-3 mb-4">
                                <div class="card bg-secondary text-white h-100">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="me-3">
                                                <div class="text-white-75 small">Categories</div>
                                                <div class="text-lg fw-bold"><?php echo $categories; ?></div>
                                            </div>
                                   
                                        </div>
                                    </div>
                                    <div class="card-footer d-flex align-items-center justify-content-between small">
                                        <a class="text-white stretched-link" href="manage-categories.php">View Categories</a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6 col-xl-3 mb-4">
                                <div class="card bg-danger text-white h-100">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="me-3">
                                                <div class="text-white-75 small"><?php echo __('CANCELLED_ORDERS'); ?></div>
                                                <div class="text-lg fw-bold"><?php echo $cancelledorders; ?></div>
                                            </div>
                                 
                                        </div>
                                    </div>
                                    <div class="card-footer d-flex align-items-center justify-content-between small">
                                        <a class="text-white stretched-link" href="cancelled-orders.php"><?php echo __('VIEW_DETAILS'); ?></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-------------->
                             <div class="row">
                                 <div class="col-lg-6 col-xl-3 mb-4">
                                <div class="card bg-black text-white h-100">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="me-3">
                                                <div class="text-white-75 small"><?php echo __('REGISTERED_USERS'); ?></div>
                                                <div class="text-lg fw-bold"><?php echo $tregusers; ?></div>
                                            </div>
    
                                        </div>
                                    </div>
                                    <div class="card-footer d-flex align-items-center justify-content-between small">
                                        <a class="text-white stretched-link" href="registered-users.php"><?php echo __('VIEW_REQUESTS'); ?></a>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-6 col-xl-3 mb-4">
                                <div class="card bg-info text-white h-100">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="me-3">
                                                <div class="text-white-75 small"><?php echo __('MONTHLY_ORDERS'); ?></div>
                                                <div class="text-lg fw-bold"><?php echo $monthlyOrders; ?></div>
                                            </div>
    
                                        </div>
                                    </div>
                                    <div class="card-footer d-flex align-items-center justify-content-between small">
                                        <a class="text-white stretched-link" href="bwdates-ordersreport.php?from=<?php echo $firstDay;?>&to=<?php echo $lastDay;?>"><?php echo __('VIEW_REPORT'); ?></a>
                                    </div>
                                </div>
                            </div>

          <div class="col-lg-6 col-xl-3 mb-4">
                                <div class="card bg-secondary  text-white h-100">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="me-3">
                                                <div class="text-white-75 small"><?php echo __('LISTED_PRODUCTS'); ?></div>
                                                <div class="text-lg fw-bold"><?php echo $lsitedproducts; ?></div>
                                            </div>
    
                                        </div>
                                    </div>
                                    <div class="card-footer d-flex align-items-center justify-content-between small">
                                        <a class="text-white stretched-link" href="manage-products.php"><?php echo __('VIEW_REQUESTS'); ?></a>
                                    </div>
                                </div>
                            </div>

                        </div>



               
                    </div>
                </main>
   <?php include_once('includes/footer.php');?>
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="js/scripts.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
        <script src="assets/demo/chart-area-demo.js"></script>
        <script src="assets/demo/chart-bar-demo.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" crossorigin="anonymous"></script>
        <script src="js/datatables-simple-demo.js"></script>
    </body>
</html>
<?php } ?>
