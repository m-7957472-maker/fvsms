<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
if (!isset($_POST['language'])) {
    header('Location: ' . (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '/'));
    exit;
}
$lang = 'ms';
// force Malay-only deployment
// try to persist into DB
include_once __DIR__ . '/../includes/config.php';
if (!$con) {
    // fallback: set session and redirect
    $_SESSION['lang'] = $lang;
    header('Location: ' . (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '/'));
    exit;
}
// create table if not exists
$sql = "CREATE TABLE IF NOT EXISTS settings (\n  name VARCHAR(100) NOT NULL PRIMARY KEY,\n  value TEXT NOT NULL\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
mysqli_query($con, $sql);
// upsert language, setup flag and default logo settings
$langEsc = mysqli_real_escape_string($con, $lang);
mysqli_query($con, "INSERT INTO settings (name,value) VALUES ('language','$langEsc') ON DUPLICATE KEY UPDATE value=VALUES(value)");
mysqli_query($con, "INSERT INTO settings (name,value) VALUES ('setup_completed','1') ON DUPLICATE KEY UPDATE value=VALUES(value)");
// default logo settings (only if not set already)
mysqli_query($con, "INSERT INTO settings (name,value) VALUES ('site_logo','logo1.png') ON DUPLICATE KEY UPDATE value=VALUES(value)");
mysqli_query($con, "INSERT INTO settings (name,value) VALUES ('logo_choice','logo1') ON DUPLICATE KEY UPDATE value=VALUES(value)");
// set session
$_SESSION['lang'] = $lang;
header('Location: ' . (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '/'));
exit;
?>