<?php
session_start();
include_once('includes/config.php');

echo "<h2>Restoring Correct Product Quantities</h2>";

// Correct quantities (stored in kg)
$correctQuantities = array(
    4 => 0.25,   // Ambri Apple: 250 gram
    10 => 1.0,   // Alphonso Mango: 1000 gram
    8 => 0.1,    // Blueberries: 100 gram
    3 => 0.05,   // Curry Leaves: 50 gram
    5 => 0.5,    // McIntosh apple: 500 gram
    2 => 0.25,   // Messroom: 250 gram
    7 => 6.0,    // Monthan Banana: 6000 gram
    6 => 6.0,    // Poovan Banana: 6000 gram
    1 => 0.4,    // Spinch(Palak): 400 gram
    9 => 0.1     // Strawberries: 100 gram
);

echo "<p>Restoring quantities to correct values (in kg):</p>";
foreach($correctQuantities as $id => $qty) {
    $updateQuery = mysqli_query($con, "UPDATE products SET Quantity = $qty WHERE id = $id");
    echo ($updateQuery ? "✓" : "✗") . " ID: $id = " . $qty . " kg<br>";
}

echo "<p><a href='manage-restock.php'>" . __('BACK_TO') . " " . __('MANAGE_RESTOCK') . "</a></p>";
?>
