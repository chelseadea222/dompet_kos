<?php
require 'koneksi.php';
if (!isset($_COOKIE['user_id'])) { header("Location: login.php"); exit; }
$user_id = $_COOKIE['user_id'];

// Variabel Default (Untuk pencatatan baru)
$edit_mode = false;
$edit_id = '';
$default_type = 'pengeluaran';
$default_amount = '';
$default_category = 'Lainnya';
$default_description = ''; 
$default_date = date('Y-m-d');

// LOGIKA JIKA DALAM MODE EDIT (Parameter ?id= ada di URL)
if (isset($_GET['id'])) {
    $edit_id = $conn->real_escape_string($_GET['id']);
    $query_edit = $conn->query("SELECT * FROM transactions WHERE id = '$edit_id' AND user_id = '$user_id'");
    
    if ($query_edit->num_rows > 0) {
        $data_edit = $query_edit->fetch_assoc();
        $edit_mode = true;
        $default_type = $data_edit['type'];
        $default_amount = (int)$data_edit['amount'];
        $default_category = $data_edit['category'];
        $default_description = $data_edit['description']; 
        $default_date = $data_edit['date'];
    }
}

// LOGIKA KETIKA TOMBOL SIMPAN / UPDATE DITEKAN
if (isset($_POST['simpan_transaksi'])) {
    $jenis = $_POST['jenis_transaksi'];
    $nominal = preg_replace("/[^0-9]/", "", $_POST['nominal']); 
    $kategori = $_POST['kategori'];
    $keterangan = $conn->real_escape_string($_POST['keterangan']); 
    $id_update = $_POST['edit_id'];

    if (!empty($id_update)) {
        // Lakukan UPDATE data lama
        $query = "UPDATE transactions SET type='$jenis', amount='$nominal', category='$kategori', description='$keterangan' WHERE id='$id_update' AND user_id='$user_id'";
        if ($conn->query($query)) {
            echo "<script>alert('Catatan berhasil diperbarui!'); window.location='riwayat.php?date=$default_date';</script>";
        }
    } else {
        // Lakukan INSERT data baru
        $tanggal_sekarang = date('Y-m-d');
        $query = "INSERT INTO transactions (user_id, type, amount, category, description, date) VALUES ('$user_id', '$jenis', '$nominal', '$kategori', '$keterangan', '$tanggal_sekarang')";
        if ($conn->query($query)) {
            echo "<script>alert('Catatan baru berhasil disimpan!'); window.location='dashboard.php';</script>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pencatatan - DompetKos</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { darkMode: 'class' }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f3f4f6; display: flex; justify-content: center; transition: background 0.3s; }
        .app-container { 
            width: 100%; max-width: 414px; min-height: 100vh; 
            background: linear-gradient(to bottom, #a7f3d0, #3b82f6); 
            position: relative; box-shadow: 0 0 15px rgba(0,0,0,0.1); 
            transition: background 0.4s ease-in-out; 
        }
        
        /* STYLE KHUSUS TOMBOL KATEGORI AKTIF */
        .btn-kategori.active { background-color: #2563eb; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
        html.dark .btn-kategori.active { background-color: #1e40af; } 

        /* EFEK MODE GELAP BIRU REDUP UNTUK PENCATATAN */
        html.dark body { background-color: #020617; }
        html.dark .app-container {
            background: linear-gradient(to bottom, #1e3a8a, #0f172a);
            box-shadow: 0 0 20px rgba(0,0,0,0.8);
        }
    </style>
</head>
<body class="dark:bg-gray-900 transition-colors duration-300">
<div class="app-container font-sans flex flex-col pb-6">
    
    <div class="p-5 flex items-center text-white bg-blue-500/30 dark:bg-black/20 backdrop-blur-md shadow-sm">
        <a href="<?= $edit_mode ? 'riwayat.php?date='.$default_date : 'dashboard.php' ?>" class="mr-4 hover:text-gray-200 dark:hover:text-gray-300 transition-colors"><i class="fas fa-arrow-left text-lg"></i></a>
        <h1 class="text-xl font-bold tracking-wide dark:text-gray-200"><?= $edit_mode ? 'Edit Catatan' : 'Catat Keuangan' ?></h1>
    </div>

    <form action="" method="POST" class="flex-grow flex flex-col p-4 mt-2 relative z-10" autocomplete="off">
        
        <input type="hidden" name="edit_id" value="<?= htmlspecialchars($edit_id) ?>">

        <div class="flex gap-4 mb-4 justify-center mt-2">
            <label class="cursor-pointer">
                <input type="radio" name="jenis_transaksi" value="pengeluaran" class="peer sr-only" <?= $default_type == 'pengeluaran' ? 'checked' : '' ?>>
                <div class="px-5 py-2.5 rounded-full bg-white/30 dark:bg-black/30 text-white dark:text-gray-300 font-semibold peer-checked:bg-red-500 dark:peer-checked:bg-red-700 peer-checked:text-white peer-checked:shadow-lg transition">
                    <i class="fas fa-arrow-up text-sm mr-1"></i> Pengeluaran
                </div>
            </label>
            <label class="cursor-pointer">
                <input type="radio" name="jenis_transaksi" value="pemasukan" class="peer sr-only" <?= $default_type == 'pemasukan' ? 'checked' : '' ?>>
                <div class="px-5 py-2.5 rounded-full bg-white/30 dark:bg-black/30 text-white dark:text-gray-300 font-semibold peer-checked:bg-green-500 dark:peer-checked:bg-green-700 peer-checked:text-white peer-checked:shadow-lg transition">
                    <i class="fas fa-arrow-down text-sm mr-1"></i> Pemasukan
                </div>
            </label>
        </div>

        <div class="flex justify-end items-end mb-4 h-16">
            <input type="text" name="nominal" id="display" value="<?= htmlspecialchars($default_amount) ?>" class="w-full bg-transparent text-right text-5xl font-extrabold text-gray-800 dark:text-gray-200 outline-none placeholder-gray-500/50 dark:placeholder-gray-500 transition-colors" placeholder="0" readonly required>
        </div>

        <div class="mb-4">
            <input type="text" name="keterangan" value="<?= htmlspecialchars($default_description) ?>" placeholder="Tulis keterangan (Opsional)..." class="w-full bg-white/50 dark:bg-black/30 border-none rounded-xl p-3 text-sm text-gray-800 dark:text-gray-200 font-semibold outline-none focus:bg-white/80 dark:focus:bg-black/50 transition placeholder-gray-600 dark:placeholder-gray-400 shadow-sm border border-transparent dark:border-white/5">
        </div>

        <input type="hidden" name="kategori" id="kategori_input" value="<?= htmlspecialchars($default_category) ?>">

        <div class="grid grid-cols-4 gap-2 bg-blue-500/20 dark:bg-black/20 p-2 rounded-xl mb-4 text-white dark:text-gray-300 shadow-inner dark:border dark:border-white/5 transition-colors">
            <button type="button" onclick="setKategori('Makanan', this)" title="Makanan" class="btn-kategori p-2 hover:bg-blue-500/80 rounded-lg transition <?= $default_category == 'Makanan' ? 'active' : '' ?>"><i class="fas fa-hamburger text-lg"></i></button>
            <button type="button" onclick="setKategori('Transportasi', this)" title="Transportasi" class="btn-kategori p-2 hover:bg-blue-500/80 rounded-lg transition <?= $default_category == 'Transportasi' ? 'active' : '' ?>"><i class="fas fa-motorcycle text-lg"></i></button>
            <button type="button" onclick="setKategori('Belanja', this)" title="Belanja" class="btn-kategori p-2 hover:bg-blue-500/80 rounded-lg transition <?= $default_category == 'Belanja' ? 'active' : '' ?>"><i class="fas fa-shopping-cart text-lg"></i></button>
            <button type="button" onclick="setKategori('Laundry', this)" title="Laundry" class="btn-kategori p-2 hover:bg-blue-500/80 rounded-lg transition <?= $default_category == 'Laundry' ? 'active' : '' ?>"><i class="fas fa-tshirt text-lg"></i></button>
            <button type="button" onclick="setKategori('Listrik', this)" title="Listrik" class="btn-kategori p-2 hover:bg-blue-500/80 rounded-lg transition <?= $default_category == 'Listrik' ? 'active' : '' ?>"><i class="fas fa-bolt text-lg"></i></button>
            <button type="button" onclick="setKategori('Nongkrong', this)" title="Nongkrong" class="btn-kategori p-2 hover:bg-blue-500/80 rounded-lg transition <?= $default_category == 'Nongkrong' ? 'active' : '' ?>"><i class="fas fa-coffee text-lg"></i></button>
            <button type="button" onclick="setKategori('Hiburan', this)" title="Hiburan" class="btn-kategori p-2 hover:bg-blue-500/80 rounded-lg transition <?= $default_category == 'Hiburan' ? 'active' : '' ?>"><i class="fas fa-gamepad text-lg"></i></button>
            <button type="button" onclick="setKategori('Lainnya', this)" title="Lainnya" class="btn-kategori p-2 hover:bg-blue-500/80 rounded-lg transition <?= $default_category == 'Lainnya' ? 'active' : '' ?>"><i class="fas fa-wallet text-lg"></i></button>
        </div>

        <div class="grid grid-cols-4 gap-2.5 bg-blue-100/50 dark:bg-black/20 p-4 rounded-3xl mt-auto shadow-inner backdrop-blur-sm border border-transparent dark:border-white/5 transition-colors">
            <button type="button" onclick="clearDisplay()" class="p-4 bg-gray-200/80 dark:bg-black/40 rounded-full font-bold text-xl hover:bg-gray-300 dark:hover:bg-black/60 transition dark:text-gray-300">AC</button>
            <button type="button" onclick="hapusSatu()" class="p-4 bg-gray-200/80 dark:bg-black/40 rounded-full font-bold text-xl hover:bg-gray-300 dark:hover:bg-black/60 transition dark:text-gray-300"><i class="fas fa-backspace"></i></button>
            <button type="button" class="p-4 bg-gray-200/80 dark:bg-black/40 rounded-full font-bold text-xl hover:bg-gray-300 dark:hover:bg-black/60 transition dark:text-gray-300">%</button>
            <button type="button" onclick="appendOperator('/')" class="p-4 bg-blue-300/80 dark:bg-blue-800/60 rounded-full font-bold text-xl hover:bg-blue-400 dark:hover:bg-blue-700 transition dark:text-gray-200">÷</button>
            
            <button type="button" onclick="appendNumber('7')" class="p-4 bg-white dark:bg-white/10 rounded-full font-bold text-xl shadow-sm hover:bg-gray-50 dark:hover:bg-white/20 transition dark:text-gray-200">7</button>
            <button type="button" onclick="appendNumber('8')" class="p-4 bg-white dark:bg-white/10 rounded-full font-bold text-xl shadow-sm hover:bg-gray-50 dark:hover:bg-white/20 transition dark:text-gray-200">8</button>
            <button type="button" onclick="appendNumber('9')" class="p-4 bg-white dark:bg-white/10 rounded-full font-bold text-xl shadow-sm hover:bg-gray-50 dark:hover:bg-white/20 transition dark:text-gray-200">9</button>
            <button type="button" onclick="appendOperator('*')" class="p-4 bg-blue-300/80 dark:bg-blue-800/60 rounded-full font-bold text-xl hover:bg-blue-400 dark:hover:bg-blue-700 transition dark:text-gray-200">x</button>
            
            <button type="button" onclick="appendNumber('4')" class="p-4 bg-white dark:bg-white/10 rounded-full font-bold text-xl shadow-sm hover:bg-gray-50 dark:hover:bg-white/20 transition dark:text-gray-200">4</button>
            <button type="button" onclick="appendNumber('5')" class="p-4 bg-white dark:bg-white/10 rounded-full font-bold text-xl shadow-sm hover:bg-gray-50 dark:hover:bg-white/20 transition dark:text-gray-200">5</button>
            <button type="button" onclick="appendNumber('6')" class="p-4 bg-white dark:bg-white/10 rounded-full font-bold text-xl shadow-sm hover:bg-gray-50 dark:hover:bg-white/20 transition dark:text-gray-200">6</button>
            <button type="button" onclick="appendOperator('-')" class="p-4 bg-blue-300/80 dark:bg-blue-800/60 rounded-full font-bold text-xl hover:bg-blue-400 dark:hover:bg-blue-700 transition dark:text-gray-200">-</button>
            
            <button type="button" onclick="appendNumber('1')" class="p-4 bg-white dark:bg-white/10 rounded-full font-bold text-xl shadow-sm hover:bg-gray-50 dark:hover:bg-white/20 transition dark:text-gray-200">1</button>
            <button type="button" onclick="appendNumber('2')" class="p-4 bg-white dark:bg-white/10 rounded-full font-bold text-xl shadow-sm hover:bg-gray-50 dark:hover:bg-white/20 transition dark:text-gray-200">2</button>
            <button type="button" onclick="appendNumber('3')" class="p-4 bg-white dark:bg-white/10 rounded-full font-bold text-xl shadow-sm hover:bg-gray-50 dark:hover:bg-white/20 transition dark:text-gray-200">3</button>
            <button type="button" onclick="appendOperator('+')" class="p-4 bg-blue-300/80 dark:bg-blue-800/60 rounded-full font-bold text-xl hover:bg-blue-400 dark:hover:bg-blue-700 transition dark:text-gray-200">+</button>
            
            <button type="button" onclick="appendNumber('00')" class="p-4 bg-white dark:bg-white/10 rounded-full font-bold text-xl shadow-sm hover:bg-gray-50 dark:hover:bg-white/20 transition col-span-2 dark:text-gray-200">00</button>
            <button type="button" onclick="appendNumber('0')" class="p-4 bg-white dark:bg-white/10 rounded-full font-bold text-xl shadow-sm hover:bg-gray-50 dark:hover:bg-white/20 transition dark:text-gray-200">0</button>
            <button type="button" onclick="calculate()" class="p-4 bg-blue-600 text-white rounded-full font-bold text-xl shadow-md hover:bg-blue-700 transition">=</button>

            <button type="submit" name="simpan_transaksi" class="col-span-4 mt-2 bg-[#2a40a3] dark:bg-[#1a2866] text-white p-3.5 rounded-xl font-bold text-lg hover:bg-blue-900 dark:hover:bg-[#111a45] transition shadow-lg flex items-center justify-center gap-2 border border-transparent dark:border-white/5">
                <i class="fas <?= $edit_mode ? 'fa-save' : 'fa-check' ?>"></i> 
                <?= $edit_mode ? 'Simpan Perubahan' : 'Simpan Catatan' ?>
            </button>
        </div>
    </form>
</div>

<script>
    if (localStorage.getItem('theme') === 'dark') {
        document.documentElement.classList.add('dark');
    }

    let display = document.getElementById('display');
    let kategoriInput = document.getElementById('kategori_input');

    function appendNumber(num) {
        if(display.value === '0') display.value = '';
        display.value += num;
    }

    function appendOperator(op) {
        let lastChar = display.value.slice(-2);
        if(lastChar !== ' ' + op) {
            display.value += ' ' + op + ' ';
        }
    }

    function clearDisplay() { display.value = ''; }
    function hapusSatu() { display.value = display.value.toString().slice(0, -1); }

    function calculate() {
        try {
            let result = eval(display.value.replace(/ /g, ''));
            display.value = Math.floor(result); 
        } catch (e) {
            display.value = 'Error';
            setTimeout(clearDisplay, 1000);
        }
    }

    function setKategori(kat, btnElement) {
        kategoriInput.value = kat;
        
        let btns = document.querySelectorAll('.btn-kategori');
        btns.forEach(btn => btn.classList.remove('active'));
        btnElement.classList.add('active');
    }
</script>
</body>
</html>