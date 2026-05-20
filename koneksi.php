<?php
// 1. Masukkan data dari TiDB Cloud di sini
$host = "gateway01.ap-southeast-1.prod.alicloud.tidbcloud.com"; // Ganti dengan Host TiDB Anda
$user = "48D8BJbofbESMRv.root"; // Ganti dengan Username TiDB Anda (biasanya ada angka di depannya)
$pass = "FXTHyWHgbNa2ByDf"; // Ganti dengan Password database TiDB
$db   = "dompetkos"; // Pastikan Anda sudah membuat database dengan nama ini di TiDB
$port = 4000; // Port standar TiDB

// 2. Inisialisasi fungsi koneksi
$conn = mysqli_init();

// 3. Menghubungkan PHP ke TiDB menggunakan mode aman (MYSQLI_CLIENT_SSL)
// Ini diwajibkan karena server Vercel dan TiDB Cloud berkomunikasi lewat jalur internet publik
mysqli_real_connect($conn, $host, $user, $pass, $db, $port, NULL, MYSQLI_CLIENT_SSL);

// 4. Memeriksa apakah koneksi berhasil
if (mysqli_connect_errno()) {
    die("Koneksi ke TiDB Cloud gagal: " . mysqli_connect_error());
}