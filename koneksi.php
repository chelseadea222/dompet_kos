<?php
// Ganti data di bawah ini sesuai dengan yang ada di Client Area InfinityFree Anda
$host     = "sql200.infinityfree.com"; // Contoh: sql123.infinityfree.com
$username = "if0_42028643";            // Username dari InfinityFree
$password = "FVCHI0hlb8eBZII";    // Password akun InfinityFree
$database = "if0_42028643_dompetkos";  // Nama database yang Anda buat di cPanel InfinityFree

$conn = mysqli_connect($host, $username, $password, $database);

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}
?>