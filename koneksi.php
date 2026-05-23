<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = "gateway01.ap-southeast-1.prod.alicloud.tidbcloud.com"; 
$user = "48D8BJbofbESMRv.root";
$pass = "0aaL5V7vTVUnnPtZ";
$db   = "dompetkos"; 
$port = 4000;

// 1. Inisialisasi koneksi MySQLi
$conn = mysqli_init();

// 2. Beritahu PHP untuk menggunakan SSL (Wajib untuk TiDB)
$conn->ssl_set(NULL, NULL, NULL, NULL, NULL);

// 3. Lakukan koneksi sebenarnya dengan flag MYSQLI_CLIENT_SSL
$berhasil = $conn->real_connect($host, $user, $pass, $db, $port, NULL, MYSQLI_CLIENT_SSL);

// Cek apakah koneksi berhasil
if (!$berhasil) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

?>