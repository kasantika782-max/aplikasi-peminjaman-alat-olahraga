<?php
session_start();
require_once '../model/m_koneksi.php';
require_once '../model/m_login.php';
require_once '../model/m_peminjaman.php';

$database = new m_koneksi();
$db = $database->koneksi;

$login_model = new m_login($db);
$pinjam_model = new m_peminjaman($db);

$aksi = $_GET['aksi'] ?? '';

if ($aksi == 'proses_login') {
    $user   = $_POST['username'] ?? '';
    $pass   = $_POST['password'] ?? ''; 
    $no_hp  = $_POST['no_hp'] ?? ''; 

    $data = $login_model->validasi_user($user, $pass, $no_hp);

    if ($data) {
        // Set Session
        $_SESSION['id_user']  = $data->id_user;
        $_SESSION['username'] = $data->username;
        $_SESSION['role']     = $data->role; 

        // =========================================================
        // HAPUS SISA PESAN REGISTRASI AGAR TIDAK TERBAWA KE DASHBOARD
        // =========================================================
        unset($_SESSION['pesan']);

        // CATAT KE LOG
        $pinjam_model->log_aktivitas($data->id_user, "User login ke sistem");

        // Redirect sesuai Role
        switch ($data->role) {
            case 'admin':
                header("location:../view/v_tampilan_user.php");
                break;
            case 'petugas':
                header("location:../view/v_peminjaman_petugas.php");
                break;
            case 'peminjam':
                header("location:../view/v_daftar_alat.php");
                break;
            default:
                header("location:../view/v_login.php");
        }
        exit();
    } else {
        // Menggunakan SweetAlert Flash Message untuk Notifikasi Gagal Login
        $_SESSION['pesan'] = [
            'judul' => 'Login Gagal!',
            'teks'  => 'Username, Password, atau Nomor Telepon salah!',
            'tipe'  => 'error'
        ];
        header("location:../view/v_login.php");
        exit();
    }
} 
// Jika kamu juga menangani proses registrasi di controller ini:
elseif ($aksi == 'proses_register') {
    // ... logic simpan user ke DB kamu ...
    
    // Set pesan sukses registrasi untuk ditangkap v_login.php
    $_SESSION['pesan'] = [
        'judul' => 'Registrasi Berhasil!',
        'teks'  => 'Akun Anda berhasil dibuat. Silakan login.',
        'tipe'  => 'success'
    ];
    
    header("location:../view/v_login.php");
    exit();
}
elseif ($aksi == 'logout') {
    session_destroy();
    header("location:../view/v_login.php");
    exit();
}