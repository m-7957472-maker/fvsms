<?php session_start();
include_once('includes/config.php');
error_reporting(0);
if(strlen( $_SESSION["aid"])==0)
{   
header('location:logout.php');
} else {

if(isset($_GET['del']))
{
mysqli_query($con,"delete from subcategory where id = '".$_GET['id']."'");
echo "<script>alert('" . addslashes(__('DATA_DELETED')) . "');</script>";
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
        <title><?php echo __('SITE_TITLE') . ' | ' . __('MANAGE_SUBCATEGORIES'); ?></title>
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
                        <h1 class="mt-4"><?php echo __('MANAGE_SUBCATEGORIES'); ?></h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="index.html"><?php echo __('DASHBOARD'); ?></a></li>
                            <li class="breadcrumb-item active"><?php echo __('MANAGE_SUBCATEGORIES'); ?></li>
                        </ol>
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-table me-1"></i>
                               <?php echo __('SUBCATEGORIES_DETAILS'); ?>
                            </div>
                            <div class="card-body">
                                <table id="datatablesSimple">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th><?php echo __('SUB_CATEGORY'); ?></th>
                                            <th><?php echo __('CATEGORY'); ?></th>
                                            <th><?php echo __('CREATION_DATE'); ?></th>
                                            <th><?php echo __('LAST_UPDATED'); ?></th>
                                            <th><?php echo __('CREATED_BY'); ?></th>
                                            <th><?php echo __('ACTION'); ?></th>
                                        </tr>
                                    </thead>

                                    <tbody>
<?php $query=mysqli_query($con,"select category.categoryName,subcategory.subcategoryName as subcatname,subcategory.creationDate,subcategory.updationDate,subcategory.id as subid,tbladmin.username from subcategory join category on subcategory.categoryid=category.id join tbladmin on tbladmin.id=subcategory.createdBy");
$cnt=1;
while($row=mysqli_fetch_array($query))
{
?>  

                                <tr>
                                            <td><?php echo htmlentities($cnt);?></td>
                                              <td><?php echo htmlentities($row['subcatname']);?></td>
                                            <td><?php echo htmlentities($row['categoryName']);?></td>
                                          
                                            <td> <?php echo htmlentities($row['creationDate']);?></td>
                                            <td><?php echo htmlentities($row['updationDate']);?></td>
                                            <td><?php echo htmlentities($row['username']);?></td>
                                            <td>
                                            <a href="edit-subcategory.php?id=<?php echo $row['subid']?>"><i class="fas fa-edit"></i></a> | 
                                            <a href="manage-subcategories.php?id=<?php echo $row['subid']?>&del=delete" onClick="return confirm('<?php echo __('CONFIRM_DELETE_ITEM'); ?>')"><i class="fa fa-trash" aria-hidden="true"></i></a></td>
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
