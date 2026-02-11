<?php
session_start();
include_once(__DIR__ . '/../includes/config.php');
// Stream Server-Sent Events for notifications
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
// allow long running
set_time_limit(0);
ignore_user_abort(true);

$lastId = 0;
// Determine user/admin and establish current max id to avoid sending historical backlog
$isAdmin = false;
$uid = 0;
if (isset($_SESSION['aid']) && intval($_SESSION['aid'])>0) {
    $isAdmin = true;
}
if (isset($_SESSION['id']) && intval($_SESSION['id'])>0) {
    $uid = intval($_SESSION['id']);
}

// Set lastId to the current max id for this user/admin so only new notifications are streamed
if ($isAdmin) {
    $resMax = mysqli_query($con, "SELECT COALESCE(MAX(id),0) as mx FROM notification WHERE userId = 0 OR userId > 0");
} else {
    $safeUid = intval($uid);
    $resMax = mysqli_query($con, "SELECT COALESCE(MAX(id),0) as mx FROM notification WHERE userId = $safeUid");
}
if ($resMax && $rowMax = mysqli_fetch_assoc($resMax)) {
    $lastId = intval($rowMax['mx']);
}

$start = time();
$timeout = 15; // seconds per connection
while (true) {
    if ((time() - $start) > $timeout) {
        // send a comment to keep connection alive
        echo ": ping\n\n";
        @ob_flush(); @flush();
        break;
    }

    if ($isAdmin) {
        $q = mysqli_query($con, "SELECT * FROM notification WHERE id > $lastId ORDER BY id ASC LIMIT 20");
    } else {
        $safeUid = intval($uid);
        $q = mysqli_query($con, "SELECT * FROM notification WHERE userId = $safeUid AND id > $lastId ORDER BY id ASC LIMIT 20");
    }

    if ($q && mysqli_num_rows($q) > 0) {
        while ($r = mysqli_fetch_assoc($q)) {
            $lastId = intval($r['id']);
            $data = json_encode($r);
            echo "id: $lastId\n";
            echo "event: notification\n";
            echo "data: $data\n\n";
            @ob_flush();
            @flush();
        }
        // after sending new items, stop this connection so client reconnects and picks up newer rows
        break;
    }

    // short sleep to reduce CPU while keeping low latency
    sleep(1);
}

?>
