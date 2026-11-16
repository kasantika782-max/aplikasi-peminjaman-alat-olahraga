<?php
// Memastikan session aktif untuk mengecek siapa yang sedang login (Admin/Petugas/User)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Menghubungkan ke file koneksi dan model peminjaman
require_once '../model/m_koneksi.php';
require_once '../model/m_peminjaman.php';

// Inisialisasi database dan model
$database = new m_koneksi();
$db = $database->koneksi;
$pinjam_model = new m_peminjaman($db);

// Menangkap instruksi aksi dan ID dari URL
$aksi = $_GET['aksi'] ?? '';
$id = $_GET['id'] ?? '';
$role = $_SESSION['role'] ?? '';

// --- AKSI: USER MELAKUKAN PEMINJAMAN ---
if ($aksi == 'proses_pinjam') {
    $id_user = $_SESSION['id_user'];
    $id_alat = $_POST['id_alat'];
    $jumlah  = $_POST['jumlah_pinjam'];
    $kondisi = $_POST['kondisi_keluar'];

    // Memasukkan data ke tabel peminjaman dengan status awal 'pending'
    $simpan = $pinjam_model->tambah_pinjam($id_user, $id_alat, $jumlah, $kondisi);

    if ($simpan) {
        $pinjam_model->log_aktivitas($_SESSION['id_user'], "User melakukan request pinjam alat baru");
        header("Location: ../view/v_peminjaman_user.php?pesan=sukses_tambah");
        exit();
    } else {
        die("Gagal memproses peminjaman: " . mysqli_error($db));
    }
}

// --- AKSI: ADMIN / OPERATOR INPUT PEMINJAMAN DIRECT/MANUAL ---
if ($aksi == 'tambah_admin') {
    // Fitur ini KHUSUS ADMIN dan peminjaman dicatat atas nama
    // ADMIN yang sedang login, bukan atas nama pengguna lain.
    if ($role !== 'admin' || !isset($_SESSION['id_user'])) {
        header('Location: ' . ($role === 'peminjam'
            ? '../view/v_daftar_alat.php'
            : ($role === 'petugas'
                ? '../view/v_peminjaman_petugas.php'
                : '../view/v_login.php')));
        exit();
    }

    $id_user = (int)$_SESSION['id_user'];
    $id_alat = (int)($_POST['id_alat'] ?? 0);
    $jumlah  = (int)($_POST['jumlah_pinjam'] ?? 0);
    $jaminan = 'KTP';
    $kondisi = trim($_POST['kondisi_keluar'] ?? 'Baik');
    $tgl_pinjam = trim($_POST['tanggal_pinjam'] ?? '');

    if ($id_alat <= 0 || $jumlah <= 0) {
        header("Location: ../view/v_alat_pinjam_admin.php?tipe=pinjam&id_alat=$id_alat&pesan=data_tidak_valid");
        exit();
    }

    $simpan = $pinjam_model->tambah_pinjam_admin(
        $id_user,
        $id_alat,
        $jumlah,
        $kondisi,
        $jaminan,
        $tgl_pinjam
    );

    if ($simpan === "stok_kurang") {
        header("Location: ../view/v_alat_pinjam_admin.php?tipe=pinjam&id_alat=$id_alat&pesan=stok_kurang");
        exit();
    }

    if ($simpan === "alat_tidak_ditemukan") {
        header("Location: ../view/v_alat_pinjam_admin.php?tipe=pinjam&id_alat=$id_alat&pesan=alat_tidak_ditemukan");
        exit();
    }

    if ($simpan === "user_tidak_ditemukan") {
        header("Location: ../view/v_alat_pinjam_admin.php?tipe=pinjam&id_alat=$id_alat&pesan=user_tidak_ditemukan");
        exit();
    }

    if ($simpan === "tanggal_tidak_valid") {
        header("Location: ../view/v_alat_pinjam_admin.php?tipe=pinjam&id_alat=$id_alat&pesan=tanggal_tidak_valid");
        exit();
    }

    if ($simpan) {
        $pinjam_model->log_aktivitas(
            $_SESSION['id_user'],
            "Admin meminjam alat untuk dirinya sendiri, alat ID: $id_alat"
        );

        header("Location: ../view/v_peminjaman_admin.php?tipe=pinjam&pesan=sukses_tambah");
        exit();
    }

    header("Location: ../view/v_alat_pinjam_admin.php?tipe=pinjam&id_alat=$id_alat&pesan=gagal_tambah");
    exit();
}

// --- AKSI: PETUGAS MENYETUJUI PINJAMAN ---
if ($aksi == 'setuju') {
    $pinjam_model->verifikasi_pinjam($id);
    $pinjam_model->log_aktivitas($_SESSION['id_user'], "Petugas menyetujui peminjaman ID: $id");
    header("Location: ../view/v_peminjaman_petugas.php?pesan=sukses_setuju");
    exit();
}

