<?php
require 'koneksi.php';

// 1. PASTIKAN USER SUDAH LOGIN
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['user_id'])) { // BENAR: Menggunakan session
    header("Location: login.php");
    exit;
}
$user_id = $_SESSION['user_id'];

// 2. AMBIL DATA USER TERBARU DARI DATABASE
$query = $conn->query("SELECT * FROM users WHERE id = '$user_id'");
if (!$query) {
    die("Gagal mengambil data user: " . $conn->error);
}
$user = $query->fetch_assoc();

$foto_sekarang = (!empty($user['profile_pic']) && file_exists('uploads/' . $user['profile_pic'])) 
                 ? 'uploads/' . $user['profile_pic'] 
                 : null;

// 3. PROSES KETIKA TOMBOL SIMPAN DIPENCET
if (isset($_POST['update_profil'])) {
    $username_baru = $conn->real_escape_string($_POST['username']);
    $email_baru = $conn->real_escape_string($_POST['email']);
    $password_baru = $_POST['password'];

    $pindah_foto_sukses = true;
    $nama_file_baru = $user['profile_pic'];

    if (isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] == 0) {
        $file_name = $_FILES['foto_profil']['name'];
        $file_size = $_FILES['foto_profil']['size'];
        $file_tmp  = $_FILES['foto_profil']['tmp_name'];
        $ekstensi_diperbolehkan = array('jpg', 'jpeg', 'png');
        $x = explode('.', $file_name);
        $ekstensi = strtolower(end($x));

        if ($file_size > 2097152) {
            $error = "Ukuran foto terlalu besar! Maksimal adalah 2MB.";
            $pindah_foto_sukses = false;
        } elseif (!in_array($ekstensi, $ekstensi_diperbolehkan)) {
            $error = "Format file salah! Hanya diperbolehkan JPG, JPEG, atau PNG.";
            $pindah_foto_sukses = false;
        } else {
            if (!file_exists('uploads')) { mkdir('uploads', 0777, true); }
            if (!empty($user['profile_pic']) && file_exists('uploads/' . $user['profile_pic'])) {
                unlink('uploads/' . $user['profile_pic']);
            }
            $nama_file_baru = 'profil_' . $user_id . '_' . time() . '.' . $ekstensi;
            if (!move_uploaded_file($file_tmp, 'uploads/' . $nama_file_baru)) {
                $error = "Gagal memindahkan file. Periksa write permission folder 'uploads'.";
                $pindah_foto_sukses = false;
                $nama_file_baru = $user['profile_pic'];
            }
        }
    }

    if ($pindah_foto_sukses) {
        $cek_email = $conn->query("SELECT id FROM users WHERE email = '$email_baru' AND id != '$user_id'");
        if ($cek_email && $cek_email->num_rows > 0) {
            $error = "Email sudah terdaftar pada akun lain!";
        } else {
            $sql = "UPDATE users SET username = '$username_baru', email = '$email_baru', profile_pic = '$nama_file_baru'";
            if (!empty($password_baru)) {
                $hashed_password = password_hash($password_baru, PASSWORD_DEFAULT);
                $sql .= ", password = '$hashed_password'";
            }
            $sql .= " WHERE id = '$user_id'";
            if ($conn->query($sql)) {
                $_SESSION['username'] = $username_baru;
                echo "<script>alert('Profil berhasil diperbarui!'); window.location.href = 'profil.php';</script>";
                exit;
            } else {
                $error = "Gagal menyimpan ke database. " . $conn->error;
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
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; -webkit-tap-highlight-color: transparent; }
        input:-webkit-autofill { -webkit-box-shadow: 0 0 0 30px #f9fafb inset !important; }
        .dark input:-webkit-autofill { -webkit-box-shadow: 0 0 0 30px #1e293b inset !important; -webkit-text-fill-color: #e2e8f0 !important; }
        input::-ms-reveal, input::-ms-clear { display: none; }

        /* Avatar ring pulse on hover */
        #avatar-ring:hover { box-shadow: 0 0 0 4px rgba(42,64,163,0.15); }

        /* File input label hover */
        .btn-ganti:hover { transform: translateY(-1px); }
        .btn-ganti { transition: transform 0.15s, opacity 0.15s; }

        /* Input focus glow */
        .field-input:focus { box-shadow: 0 0 0 3px rgba(79,140,246,0.25); }
    </style>
</head>
<body class="bg-[#f4f7fe] dark:bg-[#0f172a] transition-colors duration-300 md:flex min-h-screen text-gray-800 dark:text-gray-200">

    <!-- ═══════ SIDEBAR DESKTOP ═══════ -->
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
            <a href="pencatatan.php" class="flex items-center gap-4 text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800/50 hover:text-[#2a40a3] dark:hover:text-white px-5 py-4 rounded-2xl font-semibold transition group">
                <i class="fas fa-pen text-lg w-5 text-center group-hover:scale-110 transition-transform"></i> Catat
            </a>
            <a href="riwayat.php" class="flex items-center gap-4 text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800/50 hover:text-[#2a40a3] dark:hover:text-white px-5 py-4 rounded-2xl font-semibold transition group">
                <i class="fas fa-list text-lg w-5 text-center group-hover:scale-110 transition-transform"></i> Riwayat
            </a>
            <!-- Profil aktif -->
            <a href="profil.php" class="flex items-center gap-4 bg-blue-50 dark:bg-blue-900/20 text-[#2a40a3] dark:text-blue-400 px-5 py-4 rounded-2xl font-semibold">
                <i class="fas fa-user text-lg w-5 text-center"></i> Profil
            </a>
        </nav>
    </aside>

    <!-- ═══════ MAIN ═══════ -->
    <main class="w-full md:ml-[280px] min-h-screen pb-32 md:pb-12 pt-6 md:pt-10 px-4 md:px-10">
        <div class="max-w-xl mx-auto w-full flex flex-col gap-6">

            <!-- PAGE HEADER -->
            <div class="flex items-center gap-3 mb-2">
                <a href="profil.php" class="w-10 h-10 rounded-2xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 flex items-center justify-center text-gray-500 dark:text-gray-400 hover:text-[#2a40a3] dark:hover:text-blue-400 shadow-sm transition">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <h1 class="text-xl font-extrabold text-gray-900 dark:text-white tracking-tight">Edit Profil</h1>
                    <p class="text-xs text-gray-400 dark:text-gray-500 font-medium">Perbarui informasi akun kamu</p>
                </div>
            </div>

            <!-- FORM CARD -->
            <form method="POST" action="" enctype="multipart/form-data" autocomplete="off"
                  class="bg-white dark:bg-gray-800 rounded-[2.5rem] p-6 md:p-8 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-50 dark:border-gray-700 flex flex-col gap-5">

                <!-- ── AVATAR UPLOAD ── -->
                <div class="flex flex-col items-center pb-2">
                    <div id="avatar-ring" class="w-24 h-24 rounded-full overflow-hidden border-4 border-white dark:border-gray-700 shadow-xl transition-shadow duration-300 bg-gradient-to-br from-[#2a40a3] to-[#4f8cf6] flex items-center justify-center text-white text-4xl">
                        <div id="preview-container" class="w-full h-full flex items-center justify-center">
                            <?php if ($foto_sekarang): ?>
                                <img src="<?= $foto_sekarang ?>" alt="Profil" class="w-full h-full object-cover">
                            <?php else: ?>
                                <i class="fas fa-user opacity-80"></i>
                            <?php endif; ?>
                        </div>
                    </div>

                    <label for="foto_profil" class="btn-ganti mt-4 flex items-center gap-2 bg-gradient-to-r from-[#2a40a3] to-[#4f8cf6] text-white text-xs font-bold px-5 py-2.5 rounded-full cursor-pointer shadow-lg shadow-blue-500/20 hover:opacity-90">
                        <i class="fas fa-camera"></i> Ganti Foto
                    </label>
                    <input type="file" name="foto_profil" id="foto_profil" class="sr-only" accept="image/png,image/jpeg,image/jpg">
                    <p id="file-chosen" class="mt-2 text-[10px] text-gray-400 dark:text-gray-500 font-mono"></p>
                    <p class="text-[10px] text-gray-400 dark:text-gray-500 mt-0.5">JPG, JPEG, PNG · Maks 2MB</p>
                </div>

                <!-- ── PESAN ERROR ── -->
                <?php if (isset($error)): ?>
                    <div class="flex items-start gap-3 bg-red-50 dark:bg-red-900/20 border border-red-100 dark:border-red-800/40 text-red-600 dark:text-red-400 px-4 py-3.5 rounded-2xl text-sm font-semibold">
                        <i class="fas fa-times-circle mt-0.5 flex-shrink-0"></i>
                        <span><?= htmlspecialchars($error) ?></span>
                    </div>
                <?php endif; ?>

                <!-- ── FIELD: USERNAME ── -->
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest px-1">Nama / Username</label>
                    <div class="flex items-center gap-3 bg-gray-50 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-700 rounded-2xl px-4 py-3.5 transition focus-within:border-blue-300 dark:focus-within:border-blue-600">
                        <div class="w-8 h-8 rounded-xl bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center flex-shrink-0">
                            <i class="far fa-user text-[#2a40a3] dark:text-blue-400 text-sm"></i>
                        </div>
                        <input type="text" name="username"
                               value="<?= htmlspecialchars($user['username']) ?>"
                               required autocomplete="new-username"
                               class="field-input flex-1 bg-transparent outline-none text-sm font-semibold text-gray-800 dark:text-white placeholder-gray-400 dark:placeholder-gray-500">
                    </div>
                </div>

                <!-- ── FIELD: EMAIL ── -->
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest px-1">Email</label>
                    <div class="flex items-center gap-3 bg-gray-50 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-700 rounded-2xl px-4 py-3.5 transition focus-within:border-blue-300 dark:focus-within:border-blue-600">
                        <div class="w-8 h-8 rounded-xl bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center flex-shrink-0">
                            <i class="far fa-envelope text-[#2a40a3] dark:text-blue-400 text-sm"></i>
                        </div>
                        <input type="email" name="email"
                               value="<?= htmlspecialchars($user['email']) ?>"
                               required autocomplete="new-email"
                               class="field-input flex-1 bg-transparent outline-none text-sm font-semibold text-gray-800 dark:text-white placeholder-gray-400 dark:placeholder-gray-500">
                    </div>
                </div>

                <!-- ── FIELD: PASSWORD ── -->
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest px-1">
                        Password Baru <span class="font-normal normal-case tracking-normal">· opsional</span>
                    </label>
                    <div class="flex items-center gap-3 bg-gray-50 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-700 rounded-2xl px-4 py-3.5 transition focus-within:border-blue-300 dark:focus-within:border-blue-600">
                        <div class="w-8 h-8 rounded-xl bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-lock text-[#2a40a3] dark:text-blue-400 text-sm"></i>
                        </div>
                        <input type="password" name="password" id="password_input"
                               placeholder="Isi jika ingin mengganti..." autocomplete="new-password"
                               class="field-input flex-1 bg-transparent outline-none text-sm font-semibold text-gray-800 dark:text-white placeholder-gray-400 dark:placeholder-gray-500">
                        <button type="button" onclick="togglePassword()" class="text-gray-400 dark:text-gray-500 hover:text-[#2a40a3] dark:hover:text-blue-400 transition ml-1 flex-shrink-0">
                            <i class="far fa-eye text-sm" id="eye_icon"></i>
                        </button>
                    </div>
                </div>

                <!-- ── TOMBOL SIMPAN ── -->
                <button type="submit" name="update_profil"
                        class="mt-2 w-full bg-gradient-to-r from-[#2a40a3] to-[#4f8cf6] text-white py-4 rounded-2xl font-bold shadow-lg shadow-blue-500/20 hover:opacity-95 transition active:scale-[0.99] flex items-center justify-center gap-2 text-sm">
                    <i class="fas fa-save"></i> Simpan Perubahan
                </button>

            </form>

        </div>
    </main>

    <!-- ═══════ BOTTOM NAV MOBILE ═══════ -->
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
    // Live preview foto
    const fotoInput = document.getElementById('foto_profil');
    const fileChosen = document.getElementById('file-chosen');
    const previewContainer = document.getElementById('preview-container');

    fotoInput.addEventListener('change', function (e) {
        const file = e.target.files[0];
        if (file) {
            fileChosen.textContent = file.name;
            const reader = new FileReader();
            reader.onload = function (ev) {
                previewContainer.innerHTML = `<img src="${ev.target.result}" alt="Preview" class="w-full h-full object-cover">`;
            };
            reader.readAsDataURL(file);
        }
    });

    // Toggle password
    function togglePassword() {
        const input = document.getElementById('password_input');
        const icon  = document.getElementById('eye_icon');
        if (input.type === 'password') {
            input.type = 'text';
            icon.className = 'far fa-eye-slash text-sm';
        } else {
            input.type = 'password';
            icon.className = 'far fa-eye text-sm';
        }
    }

    // Sinkronisasi dark mode
    if (localStorage.getItem('theme') === 'dark') {
        document.documentElement.classList.add('dark');
    }
</script>
</body>
</html>