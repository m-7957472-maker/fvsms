<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
// Force Bahasa Melayu for the entire application (single-language deployment)
$lang = 'ms';
// persist in session to avoid accidental overrides
$_SESSION['lang'] = 'ms';

// load translation file (only ms supported)
// Prefer a safe fallback during debugging to avoid fatal parse errors
$defaultTrans = __DIR__ . "/lang/ms.php";
// load translation file (only ms supported)
// Use the original Malay translation file by default to ensure Bahasa Melayu is shown
$transFile = __DIR__ . "/lang/ms.php"; // force ms.php (full Malay translations)

$translations = [];
if (file_exists($transFile)) {
    $translations = include $transFile;
} else {
    // fallback: empty translations to avoid fatal errors
    $translations = array();
}

// load site settings from DB (name => value)
$site_settings = [];
if (isset($con) && $con) {
    $res = @mysqli_query($con, "SHOW TABLES LIKE 'settings'");
    if ($res && mysqli_num_rows($res)) {
        $r = mysqli_query($con, "SELECT name,value FROM settings");
        if ($r) {
            while ($row = mysqli_fetch_assoc($r)) {
                $site_settings[$row['name']] = $row['value'];
            }
        }
    }
}

function __($key) {
    global $translations;
    return isset($translations[$key]) ? $translations[$key] : $key;
}

?>