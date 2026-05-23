<?php
require 'koneksi.php';

// Jika sudah login, langsung ke dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$error = "";

if (isset($_POST['register'])) {
    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];
    $konfirm  = $_POST['konfirm'] ?? $password; // fallback jika tidak ada field konfirm

    if (empty($username) || empty($email) || empty($password)) {
        $error = "Semua field wajib diisi!";
    } elseif (strlen($password) < 6) {
        $error = "Password minimal 6 karakter!";
    } elseif ($password !== $konfirm) {
        $error = "Konfirmasi password tidak cocok!";
    } else {
        // Cek email duplikat
        $cek = $conn->prepare("SELECT id FROM users WHERE email = ?");
        if (!$cek) { die("Prepare error: " . $conn->error); }
        $cek->bind_param("s", $email);
        $cek->execute();
        $cek->store_result();

        if ($cek->num_rows > 0) {
            $error = "Email sudah terdaftar! Silakan login.";
        } else {
            $hash   = password_hash($password, PASSWORD_DEFAULT);
            $insert = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            if (!$insert) { die("Prepare error: " . $conn->error); }
            $insert->bind_param("sss", $username, $email, $hash);

            if ($insert->execute()) {
                // AUTO LOGIN setelah register berhasil
                $new_id = $conn->insert_id;
                $_SESSION['user_id']  = $new_id;
                $_SESSION['username'] = $username;
                // Langsung ke dashboard, tidak perlu login lagi
                header("Location: dashboard.php");
                exit;
            } else {
                $error = "Gagal menyimpan data: " . $insert->error;
            }
        }
        $cek->close();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar | DompetKos</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script> tailwind.config = { darkMode: 'class' } </script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; -webkit-tap-highlight-color: transparent; }
        .input-field {
            width: 100%; padding: 14px 18px; border-radius: 16px;
            background: #f8fafc; border: 1.5px solid #e2e8f0;
            outline: none; transition: all 0.2s; font-size: 14px;
            font-weight: 600; color: #1e293b;
        }
        .input-field:focus {
            border-color: #2a40a3;
            box-shadow: 0 0 0 4px rgba(42,64,163,0.08);
            background: white;
        }
        .btn-primary {
            width: 100%; background: linear-gradient(135deg, #2a40a3, #4f8cf6);
            color: white; font-weight: 800; padding: 15px; border-radius: 16px;
            border: none; cursor: pointer; transition: all 0.2s; font-size: 15px;
            box-shadow: 0 8px 20px rgba(42,64,163,0.3);
        }
        .btn-primary:hover { opacity: 0.92; transform: translateY(-1px); }
        .btn-primary:active { transform: scale(0.98); }
        .btn-primary:disabled { opacity: 0.7; cursor: not-allowed; transform: none; }
        .strength-bar { height: 4px; border-radius: 4px; transition: all 0.3s; background: #e2e8f0; }
    </style>
</head>
<body class="bg-[#f4f7fe] min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-4xl bg-white rounded-[2.5rem] shadow-[0_20px_60px_rgba(0,0,0,0.08)] flex overflow-hidden" style="min-height:580px">

        <!-- LEFT PANEL -->
        <div class="hidden md:flex w-[420px] flex-shrink-0 bg-gradient-to-br from-[#2a40a3] to-[#4f8cf6] flex-col items-center justify-center p-10 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-56 h-56 bg-white opacity-5 rounded-full blur-3xl transform translate-x-1/3 -translate-y-1/3"></div>
            <div class="absolute bottom-0 left-0 w-40 h-40 bg-white opacity-5 rounded-full blur-2xl transform -translate-x-1/2 translate-y-1/2"></div>
            <div class="relative z-10 flex flex-col items-center text-center w-full">
                <div class="w-28 h-28 bg-white/20 rounded-3xl flex items-center justify-center mb-6 shadow-xl border border-white/10 overflow-hidden">
                    <img src="images/dompetkos.png" alt="DompetKos" class="w-full h-full object-contain"
                         onerror="this.parentElement.innerHTML='<i class=\'fas fa-wallet text-white text-4xl\'></i>'">
                </div>
                <h1 class="text-3xl font-extrabold text-white mb-2 tracking-tight">DompetKos</h1>
                <p class="text-blue-200 text-sm font-medium mb-10">Keuangan Rapih, Hidup Lebih Pasti</p>
                <div class="bg-white/10 backdrop-blur-sm border border-white/10 rounded-2xl p-5 w-full text-left space-y-3">
                    <div class="flex items-center gap-3 text-white">
                        <div class="w-8 h-8 bg-white/20 rounded-xl flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-shield-alt text-sm"></i>
                        </div>
                        <span class="text-sm font-semibold">Data aman & terenkripsi</span>
                    </div>
                    <div class="flex items-center gap-3 text-white">
                        <div class="w-8 h-8 bg-white/20 rounded-xl flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-bolt text-sm"></i>
                        </div>
                        <span class="text-sm font-semibold">Pencatatan cepat & mudah</span>
                    </div>
                    <div class="flex items-center gap-3 text-white">
                        <div class="w-8 h-8 bg-white/20 rounded-xl flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-mobile-alt text-sm"></i>
                        </div>
                        <span class="text-sm font-semibold">Bisa diakses di semua perangkat</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- RIGHT PANEL -->
        <div class="flex-1 flex items-center justify-center p-8 md:p-12 overflow-y-auto">
            <div class="w-full max-w-sm">

                <!-- Header Mobile -->
                <div class="flex md:hidden items-center gap-3 mb-8">
                    <div class="w-10 h-10 bg-gradient-to-br from-[#2a40a3] to-[#4f8cf6] rounded-2xl flex items-center justify-center shadow-md">
                        <i class="fas fa-wallet text-white text-sm"></i>
                    </div>
                    <span class="text-xl font-extrabold text-gray-900">DompetKos</span>
                </div>

                <div class="mb-8">
                    <h2 class="text-2xl font-extrabold text-gray-900">Buat Akun</h2>
                    <p class="text-gray-400 text-sm font-medium mt-1">Lengkapi data untuk memulai</p>
                </div>

                <?php if ($error): ?>
                    <div class="bg-red-50 text-red-600 p-4 rounded-2xl text-sm mb-6 flex items-center gap-3 border border-red-100">
                        <i class="fas fa-exclamation-circle text-red-500 flex-shrink-0"></i>
                        <span class="font-semibold"><?= htmlspecialchars($error) ?></span>
                    </div>
                <?php endif; ?>

                <form method="POST" id="regForm" class="space-y-5" autocomplete="off">

                    <!-- Email -->
                    <div>
                        <label class="block text-xs font-extrabold text-gray-400 uppercase tracking-widest mb-2">Email</label>
                        <div class="relative">
                            <i class="fas fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-gray-300 text-sm pointer-events-none"></i>
                            <input type="email" name="email" required
                                   class="input-field pl-11"
                                   placeholder="contoh@email.com"
                                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                        </div>
                    </div>

                    <!-- Username -->
                    <div>
                        <label class="block text-xs font-extrabold text-gray-400 uppercase tracking-widest mb-2">Username</label>
                        <div class="relative">
                            <i class="fas fa-user absolute left-4 top-1/2 -translate-y-1/2 text-gray-300 text-sm pointer-events-none"></i>
                            <input type="text" name="username" required
                                   class="input-field pl-11"
                                   placeholder="Nama pengguna"
                                   value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                        </div>
                    </div>

                    <!-- Password -->
                    <div>
                        <label class="block text-xs font-extrabold text-gray-400 uppercase tracking-widest mb-2">Password</label>
                        <div class="relative">
                            <i class="fas fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-gray-300 text-sm pointer-events-none"></i>
                            <input type="password" name="password" id="pwInput" required
                                   class="input-field pl-11 pr-12"
                                   placeholder="Min. 6 karakter"
                                   oninput="checkStrength(this.value)">
                            <button type="button" onclick="togglePw('pwInput','pwIcon')"
                                    class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-300 hover:text-gray-500 transition">
                                <i class="fas fa-eye text-sm" id="pwIcon"></i>
                            </button>
                        </div>
                        <div class="mt-2 flex gap-1">
                            <div class="strength-bar flex-1" id="s1"></div>
                            <div class="strength-bar flex-1" id="s2"></div>
                            <div class="strength-bar flex-1" id="s3"></div>
                        </div>
                        <p class="text-[11px] mt-1 font-semibold" id="strengthText"></p>
                    </div>

                    <!-- Konfirmasi Password -->
                    <div>
                        <label class="block text-xs font-extrabold text-gray-400 uppercase tracking-widest mb-2">Konfirmasi Password</label>
                        <div class="relative">
                            <i class="fas fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-gray-300 text-sm pointer-events-none"></i>
                            <input type="password" name="konfirm" id="konfirmInput" required
                                   class="input-field pl-11 pr-12"
                                   placeholder="Ulangi password">
                            <button type="button" onclick="togglePw('konfirmInput','konfirmIcon')"
                                    class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-300 hover:text-gray-500 transition">
                                <i class="fas fa-eye text-sm" id="konfirmIcon"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" name="register" id="btnReg" class="btn-primary mt-2">
                        Buat Akun Sekarang
                    </button>
                </form>

                <p class="text-center text-gray-400 text-sm mt-8 font-medium">
                    Sudah punya akun?
                    <a href="login.php" class="text-[#2a40a3] font-extrabold hover:underline">Masuk Sekarang</a>
                </p>
            </div>
        </div>
    </div>

<script>
    function togglePw(inputId, iconId) {
        const input = document.getElementById(inputId);
        const icon  = document.getElementById(iconId);
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    }

    function checkStrength(val) {
        const s1  = document.getElementById('s1');
        const s2  = document.getElementById('s2');
        const s3  = document.getElementById('s3');
        const txt = document.getElementById('strengthText');
        [s1, s2, s3].forEach(b => b.style.background = '#e2e8f0');
        if (!val) { txt.textContent = ''; return; }
        let score = 0;
        if (val.length >= 6) score++;
        if (/[A-Z]/.test(val) || /[0-9]/.test(val)) score++;
        if (/[^A-Za-z0-9]/.test(val) && val.length >= 8) score++;
        const map = {
            1: { bars: [s1],         color: '#ef4444', label: 'Lemah' },
            2: { bars: [s1, s2],     color: '#f59e0b', label: 'Cukup' },
            3: { bars: [s1, s2, s3], color: '#10b981', label: 'Kuat'  },
        };
        if (map[score]) {
            map[score].bars.forEach(b => b.style.background = map[score].color);
            txt.textContent = map[score].label;
            txt.style.color = map[score].color;
        }
    }

    document.getElementById('regForm').onsubmit = function () {
        const pw  = document.getElementById('pwInput').value;
        const kfm = document.getElementById('konfirmInput').value;
        if (pw !== kfm) {
            alert('Password dan konfirmasi tidak cocok!');
            return false;
        }
        if (pw.length < 6) {
            alert('Password minimal 6 karakter!');
            return false;
        }
        const btn = document.getElementById('btnReg');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Memproses...';
    };
</script>
</body>
</html>