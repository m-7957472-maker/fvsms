<?php session_start();
include_once(__DIR__ . '/../includes/config.php');
if(strlen( $_SESSION["aid"])==0)
{   
header('location:logout.php');
} else {

if(isset($_GET['del']))
{
mysqli_query($con,"delete from products where id = '".$_GET['id']."'");
echo "<script>alert('" . addslashes(__('DATA_DELETED')) . "');</script>";
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
        <title><?php echo __('SITE_TITLE') . ' | ' . __('MANAGE_PRODUCTS'); ?></title>
        <link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" />
        <link href="css/styles.css" rel="stylesheet" />
        <script src="js/all.min.js" crossorigin="anonymous"></script>
    </head>
    <body class="sb-nav-fixed">
 <?php include_once('includes/header.php');?>
        <div id="layoutSidenav">
       <?php include_once('includes/sidebar.php');?>
            <div id="layoutSidenav_content">
                <main>
                    <div class="container-fluid px-4">
                        <h1 class="mt-4"><?php echo __('MANAGE_PRODUCTS'); ?></h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="dashboard.php"><?php echo __('DASHBOARD'); ?></a></li>
                            <li class="breadcrumb-item active"><?php echo __('MANAGE_PRODUCTS'); ?></li>
                        </ol>
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-table me-1"></i>
                               <?php echo __('PRODUCTS_DETAILS'); ?>
                            </div>
                            <div class="card-body">
                                <table id="datatablesSimple">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th><?php echo __('PRODUCT_NAME'); ?></th>
                                            <th><?php echo __('SUB_CATEGORY'); ?></th>
                                            <th><?php echo __('CATEGORY'); ?></th>
                                            <th><?php echo __('CREATION_DATE'); ?></th>
                                            <th><?php echo __('LAST_UPDATED'); ?></th>
                                            <th><?php echo __('CREATED_BY'); ?></th>
                                            <th><?php echo __('ACTION'); ?></th>
                                        </tr>
                                    </thead>
                                    <tfoot>
                                        <tr>
                                        <th>#</th>
                                            <th>Product Name</th>
                                            <th>Sub Category</th>                                           
                                            <th>Category</th>
                                            <th>Creation date</th>
                                            <th>Last Updated</th>
                                            <th>Created by</th>
                                            <th>Action</th>
                                        </tr>
                                    </tfoot>
                                    <tbody>
<?php $query=mysqli_query($con,"select products.id as pid,products.productImage1,products.productName,category.categoryName,subcategory.subcategoryName as subcatname,products.postingDate,products.updationDate,subcategory.id as subid,tbladmin.username 
    from products 
    left join subcategory on products.subCategory=subcategory.id 
   left join category on products.category=category.id 
   left join tbladmin on tbladmin.id=products.addedBy 
    order by pid desc");
$cnt=1;
while($row=mysqli_fetch_array($query))
{
?>  

                                <tr>
                                            <td><?php echo htmlentities($cnt);?></td>
                                            <td><img src="productimages/<?php echo htmlentities($row['productImage1']);?>" width="120">

                                                <?php echo htmlentities($row['productName']);?></td>
                                            <td><?php echo htmlentities($row['categoryName']);?></td>
                                            <td><?php echo htmlentities($row['subcatname']);?></td>
                                            <td> <?php echo htmlentities($row['postingDate']);?></td>
                                            <td><?php echo htmlentities($row['updationDate']);?></td>
                                            <td><?php echo htmlentities($row['username']);?></td>
                                            <td>
                                            <a href="edit-product.php?id=<?php echo $row['pid']?>"><i class="fas fa-edit"></i></a> | 
                                            <a href="manage-products.php?id=<?php echo $row['pid']?>&del=delete" onClick="return confirm('<?php echo __('CONFIRM_DELETE_ITEM'); ?>')"><i class="fa fa-trash" aria-hidden="true"></i></a></td>
                                        </tr>
                                        <?php $cnt=$cnt+1; } ?>
                                       
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </main>
<?php include_once('includes/footer.php');?>
                </footer>
            </div>
        </div>
        <script src="js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="js/scripts.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" crossorigin="anonymous"></script>
        <script src="js/datatables-simple-demo.js"></script>
    </body>
</html>
<?php } ?>