// --- AKSI: PETUGAS MENGONFIRMASI PENGEMBALIAN ---
// --- AKSI: KONFIRMASI PENGEMBALIAN ---
if ($aksi == 'konfirmasi_kembali') {

    $hasil_kembali = $pinjam_model->konfirmasi_kembali($id);

    // Catat aktivitas
    $pinjam_model->log_aktivitas(
        $_SESSION['id_user'],
        ucfirst($role) . " mengonfirmasi pengembalian alat ID: $id"
    );

    // Jika yang melakukan adalah ADMIN
    if ($role == 'admin') {

        header(
            "Location: ../view/v_peminjaman_admin.php?tipe=kembali&pesan=sukses_kembali"
        );
        exit();

    }

    // Jika yang melakukan adalah PETUGAS
    if ($role == 'petugas') {

        header(
            "Location: ../view/v_peminjaman_petugas.php?pesan=sukses_kembali"
        );
        exit();

    }

    // Jika role tidak dikenali
    header("../view/v_login.php");
    exit();
}

// --- AKSI: HAPUS PEMINJAMAN ---
if ($aksi == 'hapus') {
    $id_hapus = $_GET['id'];
    $tipe = $_GET['tipe'] ?? 'pinjam';
    
    $query = $pinjam_model->hapus_data($id_hapus);

    if ($query) {
        $pinjam_model->log_aktivitas($_SESSION['id_user'], "Admin/Petugas menghapus data peminjaman ID: $id_hapus");
        header("Location: ../view/v_peminjaman_admin.php?tipe=$tipe&pesan=sukses_hapus");
    } else {
        header("Location: ../view/v_peminjaman_admin.php?tipe=$tipe&pesan=gagal_hapus");
    }
    exit();
}

// --- LOGIKA EDIT: Mengambil data lama sebelum diubah oleh Admin ---
$data_edit = null;
if (isset($_GET['id']) && ($_GET['aksi'] == 'edit_pinjam' || $_GET['aksi'] == 'edit_kembali')) {
    $id_target = $_GET['id'];
    $query_edit = mysqli_query($db, "SELECT p.*, u.username, u.no_hp, a.nama_alat 
                                    FROM peminjaman p 
                                    JOIN user u ON p.id_user = u.id_user 
                                    JOIN alat a ON p.id_alat = a.id_alat 
                                    WHERE p.id_peminjaman = '$id_target'");
    $data_edit = mysqli_fetch_object($query_edit);
}

// --- AKSI: ADMIN MENGUBAH DATA PINJAMAN ---
if ($aksi == 'update_pinjam') {
    $id_peminjaman = $_POST['id_peminjaman'];
    $jumlah = $_POST['jumlah_pinjam'];
    $status = $_POST['status'];

    $simpan = $pinjam_model->update_pinjam($id_peminjaman, $jumlah, $status);

    if ($simpan === "stok_kurang") {
        header("Location: ../view/v_peminjaman_admin.php?tipe=pinjam&pesan=stok_kurang");
        exit();
    } elseif ($simpan) {
        $pinjam_model->log_aktivitas($_SESSION['id_user'], "Admin mengubah data peminjaman ID: $id_peminjaman");
        header("Location: ../view/v_peminjaman_admin.php?tipe=pinjam&pesan=sukses_update");
        exit();
    } else {
        header("Location: ../view/v_peminjaman_admin.php?tipe=pinjam&pesan=gagal_update");
        exit();
    }
}

// --- AKSI: ADMIN MENGUBAH DATA PENGEMBALIAN ---
if ($aksi == 'update_kembali') {
    $id_peminjaman = $_POST['id_peminjaman'];
    $kondisi = $_POST['kondisi_masuk'];
    $tgl = $_POST['tgl_kembali_asli'];

    $simpan = $pinjam_model->update_kembali($id_peminjaman, $kondisi, $tgl);
    if ($simpan) {
        $pinjam_model->log_aktivitas($_SESSION['id_user'], "Admin mengubah data pengembalian ID: $id_peminjaman");
        header("Location: ../view/v_peminjaman_admin.php?tipe=kembali&pesan=sukses_kembali");
        exit();
    } else {
        header("Location: ../view/v_peminjaman_admin.php?tipe=kembali&pesan=gagal_update");
        exit();
    }
}

// --- LOGIKA TAMPILAN DATA BERDASARKAN ROLE ---
if ($role == 'petugas') {
    $isi_tabel = $pinjam_model->tampil_data();
} elseif ($role == 'admin') {
    $isi_tabel = $pinjam_model->tampil_data_admin($_GET['tipe'] ?? 'pinjam');
}

// Selalu ambil riwayat milik user yang login agar tampil di dashboard user
if (isset($_SESSION['id_user'])) {
    $isi_tabel_user = $pinjam_model->tampil_data_user($_SESSION['id_user']);
}