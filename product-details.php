<?php session_start();
include_once('includes/config.php');
error_reporting(0);

//Code for Wish List
$pid=intval($_GET['pid']);
if(isset($_POST['wishlist'])){
if(strlen($_SESSION['id'])==0)
{   
echo "<script>alert('".addslashes(__('LOGIN_REQUIRED_WISHLIST'))."');</script>";
} else{
$userid=$_SESSION['id'];    
$query=mysqli_query($con,"select id from wishlist where userId='$userid' and productId='$pid'");
$count=mysqli_num_rows($query);
if($count==0){
mysqli_query($con,"insert into wishlist(userId,productId) values('$userid','$pid')");
echo "<script>alert('".addslashes(__('PRODUCT_ADDED_WISHLIST'))."');</script>";
  echo "<script type='text/javascript'> document.location ='my-wishlist.php'; </script>";
} else { 
echo "<script>alert('".addslashes(__('PRODUCT_ALREADY_IN_WISHLIST'))."');</script>";
}
}}

//Code for Adding Product in to Cart
if(isset($_POST['addtocart'])){
    // Debug: log incoming add-to-cart attempts to help diagnose client issues
    $dbg = array();
    $dbg[] = "time=".date('c');
    $dbg[] = "session_id=".session_id();
    $dbg[] = "session_uid=" . (isset($_SESSION['id']) ? $_SESSION['id'] : 'NONE');
    $dbg[] = "post_keys=" . implode(',', array_keys($_POST));
    $dbg[] = 'post_raw=' . @file_get_contents('php://input');
    $dbg[] = 'x_requested_with=' . (isset($_SERVER['HTTP_X_REQUESTED_WITH']) ? $_SERVER['HTTP_X_REQUESTED_WITH'] : 'NONE');
    $dbg[] = 'referer=' . (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'NONE');
    $dbg[] = 'user_agent=' . (isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'NONE');
    @file_put_contents(__DIR__ . '/scripts/addtocart_debug.log', implode("\n", $dbg) . "\n----\n", FILE_APPEND);

    $isAjax = isset($_POST['ajax_addtocart']);

// Extra debug: capture variables and intent before DB operations
$dbg2 = array();
$dbg2[] = date('c') . ' - HANDLER_REACHED';
$dbg2[] = 'pid=' . $pid;
$dbg2[] = 'SESSION_ID=' . session_id();
$dbg2[] = 'session_uid=' . (isset($_SESSION['id']) ? $_SESSION['id'] : 'NONE');
$dbg2[] = 'raw_post_keys=' . implode(',', array_keys($_POST));
$dbg2[] = 'raw_post=' . @file_get_contents('php://input');
@file_put_contents(__DIR__ . '/scripts/addtocart_debug.log', implode("\n", $dbg2) . "\n", FILE_APPEND);

if(strlen($_SESSION['id'])==0)
{   
    $msg = __('LOGIN_REQUIRED_ADD_TO_CART');
    @file_put_contents(__DIR__ . '/scripts/addtocart_debug.log', date('c') . " - LOGIN_REQUIRED - session missing (" . session_id() . ")\n", FILE_APPEND);
    if ($isAjax) { header('Content-Type: application/json'); echo json_encode(array('success'=>false,'message'=>$msg)); exit; }
    echo "<script>alert('".addslashes($msg)."');</script>";
} else{
$userid=$_SESSION['id']; 
$pqty = isset($_POST['inputQuantity']) ? $_POST['inputQuantity'] : (isset($_POST['qty']) ? $_POST['qty'] : 0);
$pqty = floatval($pqty);
if ($pqty <= 0) { $pqty = 1; }
@file_put_contents(__DIR__ . '/scripts/addtocart_debug.log', date('c') . " - COMPUTED: userid={$userid} pid={$pid} pqty={$pqty}\n", FILE_APPEND);

$selectSql = "select id,productQty from cart where userId='".$userid."' and productId='".$pid."'";
$query=mysqli_query($con,$selectSql);
if ($query === false) {
    @file_put_contents(__DIR__ . '/scripts/addtocart_debug.log', date('c') . " - SELECT failed: " . mysqli_error($con) . " -- SQL: $selectSql\n", FILE_APPEND);
    $count = 0;
} else {
    $count=mysqli_num_rows($query);
    @file_put_contents(__DIR__ . '/scripts/addtocart_debug.log', date('c') . " - SELECT ok: rows=$count\n", FILE_APPEND);
}
if($count==0){
    $insertSql = "insert into cart(userId,productId,productQty) values('".$userid."','".$pid."','".$pqty."')";
    $res = mysqli_query($con,$insertSql);
    if (!$res) {
        @file_put_contents(__DIR__ . '/scripts/addtocart_debug.log', date('c') . " - INSERT failed: " . mysqli_error($con) . " -- SQL: $insertSql\n", FILE_APPEND);
        $msg = __('SOMETHING_WENT_WRONG');
        if ($isAjax) { header('Content-Type: application/json'); echo json_encode(array('success'=>false,'message'=>$msg)); exit; }
        echo "<script>alert('".addslashes($msg)."');</script>";
    } else {
        @file_put_contents(__DIR__ . '/scripts/addtocart_debug.log', date('c') . " - INSERT ok: user={$userid} pid={$pid} qty={$pqty}\n", FILE_APPEND);
        $msg = __('PRODUCT_ADDED_IN_CART');
        if ($isAjax) { header('Content-Type: application/json'); echo json_encode(array('success'=>true,'message'=>$msg,'redirect'=>'my-cart.php')); exit; }
        echo "<script>alert('".addslashes($msg)."');</script>";
        echo "<script type='text/javascript'> document.location ='my-cart.php'; </script>";
    }
} else { 
    $row=mysqli_fetch_array($query);
    $currentpqty=$row['productQty'];
    $productqty=$pqty+$currentpqty;
    @file_put_contents(__DIR__ . '/scripts/addtocart_debug.log', date('c') . " - ABOUT TO UPDATE: current={$currentpqty} new={$productqty}\n", FILE_APPEND);
    $updateSql = "update cart set productQty='".$productqty."' where userId='".$userid."' and productId='".$pid."'";
    $res2 = mysqli_query($con,$updateSql);
    if (!$res2) {
        @file_put_contents(__DIR__ . '/scripts/addtocart_debug.log', date('c') . " - UPDATE failed: " . mysqli_error($con) . " -- SQL: $updateSql\n", FILE_APPEND);
        $msg = __('SOMETHING_WENT_WRONG');
        if ($isAjax) { header('Content-Type: application/json'); echo json_encode(array('success'=>false,'message'=>$msg)); exit; }
        echo "<script>alert('".addslashes($msg)."');</script>";
    } else {
        @file_put_contents(__DIR__ . '/scripts/addtocart_debug.log', date('c') . " - UPDATE ok: user={$userid} pid={$pid} qty={$productqty}\n", FILE_APPEND);
        $msg = __('PRODUCT_ADDED_IN_CART');
        if ($isAjax) { header('Content-Type: application/json'); echo json_encode(array('success'=>true,'message'=>$msg,'redirect'=>'my-cart.php')); exit; }
        echo "<script>alert('Product aaded in cart');</script>";
        echo "<script type='text/javascript'> document.location ='my-cart.php'; </script>";
    }
}
}
}
?>
<!DOCTYPE html>
<html lang="ms">
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="Responsive Bootstrap4 Shop Template, Created by Imran Hossain from https://imransdesign.com/">

	<!-- title -->
	<title>Single Product</title>

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
	
	<?php include_once('includes/header.php');?>
	
	<!-- breadcrumb-section -->
	<div class="breadcrumb-section breadcrumb-bg">
		<div class="container">
			<div class="row">
				<div class="col-lg-8 offset-lg-2 text-center">
					<div class="breadcrumb-text">
						<h1> Product Details</h1>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- end breadcrumb section -->

	<!-- single product -->
