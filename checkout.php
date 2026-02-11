<?php session_start();
include_once('includes/config.php');
error_reporting(0);
if(strlen($_SESSION['id'])==0)
{   header('location:logout.php');
}else{
// Code for Product deletion from  cart  
if(isset($_GET['del']))
{
$wid=intval($_GET['del']);
$query=mysqli_query($con,"delete from cart where id='$wid'");
 echo "<script>alert('Product deleted from cart.');</script>";
echo "<script type='text/javascript'> document.location ='checkout.php'; </script>";
}
// Address insertion removed - simplified checkout flow (no address/payment required)
// Checkout will perform a direct inventory checkout via scripts/quick_checkout.php

// Proceed to payment removed - payment/address steps are not used in inventory checkout

?>
<!DOCTYPE html>
<html lang="ms">
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="Responsive Bootstrap4 Shop Template, Created by Imran Hossain from https://imransdesign.com/">

	<!-- title -->
	<title>GHP INVENTORY MANAGEMENT SYSTEM || Cart</title>

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
	<style>
	/* Hide pricing on inventory system */
	.product-price, .single-product-pricing, .total-price, .cart-total, .subtotal, .tax, .shipping-cost, .grandtotal, .payment-section { display: none !important; }
	</style>

</head>
<body>
	
	<?php include_once('includes/header.php'); ?>
	


	
	<!-- breadcrumb-section -->
	<div class="breadcrumb-section breadcrumb-bg">
		<div class="container">
			<div class="row">
				<div class="col-lg-8 offset-lg-2 text-center">
					<div class="breadcrumb-text">
						<p>Segar dan Organik</p>
						<h1><?php echo __('CHECK_OUT_BUTTON'); ?></h1>
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
				<div class="col-lg-8 col-md-12">
					<div class="cart-table-wrap">
						<table class="cart-table">
							<thead class="cart-table-head">
								<tr class="table-head-row">
									<th class="product-remove"></th>
									<th class="product-image"><?php echo __('PRODUCT_IMAGE'); ?></th>
									<th class="product-name"><?php echo __('PRODUCT_NAME'); ?></th>
									<th class="product-price"><?php echo __('PRICE'); ?></th>
									<th class="product-price"><?php echo __('SHIPPING_PRICE'); ?></th>
									<th class="product-quantity"><?php echo __('QUANTITY'); ?></th>
									<th class="product-total"><?php echo __('TOTAL'); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php
								$grantotal = 0;
$uid=$_SESSION['id'];
$ret=mysqli_query($con,"select products.productName as pname,products.productName as proid,products.productImage1 as pimage,products.productPrice as pprice,cart.productId as pid,cart.id as cartid,products.productPriceBeforeDiscount,products.shippingCharge as shippingCharge,cart.productQty as productQty from cart join products on products.id=cart.productId where cart.userId='$uid'");
$num=mysqli_num_rows($ret);
    if($num>0)
    {
while ($row=mysqli_fetch_array($ret)) {
$productTotal = ($row['pprice'] * $row['productQty']) + $row['shippingCharge'];
    $grantotal += $productTotal;
?>
								<tr class="table-body-row">
									<td class="product-remove"><a href="my-cart.php?del=<?php echo htmlentities($row['cartid']);?>"  onClick="return confirm('<?php echo __('CONFIRM_DELETE_ITEM'); ?>')"><i class="far fa-window-close"></i></a></td>
									<td class="product-image"><img src="admin/productimages/<?php echo htmlentities($row['pimage']);?>" alt=""></td>
									<td class="product-name"><a href="product-details.php?pid=<?php echo htmlentities($pd=$row['pid']);?>"><?php echo htmlentities($row['pname']);?></a></td>
									<td class="product-price">  <span style="text-decoration: line-through;">$<?php echo htmlentities($row['productPriceBeforeDiscount']);?></span>
                            <span>$<?php echo htmlentities($row['pprice']);?></span></td>
                            <td class="product-quantity"><?php echo htmlentities($row['shippingCharge']);?></td>
									<td class="product-quantity"><?php echo htmlentities($row['productQty']);?></td>
									<td class="product-total"><?php echo htmlentities($totalamount=$row['productQty']*$row['pprice']+$row['shippingCharge']);?></td>
								</tr><?php } ?>
								 <tr>
                    
                </tr>
								

							 <?php } else{ ?>
                <tr>
                    <td style="font-size: 18px; font-weight:bold ">
<a href="my-cart.php" class="btn-upper btn btn-warning"><?php echo __('CONTINUE_SHOPPING'); ?></a>
                    </td>

                </tr><?php } ?>
							</tbody>
						</table>
					</div>
				</div>


			</div>
		</div>
	</div>
	<!-- end cart -->

	<!-- logo carousel -->
	<div class="logo-carousel-section">
		<div class="container">
			<div class="row">
				<div class="col-12">
					<form method="post" action="scripts/quick_checkout.php" id="simpleCheckoutForm" class="mx-auto" style="max-width:720px;">
						<div class="mb-3">
							<label class="form-label"><strong>Tujuan (pilihan)</strong></label>
							<textarea name="purpose" class="form-control" placeholder="Contoh: Pengeluaran, Sampel, Penyelidikan" rows="3"></textarea>
						</div>
						<div class="d-flex justify-content-between">
							<a href="shop.php" class="btn btn-warning">Tambah Lagi</a>
							<button class="btn btn-success" type="submit">Sahkan Pengeluaran Barang</button>
						</div>
					</form>
				</div>


<!-- Address form removed (no billing/shipping required) -->
			</div>
		</div>
	</div>
	<!-- end logo carousel -->

	
	
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

	<script>
	document.addEventListener('DOMContentLoaded', function(){
	    var f = document.getElementById('simpleCheckoutForm');
	    if (!f) return;
	    var btn = f.querySelector('button[type="submit"]');
	    f.addEventListener('submit', function(e){
	        if (btn) { btn.disabled = true; btn.textContent = 'Memproses...'; }
	    });
	});
	</script>

</body>
</html> <?php } ?>