<?php session_start();
error_reporting(0);
include_once('includes/config.php');
include_once('includes/lang.php');

// Detect first startup: check settings table and setup_completed flag
$need_setup = false;
$res = @mysqli_query($con, "SHOW TABLES LIKE 'settings'");
if (!$res || mysqli_num_rows($res) == 0) {
    $need_setup = true;
} else {
    $r2 = @mysqli_query($con, "SELECT value FROM settings WHERE name='setup_completed' LIMIT 1");
    if (!$r2 || mysqli_num_rows($r2) == 0) $need_setup = true;
    else { $row2 = mysqli_fetch_assoc($r2); if ($row2['value'] != '1') $need_setup = true; }
}

// Code for User login
if(isset($_POST['login']))
{
   $email=$_POST['emailid'];
   $password=md5($_POST['inputuserpwd']);
$query=mysqli_query($con,"SELECT id,name FROM users WHERE email='$email' and password='$password'");
$num=mysqli_fetch_array($query);
//If Login Suceesfull
if($num>0)
{
$_SESSION['login']=$_POST['email'];
$_SESSION['id']=$num['id'];
$_SESSION['username']=$num['name'];
echo "<script type='text/javascript'> document.location ='index.php'; </script>";
}
//If Login Failed
else{
    echo "<script>alert('".addslashes(__('INVALID_USERNAME_OR_PASSWORD'))."');</script>";
    echo "<script type='text/javascript'> document.location ='login.php'; </script>";
exit();
}
}
?>
<!DOCTYPE html>
<html lang="ms">
<head>
	
	<!-- title -->
	<title><?php echo __('LOGIN_TITLE'); ?> - GHP INVENTORY</title>

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
<script>
function emailAvailability() {
$("#loaderIcon").show();
jQuery.ajax({
url: "check_availability.php",
data:'email='+$("#emailid").val(),
type: "POST",
success:function(data){
$("#user-email-status").html(data);
$("#loaderIcon").hide();
},
error:function (){}
});
}
</script>
</head>
<body>
	<?php include_once('includes/header.php');?>
	<!-- end search arewa -->
	
	<!-- breadcrumb-section -->
	<div class="breadcrumb-section breadcrumb-bg">
		<div class="container">
			<div class="row">
				<div class="col-lg-8 offset-lg-2 text-center">
					<div class="breadcrumb-text">
						
						<h1><?php echo __('LOGIN_TITLE'); ?></h1>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- end breadcrumb section -->

	<!-- contact form -->
	<div class="contact-from-section mt-150 mb-150">
		<div class="container">
			<div class="row">
				<div class="col-lg-8 mb-5 mb-lg-0">
					<div class="form-title">
						<h2><?php echo __('LOGIN_PANEL'); ?></h2>
					</div>
				 	<div id="form_status"></div>
				<div class="container px-4  mt-5">
     

<form method="post" name="login">

       <div class="row mt-3">
         <div class="col-4" style="font-size:18px;font-weight: bolder;"><?php echo __('EMAIL_ID'); ?></div>
         <div class="col-8"><input type="email" name="emailid" id="emailid" class="form-control" onBlur="emailAvailability()" required>
 <span id="user-email-status" style="font-size:12px;"></span>
         </div>
          
     </div>


          <div class="row mt-3">
         <div class="col-4" style="font-size:18px;font-weight: bolder;"><?php echo __('PASSWORD'); ?></div>
         <div class="col-8"><input type="password" name="inputuserpwd" class="form-control" required>
         <small><a href="password-recovery.php"><?php echo __('FORGOT_PASSWORD'); ?></a></small></div>

     </div>

               <div class="row mt-3">
                 <div class="col-4">&nbsp;</div>
         <div class="col-6"><input type="submit" name="login" id="login" class="btn btn-primary" value="<?php echo __('LOGIN_BUTTON'); ?>" required></div>
     </div>
 </form>
              
            </div>
				</div>
			
			</div>
		</div>
	</div>
	<!-- end contact form -->

	

<?php if ($need_setup): ?>
<!-- initial setup modal -->
<div class="modal fade" id="initialSetupModal" tabindex="-1" role="dialog" aria-labelledby="setupModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="setupModalLabel"><?php echo __('SETUP_TITLE'); ?></h5>
      </div>
      <div class="modal-body">
        <p><?php echo __('SETUP_INSTRUCTION'); ?></p>
        <form method="post" action="/fvsms/scripts/save_initial_settings.php" id="initialSetupForm">
          <div class="form-group">
            <label for="languageSelect"><?php echo __('LANGUAGE'); ?></label>
            <div class="form-control" style="background:transparent;border:0;padding:6px;">Bahasa Melayu</div>
            <input type="hidden" id="languageSelect" name="language" value="ms">
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" onclick="document.getElementById('initialSetupForm').submit();"><?php echo __('SAVE_SETTINGS'); ?></button>
      </div>
    </div>
  </div>
</div>
<script>
// open modal on load (supports Bootstrap 5 and jQuery/Bootstrap 4 fallback)
document.addEventListener('DOMContentLoaded', function(){
  var el = document.getElementById('initialSetupModal');
  if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
    var myModal = new bootstrap.Modal(el, {backdrop:'static', keyboard:false});
    myModal.show();
  } else if (typeof jQuery !== 'undefined' && jQuery(el).modal) {
    jQuery(el).modal({backdrop:'static', keyboard:false, show:true});
  } else {
    // fallback: make inline visible
    el.style.display = 'block';
  }
});
</script>
<?php endif; ?>

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
	<!-- form validation js -->
	<script src="assets/js/form-validate.js"></script>
	<!-- main js -->
	<script src="assets/js/main.js"></script>
	
</body>
</html>