<?php
require 'koneksi.php';

// Cek apakah sudah ada cookie login, jika ada langsung ke dashboard
if (isset($_COOKIE['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

if (isset($_POST['login'])) {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];

    $result = $conn->query("SELECT * FROM users WHERE email = '$email'");
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            
            // --- GANTI SESSION MENJADI COOKIE DI SINI ---
            // Cookie disimpan selama 1 hari (86400 detik)
// Tambahkan awalan spasi kosong "", lalu true, true untuk keamanan Vercel
setcookie('user_id', (string)$user['id'], time() + 86400, "/", "", true, true);
setcookie('username', (string)$user['username'], time() + 86400, "/", "", true, true);            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "Email tidak terdaftar!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - DompetKos</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Mencegah warna background berubah biru/kuning saat autofill */
        input:-webkit-autofill,
        input:-webkit-autofill:hover, 
        input:-webkit-autofill:focus, 
        input:-webkit-autofill:active{
            -webkit-box-shadow: 0 0 0 30px rgba(255, 255, 255, 0.9) inset !important;
            -webkit-text-fill-color: #374151 !important;
            border-radius: 0.5rem;
        }

        /* Menyembunyikan ikon mata bawaan browser agar tersisa satu saja buatan kita */
        input::-ms-reveal,
        input::-ms-clear {
            display: none;
        }
    </style>
</head>
<body class="bg-gray-100 flex justify-center h-screen">
    <div class="w-full max-w-[414px] bg-gradient-to-b from-blue-500 to-green-200 p-8 flex flex-col justify-center">
        
        <div class="text-center mb-10 text-white flex flex-col items-center">
            <img src="images/uang.png" alt="Logo DompetKos" class="w-45 h-40 object-contain mb-4 drop-shadow-md">
            
            <h1 class="text-3xl font-bold mb-2">DompetKos</h1>
            <p>Keuangan Rapih, Hidup Lebih Pasti</p>
        </div>
        
        <?php if(isset($error)) echo "<p class='bg-red-500 text-white p-2 rounded mb-4 shadow-md'>$error</p>"; ?>

        <form method="POST" class="flex flex-col gap-4" autocomplete="off">
            
            <div class="relative w-full">
                <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-gray-600">
                    <i class="far fa-envelope"></i>
                </div>
                <input type="email" name="email" placeholder="Email address" required autocomplete="new-email" class="w-full py-3 pl-10 pr-4 rounded-lg bg-white/60 border border-blue-300/50 placeholder-gray-600 outline-none focus:bg-white transition shadow-sm">
            </div>
            
            <div class="relative w-full">
                <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-gray-600">
                    <i class="fas fa-lock"></i>
                </div>
                
                <input type="password" name="password" id="password_input" placeholder="Password" required autocomplete="new-password" class="w-full py-3 pl-10 pr-12 rounded-lg bg-white/60 border border-blue-300/50 placeholder-gray-600 outline-none focus:bg-white transition shadow-sm">
                
                <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-4 flex items-center text-gray-600 hover:text-blue-900 transition">
                    <i class="far fa-eye" id="eye_icon"></i>
                </button>
            </div>

            <button type="submit" name="login" class="bg-blue-700 text-white p-3 rounded-lg font-bold mt-4 shadow-lg hover:bg-blue-800 transition">Login</button>
        </form>

        <p class="text-center text-sm mt-4 text-gray-800">Belum Punya Akun? <a href="register.php" class="text-blue-900 font-bold hover:underline">Daftar</a></p>
    </div>

    <script>
        function togglePassword() {
            var passwordInput = document.getElementById("password_input");
            var eyeIcon = document.getElementById("eye_icon");

            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                eyeIcon.classList.remove("fa-eye");
                eyeIcon.classList.add("fa-eye-slash");
            } else {
                passwordInput.type = "password";
                eyeIcon.classList.remove("fa-eye-slash");
                eyeIcon.classList.add("fa-eye");
            }
        }
    </script>
</body>
</html>