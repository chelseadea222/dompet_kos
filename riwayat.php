<?php
require 'koneksi.php';
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }
$user_id = $_SESSION['user_id'];

// (Biarkan baki kod di bawahnya seperti biasa)

// --- LOGIKA HAPUS DATA ---
if (isset($_GET['hapus'])) {
    $id_hapus = $conn->real_escape_string($_GET['hapus']);
    $conn->query("DELETE FROM transactions WHERE id = '$id_hapus' AND user_id = '$user_id'");
    header("Location: riwayat.php");
    exit;
}

// --- LOGIKA KALENDER & FILTER TANGGAL ---
$selected_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

if (isset($_GET['m']) && isset($_GET['y'])) {
    $bulan_tampil = (int)$_GET['m'];
    $tahun_tampil = (int)$_GET['y'];
} else {
    $bulan_tampil = (int)date('m', strtotime($selected_date));
    $tahun_tampil = (int)date('Y', strtotime($selected_date));
}

$prev_m = $bulan_tampil - 1;
$prev_y = $tahun_tampil;
if ($prev_m == 0) { $prev_m = 12; $prev_y--; }

$next_m = $bulan_tampil + 1;
$next_y = $tahun_tampil;
if ($next_m == 13) { $next_m = 1; $next_y++; }

// Query Data (Mengambil data transaksi termasuk kolom keterangan/description)
$query = "SELECT * FROM transactions WHERE user_id = '$user_id' AND date = '$selected_date' ORDER BY id DESC";
$result = $conn->query($query);

