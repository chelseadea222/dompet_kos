<?php 
ob_start();
require 'koneksi.php'; 

// 1. Ambil ID dari Cookie
$user_id = null;
if (!empty($_COOKIE['user_id'])) {
    $user_id = (int)$_COOKIE['user_id'];
}

// 2. Jika Cookie KOSONG atau RUSAK (Tukang Bersih-bersih)
if (empty($user_id)) {
    setcookie('user_id', '', time() - 3600, "/");
    setcookie('username', '', time() - 3600, "/");
    unset($_COOKIE['user_id']);
    unset($_COOKIE['username']);
    
    echo "<script>window.location.replace('login.php');</script>";
    exit;
}
// ... (SISA KODE KE BAWAHNYA BIARKAN SAMA SEPERTI SEBELUMNYA) ...

$nama_user = !empty($_COOKIE['username']) ? $_COOKIE['username'] : 'Pengguna';

$hari_ini = date('Y-m-d');
$bulan_sekarang = date('m');
$tahun_sekarang = date('Y');

// Ambil foto profil
$query_user = $conn->query("SELECT profile_pic FROM users WHERE id = '$user_id'");
$user_data = $query_user->fetch_assoc();
$foto_profil = (!empty($user_data['profile_pic']) && file_exists('uploads/' . $user_data['profile_pic'])) 
               ? 'uploads/' . $user_data['profile_pic'] : null;

// NOTIFIKASI HARIAN
$cek_hari_ini = $conn->query("SELECT id FROM transactions WHERE user_id = '$user_id' AND date = '$hari_ini'");
$belum_catat_hari_ini = ($cek_hari_ini && $cek_hari_ini->num_rows == 0);

// REKAP KEUANGAN BULAN INI
$q_masuk = $conn->query("SELECT SUM(amount) AS t FROM transactions WHERE user_id='$user_id' AND type='pemasukan' AND MONTH(date)='$bulan_sekarang' AND YEAR(date)='$tahun_sekarang'");
$uang_masuk = $q_masuk->fetch_assoc()['t'] ?? 0;

$q_keluar = $conn->query("SELECT SUM(amount) AS t FROM transactions WHERE user_id='$user_id' AND type='pengeluaran' AND MONTH(date)='$bulan_sekarang' AND YEAR(date)='$tahun_sekarang'");
$uang_keluar = $q_keluar->fetch_assoc()['t'] ?? 0;

$total_saldo = $uang_masuk - $uang_keluar;

// PERINGATAN BOROS
$q_minggu = $conn->query("SELECT SUM(amount) AS t FROM transactions WHERE user_id='$user_id' AND type='pengeluaran' AND YEARWEEK(date,1)=YEARWEEK(CURDATE(),1)");
$total_minggu = $q_minggu->fetch_assoc()['t'] ?? 0;
$apakah_boros = ($total_minggu > 200000);

// DATA GRAFIK PENGELUARAN
$q_grafik_keluar = $conn->query("SELECT category, SUM(amount) as total FROM transactions WHERE user_id='$user_id' AND type='pengeluaran' AND MONTH(date)='$bulan_sekarang' AND YEAR(date)='$tahun_sekarang' GROUP BY category ORDER BY total DESC");
$data_keluar_grafik = [];
$total_keluar_grafik = 0;
while ($r = $q_grafik_keluar->fetch_assoc()) {
    $data_keluar_grafik[$r['category']] = $r['total'];
    $total_keluar_grafik += $r['total'];
}

// Warna kategori Pengeluaran
$warna_pengeluaran = [
    'Makanan' => '#3b82f6', 'Transportasi' => '#818cf8',
    'Belanja' => '#6366f1', 'Laundry' => '#1e3a8a',
    'Listrik' => '#2563eb', 'Nongkrong' => '#38bdf8',
    'Hiburan' => '#60a5fa', 'Lainnya' => '#93c5fd'
];

