<?php
session_start();
include_once(__DIR__ . '/../includes/config.php');
header('Content-Type: application/json; charset=utf-8');
$ids = isset($_GET['ids']) ? trim($_GET['ids']) : '';
$out = ['ok' => false, 'names' => []];
if ($ids === '') {
    echo json_encode($out);
    exit;
}
$parts = array_filter(array_map('intval', explode(',', $ids)));
if (empty($parts)) { echo json_encode($out); exit; }
$in = implode(',', $parts);
$q = mysqli_query($con, "SELECT id, productName FROM products WHERE id IN ($in)");
while ($r = mysqli_fetch_assoc($q)) {
    $out['names'][intval($r['id'])] = $r['productName'];
}
$out['ok'] = true;
echo json_encode($out);
exit;
?>