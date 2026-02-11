<?php
define('DB_SERVER','localhost');
define('DB_USER','root');
define('DB_PASS' ,'');
define('DB_NAME', 'fvsmsdb');
$con = mysqli_connect(DB_SERVER,DB_USER,DB_PASS,DB_NAME);
// Check connection
if (mysqli_connect_errno())
{
 echo "Failed to connect to MySQL: " . mysqli_connect_error();
}

// Helper function to format quantity nicely (no decimals)
if (!function_exists('formatQuantity')) {
    /**
     * Format quantity.
     * If $showUnit = true: returns "X kg" or "Y gram" as before.
     * If $showUnit = false: return integer count (no unit).
     */
    function formatQuantity($quantityInKg, $showUnit = true) {
        if (!$showUnit) {
            // show as integer count (no unit)
            return (string)round(floatval($quantityInKg));
        }
        $grams = floatval($quantityInKg) * 1000;
        if ($grams >= 1000 && fmod($grams, 1000) == 0) {
            return round($grams / 1000) . " kg";
        } else {
            return round($grams) . " gram";
        }
    }
}

// Helper to format a numeric quantity without unnecessary trailing zeros.
// Example: 20.0000 -> "20", 2.5000 -> "2.5", 0.1250 -> "0.125"
if (!function_exists('formatQuantityNumber')) {
    function formatQuantityNumber($num, $decimals = 4) {
        $n = floatval($num);
        $s = number_format($n, $decimals, '.', '');
        $s = rtrim(rtrim($s, '0'), '.');
        return $s;
    }
}

// Helpers for equipment and machine units
if (!function_exists('getProductAvailableNumber')) {
    function getProductAvailableNumber($row) {
        $cat = $row['categoryName'] ?? '';
        $sub = $row['subcatname'] ?? $row['subCategory'] ?? '';
        if (stripos($cat, 'Peralatan') !== false || stripos($sub, 'Peralatan') !== false || stripos($sub, 'Mesin') !== false) {
            return (int)round(floatval($row['Quantity']));
        }
        return floatval($row['Quantity']);
    }
}

if (!function_exists('getProductUnitValue')) {
    // value to use in the form <select> (short machine value = 'unit', other unit names like 'sudu')
    function getProductUnitValue($row) {
        $avail = isset($row['Availablein']) ? trim($row['Availablein']) : '';
        $cat = $row['categoryName'] ?? '';
        $sub = $row['subcatname'] ?? $row['subCategory'] ?? '';
        $pname = $row['productName'] ?? '';
        // if Availablein specifies a non-mass unit, use it
        if ($avail !== '' && strcasecmp($avail, 'KG') !== 0 && strcasecmp($avail, 'G') !== 0) {
            return strtolower($avail);
        }
        // machines -> 'unit'
        if (stripos($cat, 'Elektrik') !== false || stripos($sub, 'Mesin') !== false) {
            return 'unit';
        }
        // map some common utensil words
        $pLower = mb_strtolower($pname);
        $mapping = [ 'sudu' => 'sudu', 'senduk' => 'senduk', 'tray' => 'dulang', 'dulang' => 'dulang', 'piring' => 'piring', 'mangkuk' => 'mangkuk', 'pisau' => 'pisau', 'papan' => 'papan', 'penapis' => 'penapis', 'santan' => 'liter' ];
        foreach ($mapping as $k => $v) {
            if (stripos($pLower, $k) !== false) return $v;
        }
        return 'pcs';
    }
}

if (!function_exists('getProductUnitLabel')) {
    // Human friendly label used when showing available-unit; may include product name (e.g., 'unit mesin pengisar')
    function getProductUnitLabel($row) {
        $unitVal = getProductUnitValue($row);
        $pname = $row['productName'] ?? '';
        if ($unitVal === 'unit') {
            $lower = mb_strtolower($pname);
            if (stripos($lower, 'mesin') === false) {
                return 'unit mesin ' . $lower;
            } else {
                return 'unit ' . $lower;
            }
        }
        // normalize some unit labels
        if ($unitVal === 'l' || $unitVal === 'lt') return 'liter';
        if ($unitVal === 'pcs') return 'pcs';
        return $unitVal;
    }
}

if (!function_exists('getProductDisplayQty')) {
    // Returns the quantity string for display (without the word 'available')
    function getProductDisplayQty($row) {
        $cat = $row['categoryName'] ?? '';
        $sub = $row['subcatname'] ?? $row['subCategory'] ?? '';
        // if product defines Availablein (non-mass) use it
        $availField = isset($row['Availablein']) ? trim($row['Availablein']) : '';
        if ($availField !== '' && strcasecmp($availField, 'KG') !== 0 && strcasecmp($availField, 'G') !== 0) {
            $qty = (int)round(floatval($row['Quantity']));
            $label = getProductUnitLabel($row);
            return $qty . ' ' . $label;
        }
        if (stripos($cat, 'Peralatan') !== false || stripos($sub, 'Peralatan') !== false || stripos($sub, 'Mesin') !== false) {
            $qty = (int)round(floatval($row['Quantity']));
            $label = getProductUnitLabel($row);
            return $qty . ' ' . $label;
        }
        return formatQuantity($row['Quantity'], true);
    }
}

// Load language loader so pages including config get __() helper and site settings
include_once(__DIR__ . '/lang.php');

?>
