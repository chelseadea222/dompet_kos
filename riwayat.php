<?php
require 'koneksi.php';

// 1. Gunakan empty() untuk mengecek apakah cookie benar-benar ada isinya
if (empty($_COOKIE['user_id'])) {
    header("Location: login.php");
    exit;
}

// 2. Paksa tipe data menjadi Integer (int) agar selalu berupa angka valid untuk database
$user_id = (int)$_COOKIE['user_id'];

if (isset($_GET['hapus'])) {
    $id_hapus = $conn->real_escape_string($_GET['hapus']);
    $conn->query("DELETE FROM transactions WHERE id = '$id_hapus' AND user_id = '$user_id'");
    header("Location: riwayat.php");
    exit;
}

$selected_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

if (isset($_GET['m']) && isset($_GET['y'])) {
    $bulan_tampil = (int)$_GET['m']; $tahun_tampil = (int)$_GET['y'];
} else {
    $bulan_tampil = (int)date('m', strtotime($selected_date)); $tahun_tampil = (int)date('Y', strtotime($selected_date));
}

$prev_m = $bulan_tampil - 1; $prev_y = $tahun_tampil; if ($prev_m == 0) { $prev_m = 12; $prev_y--; }
$next_m = $bulan_tampil + 1; $next_y = $tahun_tampil; if ($next_m == 13) { $next_m = 1; $next_y++; }

$query  = "SELECT * FROM transactions WHERE user_id = '$user_id' AND date = '$selected_date' ORDER BY id DESC";
$result = $conn->query($query);

$q_sum = $conn->query("SELECT type, SUM(amount) as total FROM transactions WHERE user_id='$user_id' AND date='$selected_date' GROUP BY type");
$total_masuk = 0; $total_keluar = 0;
if ($q_sum) { while($s = $q_sum->fetch_assoc()) { if($s['type']=='pemasukan') $total_masuk=$s['total']; else $total_keluar=$s['total']; } }

