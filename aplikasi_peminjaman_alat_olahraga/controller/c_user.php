
<?php

// ==========================================
// 1. MEMULAI SESSION
// ==========================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


// ==========================================
// 2. MENGHUBUNGKAN DATABASE DAN MODEL
// ==========================================

require_once '../model/m_koneksi.php';
require_once '../model/m_user.php';

$koneksi_obj = new m_koneksi();
$dbConnection = $koneksi_obj->koneksi;

if (!$dbConnection) {
    die("Koneksi database gagal.");
}

$user_model = new m_user($dbConnection);


// ==========================================
// 3. MENGAMBIL AKSI
// ==========================================

$aksi = $_GET['aksi'] ?? 'tampil';
$id   = $_GET['id'] ?? null;

$isAdmin = isset($_SESSION['role']) &&
           strtolower($_SESSION['role']) === 'admin';

$urlRegister = '../view/v_register.php';
$urlTambahAdmin = '../view/v_tambah_user.php';

$urlKembali = $isAdmin ? $urlTambahAdmin : $urlRegister;


// ==========================================
// 4. TAMPIL DATA USER
// ==========================================

if ($aksi === 'tampil') {

    $users = $user_model->tampil_data();

    include '../view/v_tampilan_user.php';
    exit();
}


// ==========================================
// 5. EDIT DATA USER
// ==========================================

elseif ($aksi === 'edit' && $id) {

    $users = $user_model->tampil_data_by_id($id);

    include_once '../view/v_update_user.php';
    exit();
}


// ==========================================
// 6. HAPUS DATA USER
// ==========================================

