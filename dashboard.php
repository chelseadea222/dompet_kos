<?php
require 'koneksi.php';

// Proteksi halaman: Cek login via COOKIE (Mencegah user_id kosong)
if (empty($_COOKIE['user_id'])) {
    echo "<script>window.location.href = 'login.php';</script>";
    exit;
}
$user_id = (int)$_COOKIE['user_id'];
$username = isset($_COOKIE['username']) ? $_COOKIE['username'] : 'Pengguna';

// 1. Hitung Total Pemasukan
$query_pemasukan = $conn->query("SELECT SUM(amount) AS total FROM transactions WHERE user_id = '$user_id' AND type = 'pemasukan'");
$row_pemasukan = $query_pemasukan->fetch_assoc();
$total_pemasukan = (int)$row_pemasukan['total'];

// 2. Hitung Total Pengeluaran
$query_pengeluaran = $conn->query("SELECT SUM(amount) AS total FROM transactions WHERE user_id = '$user_id' AND type = 'pengeluaran'");
$row_pengeluaran = $query_pengeluaran->fetch_assoc();
$total_pengeluaran = (int)$row_pengeluaran['total'];

// 3. Hitung Sisa Saldo (Uang Kamu)
$sisa_saldo = $total_pemasukan - $total_pengeluaran;

// 4. Ambil 5 Transaksi Terakhir untuk ringkasan di Beranda
$query_terakhir = $conn->query("SELECT * FROM transactions WHERE user_id = '$user_id' ORDER BY date DESC, id DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - DompetKos</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script> tailwind.config = { darkMode: 'class' } </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-[#f4f7fe] dark:bg-[#0f172a] transition-colors duration-300 md:flex min-h-screen text-gray-800 dark:text-gray-200">

    <aside class="hidden md:flex flex-col w-[280px] bg-white dark:bg-[#1e293b] shadow-[4px_0_24px_rgba(0,0,0,0.02)] fixed h-full z-50 border-r border-gray-100 dark:border-gray-800">
        <div class="p-8 pb-4 flex items-center gap-4">
            <div class="w-12 h-12 bg-gradient-to-br from-[#2a40a3] to-[#4f8cf6] rounded-2xl flex items-center justify-center shadow-lg shadow-blue-500/30">
                <i class="fas fa-wallet text-white text-xl"></i>
            </div>
            <h2 class="text-2xl font-extrabold tracking-tight text-gray-900 dark:text-white">DompetKos</h2>
        </div>
        <nav class="flex-1 px-5 mt-6 space-y-2">
            <p class="px-4 text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-4">Menu Utama</p>
            <a href="dashboard.php" class="flex items-center gap-4 bg-blue-50 dark:bg-blue-900/20 text-[#2a40a3] dark:text-blue-400 px-5 py-4 rounded-2xl font-bold transition">
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
    </aside>

    <main class="w-full md:ml-[280px] min-h-screen relative pb-32 md:pb-12 pt-6 md:pt-10 px-4 md:px-10">
        <div class="max-w-4xl mx-auto w-full space-y-6">
            
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl md:text-3xl font-extrabold text-gray-900 dark:text-white">Halo, <?= htmlspecialchars($username) ?>!</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Berikut ringkasan keuangan kosmu hari ini.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <div class="bg-gradient-to-br from-[#2a40a3] to-[#4f8cf6] p-6 rounded-[2rem] text-white shadow-xl shadow-blue-500/20 relative overflow-hidden">
                    <p class="text-xs font-bold uppercase tracking-wider opacity-70">Uang Kamu</p>
                    <p class="text-2xl md:text-3xl font-black mt-2">Rp <?= number_format($sisa_saldo, 0, ',', '.') ?></p>
                    <i class="fas fa-coins absolute right-6 bottom-6 text-5xl opacity-10"></i>
                </div>
                <div class="bg-white dark:bg-gray-800 p-6 rounded-[2rem] shadow-sm border border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Pemasukan</p>
                        <p class="text-xl font-extrabold text-green-500 mt-1">Rp <?= number_format($total_pemasukan, 0, ',', '.') ?></p>
                    </div>
                    <div class="w-12 h-12 bg-green-50 dark:bg-green-950/30 rounded-2xl flex items-center justify-center text-green-500">
                        <i class="fas fa-arrow-down text-lg"></i>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 p-6 rounded-[2rem] shadow-sm border border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Pengeluaran</p>
                        <p class="text-xl font-extrabold text-red-500 mt-1">Rp <?= number_format($total_pengeluaran, 0, ',', '.') ?></p>
                    </div>
                    <div class="w-12 h-12 bg-red-50 dark:bg-red-950/30 rounded-2xl flex items-center justify-center text-red-500">
                        <i class="fas fa-arrow-up text-lg"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] p-6 md:p-8 shadow-sm border border-gray-100 dark:border-gray-700">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Catatan Terbaru</h3>
                    <a href="riwayat.php" class="text-sm font-bold text-[#2a40a3] dark:text-blue-400 hover:underline">Lihat Semua</a>
                </div>

                <div class="space-y-4">
                    <?php if ($query_terakhir->num_rows > 0): ?>
                        <?php while ($transaksi = $query_terakhir->fetch_assoc()): ?>
                            <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-900/50 rounded-2xl border border-gray-100/50 dark:border-gray-800">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-xl flex items-center justify-center font-bold text-sm <?= $transaksi['type'] == 'pemasukan' ? 'bg-green-50 text-green-500 dark:bg-green-950/30' : 'bg-red-50 text-red-500 dark:bg-red-950/30' ?>">
                                        <i class="fas <?= $transaksi['type'] == 'pemasukan' ? 'fa-arrow-down' : 'fa-arrow-up' ?>"></i>
                                    </div>
                                    <div>
                                        <p class="font-bold text-sm text-gray-900 dark:text-white"><?= htmlspecialchars($transaksi['category']) ?></p>
                                        <p class="text-xs text-gray-400 truncate max-w-[180px] md:max-w-sm"><?= htmlspecialchars($transaksi['description']) ?></p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="font-extrabold text-sm <?= $transaksi['type'] == 'pemasukan' ? 'text-green-500' : 'text-red-500' ?>">
                                        <?= $transaksi['type'] == 'pemasukan' ? '+' : '-' ?> Rp <?= number_format($transaksi['amount'], 0, ',', '.') ?>
                                    </p>
                                    <p class="text-[10px] text-gray-400 font-semibold mt-0.5"><?= date('d M Y', strtotime($transaksi['date'])) ?></p>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="text-center py-8">
                            <i class="fas fa-receipt text-4xl text-gray-300 dark:text-gray-700 mb-3"></i>
                            <p class="text-sm text-gray-400">Belum ada transaksi yang dicatat.</p>
                            <a href="pencatatan.php" class="inline-block mt-3 text-xs bg-blue-600 text-white font-bold px-4 py-2 rounded-xl">Mulai Catat</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </main>

    <div class="md:hidden fixed bottom-6 left-6 right-6 bg-white/90 dark:bg-[#1e293b]/90 backdrop-blur-xl border border-white/40 dark:border-gray-700 rounded-3xl shadow-[0_20px_40px_-10px_rgba(0,0,0,0.15)] px-2 py-2 flex justify-between items-center z-50">
        <a href="dashboard.php" class="flex flex-col items-center justify-center w-16 h-14 text-[#2a40a3] dark:text-blue-400 relative">
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
        if (localStorage.getItem('theme') === 'dark') {
            document.documentElement.classList.add('dark');
        }
    </script>
</body>
</html>