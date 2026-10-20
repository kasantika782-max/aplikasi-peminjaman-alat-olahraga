<?php
// Memulai session untuk mengecek hak akses (login)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Memanggil file koneksi dan semua model yang dibutuhkan
require_once '../model/m_security.php';
require_once '../model/m_koneksi.php';
require_once '../model/m_alat.php';
require_once '../model/m_kategori.php';

// Menyiapkan koneksi database
$db = (new m_koneksi())->koneksi;

// Membuat objek dari model alat dan kategori
$alat_model     = new m_alat($db);
$kategori_model = new m_kategori($db);

// Menangkap instruksi aksi (default: tampil) dan ID alat
// Proteksi controller: hanya admin yang boleh mengelola data ini.
if (($_SESSION['role'] ?? '') !== 'admin') {
    $role_now = $_SESSION['role'] ?? '';
    if ($role_now === 'petugas') {
        header("Location: ../view/v_peminjaman_petugas.php");
    } elseif ($role_now === 'peminjam') {
        header("Location: ../view/v_daftar_alat.php");
    } else {
        header("Location: ../view/v_login.php");
    }
    exit();
}

$aksi = $_REQUEST['aksi'] ?? 'tampil';
$id   = $_REQUEST['id'] ?? null;


/* ==========================================================================
   1. LOGIKA TAMPIL DATA ALAT (READ)
   ========================================================================== */
if ($aksi === 'tampil') {
    // Mengambil semua daftar alat dari database melalui model
    $data_alat = $alat_model->tampil_data();
    if (basename($_SERVER['SCRIPT_FILENAME'] ?? '') === 'v_alat.php') {
        return;
    }
    
    // Tampilkan file view
    include '../view/v_alat.php';
    exit();
} 


/* ==========================================================================
   2. LOGIKA HALAMAN TAMBAH ALAT (FORM TAMBAH)
   ========================================================================== */
elseif ($aksi === 'tambah') {
    // Mengambil data kategori untuk dropdown pilihan kategori
    $data_kategori = $kategori_model->tampil_data(); 
    include '../view/v_tambah_alat.php';
    exit();
} 


/* ==========================================================================
   3. LOGIKA PROSES SIMPAN ALAT BARU
   ========================================================================== */
elseif ($aksi === 'proses_tambah') {
    require_csrf();
    $nama_alat   = trim($_POST['nama_alat'] ?? '');
    $id_kategori = $_POST['id_kategori'] ?? null;
    $stok        = $_POST['stok'] ?? 0;
    $foto        = $_POST['url_foto'] ?? '';

    // Meminta model untuk memasukkan data ke tabel alat
    $result = $alat_model->tambah_data($nama_alat, $id_kategori, $stok, $foto);
    
    if ($result) {
        $_SESSION['pesan'] = [
            'judul' => 'Berhasil!',
            'teks'  => 'Alat baru berhasil ditambahkan.',
            'tipe'  => 'success'
        ];
        header("Location: ../view/v_alat.php");
    } else {
        $_SESSION['pesan'] = [
            'judul' => 'Gagal!',
            'teks'  => 'Gagal menambahkan alat baru.',
            'tipe'  => 'error'
        ];
        header("Location: ../view/v_tambah_alat.php");
    }
    exit();
} 


/* ==========================================================================
   4. LOGIKA HALAMAN EDIT ALAT (FORM EDIT)
   ========================================================================== */
elseif ($aksi === 'edit' && $id) {
    // Mengambil data alat yang mau diedit berdasarkan ID
    $data_alat     = $alat_model->tampil_data_by_id($id);
    // Mengambil kategori untuk pilihan dropdown di form edit
    $data_kategori = $kategori_model->tampil_data(); 
    
    include '../view/v_update_alat.php';
    exit();
} 


/* ==========================================================================
   5. LOGIKA PROSES UPDATE ALAT
   ========================================================================== */
elseif ($aksi === 'update') {
    require_csrf();
    $id_alat = $_POST['id_alat'] ?? null;
    $nama    = trim($_POST['nama_alat'] ?? '');
    $id_kat  = $_POST['id_kategori'] ?? null;
    $stok    = $_POST['stok'] ?? 0;
    $foto    = $_POST['url_foto'] ?? ''; 

    // Memperbarui data di database melalui model
    $result = $alat_model->ubah_data($id_alat, $nama, $id_kat, $stok, $foto);
    
    if ($result) {
        $_SESSION['pesan'] = [
            'judul' => 'Berhasil!',
            'teks'  => 'Data alat berhasil diperbarui.',
            'tipe'  => 'success'
        ];
        header("Location: ../view/v_alat.php");
    } else {
        $_SESSION['pesan'] = [
            'judul' => 'Gagal!',
            'teks'  => 'Gagal mengupdate data alat.',
            'tipe'  => 'error'
        ];
        header("Location: v_update_alat.php?aksi=edit&id=" . $id_alat);
    }
    exit();
} 


/* ==========================================================================
   6. LOGIKA HAPUS ALAT (DELETE)
   ========================================================================== */
elseif ($aksi === 'hapus' && $id) {
    require_csrf();
    // Meminta model untuk menghapus alat berdasarkan ID
    $result = $alat_model->hapus_data($id);
    
    if ($result) {
        $_SESSION['pesan'] = [
            'judul' => 'Berhasil!',
            'teks'  => 'Data alat berhasil dihapus.',
            'tipe'  => 'success'
        ];
    } else {
        $_SESSION['pesan'] = [
            'judul' => 'Gagal!',
            'teks'  => 'Gagal menghapus data alat.',
            'tipe'  => 'error'
        ];
    }
    
    header("Location: ../view/v_alat.php");
    exit();
}


/* ==========================================================================
   7. DEFAULT FALLBACK
   ========================================================================== */
else {
    header("Location: ../view/v_alat.php");
    exit();
}