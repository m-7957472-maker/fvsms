<?php
// Diagnostics page for fvsms
error_reporting(E_ALL);
ini_set('display_errors', 1);
include_once('includes/config.php');

function h($s){ return htmlspecialchars($s); }
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>FVSMS Diagnostics</title>
<style>body{font-family:Arial,Helvetica,sans-serif;padding:20px} pre{background:#f6f8fa;border:1px solid #ddd;padding:10px;overflow:auto} .ok{color:green}.bad{color:red}</style>
</head>
<body>
<h1>FVSMS Diagnostics</h1>
<h2>1) PHP & environment</h2>
<p>PHP version: <?php echo phpversion(); ?></p>

<h2>2) Database connection</h2>
<?php
if (isset($con) && $con){
    echo '<p class="ok">Connected to DB: ' . h(DB_NAME) . '</p>';
    $res = mysqli_query($con, "SHOW TABLES");
    if ($res){
        echo '<h3>Tables</h3><ul>';
        $tables = [];
        while ($r = mysqli_fetch_row($res)){
            $tables[] = $r[0];
            echo '<li>' . h($r[0]) . '</li>';
        }
        echo '</ul>';

        $checkTables = ['products','users','cart','wishlist','notification','tbladmin','category','subcategory'];
        echo '<h3>Table existence</h3><ul>';
        // do a case-insensitive check against the listed tables
        $lowerTables = array_map('strtolower', $tables);
        foreach ($checkTables as $t){
            $exists = in_array(strtolower($t), $lowerTables);
            echo '<li>' . h($t) . ': ' . ($exists ? '<span class="ok">exists</span>' : '<span class="bad">missing</span>') . '</li>';
        }
        echo '</ul>';

        if (in_array('NOTIFICATION',$tables)){
            echo '<h3>NOTIFICATION recent rows</h3>';
            $r2 = mysqli_query($con, "SELECT id,userId,productId,qty,unit,created_at FROM NOTIFICATION ORDER BY id DESC LIMIT 20");
            if ($r2 && mysqli_num_rows($r2)>0){
                echo '<table border="1" cellpadding="6"><tr><th>id</th><th>userId</th><th>productId</th><th>qty(kg)</th><th>unit</th><th>created_at</th></tr>';
                while ($row = mysqli_fetch_assoc($r2)){
                    echo '<tr><td>' . h($row['id']) . '</td><td>' . h($row['userId']) . '</td><td>' . h($row['productId']) . '</td><td>' . h($row['qty']) . '</td><td>' . h($row['unit']) . '</td><td>' . h($row['created_at']) . '</td></tr>';
                }
                echo '</table>';
            } else { echo '<p>No rows found in NOTIFICATION.</p>'; }
        }

        // sample counts
        echo '<h3>Row counts</h3><ul>';
        foreach (['products','users','cart','wishlist'] as $t){
            if (in_array($t,$tables)){
                $c = mysqli_fetch_row(mysqli_query($con, "SELECT COUNT(*) FROM $t"))[0];
                echo '<li>' . h($t) . ': ' . h($c) . '</li>';
            }
        }
        echo '</ul>';

    } else {
        echo '<p class="bad">Could not list tables: ' . h(mysqli_error($con)) . '</p>';
    }
} else {
    echo '<p class="bad">DB connection not available.</p>';
}
?>

<h2>3) Inventory errors log (inventory_errors.log)</h2>
<pre><?php
$errfile = __DIR__ . '/inventory_errors.log';
if (file_exists($errfile)){
    echo h( file_get_contents($errfile) );
} else echo 'No inventory_errors.log found.';
?></pre>

<h2>4) Apache/PHP error log attempt</h2>
<p>This attempts to read the XAMPP apache error log (may be blocked by permissions).</p>
<pre><?php
$paths = [
    'C:/xampp/apache/logs/error.log',
    'C:/xampp/apache/logs/error.log.1',
    '/var/log/apache2/error.log'
];
$found=false;
foreach($paths as $p){
    if (file_exists($p)){
        echo "-- Log: $p --\n" . h(file_get_contents($p));
        $found=true; break;
    }
}
if (!$found) echo 'No apache error log readable from PHP.';
?></pre>

<h2>5) Quick product sample (first 10)</h2>
<pre><?php
if (isset($con) && $con){
    $r = mysqli_query($con, "SELECT id,productName,Quantity FROM products LIMIT 10");
    if ($r){
        while($p = mysqli_fetch_assoc($r)){
            echo sprintf("%d | %s | %s\n", $p['id'], $p['productName'], $p['Quantity']);
        }
    } else echo 'Could not query products: ' . mysqli_error($con);
}
?></pre>

</body>
</html>