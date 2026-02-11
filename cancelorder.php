<?php
session_start();
error_reporting(0);
include_once('includes/config.php');
if(isset($_POST['submit']))
  {
    
   $orderid=$_GET['oid'];
    $ressta="Cancelled";
    $remark=$_POST['restremark'];
    $canclbyuser='User';
 
  
    $query=mysqli_query($con,"insert into ordertrackhistory(orderId,remark,status,canceledBy) value('$orderid','$remark','$ressta','$canclbyuser')"); 
   // check current status to avoid double-revert
   $hasOrderStatus = false;
   $hasStatus = false;
   $c1 = @mysqli_query($con, "SHOW COLUMNS FROM `orders` LIKE 'orderStatus'");
   if ($c1 && mysqli_num_rows($c1)) { $hasOrderStatus = true; }
   $c2 = @mysqli_query($con, "SHOW COLUMNS FROM `orders` LIKE 'status'");
   if ($c2 && mysqli_num_rows($c2)) { $hasStatus = true; }

   $selectCols = 'orderNumber';
   if ($hasOrderStatus) $selectCols .= ', orderStatus';
   if ($hasStatus) $selectCols .= ', status';

   $curQ = mysqli_query($con, "SELECT $selectCols FROM orders WHERE id='$orderid' LIMIT 1");
   $cur = mysqli_fetch_assoc($curQ);
   $already = false;
   if ($cur) {
     $currentStatus = '';
     if ($hasOrderStatus && isset($cur['orderStatus'])) $currentStatus = $cur['orderStatus'];
     else if ($hasStatus && isset($cur['status'])) $currentStatus = $cur['status'];
     if (strtolower($currentStatus) === 'cancelled') $already = true;
     $ordernumber = $cur['orderNumber'];
   } else {
     $ordernumber = null;
   }

   $query=mysqli_query($con, "update orders set orderStatus='$ressta', status='$ressta' where id='$orderid'");
  if ($query) {
    // if not already cancelled, revert product quantities back
    if (!$already && $ordernumber) {
      // track restored amounts per product to avoid double-restores
      $restored = array();

      // get ordered items; some installs use ordersdetails.orderNumber
      $rd = mysqli_query($con, "SELECT productId, quantity FROM ordersdetails WHERE orderNumber='".mysqli_real_escape_string($con,$ordernumber)."'");
      if ($rd) {
        while ($it = mysqli_fetch_assoc($rd)) {
          $pid = intval($it['productId']);
          $q = floatval($it['quantity']);
          // add back the quantity
          mysqli_query($con, "UPDATE products SET Quantity = Quantity + $q WHERE id='$pid'");
          if (!isset($restored[$pid])) $restored[$pid] = 0;
          $restored[$pid] += $q;
        }
      }
      // also revert any flat items table, but only add missing amount
      $rd2 = @mysqli_query($con, "SELECT productId, quantity FROM order_items_flat WHERE orderNumber='".mysqli_real_escape_string($con,$ordernumber)."'");
      if ($rd2) {
        while ($it2 = mysqli_fetch_assoc($rd2)) {
          $pid2 = intval($it2['productId']);
          $q2 = floatval($it2['quantity']);
          $already = isset($restored[$pid2]) ? floatval($restored[$pid2]) : 0;
          $toAdd = max(0, $q2 - $already);
          if ($toAdd <= 0) continue;
          mysqli_query($con, "UPDATE products SET Quantity = Quantity + $toAdd WHERE id='$pid2'");
          if (!isset($restored[$pid2])) $restored[$pid2] = 0;
          $restored[$pid2] += $toAdd;
        }
      }
    }
echo '<script>alert("'. addslashes(__('ORDER_CANCELLED')) .'")</script>';
  }else{
echo '<script>alert("'. addslashes(__('SOMETHING_WENT_WRONG')) .'")</script>';
  }

  
}

 ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title> Order Cancelation</title>
</head>
<body>

<div style="margin-left:50px;">
<?php  
$orderid=$_GET['oid'];
$query=mysqli_query($con,"select orderNumber,orderStatus from orders where id='$orderid'");
$num=mysqli_num_rows($query);
$cnt=1;
?>
<?php  
while ($row=mysqli_fetch_array($query)) {
  ?>
<table border="1"  cellpadding="10" style="border-collapse: collapse; border-spacing:0; width: 100%; text-align: center;">
  <tr align="center">
   <th colspan="4" ><?php echo __('CANCEL_ORDER') . ' #' . htmlentities($row['orderNumber']);?></th> 
  </tr>
  <tr>
<th><?php echo __('ORDER_NUMBER'); ?> </th>
<th><?php echo __('CURRENT_STATUS'); ?> </th>
</tr>

<tr> 
  <td><?php  echo $row['orderNumber'];?></td> 
   <td><?php  $status=$row['orderStatus'];
if($status==""){
  echo __('WAITING_FOR_CONFIRMATION');
} else { 
echo $status;
}
?></td> 
</tr>
<?php 
} ?>

</table>
     <?php if($status=="" || $status=="Packed" || $status=="Dispatched" || $status=="In Transit") {?>
<form method="post">
      <table>
        <tr>
          <th><?php echo __('REASON_FOR_CANCEL'); ?></th>
<td>    <textarea name="restremark" placeholder="" rows="12" cols="50" class="form-control wd-450" required="true"></textarea></td>
        </tr>
<tr>
  <td colspan="2" align="center"><button type="submit" name="submit" class="btn btn--box btn--small btn--blue btn--uppercase btn--weight"><?php echo __('UPDATE'); ?></button></td>

</tr>
      </table>

</form>
    <?php } else { ?>
<?php if($status=='Cancelled'){?>
<p style="color:red; font-size:20px;"> <?php echo __('ORDER_ALREADY_CANCELLED'); ?></p>
<?php } else { ?>
  <p style="color:red; font-size:20px;"> <?php echo __('CANNOT_CANCEL_OUT_FOR_DELIVERY'); ?></p>

<?php }  } ?>
  
</div>

</body>
</html>

     