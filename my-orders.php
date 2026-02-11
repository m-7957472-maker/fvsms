<?php session_start();
include_once('includes/config.php');
include_once('includes/lang.php');
if(strlen($_SESSION['id'])==0)
{   header('location:logout.php');
}else{
?>
<!DOCTYPE html>
<html lang="ms">
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="Responsive Bootstrap4 Shop Template, Created by Imran Hossain from https://imransdesign.com/">

	<!-- title -->
	<title>GHP INVENTORY</title>

	<!-- favicon -->
	<link rel="shortcut icon" type="image/png" href="assets/img/favicon.png">
	<!-- google font -->
	<link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,700" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css?family=Poppins:400,700&display=swap" rel="stylesheet">
	<!-- fontawesome -->
	<link rel="stylesheet" href="assets/css/all.min.css">
	<!-- bootstrap -->
	<link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
	<!-- owl carousel -->
	<link rel="stylesheet" href="assets/css/owl.carousel.css">
	<!-- magnific popup -->
	<link rel="stylesheet" href="assets/css/magnific-popup.css">
	<!-- animate css -->
	<link rel="stylesheet" href="assets/css/animate.css">
	<!-- mean menu css -->
	<link rel="stylesheet" href="assets/css/meanmenu.min.css">
	<!-- main style -->
	<link rel="stylesheet" href="assets/css/main.css">
	<!-- responsive -->
	<link rel="stylesheet" href="assets/css/responsive.css">

</head>
<body>
	
	<?php include_once('includes/header.php'); ?>
	


	
	<!-- breadcrumb-section -->
	<div class="breadcrumb-section breadcrumb-bg">
		<div class="container">
			<div class="row">
				<div class="col-lg-8 offset-lg-2 text-center">
					<div class="breadcrumb-text">
						<p><?php echo __('FAST_AND_RELIABLE'); ?></p>
						<h1>GHP INVENTORY</h1>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- end breadcrumb section -->

	<!-- cart -->
	<div class="cart-section mt-150 mb-150">
		<div class="container">
			<div class="row">
				<div class="col-lg-12 col-md-12">
					<div class="cart-table-wrap">
					<div class="table-responsive">
        <table class="table">
				<thead>
				<tr>
					<th colspan="4"><h4><?php echo __('CHECKOUT_DETAILS'); ?></h4></th>
				</tr>
			</thead>
            <tr>
                <thead>
                    <th><?php echo __('ID'); ?></th>
					<th><?php echo __('ORDER_NUMBER'); ?></th>
					<th><?php echo __('ORDER_DATE'); ?></th>
					<th><?php echo __('TRANSACTION_TYPE'); ?></th>
					<th><?php echo __('ITEMS'); ?></th>
					<th><?php echo __('ORDER_STATUS'); ?></th>
					<th><?php echo __('ACTION'); ?></th>
                </thead>
            </tr>
            <tbody>
<?php
$uid=$_SESSION['id'];
// Prefer ordering by created_at (if available), fallback to orderDate, otherwise use id
$orderBy = 'id DESC';
$c1 = @mysqli_query($con, "SHOW COLUMNS FROM `orders` LIKE 'created_at'");
if ($c1 && mysqli_num_rows($c1) > 0) {
    $orderBy = 'created_at DESC';
} else {
    $c2 = @mysqli_query($con, "SHOW COLUMNS FROM `orders` LIKE 'orderDate'");
    if ($c2 && mysqli_num_rows($c2) > 0) {
        $orderBy = 'orderDate DESC';
    }
}
$ret = mysqli_query($con, "SELECT * FROM orders WHERE userId='".intval($uid)."' ORDER BY $orderBy");
$num=mysqli_num_rows($ret);
$cnt=1;
    if($num>0)
    {
while ($row=mysqli_fetch_array($ret)) {

?>

                <tr>
                    <td><?php echo htmlentities($cnt);?></td>
                    <td><?php echo htmlentities($row['orderNumber']);?></td>
                    <td><?php echo htmlentities($row['orderDate']);?></td>
					<td><?php echo htmlentities($row['txnType']);?></td>
					<td><?php
						// show list of ordered items for this order (name x qty)
						$onum = $row['orderNumber'];
						$itemsHtml = [];
						$rc = mysqli_query($con, "SELECT od.quantity, p.productName, p.id as pid FROM ordersdetails od JOIN products p ON p.id=od.productId WHERE od.orderNumber='".mysqli_real_escape_string($con,$onum)."' AND od.userId='".mysqli_real_escape_string($con,$uid)."'");
						if ($rc && mysqli_num_rows($rc) > 0) {
							while ($ir = mysqli_fetch_assoc($rc)) {
								$pname = htmlentities($ir['productName']);
								$qty = htmlentities($ir['quantity']);
								$pid = intval($ir['pid']);
								$itemsHtml[] = "<a href=\"product-details.php?pid={$pid}\">{$pname}</a> x {$qty}";
							}
						}					// fallback to flat items table if no details found
					if (empty($itemsHtml)) {
						$rf = mysqli_query($con, "SELECT productName, quantity, unit FROM order_items_flat WHERE orderNumber='".mysqli_real_escape_string($con,$onum)."'");
						if ($rf && mysqli_num_rows($rf) > 0) {
							while ($ir = mysqli_fetch_assoc($rf)) {
								$pname = htmlentities($ir['productName']);
								$qty = htmlentities($ir['quantity']);
								$itemsHtml[] = "{$pname} x {$qty}";
							}
						}
					}						echo $itemsHtml ? implode('<br/>', $itemsHtml) : '—';
					?></td>
					<td><?php $ostatus = isset($row['orderStatus']) && $row['orderStatus'] !== '' ? $row['orderStatus'] : (isset($row['status']) ? $row['status'] : '');
// map common statuses to Malay labels
$status_map = [
    'New Order' => __('STATUS_NEW_ORDER'),
    'Packed' => __('STATUS_PACKED'),
    'Dispatched' => __('STATUS_DISPATCHED'),
    'In Transit' => __('STATUS_IN_TRANSIT'),
    'Out For Delivery' => __('STATUS_OUT_FOR_DELIVERY'),
    'Delivered' => __('STATUS_DELIVERED'),
    'Cancelled' => __('STATUS_CANCELLED'),
    'Pending' => __('NOT_PROCESSED_YET'),
    'Complete' => __('STATUS_COMPLETE'),
    'Approved' => __('STATUS_APPROVED'),
    'Rejected' => __('STATUS_REJECTED')
];
if (empty($ostatus)) { echo __('NOT_PROCESSED_YET'); } else if (isset($status_map[$ostatus])) { echo $status_map[$ostatus]; } else { echo htmlentities($ostatus); }
?><br />
                    </td>
                    <td><a href="order-details.php?onumber=<?php echo htmlentities($row['orderNumber']);?>" class="btn-upper btn btn-primary"><?php echo __('DETAILS'); ?></a></td>
                
                </tr>
            
                <?php $cnt++;}  } else{ ?>
                <tr>
                    <td colspan="7" class="text-center" style="font-size:18px; font-weight:bold;">
                        <?php echo __('NO_ORDERS_YET'); ?> &nbsp;
                        <a href="shop.php" class="btn-upper btn btn-warning"><?php echo __('CONTINUE_SHOPPING'); ?></a>
                    </td>

                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
					</div>
				</div>

				
			</div>
		</div>
	</div>
	<!-- end cart -->


	
	
	<?php include_once('includes/footer.php'); ?>
	
	<!-- jquery -->
	<script src="assets/js/jquery-1.11.3.min.js"></script>
	<!-- bootstrap -->
	<script src="assets/bootstrap/js/bootstrap.min.js"></script>
	<!-- count down -->
	<script src="assets/js/jquery.countdown.js"></script>
	<!-- isotope -->
	<script src="assets/js/jquery.isotope-3.0.6.min.js"></script>
	<!-- waypoints -->
	<script src="assets/js/waypoints.js"></script>
	<!-- owl carousel -->
	<script src="assets/js/owl.carousel.min.js"></script>
	<!-- magnific popup -->
	<script src="assets/js/jquery.magnific-popup.min.js"></script>
	<!-- mean menu -->
	<script src="assets/js/jquery.meanmenu.min.js"></script>
	<!-- sticker js -->
	<script src="assets/js/sticker.js"></script>
	<!-- main js -->
	<script src="assets/js/main.js"></script>

</body>
</html> <?php } ?>