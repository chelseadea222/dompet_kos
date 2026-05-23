<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selamat Datang - DompetKos</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { 
            background-color: #f3f4f6; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            min-height: 100vh; 
            margin: 0; 
        }
        /* Animasi melayang halus untuk logo */
        .float-anim { animation: floating 3s ease-in-out infinite; }
        @keyframes floating {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-12px); }
            100% { transform: translateY(0px); }
        }
    </style>
</head>
<body class="bg-gray-100 p-0 md:p-6">

<div class="w-full max-w-full md:max-w-md h-screen md:h-[85vh] bg-gradient-to-b from-[#4f8cf6] to-[#b4eedb] position-relative md:rounded-3xl md:shadow-2xl flex flex-col justify-between overflow-hidden">
    
    <div class="absolute top-[-50px] right-[-50px] w-64 h-64 bg-white/20 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-[20%] left-[-50px] w-48 h-48 bg-blue-600/20 rounded-full blur-2xl pointer-events-none"></div>

    <div class="flex-grow flex flex-col items-center justify-center p-8 relative z-10 mt-8">
        <div class="w-40 h-40 md:w-44 md:h-44 bg-white/20 p-3 rounded-[2rem] shadow-2xl backdrop-blur-md mb-8 border border-white/40 float-anim">
            <img src="images/dompetkos.png" alt="Logo DompetKos" class="w-full h-full object-cover rounded-[1.2rem] shadow-inner">
        </div>
        
        <h1 class="text-4xl font-extrabold text-white drop-shadow-md mb-3 text-center tracking-wide">
            DompetKos
        </h1>
        <p class="text-white/90 text-center text-sm font-medium leading-relaxed px-2 drop-shadow-sm">
            Kelola uang saku, pantau pengeluaran kos, dan wujudkan target tabungan Anda dengan lebih mudah dan terencana.
        </p>
    </div>

    <div class="p-8 pb-12 w-full relative z-10 flex flex-col gap-4 bg-white/20 backdrop-blur-lg rounded-t-[40px] md:rounded-b-3xl border-t border-white/40 shadow-[0_-15px_30px_rgba(0,0,0,0.1)]">
        <a href="login.php" class="w-full bg-[#2a40a3] text-white py-4 rounded-2xl font-bold text-center text-lg shadow-[0_8px_20px_rgba(42,64,163,0.4)] hover:bg-blue-800 transition-all transform active:scale-95 flex items-center justify-center gap-2">
            Masuk ke Akun
        </a>
        <a href="register.php" class="w-full bg-white text-[#2a40a3] py-4 rounded-2xl font-bold text-center text-lg shadow-[0_8px_20px_rgba(0,0,0,0.1)] hover:bg-gray-50 transition-all transform active:scale-95 flex items-center justify-center gap-2">
            Daftar Akun Baru
        </a>
    </div>
</div>

</body>
</html>