<?php
// Memulai sesi untuk menyimpan data login & notifikasi SweetAlert
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Mengambil file koneksi dan model
require_once '../model/m_koneksi.php'; 
require_once '../model/m_user.php'; 

// Membuat objek koneksi database
$koneksi_obj = new m_koneksi();
$dbConnection = $koneksi_obj->koneksi; 

// Cek apakah koneksi berhasil
if (!$dbConnection) {
    die("Koneksi database gagal.");
}

// Menyiapkan model user
$user_model = new m_user($dbConnection);

// Menangkap instruksi aksi & id dari URL
$aksi = $_GET['aksi'] ?? 'tampil'; 
$id   = $_GET['id'] ?? null;


/* ==========================================================================
   1. LOGIKA TAMPIL DATA (READ)
   ========================================================================== */
if ($aksi === 'tampil') {
    // Ambil semua data user dari model
    $users = $user_model->tampil_data();
    
    // Tampilkan file view
    include '../view/v_tampilan_user.php'; 
    exit(); 
} 


/* ==========================================================================
   2. LOGIKA EDIT (FORM EDIT USER)
   ========================================================================== */
elseif ($aksi === 'edit' && $id) {
    // Ambil data user spesifik berdasarkan ID
    $users = $user_model->tampil_data_by_id($id);
    
    // Tampilkan form update
    include_once '../view/v_update_user.php';
    exit();
} 


/* ==========================================================================
   3. LOGIKA HAPUS DATA (DELETE)
   ========================================================================== */
elseif ($aksi === 'hapus' && $id) {
    // Eksekusi hapus data
    $result = $user_model->hapus_data($id);
    
    if ($result) {
        $_SESSION['pesan'] = [
            'tipe'  => 'success',
            'judul' => 'Berhasil Dihapus!',
            'teks'  => 'Data pengguna berhasil dihapus dari sistem.'
        ];
    } else {
        $_SESSION['pesan'] = [
            'tipe'  => 'error',
            'judul' => 'Gagal Hapus!',
            'teks'  => 'Data pengguna gagal dihapus.'
        ];
    }
    
    header("Location: ../view/v_tampilan_user.php");
    exit();
}


/* ==========================================================================
   4. LOGIKA PROSES FORM (POST: TAMBAH & UPDATE)
   ========================================================================== */
elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Tangkap data input dari Form
    $id_user  = $_POST['id_user'] ?? null;
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? null;
    $role     = $_POST['role'] ?? 'peminjam';
    
    // Hash password jika terisi
    $pass_hash = !empty($password) ? password_hash($password, PASSWORD_DEFAULT) : null;


    // --- SUB-LOGIKA A: TAMBAH USER / REGISTRASI ---
    if ($aksi === 'tambah') {
        
        // Validasi wajib isi password
        if (empty($password)) {
            $_SESSION['pesan'] = [
                'tipe'  => 'warning',
                'judul' => 'Peringatan!',
                'teks'  => 'Password wajib diisi.'
            ];
            header("Location: " . $_SERVER['HTTP_REFERER']);
            exit();
        }
        
        // Eksekusi tambah ke database
        $result = $user_model->tambah_data($username, $pass_hash, $role, $_POST['no_hp'] ?? null);
        
        if ($result) {
            // Cek apakah yang menambah data adalah Admin (di dalam dashboard)
            if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
                $_SESSION['pesan'] = [
                    'tipe'  => 'success',
                    'judul' => 'Berhasil!',
                    'teks'  => 'User baru berhasil ditambahkan oleh Admin.'
                ];
                header("Location: ../view/v_tampilan_user.php");
            } else {
                // Jika registrasi mandiri dari halaman depan
                $_SESSION['pesan'] = [
                    'tipe'  => 'success',
                    'judul' => 'Registrasi Berhasil!',
                    'teks'  => 'Akun Anda berhasil dibuat. Silakan login.'
                ];
                header("Location: ../view/v_login.php");
            }
        } else {
            $_SESSION['pesan'] = [
                'tipe'  => 'error',
                'judul' => 'Gagal!',
                'teks'  => 'Gagal menambahkan data user baru.'
            ];
            header("Location: " . $_SERVER['HTTP_REFERER']);
        }
        exit();
    }
    

    // --- SUB-LOGIKA B: UPDATE / UBAH USER ---
    elseif ($aksi === 'update') {
        
        // Eksekusi update data
        $result = $user_model->ubah_data($id_user, $username, $pass_hash, $_POST['no_hp'] ?? null);
        
        if ($result) {
            $_SESSION['pesan'] = [
                'tipe'  => 'success',
                'judul' => 'Berhasil Diperbarui!',
                'teks'  => 'Data pengguna telah berhasil diperbarui.'
            ];
            header("Location: ../view/v_tampilan_user.php");
        } else {
            $_SESSION['pesan'] = [
                'tipe'  => 'error',
                'judul' => 'Gagal!',
                'teks'  => 'Gagal memperbarui data pengguna.'
            ];
            header("Location: " . $_SERVER['HTTP_REFERER']);
        }
        exit();
    }
}


/* ==========================================================================
   5. DEFAULT FALLBACK (Jika Aksinya Tidak Dikenali)
   ========================================================================== */
else {
    header("Location: ../view/v_tampilan_user.php");
    exit();
}