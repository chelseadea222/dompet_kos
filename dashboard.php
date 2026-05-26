<?php
require 'koneksi.php';

// Cek login via COOKIE
if (!isset($_COOKIE['user_id'])) {
    header("Location: login.php");
    exit;
}
$user_id = $_COOKIE['user_id'];

$edit_mode = false;
$edit_id = '';
$default_type = 'pengeluaran';
$default_amount = '';
$default_category = 'Lainnya';
$default_description = '';
$default_date = date('Y-m-d');

if (isset($_GET['id'])) {
    $edit_id = $conn->real_escape_string($_GET['id']);
    $query_edit = $conn->query("SELECT * FROM transactions WHERE id = '$edit_id' AND user_id = '$user_id'");
    if ($query_edit->num_rows > 0) {
        $data_edit = $query_edit->fetch_assoc();
        $edit_mode = true;
        $default_type = $data_edit['type'];
        $default_amount = number_format((int)$data_edit['amount'], 0, ',', '.');
        $default_category = $data_edit['category'];
        $default_description = $data_edit['description'];
        $default_date = $data_edit['date'];
    }
}

if (isset($_POST['simpan_transaksi'])) {
    $jenis     = $_POST['jenis_transaksi'];
    $nominal   = preg_replace("/[^0-9]/", "", $_POST['nominal']);
    $kategori  = $_POST['kategori'];
    $keterangan = $conn->real_escape_string($_POST['keterangan']);
    $id_update = $_POST['edit_id'];

    if (!empty($id_update)) {
        $query = "UPDATE transactions SET type='$jenis', amount='$nominal', category='$kategori', description='$keterangan' WHERE id='$id_update' AND user_id='$user_id'";
        if ($conn->query($query)) {
            echo "<script>alert('Catatan berhasil diperbarui!'); window.location='riwayat.php?date=$default_date';</script>";
        }
    } else {
        $tanggal_sekarang = date('Y-m-d');
        $query = "INSERT INTO transactions (user_id, type, amount, category, description, date) VALUES ('$user_id', '$jenis', '$nominal', '$kategori', '$keterangan', '$tanggal_sekarang')";
        if ($conn->query($query)) {
            echo "<script>alert('Catatan baru berhasil disimpan!'); window.location='riwayat.php';</script>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Pencatatan - DompetKos</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script> tailwind.config = { darkMode: 'class' } </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; -webkit-tap-highlight-color: transparent; }
        .btn-kategori.active {
            transform: scale(1.05);
            box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.3);
        }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
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
            <a href="dashboard.php" class="flex items-center gap-4 text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800/50 hover:text-[#2a40a3] dark:hover:text-white px-5 py-4 rounded-2xl font-semibold transition group">
                <i class="fas fa-home text-lg w-5 text-center group-hover:scale-110 transition-transform"></i> Beranda
            </a>
            <a href="pencatatan.php" class="flex items-center gap-4 bg-blue-50 dark:bg-blue-900/20 text-[#2a40a3] dark:text-blue-400 px-5 py-4 rounded-2xl font-bold transition">
                <i class="fas fa-pen text-lg w-5 text-center"></i> Catat
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
        <div class="max-w-xl mx-auto w-full">

            <form action="" method="POST" class="bg-white dark:bg-gray-800 rounded-[2.5rem] p-6 md:p-8 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-50 dark:border-gray-700 flex flex-col gap-6" autocomplete="off">
                <input type="hidden" name="edit_id" value="<?= htmlspecialchars($edit_id) ?>">

                <!-- TAB PENGELUARAN / PEMASUKAN -->
                <div class="flex gap-2 p-1.5 bg-gray-100 dark:bg-gray-900 rounded-2xl">
                    <label class="flex-1 cursor-pointer">
                        <input type="radio" name="jenis_transaksi" value="pengeluaran" class="peer sr-only" <?= $default_type == 'pengeluaran' ? 'checked' : '' ?>>
                        <div class="py-3 text-center rounded-xl text-gray-500 dark:text-gray-400 font-bold text-sm peer-checked:bg-white dark:peer-checked:bg-gray-800 peer-checked:text-red-500 peer-checked:shadow-sm transition">
                            <i class="fas fa-arrow-up mr-1 text-xs"></i> Pengeluaran
                        </div>
                    </label>
                    <label class="flex-1 cursor-pointer">
                        <input type="radio" name="jenis_transaksi" value="pemasukan" class="peer sr-only" <?= $default_type == 'pemasukan' ? 'checked' : '' ?>>
                        <div class="py-3 text-center rounded-xl text-gray-500 dark:text-gray-400 font-bold text-sm peer-checked:bg-white dark:peer-checked:bg-gray-800 peer-checked:text-green-500 peer-checked:shadow-sm transition">
                            <i class="fas fa-arrow-down mr-1 text-xs"></i> Pemasukan
                        </div>
                    </label>
                </div>

                <!-- NOMINAL -->
                <div class="flex flex-col border-b border-gray-100 dark:border-gray-700 pb-4">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Nominal (Rp)</span>
                    <input type="text" name="nominal" id="display" value="<?= htmlspecialchars($default_amount) ?>" class="w-full bg-transparent text-right text-4xl md:text-5xl font-black text-gray-900 dark:text-white outline-none placeholder-gray-300 dark:placeholder-gray-700" placeholder="0" readonly required>
                </div>

                <!-- KETERANGAN -->
                <div class="flex flex-col">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Keterangan Catatan</span>
                    <div class="relative flex items-center">
                        <i class="far fa-edit absolute left-4 text-gray-400"></i>
                        <input type="text" name="keterangan" value="<?= htmlspecialchars($default_description) ?>" placeholder="Tulis catatan di sini..." class="w-full bg-gray-50 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-800 rounded-xl py-3.5 pl-11 pr-4 text-sm font-semibold outline-none focus:border-blue-500 focus:bg-white dark:focus:bg-gray-900 transition">
                    </div>
                </div>

                <!-- KATEGORI -->
                <input type="hidden" name="kategori" id="kategori_input" value="<?= htmlspecialchars($default_category) ?>">
                <div class="flex flex-col">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Pilih Kategori</span>
                    <div class="grid grid-cols-4 gap-3" id="grid-kategori"></div>
                </div>

                <!-- KALKULATOR -->
                <div class="grid grid-cols-4 gap-2.5 bg-gray-50 dark:bg-gray-900/40 p-3 rounded-[2rem] border border-gray-100 dark:border-gray-800">
                    <button type="button" onclick="clearDisplay()" class="p-3.5 bg-white dark:bg-gray-800 rounded-xl font-bold text-lg text-amber-500 border border-gray-100 dark:border-gray-700 shadow-sm active:scale-95 transition">AC</button>
                    <button type="button" onclick="hapusSatu()" class="p-3.5 bg-white dark:bg-gray-800 rounded-xl font-bold text-lg text-gray-500 border border-gray-100 dark:border-gray-700 shadow-sm active:scale-95 transition"><i class="fas fa-backspace"></i></button>
                    <button type="button" class="p-3.5 bg-white dark:bg-gray-800 rounded-xl font-bold text-lg text-blue-500 border border-gray-100 dark:border-gray-700 shadow-sm active:scale-95 transition">%</button>
                    <button type="button" class="p-3.5 bg-blue-50 dark:bg-blue-950/40 rounded-xl font-bold text-lg text-blue-600 dark:text-blue-400 border border-blue-100/50 dark:border-blue-900 shadow-sm active:scale-95 transition">÷</button>

                    <button type="button" onclick="appendNumber('7')" class="p-3.5 bg-white dark:bg-gray-800 rounded-xl font-bold text-lg text-gray-800 dark:text-gray-200 border border-gray-100 dark:border-gray-700 shadow-sm active:scale-95 transition">7</button>
                    <button type="button" onclick="appendNumber('8')" class="p-3.5 bg-white dark:bg-gray-800 rounded-xl font-bold text-lg text-gray-800 dark:text-gray-200 border border-gray-100 dark:border-gray-700 shadow-sm active:scale-95 transition">8</button>
                    <button type="button" onclick="appendNumber('9')" class="p-3.5 bg-white dark:bg-gray-800 rounded-xl font-bold text-lg text-gray-800 dark:text-gray-200 border border-gray-100 dark:border-gray-700 shadow-sm active:scale-95 transition">9</button>
                    <button type="button" class="p-3.5 bg-blue-50 dark:bg-blue-950/40 rounded-xl font-bold text-lg text-blue-600 dark:text-blue-400 border border-blue-100/50 dark:border-blue-900 shadow-sm active:scale-95 transition">×</button>

                    <button type="button" onclick="appendNumber('4')" class="p-3.5 bg-white dark:bg-gray-800 rounded-xl font-bold text-lg text-gray-800 dark:text-gray-200 border border-gray-100 dark:border-gray-700 shadow-sm active:scale-95 transition">4</button>
                    <button type="button" onclick="appendNumber('5')" class="p-3.5 bg-white dark:bg-gray-800 rounded-xl font-bold text-lg text-gray-800 dark:text-gray-200 border border-gray-100 dark:border-gray-700 shadow-sm active:scale-95 transition">5</button>
                    <button type="button" onclick="appendNumber('6')" class="p-3.5 bg-white dark:bg-gray-800 rounded-xl font-bold text-lg text-gray-800 dark:text-gray-200 border border-gray-100 dark:border-gray-700 shadow-sm active:scale-95 transition">6</button>
                    <button type="button" class="p-3.5 bg-blue-50 dark:bg-blue-950/40 rounded-xl font-bold text-lg text-blue-600 dark:text-blue-400 border border-blue-100/50 dark:border-blue-900 shadow-sm active:scale-95 transition">-</button>

                    <button type="button" onclick="appendNumber('1')" class="p-3.5 bg-white dark:bg-gray-800 rounded-xl font-bold text-lg text-gray-800 dark:text-gray-200 border border-gray-100 dark:border-gray-700 shadow-sm active:scale-95 transition">1</button>
                    <button type="button" onclick="appendNumber('2')" class="p-3.5 bg-white dark:bg-gray-800 rounded-xl font-bold text-lg text-gray-800 dark:text-gray-200 border border-gray-100 dark:border-gray-700 shadow-sm active:scale-95 transition">2</button>
                    <button type="button" onclick="appendNumber('3')" class="p-3.5 bg-white dark:bg-gray-800 rounded-xl font-bold text-lg text-gray-800 dark:text-gray-200 border border-gray-100 dark:border-gray-700 shadow-sm active:scale-95 transition">3</button>
                    <button type="button" class="p-3.5 bg-blue-50 dark:bg-blue-950/40 rounded-xl font-bold text-lg text-blue-600 dark:text-blue-400 border border-blue-100/50 dark:border-blue-900 shadow-sm active:scale-95 transition">+</button>

                    <button type="button" onclick="appendNumber('00')" class="p-3.5 bg-white dark:bg-gray-800 rounded-xl font-bold text-lg text-gray-800 dark:text-gray-200 border border-gray-100 dark:border-gray-700 shadow-sm active:scale-95 transition col-span-2">00</button>
                    <button type="button" onclick="appendNumber('0')" class="p-3.5 bg-white dark:bg-gray-800 rounded-xl font-bold text-lg text-gray-800 dark:text-gray-200 border border-gray-100 dark:border-gray-700 shadow-sm active:scale-95 transition">0</button>
                    <button type="button" class="p-3.5 bg-[#2a40a3] text-white rounded-xl font-bold text-lg shadow-md shadow-blue-900/20 hover:bg-blue-800 active:scale-95 transition">=</button>

                    <button type="submit" name="simpan_transaksi" class="col-span-4 mt-3 bg-gradient-to-r from-[#2a40a3] to-[#4f8cf6] text-white py-4 rounded-xl font-bold text-base hover:opacity-95 shadow-lg shadow-blue-500/20 active:scale-[0.99] transition flex items-center justify-center gap-2">
                        <i class="fas <?= $edit_mode ? 'fa-save' : 'fa-check' ?>"></i>
                        <?= $edit_mode ? 'Simpan Perubahan' : 'Simpan Catatan' ?>
                    </button>
                </div>
            </form>
        </div>
    </main>

    <!-- BOTTOM NAV MOBILE -->
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
    if (localStorage.getItem('theme') === 'dark') {
        document.documentElement.classList.add('dark');
    }

    let display       = document.getElementById('display');
    let kategoriInput = document.getElementById('kategori_input');
    let rawValue      = '<?= preg_replace("/[^0-9]/", "", $default_amount) ?>';

    function formatRibuan(str) {
        if (!str) return '';
        return str.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }
    function updateDisplay() { display.value = formatRibuan(rawValue) || ''; }
    function appendNumber(num) {
        if (rawValue === '0') rawValue = '';
        rawValue += num;
        updateDisplay();
    }
    function clearDisplay() { rawValue = ''; display.value = ''; }
    function hapusSatu() { rawValue = rawValue.slice(0, -1); updateDisplay(); }

    updateDisplay();

    const kategoriData = {
        pengeluaran: [
            { name: 'Makanan',      icon: 'fa-hamburger',     color: '#3b82f6' },
            { name: 'Transportasi', icon: 'fa-motorcycle',    color: '#818cf8' },
            { name: 'Belanja',      icon: 'fa-shopping-cart', color: '#6366f1' },
            { name: 'Laundry',      icon: 'fa-tshirt',        color: '#1e3a8a' },
            { name: 'Listrik',      icon: 'fa-bolt',          color: '#2563eb' },
            { name: 'Nongkrong',    icon: 'fa-coffee',        color: '#38bdf8' },
            { name: 'Hiburan',      icon: 'fa-gamepad',       color: '#6366f1' },
            { name: 'Lainnya',      icon: 'fa-wallet',        color: '#93c5fd' },
        ],
        pemasukan: [
            { name: 'Gaji',      icon: 'fa-money-bill-wave', color: '#10b981' },
            { name: 'Freelance', icon: 'fa-laptop-code',     color: '#059669' },
            { name: 'Beasiswa',  icon: 'fa-graduation-cap',  color: '#0d9488' },
            { name: 'Bisnis',    icon: 'fa-store',           color: '#0891b2' },
            { name: 'Investasi', icon: 'fa-chart-line',      color: '#0284c7' },
            { name: 'Hadiah',    icon: 'fa-gift',            color: '#7c3aed' },
            { name: 'Transfer',  icon: 'fa-exchange-alt',    color: '#4f46e5' },
            { name: 'Lainnya',   icon: 'fa-wallet',          color: '#6b7280' },
        ]
    };

    function renderKategori(jenis, activeKat = null) {
        const grid = document.getElementById('grid-kategori');
        const list = kategoriData[jenis];
        if (!activeKat) activeKat = list[list.length - 1].name;
        grid.innerHTML = '';
        list.forEach(kat => {
            const isActive = (kat.name === activeKat);
            const bgStyle  = isActive ? `background-color: ${kat.color}; color: white;` : `background-color: transparent; color: #64748b;`;
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'btn-kategori flex flex-col items-center justify-center p-3 rounded-2xl border border-gray-100 dark:border-gray-700/60 transition-all duration-200 hover:bg-gray-50 dark:hover:bg-gray-900/40' + (isActive ? ' active' : '');
            btn.setAttribute('data-color', kat.color);
            btn.setAttribute('style', bgStyle);
            btn.innerHTML = `<i class="fas ${kat.icon} text-lg mb-1.5"></i><span class="text-[10px] font-bold tracking-tight text-center truncate w-full">${kat.name}</span>`;
            btn.onclick = function() { setKategori(kat.name, this); };
            if (isActive) kategoriInput.value = kat.name;
            grid.appendChild(btn);
        });
    }

    document.querySelectorAll('input[name="jenis_transaksi"]').forEach(radio => {
        radio.addEventListener('change', function() { renderKategori(this.value, null); });
    });

    const initJenis = '<?= $default_type ?>';
    const initKat   = '<?= $default_category ?>';
    renderKategori(initJenis, initKat);

    function setKategori(kat, btnElement) {
        kategoriInput.value = kat;
        document.querySelectorAll('.btn-kategori').forEach(btn => {
            btn.classList.remove('active');
            btn.style.backgroundColor = 'transparent';
            btn.style.color = '#64748b';
        });
        let activeColor = btnElement.getAttribute('data-color');
        btnElement.classList.add('active');
        btnElement.style.backgroundColor = activeColor;
        btnElement.style.color = 'white';
    }
</script>
</body>
</html>