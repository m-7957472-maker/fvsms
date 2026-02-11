<?php
header('Content-Type: application/json; charset=utf-8');
include_once(__DIR__ . '/../includes/config.php');
$out = []; if (isset($con) && $con) {
    $res = @mysqli_query($con, "SHOW TABLES LIKE 'settings'");
    if ($res && mysqli_num_rows($res)) {
        $r = mysqli_query($con, "SELECT name,value FROM settings");
        while ($row = mysqli_fetch_assoc($r)) { $out[$row['name']] = $row['value']; }
    }
}
echo json_encode(['ok' => true, 'settings' => $out]);