<?php
$pid=intval($_GET['pid']);                

                $query=mysqli_query($con,"select products.id as pid,products.productImage1,products.productName,products.productDescription,products.Quantity,products.productPriceBeforeDiscount,products.productPrice,productAvailability,category.categoryName,subcategory.subcategoryName as subcatname,subcategory.id as subid,category.id as catid from products join subcategory on products.subCategory=subcategory.id  join category on products.category=category.id where  products.id='$pid'");
                $cnt=1;
                while($row = mysqli_fetch_array($query)) {
                ?>
	<div class="single-product mt-150 mb-150">
		<div class="container">
			<div class="row">

				<div class="col-md-5">
					<div class="single-product-img">
						<img src="admin/productimages/<?php echo htmlentities($row['productImage1']);?>" alt="">
					</div>
						<?php $isEquipment = stripos($row['categoryName'], 'Peralatan') !== false || stripos($row['subcatname'] ?? '', 'Peralatan') !== false || stripos($row['subcatname'] ?? '', 'Mesin') !== false; $displayQty = getProductDisplayQty($row); if ($row['Quantity'] > 0):
								$qtyVal = floatval($row['Quantity']);
								$s = number_format($qtyVal, 3, '.', '');
								$s = rtrim(rtrim($s, '0'), '.');
							?>
								<p style="margin-top:12px; text-align: left; font-weight:600; color:#333; font-size:14px;"><?php echo $displayQty; ?> available</p>
							<?php else: ?>
								<h5 style="color:red; margin-top:12px;"><?php echo __('OUT_OF_STOCK'); ?></h5>
							<?php endif; ?>
				</div>

				<div class="col-md-7">
					<div class="single-product-content">
						<h3><?php echo htmlentities($row['productName']);?></h3>
						<!-- price removed for inventory-only view -->
						<p><?php echo $row['productDescription'];?>.</p>
						<div class="single-product-form">
							<?php if ($row['Quantity'] > 0):
								$qtyVal = floatval($row['Quantity']);
								$s = number_format($qtyVal, 3, '.', '');
								$s = rtrim(rtrim($s, '0'), '.');
							?>
								<p style="text-align: center; font-weight:600; margin-top: 15px; color:#333; font-size:14px;"><?php echo $displayQty; ?> available</p>
<form method="post" action="scripts/add_to_cart_api.php" style="text-align:center;margin-top:10px;">
							<input type="hidden" name="pid" value="<?php echo $pid; ?>" />
								<input type="number" name="inputQuantity" min="1" step="any" placeholder="Qty" style="width:120px;padding:6px;" required />
								<button type="submit" name="addtocart" class="btn btn-secondary" style="margin-left:8px;padding:6px 10px;"><?php echo __('ADD_TO_CART'); ?></button>
							</form>
		
					</div>
				</div>
			</div>
		</div>
	</div> <?php endif; ?>  <?php
                }
                ?>
	<!-- end single product -->


	<?php include_once('includes/footer.php');?>
	
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

	<script>
	/* AJAX add-to-cart disabled — using normal form submit fallback */
	</script>

</body>
</html>