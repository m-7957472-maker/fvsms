<?php
include __DIR__ . '/../includes/config.php';
header('Content-Type: text/plain');
$id = intval($_GET['id'] ?? 43);
$res = mysqli_query($con, "SELECT * FROM orders WHERE id=$id LIMIT 1");
if (!$res) { echo "ERROR: " . mysqli_error($con) . "\n"; exit; }
$r = mysqli_fetch_assoc($res);
if (!$r) { echo "NOT FOUND\n"; exit; }
print_r($r);
