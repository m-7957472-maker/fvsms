<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
$lang = isset($_REQUEST['lang']) ? $_REQUEST['lang'] : null;
// Language switching is disabled in Malay-only deployment. Keep session lang = 'ms'.
$_SESSION['lang'] = 'ms';
// (no DB changes allowed) -- redirect back to referrer/home below.
// Determine redirect target: allow explicit redirect but validate it's internal to avoid open-redirects
$ref = '/';
if (!empty($_REQUEST['redirect']) && strpos($_REQUEST['redirect'], '/') === 0) {
    $ref = $_REQUEST['redirect'];
} elseif (!empty($_SERVER['HTTP_REFERER'])) {
    $ref = $_SERVER['HTTP_REFERER'];
}
header('Location: ' . $ref);
exit;
?>