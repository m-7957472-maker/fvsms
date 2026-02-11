<?php
session_start();
include_once(__DIR__ . '/../includes/config.php');
error_reporting(0);

$log = [];
$log[] = "time=".date('c');
$log[] = 'remote_addr=' . ($_SERVER['REMOTE_ADDR'] ?? 'NONE');
$log[] = 'session_id=' . session_id();
$log[] = 'session_uid=' . (isset($_SESSION['id']) ? $_SESSION['id'] : 'NONE');
$log[] = 'post_keys=' . implode(',', array_keys($_POST));
$log[] = 'post_raw=' . @file_get_contents('php://input');
$log[] = 'x_requested_with=' . (isset($_SERVER['HTTP_X_REQUESTED_WITH']) ? $_SERVER['HTTP_X_REQUESTED_WITH'] : 'NONE');
@file_put_contents(__DIR__ . '/addtocart_debug.log', implode("\n", $log) . "\n----\n", FILE_APPEND);

$isAjax = isset($_POST['ajax_addtocart']) || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

$pid = isset($_POST['pid']) ? intval($_POST['pid']) : 0;
$pqty = isset($_POST['inputQuantity']) ? $_POST['inputQuantity'] : (isset($_POST['qty']) ? $_POST['qty'] : 0);
$pqty = floatval($pqty);
if ($pqty <= 0) $pqty = 1;

if (strlen($_SESSION['id']) == 0) {
    $msg = 'Sila log masuk untuk menambah ke troli';
    @file_put_contents(__DIR__ . '/addtocart_debug.log', date('c') . " - LOGIN_REQUIRED\n", FILE_APPEND);
    if ($isAjax) { header('Content-Type: application/json'); echo json_encode(['success'=>false,'message'=>$msg]); exit; }
    echo "<script>alert('".addslashes($msg)."');</script>";
    echo "<script type='text/javascript'> window.location = '/fvsms/login.php'; </script>";
    exit;
}

$userid = $_SESSION['id'];
@file_put_contents(__DIR__ . '/addtocart_debug.log', date('c') . " - COMPUTED: userid={$userid} pid={$pid} pqty={$pqty}\n", FILE_APPEND);

$selectSql = "SELECT id, productQty FROM cart WHERE userId='".$userid."' AND productId='".$pid."'";
$q = mysqli_query($con, $selectSql);
if ($q === false) {
    @file_put_contents(__DIR__ . '/addtocart_debug.log', date('c') . " - SELECT failed: " . mysqli_error($con) . " -- SQL: $selectSql\n", FILE_APPEND);
    $count = 0;
} else {
    $count = mysqli_num_rows($q);
    @file_put_contents(__DIR__ . '/addtocart_debug.log', date('c') . " - SELECT ok: rows=$count\n", FILE_APPEND);
}

if ($count == 0) {
    $insertSql = "INSERT INTO cart(userId,productId,productQty) VALUES('".$userid."','".$pid."','".$pqty."')";
    $res = mysqli_query($con, $insertSql);
    if (!$res) {
        @file_put_contents(__DIR__ . '/addtocart_debug.log', date('c') . " - INSERT failed: " . mysqli_error($con) . " -- SQL: $insertSql\n", FILE_APPEND);
        $msg = 'Ralat pelayan semasa menambah ke troli';
        if ($isAjax) { header('Content-Type: application/json'); echo json_encode(['success'=>false,'message'=>$msg]); exit; }
        echo "<script>alert('".addslashes($msg)."');</script>";
        echo "<script type='text/javascript'> window.location = '/fvsms/product-details.php?pid={$pid}'; </script>";
        exit;
    } else {
        @file_put_contents(__DIR__ . '/addtocart_debug.log', date('c') . " - INSERT ok: user={$userid} pid={$pid} qty={$pqty}\n", FILE_APPEND);
        $msg = 'Produk berjaya ditambah ke troli';
        if ($isAjax) { header('Content-Type: application/json'); echo json_encode(['success'=>true,'message'=>$msg,'redirect'=>'my-cart.php']); exit; }
        header('Location: /fvsms/my-cart.php');
        exit;
    }
} else {
    $row = mysqli_fetch_array($q);
    $current = $row['productQty'];
    $newQty = $current + $pqty;
    $updateSql = "UPDATE cart SET productQty='".$newQty."' WHERE userId='".$userid."' AND productId='".$pid."'";
    $res2 = mysqli_query($con, $updateSql);
    if (!$res2) {
        @file_put_contents(__DIR__ . '/addtocart_debug.log', date('c') . " - UPDATE failed: " . mysqli_error($con) . " -- SQL: $updateSql\n", FILE_APPEND);
        $msg = 'Ralat pelayan semasa mengemaskini troli';
        if ($isAjax) { header('Content-Type: application/json'); echo json_encode(['success'=>false,'message'=>$msg]); exit; }
        echo "<script>alert('".addslashes($msg)."');</script>";
        echo "<script type='text/javascript'> window.location = '/fvsms/product-details.php?pid={$pid}'; </script>";
        exit;
    } else {
        @file_put_contents(__DIR__ . '/addtocart_debug.log', date('c') . " - UPDATE ok: user={$userid} pid={$pid} qty={$newQty}\n", FILE_APPEND);
        $msg = 'Produk berjaya dikemas kini dalam troli';
        if ($isAjax) { header('Content-Type: application/json'); echo json_encode(['success'=>true,'message'=>$msg,'redirect'=>'my-cart.php']); exit; }
        header('Location: /fvsms/my-cart.php');
        exit;
    }
}
