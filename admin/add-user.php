<?php session_start();
include_once(__DIR__ . '/../includes/config.php');
if(strlen($_SESSION['aid'])==0)
{   
header('location:logout.php');
} else {

$message = '';
if(isset($_POST['submit'])){
    $role = mysqli_real_escape_string($con, $_POST['role']);
    if($role == 'User'){
        $name = mysqli_real_escape_string($con, $_POST['name']);
        $email = mysqli_real_escape_string($con, $_POST['email']);
        $contact = mysqli_real_escape_string($con, $_POST['contact']);
        $rawPassword = isset($_POST['password']) ? $_POST['password'] : '';
        if(empty($name) || empty($email) || empty($rawPassword)){
            $message = "<div class='alert alert-danger'>" . __('PLEASE_FILL_USER') . "</div>";
        } else {
            $password = md5($rawPassword);
            $check = mysqli_query($con, "SELECT id FROM users WHERE email='$email'");
            if(mysqli_num_rows($check) > 0){
                $message = "<div class='alert alert-danger'>Email already registered.</div>";
            } else {
                $ins = mysqli_query($con, "INSERT INTO users(name,email,contactno,password) VALUES('$name','$email','$contact','$password')");
                if($ins){
                    $message = "<div class='alert alert-success'>User added successfully.</div>";
                } else {
                    $message = "<div class='alert alert-danger'>Error creating user.</div>";
                }
            }
        }
    } else { // Admin
        $username = mysqli_real_escape_string($con, $_POST['username']);
        $rawPassword = isset($_POST['password']) ? $_POST['password'] : '';
        if(empty($username) || empty($rawPassword)){
            $message = "<div class='alert alert-danger'>" . __('PLEASE_FILL_ADMIN') . "</div>";
        } else {
            $password = md5($rawPassword);
            $check = mysqli_query($con, "SELECT id FROM tbladmin WHERE username='$username'");
            if(mysqli_num_rows($check) > 0){
                $message = "<div class='alert alert-danger'>" . __('ADMIN_USERNAME_EXISTS') . "</div>";
            } else {
                $ins = mysqli_query($con, "INSERT INTO tbladmin(username,password) VALUES('$username','$password')");
                if($ins){
                    $message = "<div class='alert alert-success'>Admin user added successfully.</div>";
                } else {
                    $message = "<div class='alert alert-danger'>Error creating admin user.</div>";
                }
            }
        }
    }
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
        <title>GHP | Add User or Admin</title>
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
                        <h1 class="mt-4"><?php echo __('ADD_USER_ADMIN'); ?></h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="dashboard.php"><?php echo __('DASHBOARD'); ?></a></li>
                            <li class="breadcrumb-item active"><?php echo __('ADD_USER_ADMIN'); ?></li>
                        </ol>

                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-user-plus me-1"></i>
                                <?php echo __('CREATE_ACCOUNT'); ?>
                            </div>
                            <div class="card-body">
                                <?php echo $message; ?>
                                <form method="post" class="row g-3">
                                    <div class="col-md-3">
                                        <label class="form-label"><?php echo __('ROLE'); ?></label>
                                        <select name="role" id="role" class="form-control" required>
                                            <option value="User"><?php echo __('USER'); ?></option>
                                            <option value="Admin"><?php echo __('ADMIN'); ?></option>
                                        </select>
                                    </div>

                                    <div id="userFields" class="col-12 mt-3">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <label class="form-label"><?php echo __('FULL_NAME'); ?></label>
                                                <input type="text" name="name" class="form-control">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label"><?php echo __('EMAIL_ID'); ?></label>
                                                <input type="email" name="email" class="form-control">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label"><?php echo __('CONTACT_NO'); ?></label>
                                                <input type="text" name="contact" class="form-control">
                                            </div>
                                        </div>
                                    </div>

                                    <div id="adminFields" class="col-12 mt-3" style="display:none;">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label class="form-label"><?php echo __('ADMIN_USERNAME'); ?></label>
                                                <input type="text" name="username" class="form-control">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-4 mt-3">
                                        <label class="form-label">Password</label>
                                        <input type="password" name="password" class="form-control" required>
                                    </div>

                                    <div class="col-12 mt-4">
                                        <button type="submit" name="submit" class="btn btn-primary"><?php echo __('CREATE'); ?></button> 
                                        <a class="btn btn-secondary" href="registered-users.php"><?php echo __('BACK_TO') . ' ' . __('USERS'); ?></a>
                                    </div>
                                </form>
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
        <script>
            // Switch visible fields by role
            document.getElementById('role').addEventListener('change', function(e){
                var role = e.target.value;
                if(role === 'Admin'){
                    document.getElementById('userFields').style.display = 'none';
                    document.getElementById('adminFields').style.display = '';
                } else {
                    document.getElementById('userFields').style.display = '';
                    document.getElementById('adminFields').style.display = 'none';
                }
            });
        </script>
    </body>
</html>
<?php } ?>
