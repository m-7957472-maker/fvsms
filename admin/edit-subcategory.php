<?php session_start();
include_once(__DIR__ . '/../includes/config.php');
error_reporting(0);
if(strlen( $_SESSION["aid"])==0)
{   
header('location:logout.php');
} else {
//For Update Sub-categories
if(isset($_POST['submit']))
{
    $category=$_POST['category'];
    $subcat=$_POST['subcategory'];
    $id=intval($_GET['id']);
$sql=mysqli_query($con,"update subcategory set categoryid='$category',subcategoryName='$subcat' where id='$id'");
echo "<script>alert('" . addslashes(__('SUBCATEGORY_UPDATED')) . "');</script>";
echo "<script>window.location.href='manage-subcategories.php'</script>";

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
        <title>GHP INVENTORY MANAGEMENT SYSTEM | Edit Sub Category</title>
        <link href="css/styles.css" rel="stylesheet" />
        <script src="js/all.min.js" crossorigin="anonymous"></script>
    </head>
    <body>
   <?php include_once('includes/header.php');?>
        <div id="layoutSidenav">
   <?php include_once('includes/sidebar.php');?>
            <div id="layoutSidenav_content">
                <main>
                    <div class="container-fluid px-4">
                        <h1 class="mt-4"><?php echo __('EDIT_SUBCATEGORY'); ?></h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="dashboard.php"><?php echo __('DASHBOARD'); ?></a></li>
                            <li class="breadcrumb-item active"><?php echo __('EDIT_SUBCATEGORY'); ?></li>
                        </ol>
                        <div class="card mb-4">
                            <div class="card-body">

<?php
$id=intval($_GET['id']);
$query=mysqli_query($con,"select category.id,category.categoryName,subcategory.subcategoryName from subcategory join category on category.id=subcategory.categoryid where subcategory.id='$id'");
while($row=mysqli_fetch_array($query))
{
?>  
<form  method="post">                                
<div class="row">
<div class="col-4"><?php echo __('CATEGORY_NAME'); ?></div>
<div class="col-8">
<select name="category" class="form-control" required>
<option value="<?php echo htmlentities($row['id']);?>"><?php echo htmlentities($catname=$row['categoryName']);?></option>
<?php $ret=mysqli_query($con,"select * from category");
while($result=mysqli_fetch_array($ret))
{
echo $cat=$result['categoryName'];
if($catname==$cat)
{continue;
}else{
?>
<option value="<?php echo $result['id'];?>"><?php echo $result['categoryName'];?></option>
<?php } }?>
</select>    
</div>
</div>

<div class="row" style="margin-top:1%;">
<div class="col-4"><?php echo __('SUBCATEGORY_NAME'); ?></div>
<div class="col-8"><input type="text" value="<?php echo  htmlentities($row['subcategoryName']);?>"  name="subcategory" class="form-control" required></div>
</div>

<div class="row" style="margin-top:1%;">
<div class="col-8" align="right"><button type="submit" name="submit" class="btn btn-primary"><?php echo __('UPDATE'); ?></button></div>
</div>

</form>
<?php } ?>

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
