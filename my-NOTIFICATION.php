<?php session_start();
error_reporting(0);
include_once('includes/config.php');
if(strlen($_SESSION['id'])==0)
{   header('location:logout.php');
}else{
// Code forProduct deletion from NOTIFICATION
if(isset($_GET['del']))
{
$wid=intval($_GET['del']);
$query=mysqli_query($con,"delete from NOTIFICATION where id='$wid'");
 echo "<script>alert('Product deleted from NOTIFICATION.');</script>";
echo "<script type='text/javascript'> document.location ='my-NOTIFICATION.php'; </script>";

}

//Move the product from NOTIFICATION to cart
if($_GET['id']){
 $wid=$_GET['id'] ;
$sql="insert into cart(userID,productId,productQty) select userId,productId,'1' from NOTIFICATION where id='$wid';";
$sql.="delete from  NOTIFICATION where id='$wid'";
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
	<title>GHP INVENTORY MANAGEMENT SYSTEM || <?php echo __('NOTIFICATIONS_LOG'); ?></title>

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
						<p>GHP INVENTORY</p>
						<h1>NOTIFICATION</h1>
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
						<table class="table">
            <thead>
                <tr>
                    <th colspan="4"><h4><?php echo __('NOTIFICATIONS_LOG'); ?></h4></th>
                </tr>
            </thead>
            <tbody>
<?php
$uid=$_SESSION['id'];
$ret=mysqli_query($con,"SELECT n.id as wid, n.productId as pid, n.qty, n.unit, n.action, n.meta, n.created_at, p.productName as pname, p.productImage1 as pimage FROM notification n JOIN products p ON p.id = n.productId WHERE n.userId='$uid' ORDER BY n.created_at DESC");
$num=mysqli_num_rows($ret);
    if($num>0)
    {
while ($row=mysqli_fetch_array($ret)) {

?>

				<tr>
					<td class="col-md-2"><img src="admin/productimages/<?php echo htmlentities($row['pimage']);?>" alt="<?php echo htmlentities($row['pname']);?>" width="100" height="100"></td>
					<td class="col-md-5">
					<div class="product-name"><a href="product-details.php?pid=<?php echo htmlentities($pd=$row['pid']);?>"><?php echo htmlentities($row['pname']);?></a>
					<?php
					// show a small type badge based on action
					$action = isset($row['action']) ? $row['action'] : '';
					if ($action === 'checkout_single') {
						echo ' <span class="badge bg-info" style="font-size:12px;">'.htmlentities(__('NOTIF_CHECKOUT_SINGLE')).'</span>';
					} elseif ($action === 'checkout_cart') {
						echo ' <span class="badge bg-warning" style="font-size:12px;">'.htmlentities(__('NOTIF_CHECKOUT_CART')).'</span>';
					}
					?>
					</div>
					<div style="margin-top:6px; color:#555; font-size:14px;">Quantity: <strong><?php 
					$qty = htmlentities($row['qty']);
					$unit = trim($row['unit']);
					$pname = trim($row['pname']);
					if (strtolower($unit) === 'unit') {
						$lower = mb_strtolower($pname);
						if (stripos($lower, 'mesin') === false) {
							echo $qty . ' unit mesin ' . htmlentities($lower);
						} else {
							echo $qty . ' unit ' . htmlentities($lower);
						}
					} else {
						echo $qty . ' ' . htmlentities($unit);
					}
				?></strong></div>
					<div style="margin-top:6px; color:#777; font-size:13px;"><?php echo __('CREATED'); ?>: <?php echo htmlentities($row['created_at']); ?></div>
					<?php
					// show purpose if present in meta
					if (!empty($row['meta'])) {
						$md = json_decode($row['meta'], true);
						if (is_array($md) && !empty($md['purpose'])) {
							echo '<div style="color:#666; font-size:12px; margin-top:4px;">'.__('PURPOSE').': ' . htmlentities($md['purpose']) . '</div>';
						}
					}
					?>
					</div>
					</td>
					<td class="col-md-3" style="vertical-align: middle;">
						<a href="my-NOTIFICATION.php?action=movetocart&id=<?php echo $row['wid']; ?>" class="btn-upper btn btn-primary"><?php echo __('MOVE_TO_CART'); ?></a>
					</td>
					<td class="col-md-2" style="vertical-align: middle;">
						<a href="my-NOTIFICATION.php?del=<?php echo htmlentities($row['wid']);?>" onClick="return confirm('<?php echo __('CONFIRM_DELETE_ITEM'); ?>')" class="btn-upper btn btn-danger"><?php echo __('DELETE'); ?></a>
					</td>
				</tr>
                <?php } } else{ ?>
                <tr>
                    <td style="font-size: 18px; font-weight:bold "><?php echo __('NO_NOTIFICATIONS'); ?></td>

                </tr>
                <?php } ?>
            </tbody>
        </table>
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