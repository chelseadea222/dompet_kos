<?php 
require 'koneksi.php'; 

// Memastikan pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$nama_user = $_SESSION['username'];

// Tentukan tanggal, bulan, dan tahun saat ini
$hari_ini = date('Y-m-d');
$bulan_sekarang = date('m');
$tahun_sekarang = date('Y');

// ==========================================
// 1. LOGIKA FITUR NOTIFIKASI PENGINGAT HARIAN
// ==========================================
// Memeriksa apakah pengguna sudah melakukan pencatatan pada hari ini
$cek_hari_ini = $conn->query("SELECT id FROM transactions WHERE user_id = '$user_id' AND date = '$hari_ini'");
$belum_catat_hari_ini = ($cek_hari_ini && $cek_hari_ini->num_rows == 0);


// ==========================================
// 2. REKAPAN KEUANGAN BULAN INI
// ==========================================
$query_masuk = $conn->query("SELECT SUM(amount) AS total_masuk FROM transactions WHERE user_id = '$user_id' AND type = 'pemasukan' AND MONTH(date) = '$bulan_sekarang' AND YEAR(date) = '$tahun_sekarang'");
$data_masuk = $query_masuk->fetch_assoc();
$uang_masuk = $data_masuk['total_masuk'] ? $data_masuk['total_masuk'] : 0;

$query_keluar = $conn->query("SELECT SUM(amount) AS total_keluar FROM transactions WHERE user_id = '$user_id' AND type = 'pengeluaran' AND MONTH(date) = '$bulan_sekarang' AND YEAR(date) = '$tahun_sekarang'");
$data_keluar = $query_keluar->fetch_assoc();
$uang_keluar = $data_keluar['total_keluar'] ? $data_keluar['total_keluar'] : 0;

$total_saldo = $uang_masuk - $uang_keluar;


// ==========================================
// 3. NOTIFIKASI PERINGATAN BOROS (> 200 RIBU PER MINGGU)
// ==========================================
$cek_minggu_ini = $conn->query("SELECT SUM(amount) AS total_minggu FROM transactions WHERE user_id = '$user_id' AND type = 'pengeluaran' AND YEARWEEK(date, 1) = YEARWEEK(CURDATE(), 1)");
$data_minggu = $cek_minggu_ini->fetch_assoc();
$total_pengeluaran_minggu_ini = $data_minggu['total_minggu'] ? $data_minggu['total_minggu'] : 0;
$apakah_boros = ($total_pengeluaran_minggu_ini > 200000);


// ==========================================
// 4. MENGAMBIL DATA UNTUK GRAFIK KUE (PIE CHART)
// ==========================================
$query_grafik = $conn->query("SELECT category, SUM(amount) as total FROM transactions WHERE user_id = '$user_id' AND type = 'pengeluaran' AND MONTH(date) = '$bulan_sekarang' AND YEAR(date) = '$tahun_sekarang' GROUP BY category");

$data_grafik = [];
$total_pengeluaran_semua = 0;
while ($row = $query_grafik->fetch_assoc()) {
    $data_grafik[$row['category']] = $row['total'];
    $total_pengeluaran_semua += $row['total'];
}

// Pengaturan 8 Warna Kategori
$warna_kategori = [
    'Makanan'      => '#3b82f6', 
    'Transportasi' => '#818cf8', 
    'Belanja'      => '#6366f1', 
    'Laundry'      => '#1e3a8a', 
    'Listrik'      => '#2563eb', 
    'Nongkrong'    => '#38bdf8', 
    'Hiburan'      => '#e0f2fe', 
    'Lainnya'      => '#93c5fd'  
];

$conic_gradient_parts = [];
$labels_html = "";
$current_percentage = 0;

if ($total_pengeluaran_semua > 0) {
    foreach ($data_grafik as $kategori => $total) {
        $percentage = ($total / $total_pengeluaran_semua) * 100;
        
        $start_pct = $current_percentage;
        $end_pct = $current_percentage + $percentage;
        $warna = isset($warna_kategori[$kategori]) ? $warna_kategori[$kategori] : '#94a3b8';
        
        $conic_gradient_parts[] = "$warna $start_pct% $end_pct%";
        
        $mid_pct = $start_pct + ($percentage / 2);
        $angle_deg = $mid_pct * 3.6; 
        $angle_rad = deg2rad($angle_deg - 90);
        
        $radius = 33; 
        $left = 50 + ($radius * cos($angle_rad));
        $top = 50 + ($radius * sin($angle_rad));
        
        $teks_persen = round($percentage) . "%";
        
        $text_color = "text-white";
        if (in_array($warna, ['#e0f2fe', '#93c5fd', '#38bdf8'])) { 
             $text_color = "text-blue-900";
        }

        if ($percentage >= 3) {
            $labels_html .= "<span class='absolute text-[11px] font-extrabold $text_color transform -translate-x-1/2 -translate-y-1/2 z-10' style='top: {$top}%; left: {$left}%;'>$teks_persen</span>\n";
        }
        
        $current_percentage = $end_pct;
    }
    $conic_gradient_str = implode(', ', $conic_gradient_parts);
} else {
    $conic_gradient_str = "#e5e7eb 0% 100%"; 
    $labels_html = "<span class='absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-300 font-bold text-sm z-10'>Belum ada data bulan ini</span>";
}

