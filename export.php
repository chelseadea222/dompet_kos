<?php
require 'koneksi.php';

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }
$user_id = $_SESSION['user_id'];

$date   = isset($_GET['date'])   ? $_GET['date']   : date('Y-m-d');
$bulan  = isset($_GET['bulan'])  ? $_GET['bulan']  : null;

if ($bulan) {
    $result     = $conn->query("SELECT * FROM transactions WHERE user_id='$user_id' AND DATE_FORMAT(date,'%Y-%m')='$bulan' ORDER BY date ASC, id ASC");
    $nama_file  = "DompetKos_" . $bulan . ".xls";
    $judul      = "Laporan Transaksi Bulan " . date('F Y', strtotime($bulan . '-01'));
} else {
    $result     = $conn->query("SELECT * FROM transactions WHERE user_id='$user_id' AND date='$date' ORDER BY id ASC");
    $nama_file  = "DompetKos_" . $date . ".xls";
    $judul      = "Laporan Transaksi " . date('d F Y', strtotime($date));
}

$total_masuk = 0; $total_keluar = 0; $rows = [];
if ($result) {
    while ($r = $result->fetch_assoc()) {
        $rows[] = $r;
        if ($r['type'] == 'pemasukan') $total_masuk += $r['amount'];
        else $total_keluar += $r['amount'];
    }
}
$saldo = $total_masuk - $total_keluar;

header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=\"$nama_file\"");
header("Pragma: no-cache");
header("Expires: 0");
?>
<html xmlns:o="urn:schemas-microsoft-com:office:office"
      xmlns:x="urn:schemas-microsoft-com:office:excel"
      xmlns="http://www.w3.org/TR/REC-html40">
<head>
<meta charset="UTF-8">
<!--[if gte mso 9]>
<xml>
  <x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet>
    <x:Name>Laporan</x:Name>
    <x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions>
  </x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook>
</xml>
<![endif]-->
</head>
<body>
<table border="1" style="border-collapse:collapse; font-family:Arial; font-size:11pt;">

    <!-- JUDUL -->
    <tr>
        <td colspan="6" style="background:#2a40a3; color:white; font-size:15pt; font-weight:bold; text-align:center; padding:12px;">
            <?= $judul ?>
        </td>
    </tr>
    <tr>
        <td colspan="6" style="background:#4f8cf6; color:white; text-align:center; padding:6px; font-size:10pt;">
            Diekspor dari DompetKos &nbsp;·&nbsp; <?= date('d F Y, H:i') ?> WIB
        </td>
    </tr>
    <tr><td colspan="6" style="padding:4px;"></td></tr>

    <!-- RINGKASAN -->
    <tr>
        <td colspan="3" style="background:#dcfce7; color:#15803d; font-weight:bold; padding:8px; text-align:center; font-size:11pt;">
            ↓ Total Pemasukan: Rp <?= number_format($total_masuk, 0, ',', '.') ?>
        </td>
        <td colspan="3" style="background:#fee2e2; color:#dc2626; font-weight:bold; padding:8px; text-align:center; font-size:11pt;">
            ↑ Total Pengeluaran: Rp <?= number_format($total_keluar, 0, ',', '.') ?>
        </td>
    </tr>
    <tr>
        <td colspan="6" style="background:<?= $saldo >= 0 ? '#2a40a3' : '#dc2626' ?>; color:white; font-weight:bold; text-align:center; padding:8px; font-size:12pt;">
            Saldo Bersih: <?= $saldo >= 0 ? '+' : '' ?>Rp <?= number_format($saldo, 0, ',', '.') ?>
        </td>
    </tr>
    <tr><td colspan="6" style="padding:4px;"></td></tr>

    <!-- HEADER TABEL -->
    <tr style="background:#2a40a3; color:white; font-weight:bold; text-align:center;">
        <td style="padding:9px; width:35px;">No</td>
        <td style="padding:9px; width:90px;">Tanggal</td>
        <td style="padding:9px; width:100px;">Jenis</td>
        <td style="padding:9px; width:100px;">Kategori</td>
        <td style="padding:9px; width:220px;">Keterangan</td>
        <td style="padding:9px; width:130px;">Nominal (Rp)</td>
    </tr>

    <!-- ISI DATA -->
    <?php if (count($rows) > 0): ?>
        <?php $no = 1; foreach ($rows as $row):
            $is_masuk   = $row['type'] == 'pemasukan';
            $bg_row     = ($no % 2 == 0) ? '#f0f4ff' : '#ffffff';
            $warna_nom  = $is_masuk ? '#16a34a' : '#dc2626';
            $bg_jenis   = $is_masuk ? '#dcfce7' : '#fee2e2';
            $txt_jenis  = $is_masuk ? '#16a34a' : '#dc2626';
            $label      = $is_masuk ? '↓ Pemasukan' : '↑ Pengeluaran';
        ?>
        <tr style="background:<?= $bg_row ?>;">
            <td style="padding:7px; text-align:center;"><?= $no++ ?></td>
            <td style="padding:7px; text-align:center;"><?= date('d/m/Y', strtotime($row['date'])) ?></td>
            <td style="padding:7px; text-align:center; background:<?= $bg_jenis ?>; color:<?= $txt_jenis ?>; font-weight:bold;"><?= $label ?></td>
            <td style="padding:7px;"><?= htmlspecialchars($row['category']) ?></td>
            <td style="padding:7px;"><?= htmlspecialchars($row['description'] ?? '-') ?></td>
            <td style="padding:7px; text-align:right; color:<?= $warna_nom ?>; font-weight:bold;">
                <?= ($is_masuk ? '+' : '-') . 'Rp ' . number_format($row['amount'], 0, ',', '.') ?>
            </td>
        </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr>
            <td colspan="6" style="text-align:center; padding:20px; color:#9ca3af;">
                Tidak ada transaksi pada periode ini.
            </td>
        </tr>
    <?php endif; ?>

    <!-- FOOTER TOTAL -->
    <tr><td colspan="6" style="padding:4px;"></td></tr>
    <tr style="background:#f0fdf4;">
        <td colspan="5" style="padding:8px; font-weight:bold; text-align:right; color:#15803d;">Total Pemasukan</td>
        <td style="padding:8px; text-align:right; color:#16a34a; font-weight:bold;">+Rp <?= number_format($total_masuk, 0, ',', '.') ?></td>
    </tr>
    <tr style="background:#fff0f0;">
        <td colspan="5" style="padding:8px; font-weight:bold; text-align:right; color:#dc2626;">Total Pengeluaran</td>
        <td style="padding:8px; text-align:right; color:#dc2626; font-weight:bold;">-Rp <?= number_format($total_keluar, 0, ',', '.') ?></td>
    </tr>
    <tr style="background:<?= $saldo >= 0 ? '#2a40a3' : '#dc2626' ?>;">
        <td colspan="5" style="padding:9px; font-weight:bold; text-align:right; color:white;">Saldo Bersih</td>
        <td style="padding:9px; text-align:right; color:<?= $saldo >= 0 ? '#86efac' : '#fca5a5' ?>; font-weight:bold;">
            <?= ($saldo >= 0 ? '+' : '') . 'Rp ' . number_format($saldo, 0, ',', '.') ?>
        </td>
    </tr>

</table>
</body>
</html>