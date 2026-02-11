<?php session_start();
include_once('includes/config.php');
error_reporting(0);
if(strlen( $_SESSION["aid"])==0)
{   
header('location:logout.php');
} else {

//For Adding categories
if(isset($_POST['submit']))
{
$category=$_POST['category'];
$description=$_POST['description'];
$createdby=$_SESSION['aid'];
$sql=mysqli_query($con,"insert into category(categoryName,categoryDescription,createdBy) values('$category','$description','$createdby')");
echo "<script>alert('" . addslashes(__('CATEGORY_ADDED')) . "');</script>";
echo "<script>window.location.href='manage-categories.php'</script>";

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
        <title>GHP INVENTORY MANAGEMENT SYSTEM | Add Category</title>
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
                        <h1 class="mt-4"><?php echo __('ADD_CATEGORY'); ?></h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="dashboard.php"><?php echo __('DASHBOARD'); ?></a></li>
                            <li class="breadcrumb-item active"><?php echo __('ADD_CATEGORY'); ?></li>
                        </ol>
                        <div class="card mb-4">
                            <div class="card-body">
<form  method="post">                                
<div class="row">
<div class="col-4"><?php echo __('CATEGORY_NAME'); ?></div>
<div class="col-8"><input type="text" placeholder="<?php echo __('ENTER_CATEGORY_NAME'); ?>"  name="category" class="form-control" required></div>
</div>

<div class="row" style="margin-top:1%;">
<div class="col-4"><?php echo __('DESCRIPTION'); ?></div>
<div class="col-8"><textarea placeholder="<?php echo __('ENTER_CATEGORY_DESCRIPTION'); ?>"  name="description" class="form-control" required></textarea></div>
</div>

<div class="row" style="margin-top:1%;">
<div class="col-8" align="right"><button type="submit" name="submit" class="btn btn-primary"><?php echo __('SUBMIT'); ?></button></div>
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
