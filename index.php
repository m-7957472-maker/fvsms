<?php 
session_start();
include_once('includes/config.php');
error_reporting(0);

// Code for Wish List
$pid=intval($_POST['pid']);
if(isset($_POST['wishlist'])){
    if(strlen($_SESSION['id'])==0) {   
        echo "<script>alert('".addslashes(__('LOGIN_REQUIRED_WISHLIST'))."');</script>";
    } else {
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
    }
}

// Code for Adding Product to Cart
if(isset($_POST['addtocart'])){
    if(strlen($_SESSION['id'])==0) {   
        echo "<script>alert('".addslashes(__('LOGIN_REQUIRED_ADD_TO_CART'))."');</script>";
    } else {
        $userid=$_SESSION['id']; 
        $pqty=$_POST['inputQuantity'];  
        $query=mysqli_query($con,"select id,productQty from cart where userId='$userid' and productId='$pid'");
        $count=mysqli_num_rows($query);
        if($count==0){
            mysqli_query($con,"insert into cart(userId,productId,productQty) values('$userid','$pid','$pqty')");
            echo "<script>alert('".addslashes(__('PRODUCT_ADDED_IN_CART'))."');</script>";
            echo "<script type='text/javascript'> document.location ='my-cart.php'; </script>";
        } else { 
            $row=mysqli_fetch_array($query);
            $currentpqty=$row['productQty'];
            $productqty=$pqty+$currentpqty;
            mysqli_query($con,"update cart set productQty='$productqty' where userId='$userid' and productId='$pid'");
            echo "<script>alert('".addslashes(__('PRODUCT_ADDED_IN_CART'))."');</script>";
            echo "<script type='text/javascript'> document.location ='my-cart.php'; </script>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ms">
<head>
	

	<!-- title -->
	<title>GHP INVENTORY MANAGEMENT SYSTEM</title>

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
	<!-- home page slider -->
	<div class="homepage-slider">
		<!-- single home slider -->
		<div class="single-homepage-slider homepage-bg-1">
			<div class="container">
				<div class="row">
					<div class="col-md-12 col-lg-7 offset-lg-1 offset-xl-0">
						<div class="hero-text">
							<div class="hero-text-tablecell">
								<p class="subtitle"><?php echo __('HERO_SUBTITLE_1'); ?></p>
								<h1><?php echo __('HERO_TITLE_1'); ?></h1>
								<div class="hero-btns">
									<a href="shop.php" class="boxed-btn"><?php echo __('HERO_BTN_PRIMARY'); ?></a>
									<a href="contact.php" class="bordered-btn"><?php echo __('HERO_BTN_CONTACT'); ?></a>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- single home slider -->
		<div class="single-homepage-slider homepage-bg-2">
			<div class="container">
				<div class="row">
					<div class="col-lg-10 offset-lg-1 text-center">
						<div class="hero-text">
							<div class="hero-text-tablecell">
								<p class="subtitle"><?php echo __('HERO_SUBTITLE_2'); ?></p>
								<h1><?php echo __('HERO_TITLE_2'); ?></h1>
								<div class="hero-btns">
							<a href="shop.php" class="boxed-btn"><?php echo __('HERO_BTN_PRIMARY'); ?></a>
									<a href="contact.php" class="bordered-btn"><?php echo __('HERO_BTN_CONTACT'); ?></a>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- single home slider -->
		<div class="single-homepage-slider homepage-bg-3">
			<div class="container">
				<div class="row">
					<div class="col-lg-10 offset-lg-1 text-right">
						<div class="hero-text">
							<div class="hero-text-tablecell">
								<p class="subtitle"><?php echo __('HERO_SUBTITLE_3'); ?></p>
								<h1><?php echo __('HERO_TITLE_3'); ?></h1>
								<div class="hero-btns">
									<a href="shop.php" class="boxed-btn"><?php echo __('HERO_BTN_PRIMARY'); ?></a>
									<a href="contact.php" class="bordered-btn"><?php echo __('HERO_BTN_CONTACT'); ?></a>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- end home page slider -->

	<script>
	// Ensure homepage hero buttons are shown in site language (in-case some slides were left in static text)
	document.addEventListener('DOMContentLoaded', function(){
	    var contactLabel = "<?php echo addslashes(__('HERO_BTN_CONTACT')); ?>";
	    var primaryLabel = "<?php echo addslashes(__('HERO_BTN_PRIMARY')); ?>";
	    document.querySelectorAll('.bordered-btn').forEach(function(el){
	        if(el.textContent.trim() === 'Contact Us') el.textContent = contactLabel;
	    });
	    document.querySelectorAll('.boxed-btn').forEach(function(el){
	        if(el.textContent.trim() === 'Visit Inventory') el.textContent = primaryLabel;
	    });
	});
	</script>

	<!-- features list section -->
	<div class="list-section pt-80 pb-80">
		<div class="container">

			<div class="row">
				<div class="col-lg-4 col-md-6 mb-4 mb-lg-0">
					<div class="list-box d-flex align-items-center">
						<div class="list-icon">
							<i class="fas fa-shipping-fast"></i>
						</div>
						<div class="content">
							<h3><?php echo __('FAST_AND_RELIABLE'); ?></h3>
						</div>
					</div>
				</div>
				<div class="col-lg-4 col-md-6 mb-4 mb-lg-0">
					<div class="list-box d-flex align-items-center">
						<div class="list-icon">
							<i class="fas fa-phone-volume"></i>
						</div>
						<div class="content">
							<h3><?php echo __('SUPPORT_24_7'); ?></h3>
						</div>
					</div>
				</div>
				<div class="col-lg-4 col-md-6">
					<div class="list-box d-flex justify-content-start align-items-center">
						<div class="list-icon">
							<i class="fas fa-sync"></i>
						</div>
						<div class="content">
							<h3><?php echo __('RELIABLE'); ?></h3>
						</div>
					</div>
				</div>
			</div>

		</div>
	</div>
	<!-- end features list section -->

	<!-- product section -->
	<div id="product-list" class="product-section mt-150 mb-150">
		<div class="container">
			<div class="row">
				<div class="col-lg-8 offset-lg-2 text-center">
					<div class="section-title">	
						<h3><span class="orange-text"><?php echo __('PRODUCTS'); ?></span> <?php echo __('OUR'); ?></h3>
						<p>Dapatkan keperluan bahan mentah anda dengan mudah menggunakan sistem kami, sistem ini merekodkan penggunaan bahan mentah serta ketersediaan bahan mentah secara tepat.</p>
					</div>
				</div>
			</div>

			<div class="row">
			  <?php
                if (isset($_GET['page_no']) && $_GET['page_no']!="") {
                    $page_no = $_GET['page_no'];
                } else {
                    $page_no = 1;
                }

                $total_records_per_page = 6;
                $offset = ($page_no-1) * $total_records_per_page;
                $previous_page = $page_no - 1;
                $next_page = $page_no + 1;
                $adjacents = "2"; 

                $result_count = mysqli_query($con,"SELECT COUNT(*) As total_records FROM products ");
                $total_records = mysqli_fetch_array($result_count);
                $total_records = $total_records['total_records'];
                $total_no_of_pages = ceil($total_records / $total_records_per_page);
                $second_last = $total_no_of_pages - 1;

                $query=mysqli_query($con,"select products.id as pid,products.productImage1,products.productName,products.Quantity,products.productPriceBeforeDiscount,products.productPrice, category.categoryName from products left join category on products.category = category.id order by pid desc LIMIT $offset, $total_records_per_page ");
                $cnt=1;
                while($row = mysqli_fetch_array($query)) {
                    $isEquipment = stripos($row['categoryName'] ?? '', 'Peralatan') !== false;
                    $displayQty = getProductDisplayQty($row);
                ?>
                <div class="col-lg-4 col-md-6 text-center strawberry">
                    <div class="single-product-item">
                        <div class="product-image">
                            <a href="product-details.php?pid=<?php echo htmlentities($row['pid']); ?>">
                                <img src="admin/productimages/<?php echo htmlentities($row['productImage1']); ?>" alt="" width="300" height="300">
                            </a>
                        </div>
                        <h3><?php echo htmlentities($row['productName']); ?></h3>

						<?php if ($row['Quantity'] > 0):
							$qtyVal = floatval($row['Quantity']);
							$s = number_format($qtyVal, 3, '.', '');
							$s = rtrim(rtrim($s, '0'), '.');
						?>
							<p style="margin-top: 18px; text-align: center; font-weight:600; color:#333; font-size:14px;"><?php echo $displayQty; ?> <?php echo __('AVAILABLE'); ?></p>
						<?php else: ?>
							<h5 style="color:red; margin-top: 35px;"><?php echo __('OUT_OF_STOCK'); ?></h5>
						<?php endif; ?>
                    </div>
                </div>
                <?php
                }
                ?>
            </div>
				
				
            <div class="row">
                <div class="col-lg-12 text-center">
                    <div class="pagination-wrap">
                        <ul>
                            <li <?php if($page_no <= 1){ echo "class='page-item disabled'"; } ?>>
                                <a <?php if($page_no > 1){ echo "href='?page_no=$previous_page#product-list'"; } ?> class="page-link"><?php echo __('PAGINATION_PREVIOUS'); ?></a>
                            </li>
                            <?php 
                            if ($total_no_of_pages <= 10) {       
                                for ($counter = 1; $counter <= $total_no_of_pages; $counter++) {
                                    if ($counter == $page_no) {
                                        echo "<li class='page-link active'><a>$counter</a></li>";  
                                    } else {
                                        echo "<li><a href='?page_no=$counter#product-list' class='page-link'>$counter</a></li>";
                                    }
                                }
                            } elseif ($total_no_of_pages > 10) {
                                if ($page_no <= 4) {            
                                    for ($counter = 1; $counter < 8; $counter++) {         
                                        if ($counter == $page_no) {
                                            echo "<li class='page-link active'><a>$counter</a></li>";  
                                        } else {
                                            echo "<li><a href='?page_no=$counter#product-list' class='page-link'>$counter</a></li>";
                                        }
                                    }
                                    echo "<li class='page-item'><a class='page-link'>...</a></li>";
                                    echo "<li><a href='?page_no=$second_last#product-list' class='page-link'>$second_last</a></li>";
                                    echo "<li><a href='?page_no=$total_no_of_pages#product-list' class='page-link'>$total_no_of_pages</a></li>";
                                } elseif ($page_no > 4 && $page_no < $total_no_of_pages - 4) {         
                                    echo "<li><a href='?page_no=1#product-list' class='page-link'>1</a></li>";
                                    echo "<li><a href='?page_no=2#product-list' class='page-link'>2</a></li>";
                                    echo "<li class='page-item'><a class='page-link'>...</a></li>";
                                    for ($counter = $page_no - $adjacents; $counter <= $page_no + $adjacents; $counter++) {         
                                        if ($counter == $page_no) {
                                            echo "<li class='page-link active'><a>$counter</a></li>";  
                                        } else {
                                            echo "<li><a href='?page_no=$counter#product-list' class='page-link'>$counter</a></li>";
                                        }
                                    }
                                    echo "<li class='page-item'><a class='page-link'>...</a></li>";
                                    echo "<li><a href='?page_no=$second_last#product-list' class='page-link'>$second_last</a></li>";
                                    echo "<li><a href='?page_no=$total_no_of_pages#product-list' class='page-link'>$total_no_of_pages</a></li>";
                                } else {
                                    echo "<li><a href='?page_no=1#product-list' class='page-link'>1</a></li>";
                                    echo "<li><a href='?page_no=2#product-list' class='page-link'>2</a></li>";
                                    echo "<li class='page-item'><a class='page-link'>...</a></li>";
                                    for ($counter = $total_no_of_pages - 6; $counter <= $total_no_of_pages; $counter++) {
                                        if ($counter == $page_no) {
                                            echo "<li class='page-link active'><a>$counter</a></li>";  
                                        } else {
                                            echo "<li><a href='?page_no=$counter#product-list' class='page-link'>$counter</a></li>";
                                        }
                                    }
                                }
                            }
                            ?>
                            <li <?php if($page_no >= $total_no_of_pages){ echo "class='page-item disabled'"; } ?>>
                                <a <?php if($page_no < $total_no_of_pages) { echo "href='?page_no=$next_page#product-list'"; } ?> class="page-link"><?php echo __('PAGINATION_NEXT'); ?></a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
			</div>
		</div>
	</div>
	<!-- end product section -->

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

</body>
</html>