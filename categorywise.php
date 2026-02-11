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
        $pqty = isset($_POST['inputQuantity']) ? $_POST['inputQuantity'] : (isset($_POST['qty']) ? $_POST['qty'] : 0);
        $pqty = floatval($pqty);
        if ($pqty <= 0) { $pqty = 1; }
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
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Responsive Bootstrap4 Shop Template, Created by Imran Hossain from https://imransdesign.com/">
    
    <!-- title -->
	<title>GHP INVENTORY MANAGEMENT SYSTEM || Shop</title>
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
    /* Hide price on category listing (inventory-only view) */
    p.product-price, p.single-product-pricing { display: none !important; }
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
                        <p>GHP INVENTORY MANAGEMENT SYSTEM</p>
                        <h1>Shop</h1>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- end breadcrumb section -->

    <!-- products -->
    <div class="product-section mt-150 mb-150">
        <div class="container">

            <div class="row">
                <div class="col-md-12">
                    <div class="product-filters">
                        <ul><?php 
$cid=$_GET['cid'];
$query=mysqli_query($con,"select category.id as catid,category.categoryName from category where id='$cid' ");
while($result=mysqli_fetch_array($query))
{ ?>
                          <h1 class="display-4 fw-bolder"><?php echo $result['categoryName'];?></h1>
                    <p class="lead fw-normal text-white-50 mb-0">Catgeory Products/Items</p> <?php } ?>
                           
                            
                        </ul>
                    </div>
                </div>
            </div>

            <div class="row product-lists">
                <?php
                $cid=$_GET['cid'];
                if (isset($_GET['page_no']) && $_GET['page_no']!="") {
                    $page_no = $_GET['page_no'];
                } else {
                    $page_no = 1;
                }

                $total_records_per_page = 9;
                $adjacents = "2"; 
                // offset will be computed after we clamp $page_no based on total pages (below)

                // Count only products in this category
                $safeCid = intval($cid);
                $result_count = mysqli_query($con,"SELECT COUNT(*) As total_records FROM products WHERE category='$safeCid'");
                $total_records = mysqli_fetch_array($result_count);
                $total_records = $total_records['total_records'];
                $total_no_of_pages = $total_records > 0 ? ceil($total_records / $total_records_per_page) : 1;
                $second_last = $total_no_of_pages - 1;
                // clamp current page number
                $page_no = max(1, min(intval($page_no), $total_no_of_pages));
                $offset = ($page_no-1) * $total_records_per_page;
                $previous_page = $page_no - 1;
                $next_page = $page_no + 1;

                $query=mysqli_query($con,"select products.id as pid,products.productImage1,products.productName,products.Quantity,products.Availablein,products.productPriceBeforeDiscount,products.productPrice from products where category='$cid' order by pid desc LIMIT $offset, $total_records_per_page ");
                $count=mysqli_num_rows($query);
