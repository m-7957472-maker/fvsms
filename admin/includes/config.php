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

// No local helpers here; root includes/config.php defines shared helpers.
// Provide `formatQuantity` here if the root include is not loaded.
if (!function_exists('formatQuantity')) {
	function formatQuantity($quantityInKg, $showUnit = true) {
		if (!$showUnit) {
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

// helpers for equipment display (same logic as root include)
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
	function getProductUnitValue($row) {
		$avail = isset($row['Availablein']) ? trim($row['Availablein']) : '';
		$cat = $row['categoryName'] ?? '';
		$sub = $row['subcatname'] ?? $row['subCategory'] ?? '';
		$pname = $row['productName'] ?? '';
		if ($avail !== '' && strcasecmp($avail, 'KG') !== 0 && strcasecmp($avail, 'G') !== 0) {
			return strtolower($avail);
		}
		if (stripos($cat, 'Elektrik') !== false || stripos($sub, 'Mesin') !== false) {
			return 'unit';
		}
		$pLower = mb_strtolower($pname);
		$mapping = [ 'sudu' => 'sudu', 'senduk' => 'senduk', 'tray' => 'dulang', 'dulang' => 'dulang', 'piring' => 'piring', 'mangkuk' => 'mangkuk', 'pisau' => 'pisau', 'papan' => 'papan', 'penapis' => 'penapis', 'santan' => 'liter' ];
		foreach ($mapping as $k => $v) {
			if (stripos($pLower, $k) !== false) return $v;
		}
		return 'pcs';
	}
}

if (!function_exists('getProductUnitLabel')) {
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
		if ($unitVal === 'l' || $unitVal === 'lt') return 'liter';
		if ($unitVal === 'pcs') return 'pcs';
		return $unitVal;
	}
}

if (!function_exists('getProductDisplayQty')) {
	function getProductDisplayQty($row) {
		$cat = $row['categoryName'] ?? '';
		$sub = $row['subcatname'] ?? $row['subCategory'] ?? '';
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

?>