$nama_bulan = ["", "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];

// PEMETAAN IKON KATEGORI (8 Kategori Sinkron dengan Dashboard)
$ikon_kategori = [
    'Makanan'      => 'fa-hamburger',
    'Transportasi' => 'fa-motorcycle',
    'Belanja'      => 'fa-shopping-cart',
    'Laundry'      => 'fa-tshirt',
    'Listrik'      => 'fa-bolt',
    'Nongkrong'    => 'fa-coffee',
    'Hiburan'      => 'fa-gamepad',
    'Lainnya'      => 'fa-wallet'
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat - DompetKos</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { darkMode: 'class' }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style> 
        body { background-color: #f3f4f6; display: flex; justify-content: center; transition: background 0.3s; } 
        .app-container { 
            width: 100%; max-width: 414px; min-height: 100vh; 
            background: linear-gradient(to bottom, #a2f2d2, #aabaf8); 
            position: relative; padding-bottom: 100px; box-shadow: 0 0 15px rgba(0,0,0,0.1); 
            transition: background 0.4s ease-in-out;
        }
        .hide-scroll::-webkit-scrollbar { display: none; }

        /* TEMA MODE GELAP BIRU REDUP UNTUK RIWAYAT */
        html.dark body { background-color: #020617; }
        html.dark .app-container {
            background: linear-gradient(to bottom, #1e3a8a, #0f172a);
            box-shadow: 0 0 20px rgba(0,0,0,0.8);
        }
    </style>
</head>
<body class="dark:bg-gray-900 transition-colors duration-300">
<div class="app-container flex flex-col">
    
    <div class="bg-[#4286f4] dark:bg-black/20 pt-8 pb-16 px-6 rounded-br-[60px] shadow-sm transition-colors">
        <h1 class="text-2xl font-bold text-white tracking-wide dark:text-gray-200">Riwayat</h1>
    </div>

    <div class="-mt-10 mx-6 bg-white dark:bg-black/20 backdrop-blur-md rounded-2xl shadow-lg p-5 z-10 border border-transparent dark:border-white/5 transition-all">
        <div class="flex justify-between items-center mb-4 text-gray-700 dark:text-gray-300">
            <a href="?m=<?= $prev_m ?>&y=<?= $prev_y ?>" class="w-8 h-8 flex items-center justify-center bg-gray-100 dark:bg-white/10 rounded-full hover:bg-gray-200 dark:hover:bg-white/20 transition">
                <i class="fas fa-chevron-left text-xs"></i>
            </a>
            <h2 class="font-bold text-sm text-gray-800 dark:text-gray-200"><?= $nama_bulan[$bulan_tampil] . " " . $tahun_tampil ?></h2>
            <a href="?m=<?= $next_m ?>&y=<?= $next_y ?>" class="w-8 h-8 flex items-center justify-center bg-gray-100 dark:bg-white/10 rounded-full hover:bg-gray-200 dark:hover:bg-white/20 transition">
                <i class="fas fa-chevron-right text-xs"></i>
            </a>
        </div>

        <div class="grid grid-cols-7 text-center text-[10px] font-bold text-gray-400 dark:text-gray-500 mb-2">
            <div>MON</div><div>TUE</div><div>WED</div><div>THU</div><div>FRI</div><div>SAT</div><div>SUN</div>
        </div>

        <div class="grid grid-cols-7 text-center text-xs gap-y-2 font-semibold">
            <?php
            $first_day_of_month = strtotime("$tahun_tampil-$bulan_tampil-01");
            $day_of_week = date('N', $first_day_of_month); 
            $days_in_month = date('t', $first_day_of_month);
            
            $offset = $day_of_week - 1;
            for ($i = 0; $i < $offset; $i++) { echo "<div></div>"; }

            for ($day = 1; $day <= $days_in_month; $day++) {
                $loop_date = sprintf("%04d-%02d-%02d", $tahun_tampil, $bulan_tampil, $day);
                if ($loop_date == $selected_date) {
                    $style = "bg-[#2a40a3] dark:bg-blue-600 text-white shadow-md"; 
                } else {
                    $style = "text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/10"; 
                }
                echo "<a href='?date=$loop_date&m=$bulan_tampil&y=$tahun_tampil' class='w-7 h-7 mx-auto flex items-center justify-center rounded-full transition $style'>$day</a>";
            }
            ?>
        </div>
    </div>

    <div class="px-6 mt-6 flex justify-between items-center">
        <span class="text-gray-800 dark:text-gray-200 font-semibold text-lg">Export File Excell</span>
        <a href="export.php?date=<?= $selected_date ?>" class="bg-[#2a40a3] dark:bg-[#1a2866] hover:bg-blue-800 dark:hover:bg-[#111a45] text-white px-5 py-2 rounded-xl shadow-md font-bold text-sm transition border border-transparent dark:border-white/5">
            Export
        </a>
    </div>

    <div class="px-6 mt-6 flex-grow overflow-y-auto hide-scroll flex flex-col gap-4">
        <?php if ($result && $result->num_rows > 0): ?>
            <p class="text-xs text-gray-500 dark:text-gray-400 font-semibold mb-[-5px]">Transaksi pada <?= date('d M Y', strtotime($selected_date)) ?></p>
            
            <?php while($row = $result->fetch_assoc()): ?>
                <?php 
                    $kategori_saat_ini = $row['category'];
                    $ikon = isset($ikon_kategori[$kategori_saat_ini]) ? $ikon_kategori[$kategori_saat_ini] : 'fa-wallet';
                ?>
                <div class="bg-white dark:bg-black/30 p-3.5 rounded-2xl flex justify-between items-center shadow-sm border border-transparent dark:border-white/5 transition-all">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-[#5475f7] dark:bg-blue-800/80 rounded-xl text-white flex justify-center items-center text-xl shadow-sm">
                            <i class="fas <?= $ikon ?>"></i>
                        </div>
                        <div>
                            <p class="font-bold text-sm text-gray-800 dark:text-gray-200"><?= htmlspecialchars($row['category']) ?></p>
                            
                            <?php if(!empty($row['description'])): ?>
                                <p class="text-[11px] text-gray-500 dark:text-gray-400 leading-tight font-medium my-0.5"><i class="fas fa-quote-left text-[8px] mr-1 opacity-50"></i><?= htmlspecialchars($row['description']) ?></p>
                            <?php endif; ?>
                            
                            <p class="text-[10px] text-gray-400 dark:text-gray-500 font-semibold mt-0.5"><?= date('d F Y', strtotime($row['date'])) ?></p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="font-extrabold text-sm <?= $row['type'] == 'pemasukan' ? 'text-green-500 dark:text-green-400' : 'text-red-500 dark:text-red-400' ?>">
                            <?= $row['type'] == 'pemasukan' ? '+' : '-' ?> Rp <?= number_format($row['amount'], 0, ',', '.') ?>
                        </p>
                        <div class="mt-1.5 flex justify-end gap-2">
                            <a href="pencatatan.php?id=<?= $row['id'] ?>" class="w-6 h-6 rounded-full bg-blue-100 dark:bg-white/10 flex items-center justify-center text-blue-500 dark:text-blue-400 hover:bg-blue-200 dark:hover:bg-white/20 transition">
                                <i class="fas fa-pen text-[10px]"></i>
                            </a>
                            <a href="riwayat.php?hapus=<?= $row['id'] ?>" onclick="return confirm('Hapus transaksi ini?')" class="w-6 h-6 rounded-full bg-red-100 dark:bg-white/10 flex items-center justify-center text-red-500 dark:text-red-400 hover:bg-red-200 dark:hover:bg-white/20 transition">
                                <i class="fas fa-trash text-[10px]"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
            
        <?php else: ?>
            <div class="flex flex-col items-center justify-center text-center mt-10 opacity-60">
                <i class="fas fa-box-open text-4xl text-gray-500 dark:text-gray-400 mb-3"></i>
                <p class="text-sm font-semibold text-gray-600 dark:text-gray-400">Tidak ada transaksi <br>pada tanggal ini.</p>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="absolute bottom-0 w-full bg-[#2a40a3] dark:bg-[#0f172a] rounded-t-[30px] shadow-[0_-10px_25px_rgba(0,0,0,0.2)] dark:shadow-[0_-10px_25px_rgba(0,0,0,0.8)] px-6 py-4 flex justify-between items-center z-50 border-t border-transparent dark:border-white/5 transition-colors duration-300">
        <a href="dashboard.php" class="w-10 h-10 bg-gray-200 dark:bg-white/10 rounded-full flex items-center justify-center text-gray-800 dark:text-gray-400 hover:bg-white dark:hover:bg-white/20 transition shadow-inner">
            <i class="fas fa-home text-lg"></i>
        </a>
        <a href="pencatatan.php" class="w-10 h-10 bg-gray-200 dark:bg-white/10 rounded-full flex items-center justify-center text-gray-800 dark:text-gray-400 hover:bg-white dark:hover:bg-white/20 transition shadow-inner">
            <i class="fas fa-pen text-lg"></i>
        </a>
        <a href="riwayat.php" class="flex items-center bg-[#00cbf7] dark:bg-[#008ba3] px-5 py-2.5 rounded-full text-white dark:text-gray-200 shadow-md">
            <i class="fas fa-list mr-2 text-lg"></i>
            <span class="text-sm font-bold">Riwayat</span>
        </a>
        <a href="profil.php" class="w-10 h-10 bg-gray-200 dark:bg-white/10 rounded-full flex items-center justify-center text-gray-800 dark:text-gray-400 hover:bg-white dark:hover:bg-white/20 transition shadow-inner">
            <i class="fas fa-user text-lg"></i>
        </a>
    </div>

</div>

<script>
    if (localStorage.getItem('theme') === 'dark') {
        document.documentElement.classList.add('dark');
    }
</script>
</body>
</html>