<?php
session_start();

// Cek apakah user sudah login atau belum
if (!isset($_SESSION['id_user'])) {
    // Jika belum login, arahkan ke halaman login
    header("location:view/v_login.php");
    exit();
} else {
    // Jika sudah login, arahkan sesuai role-nya
    if ($_SESSION['role'] == 'admin') {
        header("location:controller/c_user.php?aksi=tampil");
    } else if ($_SESSION['role'] == 'petugas') {
        header("location:view/v_peminjaman_petugas.php");
    } else {
        header("location:view/v_daftar_alat.php");
    }
    exit();
}