function buildPieData($data_grafik, $total, $warna_map) {
    if ($total <= 0) return ['gradient' => '#e5e7eb 0% 100%', 'labels' => [], 'legend' => []];
    
    $parts = []; $labels = []; $legend = []; $cur = 0;
    foreach ($data_grafik as $kat => $total_kat) {
        $pct = ($total_kat / $total) * 100;
        $start = $cur; $end = $cur + $pct;
        $warna = $warna_map[$kat] ?? '#94a3b8';
        $parts[] = "$warna $start% $end%";
        $mid = $start + ($pct / 2);
        $angle_rad = deg2rad($mid * 3.6 - 90);
        $r = 33; 
        $left = 50 + ($r * cos($angle_rad));
        $top  = 50 + ($r * sin($angle_rad));
        if ($pct >= 4) {
            $labels[] = ['top' => round($top, 2), 'left' => round($left, 2), 'text' => round($pct) . '%', 'light' => in_array($warna, ['#e0f2fe', '#93c5fd', '#38bdf8', '#60a5fa'])];
        }
        $legend[] = ['name' => $kat, 'color' => $warna, 'amount' => $total_kat, 'pct' => round($pct)];
        $cur = $end;
    }
    return ['gradient' => implode(', ', $parts), 'labels' => $labels, 'legend' => $legend];
}