elseif ($aksi === 'hapus' && $id) {

    $result = $user_model->hapus_data($id);

    if ($result) {

        $_SESSION['pesan'] = [
            'tipe'  => 'success',
            'judul' => 'Berhasil Dihapus!',
            'teks'  => 'Data pengguna berhasil dihapus.'
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


// ==========================================
// 7. PROSES FORM TAMBAH DAN UPDATE
// ==========================================

elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id_user  = $_POST['id_user'] ?? null;

    $username = trim($_POST['username'] ?? '');

    $password = $_POST['password'] ?? '';

    // Registrasi mandiri hanya boleh menjadi peminjam.
    $role = $isAdmin
        ? ($_POST['role'] ?? 'peminjam')
        : 'peminjam';

    // Form register menggunakan no_telp.
    // Database dan model menggunakan no_hp.
    $no_hp = trim($_POST['no_telp'] ?? $_POST['no_hp'] ?? '');


    // ======================================
    // 8. VALIDASI INPUT
    // ======================================

    if ($username === '') {

        $_SESSION['pesan'] = [
            'tipe'  => 'warning',
            'judul' => 'Username Kosong!',
            'teks'  => 'Username wajib diisi.'
        ];

        header("Location: " . $urlKembali);
        exit();
    }

    if (strlen($username) < 4 || strlen($username) > 20 ||
        !preg_match('/^[A-Za-z0-9]+$/', $username)) {

        $_SESSION['pesan'] = [
            'tipe'  => 'warning',
            'judul' => 'Username Tidak Valid!',
            'teks'  => 'Username harus terdiri dari 4-20 karakter huruf atau angka.'
        ];

        header("Location: " . $urlKembali);
        exit();
    }


    // ======================================
    // 9. PROSES TAMBAH / REGISTER
    // ======================================

    if ($aksi === 'tambah') {

        if ($password === '') {

            $_SESSION['pesan'] = [
                'tipe'  => 'warning',
                'judul' => 'Password Kosong!',
                'teks'  => 'Password wajib diisi.'
            ];

            header("Location: " . $urlKembali);
            exit();
        }

        if (strlen($password) < 4) {

            $_SESSION['pesan'] = [
                'tipe'  => 'warning',
                'judul' => 'Password Tidak Valid!',
                'teks'  => 'Password minimal harus 4 karakter.'
            ];

            header("Location: " . $urlKembali);
            exit();
        }

        // Nomor telepon wajib untuk registrasi mandiri.
        if (!$isAdmin && !preg_match('/^[0-9]{10,15}$/', $no_hp)) {

            $_SESSION['pesan'] = [
                'tipe'  => 'warning',
                'judul' => 'Nomor Telepon Tidak Valid!',
                'teks'  => 'Nomor telepon harus terdiri dari 10-15 angka.'
            ];

            header("Location: " . $urlRegister);
            exit();
        }

        // Cek username duplikat.
        try {

            if ($user_model->username_sudah_ada($username)) {

                $_SESSION['pesan'] = [
                    'tipe'  => 'warning',
                    'judul' => 'Username Sudah Digunakan!',
                    'teks'  => 'Silakan gunakan username yang berbeda.'
                ];

                header("Location: " . $urlKembali);
                exit();
            }

            // Hash password.
            $pass_hash = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            // Simpan user ke database.
            $result = $user_model->tambah_data(
                $username,
                $pass_hash,
                $role,
                $no_hp
            );

            if ($result) {

                if ($isAdmin) {

                    $_SESSION['pesan'] = [
                        'tipe'  => 'success',
                        'judul' => 'Berhasil!',
                        'teks'  => 'Pengguna baru berhasil ditambahkan.'
                    ];

                    header("Location: ../view/v_tampilan_user.php");

                } else {

                    $_SESSION['pesan'] = [
                        'tipe'  => 'success',
                        'judul' => 'Registrasi Berhasil!',
                        'teks'  => 'Akun berhasil dibuat. Silakan login menggunakan username dan password.'
                    ];

                    header("Location: ../view/v_login.php");
                }

                exit();

            } else {

                $_SESSION['pesan'] = [
                    'tipe'  => 'error',
                    'judul' => 'Gagal!',
                    'teks'  => 'Data pengguna gagal disimpan ke database.'
                ];

                header("Location: " . $urlKembali);
                exit();
            }

        } catch (mysqli_sql_exception $e) {

            // Catat penyebab asli error ke log hosting.
            error_log(
                'ERROR REGISTER USER: ' . $e->getMessage()
            );

            if ((int)$e->getCode() === 1062) {

                $_SESSION['pesan'] = [
                    'tipe'  => 'warning',
                    'judul' => 'Data Sudah Digunakan!',
                    'teks'  => 'Username atau nomor telepon sudah terdaftar.'
                ];

            } else {

                $_SESSION['pesan'] = [
                    'tipe'  => 'error',
                    'judul' => 'Gagal Menyimpan!',
                    'teks'  => 'Terjadi kesalahan database. Periksa struktur tabel user dan coba lagi.'
                ];
            }

            header("Location: " . $urlKembali);
            exit();
        }
    }


    // ======================================
    // 10. PROSES UPDATE USER
    // ======================================

    elseif ($aksi === 'update') {

        $pass_hash = !empty($password)
            ? password_hash($password, PASSWORD_DEFAULT)
            : null;

        try {

            $result = $user_model->ubah_data(
                $id_user,
                $username,
                $pass_hash,
                $no_hp
            );

            if ($result) {

                $_SESSION['pesan'] = [
                    'tipe'  => 'success',
                    'judul' => 'Berhasil Diperbarui!',
                    'teks'  => 'Data pengguna berhasil diperbarui.'
                ];

            } else {

                $_SESSION['pesan'] = [
                    'tipe'  => 'error',
                    'judul' => 'Gagal!',
                    'teks'  => 'Data pengguna gagal diperbarui.'
                ];
            }

        } catch (mysqli_sql_exception $e) {

            error_log(
                'ERROR UPDATE USER: ' . $e->getMessage()
            );

            $_SESSION['pesan'] = [
                'tipe'  => 'error',
                'judul' => 'Gagal!',
                'teks'  => 'Terjadi kesalahan saat memperbarui pengguna.'
            ];
        }

        header("Location: ../view/v_tampilan_user.php");
        exit();
    }
}


// ==========================================
// 11. AKSI TIDAK DIKENALI
// ==========================================

else {

    header("Location: ../view/v_login.php");
    exit();
}

?>
