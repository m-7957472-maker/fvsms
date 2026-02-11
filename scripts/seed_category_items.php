<?php
// Simple seeder: add example items for a category (useful for demo)
session_start();
include_once(__DIR__ . '/../includes/config.php');

// Support CLI usage: php scripts/seed_category_items.php "Bahan Basah"
$catName = null;
if (php_sapi_name() === 'cli' && isset($argv[1])) {
    $catName = trim($argv[1]);
} elseif (isset($_GET['name'])) {
    $catName = trim($_GET['name']);
}
$catName = $catName ?: 'Bahan Kering';

// Detailed samples: name, optional subcategory, image filename (admin/productimages), optional description
$samples = [
    'bahan kering' => [
        ['name' => 'Tepung Gandum', 'subcat' => 'Tepung', 'image' => 'tepung_gandum.jpg', 'description' => 'Tepung gandum serba guna sesuai untuk roti, kek dan pastri; tekstur halus dan berkualiti.'],
        ['name' => 'Tepung Beras', 'subcat' => 'Tepung', 'image' => 'tepung_beras.jpg', 'description' => 'Tepung beras ringan, sesuai untuk kuih tradisional dan adunan tanpa gluten.'],
        ['name' => 'Tepung Jagung', 'subcat' => 'Tepung', 'image' => 'tepung_jagung.jpg', 'description' => 'Tepung jagung sesuai untuk coating, pemekat dan pembuatan kuih tertentu.'],
        ['name' => 'Gula Pasir', 'subcat' => 'Gula', 'image' => 'gula_pasir.jpg', 'description' => 'Gula pasir halus untuk minuman, penaik dan manisan harian.'],
        ['name' => 'Gula Perang', 'subcat' => 'Gula', 'image' => 'gula_perang.jpg', 'description' => 'Gula perang memberi rasa karamel yang kaya, sesuai untuk pencuci mulut dan sos.'],
        ['name' => 'Garam Halus', 'subcat' => 'Rempah', 'image' => 'garam.jpg', 'description' => 'Garam halus berkualiti untuk perasa asas dalam semua jenis masakan.'],
        ['name' => 'Serbuk Penaik', 'subcat' => 'Penaik', 'image' => 'serbuk_penaik.jpg', 'description' => 'Serbuk penaik untuk memastikan kek dan biskut naik dengan baik.'],
        ['name' => 'Soda Bikarbonat', 'subcat' => 'Penaik', 'image' => 'soda_bikarbonat.jpg', 'description' => 'Soda bikarbonat membantu dalam tekstur dan kereaktifan dalam penaik.'],
        ['name' => 'Yis', 'subcat' => 'Penaik', 'image' => 'yis.jpg', 'description' => 'Yis kering aktif untuk pembuatan roti dan doh yang gebu.'],
        ['name' => 'Susu Tepung', 'subcat' => 'Susu Serbuk', 'image' => 'susu_tepung.jpg', 'description' => 'Susu tepung serbaguna untuk minuman, baking dan pengganti susu segar.'],
        ['name' => 'Serbuk Koko', 'subcat' => 'Coklat', 'image' => 'serbuk_koko.jpg', 'description' => 'Serbuk koko beraroma sesuai untuk kek, minuman coklat dan pencuci mulut.'],
        ['name' => 'Oat', 'subcat' => 'Bijirin', 'image' => 'oat.jpg', 'description' => 'Oat sihat untuk sarapan, baking dan sebagai pengganti sebahagian tepung.'],
        ['name' => 'Rempah Ratus - Lada', 'subcat' => 'Rempah Ratus', 'image' => 'lada.jpg', 'description' => 'Lada kering berkualiti untuk menambah kepedasan dan aroma pada hidangan.'],
        ['name' => 'Rempah Ratus - Cili Kering', 'subcat' => 'Rempah Ratus', 'image' => 'cili_kering.jpg', 'description' => 'Cili kering untuk pes, serbuk atau tumis pedas.'],
        ['name' => 'Rempah Ratus - Kunyit', 'subcat' => 'Rempah Ratus', 'image' => 'kunyit.jpg', 'description' => 'Serbuk kunyit untuk warna dan rasa tradisional dalam kari dan sup.'],
        ['name' => 'Rempah Ratus - Ketumbar', 'subcat' => 'Rempah Ratus', 'image' => 'ketumbar.jpg', 'description' => 'Ketumbar kering untuk menambah aroma dan rasa dalam pes dan rempah.'],
        ['name' => 'Herba Kering', 'subcat' => 'Herba', 'image' => 'herba_kering.jpg', 'description' => 'Campuran herba kering untuk menambah aroma pada hidangan Mediterranean dan tempatan.'],
    ],
    'bahan mentah' => [
        ['name' => 'Ayam (Penuh)', 'subcat' => 'Ayam', 'image' => 'ayam_whole.jpg', 'description' => 'Ayam segar pilihan kami merupakan sumber protein berkualiti tinggi yang lembut, enak dan mudah dimasak dalam pelbagai cara.'],
        ['name' => 'Ayam - Dada', 'subcat' => 'Ayam', 'image' => 'ayam_dada.jpg', 'description' => 'Dada ayam tanpa tulang, rendah lemak dan sesuai untuk panggang, goreng atau dikukus.'],
        ['name' => 'Ayam - Paha', 'subcat' => 'Ayam', 'image' => 'ayam_paha.jpg', 'description' => 'Paha ayam berjus, sesuai untuk masakan berempah, panggang atau rebus.'],
        ['name' => 'Ayam - Sayap', 'subcat' => 'Ayam', 'image' => 'ayam_sayap.jpg', 'description' => 'Sayap ayam rangup sesuai untuk deep-fry, panggang dan hidangan pembuka.'],
        ['name' => 'Ayam - Kaki', 'subcat' => 'Ayam', 'image' => 'ayam_kaki.jpg', 'description' => 'Kaki ayam sesuai untuk sup dan rebusan berperisa.'],

        ['name' => 'Daging Lembu', 'subcat' => 'Daging', 'image' => 'daging_lembu.jpg', 'description' => 'Daging lembu berkualiti untuk stik, panggang dan kari; kaya dengan protein dan nutrisi.'],
        ['name' => 'Ikan Segar', 'subcat' => 'Ikan', 'image' => 'ikan_seg.jpg', 'description' => 'Ikan segar laut pilihan, sesuai untuk digoreng, dibakar atau dijadikan sup.'],
        ['name' => 'Udang', 'subcat' => 'Makanan Laut', 'image' => 'udang.jpg', 'description' => 'Udang segar, manis dan berisi, sesuai untuk gulai, goreng tepung atau sup.'],
        ['name' => 'Sotong', 'subcat' => 'Makanan Laut', 'image' => 'sotong.jpg', 'description' => 'Sotong segar untuk goreng tepung, masak pedas atau dibakar; tekstur kenyal yang disukai ramai.'],
        ['name' => 'Ketam', 'subcat' => 'Makanan Laut', 'image' => 'ketam.jpg', 'description' => 'Ketam berisi, sesuai untuk stim, kari atau resepi berminyak yang kaya rasa.'],
        ['name' => 'Kerang', 'subcat' => 'Makanan Laut', 'image' => 'kerang.jpg', 'description' => 'Kerang laut segar, sesuai untuk sup, masak pedas atau rebus bersama herba.'],
        ['name' => 'Telur', 'subcat' => 'Telur', 'image' => 'telur.jpg', 'description' => 'Telur segar untuk penggunaan harian, pembakar dan baking; sumber protein mudah.'],
        ['name' => 'Susu Segar', 'subcat' => 'Susu', 'image' => 'susu_segar.jpg', 'description' => 'Susu segar penuh krim untuk minuman dan masakan.'],
        ['name' => 'Minyak Masak', 'subcat' => 'Minyak', 'image' => 'minyak_masak.jpg', 'description' => 'Minyak masak untuk menumis, menggoreng dan memanggang; pilihan berkualiti.'],
        ['name' => 'Mentega / Marjerin', 'subcat' => 'Mentega', 'image' => 'mentega.jpg', 'description' => 'Mentega dan marjerin untuk baking, sapuan dan menambah rasa pada masakan.'],
        ['name' => 'Keju', 'subcat' => 'Tenusu', 'image' => 'keju.jpg', 'description' => 'Pelbagai jenis keju untuk hidangan ruji, topping dan baking.'],
        ['name' => 'Sayur-sayuran - Leafy', 'subcat' => 'Sayur-sayuran', 'image' => 'sayur_leafy.jpg', 'description' => 'Sayuran berdaun segar, sesuai untuk hamparan salad dan tumisan.'],
        ['name' => 'Sayur-sayuran - Root', 'subcat' => 'Sayur-sayuran', 'image' => 'sayur_root.jpg', 'description' => 'Sayuran akar seperti lobak untuk sup, rebus dan goreng.'],
        ['name' => 'Buah-buahan', 'subcat' => 'Buah-buahan', 'image' => 'buah.jpg', 'description' => 'Buah-buahan segar bermusim untuk snek, pencuci mulut dan baking.'],
        ['name' => 'Bawang', 'subcat' => 'Rempah Segar', 'image' => 'bawang.jpg', 'description' => 'Bawang segar untuk asas perisa dalam masakan.'],
        ['name' => 'Bawang Putih', 'subcat' => 'Rempah Segar', 'image' => 'bawang_putih.jpg', 'description' => 'Bawang putih harum untuk tumis, sos dan perapan.'],
        ['name' => 'Halia', 'subcat' => 'Rempah Segar', 'image' => 'halia.jpg', 'description' => 'Halia segar untuk menambah aroma dan kepedasan ringan pada hidangan.'],
        ['name' => 'Santan', 'subcat' => 'Tenusu', 'image' => 'santan.jpg', 'description' => 'Santan berkualiti untuk kari, kuah dan pencuci mulut tradisional.'],
    ],
    'peralatan tangan' => [
        ['name' => 'Pisau', 'subcat' => 'Peralatan Tangan', 'image' => 'pisau.jpg', 'description' => 'Pisau tajam dan ergonomik, memudahkan kerja memotong daging, sayur dan buah dengan lebih selamat dan cekap.'],
        ['name' => 'Papan Pemotong', 'subcat' => 'Peralatan Tangan', 'image' => 'papan_pemotong.jpg', 'description' => 'Papan pemotong tahan lasak, sesuai untuk pelbagai jenis bahan mentah dan mudah dibersihkan.'],
        ['name' => 'Sudu', 'subcat' => 'Peralatan Tangan', 'image' => 'sudu.jpg', 'description' => 'Sudu tahan panas, sesuai untuk mengacau, menyenduk dan menghidang makanan.'],
        ['name' => 'Senduk', 'subcat' => 'Peralatan Tangan', 'image' => 'senduk.jpg', 'description' => 'Senduk pelbagai saiz, memudahkan proses mengacau dan menyenduk sup, kari atau bubur.'],
        ['name' => 'Pengupas', 'subcat' => 'Peralatan Tangan', 'image' => 'pengupas.jpg', 'description' => 'Pengupas ergonomik, mempercepatkan kerja mengupas kulit sayur dan buah dengan kemas.'],
        ['name' => 'Mangkuk besar/kecil', 'subcat' => 'Peralatan Tangan', 'image' => 'mangkuk.jpg', 'description' => 'Mangkuk pelbagai saiz, sesuai untuk mengadun, menyimpan dan menghidang makanan.'],
        ['name' => 'Tray', 'subcat' => 'Peralatan Tangan', 'image' => 'tray.jpg', 'description' => 'Tray tahan panas, sesuai untuk membakar, menghidang atau menyusun bahan masakan.'],
        ['name' => 'Penapis', 'subcat' => 'Peralatan Tangan', 'image' => 'penapis.jpg', 'description' => 'Penapis halus, membantu menapis bahan kering atau cecair untuk hasil masakan yang lebih sempurna.'],
    ],
    'peralatan elektrik' => [
        ['name' => 'Pengisar', 'subcat' => 'Mesin', 'image' => 'pengisar.jpg', 'description' => 'Pengisar berkuasa tinggi, memudahkan kerja mengisar rempah, buah dan bahan masakan lain dengan cepat.'],
        ['name' => 'Mesin Pengadun', 'subcat' => 'Mesin', 'image' => 'pengadun.jpg', 'description' => 'Mesin pengadun automatik, menjadikan kerja mengadun doh dan kek lebih mudah dan sekata.'],
        ['name' => 'Mesin Pemproses Makanan', 'subcat' => 'Mesin', 'image' => 'pemproses.jpg', 'description' => 'Mesin pemproses makanan pelbagai fungsi, mempercepatkan kerja memotong, menghiris dan mencincang.'],
        ['name' => 'Ketuhar', 'subcat' => 'Mesin', 'image' => 'ketuhar.jpg', 'description' => 'Ketuhar moden, sesuai untuk membakar kek, roti, biskut dan pelbagai hidangan.'],
        ['name' => 'Dapur Gas / Dapur Elektrik', 'subcat' => 'Mesin', 'image' => 'dapur.jpg', 'description' => 'Dapur gas dan elektrik, memberikan kawalan suhu yang tepat untuk pelbagai jenis masakan.'],
        ['name' => 'Pengukus', 'subcat' => 'Mesin', 'image' => 'pengukus.jpg', 'description' => 'Pengukus serbaguna, mengekalkan khasiat dan rasa asli makanan dengan kaedah memasak sihat.'],
        ['name' => 'Ketuhar Gelombang Mikro', 'subcat' => 'Mesin', 'image' => 'microwave.jpg', 'description' => 'Ketuhar gelombang mikro, memanaskan dan memasak makanan dengan pantas dan mudah.'],
        ['name' => 'Periuk Nasi', 'subcat' => 'Mesin', 'image' => 'periuk_nasi.jpg', 'description' => 'Periuk nasi automatik, memastikan nasi sentiasa masak sempurna dan tidak berkerak.'],
    ],
    'peralatan sokongan' => [
        ['name' => 'Mangkuk', 'subcat' => 'Sokongan', 'image' => 'mangkuk.jpg', 'description' => 'Mangkuk tahan panas, sesuai untuk mengadun, menyimpan dan menghidang makanan.'],
        ['name' => 'Periuk', 'subcat' => 'Sokongan', 'image' => 'periuk.jpg', 'description' => 'Periuk pelbagai saiz, sesuai untuk memasak sup, kari, bubur dan pelbagai hidangan lain.'],
        ['name' => 'Kuali', 'subcat' => 'Sokongan', 'image' => 'kuali.jpg', 'description' => 'Kuali tidak melekat, memudahkan kerja menumis, menggoreng dan memasak dengan kurang minyak.'],
        ['name' => 'Loyang', 'subcat' => 'Sokongan', 'image' => 'loyang.jpg', 'description' => 'Loyang tahan panas, sesuai untuk membakar kek, roti dan biskut.'],
        ['name' => 'Acuan Kek', 'subcat' => 'Sokongan', 'image' => 'acuan_kek.jpg', 'description' => 'Acuan kek pelbagai bentuk, menjadikan kek dan kuih-muih lebih menarik dan kreatif.'],
        ['name' => 'Cawan Penyukat', 'subcat' => 'Sokongan', 'image' => 'cawan_penyukat.jpg', 'description' => 'Cawan penyukat tepat, membantu mendapatkan sukatan bahan yang betul untuk resipi sempurna.'],
        ['name' => 'Sudu Penyukat', 'subcat' => 'Sokongan', 'image' => 'sudu_penyukat.jpg', 'description' => 'Sudu penyukat pelbagai saiz, memastikan sukatan bahan tepat untuk hasil masakan terbaik.'],
        ['name' => 'Penimbang Digital', 'subcat' => 'Sokongan', 'image' => 'penimbang_digital.jpg', 'description' => 'Penimbang digital berketepatan tinggi, memudahkan kerja menimbang bahan dengan tepat dan pantas.'],
    ],
];

