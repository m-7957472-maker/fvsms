<?php session_start();
error_reporting(0);
include_once('includes/config.php');
if(strlen($_SESSION['id'])==0)
{   header('location:logout.php');
}else{
// Code forProduct deletion from wishlist  
if(isset($_GET['del']))
{
$wid=intval($_GET['del']);
$query=mysqli_query($con,"delete from wishlist where id='$wid'");
 echo "<script>alert('Product deleted from wishlist.');</script>";
echo "<script type='text/javascript'> document.location ='my-wishlist.php'; </script>";

}

//Move the product from wishlist to cart
if($_GET['id']){
 $wid=$_GET['id'] ;
$sql="insert into cart(userID,productId,productQty) select userId,productId,'1' from wishlist where id='$wid';";
$sql.="delete from  wishlist where id='$wid'";
$result = mysqli_multi_query($con, $sql);
if ($result) {
     echo "<script>alert('Product moved into the cart');</script>";
     echo "<script type='text/javascript'> document.location ='my-cart.php'; </script>";
 }}
?>
<!DOCTYPE html>
<html lang="ms">
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="Responsive Bootstrap4 Shop Template, Created by Imran Hossain from https://imransdesign.com/">

	<!-- title -->
	<title>GHP INVENTORY || History</title>

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
						<h1><?php echo __('HISTORY'); ?></h1>
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
					<th colspan="6"><h4><?php echo __('CHECKOUT_HISTORY'); ?></h4></th>
				</tr>
			</thead>
			<thead>
				<tr>
					<th>#</th>
					<th><?php echo __('ORDER_NUMBER'); ?></th>
					<th><?php echo __('ORDER_DATE'); ?></th>
					<th><?php echo __('TRANSACTION_TYPE'); ?></th>
					<th><?php echo __('ITEMS'); ?></th>
					<th><?php echo __('ORDER_STATUS'); ?></th>
				</tr>
			</thead>
			<tbody>
<?php
$uid=$_SESSION['id'];
$ret=mysqli_query($con,"select * from orders where userId='$uid' order by id desc");
$num=mysqli_num_rows($ret);
$cnt=1;
if($num>0){
	while($row=mysqli_fetch_array($ret)){
		$onum = $row['orderNumber'];
		// get items for this order
		$itemsList = [];
		$rc = mysqli_query($con, "SELECT od.quantity, p.productName, p.id as pid FROM ordersdetails od JOIN products p ON p.id=od.productId WHERE od.orderNumber='".mysqli_real_escape_string($con,$onum)."' AND od.userId='".mysqli_real_escape_string($con,$uid)."'");
		if($rc && mysqli_num_rows($rc)>0){
			while($ir=mysqli_fetch_assoc($rc)){
				$itemsList[] = htmlentities($ir['productName']) . ' x ' . htmlentities($ir['quantity']);
			}
		}
		// fallback to flat items table if no details found (some older orders may lack ordersdetails records)
		if(empty($itemsList)){
			$rf = mysqli_query($con, "SELECT productName, quantity, unit FROM order_items_flat WHERE orderNumber='".mysqli_real_escape_string($con,$onum)."'");
			if($rf && mysqli_num_rows($rf)>0){
				while($ir=mysqli_fetch_assoc($rf)){
					$itemsList[] = htmlentities($ir['productName']) . ' x ' . htmlentities($ir['quantity']);
				}
			}
		}
?>
				<tr>
					<td><?php echo htmlentities($cnt);?></td>
					<td><?php echo htmlentities($row['orderNumber']);?></td>
					<td><?php echo htmlentities($row['orderDate']);?></td>
					<?php
					// show friendly label for some txn types
					$displayTxn = $row['txnType'];
					if ($displayTxn === 'inventory') {
						$displayTxn = __('BULK_ORDER');
					}
				?>
				<td><?php echo htmlentities($displayTxn);?></td>
					<td><?php echo $itemsList ? implode('<br/>',$itemsList) : '—';?></td>
						<?php
						$ostatus = isset($row['orderStatus']) && $row['orderStatus'] !== '' ? $row['orderStatus'] : (isset($row['status']) ? $row['status'] : '');
						if ($ostatus == '') {
							$displayStatus = __('NOT_PROCESSED_YET');
						} else {
							$statusMap = [
								'Complete' => __('STATUS_COMPLETE'),
								'Approved' => __('STATUS_APPROVED'),
								'Cancelled' => __('STATUS_CANCELLED'),
								'Packed' => __('STATUS_PACKED'),
								'Dispatched' => __('STATUS_DISPATCHED'),
								'In Transit' => __('STATUS_IN_TRANSIT'),
								'Out For Delivery' => __('STATUS_OUT_FOR_DELIVERY'),
								'New Order' => __('STATUS_NEW_ORDER'),
								'Rejected' => __('STATUS_REJECTED')
							];
							$displayStatus = isset($statusMap[$ostatus]) ? $statusMap[$ostatus] : $ostatus;
						}
						?>
						<td><?php echo htmlentities($displayStatus); ?></td>
				</tr>
<?php $cnt++; } } else { ?>
				<tr>
					<td colspan="6" style="font-size: 18px; font-weight:bold"><?php echo __('NO_CHECKOUT_HISTORY'); ?></td>
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