$pie_keluar = buildPieData($data_keluar_grafik, $total_keluar_grafik, $warna_pengeluaran);
$nama_bulan_indo = ["","Januari","Februari","Maret","April","Mei","Juni","Juli","Agustus","September","Oktober","November","Desember"];
$bulan_teks = $nama_bulan_indo[(int)$bulan_sekarang];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>DompetKos - Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { darkMode: 'class' }</script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; -webkit-tap-highlight-color: transparent; }

        /* Donut chart yang diperbesar */
        .pie-chart {
            border-radius: 50%;
            width: 240px; height: 240px;
            position: relative;
            animation: pieGrow .9s cubic-bezier(0.175,0.885,0.32,1.275) forwards;
        }
        .pie-chart::after {
            content: '';
            position: absolute; top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            width: 130px; height: 130px;
            background: white;
            border-radius: 50%;
            box-shadow: inset 0 2px 10px rgba(0,0,0,0.05);
        }
        html.dark .pie-chart::after { background: #1e293b; }

        @keyframes pieGrow {
            0%   { transform: scale(0.7) rotate(-15deg); opacity: 0; }
            100% { transform: scale(1) rotate(0); opacity: 1; }
        }

        /* Card fade-in */
        .fade-up { opacity: 0; transform: translateY(16px); animation: fadeUp .5s ease forwards; }
        @keyframes fadeUp { to { opacity: 1; transform: translateY(0); } }
        .delay-1 { animation-delay: .05s; }
        .delay-2 { animation-delay: .1s; }
        .delay-3 { animation-delay: .15s; }
        .delay-4 { animation-delay: .2s; }

        ::-webkit-scrollbar { width: 5px; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        html.dark ::-webkit-scrollbar-thumb { background: #475569; }

        /* Saldo shimmer */
        @keyframes shimmer {
            0%,100% { opacity: 1; } 50% { opacity: .85; }
        }
        .saldo-val { animation: shimmer 3s ease-in-out infinite; }
    </style>
</head>
<body class="bg-[#f4f7fe] dark:bg-[#0f172a] transition-colors duration-300 md:flex min-h-screen text-gray-800 dark:text-gray-200 selection:bg-blue-200 selection:text-blue-900">

    <aside class="hidden md:flex flex-col w-[280px] bg-white dark:bg-[#1e293b] shadow-[4px_0_24px_rgba(0,0,0,0.02)] fixed h-full z-50 border-r border-gray-100 dark:border-gray-800">
        <div class="p-8 pb-4 flex items-center gap-4">
            <div class="w-12 h-12 bg-gradient-to-br from-[#2a40a3] to-[#4f8cf6] rounded-2xl flex items-center justify-center shadow-lg shadow-blue-500/30">
                <i class="fas fa-wallet text-white text-xl"></i>
            </div>
            <h2 class="text-2xl font-extrabold tracking-tight text-gray-900 dark:text-white">DompetKos</h2>
        </div>
        <nav class="flex-1 px-5 mt-6 space-y-2">
            <p class="px-4 text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-4">Menu Utama</p>
            <a href="dashboard.php" class="flex items-center gap-4 bg-blue-50 dark:bg-blue-900/20 text-[#2a40a3] dark:text-blue-400 px-5 py-4 rounded-2xl font-bold">
                <i class="fas fa-home text-lg w-5 text-center"></i> Beranda
            </a>
            <a href="pencatatan.php" class="flex items-center gap-4 text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800/50 hover:text-[#2a40a3] dark:hover:text-white px-5 py-4 rounded-2xl font-semibold transition group">
                <i class="fas fa-pen text-lg w-5 text-center group-hover:scale-110 transition-transform"></i> Catat
            </a>
            <a href="riwayat.php" class="flex items-center gap-4 text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800/50 hover:text-[#2a40a3] dark:hover:text-white px-5 py-4 rounded-2xl font-semibold transition group">
                <i class="fas fa-list text-lg w-5 text-center group-hover:scale-110 transition-transform"></i> Riwayat
            </a>
            <a href="profil.php" class="flex items-center gap-4 text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800/50 hover:text-[#2a40a3] dark:hover:text-white px-5 py-4 rounded-2xl font-semibold transition group">
                <i class="fas fa-user text-lg w-5 text-center group-hover:scale-110 transition-transform"></i> Profil
            </a>
        </nav>
        <div class="p-5">
            <a href="profil.php" class="bg-gray-50 dark:bg-gray-800/50 rounded-2xl p-4 flex items-center gap-3 border border-gray-100 dark:border-gray-700 hover:border-blue-200 dark:hover:border-blue-700 transition group">
                <div class="w-10 h-10 rounded-full overflow-hidden bg-gradient-to-br from-[#2a40a3] to-[#4f8cf6] flex items-center justify-center text-white flex-shrink-0">
                    <?php if ($foto_profil): ?>
                        <img src="<?= $foto_profil ?>" class="w-full h-full object-cover">
                    <?php else: ?>
                        <i class="fas fa-user text-sm"></i>
                    <?php endif; ?>
                </div>
                <div class="overflow-hidden">
                    <p class="text-sm font-bold text-gray-900 dark:text-white truncate"><?= htmlspecialchars($nama_user) ?></p>
                    <p class="text-[10px] text-gray-500 font-medium">Pengguna Aktif ✦</p>
                </div>
            </a>
        </div>
    </aside>

    <main class="w-full md:ml-[280px] min-h-screen pb-32 md:pb-16 pt-6 md:pt-10 px-4 md:px-10">
        <div class="max-w-5xl mx-auto w-full">

            <header class="flex justify-between items-center mb-6 px-1 fade-up">
                <div>
                    <p class="text-xs md:text-sm font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-1">Rekapan <?= $bulan_teks ?> <?= $tahun_sekarang ?></p>
                    <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight text-gray-900 dark:text-white">Halo, <?= htmlspecialchars($nama_user) ?> 👋</h1>
                </div>
                <a href="profil.php" class="hidden md:flex w-11 h-11 bg-white dark:bg-gray-800 rounded-full items-center justify-center shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden hover:border-blue-300 transition relative">
                    <?php if ($foto_profil): ?>
                        <img src="<?= $foto_profil ?>" class="w-full h-full object-cover">
                    <?php else: ?>
                        <i class="fas fa-user text-gray-400"></i>
                    <?php endif; ?>
                    <?php if ($belum_catat_hari_ini || $apakah_boros): ?>
                        <span class="absolute top-0.5 right-0.5 w-2.5 h-2.5 bg-red-500 rounded-full border-2 border-white dark:border-gray-800"></span>
                    <?php endif; ?>
                </a>
            </header>

            <?php
                $batas_boros   = 200000;
                $max_bar       = 300000;
                $pct_bar       = min(100, round(($total_minggu / $max_bar) * 100));
                $pct_label     = min(100, round(($total_minggu / $batas_boros) * 100));
                $bar_color     = $total_minggu < 150000 ? '#22c55e' : ($total_minggu < 200000 ? '#f59e0b' : '#ef4444');
                $sisa_boros    = max(0, $batas_boros - $total_minggu);
            ?>
            <div id="notif-wrapper" class="flex flex-col gap-3 mb-6 fade-up delay-1">

                <?php if ($belum_catat_hari_ini): ?>
                <div id="notif-catat" class="relative bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800/40 rounded-2xl overflow-hidden">
                    <div class="flex items-start gap-4 px-5 py-4">
                        <div class="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-900/40 flex items-center justify-center text-amber-500 flex-shrink-0 mt-0.5">
                            <i class="fas fa-pen-alt text-sm"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-extrabold text-amber-800 dark:text-amber-300 text-sm">Belum Ada Catatan Hari Ini</p>
                            <p class="text-xs text-amber-600 dark:text-amber-500 mt-0.5 leading-relaxed">
                                Kamu belum mencatat pemasukan atau pengeluaran hari ini. Yuk, catat sekarang supaya keuanganmu tetap terpantau!
                            </p>
                            <a href="pencatatan.php" class="inline-flex items-center gap-1.5 mt-2.5 bg-amber-500 hover:bg-amber-600 text-white text-[11px] font-bold px-3.5 py-1.5 rounded-lg transition">
                                <i class="fas fa-plus text-[10px]"></i> Catat Sekarang
                            </a>
                        </div>
                        <button onclick="tutupNotif('notif-catat')" class="text-amber-400 hover:text-amber-600 transition flex-shrink-0 mt-0.5" title="Tutup">
                            <i class="fas fa-times text-sm"></i>
                        </button>
                    </div>
                    <div class="absolute left-0 top-0 bottom-0 w-1 bg-amber-400 rounded-l-2xl"></div>
                </div>
                <?php endif; ?>

                <?php if ($apakah_boros || $total_minggu > 0): ?>
                <div id="notif-boros" class="relative <?= $apakah_boros ? 'bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800/40' : 'bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700' ?> rounded-2xl overflow-hidden">
                    <div class="flex items-start gap-4 px-5 py-4">
                        <div class="w-10 h-10 rounded-xl <?= $apakah_boros ? 'bg-red-100 dark:bg-red-900/40 text-red-500' : 'bg-blue-50 dark:bg-blue-900/30 text-blue-500' ?> flex items-center justify-center flex-shrink-0 mt-0.5">
                            <i class="fas <?= $apakah_boros ? 'fa-exclamation-triangle' : 'fa-chart-bar' ?> text-sm"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex justify-between items-start flex-wrap gap-1">
                                <p class="font-extrabold <?= $apakah_boros ? 'text-red-800 dark:text-red-300' : 'text-gray-800 dark:text-white' ?> text-sm">
                                    <?= $apakah_boros ? '⚠️ Peringatan Boros!' : '📊 Pengeluaran Minggu Ini' ?>
                                </p>
                                <span class="text-xs font-extrabold <?= $apakah_boros ? 'text-red-600 dark:text-red-400' : 'text-gray-600 dark:text-gray-400' ?>">
                                    Rp <?= number_format($total_minggu, 0, ',', '.') ?>
                                </span>
                            </div>

                            <p class="text-xs <?= $apakah_boros ? 'text-red-600 dark:text-red-400' : 'text-gray-500 dark:text-gray-400' ?> mt-0.5 mb-3 leading-relaxed">
                                <?php if ($apakah_boros): ?>
                                    Pengeluaranmu minggu ini sudah melebihi batas <strong>Rp 200.000</strong>. Lebih hemat mulai sekarang, ya!
                                <?php else: ?>
                                    Sisa batas aman minggu ini: <strong>Rp <?= number_format($sisa_boros, 0, ',', '.') ?></strong> lagi sebelum batas Rp 200.000.
                                <?php endif; ?>
                            </p>

                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 overflow-hidden">
                                <div class="h-2 rounded-full transition-all duration-700"
                                     style="width: <?= $pct_bar ?>%; background-color: <?= $bar_color ?>;"></div>
                            </div>
                            <div class="flex justify-between mt-1.5 text-[10px] font-semibold text-gray-400 dark:text-gray-500">
                                <span>Rp 0</span>
                                <span class="<?= $apakah_boros ? 'text-red-500 font-extrabold' : '' ?>">
                                    <?= $pct_label ?>% dari batas Rp 200.000
                                </span>
                                <span>Rp 300rb+</span>
                            </div>
                        </div>
                        <button onclick="tutupNotif('notif-boros')" class="<?= $apakah_boros ? 'text-red-400 hover:text-red-600' : 'text-gray-400 hover:text-gray-600' ?> transition flex-shrink-0 mt-0.5" title="Tutup">
                            <i class="fas fa-times text-sm"></i>
                        </button>
                    </div>
                    <div class="absolute left-0 top-0 bottom-0 w-1 rounded-l-2xl" style="background-color: <?= $bar_color ?>"></div>
                </div>
                <?php endif; ?>

            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-6">

                <div class="lg:col-span-2 relative overflow-hidden bg-gradient-to-br from-[#1a2d8f] via-[#2a40a3] to-[#4f8cf6] rounded-[2rem] p-8 md:p-9 text-white shadow-[0_24px_48px_-12px_rgba(42,64,163,0.55)] fade-up delay-1">
                    <div class="absolute -top-12 -right-12 w-56 h-56 bg-white/10 rounded-full blur-3xl pointer-events-none"></div>
                    <div class="absolute -bottom-10 -left-10 w-40 h-40 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
                    <div class="absolute inset-0 opacity-[0.04]" style="background-image: radial-gradient(white 1.5px, transparent 1.5px); background-size: 22px 22px;"></div>

                    <div class="relative z-10">
                        <div class="flex justify-between items-start mb-6">
                            <div>
                                <p class="text-white/70 text-xs font-bold uppercase tracking-widest mb-3">Total Saldo Bulan Ini</p>
                                <div class="flex items-baseline gap-1.5">
                                    <span class="text-xl font-bold opacity-80">Rp</span>
                                    <span class="saldo-val text-4xl md:text-5xl font-black tracking-tight"><?= number_format($total_saldo, 0, ',', '.') ?></span>
                                </div>
                            </div>
                            <div class="w-12 h-12 bg-white/15 rounded-2xl flex items-center justify-center backdrop-blur-sm">
                                <i class="fas fa-wallet text-white text-xl"></i>
                            </div>
                        </div>
                        <?php 
                        $bar_total = $uang_masuk + $uang_keluar;
                        $pct_masuk = $bar_total > 0 ? round(($uang_masuk / $bar_total) * 100) : 50;
                        $pct_keluar = 100 - $pct_masuk;
                        ?>
                        <div class="mb-4">
                            <div class="h-2 w-full bg-white/20 rounded-full overflow-hidden">
                                <div class="h-full bg-white/80 rounded-full transition-all" style="width: <?= $pct_masuk ?>%"></div>
                            </div>
                            <div class="flex justify-between mt-2 text-[11px] text-white/60 font-semibold">
                                <span>Pemasukan <?= $pct_masuk ?>%</span>
                                <span>Pengeluaran <?= $pct_keluar ?>%</span>
                            </div>
                        </div>
                        <span class="inline-flex items-center gap-2 bg-white/15 backdrop-blur-md px-4 py-1.5 rounded-lg text-xs font-bold tracking-wider border border-white/10">
                            <i class="fas fa-star text-yellow-300 text-[10px]"></i> DOMPETKOS PRO
                        </span>
                    </div>
                </div>

                <div class="flex flex-col gap-5">
                    <div class="bg-white dark:bg-gray-800 p-5 rounded-[2rem] shadow-[0_8px_24px_rgb(0,0,0,0.04)] border border-gray-50 dark:border-gray-700 flex items-center gap-4 relative overflow-hidden group hover:-translate-y-0.5 transition-transform fade-up delay-2">
                        <div class="absolute right-0 top-0 w-20 h-20 bg-emerald-500/10 rounded-bl-[2rem] group-hover:scale-125 transition-transform"></div>
                        <div class="w-12 h-12 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-500 rounded-2xl flex items-center justify-center text-xl flex-shrink-0 shadow-sm">
                            <i class="fas fa-arrow-down"></i>
                        </div>
                        <div class="relative z-10 min-w-0">
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-0.5">Pemasukan</p>
                            <p class="text-lg font-extrabold text-gray-900 dark:text-white truncate">Rp <?= number_format($uang_masuk, 0, ',', '.') ?></p>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 p-5 rounded-[2rem] shadow-[0_8px_24px_rgb(0,0,0,0.04)] border border-gray-50 dark:border-gray-700 flex items-center gap-4 relative overflow-hidden group hover:-translate-y-0.5 transition-transform fade-up delay-3">
                        <div class="absolute right-0 top-0 w-20 h-20 bg-red-500/10 rounded-bl-[2rem] group-hover:scale-125 transition-transform"></div>
                        <div class="w-12 h-12 bg-red-50 dark:bg-red-900/20 text-red-500 rounded-2xl flex items-center justify-center text-xl flex-shrink-0 shadow-sm">
                            <i class="fas fa-arrow-up"></i>
                        </div>
                        <div class="relative z-10 min-w-0">
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-0.5">Pengeluaran</p>
                            <p class="text-lg font-extrabold text-gray-900 dark:text-white truncate">Rp <?= number_format($uang_keluar, 0, ',', '.') ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] p-6 md:p-10 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-50 dark:border-gray-700 fade-up delay-4 mb-6">
                
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4 border-b border-gray-100 dark:border-gray-700/50 pb-6">
                    <div>
                        <h3 class="font-extrabold text-xl text-gray-900 dark:text-white">Analisis Pengeluaran</h3>
                        <p class="text-xs text-gray-400 font-semibold mt-1">Bulan <?= $bulan_teks ?> <?= $tahun_sekarang ?></p>
                    </div>
                    <div class="flex items-center gap-2 bg-red-50 dark:bg-red-900/20 px-5 py-2.5 rounded-2xl">
                        <span class="w-2.5 h-2.5 rounded-full bg-red-400 inline-block animate-pulse"></span>
                        <span class="text-sm font-bold text-red-600 dark:text-red-400">Total: Rp <?= number_format($uang_keluar, 0, ',', '.') ?></span>
                    </div>
                </div>

                <div class="flex flex-col md:flex-row items-center justify-center gap-10 lg:gap-16">
                    
                    <div class="relative flex-shrink-0">
                        <div class="pie-chart" style="background: conic-gradient(<?= $pie_keluar['gradient'] ?>);">
                            <?php foreach ($pie_keluar['labels'] as $lbl): ?>
                                <span class="absolute text-[11px] font-extrabold <?= $lbl['light'] ? 'text-blue-900' : 'text-white' ?> transform -translate-x-1/2 -translate-y-1/2 z-10 pointer-events-none drop-shadow-md" style="top:<?= $lbl['top'] ?>%;left:<?= $lbl['left'] ?>%"><?= $lbl['text'] ?></span>
                            <?php endforeach; ?>
                        </div>
                        <div class="absolute inset-0 flex items-center justify-center pointer-events-none z-20">
                            <div class="text-center">
                                <p class="text-[11px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Kategori</p>
                                <p class="text-lg font-black text-gray-800 dark:text-white leading-tight mt-1"><?= count($data_keluar_grafik) ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="w-full md:w-auto flex-1">
                        <?php if (!empty($pie_keluar['legend'])): ?>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <?php foreach ($pie_keluar['legend'] as $leg): ?>
                                <div class="flex items-center justify-between p-3.5 bg-gray-50 dark:bg-gray-800/60 rounded-2xl border border-gray-100 dark:border-gray-700/50 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <span class="w-3.5 h-3.5 rounded-full flex-shrink-0 shadow-sm" style="background:<?= $leg['color'] ?>"></span>
                                        <div class="min-w-0">
                                            <p class="text-xs font-bold text-gray-800 dark:text-gray-200 truncate"><?= $leg['name'] ?></p>
                                            <p class="text-[10px] text-gray-400 font-semibold mt-0.5"><?= $leg['pct'] ?>%</p>
                                        </div>
                                    </div>
                                    <div class="text-right flex-shrink-0 pl-2">
                                        <p class="text-xs font-extrabold text-gray-900 dark:text-white">Rp <?= number_format($leg['amount']/1000, 0, ',', '.') ?>K</p>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="flex flex-col items-center justify-center py-10 bg-gray-50 dark:bg-gray-800/50 rounded-3xl border border-dashed border-gray-200 dark:border-gray-700">
                                <i class="fas fa-receipt text-3xl text-gray-300 dark:text-gray-600 mb-3"></i>
                                <p class="text-center text-sm text-gray-500 font-medium">Belum ada pengeluaran bulan ini.<br>Mulai catat pengeluaranmu!</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                </div>
            </div>
            </div>
    </main>

    <div class="md:hidden fixed bottom-6 left-6 right-6 bg-white/90 dark:bg-[#1e293b]/90 backdrop-blur-xl border border-white/40 dark:border-gray-700 rounded-3xl shadow-[0_20px_40px_-10px_rgba(0,0,0,0.15)] px-2 py-2 flex justify-between items-center z-50">
        <a href="dashboard.php" class="flex flex-col items-center justify-center w-16 h-14 relative text-[#2a40a3] dark:text-blue-400">
            <div class="absolute -top-2 w-8 h-1 bg-[#2a40a3] dark:bg-blue-400 rounded-b-full"></div>
            <i class="fas fa-home text-xl mb-1 mt-1"></i>
            <span class="text-[10px] font-extrabold tracking-wide">Beranda</span>
        </a>
        <a href="pencatatan.php" class="flex flex-col items-center justify-center w-16 h-14 text-gray-400 dark:text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 transition">
            <i class="fas fa-pen text-xl mb-1"></i>
            <span class="text-[10px] font-bold">Catat</span>
        </a>
        <a href="riwayat.php" class="flex flex-col items-center justify-center w-16 h-14 text-gray-400 dark:text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 transition">
            <i class="fas fa-list text-xl mb-1"></i>
            <span class="text-[10px] font-bold">Riwayat</span>
        </a>
        <a href="profil.php" class="flex flex-col items-center justify-center w-16 h-14 text-gray-400 dark:text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 transition">
            <i class="fas fa-user text-xl mb-1"></i>
            <span class="text-[10px] font-bold">Profil</span>
        </a>
    </div>

<script>
    // Dark mode
    if (localStorage.getItem('theme') === 'dark') {
        document.documentElement.classList.add('dark');
    }

    // Tutup notifikasi
    function tutupNotif(id) {
        const el = document.getElementById(id);
        if (!el) return;
        el.style.transition = 'opacity 0.3s, transform 0.3s, max-height 0.4s, margin 0.4s, padding 0.4s';
        el.style.opacity = '0';
        el.style.transform = 'translateY(-6px)';
        el.style.maxHeight = el.scrollHeight + 'px';
        setTimeout(() => {
            el.style.maxHeight = '0';
            el.style.marginBottom = '0';
            el.style.overflow = 'hidden';
        }, 280);
        setTimeout(() => { el.remove(); }, 680);
    }
</script>
</body>
</html>