// fallback default image per category
$fallbackImages = [
    'bahan kering' => 'placeholder_kering.svg',
    'bahan mentah' => 'placeholder_mentah.svg',
    'peralatan tangan' => 'placeholder_tools.svg',
    'peralatan elektrik' => 'placeholder_tools.svg',
    'peralatan sokongan' => 'placeholder_tools.svg',
];

// default category descriptions (Bahasa Melayu)
$categoryDescriptions = [
    'bahan kering' => 'Bahan kering pilihan kami memudahkan penyediaan pelbagai resipi, tahan lama dan sentiasa tersedia untuk keperluan dapur anda.',
    'bahan mentah' => 'Bahan mentah segar dan berkualiti untuk memastikan setiap hidangan anda enak, bernutrisi dan memenuhi kehendak masakan tradisional atau moden.',
    'peralatan tangan' => 'Peralatan tangan asas yang praktikal dan tahan lasak untuk memudahkan penyediaan makanan harian dan pembuat resipi kreatif.',
    'peralatan elektrik' => 'Peralatan elektrik dan mesin dapur moden yang direka untuk menjimatkan masa dan meningkatkan kualiti masakan.',
    'peralatan sokongan' => 'Peralatan sokongan dapur seperti periuk, kuali dan alat pengukur untuk memastikan keputusan masakan yang konsisten dan profesional.',
];

