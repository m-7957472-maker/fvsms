<?php
include __DIR__ . '/../includes/config.php';
header('Content-Type: text/plain');
$res = mysqli_query($con, "SELECT id, orderNumber, orderDate, orderStatus FROM orders ORDER BY id DESC LIMIT 20");
if (!$res) { echo "ERROR: " . mysqli_error($con) . "\n"; exit; }
while ($r = mysqli_fetch_assoc($res)) {
    echo sprintf("ID=%d NUMBER=%s DATE=%s STATUS=%s\n", $r['id'], $r['orderNumber'], $r['orderDate'], $r['orderStatus']);
}
