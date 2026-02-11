<?php
// Reset admin and user accounts per request
include __DIR__ . '/../includes/config.php';
$log = __DIR__ . '/reset_accounts.log';
file_put_contents($log, date('c') . " START reset_accounts\n", FILE_APPEND);
mysqli_begin_transaction($con);
try {
    // Ensure tbladmin has email column
    $res = mysqli_query($con, "SHOW COLUMNS FROM tbladmin LIKE 'email'");
    if(mysqli_num_rows($res) == 0){
        mysqli_query($con, "ALTER TABLE tbladmin ADD COLUMN email VARCHAR(255) NULL AFTER username");
        file_put_contents($log, date('c') . " Added email column to tbladmin\n", FILE_APPEND);
    } else {
        file_put_contents($log, date('c') . " tbladmin already has email column\n", FILE_APPEND);
    }

    // Update admin credentials
    $adminUser = 'Elland';
    $adminEmail = 'elland@gmail.com';
    $adminPass = md5('Elland076');
    // If there is at least one admin row, update the first one, then remove others
    $r = mysqli_query($con, "SELECT id FROM tbladmin LIMIT 1");
    if(mysqli_num_rows($r) > 0){
        $row = mysqli_fetch_assoc($r);
        $aid = intval($row['id']);
        mysqli_query($con, "UPDATE tbladmin SET username='".mysqli_real_escape_string($con,$adminUser)."', password='$adminPass', email='".mysqli_real_escape_string($con,$adminEmail)."' WHERE id=$aid");
        file_put_contents($log, date('c') . " Updated tbladmin id=$aid to $adminUser\n", FILE_APPEND);
        // delete others
        mysqli_query($con, "DELETE FROM tbladmin WHERE id<>$aid");
        file_put_contents($log, date('c') . " Deleted other tbladmin rows\n", FILE_APPEND);
    } else {
        // no admin rows: insert
        mysqli_query($con, "INSERT INTO tbladmin (username,password,email) VALUES ('".mysqli_real_escape_string($con,$adminUser)."', '$adminPass', '".mysqli_real_escape_string($con,$adminEmail)."')");
        $aid = mysqli_insert_id($con);
        file_put_contents($log, date('c') . " Inserted new tbladmin id=$aid user=$adminUser\n", FILE_APPEND);
    }

    // Users: keep only Abu
    $keepEmail = 'abu@gmail.com';
    $keepName = 'Abu';
    $keepPass = md5('Abubakar');

    // Remove all other users except keepEmail
    mysqli_query($con, "DELETE FROM users WHERE email <> '".mysqli_real_escape_string($con,$keepEmail)."'");
    file_put_contents($log, date('c') . " Deleted users not matching $keepEmail\n", FILE_APPEND);

    // Ensure Abu exists and has correct credentials
    $u = mysqli_query($con, "SELECT id FROM users WHERE email='".mysqli_real_escape_string($con,$keepEmail)."' LIMIT 1");
    if(mysqli_num_rows($u) > 0){
        $ur = mysqli_fetch_assoc($u);
        $uid = intval($ur['id']);
        mysqli_query($con, "UPDATE users SET name='".mysqli_real_escape_string($con,$keepName)."', password='$keepPass' WHERE id=$uid");
        file_put_contents($log, date('c') . " Updated existing user id=$uid to $keepName\n", FILE_APPEND);
    } else {
        // insert user
        mysqli_query($con, "INSERT INTO users (name,email,password) VALUES ('".mysqli_real_escape_string($con,$keepName)."','".mysqli_real_escape_string($con,$keepEmail)."','$keepPass')");
        $uid = mysqli_insert_id($con);
        file_put_contents($log, date('c') . " Inserted user id=$uid $keepName <$keepEmail>\n", FILE_APPEND);
    }

    mysqli_commit($con);
    file_put_contents($log, date('c') . " COMMIT\n", FILE_APPEND);
    echo "Done. Admin id=$aid, user id=$uid\n";
} catch (Exception $e){
    mysqli_rollback($con);
    file_put_contents($log, date('c') . " ROLLBACK: " . $e->getMessage() . "\n", FILE_APPEND);
    echo "Error: " . $e->getMessage() . "\n";
}
?>