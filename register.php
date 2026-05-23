<?php
require 'koneksi.php';
$error = "";

// Menggunakan REQUEST_METHOD agar pasti terbaca saat form dikirim
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Menangkap data dari form
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Cek apakah email sudah ada
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows > 0) {
        $error = "Email sudah terdaftar!";
    } else {
        // Masukkan data ke database
        $insert = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $insert->bind_param("sss", $username, $email, $password);
        
        if ($insert->execute()) {
            // Jika berhasil, munculkan popup dan pindah ke login
            echo "<script>alert('Daftar berhasil! Silakan login.'); window.location.href='login.php';</script>";
            exit;
        } else {
            // Jika database error, tampilkan pesan error-nya
            $error = "Error Database: " . $conn->error;
        }
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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-[#F8FAFC] min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-5xl bg-white shadow-2xl rounded-[3rem] flex overflow-hidden min-h-[600px] my-6">
        
        <div class="hidden lg:flex w-full max-w-[414px] bg-gradient-to-b from-blue-500 to-green-200 p-8 flex-col justify-center relative shrink-0">
            <div class="text-center text-white flex flex-col items-center">
                <img src="images/dompetkos.png" alt="Logo DompetKos" class="w-45 h-45 object-contain mb-4 drop-shadow-md">
            </div>
            <div class="absolute -bottom-20 -right-20 w-64 h-64 bg-blue-600 rounded-full blur-3xl opacity-20"></div>
        </div>

        <div class="w-full lg:w-1/2 flex items-center justify-center p-8 lg:p-16">
            <div class="w-full max-w-sm">
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-slate-900">Buat Akun</h1>
                    <p class="text-slate-500 mt-2">Lengkapi data untuk memulai</p>
                </div>

                <?php if($error): ?>
                    <div class="bg-red-50 text-red-600 p-4 rounded-2xl text-sm mb-6 flex items-center gap-3 border border-red-100">
                        <i class="fas fa-exclamation-triangle"></i> <?= $error ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="space-y-5" autocomplete="off">
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Email</label>
                        <input type="email" name="email" required autocomplete="new-email"
                            class="w-full px-5 py-4 rounded-2xl bg-slate-50 border border-slate-100 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-600 outline-none transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Username</label>
                        <input type="text" name="username" required autocomplete="new-username"
                            class="w-full px-5 py-4 rounded-2xl bg-slate-50 border border-slate-100 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-600 outline-none transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Password</label>
                        <input type="password" name="password" required autocomplete="new-password"
                            class="w-full px-5 py-4 rounded-2xl bg-slate-50 border border-slate-100 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-600 outline-none transition-all">
                    </div>
                    
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 rounded-2xl transition-all transform active:scale-95 shadow-lg shadow-blue-500/30 mt-4">
                        Daftar Akun
                    </button>
                </form>

                <p class="text-center text-slate-500 text-sm mt-8">
                    Sudah punya akun? <a href="login.php" class="text-blue-600 font-bold hover:underline">Masuk Sekarang</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>