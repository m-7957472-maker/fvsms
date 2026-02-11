<?php
session_start();
include_once(__DIR__ . '/../includes/config.php');
header('Content-Type: application/json; charset=utf-8');

$lastId = isset($_GET['last_id']) ? intval($_GET['last_id']) : 0;
$isAdmin = false;
$uid = 0;
if (isset($_SESSION['aid']) && intval($_SESSION['aid'])>0) {
    $isAdmin = true;
}
if (isset($_SESSION['id']) && intval($_SESSION['id'])>0) {
    $uid = intval($_SESSION['id']);
}

$items = [];
// If this is the first poll from the client (lastId == 0), return only the current max id
if ($lastId === 0) {
    if ($isAdmin) {
        $res = mysqli_query($con, "SELECT COALESCE(MAX(id),0) as mx FROM notification WHERE userId = 0 OR userId > 0");
    } else {
        $safeUid = intval($uid);
        $res = mysqli_query($con, "SELECT COALESCE(MAX(id),0) as mx FROM notification WHERE userId = $safeUid");
    }
    $mx = 0;
    if ($res && $r = mysqli_fetch_assoc($res)) {
        $mx = intval($r['mx']);
    }
    echo json_encode(['notifications' => [], 'current_max' => $mx]);
    exit;
} else {
    if ($isAdmin) {
        $q = mysqli_query($con, "SELECT * FROM notification WHERE id > $lastId ORDER BY id ASC LIMIT 100");
    } else {
        $safeUid = intval($uid);
        $q = mysqli_query($con, "SELECT * FROM notification WHERE userId = $safeUid AND id > $lastId ORDER BY id ASC LIMIT 100");
    }
    if ($q) {
        while ($r = mysqli_fetch_assoc($q)) {
            $items[] = $r;
        }
    }
    // Also return current max so client can resync if needed
    $mxRes = null;
    if ($isAdmin) {
        $mxRes = mysqli_query($con, "SELECT COALESCE(MAX(id),0) as mx FROM notification WHERE userId = 0 OR userId > 0");
    } else {
        $safeUid = intval($uid);
        $mxRes = mysqli_query($con, "SELECT COALESCE(MAX(id),0) as mx FROM notification WHERE userId = $safeUid");
    }
    $mx = 0;
    if ($mxRes && $r2 = mysqli_fetch_assoc($mxRes)) { $mx = intval($r2['mx']); }
    echo json_encode(['notifications' => $items, 'current_max' => $mx]);
    exit;
}

