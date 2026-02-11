<?php session_start();
include_once(__DIR__ . '/../includes/config.php');
if(strlen( $_SESSION["aid"])==0)
{   
header('location:logout.php');
} else {

// normalize GET vars to avoid undefined index warnings
$del = isset($_GET['del']) ? $_GET['del'] : null;
$delId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($del !== null && $delId > 0) {
    $uid = intval($delId);
    // delete user if exists
    mysqli_query($con, "DELETE FROM users WHERE id = '" . intval($uid) . "'");
    echo "<script>alert('" . addslashes(__('USER_DELETED')) . "');</script>";
    echo "<script>window.location.href='registered-users.php'</script>";
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
        <title>GHP INVENTORY MANAGEMENT SYSTEM | <?php echo __('MANAGE_REGISTERED_USERS'); ?></title>
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
                        <h1 class="mt-4"><?php echo __('MANAGE_REGISTERED_USERS'); ?></h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="dashboard.php"><?php echo __('DASHBOARD'); ?></a></li>
                            <li class="breadcrumb-item active"><?php echo __('MANAGE_REGISTERED_USERS'); ?></li>
                        </ol>
                        <div class="card mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <div><i class="fas fa-table me-1"></i> <?php echo __('USERS_DETAILS'); ?></div>
                                <div><a href="add-user.php" class="btn btn-success btn-sm"><?php echo __('ADD_USER_ADMIN'); ?></a></div>
                            </div>
                            <div class="card-body">
                                <table id="datatablesSimple">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th><?php echo __('NAME'); ?></th>
                                            <th><?php echo __('EMAIL_ID'); ?></th>
                                            <th><?php echo __('CONTACT_NO'); ?></th>
                                            <th><?php echo __('REG_DATE'); ?></th>
                                            <th><?php echo __('LAST_UPDATION'); ?></th>
                                            <th><?php echo __('ACTION'); ?></th>
                                        </tr>
                                    </thead>
                               
                                    <tbody>
<?php $query=mysqli_query($con,"select * from users");
$cnt=1;
while($row=mysqli_fetch_array($query))
{
?>  

                                <tr>
                                            <td><?php echo htmlentities($cnt);?></td>
                                            <td><?php echo htmlentities($row['name'] ?? '');?></td>
                                            <td><?php echo htmlentities($row['email'] ?? '');?></td>
                                            <td> <?php echo htmlentities($row['contactno'] ?? '');?></td>
                                            <td><?php echo htmlentities($row['regDate'] ?? '');?></td>
                                            <td><?php echo htmlentities($row['updationDate'] ?? '');?></td>
                                            <td>
                                            <a href="user-orders.php?uid=<?php echo $row['id']?>&&uname=<?php echo urlencode($row['name'] ?? '');?>" target="_blank" class="btn btn-primary btn-sm"><?php echo __('VIEW_ORDERS'); ?></a> </td>
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
