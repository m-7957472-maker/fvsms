<?php
session_start();
include_once(__DIR__ . '/../includes/config.php');
header('Content-Type: application/json; charset=utf-8');

$ids = isset($_GET['ids']) ? trim($_GET['ids']) : '';
$result = [];
if ($ids !== '') {
    // sanitize comma separated integers
    $parts = array_filter(array_map('trim', explode(',', $ids)));
    $ints = array_map('intval', $parts);
    $uniq = array_unique($ints);
    if (count($uniq) > 0) {
        $in = implode(',', $uniq);
        $q = mysqli_query($con, "SELECT id, Quantity FROM products WHERE id IN ($in)");
        if ($q) {
            while ($r = mysqli_fetch_assoc($q)) {
                $result[intval($r['id'])] = floatval($r['Quantity']);
            }
        }
    }
}

echo json_encode(['quantities' => $result]);
exit;
?>
