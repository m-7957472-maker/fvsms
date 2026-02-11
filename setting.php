<?php session_start();
include_once('includes/config.php');
if(strlen($_SESSION['id'])==0)
{   header('location:logout.php');
}else{

//For changing  User  Profile Password
if(isset($_POST['update']))
{
$currentpwd=md5($_POST['cpass']);
$newpwd=md5($_POST['newpass']);
$uid=$_SESSION['id'];
$sql=mysqli_query($con,"SELECT id FROM  users where password='$currentpwd' and id='$uid'");
$num=mysqli_num_rows($sql);
if($num>0)
{
 $con=mysqli_query($con,"update users set password='$newpwd' where id='$uid'");
echo "<script>alert('Password Changed Successfully !!');</script>";
 echo "<script type='text/javascript'> document.location ='setting.php'; </script>";
}else{
    echo "<script>alert('Current Password not match !!');</script>";
     echo "<script type='text/javascript'> document.location ='setting.php'; </script>";
}
}
?>
<!DOCTYPE html>
<html lang="ms">
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="Responsive Bootstrap4 Shop Template, Created by Imran Hossain from https://imransdesign.com/">

	<!-- title -->
	<title>GHP INVENTORY MANAGEMENT SYSTEM || <?php echo __('CHANGE_PASSWORD'); ?></title>

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
if(document.chngpwd.cpass.value=="")
{
alert("<?php echo addslashes(__('CURRENT_PASSWORD_REQUIRED')); ?>");
document.chngpwd.cpass.focus();
return false;
}
else if(document.chngpwd.newpass.value=="")
{
alert("<?php echo addslashes(__('NEW_PASSWORD_REQUIRED')); ?>");
document.chngpwd.newpass.focus();
return false;
}
else if(document.chngpwd.cnfpass.value=="")
{
alert("<?php echo addslashes(__('CONFIRM_PASSWORD_REQUIRED')); ?>");
document.chngpwd.cnfpass.focus();
return false;
}
else if(document.chngpwd.newpass.value!= document.chngpwd.cnfpass.value)
{
alert("<?php echo addslashes(__('PASSWORD_MISMATCH')); ?>");
document.chngpwd.cnfpass.focus();
return false;
}
return true;
}
</script>
</head>
<body>
	
	<?php include_once('includes/header.php'); ?>
	
	<!-- breadcrumb-section -->
	<div class="breadcrumb-section breadcrumb-bg">
		<div class="container">
			<div class="row">
				<div class="col-lg-8 offset-lg-2 text-center">
					<div class="breadcrumb-text">
						
						<h1><?php echo __('CHANGE_PASSWORD'); ?></h1>
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
						<h2><?php echo __('CHANGE_PASSWORD'); ?></h2>
						
					</div>
					<div class="contact-form-box">
							<form method="post" name="chngpwd" onSubmit="return valid();">
     <div class="row">
         <div class="col-4"><?php echo __('CURRENT_PASSWORD'); ?></div>
         <div class="col-8">    
            <input type="password" class="form-control" id="cpass" name="cpass" required="required"></div>
     </div>
       <div class="row mt-3">
         <div class="col-4"><?php echo __('NEW_PASSWORD'); ?></div>
         <div class="col-8">
     <input type="password" class="form-control" id="newpass" name="newpass">
         </div>
          
     </div>

       <div class="row mt-3">
         <div class="col-4"><?php echo __('CONFIRM_PASSWORD'); ?></div>
         <div class="col-8"><input type="password" class="form-control" id="cnfpass" name="cnfpass" required="required" ></div>
     </div>



               <div class="row mt-3">
                 <div class="col-4">&nbsp;</div>
         <div class="col-6"><input type="submit" name="update" id="update" class="btn btn-primary" value="<?php echo __('UPDATE'); ?>" required></div>
     </div>
 </form>
						</div>
						
				 	<div id="form_status"></div>
				
				</div>
			
			</div>
		</div>
	</div>
	<!-- end contact form -->

	


	<?php include_once('includes/footer.php'); ?>
	
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
</html><?php } ?>