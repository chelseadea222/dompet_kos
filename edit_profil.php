<?php
require 'koneksi.php';

// 1. PASTIKAN USER SUDAH LOGIN
if (!isset($_COOKIE['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_COOKIE['user_id'];

// 2. AMBIL DATA USER TERBARU DARI DATABASE
$query = $conn->query("SELECT * FROM users WHERE id = '$user_id'");
if (!$query) {
    die("Gagal mengambil data user: " . $conn->error);
}
$user = $query->fetch_assoc();

// Tentukan path foto profil saat ini
$foto_sekarang = (!empty($user['profile_pic']) && file_exists('uploads/' . $user['profile_pic'])) 
                 ? 'uploads/' . $user['profile_pic'] 
                 : null;

// 3. PROSES KETIKA TOMBOL SIMPAN DIPENCET
if (isset($_POST['update_profil'])) {
    $username_baru = $conn->real_escape_string($_POST['username']);
    $email_baru = $conn->real_escape_string($_POST['email']);
    $password_baru = $_POST['password'];

    $pindah_foto_sukses = true;
    $nama_file_baru = $user['profile_pic']; // Default pakai nama file lama

    // A. PROSES UNGGAH FOTO (JIKA ADA FILE YANG DIPILIH)
    if (isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] == 0) {
        $file_name = $_FILES['foto_profil']['name'];
        $file_size = $_FILES['foto_profil']['size'];
        $file_tmp = $_FILES['foto_profil']['tmp_name'];
        
        // Cek Ekstensi File
        $ekstensi_diperbolehkan = array('jpg', 'jpeg', 'png');
        $x = explode('.', $file_name);
        $ekstensi = strtolower(end($x));

        // Validasi Ukuran (Maksimal 2MB)
        if ($file_size > 2097152) {
            $error = "❌ Ukuran foto terlalu besar! Maksimal adalah 2MB.";
            $pindah_foto_sukses = false;
        } 
        // Validasi Ekstensi
        elseif (!in_array($ekstensi, $ekstensi_diperbolehkan)) {
            $error = "❌ Format file salah! Hanya diperbolehkan JPG, JPEG, atau PNG.";
            $pindah_foto_sukses = false;
        } 
        // Jika lolos validasi, pindahkan file
        else {
            // Otomatis buat folder 'uploads' jika belum ada
            if (!file_exists('uploads')) {
                mkdir('uploads', 0777, true);
            }

            // Hapus foto profil lama dari folder biar tidak menumpuk sampah
            if (!empty($user['profile_pic']) && file_exists('uploads/' . $user['profile_pic'])) {
                unlink('uploads/' . $user['profile_pic']);
            }

            // Acak nama file baru agar tidak bentrok antar pengguna
            $nama_file_baru = 'profil_' . $user_id . '_' . time() . '.' . $ekstensi;
            
            if (!move_uploaded_file($file_tmp, 'uploads/' . $nama_file_baru)) {
                $error = "❌ Sistem gagal memindahkan file ke folder 'uploads'. Pastikan folder tersebut memiliki izin akses menulis (write permission).";
                $pindah_foto_sukses = false;
                $nama_file_baru = $user['profile_pic']; 
            }
        }
    }

    // B. JALANKAN UPDATE DATABASE (JIKA PROSES FOTO AMAN)
    if ($pindah_foto_sukses) {
        // Cek apakah email sudah dipakai orang lain
        $cek_email = $conn->query("SELECT id FROM users WHERE email = '$email_baru' AND id != '$user_id'");
        
        if ($cek_email && $cek_email->num_rows > 0) {
            $error = "❌ Email sudah terdaftar pada akun lain!";
        } else {
            // Struktur dasar query SQL update
            $sql = "UPDATE users SET username = '$username_baru', email = '$email_baru', profile_pic = '$nama_file_baru'";
            
            // Tambahkan update password jika kolom password baru diisi
            if (!empty($password_baru)) {
                $hashed_password = password_hash($password_baru, PASSWORD_DEFAULT);
                $sql .= ", password = '$hashed_password'";
            }
            
            $sql .= " WHERE id = '$user_id'";
            
            // Eksekusi query ke database
            // Eksekusi query ke database
            if ($conn->query($sql)) {
                // Perbarui nama di $_COOKIE agar nama di dashboard langsung berubah
                $_COOKIE['username'] = $username_baru;
                
                // MENGARAHKAN KEMBALI KE HALAMAN PROFIL
                echo "<script>
                        alert('Profil berhasil diperbarui!');
                        window.location.href = 'profil.php';
                      </script>";
                exit;
            } else {
                // FITUR PELACAK ERROR DATABASE
                $error = "❌ Gagal menyimpan ke database. Pesan Sistem: " . $conn->error;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profil - DompetKos</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { darkMode: 'class' }</script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style> 
        body { display: flex; justify-content: center; background-color: #f3f4f6; } 
        .app { 
            max-width: 414px; min-height: 100vh; width: 100%; 
            background: linear-gradient(to bottom, #4f8cf6, #b4eedb); 
            position: relative; box-shadow: 0 0 15px rgba(0,0,0,0.1); 
            transition: background 0.3s ease; padding-bottom: 40px; 
        } 
        html.dark .app { background: linear-gradient(to bottom, #1f2937, #111827); }
        input:-webkit-autofill { -webkit-box-shadow: 0 0 0 30px rgba(255, 255, 255, 0.5) inset !important; }
        input::-ms-reveal, input::-ms-clear { display: none; }
    </style>
</head>
<body class="dark:bg-gray-900 transition-colors duration-300">
<div class="app flex flex-col">
    
    <div class="p-6 flex items-center text-white bg-blue-600/30 dark:bg-gray-800/50 backdrop-blur-md shadow-sm">
        <a href="profil.php" class="mr-4 hover:text-gray-200 transition"><i class="fas fa-arrow-left text-xl"></i></a>
        <h1 class="text-xl font-bold">Edit Profil</h1>
    </div>

    <div class="p-6 flex-grow">
        
        <form method="POST" action="" enctype="multipart/form-data" class="flex flex-col gap-4 mt-2" autocomplete="off">
            
            <div class="flex flex-col items-center mb-6 relative">
                <div id="preview-container" class="w-28 h-28 bg-gray-300 rounded-full flex items-center justify-center text-5xl text-gray-500 shadow-lg border-4 border-white dark:border-gray-700 overflow-hidden">
                    <?php if($foto_sekarang): ?>
                        <img src="<?= $foto_sekarang ?>" alt="Profil" class="w-full h-full object-cover">
                    <?php else: ?>
                        <i class="fas fa-user"></i>
                    <?php endif; ?>
                </div>
                
                <label for="foto_profil" class="mt-3 text-xs font-bold bg-[#2a40a3] dark:bg-gray-700 text-white px-4 py-2 rounded-full cursor-pointer shadow-md hover:bg-blue-800 transition flex items-center gap-1.5">
                    <i class="fas fa-camera"></i> Ganti Foto
                </label>
                
                <input type="file" name="foto_profil" id="foto_profil" class="sr-only" accept="image/png, image/jpeg, image/jpg">
                <p id="file-chosen" class="text-[10px] text-white dark:text-gray-400 mt-1 font-mono tracking-wide"></p>
            </div>
            
            <?php if(isset($error)): ?>
                <div class="bg-red-500 text-white p-3 rounded-lg mb-2 text-sm font-semibold shadow-md flex items-start gap-2">
                    <i class="fas fa-times-circle mt-0.5 flex-shrink-0"></i> 
                    <span><?= $error ?></span>
                </div>
            <?php endif; ?>
            
            <?php if(isset($sukses)): ?>
                <div class="bg-green-500 text-white p-3 rounded-lg mb-2 text-sm font-semibold shadow-md flex items-center gap-2">
                    <i class="fas fa-check-circle flex-shrink-0"></i> 
                    <span><?= $sukses ?></span>
                </div>
            <?php endif; ?>

            <div class="flex flex-col gap-1.5">
                <label class="text-sm font-bold text-white dark:text-gray-300">Nama / Username</label>
                <div class="relative w-full">
                    <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-gray-600 dark:text-gray-400"><i class="far fa-user"></i></div>
                    <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required autocomplete="new-username" class="w-full py-3 pl-10 pr-4 rounded-lg bg-white/60 dark:bg-gray-700 dark:text-white border border-blue-300/50 dark:border-gray-600 outline-none focus:ring-2 focus:ring-blue-400">
                </div>
            </div>

            <div class="flex flex-col gap-1.5">
                <label class="text-sm font-bold text-white dark:text-gray-300">Email</label>
                <div class="relative w-full">
                    <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-gray-600 dark:text-gray-400"><i class="far fa-envelope"></i></div>
                    <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required autocomplete="new-email" class="w-full py-3 pl-10 pr-4 rounded-lg bg-white/60 dark:bg-gray-700 dark:text-white border border-blue-300/50 dark:border-gray-600 outline-none focus:ring-2 focus:ring-blue-400">
                </div>
            </div>

            <div class="flex flex-col gap-1.5">
                <label class="text-sm font-bold text-white dark:text-gray-300">Password Baru <span class="font-normal text-xs text-white/70 dark:text-gray-400">(Opsional)</span></label>
                <div class="relative w-full">
                    <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-gray-600 dark:text-gray-400"><i class="fas fa-lock"></i></div>
                    <input type="password" name="password" id="password_input" placeholder="Isi jika ingin mengganti..." autocomplete="new-password" class="w-full py-3 pl-10 pr-12 rounded-lg bg-white/60 dark:bg-gray-700 dark:text-white border border-blue-300/50 dark:border-gray-600 outline-none focus:ring-2 focus:ring-blue-400 placeholder-gray-500">
                    <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-4 flex items-center text-gray-600 dark:text-gray-400 hover:text-blue-900 transition">
                        <i class="far fa-eye" id="eye_icon"></i>
                    </button>
                </div>
            </div>

            <button type="submit" name="update_profil" class="bg-[#2a40a3] dark:bg-blue-600 text-white p-3.5 rounded-lg font-bold mt-5 shadow-lg hover:bg-blue-800 dark:hover:bg-blue-700 transition transform active:scale-95 text-center">
                <i class="fas fa-save mr-2"></i> Simpan Perubahan
            </button>
        </form>
    </div>
</div>

<script>
    // SCRIPT LIVE PREVIEW IMAGE
    const fotoInput = document.getElementById('foto_profil');
    const fileChosen = document.getElementById('file-chosen');
    const previewContainer = document.getElementById('preview-container');

    fotoInput.addEventListener('change', function(event){
        const file = event.target.files[0];
        if(file) {
            fileChosen.textContent = file.name;
            
            const reader = new FileReader();
            reader.onload = function(e) {
                previewContainer.innerHTML = `<img src="${e.target.result}" alt="Preview" class="w-full h-full object-cover">`;
            }
            reader.readAsDataURL(file);
        }
    });

    // SCRIPT LIHAT PASSWORD
    function togglePassword() {
        var passwordInput = document.getElementById("password_input");
        var eyeIcon = document.getElementById("eye_icon");
        if (passwordInput.type === "password") {
            passwordInput.type = "text"; eyeIcon.className = "far fa-eye-slash";
        } else {
            passwordInput.type = "password"; eyeIcon.className = "far fa-eye";
        }
    }

    // SINKRONISASI MODE GELAP
    if (localStorage.getItem('theme') === 'dark') { document.documentElement.classList.add('dark'); }
</script>
</body>
</html>