<?php 

if (session_status() === PHP_SESSION_NONE) { session_start();
require_once __DIR__ . '/../model/m_security.php';
$csrf_token = csrf_token(); }

// 1. Cek apakah sudah login?
if (!isset($_SESSION['role'])) {
    header("Location: v_login.php");
    exit();
}

// 2. Jika yang masuk BUKAN petugas, maka ditendang balik
if ($_SESSION['role'] !== 'petugas') {
    if ($_SESSION['role'] === 'admin') {
        // Jika admin nyasar ke sini, balikin ke dashboard admin
        header("Location: v_tampilan_user.php");
    } else {
        // Jika peminjam nyasar ke sini, balikin ke dashboard user
        header("Location: v_daftar_alat.php");
    }
    exit();
}

// Tetap include controller yang sama karena datanya sudah disiapkan di sana
include '../controller/c_peminjaman.php'; 
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Laporan Peminjaman</title>
     <link rel="stylesheet" href="../asset/style_cetak_laporan.css"> 
</head>
<body onload="window.print()">

    <div class="header">
        <h2><center>LAPORAN RIWAYAT PEMINJAMAN ALAT</h2>
        <h3><center>SMK SANGKURIANG 1 CIMAHI</h3>
        <hr>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Peminjam</th>
                <th>Nama Alat</th>
                <th>Status</th>
                <th>Petugas Pelayan</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1; 
            // Variabel $isi_tabel otomatis tersedia karena include controller di atas
            while($row = mysqli_fetch_object($isi_tabel)): 
            ?>
            <tr>
                <td><?= $no++; ?></td>
                <td><?= $row->nama_peminjam; ?></td>
                <td><?= $row->nama_alat; ?></td>
                <td><?= strtoupper($row->status); ?></td>
                <td><?= $row->nama_petugas ?? '-'; ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

</body>
</html>