// Struktur Kategori, Subkategori & Deskripsi Promosi (Bahasa Melayu)
$kategori = [
    [
        'nama' => 'Bahan Kering',
        'deskripsi' => 'Bahan kering pilihan kami memudahkan penyediaan pelbagai resipi, tahan lama dan sentiasa tersedia untuk keperluan dapur anda.',
        'subkategori' => [
            [
                'nama' => 'Tepung',
                'deskripsi' => 'Tepung pelbagai jenis untuk kek, roti, kuih-muih dan masakan harian, menjadikan hidangan anda lebih gebu dan lazatub.',
                'item' => [
                    ['nama' => 'Tepung gandum', 'deskripsi' => 'Tepung gandum serbaguna, sesuai untuk pelbagai jenis masakan dan kuih tradisional, menghasilkan tekstur lembut dan gebu.'],
                    ['nama' => 'Tepung beras', 'deskripsi' => 'Tepung beras halus, pilihan utama untuk kuih-muih tempatan dan pencuci mulut yang kenyal dan enak.'],
                    ['nama' => 'Tepung jagung', 'deskripsi' => 'Tepung jagung berkualiti, memekatkan sup dan sos serta menjadikan kek dan biskut lebih rangup.'],
                ]
            ],
            [
                'nama' => 'Gula',
                'deskripsi' => 'Gula pilihan untuk manisan, minuman dan masakan, memberikan rasa manis semula jadi yang memikat.',
                'item' => [
                    ['nama' => 'Gula pasir', 'deskripsi' => 'Gula pasir halus, mudah larut dan sesuai untuk semua jenis minuman serta pencuci mulut.'],
                    ['nama' => 'Gula perang', 'deskripsi' => 'Gula perang beraroma, menambah rasa karamel dan warna menarik pada kek serta kuih-muih.'],
                ]
            ],
            [
                'nama' => 'Lain-lain',
                'deskripsi' => 'Bahan asas dapur yang melengkapkan setiap resipi, menjadikan masakan lebih enak dan sempurna.',
                'item' => [
                    ['nama' => 'Garam', 'deskripsi' => 'Garam halus, penambah rasa utama yang menyerlahkan keenakan setiap hidangan.'],
                    ['nama' => 'Serbuk penaik', 'deskripsi' => 'Serbuk penaik berkualiti, membantu kek dan roti naik dengan sempurna dan gebu.'],
                    ['nama' => 'Soda bikarbonat', 'deskripsi' => 'Soda bikarbonat serbaguna, sesuai untuk membakar kek, biskut dan juga membersih dapur.'],
                    ['nama' => 'Yis', 'deskripsi' => 'Yis aktif, mempercepatkan proses penaikan roti dan pastri, menghasilkan tekstur lembut dan gebu.'],
                    ['nama' => 'Susu tepung', 'deskripsi' => 'Susu tepung berkrim, sesuai untuk minuman, kuih-muih dan menambah rasa lemak pada masakan.'],
                    ['nama' => 'Serbuk koko', 'deskripsi' => 'Serbuk koko asli, memberikan rasa coklat yang kaya untuk kek, biskut dan minuman.'],
                    ['nama' => 'Oat', 'deskripsi' => 'Oat penuh khasiat, sumber serat yang baik untuk sarapan sihat dan tenaga sepanjang hari.'],
                    ['nama' => 'Rempah ratus kering', 'deskripsi' => 'Rempah ratus kering (lada, cili kering, kunyit, ketumbar) menambah aroma dan rasa unik pada setiap masakan.'],
                    ['nama' => 'Herba kering', 'deskripsi' => 'Herba kering pilihan, menyedapkan sup, sos dan pelbagai hidangan dengan aroma semula jadi.'],
                ]
            ]
        ]
    ],
    [
        'nama' => 'Bahan Mentah',
        'deskripsi' => 'Bahan mentah segar, berkualiti dan terpilih untuk memastikan setiap hidangan anda enak, sihat dan penuh khasiat.',
        'subkategori' => [
            ['nama' => 'Ayam', 'deskripsi' => 'Ayam segar pilihan kami merupakan sumber protein berkualiti tinggi yang lembut, enak dan mudah dimasak dalam pelbagai cara, sama ada digoreng rangup, dipanggang beraroma, dimasak kari berempah atau direbus menjadi sup berkhasiat; dengan teksturnya yang berjus serta rasa semula jadi yang digemari ramai, ayam ini bukan sahaja menyihatkan kerana rendah lemak tetapi juga sesuai untuk pelbagai jenis hidangan, menjadikannya pilihan bijak bagi pengguna yang mahukan bahan masakan serbaguna, bernutrisi dan sentiasa segar untuk memenuhi keperluan harian.'],
            ['nama' => 'Daging lembu', 'deskripsi' => 'Daging lembu segar, kaya zat besi dan protein, sesuai untuk rendang, sup, panggang dan pelbagai masakan tradisional.'],
            ['nama' => 'Ikan', 'deskripsi' => 'Ikan segar dari laut, sumber omega-3 yang baik untuk kesihatan jantung dan otak, sesuai digoreng, bakar atau dibuat asam pedas.'],
            ['nama' => 'Makanan laut', 'deskripsi' => 'Makanan laut (udang, sotong, ketam, kerang) segar, kaya mineral dan protein, menambah kelazatan dalam setiap hidangan.'],
            ['nama' => 'Telur', 'deskripsi' => 'Telur segar, mudah dimasak dan pelbagai guna, sesuai untuk sarapan, kuih-muih dan lauk pauk.'],
            ['nama' => 'Susu segar', 'deskripsi' => 'Susu segar penuh khasiat, kaya kalsium untuk tulang kuat dan sesuai diminum atau digunakan dalam masakan.'],
            ['nama' => 'Minyak masak', 'deskripsi' => 'Minyak masak berkualiti, tidak mudah berasap dan mengekalkan rasa asli makanan, sesuai untuk menggoreng dan menumis.'],
            ['nama' => 'Mentega / marjerin', 'deskripsi' => 'Mentega dan marjerin premium, menambah rasa lemak dan aroma pada kek, roti serta masakan harian.'],
            ['nama' => 'Keju', 'deskripsi' => 'Keju leleh dan berkrim, sesuai untuk pizza, pasta, sandwic dan pencuci mulut moden.'],
            ['nama' => 'Sayur-sayuran', 'deskripsi' => 'Sayur-sayuran segar, kaya vitamin dan serat, penting untuk diet seimbang dan gaya hidup sihat.'],
            ['nama' => 'Buah-buahan', 'deskripsi' => 'Buah-buahan segar, manis dan berjus, sesuai dimakan begitu sahaja atau dijadikan pencuci mulut.'],
            ['nama' => 'Bawang', 'deskripsi' => 'Bawang segar, penambah aroma dan rasa dalam setiap masakan, sesuai untuk menumis dan sup.'],
            ['nama' => 'Bawang putih', 'deskripsi' => 'Bawang putih segar, kaya antioksidan dan menambah rasa unik pada masakan Asia.'],
            ['nama' => 'Halia', 'deskripsi' => 'Halia segar, menambah rasa pedas dan aroma dalam sup, teh dan masakan tradisional.'],
            ['nama' => 'Santan', 'deskripsi' => 'Santan pekat dan berlemak, menjadikan kari, kuih dan pencuci mulut lebih enak dan beraroma.'],
        ]
    ],
    [
        'nama' => 'Peralatan Pemprosesan Makanan',
        'deskripsi' => 'Pelbagai peralatan dapur moden dan tradisional untuk memudahkan penyediaan makanan, menjimatkan masa dan meningkatkan kualiti masakan.',
        'subkategori' => [
            [
                'nama' => 'Peralatan Tangan',
                'deskripsi' => 'Peralatan tangan asas yang wajib ada di dapur untuk memudahkan kerja memotong, mengadun dan menyediakan bahan masakan.',
                'item' => [
                    ['nama' => 'Pisau', 'deskripsi' => 'Pisau tajam dan ergonomik, memudahkan kerja memotong daging, sayur dan buah dengan lebih selamat dan cekap.'],
                    ['nama' => 'Papan pemotong', 'deskripsi' => 'Papan pemotong tahan lasak, sesuai untuk pelbagai jenis bahan mentah dan mudah dibersihkan.'],
                    ['nama' => 'Sudu', 'deskripsi' => 'Sudu tahan panas, sesuai untuk mengacau, menyenduk dan menghidang makanan.'],
                    ['nama' => 'Senduk', 'deskripsi' => 'Senduk pelbagai saiz, memudahkan proses mengacau dan menyenduk sup, kari atau bubur.'],
                    ['nama' => 'Pengupas', 'deskripsi' => 'Pengupas ergonomik, mempercepatkan kerja mengupas kulit sayur dan buah dengan kemas.'],
                    ['nama' => 'Mangkuk besar/kecil', 'deskripsi' => 'Mangkuk pelbagai saiz, sesuai untuk mengadun, menyimpan dan menghidang makanan.'],
                    ['nama' => 'Tray', 'deskripsi' => 'Tray tahan panas, sesuai untuk membakar, menghidang atau menyusun bahan masakan.'],
                    ['nama' => 'Penapis', 'deskripsi' => 'Penapis halus, membantu menapis bahan kering atau cecair untuk hasil masakan yang lebih sempurna.'],
                ]
            ],
            [
                'nama' => 'Peralatan Elektrik / Mesin',
                'deskripsi' => 'Peralatan elektrik dan mesin dapur moden yang mempercepatkan dan memudahkan proses memasak.',
                'item' => [
                    ['nama' => 'Pengisar', 'deskripsi' => 'Pengisar berkuasa tinggi, memudahkan kerja mengisar rempah, buah dan bahan masakan lain dengan cepat.'],
                    ['nama' => 'Mesin pengadun', 'deskripsi' => 'Mesin pengadun automatik, menjadikan kerja mengadun doh dan kek lebih mudah dan sekata.'],
                    ['nama' => 'Mesin pemproses makanan', 'deskripsi' => 'Mesin pemproses makanan pelbagai fungsi, mempercepatkan kerja memotong, menghiris dan mencincang.'],
                    ['nama' => 'Ketuhar', 'deskripsi' => 'Ketuhar moden, sesuai untuk membakar kek, roti, biskut dan pelbagai hidangan barat.'],
                    ['nama' => 'Dapur gas / dapur elektrik', 'deskripsi' => 'Dapur gas dan elektrik, memberikan kawalan suhu yang tepat untuk pelbagai jenis masakan.'],
                    ['nama' => 'Pengukus', 'deskripsi' => 'Pengukus serbaguna, mengekalkan khasiat dan rasa asli makanan dengan kaedah memasak sihat.'],
                    ['nama' => 'Ketuhar gelombang mikro', 'deskripsi' => 'Ketuhar gelombang mikro, memanaskan dan memasak makanan dengan pantas dan mudah.'],
                    ['nama' => 'Periuk nasi', 'deskripsi' => 'Periuk nasi automatik, memastikan nasi sentiasa masak sempurna dan tidak berkerak.'],
                ]
            ],
            [
                'nama' => 'Peralatan Sokongan',
                'deskripsi' => 'Peralatan sokongan dapur yang membantu memudahkan kerja memasak dan penyediaan makanan.',
                'item' => [
                    ['nama' => 'Mangkuk', 'deskripsi' => 'Mangkuk tahan panas, sesuai untuk mengadun, menyimpan dan menghidang makanan.'],
                    ['nama' => 'Periuk', 'deskripsi' => 'Periuk pelbagai saiz, sesuai untuk memasak sup, kari, bubur dan pelbagai hidangan lain.'],
                    ['nama' => 'Kuali', 'deskripsi' => 'Kuali tidak melekat, memudahkan kerja menumis, menggoreng dan memasak dengan kurang minyak.'],
                    ['nama' => 'Loyang', 'deskripsi' => 'Loyang tahan panas, sesuai untuk membakar kek, roti dan biskut.'],
                    ['nama' => 'Acuan kek', 'deskripsi' => 'Acuan kek pelbagai bentuk, menjadikan kek dan kuih-muih lebih menarik dan kreatif.'],
                    ['nama' => 'Cawan penyukat', 'deskripsi' => 'Cawan penyukat tepat, membantu mendapatkan sukatan bahan yang betul untuk resipi sempurna.'],
                    ['nama' => 'Sudu penyukat', 'deskripsi' => 'Sudu penyukat pelbagai saiz, memastikan sukatan bahan tepat untuk hasil masakan terbaik.'],
                    ['nama' => 'Penimbang digital', 'deskripsi' => 'Penimbang digital berketepatan tinggi, memudahkan kerja menimbang bahan dengan tepat dan pantas.'],
                ]
            ]
        ]
    ]
];