$nama_bulan_indo = ["", "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
$bulan_teks = $nama_bulan_indo[(int)$bulan_sekarang];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DompetKos - Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { darkMode: 'class' }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f3f4f6; display: flex; justify-content: center; transition: background 0.3s; }
        .app-container {
            width: 100%; max-width: 414px; min-height: 100vh;
            background: linear-gradient(to bottom, #4f8cf6, #b4eedb);
            position: relative; padding-bottom: 90px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1); overflow-y: auto;
            transition: background 0.4s ease-in-out;
        }
        .app-container::-webkit-scrollbar { display: none; }
        html.dark body { background-color: #020617; }
        html.dark .app-container {
            background: linear-gradient(to bottom, #1e3a8a, #0f172a);
        }
        .pie-chart {
            border-radius: 50%; width: 240px; height: 240px; position: relative;
            box-shadow: 0 10px 20px rgba(0,0,0,0.15);
            animation: pieGrow 1s ease-out forwards;
        }
        @keyframes pieGrow {
            0% { transform: scale(0.8); opacity: 0; }
            100% { transform: scale(1); opacity: 1; }
        }
    </style>
</head>
<body class="dark:bg-gray-900 transition-colors duration-300">

<div class="app-container text-gray-800 font-sans">
    
    <div class="absolute top-[-40px] right-[-20px] w-40 h-40 bg-white/10 rounded-full blur-md"></div>

    <div class="p-6 pt-10 text-white relative z-10">
        <h1 class="text-2xl font-bold tracking-wide dark:text-gray-200">Hello, <?= htmlspecialchars($nama_user) ?> !</h1>
        <p class="text-sm font-medium mb-6 dark:text-gray-300">Rekapan Bulan <?= $bulan_teks ?></p>

        <div class="flex flex-col gap-2.5 mb-6">
            
            <?php if ($belum_catat_hari_ini): ?>
                <div class="bg-amber-500/90 backdrop-blur-md text-white px-4 py-3 rounded-2xl shadow-md flex items-start gap-3 border border-amber-400/30">
                    <i class="fas fa-bell text-lg mt-0.5 flex-shrink-0 text-white animate-bounce"></i>
                    <div class="flex flex-col text-xs">
                        <span class="font-extrabold text-sm tracking-wide">Yuk Catat Keuangan!</span>
                        <span class="font-medium mt-0.5 opacity-90">Hari ini kamu belum melakukan pencatatan keuangan sama sekali. Catat sekarang yuk agar pengelolaan uang kosmu tetap rapi!</span>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($apakah_boros): ?>
                <div class="bg-red-500/90 backdrop-blur-md text-white px-4 py-3 rounded-2xl shadow-md flex items-start gap-3 border border-red-400/30 animate-pulse">
                    <i class="fas fa-exclamation-triangle text-lg mt-0.5 flex-shrink-0 text-amber-300"></i>
                    <div class="flex flex-col text-xs">
                        <span class="font-extrabold text-sm tracking-wide">Peringatan Kuota Boros!</span>
                        <span class="font-medium mt-0.5 opacity-90">Pengeluaranmu minggu ini mencapai <b class="underline">Rp <?= number_format($total_pengeluaran_minggu_ini, 0, ',', '.') ?></b>. Sudah melewati batas Rp 200.000!</span>
                    </div>
                </div>
            <?php endif; ?>

        </div>
        <div class="flex items-center justify-between">
            <div class="flex flex-col leading-tight">
                <span class="text-4xl font-extrabold tracking-tight drop-shadow-sm dark:text-gray-200">Rp.</span>
                <span class="text-5xl font-extrabold tracking-tight drop-shadow-sm dark:text-gray-200"><?= number_format($total_saldo, 0, ',', '.') ?></span>
            </div>
            
            <div class="flex flex-col gap-3 text-xs font-bold">
                <div class="flex items-center justify-end gap-2">
                    <span class="text-right leading-tight dark:text-gray-300">Uang<br>Masuk</span>
                    <div class="bg-[#3b5ab2] dark:bg-black/30 px-3 py-2 rounded-lg shadow-md min-w-[90px] border border-blue-400/20 dark:border-white/5 text-right">
                        Rp. <br><?= number_format($uang_masuk, 0, ',', '.') ?>
                    </div>
                </div>
                <div class="flex items-center justify-end gap-2">
                    <span class="text-right leading-tight dark:text-gray-300">Uang<br>Keluar</span>
                    <div class="bg-[#3b5ab2] dark:bg-black/30 px-3 py-2 rounded-lg shadow-md min-w-[90px] border border-blue-400/20 dark:border-white/5 text-right">
                        Rp. <br><?= number_format($uang_keluar, 0, ',', '.') ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="px-6 mt-2 relative z-10">
        <h3 class="text-center font-bold text-gray-800 text-lg mb-6 drop-shadow-sm dark:text-gray-300">Grafik Pengeluaran</h3>
        
        <div class="flex justify-center mb-10">
            <div class="pie-chart dark:border-4 dark:border-[#0f172a]" style="background: conic-gradient(<?= $conic_gradient_str ?>);">
                <?= $labels_html ?>
            </div>
        </div>

        <div class="grid grid-cols-4 gap-y-6 gap-x-2 text-center mb-8">
            <div class="flex flex-col items-center">
                <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-white text-2xl mb-1 shadow-md dark:opacity-80 border dark:border-white/10" style="background-color: <?= $warna_kategori['Makanan'] ?>;"><i class="fas fa-hamburger"></i></div>
                <span class="text-[11px] text-gray-700 dark:text-gray-400 font-bold">Makanan</span>
            </div>
            <div class="flex flex-col items-center">
                <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-white text-2xl mb-1 shadow-md dark:opacity-80 border dark:border-white/10" style="background-color: <?= $warna_kategori['Transportasi'] ?>;"><i class="fas fa-motorcycle"></i></div>
                <span class="text-[11px] text-gray-700 dark:text-gray-400 font-bold">Transportasi</span>
            </div>
            <div class="flex flex-col items-center">
                <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-white text-2xl mb-1 shadow-md dark:opacity-80 border dark:border-white/10" style="background-color: <?= $warna_kategori['Belanja'] ?>;"><i class="fas fa-shopping-cart"></i></div>
                <span class="text-[11px] text-gray-700 dark:text-gray-400 font-bold">Belanja</span>
            </div>
            <div class="flex flex-col items-center">
                <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-white text-2xl mb-1 shadow-md dark:opacity-80 border dark:border-white/10" style="background-color: <?= $warna_kategori['Laundry'] ?>;"><i class="fas fa-tshirt"></i></div>
                <span class="text-[11px] text-gray-700 dark:text-gray-400 font-bold">Laundry</span>
            </div>
            <div class="flex flex-col items-center">
                <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-white text-2xl mb-1 shadow-md dark:opacity-80 border dark:border-white/10" style="background-color: <?= $warna_kategori['Listrik'] ?>;"><i class="fas fa-bolt"></i></div>
                <span class="text-[11px] text-gray-700 dark:text-gray-400 font-bold">Listrik</span>
            </div>
            <div class="flex flex-col items-center">
                <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-white text-2xl mb-1 shadow-md dark:opacity-80 border dark:border-white/10" style="background-color: <?= $warna_kategori['Nongkrong'] ?>;"><i class="fas fa-coffee"></i></div>
                <span class="text-[11px] text-gray-700 dark:text-gray-400 font-bold">Nongkrong</span>
            </div>
            <div class="flex flex-col items-center">
                <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-blue-900 text-2xl mb-1 shadow-md dark:opacity-80 border dark:border-white/10" style="background-color: <?= $warna_kategori['Hiburan'] ?>;"><i class="fas fa-gamepad"></i></div>
                <span class="text-[11px] text-gray-700 dark:text-gray-400 font-bold">Hiburan</span>
            </div>
            <div class="flex flex-col items-center">
                <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-blue-900 text-2xl mb-1 shadow-md dark:opacity-80 border dark:border-white/10" style="background-color: <?= $warna_kategori['Lainnya'] ?>;"><i class="fas fa-wallet"></i></div>
                <span class="text-[11px] text-gray-700 dark:text-gray-400 font-bold">Lainnya</span>
            </div>
        </div>
    </div>

    <div class="absolute bottom-0 w-full bg-[#2a40a3] dark:bg-[#0f172a] rounded-t-3xl shadow-[0_-10px_25px_rgba(0,0,0,0.2)] px-6 py-4 flex justify-between items-center z-50 border-t border-transparent dark:border-white/5">
        <a href="dashboard.php" class="flex items-center bg-[#00cbf7] dark:bg-[#008ba3] px-5 py-2.5 rounded-full text-white shadow-md">
            <i class="fas fa-home mr-2 text-lg"></i> <span class="text-sm font-bold">Beranda</span>
        </a>
        <a href="pencatatan.php" class="w-10 h-10 bg-gray-200 dark:bg-white/10 rounded-full flex items-center justify-center text-gray-800 dark:text-gray-400 hover:bg-white transition shadow-inner"><i class="fas fa-pen text-lg"></i></a>
        <a href="riwayat.php" class="w-10 h-10 bg-gray-200 dark:bg-white/10 rounded-full flex items-center justify-center text-gray-800 dark:text-gray-400 hover:bg-white transition shadow-inner"><i class="fas fa-list text-lg"></i></a>
        <a href="profil.php" class="w-10 h-10 bg-gray-200 dark:bg-white/10 rounded-full flex items-center justify-center text-gray-800 dark:text-gray-400 hover:bg-white transition shadow-inner"><i class="fas fa-user text-lg"></i></a>
    </div>

</div>

<script>
    if (localStorage.getItem('theme') === 'dark') {
        document.documentElement.classList.add('dark');
    }
</script>

</body>
</html>