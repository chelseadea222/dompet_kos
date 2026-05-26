<?php
require 'koneksi.php';

// Logout: hapus semua cookie
if (isset($_GET['logout'])) {
    setcookie('user_id',  '', time() - 3600, "/");
    setcookie('username', '', time() - 3600, "/");
    header("Location: login.php");
    exit;
}

// Cek login via COOKIE
if (empty($_COOKIE['user_id'])) {
    header("Location: login.php");
    exit;
}

// 2. Paksa tipe data menjadi Integer (int) agar selalu berupa angka valid untuk database
$user_id = (int)$_COOKIE['user_id'];

$query = $conn->query("SELECT * FROM users WHERE id = '$user_id'");
if ($query && $query->num_rows > 0) {
    $user        = $query->fetch_assoc();
    $email_user  = $user['email'];
    $nama_user   = $user['username'];
    $foto_profil = (!empty($user['profile_pic']) && file_exists('uploads/' . $user['profile_pic'])) ? 'uploads/' . $user['profile_pic'] : null;
} else {
    $email_user  = "Email tidak ditemukan";
    $nama_user   = $_COOKIE['username'] ?? 'Pengguna';
    $foto_profil = null;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil - DompetKos</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { darkMode: 'class' }</script>
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
            <a href="riwayat.php" class="flex items-center gap-4 text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800/50 hover:text-[#2a40a3] dark:hover:text-white px-5 py-4 rounded-2xl font-semibold transition group">
                <i class="fas fa-list text-lg w-5 text-center group-hover:scale-110 transition-transform"></i> Riwayat
            </a>
            <a href="profil.php" class="flex items-center gap-4 bg-blue-50 dark:bg-blue-900/20 text-[#2a40a3] dark:text-blue-400 px-5 py-4 rounded-2xl font-semibold transition">
                <i class="fas fa-user text-lg w-5 text-center"></i> Profil
            </a>
        </nav>
    </aside>

    <!-- MAIN -->
    <main class="w-full md:ml-[280px] min-h-screen relative pb-32 md:pb-12 pt-6 md:pt-10 px-4 md:px-10">
        <div class="max-w-xl mx-auto w-full flex flex-col gap-6">

            <!-- FOTO & NAMA -->
            <div class="flex flex-col items-center pt-4">
                <div class="w-24 h-24 rounded-full flex items-center justify-center text-4xl text-white mb-4 shadow-xl border-4 border-white dark:border-white/10 overflow-hidden bg-gradient-to-br from-[#2a40a3] to-[#4f8cf6]">
                    <?php if($foto_profil): ?>
                        <img src="<?= $foto_profil ?>" alt="Profil" class="w-full h-full object-cover">
                    <?php else: ?>
                        <i class="fas fa-user opacity-80"></i>
                    <?php endif; ?>
                </div>
                <h2 class="text-xl font-extrabold text-gray-900 dark:text-white tracking-wide">
                    <?= htmlspecialchars($nama_user) ?>
                </h2>
                <p class="text-sm text-gray-400 dark:text-gray-500 font-medium mt-1"><?= htmlspecialchars($email_user) ?></p>
                <a href="edit_profil.php" class="mt-4 text-xs font-bold bg-gradient-to-r from-[#2a40a3] to-[#4f8cf6] hover:opacity-90 text-white px-6 py-2.5 rounded-full transition shadow-lg shadow-blue-500/20">
                    <i class="fas fa-pen mr-1"></i> Edit Profil
                </a>
            </div>

            <!-- KARTU PENGATURAN -->
            <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] p-6 md:p-8 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-50 dark:border-gray-700 flex flex-col gap-4">

                <!-- Email -->
                <div class="flex justify-between items-center p-4 bg-gray-50 dark:bg-gray-900/50 rounded-2xl border border-gray-100 dark:border-gray-800">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center">
                            <i class="fas fa-envelope text-[#2a40a3] dark:text-blue-400"></i>
                        </div>
                        <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Email</span>
                    </div>
                    <span class="text-xs font-medium text-gray-400 dark:text-gray-500 bg-gray-100 dark:bg-gray-800 px-3 py-1.5 rounded-lg truncate max-w-[160px]">
                        <?= htmlspecialchars($email_user) ?>
                    </span>
                </div>

                <!-- Mode Gelap -->
                <div class="flex justify-between items-center p-4 bg-gray-50 dark:bg-gray-900/50 rounded-2xl border border-gray-100 dark:border-gray-800">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center">
                            <i class="fas fa-moon text-[#2a40a3] dark:text-blue-400"></i>
                        </div>
                        <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Mode Gelap</span>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" id="darkModeToggle" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 dark:bg-gray-700 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#2a40a3] shadow-inner"></div>
                    </label>
                </div>

                <!-- Notifikasi Harian -->
                <div class="flex justify-between items-center p-4 bg-gray-50 dark:bg-gray-900/50 rounded-2xl border border-gray-100 dark:border-gray-800">
                    <div class="flex items-center gap-3 flex-1 min-w-0">
                        <div class="w-9 h-9 rounded-xl bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-bell text-[#2a40a3] dark:text-blue-400"></i>
                        </div>
                        <div class="min-w-0">
                            <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Notifikasi Harian</span>
                            <p id="notif-status-text" class="text-[10px] font-medium text-gray-400 mt-0.5 truncate">Memuat status...</p>
                        </div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer ml-3 flex-shrink-0">
                        <input type="checkbox" id="notifToggle" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 dark:bg-gray-700 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#2a40a3] shadow-inner"></div>
                    </label>
                </div>

                <!-- TOAST -->
                <div id="notif-toast" class="hidden items-center gap-3 px-4 py-3 rounded-2xl text-sm font-semibold border">
                    <i id="notif-toast-icon" class="flex-shrink-0"></i>
                    <span id="notif-toast-msg"></span>
                </div>
            </div>

            <!-- TOMBOL LOGOUT -->
            <a href="profil.php?logout=true" class="w-full bg-gradient-to-r from-[#2a40a3] to-[#4f8cf6] text-center text-white py-4 rounded-2xl font-bold shadow-lg shadow-blue-500/20 hover:opacity-95 transition active:scale-[0.99] flex items-center justify-center gap-2">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>

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
        <a href="riwayat.php" class="flex flex-col items-center justify-center w-16 h-14 text-gray-400 dark:text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 transition">
            <i class="fas fa-list text-xl mb-1"></i>
            <span class="text-[10px] font-bold">Riwayat</span>
        </a>
        <a href="profil.php" class="flex flex-col items-center justify-center w-16 h-14 relative text-[#2a40a3] dark:text-blue-400">
            <div class="absolute -top-2 w-8 h-1 bg-[#2a40a3] dark:bg-blue-400 rounded-b-full"></div>
            <i class="fas fa-user text-xl mb-1 mt-1"></i>
            <span class="text-[10px] font-extrabold tracking-wide">Profil</span>
        </a>
    </div>

