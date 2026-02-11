<?php session_start();
include_once(__DIR__ . '/../includes/config.php');
if(strlen( $_SESSION["aid"])==0)
{   
header('location:logout.php');
} else {

//For Adding Products
if(isset($_POST['submit']))
{
    $category=$_POST['category'];
    $subcat=$_POST['subcategory'];
    $productname=$_POST['productName'];
    $variety=$_POST['variety'];
    $availableIn=$_POST['availablein'];
    $quantity=$_POST['quantity'];
    $productdescription=$_POST['productDescription'];
    $productavailability=$_POST['productAvailability'];
    $productimage1=$_FILES["productimage1"]["name"];
    $productimage2=$_FILES["productimage2"]["name"];
    $productimage3=$_FILES["productimage3"]["name"];
$extension1 = substr($productimage1,strlen($productimage1)-4,strlen($productimage1));
$extension2 = substr($productimage2,strlen($productimage2)-4,strlen($productimage2));
$extension3 = substr($productimage3,strlen($productimage3)-4,strlen($productimage3));
//Renaming the  image file
$imgnewfile1=md5($productimage1.time()).$extension1;
$imgnewfile2=md5($productimage2.time()).$extension2;
$imgnewfile3=md5($productimage3.time()).$extension3;
$addedby=$_SESSION['aid'];


    move_uploaded_file($_FILES["productimage1"]["tmp_name"],"productimages/".$imgnewfile1);
    move_uploaded_file($_FILES["productimage2"]["tmp_name"],"productimages/".$imgnewfile2);
    move_uploaded_file($_FILES["productimage3"]["tmp_name"],"productimages/".$imgnewfile3);
$sql=mysqli_query($con,"insert into products(category,subCategory,productName,variety,Availablein,Quantity,productDescription,productAvailability,productImage1,productImage2,productImage3,addedBy) values('$category','$subcat','$productname','$variety','$availableIn','$quantity','$productdescription','$productavailability','$imgnewfile1','$imgnewfile2','$imgnewfile3','$addedby')");
echo "<script>alert('" . addslashes(__('PRODUCT_ADDED_SUCCESS')) . "');</script>";
echo "<script>window.location.href='manage-products.php'</script>";
}








?>

<!DOCTYPE html>
<html lang="ms">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title><?php echo __('ADD_PRODUCT'); ?> - GHP INVENTORY</title>
        <link href="css/styles.css" rel="stylesheet" />
        <script src="js/all.min.js" crossorigin="anonymous"></script>
        <script src="js/jquery-3.5.1.min.js"></script>
   <script>
function getSubcat(val) {
    $.ajax({
    type: "POST",
    url: "get_subcat.php",
    data:'cat_id='+val,
    success: function(data){
        $("#subcategory").html(data);
    }
    });
}
</script>   

    </head>
    <body>
   <?php include_once('includes/header.php');?>
        <div id="layoutSidenav">
   <?php include_once('includes/sidebar.php');?>
            <div id="layoutSidenav_content">
                <main>
                    <div class="container-fluid px-4">
                        <h1 class="mt-4"><?php echo __('ADD_PRODUCT'); ?></h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="dashboard.php"><?php echo __('DASHBOARD'); ?></a></li>
                            <li class="breadcrumb-item active"><?php echo __('ADD_PRODUCT'); ?></li>
                        </ol>
                        <div class="card mb-4">
                            <div class="card-body">
<form  method="post" enctype="multipart/form-data">                                
<div class="row">
<div class="col-4"><?php echo __('CATEGORY_NAME'); ?></div>
<div class="col-8">
<select name="category" id="category" class="form-control" onChange="getSubcat(this.value);" required>
<option value=""><?php echo __('SELECT_CATEGORY_PLACEHOLDER'); ?></option>  
<?php $query=mysqli_query($con,"select * from category");
while($row=mysqli_fetch_array($query))
{?>

<option value="<?php echo $row['id'];?>"><?php echo $row['categoryName'];?></option>
<?php } ?>
</select>    
</div>
</div>

<div class="row" style="margin-top:1%;">
<div class="col-4"><?php echo __('SUBCATEGORY_NAME'); ?></div>
<div class="col-8"><select   name="subcategory"  id="subcategory" class="form-control" required>
</select>
</div>
</div>

<div class="row" style="margin-top:1%;">
<div class="col-4"><?php echo __('PRODUCT_NAME'); ?></div>
<div class="col-8"><input type="text"    name="productName"  placeholder="<?php echo __('ENTER_PRODUCT_NAME'); ?>" class="form-control" required></div>
</select>
</div>
</div>

<div class="row" style="margin-top:1%;">
<div class="col-4"><?php echo __('VARIETY'); ?></div>
<div class="col-8"><input type="text"    name="variety"  placeholder="<?php echo __('ENTER_PRODUCT_VARIETY'); ?>" class="form-control">

</div>
</div>
<div class="row" style="margin-top:1%;">
<div class="col-4"><?php echo __('AVAILABLE_IN'); ?></div>
<div class="col-8">
    <select name="availablein" id="availableIn" class="form-control" onchange="updateOptions()">
        <option value="KG">KG</option>
        <option value="Count">Count</option>
    </select>
</div>
</div>

<div class="row" style="margin-top:1%;">
<div class="col-4"><?php echo __('QUANTITY'); ?></div>
<div class="col-8">
    <select name="quantity" id="quantity" class="form-control">
        <!-- Options will be updated here -->
    </select>
</div>
</div>
<div class="row" style="margin-top:1%;">
<div class="col-4"><?php echo __('PRODUCT_DESCRIPTION'); ?></div>
<div class="col-8"><textarea  name="productDescription"  placeholder="<?php echo __('ENTER_PRODUCT_DESCRIPTION'); ?>" rows="6" class="form-control"></textarea></div>
</div>
</div>

<div class="row" style="margin-top:1%;">
<div class="col-4"><?php echo __('PRODUCT_AVAILABILITY'); ?></div>
<div class="col-8"><select   name="productAvailability"  id="productAvailability" class="form-control" required>
<option value=""><?php echo __('SELECT'); ?></option>
<option value="In Stock"><?php echo __('IN_STOCK'); ?></option>
<option value="Out of Stock"><?php echo __('OUT_OF_STOCK'); ?></option>
</select>
</select>
</div>
</div>

<div class="row" style="margin-top:1%;">
<div class="col-4"><?php echo __('PRODUCT_FEATURED_IMAGE'); ?></div>
<div class="col-8"><input type="file" name="productimage1" id="productimage1"  class="form-control" accept="image/*" title="Accept images only" required>
</div>
</div>

<div class="row" style="margin-top:1%;">
<div class="col-4"><?php echo __('PRODUCT_IMAGE_2'); ?></div>
<div class="col-8"><input type="file" name="productimage2"  class="form-control" accept="image/*" title="Accept images only" required>
</div>
</div>


<div class="row" style="margin-top:1%;">
<div class="col-4"><?php echo __('PRODUCT_IMAGE_3'); ?></div>
<div class="col-8"><input type="file" name="productimage3"  class="form-control" accept="image/*" title="Accept images only" required>
</div>
</div>

<div class="row">
<div class="col-8"><button type="submit" name="submit" class="btn btn-primary"><?php echo __('SUBMIT'); ?></button></div>
</div>

</form>
                            </div>
                        </div>
                    </div>
                </main>
          <?php include_once('includes/footer.php');?>
            </div>
        </div>
        <script src="js/bootstrap.bundle.min.js"></script>
        <script src="js/scripts.js"></script>
    </body>
</html>
<?php } ?>
<script>
    function updateOptions() {
        const availableIn = document.getElementById("availableIn").value;
        const quantitySelect = document.getElementById("quantity");
        quantitySelect.innerHTML = ""; // Clear existing options

        if (availableIn === "KG") {
            // Add granular options for KG
            const kgOptions = ["100 gm", "250 gm", "500 gm", "1 KG", "2 KG", "5 KG", "10 KG"];
            kgOptions.forEach(optionText => {
                const option = document.createElement("option");
                option.value = optionText;
                option.text = optionText;
                quantitySelect.appendChild(option);
            });
        } else if (availableIn === "Count") {
            // Add options for Count
            for (let i = 1; i <= 10; i++) {
                const option = document.createElement("option");
                option.value = i + " pcs";
                option.text = i + " pcs";
                quantitySelect.appendChild(option);
            }
        }
    }

    // Initialize options based on default selection
    updateOptions();
</script>