<?php
// 1. CEK SESSION: Hanya mulai session jika belum ada session yang aktif
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Koneksi Database
$host = "localhost";
$user = "root";
$pass = "";
$db   = "dompet_kos"; // Pastikan nama database sesuai dengan yang di PhpMyAdmin

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// --- LOGIKA NOTIFIKASI ---
$pesan_notifikasi = [];

// 2. CEK LOGIN: Pastikan query notifikasi hanya berjalan JIKA user sudah login
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    // 1. Cek apakah hari ini belum ada pencatatan
    $hari_ini = date('Y-m-d');
    $query_cek_harian = "SELECT COUNT(id) as total FROM transactions WHERE user_id = $user_id AND date = '$hari_ini'";
    $result_harian = $conn->query($query_cek_harian);
    
    // Pastikan query berhasil sebelum fetch data
    if ($result_harian) {
        $row_harian = $result_harian->fetch_assoc();
        if ($row_harian['total'] == 0) {
            $pesan_notifikasi[] = "⚠️ Kamu belum melakukan pencatatan harian hari ini.";
        }
    }

    // 2. Cek pengeluaran mingguan (7 hari terakhir) > 200.000
    $query_cek_mingguan = "SELECT SUM(amount) as total_pengeluaran 
                           FROM transactions 
                           WHERE user_id = $user_id 
                           AND type = 'pengeluaran' 
                           AND date >= DATE(NOW()) - INTERVAL 7 DAY";
    $result_mingguan = $conn->query($query_cek_mingguan);
    
    // Pastikan query berhasil sebelum fetch data
    if ($result_mingguan) {
        $row_mingguan = $result_mingguan->fetch_assoc();
        if ($row_mingguan['total_pengeluaran'] > 200000) {
            $pesan_notifikasi[] = "🚨 Tolong hentikan pengeluaran dan tolong berhemat! (Pengeluaran minggu ini: Rp " . number_format($row_mingguan['total_pengeluaran'], 0, ',', '.') . ")";
        }
    }
}
?>