<script>
// DARK MODE
const darkToggle = document.getElementById('darkModeToggle');
const html = document.documentElement;
if (localStorage.getItem('theme') === 'dark') {
    html.classList.add('dark');
    darkToggle.checked = true;
} else {
    html.classList.remove('dark');
    darkToggle.checked = false;
}
darkToggle.addEventListener('change', function () {
    if (this.checked) { html.classList.add('dark'); localStorage.setItem('theme', 'dark'); }
    else { html.classList.remove('dark'); localStorage.setItem('theme', 'light'); }
});

// NOTIFIKASI HARIAN
const notifToggle    = document.getElementById('notifToggle');
const notifStatusTxt = document.getElementById('notif-status-text');
const notifToast     = document.getElementById('notif-toast');
const notifToastIcon = document.getElementById('notif-toast-icon');
const notifToastMsg  = document.getElementById('notif-toast-msg');
const JAM_NOTIFIKASI   = 20;
const MENIT_NOTIFIKASI = 0;

function showToast(type, message) {
    notifToast.classList.remove('hidden', 'flex',
        'bg-green-50','border-green-200','text-green-700','dark:bg-green-900/20','dark:border-green-800/40','dark:text-green-400',
        'bg-red-50','border-red-200','text-red-700','dark:bg-red-900/20','dark:border-red-800/40','dark:text-red-400',
        'bg-amber-50','border-amber-200','text-amber-700','dark:bg-amber-900/20','dark:border-amber-800/40','dark:text-amber-400',
        'bg-blue-50','border-blue-200','text-blue-700','dark:bg-blue-900/20','dark:border-blue-800/40','dark:text-blue-400'
    );
    const styles = {
        success: ['bg-green-50','border-green-200','text-green-700','dark:bg-green-900/20','dark:border-green-800/40','dark:text-green-400','fas fa-check-circle'],
        error:   ['bg-red-50','border-red-200','text-red-700','dark:bg-red-900/20','dark:border-red-800/40','dark:text-red-400','fas fa-times-circle'],
        warning: ['bg-amber-50','border-amber-200','text-amber-700','dark:bg-amber-900/20','dark:border-amber-800/40','dark:text-amber-400','fas fa-exclamation-circle'],
        info:    ['bg-blue-50','border-blue-200','text-blue-700','dark:bg-blue-900/20','dark:border-blue-800/40','dark:text-blue-400','fas fa-info-circle'],
    };
    const s = styles[type] || styles.info;
    notifToast.classList.add('flex', ...s.slice(0, 6));
    notifToastIcon.className = s[6] + ' text-base flex-shrink-0';
    notifToastMsg.textContent = message;
    clearTimeout(notifToast._hideTimer);
    notifToast._hideTimer = setTimeout(() => {
        notifToast.classList.add('hidden');
        notifToast.classList.remove('flex');
    }, 5000);
}
function updateStatusLabel(aktif) {
    notifStatusTxt.textContent = aktif
        ? `Aktif · pengingat jam ${JAM_NOTIFIKASI.toString().padStart(2,'0')}.${MENIT_NOTIFIKASI.toString().padStart(2,'0')} setiap hari`
        : 'Nonaktif';
}
let notifTimer = null;
function jadwalkanNotifikasi() {
    clearTimeout(notifTimer);
    const sekarang = new Date();
    const target   = new Date();
    target.setHours(JAM_NOTIFIKASI, MENIT_NOTIFIKASI, 0, 0);
    if (target <= sekarang) target.setDate(target.getDate() + 1);
    const selisihMs = target - sekarang;
    notifTimer = setTimeout(() => {
        kirimNotifikasi();
        setInterval(kirimNotifikasi, 24 * 60 * 60 * 1000);
    }, selisihMs);
}
function kirimNotifikasi() {
    if (Notification.permission !== 'granted') return;
    if (localStorage.getItem('notif_aktif') !== 'true') return;
    const notif = new Notification('💰 DompetKos — Pengingat Harian', {
        body: 'Jangan lupa catat keuanganmu hari ini!',
        icon: 'favicon.ico', tag: 'dompetkos-harian', renotify: false,
    });
    notif.onclick = function () { window.focus(); window.location.href = 'pencatatan.php'; notif.close(); };
}
async function aktifkanNotifikasi() {
    if (!('Notification' in window)) { showToast('error', 'Browser tidak mendukung notifikasi.'); notifToggle.checked = false; return; }
    let permission = Notification.permission;
    if (permission === 'default') permission = await Notification.requestPermission();
    if (permission === 'granted') {
        localStorage.setItem('notif_aktif', 'true');
        updateStatusLabel(true);
        jadwalkanNotifikasi();
        showToast('success', `Notifikasi aktif! Kamu akan diingatkan setiap jam ${JAM_NOTIFIKASI.toString().padStart(2,'0')}.${MENIT_NOTIFIKASI.toString().padStart(2,'0')}.`);
        setTimeout(() => { new Notification('✅ DompetKos — Notifikasi Aktif', { body: 'Pengingat harian berhasil diaktifkan.', icon: 'favicon.ico', tag: 'dompetkos-test' }); }, 3000);
    } else if (permission === 'denied') {
        notifToggle.checked = false;
        localStorage.setItem('notif_aktif', 'false');
        updateStatusLabel(false);
        showToast('warning', 'Izin notifikasi diblokir. Buka pengaturan browser untuk mengizinkan.');
    } else {
        notifToggle.checked = false;
        localStorage.setItem('notif_aktif', 'false');
        updateStatusLabel(false);
        showToast('info', 'Izin notifikasi belum diberikan.');
    }
}
function nonaktifkanNotifikasi() {
    clearTimeout(notifTimer);
    localStorage.setItem('notif_aktif', 'false');
    updateStatusLabel(false);
    showToast('info', 'Notifikasi harian dinonaktifkan.');
}
notifToggle.addEventListener('change', function () {
    if (this.checked) aktifkanNotifikasi(); else nonaktifkanNotifikasi();
});
(function initNotifikasi() {
    const savedAktif = localStorage.getItem('notif_aktif') === 'true';
    if (!('Notification' in window)) { notifToggle.disabled = true; notifStatusTxt.textContent = 'Browser tidak mendukung notifikasi'; return; }
    if (Notification.permission === 'denied') { notifToggle.checked = false; localStorage.setItem('notif_aktif', 'false'); updateStatusLabel(false); notifStatusTxt.textContent = 'Diblokir browser'; return; }
    if (savedAktif && Notification.permission === 'granted') { notifToggle.checked = true; updateStatusLabel(true); jadwalkanNotifikasi(); }
    else { notifToggle.checked = false; updateStatusLabel(false); }
})();
</script>
</body>
</html>