$nama_bulan = ["", "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
$ikon_kategori = [
    'Makanan'      => ['icon' => 'fa-hamburger',      'color' => '#3b82f6'],
    'Transportasi' => ['icon' => 'fa-motorcycle',     'color' => '#818cf8'],
    'Belanja'      => ['icon' => 'fa-shopping-cart',  'color' => '#6366f1'],
    'Laundry'      => ['icon' => 'fa-tshirt',         'color' => '#1e3a8a'],
    'Listrik'      => ['icon' => 'fa-bolt',           'color' => '#2563eb'],
    'Nongkrong'    => ['icon' => 'fa-coffee',         'color' => '#38bdf8'],
    'Hiburan'      => ['icon' => 'fa-gamepad',        'color' => '#6366f1'],
    'Gaji'         => ['icon' => 'fa-money-bill-wave','color' => '#10b981'],
    'Freelance'    => ['icon' => 'fa-laptop-code',    'color' => '#059669'],
    'Beasiswa'     => ['icon' => 'fa-graduation-cap', 'color' => '#0d9488'],
    'Bisnis'       => ['icon' => 'fa-store',          'color' => '#0891b2'],
    'Investasi'    => ['icon' => 'fa-chart-line',     'color' => '#0284c7'],
    'Hadiah'       => ['icon' => 'fa-gift',           'color' => '#7c3aed'],
    'Transfer'     => ['icon' => 'fa-exchange-alt',   'color' => '#4f46e5'],
    'Lainnya'      => ['icon' => 'fa-wallet',         'color' => '#93c5fd'],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat - DompetKos</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script> tailwind.config = { darkMode: 'class' } </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; -webkit-tap-highlight-color: transparent; }
    </style>
</head>
<body class="bg-[#f4f7fe] dark:bg-[#0f172a] transition-colors duration-300 md:flex min-h-screen text-gray-800 dark:text-gray-200">

    <!-- SIDEBAR DESKTOP -->
    <aside class="hidden md:flex flex-col w-[280px] bg-white dark:bg-[#1e293b] shadow-[4px_0_24px_rgba(0,0,0,0.02)] fixed h-full z-50 border-r border-gray-100 dark:border-gray-800">
        <div class="p-8 pb-4 flex items-center gap-4">
            <div class="w-12 h-12 bg-gradient-to-br from-[#2a40a3] to-[#4f8cf6] rounded-2xl flex items-center justify-center shadow-lg shadow-blue-500/30">
                <i class="fas fa-wallet text-white text-xl"></i>
            </div>
            <h2 class="text-2xl font-extrabold tracking-tight text-gray-900 dark:text-white">DompetKos</h2>
        </div>
        <nav class="flex-1 px-5 mt-6 space-y-2">
            <p class="px-4 text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-4">Menu Utama</p>
            <a href="dashboard.php" class="flex items-center gap-4 text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800/50 hover:text-[#2a40a3] dark:hover:text-white px-5 py-4 rounded-2xl font-semibold transition group">
                <i class="fas fa-home text-lg w-5 text-center group-hover:scale-110 transition-transform"></i> Beranda
            </a>
            <a href="pencatatan.php" class="flex items-center gap-4 text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800/50 hover:text-[#2a40a3] dark:hover:text-white px-5 py-4 rounded-2xl font-bold transition group">
                <i class="fas fa-pen text-lg w-5 text-center group-hover:scale-110 transition-transform"></i> Catat
            </a>
            <a href="riwayat.php" class="flex items-center gap-4 bg-blue-50 dark:bg-blue-900/20 text-[#2a40a3] dark:text-blue-400 px-5 py-4 rounded-2xl font-semibold transition">
                <i class="fas fa-list text-lg w-5 text-center"></i> Riwayat
            </a>
            <a href="profil.php" class="flex items-center gap-4 text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800/50 hover:text-[#2a40a3] dark:hover:text-white px-5 py-4 rounded-2xl font-semibold transition group">
                <i class="fas fa-user text-lg w-5 text-center group-hover:scale-110 transition-transform"></i> Profil
            </a>
        </nav>
    </aside>

    <!-- MAIN -->
    <main class="w-full md:ml-[280px] min-h-screen pb-32 md:pb-12">

        <!-- HEADER -->
        <div class="bg-gradient-to-br from-[#2a40a3] to-[#4f8cf6] pt-10 px-6 pb-20 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-64 h-64 bg-white opacity-5 rounded-full blur-3xl transform translate-x-1/3 -translate-y-1/3"></div>
            <div class="absolute bottom-0 left-0 w-40 h-40 bg-white opacity-5 rounded-full blur-2xl transform -translate-x-1/2 translate-y-1/2"></div>
            <div class="max-w-5xl mx-auto relative z-10 flex justify-between items-start">
                <div>
                    <p class="text-blue-200 text-xs font-semibold uppercase tracking-widest mb-1">DompetKos</p>
                    <h1 class="text-2xl font-extrabold text-white tracking-wide">Riwayat</h1>
                    <p class="text-blue-200 text-sm font-medium mt-1"><?= date('d F Y', strtotime($selected_date)) ?></p>
                </div>
                <a href="export.php?date=<?= $selected_date ?>"
                   class="flex items-center gap-2 bg-white/20 hover:bg-white/30 backdrop-blur-sm text-white px-4 py-2.5 rounded-2xl font-bold text-sm transition border border-white/20 shadow-sm mt-1">
                    <i class="fas fa-file-excel text-green-300"></i>
                    <span>Export Excel</span>
                </a>
            </div>
        </div>

        <!-- RINGKASAN HARI INI -->
        <div class="max-w-5xl mx-auto px-4 md:px-6 mt-[-3rem] mb-6 grid grid-cols-2 gap-3">
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 shadow-[0_8px_30px_rgb(0,0,0,0.06)] border border-gray-50 dark:border-gray-700">
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-7 h-7 bg-green-50 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                        <i class="fas fa-arrow-down text-green-500 text-xs"></i>
                    </div>
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wide">Pemasukan</span>
                </div>
                <p class="text-lg font-extrabold text-green-500">+Rp <?= number_format($total_masuk, 0, ',', '.') ?></p>
            </div>
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 shadow-[0_8px_30px_rgb(0,0,0,0.06)] border border-gray-50 dark:border-gray-700">
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-7 h-7 bg-red-50 dark:bg-red-900/30 rounded-lg flex items-center justify-center">
                        <i class="fas fa-arrow-up text-red-500 text-xs"></i>
                    </div>
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wide">Pengeluaran</span>
                </div>
                <p class="text-lg font-extrabold text-red-500">-Rp <?= number_format($total_keluar, 0, ',', '.') ?></p>
            </div>
        </div>

        <div class="max-w-5xl w-full mx-auto px-4 md:px-6 md:grid md:grid-cols-12 gap-6 items-start">

            <!-- KALENDER -->
            <div class="md:col-span-5 bg-white dark:bg-[#1e293b] rounded-[2rem] shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-50 dark:border-gray-700 p-5 mb-6 md:mb-0">
                <div class="flex justify-between items-center mb-4">
                    <a href="?m=<?= $prev_m ?>&y=<?= $prev_y ?>" class="w-8 h-8 flex items-center justify-center bg-gray-100 dark:bg-gray-800 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-700 transition">
                        <i class="fas fa-chevron-left text-xs text-gray-500 dark:text-gray-400"></i>
                    </a>
                    <h2 class="font-extrabold text-sm text-gray-800 dark:text-white"><?= $nama_bulan[$bulan_tampil] . " " . $tahun_tampil ?></h2>
                    <a href="?m=<?= $next_m ?>&y=<?= $next_y ?>" class="w-8 h-8 flex items-center justify-center bg-gray-100 dark:bg-gray-800 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-700 transition">
                        <i class="fas fa-chevron-right text-xs text-gray-500 dark:text-gray-400"></i>
                    </a>
                </div>
                <div class="grid grid-cols-7 text-center text-[10px] font-extrabold text-gray-400 dark:text-gray-500 mb-3 tracking-wider">
                    <div>SEN</div><div>SEL</div><div>RAB</div><div>KAM</div><div>JUM</div><div>SAB</div><div>MIN</div>
                </div>
                <div class="grid grid-cols-7 text-center text-xs gap-y-1.5 font-semibold">
                    <?php
                    $first_day_of_month = strtotime("$tahun_tampil-$bulan_tampil-01");
                    $day_of_week  = date('N', $first_day_of_month);
                    $days_in_month = date('t', $first_day_of_month);
                    $offset = $day_of_week - 1;
                    for ($i = 0; $i < $offset; $i++) { echo "<div></div>"; }
                    for ($day = 1; $day <= $days_in_month; $day++) {
                        $loop_date   = sprintf("%04d-%02d-%02d", $tahun_tampil, $bulan_tampil, $day);
                        $is_selected = ($loop_date == $selected_date);
                        $is_today    = ($loop_date == date('Y-m-d'));
                        if ($is_selected) {
                            $style = "bg-gradient-to-br from-[#2a40a3] to-[#4f8cf6] text-white shadow-md shadow-blue-500/30";
                        } elseif ($is_today) {
                            $style = "bg-blue-50 dark:bg-blue-900/20 text-[#2a40a3] dark:text-blue-400 font-extrabold";
                        } else {
                            $style = "text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800";
                        }
                        echo "<a href='?date=$loop_date&m=$bulan_tampil&y=$tahun_tampil' class='w-8 h-8 mx-auto flex items-center justify-center rounded-xl transition $style'>$day</a>";
                    }
                    ?>
                </div>
                <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700 flex items-center gap-2 flex-wrap">
                    <div class="w-2.5 h-2.5 rounded-full bg-gradient-to-br from-[#2a40a3] to-[#4f8cf6]"></div>
                    <span class="text-[11px] text-gray-400 dark:text-gray-500 font-semibold">Dipilih</span>
                    <div class="w-2.5 h-2.5 rounded-full bg-blue-100 dark:bg-blue-900/40 ml-3"></div>
                    <span class="text-[11px] text-gray-400 dark:text-gray-500 font-semibold">Hari ini</span>
                </div>
            </div>

            <!-- DAFTAR TRANSAKSI -->
            <div class="md:col-span-7 flex flex-col gap-3">

                <?php if ($result && $result->num_rows > 0): ?>
                    <div class="flex items-center justify-between mb-1">
                        <p class="text-xs font-extrabold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Transaksi · <?= date('d M Y', strtotime($selected_date)) ?>
                        </p>
                        <span class="text-xs font-bold text-[#2a40a3] dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 px-2.5 py-1 rounded-full">
                            <?= $result->num_rows ?> transaksi
                        </span>
                    </div>

                    <?php
                    $result->data_seek(0);
                    while($row = $result->fetch_assoc()):
                        $kat    = $row['category'];
                        $meta   = isset($ikon_kategori[$kat]) ? $ikon_kategori[$kat] : ['icon'=>'fa-wallet','color'=>'#93c5fd'];
                        $ikon   = $meta['icon'];
                        $warna  = $meta['color'];
                        $is_masuk = $row['type'] == 'pemasukan';
                    ?>
                        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 flex items-center gap-4 shadow-[0_2px_15px_rgb(0,0,0,0.04)] border border-gray-50 dark:border-gray-700 hover:shadow-[0_4px_20px_rgb(0,0,0,0.08)] transition-shadow">
                            <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-white text-lg shadow-sm flex-shrink-0" style="background-color: <?= $warna ?>">
                                <i class="fas <?= $ikon ?>"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-extrabold text-sm text-gray-800 dark:text-gray-100"><?= htmlspecialchars($row['category']) ?></p>
                                <?php if(!empty($row['description'])): ?>
                                    <p class="text-[11px] text-gray-400 dark:text-gray-500 font-medium truncate mt-0.5">
                                        <?= htmlspecialchars($row['description']) ?>
                                    </p>
                                <?php endif; ?>
                                <p class="text-[10px] text-gray-300 dark:text-gray-600 font-semibold mt-1"><?= date('d F Y', strtotime($row['date'])) ?></p>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <p class="font-extrabold text-sm <?= $is_masuk ? 'text-green-500' : 'text-red-500' ?>">
                                    <?= $is_masuk ? '+' : '-' ?>Rp <?= number_format($row['amount'], 0, ',', '.') ?>
                                </p>
                                <div class="mt-2 flex justify-end gap-1.5">
                                    <a href="pencatatan.php?id=<?= $row['id'] ?>" class="w-7 h-7 rounded-xl bg-blue-50 dark:bg-blue-900/20 flex items-center justify-center text-blue-500 dark:text-blue-400 hover:bg-blue-100 transition">
                                        <i class="fas fa-pen text-[10px]"></i>
                                    </a>
                                    <a href="riwayat.php?hapus=<?= $row['id'] ?>" onclick="return confirm('Hapus transaksi ini?')" class="w-7 h-7 rounded-xl bg-red-50 dark:bg-red-900/20 flex items-center justify-center text-red-500 dark:text-red-400 hover:bg-red-100 transition">
                                        <i class="fas fa-trash text-[10px]"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>

                <?php else: ?>
                    <div class="bg-white dark:bg-[#1e293b] rounded-[2rem] p-10 flex flex-col items-center justify-center text-center shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-50 dark:border-gray-700">
                        <div class="w-16 h-16 bg-gray-50 dark:bg-gray-800 rounded-2xl flex items-center justify-center mb-4">
                            <i class="fas fa-box-open text-2xl text-gray-300 dark:text-gray-600"></i>
                        </div>
                        <p class="font-extrabold text-gray-400 dark:text-gray-500 text-sm">Belum ada transaksi</p>
                        <p class="text-xs text-gray-300 dark:text-gray-600 mt-1">pada <?= date('d F Y', strtotime($selected_date)) ?></p>
                        <a href="pencatatan.php" class="mt-5 bg-gradient-to-r from-[#2a40a3] to-[#4f8cf6] text-white text-xs font-bold px-5 py-2.5 rounded-xl shadow-md shadow-blue-500/20 hover:opacity-90 transition">
                            <i class="fas fa-plus mr-1"></i> Tambah Transaksi
                        </a>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </main>

    <!-- BOTTOM NAV MOBILE -->
    <div class="md:hidden fixed bottom-6 left-6 right-6 bg-white/90 dark:bg-[#1e293b]/90 backdrop-blur-xl border border-white/40 dark:border-gray-700 rounded-3xl shadow-[0_20px_40px_-10px_rgba(0,0,0,0.15)] px-2 py-2 flex justify-between items-center z-50">
        <a href="dashboard.php" class="flex flex-col items-center justify-center w-16 h-14 text-gray-400 dark:text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 transition">
            <i class="fas fa-home text-xl mb-1"></i>
            <span class="text-[10px] font-bold">Beranda</span>
        </a>
        <a href="pencatatan.php" class="flex flex-col items-center justify-center w-16 h-14 text-gray-400 dark:text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 transition">
            <i class="fas fa-pen text-xl mb-1"></i>
            <span class="text-[10px] font-bold">Catat</span>
        </a>
        <a href="riwayat.php" class="flex flex-col items-center justify-center w-16 h-14 relative text-[#2a40a3] dark:text-blue-400">
            <div class="absolute -top-2 w-8 h-1 bg-[#2a40a3] dark:bg-blue-400 rounded-b-full"></div>
            <i class="fas fa-list text-xl mb-1 mt-1"></i>
            <span class="text-[10px] font-extrabold tracking-wide">Riwayat</span>
        </a>
        <a href="profil.php" class="flex flex-col items-center justify-center w-16 h-14 text-gray-400 dark:text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 transition">
            <i class="fas fa-user text-xl mb-1"></i>
            <span class="text-[10px] font-bold">Profil</span>
        </a>
    </div>

<script>
    if (localStorage.getItem('theme') === 'dark') { document.documentElement.classList.add('dark'); }
</script>
</body>
</html>