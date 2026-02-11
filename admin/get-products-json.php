<?php 
session_start();
include_once('includes/config.php');

if(strlen($_SESSION["aid"]) == 0) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$all = isset($_GET['all']) ? intval($_GET['all']) : 0;
$products = [];

if ($all) {
    $query = mysqli_query($con, "SELECT id, productName FROM products ORDER BY productName ASC");
} else {
    $ids = isset($_GET['ids']) ? $_GET['ids'] : '';
    if (!$ids) {
        echo json_encode([]);
        exit;
    }
    $idArray = array_map('intval', explode(',', $ids));
// QR product JSON endpoint removed. Return empty array.
header('Content-Type: application/json');
echo json_encode([]);
exit;
while($row = mysqli_fetch_array($query)) {
