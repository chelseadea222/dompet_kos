<?php
require 'koneksi.php';
if (!isset($_SESSION['user_id'])) header("Location: login.php");

$user_id = $_SESSION['user_id'];

// Ambil data user dari database
$query = $conn->query("SELECT * FROM users WHERE id = '$user_id'");
if ($query && $query->num_rows > 0) {
    $user = $query->fetch_assoc();
    $email_user = $user['email'];
    
    // Logika tampil foto profil
    $foto_profil = (!empty($user['profile_pic']) && file_exists('uploads/' . $user['profile_pic'])) 
                   ? 'uploads/' . $user['profile_pic'] 
                   : null;
} else {
    $email_user = "Email tidak ditemukan"; 
    $foto_profil = null;
}

// Logika Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil - DompetKos</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class', 
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style> 
        body { display: flex; justify-content: center; background-color: #f3f4f6; } 
        .app { 
            max-width: 414px; min-height: 100vh; width: 100%; 
            background: linear-gradient(to bottom, #4f8cf6, #b4eedb); 
            position: relative;
            padding-bottom: 100px; 
            transition: background 0.4s ease-in-out;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        } 
        
        /* MODE GELAP: Warna latar belakang biru dibuat redup/gelap */
        html.dark body {
            background-color: #020617; 
        }
        html.dark .app {
            /* Biru terang menjadi Biru Tua Redup */
            background: linear-gradient(to bottom, #1e3a8a, #0f172a);
            box-shadow: 0 0 20px rgba(0,0,0,0.8);
        }
    </style>
</head>
<body class="transition-colors duration-300 dark:bg-gray-900">
<div class="app text-white flex flex-col">
    
    <div class="p-6 flex-grow flex flex-col relative z-10">
        <div class="flex items-center mb-8 gap-4">
            <a href="dashboard.php" class="text-white dark:text-gray-300 hover:text-gray-200 transition-colors"><i class="fas fa-arrow-left text-xl"></i></a>
            <h1 class="text-2xl font-bold tracking-wide dark:text-gray-200">Profil</h1>
        </div>
        
        <div class="flex flex-col items-center mb-8">
            <div class="w-24 h-24 bg-white/20 dark:bg-black/20 rounded-full flex items-center justify-center text-4xl text-white mb-4 shadow-xl border-4 border-white dark:border-white/10 overflow-hidden backdrop-blur-sm transition-all">
                <?php if($foto_profil): ?>
                    <img src="<?= $foto_profil ?>" alt="Profil" class="w-full h-full object-cover">
                <?php else: ?>
                    <i class="fas fa-user opacity-80 dark:opacity-50"></i>
                <?php endif; ?>
            </div>
            
            <h2 class="text-xl font-bold drop-shadow-md tracking-wider dark:text-gray-200"><?= htmlspecialchars($_SESSION['username']) ?></h2>
            
            <a href="edit_profil.php" class="mt-3 text-xs font-bold bg-white/30 dark:bg-black/30 hover:bg-white/50 dark:hover:bg-black/50 text-white dark:text-gray-300 px-5 py-2 rounded-full backdrop-blur-md transition shadow-md border border-white/20 dark:border-white/5">
                <i class="fas fa-pen mr-1"></i> Edit Profil
            </a>
        </div>

        <div class="bg-white/30 dark:bg-black/20 backdrop-blur-xl p-4 rounded-2xl flex flex-col gap-4 text-gray-800 dark:text-gray-300 font-semibold mb-6 shadow-md border border-white/20 dark:border-white/5 transition-all duration-300">
            
            <div class="flex justify-between items-center bg-white/50 dark:bg-black/40 p-3.5 rounded-xl shadow-sm border border-white/30 dark:border-transparent">
                <div class="flex items-center gap-3">
                    <i class="fas fa-envelope text-blue-500 dark:text-blue-400"></i>
                    <span>Email</span>
                </div>
                <span class="text-xs font-normal text-gray-600 dark:text-gray-400"><?= htmlspecialchars($email_user) ?></span> 
            </div>
            
            <div class="flex justify-between items-center bg-white/50 dark:bg-black/40 p-3.5 rounded-xl shadow-sm border border-white/30 dark:border-transparent">
                <div class="flex items-center gap-3">
                    <i class="fas fa-moon text-indigo-500 dark:text-indigo-400"></i>
                    <span>Mode Gelap</span>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                  <input type="checkbox" id="darkModeToggle" class="sr-only peer">
                  <div class="w-11 h-6 bg-gray-400 dark:bg-black/60 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white dark:after:bg-gray-300 after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600 dark:peer-checked:bg-blue-800 shadow-inner"></div>
                </label>
            </div>

            <div class="flex justify-between items-center bg-white/50 dark:bg-black/40 p-3.5 rounded-xl shadow-sm border border-white/30 dark:border-transparent">
                <div class="flex items-center gap-3">
                    <i class="fas fa-bell text-yellow-500 dark:text-yellow-600"></i>
                    <span>Notifikasi Harian</span>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                  <input type="checkbox" value="" class="sr-only peer" checked>
                  <div class="w-11 h-6 bg-gray-400 dark:bg-black/60 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white dark:after:bg-gray-300 after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600 dark:peer-checked:bg-blue-800 shadow-inner"></div>
                </label>
            </div>
        </div>

        <a href="profil.php?logout=true" class="bg-[#2a40a3] dark:bg-[#1a2866] text-center text-white dark:text-gray-300 p-3.5 rounded-xl font-bold shadow-lg mt-auto hover:bg-blue-800 dark:hover:bg-[#111a45] transition transform active:scale-95 border border-transparent dark:border-white/5">
            <i class="fas fa-sign-out-alt mr-2"></i> Logout
        </a>
    </div>
    
    <div class="absolute bottom-0 w-full bg-[#2a40a3] dark:bg-[#0f172a] rounded-t-[30px] shadow-[0_-10px_25px_rgba(0,0,0,0.2)] dark:shadow-[0_-10px_25px_rgba(0,0,0,0.8)] px-6 py-4 flex justify-between items-center z-50 transition-colors duration-300 border-t border-transparent dark:border-white/5">
        <a href="dashboard.php" class="w-10 h-10 bg-gray-200 dark:bg-white/10 rounded-full flex items-center justify-center text-gray-800 dark:text-gray-400 hover:bg-white dark:hover:bg-white/20 transition shadow-inner">
            <i class="fas fa-home text-lg"></i>
        </a>
        <a href="pencatatan.php" class="w-10 h-10 bg-gray-200 dark:bg-white/10 rounded-full flex items-center justify-center text-gray-800 dark:text-gray-400 hover:bg-white dark:hover:bg-white/20 transition shadow-inner">
            <i class="fas fa-pen text-lg"></i>
        </a>
        <a href="riwayat.php" class="w-10 h-10 bg-gray-200 dark:bg-white/10 rounded-full flex items-center justify-center text-gray-800 dark:text-gray-400 hover:bg-white dark:hover:bg-white/20 transition shadow-inner">
            <i class="fas fa-list text-lg"></i>
        </a>
        <a href="profil.php" class="flex items-center bg-[#00cbf7] dark:bg-[#008ba3] px-5 py-2.5 rounded-full text-white dark:text-gray-200 shadow-md">
            <i class="fas fa-user mr-2 text-lg"></i>
            <span class="text-sm font-bold">Profil</span>
        </a>
    </div>

</div>

<script>
    const toggle = document.getElementById('darkModeToggle');
    const html = document.documentElement;

    if (localStorage.getItem('theme') === 'dark') {
        html.classList.add('dark');
        toggle.checked = true;
    }

    toggle.addEventListener('change', function() {
        if (this.checked) {
            html.classList.add('dark');
            localStorage.setItem('theme', 'dark');
        } else {
            html.classList.remove('dark');
            localStorage.setItem('theme', 'light');
        }
    });
</script>
</body>
</html>