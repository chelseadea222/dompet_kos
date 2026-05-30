<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DompetKos - Selamat Datang</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body { font-family: 'Inter', sans-serif; }
        
        /* Animasi melayang untuk gambar logo */
        .float-anim { animation: floating 3s ease-in-out infinite; }
        @keyframes floating {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-12px); }
        }
        
        /* Custom scrollbar tipis */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</head>
<body class="bg-[#f4f7f6] min-h-screen flex items-center justify-center p-4 md:p-8">

    <div class="w-full max-w-5xl bg-white rounded-[2.5rem] shadow-[0_20px_50px_rgba(0,0,0,0.05)] flex flex-col md:flex-row overflow-hidden min-h-[85vh] md:min-h-[600px] border border-gray-100">
        
        <div class="w-full md:w-5/12 bg-gradient-to-br from-[#3b82f6] via-[#4fa4f4] to-[#6ee7b7] p-10 flex flex-col items-center justify-center relative overflow-hidden">
            <div class="absolute top-[-20%] left-[-20%] w-72 h-72 bg-white/20 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute bottom-[-10%] right-[-10%] w-56 h-56 bg-blue-900/10 rounded-full blur-2xl pointer-events-none"></div>
            
            <div class="relative z-10 float-anim bg-white/10 p-5 md:p-7 rounded-3xl backdrop-blur-sm border border-white/20 shadow-xl">
                <img src="images/dompetkos.png" alt="Logo DompetKos" class="w-44 md:w-56 h-auto drop-shadow-2xl object-contain">
            </div>
        </div>

        <div class="w-full md:w-7/12 p-10 md:p-16 flex flex-col justify-center bg-white relative z-10">
            <div class="max-w-md mx-auto w-full">
                
                <div class="mb-10 text-center md:text-left">
                    <h2 class="text-3xl md:text-4xl font-extrabold text-gray-800 mb-4 leading-tight tracking-tight">
                        Selamat Datang di <br class="hidden md:block">
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-teal-500">DompetKos</span>
                    </h2>
                    
                    <p class="text-gray-500 text-sm md:text-base leading-relaxed font-medium">
                        Aplikasi cerdas untuk mengelola uang saku, memantau pengeluaran bulanan kos, dan mewujudkan target tabungan Anda dengan lebih mudah dan terencana.
                    </p>
                </div>

                <div class="space-y-4">
                    <a href="login.php" class="group flex items-center justify-center gap-3 w-full bg-[#2563eb] hover:bg-[#1d4ed8] text-white font-semibold py-4 rounded-2xl transition-all duration-300 shadow-[0_8px_20px_rgba(37,99,235,0.25)] hover:shadow-[0_10px_25px_rgba(37,99,235,0.4)] transform hover:-translate-y-1">
                        <i class="fa-solid fa-arrow-right-to-bracket text-lg transition-transform group-hover:translate-x-1"></i>
                        Masuk ke Akun
                    </a>
                    
                    <a href="register.php" class="group flex items-center justify-center gap-3 w-full bg-white text-[#2563eb] border-2 border-blue-100 hover:border-blue-500 font-semibold py-4 rounded-2xl transition-all duration-300 transform hover:-translate-y-1 hover:bg-blue-50/50">
                        <i class="fa-solid fa-user-plus text-lg"></i>
                        Daftar Akun Baru
                    </a>
                </div>
                
                <div class="mt-12 pt-6 border-t border-gray-100 text-center md:text-left">
                    <p class="text-xs text-gray-400 font-medium tracking-wide">© 2026 DompetKos. Hak Cipta Dilindungi.</p>
                </div>

            </div>
        </div>

    </div>

</body>
</html>