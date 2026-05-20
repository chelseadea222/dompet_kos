<?php
session_start();
require 'koneksi.php';

$user_id = $_SESSION['user_id'];

// Nama file yang akan diunduh
$filename = "Riwayat_Keuangan_" . date('Ymd') . ".csv";

header("Content-Description: File Transfer");
header("Content-Disposition: attachment; filename=$filename");
header("Content-Type: application/csv; "); 

$file = fopen('php://output', 'w');
// Header kolom di Excel
$header = array("Tanggal", "Jenis", "Kategori", "Jumlah (Rp)");
fputcsv($file, $header);

// Ambil data riwayat
$query = "SELECT date, type, category, amount FROM transactions WHERE user_id = $user_id ORDER BY date DESC";
$result = $conn->query($query);

while ($row = $result->fetch_assoc()) {
    $lineData = array($row['date'], $row['type'], $row['category'], $row['amount']);
    fputcsv($file, $lineData);
}
fclose($file);
exit;
?>