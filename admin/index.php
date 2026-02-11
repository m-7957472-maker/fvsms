v<?php
session_start();
//error_reporting(0);
include("includes/config.php");
include_once('../includes/lang.php');

// Detect first startup using admin DB connection
$need_setup = false;
$res = @mysqli_query($con, "SHOW TABLES LIKE 'settings'");
if (!$res || mysqli_num_rows($res) == 0) {
    $need_setup = true;
} else {
    $r2 = @mysqli_query($con, "SELECT value FROM settings WHERE name='setup_completed' LIMIT 1");
    if (!$r2 || mysqli_num_rows($r2) == 0) $need_setup = true;
    else { $row2 = mysqli_fetch_assoc($r2); if ($row2['value'] != '1') $need_setup = true; }
}
if(isset($_POST['submit']))
{
$username=$_POST['username'];
$password=md5($_POST['inputPassword']);
$ret=mysqli_query($con,"SELECT id FROM tbladmin WHERE username='$username' and password='$password'");
$num=mysqli_fetch_array($ret);
if($num>0)
{
$_SESSION['alogin']=$_POST['username'];
$_SESSION['aid']=$num['id'];
header("location:dashboard.php");
}else{
echo "<script>alert('".addslashes(__('INVALID_USERNAME_OR_PASSWORD'))."');</script>";
//header("location:index.php");
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
        <title><?php echo __('ADMIN_LOGIN_TITLE'); ?></title>
        <link href="css/styles.css" rel="stylesheet" />
        <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js" crossorigin="anonymous"></script>
    </head>
    <body class="bg-primary">
        <div id="layoutAuthentication">
            <div id="layoutAuthentication_content">
                <main>
                    <div class="container">
                        <div class="row justify-content-center">
                            <div class="col-lg-5">
                                <div class="card shadow-lg border-0 rounded-lg mt-5">
                                    <div class="card-header"><h3 class="text-center font-weight-light my-4"><?php echo __('ADMIN_LOGIN_TITLE'); ?></h3></div>
                                    <div class="card-body">
                                        <form method="post">
                                            <div class="form-floating mb-3">
                                                <input class="form-control" id="username" name="username" type="text" placeholder="<?php echo __('USERNAME'); ?>" required />
                                                <label for="username"><?php echo __('USERNAME'); ?></label>
                                            </div>
                                            <div class="form-floating mb-3">
                                                <input class="form-control" id="inputPassword" name="inputPassword" type="password" placeholder="<?php echo __('PASSWORD'); ?>" required />
                                                <label for="inputPassword"><?php echo __('PASSWORD'); ?></label>
                                            </div>
                                        
                                            <div class="d-flex align-items-center justify-content-between mt-4 mb-0">
                                                <a class="small" href="password-recovery.php">Forgot Password?</a>
                                                <button type="submit" name="submit" class="btn btn-primary"><?php echo __('LOGIN_BUTTON'); ?></button> 
                                            </div>
                                        </form>
                                    </div>
                              <div class="card-footer text-center py-3">
                                        <div class="small"><a href="../index.php"><?php echo __('BACK_TO') . ' ' . __('HOME'); ?></a></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </main>
            </div>
            <div id="layoutAuthentication_footer">
<?php if ($need_setup): ?>
<!-- initial setup modal (admin) -->
<div class="modal fade" id="initialSetupModalAdmin" tabindex="-1" role="dialog" aria-labelledby="setupModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="setupModalLabel"><?php echo __('SETUP_TITLE'); ?></h5>
      </div>
      <div class="modal-body">
        <p><?php echo __('SETUP_INSTRUCTION'); ?></p>
        <form method="post" action="/fvsms/scripts/save_initial_settings.php" id="initialSetupFormAdmin">
          <div class="form-group">
            <label for="languageSelectAdmin"><?php echo __('LANGUAGE'); ?></label>
            <div class="form-control" style="background:transparent;border:0;padding:6px;">Bahasa Melayu</div>
            <input type="hidden" id="languageSelectAdmin" name="language" value="ms">
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" onclick="document.getElementById('initialSetupFormAdmin').submit();"><?php echo __('SAVE_SETTINGS'); ?></button>
      </div>
    </div>
  </div>
</div>
<script>
// open modal on load (admin)
document.addEventListener('DOMContentLoaded', function(){
  var el = document.getElementById('initialSetupModalAdmin');
  if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
    var myModal = new bootstrap.Modal(el, {backdrop:'static', keyboard:false});
    myModal.show();
  } else if (typeof jQuery !== 'undefined' && jQuery(el).modal) {
    jQuery(el).modal({backdrop:'static', keyboard:false, show:true});
  } else {
    el.style.display = 'block';
  }
});
</script>
<?php endif; ?>
                <?php include_once('includes/footer.php');?>
            </div>
        </div>
        <script src="js/bootstrap.bundle.min.js"></script>
        <script src="js/scripts.js"></script>
    </body>
</html>
