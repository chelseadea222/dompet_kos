<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// UBAH DENGAN KREDENSIAL DATABASE ONLINE ANDA
$host = "gateway01.ap-southeast-1.prod.alicloud.tidbcloud.com"; 
$user = "48D8BJbofbESMRv.root";
$pass = "0aaL5V7vTVUnnPtZ";
$db   = "dompetkos"; 
$port = "4000";

$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}
?>