// category -> default image file (placed under admin/productimages)
$images = $fallbackImages;

function seed_category($con, $catNameLocal, $itemsLocal, $fallbackImages) {
    $keyLocal = strtolower($catNameLocal);
    if (empty($itemsLocal)) return 0;

    // find category id, create if missing
    $stmt = mysqli_query($con, "SELECT id FROM category WHERE LOWER(categoryName)='".mysqli_real_escape_string($con,$keyLocal)."' LIMIT 1");
    $cat = mysqli_fetch_assoc($stmt);
    if (!$cat) {
        $createdBy = 1;
        $catNameEsc = mysqli_real_escape_string($con, $catNameLocal);
        mysqli_query($con, "INSERT INTO category (categoryName, createdBy) VALUES ('{$catNameEsc}', '{$createdBy}')");
        $catid = mysqli_insert_id($con);
    } else {
        $catid = intval($cat['id']);
    }

    // ensure category description is set (if available)
    global $categoryDescriptions;
    $kdesc = strtolower($catNameLocal);
    if (isset($categoryDescriptions[$kdesc])) {
        $descEsc = mysqli_real_escape_string($con, $categoryDescriptions[$kdesc]);
        mysqli_query($con, "UPDATE category SET categoryDescription='{$descEsc}' WHERE id='{$catid}'");
    }

    $addedLocal = 0;

    foreach ($itemsLocal as $item) {
        $iname = trim($item['name']);
        $isub = isset($item['subcat']) ? trim($item['subcat']) : '';
        $iimg = isset($item['image']) ? trim($item['image']) : '';

        // check exists
        $q = mysqli_query($con, "SELECT id FROM products WHERE productName='".mysqli_real_escape_string($con,$iname)."' AND category='$catid'");
        if (mysqli_num_rows($q) > 0) continue;

        // ensure subcategory exists (create if missing)
        $subcatId = 0;
        if ($isub !== '') {
            $sq = mysqli_query($con, "SELECT id FROM subcategory WHERE LOWER(subcategoryName)='".mysqli_real_escape_string($con,strtolower($isub))."' AND categoryid='$catid' LIMIT 1");
            if ($rowSub = mysqli_fetch_assoc($sq)) {
                $subcatId = intval($rowSub['id']);
            } else {
                $createdBy = 1; // admin user id
                mysqli_query($con, "INSERT INTO subcategory (categoryid, subcategoryName, createdBy) VALUES ('{$catid}','".mysqli_real_escape_string($con,$isub)."','{$createdBy}')");
                $subcatId = mysqli_insert_id($con);
            }
        }

        $inameEsc = mysqli_real_escape_string($con, $iname);
        $now = date('Y-m-d H:i:s');
        $img = $iimg !== '' ? $iimg : (isset($fallbackImages[$keyLocal]) ? $fallbackImages[$keyLocal] : 'no-image.png');
        $imgEsc = mysqli_real_escape_string($con, $img);

        $descRaw = isset($item['description']) && trim($item['description']) !== '' ? $item['description'] : "Sample item ({$iname})";
        $descEsc = mysqli_real_escape_string($con, $descRaw);

        // default unit is KG for ingredients; override for specific products like Santan -> liter
        $availableUnit = 'KG';
        if (mb_strtolower($iname) === 'santan') {
            $availableUnit = 'L';
        }

        $sql = "INSERT INTO products (category, subCategory, productName, variety, Availablein, Quantity, productDescription, productAvailability, productImage1, productImage2, productImage3, postingDate, addedBy) VALUES ('{$catid}', '{$subcatId}', '{$inameEsc}', '', '{$availableUnit}', '10', '{$descEsc}', 'In Stock', '{$imgEsc}', '{$imgEsc}', '{$imgEsc}', '{$now}', 1)";
        if (mysqli_query($con, $sql)) $addedLocal++;
    }

    echo "Added {$addedLocal} items to category: " . htmlentities($catNameLocal) . ". ";
    echo "<a href=\"../categorywise.php?cid={$catid}\">View Category</a><br>";
    return $addedLocal;
}

$key = strtolower($catName);
$items = isset($samples[$key]) ? $samples[$key] : [];

// support seeding all categories
if ($key === 'all' || (isset($_GET['all']) && $_GET['all'])) {
    $total = 0;
    foreach ($samples as $sk => $sitems) {
        $total += seed_category($con, ucwords($sk), $sitems, $fallbackImages);
    }
    echo "<br>Total items added across all categories: {$total}";
    exit;
}

if (empty($items)) {
    echo "No sample items configured for category: " . htmlentities($catName);
    exit;
}

?>
