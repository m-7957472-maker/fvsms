<?php session_start();
include_once('includes/config.php');
if(strlen($_SESSION['id'])==0)
{   header('location:logout.php');
}else{

// Code for Product deletion from  cart  
if(isset($_GET['del']))
{
$wid=intval($_GET['del']);
$query=mysqli_query($con,"delete from cart where id='$wid'");
 echo "<script>alert('Product deleted from cart.');</script>";
echo "<script type='text/javascript'> document.location ='my-cart.php'; </script>";
}

// Handle Update Cart (quantities submitted from cart page)
if(isset($_POST['update_cart'])) {
    $uid = intval($_SESSION['id']);
    if(isset($_POST['qty']) && is_array($_POST['qty'])) {
        foreach($_POST['qty'] as $cartid => $q) {
            $cid = intval($cartid);
            $qtyVal = floatval(str_replace(',', '.', $q));
            if($qtyVal <= 0) {
                mysqli_query($con, "delete from cart where id='".$cid."' and userId='".$uid."'");
            } else {
                mysqli_query($con, "update cart set productQty='".mysqli_real_escape_string($con,$qtyVal)."' where id='".$cid."' and userId='".$uid."'");
            }
        }
    }
    echo "<script>alert('Cart updated.');</script>";
    echo "<script type='text/javascript'> document.location ='my-cart.php'; </script>";
    exit;
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
	.product-price, .single-product-pricing, .total-price, .cart-total, .subtotal, .tax, .shipping-cost, .grandtotal { display: none !important; }
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
							<p><?php echo __('FAST_AND_RELIABLE'); ?></p>
							<h1><?php echo __('CART'); ?></h1>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- end breadcrumb section -->

	<!-- cart -->
	<div class="cart-section mt-150 mb-150">
		<div class="container">
            <form method="post" action="my-cart.php">
			<div class="row">
				<div class="col-lg-8 col-md-12">
					<div class="cart-table-wrap">
						<table class="cart-table">
							<thead class="cart-table-head">
								<tr class="table-head-row">
									<th class="product-remove"></th>
									<th class="product-image"><?php echo __('PRODUCT_IMAGE'); ?></th>
									<th class="product-name"><?php echo __('PRODUCT_NAME'); ?></th>
								<th class="product-quantity"><?php echo __('QUANTITY'); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php
								$totalQty = 0;
$uid=$_SESSION['id'];
$ret=mysqli_query($con,"select products.productName as pname,products.productImage1 as pimage,cart.productId as pid,cart.id as cartid,cart.productQty as productQty from cart join products on products.id=cart.productId where cart.userId='$uid'");
$num=mysqli_num_rows($ret);
    if($num>0)
    {
while ($row=mysqli_fetch_array($ret)) {
    $totalQty += floatval($row['productQty']);
?>
								<tr class="table-body-row">
									<td class="product-remove"><a href="my-cart.php?del=<?php echo htmlentities($row['cartid']);?>"  onClick="return confirm('<?php echo __('CONFIRM_DELETE_PRODUCT'); ?>')"><i class="far fa-window-close"></i></a></td>
									<td class="product-image"><img src="admin/productimages/<?php echo htmlentities($row['pimage']);?>" alt=""></td>
									<td class="product-name"><a href="product-details.php?pid=<?php echo htmlentities($pd=$row['pid']);?>"><?php echo htmlentities($row['pname']);?></a></td>
	
								<td class="product-quantity">
				<input type="number" step="0.01" min="0" name="qty[<?php echo intval($row['cartid']);?>]" value="<?php echo htmlentities($row['productQty']);?>" class="form-control" style="width:90px;">
			</td>
								</tr><?php } ?>
								 <tr>
                    <td colspan="4" style="text-align:right;">
<a href="shop.php" class="btn-upper btn btn-warning"><?php echo __('CONTINUE_SHOPPING'); ?></a>
                        </td>
                </tr>
								

							 <?php } else{ ?>
                <tr>
                    <td style="font-size: 18px; font-weight:bold " colspan="4"><?php echo __('YOUR_CART_IS_EMPTY'); ?>&nbsp;
<a href="shop.php" class="btn-upper btn btn-warning"><?php echo __('CONTINUE_SHOPPING'); ?></a>
                    </td>

                </tr><?php } ?>
							</tbody>
						</table>
					</div>
				</div>

				<div class="col-lg-4">
					<div class="total-section">
						<table class="total-table">
							<thead class="total-table-head">
								<tr class="table-total-row">
							<th colspan="2"><?php echo __('SUMMARY_TOTALS'); ?></th>
						</tr>
						</thead>
						<tbody>
							<tr class="total-data">
							<td><strong><?php echo __('ITEMS'); ?>: </strong></td>
							<td><?php echo isset($num) ? intval($num) : 0; ?></td>
							</tr>

							<tr class="total-data">
							<td><strong><?php echo __('QUANTITY'); ?>: </strong></td>
								<td><?php echo isset($totalQty) ? htmlspecialchars($totalQty) : 0; ?></td>
								</tr>
							</tbody>
						</table>
						<div class="cart-buttons">
					<button type="submit" name="update_cart" class="boxed-btn"><?php echo __('UPDATE_CART'); ?></button>
					<button type="button" class="boxed-btn black" data-toggle="modal" data-target="#checkoutModal"><?php echo __('CHECK_OUT_BUTTON'); ?></button>
						</div>
					</div>

				
				</div>
			</div>
		</div>
            </form>
	</div>
	<!-- end cart -->

<!-- Checkout modal to capture purpose -->
<div class="modal fade" id="checkoutModal" tabindex="-1" aria-labelledby="checkoutModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="post" action="/fvsms/scripts/quick_checkout.php" id="checkoutForm" onsubmit="return enhancedCheckoutSubmit(this,event)">
      <input type="hidden" name="modal_checkout" value="1">
      <div class="modal-header">
        <h5 class="modal-title" id="checkoutModalLabel"><?php echo __('CHECK_OUT_BUTTON'); ?></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
            <label for="purpose" class="form-label">Tujuan (pilihan)</label>
            <textarea name="purpose" id="purpose" class="form-control" rows="2" placeholder="cth: Pengeluaran, Sampel, Penyelidikan"></textarea>
        </div>
        <div id="checkoutStatus" class="alert d-none" role="alert" style="margin-top:8px;"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
        <button type="submit" class="btn btn-primary" id="checkoutSubmit"><?php echo __('CHECK_OUT_BUTTON'); ?></button>
      </div>
      </form>
    </div>
  </div>
</div>

<script>
// Extra client-side logging to help diagnose checkout submission
(function(){
    var f = document.getElementById('checkoutForm');
    if (!f) return;
    f.addEventListener('submit', function(e){
        try { 
            // append a timestamp field so quick_checkout can log POST keys
            var ts = document.createElement('input'); ts.type='hidden'; ts.name='client_ts'; ts.value = Date.now();
            f.appendChild(ts);
        } catch(ex) { console.error(ex); }
    });
})();
</script>

<script>
// Fallback: robust delegated submit handler to ensure POST reaches server even if form submit is blocked
(function(){
    function doFetchSubmission(f, submitBtn){
        try {
            console.log('checkout: initiating fetch submit');
            submitBtn = submitBtn || f.querySelector('#checkoutSubmit') || f.querySelector('button[type=submit]');
            if (submitBtn) submitBtn.disabled = true;
            var fd = new FormData(f);
            if (!fd.has('client_ts')) fd.append('client_ts', Date.now());
            return fetch(f.action, { method: 'POST', body: fd, credentials: 'same-origin' })
                .then(function(r){ return r.text(); })
                .then(function(txt){
                    console.log('checkout response:', txt);
                    // Success: exact phrase (server uses double quotes here)
                    if (txt.indexOf('<?php echo addslashes(__('ORDER_PLACED_PREFIX')); ?>') !== -1) {
                        var m = txt.match(/(\d{6,})/);
                        var ord = m ? m[1] : '';
                        alert('<?php echo addslashes(__('ORDER_PLACED_PREFIX')); ?>' + (ord ? ('. Nombor pesanan: ' + ord) : ''));
                        window.location = '/fvsms/my-orders.php';
                        return;
                    }
                    // Try to extract alert message whether it uses double or single quotes
                    var mm = txt.match(/alert\((?:"([^\"]+)"|'([^']+)')\)/i);
                    if (mm) {
                        var msg = mm[1] || mm[2];
                        alert(msg);
                        // If server indicated success inside an alert, redirect to orders
                        if (msg.indexOf('<?php echo addslashes(__('ORDER_PLACED_PREFIX')); ?>') !== -1) {
                            var m2 = msg.match(/(\d{6,})/);
                            var ord2 = m2 ? m2[1] : '';
                            window.location = '/fvsms/my-orders.php';
                            return;
                        }
                        return;
                    }
                    // Fallback: strip HTML and show raw text
                    var stripped = txt.replace(/<[^>]+>/g,'').trim();
                    if (stripped.length) {
                        alert(stripped);
                    } else {
                        alert('Ralat semasa semak keluar. Sila semak Console (F12) untuk maklumat lanjut.');
                        console.error('checkout response (no obvious message):', txt);
                    }
                })
                .catch(function(err){ console.error('checkout fetch error', err); alert('Ralat sambungan ke pelayan: ' + err.message); })
                .finally(function(){ if (submitBtn) submitBtn.disabled = false; });
        } catch (ex) { console.error('checkout submit exception', ex); if (submitBtn) submitBtn.disabled = false; }
    }

    // delegated listener (works even if form replaced or created later)
    document.body.addEventListener('submit', function(e){
        var f = e.target;
        if (f && f.id === 'checkoutForm') {
            e.preventDefault();
            console.log('checkout: submit event intercepted (delegated)');
            doFetchSubmission(f);
        }
    }, true);

    // also attach direct listener if form already exists (for convenience)
    var f = document.getElementById('checkoutForm');
    if (f) {
        f.addEventListener('submit', function(e){
            console.log('checkout: direct submit listener invoked');
            // allow delegated handler to do the work (delegated prevents default), but in case delegated didn't run, prevent default here and do fetch
            e.preventDefault();
            doFetchSubmission(f);
        });
    }

})();
</script>



	
	
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