if($count>0){
    $isCatEquipment = stripos($result['categoryName'], 'Peralatan') !== false;
while($row=mysqli_fetch_array($query))
{
    $isEquipment = $isCatEquipment || stripos($row['productName'] ?? '', 'sudu') !== false;
    $displayQty = getProductDisplayQty($row);
    $dataAvail = getProductAvailableNumber($row);
    $availUnitAttr = getProductUnitLabel($row);
    $unitVal = getProductUnitValue($row);
?> 
                <div class="col-lg-4 col-md-6 text-center strawberry">
                    <div class="single-product-item">
                        <div class="product-image">
                            <a href="product-details.php?pid=<?php echo htmlentities($row['pid']); ?>">
                                <img src="admin/productimages/<?php echo htmlentities($row['productImage1']); ?>" alt="" width="300" height="300">
                            </a>
                        </div>
                        <h3><?php echo htmlentities($row['productName']); ?></h3>
                        <p class="product-price">
                            <span style="text-decoration: line-through;">$<?php echo htmlentities($row['productPriceBeforeDiscount']); ?></span>
                            $<?php echo htmlentities($row['productPrice']); ?>
                        </p>
                        <?php if ($row['Quantity'] > 0):
                            $qtyVal = floatval($row['Quantity']);
                            $s = number_format($qtyVal, 3, '.', '');
                            $s = rtrim(rtrim($s, '0'), '.');
                        ?>
                            <p style="margin-top: 18px; text-align: center; font-weight:600; color:#333; font-size:14px;"><?php echo $displayQty; ?> available</p>
                            <form method="post" class="inventory-checkout-form" data-available="<?php echo $dataAvail; ?>" data-available-unit="<?php echo htmlentities($availUnitAttr); ?>" style="text-align:center; margin-top:10px;">
                                <input type="number" name="qty" min="0.001" step="any" placeholder="Qty" class="inventory-qty" style="width:90px; padding:6px;" required />
                                <?php if ($isEquipment): ?>
                                <select name="unit" class="inventory-unit" style="padding:6px; margin-left:6px;">
                                    <option value="<?php echo htmlspecialchars($unitVal); ?>"><?php echo htmlspecialchars($availUnitAttr); ?></option>
                                </select>
                                <?php else: ?>
                                <select name="unit" class="inventory-unit" style="padding:6px; margin-left:6px;">
                                    <option value="kg">kg</option>
                                    <option value="g">g</option>
                                </select>
                                <?php endif; ?>
                                <input type="hidden" name="pid" value="<?php echo htmlentities($row['pid']); ?>" />

                                <!-- Checkout button posts to inventory handler -->
                                <input type="text" name="purpose" placeholder="Purpose (optional)" class="form-control" style="display:inline-block; width:220px; margin-right:8px; vertical-align:middle;" />
                                <button type="submit" formaction="inventory_checkout.php" class="btn btn-primary inventory-submit" style="padding:6px 10px;">Checkout</button>

                                <!-- Add to Cart posts back to categorywise.php using the same qty field -->
                                <button type="submit" name="addtocart" formaction="scripts/add_to_cart_api.php" class="btn btn-secondary" style="margin-left:8px;padding:6px 10px;"><?php echo __('ADD_TO_CART'); ?></button>

                                <div class="inventory-msg" style="margin-top:8px;color:#c00;font-size:13px;display:none;"></div>
                            </form>
                        <?php else: ?>
                            <h5 style="color:red; margin-top: 35px;"><?php echo __('OUT_OF_STOCK'); ?></h5>
                        <?php endif; ?>
                    </div>
                </div>
              <?php } }  else{ ?>
     <h4 style="color:red">No Record found</h4>
<?php } ?>
            </div>

            <div class="row">
                <div class="col-lg-12 text-center">
                    <div class="pagination-wrap">
                        <ul>
                            <?php if ($total_no_of_pages > 1) {
                                // Previous
                                echo "<li " . ($page_no <= 1 ? "class='page-item disabled'" : "") . ">";
                                if ($page_no > 1) {
                                    echo "<a href='?cid={$safeCid}&page_no={$previous_page}' class='page-link'>Previous</a>";
                                } else {
                                    echo "<a class='page-link'>Previous</a>";
                                }
                                echo "</li>";

                                // Page links
                                if ($total_no_of_pages <= 10) {
                                    for ($counter = 1; $counter <= $total_no_of_pages; $counter++) {
                                        if ($counter == $page_no) {
                                            echo "<li class='page-link active'><a>$counter</a></li>";
                                        } else {
                                            echo "<li><a href='?cid={$safeCid}&page_no=$counter' class='page-link'>$counter</a></li>";
                                        }
                                    }
                                } elseif ($page_no <= 4) {
                                    for ($counter = 1; $counter < 8; $counter++) {
                                        if ($counter == $page_no) {
                                            echo "<li class='page-link active'><a>$counter</a></li>";
                                        } else {
                                            echo "<li><a href='?cid={$safeCid}&page_no=$counter' class='page-link'>$counter</a></li>";
                                        }
                                    }
                                    echo "<li class='page-item'><a class='page-link'>...</a></li>";
                                    echo "<li><a href='?cid={$safeCid}&page_no=$second_last' class='page-link'>$second_last</a></li>";
                                    echo "<li><a href='?cid={$safeCid}&page_no=$total_no_of_pages' class='page-link'>$total_no_of_pages</a></li>";
                                } elseif ($page_no > 4 && $page_no < $total_no_of_pages - 4) {
                                    echo "<li><a href='?cid={$safeCid}&page_no=1' class='page-link'>1</a></li>";
                                    echo "<li><a href='?cid={$safeCid}&page_no=2' class='page-link'>2</a></li>";
                                    echo "<li class='page-item'><a class='page-link'>...</a></li>";
                                    for ($counter = $page_no - $adjacents; $counter <= $page_no + $adjacents; $counter++) {
                                        if ($counter == $page_no) {
                                            echo "<li class='page-link active'><a>$counter</a></li>";
                                        } else {
                                            echo "<li><a href='?cid={$safeCid}&page_no=$counter' class='page-link'>$counter</a></li>";
                                        }
                                    }
                                    echo "<li class='page-item'><a class='page-link'>...</a></li>";
                                    echo "<li><a href='?cid={$safeCid}&page_no=$second_last' class='page-link'>$second_last</a></li>";
                                    echo "<li><a href='?cid={$safeCid}&page_no=$total_no_of_pages' class='page-link'>$total_no_of_pages</a></li>";
                                } else {
                                    echo "<li><a href='?cid={$safeCid}&page_no=1' class='page-link'>1</a></li>";
                                    echo "<li><a href='?cid={$safeCid}&page_no=2' class='page-link'>2</a></li>";
                                    echo "<li class='page-item'><a class='page-link'>...</a></li>";
                                    for ($counter = $total_no_of_pages - 6; $counter <= $total_no_of_pages; $counter++) {
                                        if ($counter == $page_no) {
                                            echo "<li class='page-link active'><a>$counter</a></li>";
                                        } else {
                                            echo "<li><a href='?cid={$safeCid}&page_no=$counter' class='page-link'>$counter</a></li>";
                                        }
                                    }
                                }

                                // Next
                                echo "<li " . ($page_no >= $total_no_of_pages ? "class='page-item disabled'" : "") . ">";
                                if ($page_no < $total_no_of_pages) {
                                    echo "<a href='?cid={$safeCid}&page_no=$next_page' class='page-link'>Next</a>";
                                } else {
                                    echo "<a class='page-link'>Next</a>";
                                }
                                echo "</li>";

                            } else {
                                // Single page - show single active page number
                                echo "<li class='page-link active'><a>1</a></li>";
                            }
                            ?>
                        </ul>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <!-- end products -->

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
</html>
