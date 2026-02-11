<?php session_start();
error_reporting(0);
include_once('includes/config.php');
// Code for User login
if(isset($_POST['submit']))
{
$username=$_POST['emailid'];
$cnumber=$_POST['phoneno'];
$newpassword=md5($_POST['inputPassword']);
$ret=mysqli_query($con,"SELECT id FROM users WHERE email='$username' and contactno='$cnumber'");
$num=mysqli_num_rows($ret);
if($num>0)
{
$query=mysqli_query($con,"update users set password='$newpassword' WHERE email='$username' and contactno='$cnumber'");

echo "<script>alert('Password reset successfully.');</script>";
echo "<script type='text/javascript'> document.location ='login.php'; </script>";
}else{
echo "<script>alert('".addslashes(__('INVALID_EMAIL_OR_CONTACT'))."');</script>";
echo "<script type='text/javascript'> document.location ='password-recovery.php'; </script>";
}
}
?>
<!DOCTYPE html>
<html lang="ms">
<head>
	
	<!-- title -->
	<title>GHP INVENTORY MANAGEMENT SYSTEM || Forgot Password</title>

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
 <script type="text/javascript">
function valid()
{
 if(document.passwordrecovery.inputPassword.value!= document.passwordrecovery.cinputPassword.value)
{
alert("<?php echo addslashes(__('PASSWORD_MISMATCH')); ?>");
document.passwordrecovery.cinputPassword.focus();
return false;
}
return true;
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
						
						<h1><?php echo __('FORGOT_PASSWORD'); ?></h1>
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
						<h2><?php echo __('FORGOT_PASSWORD'); ?></h2>
					</div>
				 	<div id="form_status"></div>
				<div class="container px-4  mt-5">
     

<form method="post" name="passwordrecovery" onSubmit="return valid();">

       <div class="row mt-3">
         <div class="col-4" style="font-size:18px;font-weight: bolder;">Email Id</div>
         <div class="col-8"><input type="email" name="emailid" id="emailid" class="form-control"  required>
 <span id="user-email-status" style="font-size:12px;"></span>
         </div>
          
     </div>


          <div class="row mt-3">
         <div class="col-4" style="font-size:18px;font-weight: bolder;">Reg. Contact No</div>
         <div class="col-8"><input type="text" name="phoneno" id="phoneno" class="form-control" required>
         </div>

     </div>
        <div class="row mt-3">
         <div class="col-4" style="font-size:18px;font-weight: bolder;">Password</div>
         <div class="col-8"><input type="password" name="inputPassword" id="inputPassword" class="form-control" required>
         </div>

     </div>
        <div class="row mt-3">
         <div class="col-4" style="font-size:18px;font-weight: bolder;">Password Recovery</div>
         <div class="col-8"><input type="password" name="cinputPassword" id="cinputPassword" class="form-control" required>
         </div>

     </div>

               <div class="row mt-3">
                 <div class="col-4">&nbsp;</div>
         <div class="col-6"><input type="submit" name="submit" id="submit" class="btn btn-primary" value="<?php echo __('SUBMIT'); ?>" required></div>
     </div>
 </form>
              
            </div>
				</div>
			
			</div>
		</div>
	</div>
	<!-- end contact form -->

	

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