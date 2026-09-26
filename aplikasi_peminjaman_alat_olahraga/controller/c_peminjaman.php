<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../model/m_security.php';
require_once '../model/m_koneksi.php';
require_once '../model/m_peminjaman.php';

$db = (new m_koneksi())->koneksi;
$pinjam_model = new m_peminjaman($db);

$aksi = $_GET['aksi'] ?? '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$role = $_SESSION['role'] ?? '';

function redirect_login() {
    header('Location: ../view/v_login.php');
    exit();
}

function redirect_role($role) {
    if ($role === 'admin') {
        header('Location: ../view/v_tampilan_user.php');
    } elseif ($role === 'petugas') {
        header('Location: ../view/v_peminjaman_petugas.php');
    } elseif ($role === 'peminjam') {
        header('Location: ../view/v_daftar_alat.php');
    } else {
        redirect_login();
    }
    exit();
}

// PEMINJAM: mengajukan pinjaman untuk dirinya sendiri.
if ($aksi === 'proses_pinjam') {
    require_csrf();
    if ($role !== 'peminjam' || !isset($_SESSION['id_user'])) {
        redirect_role($role);
    }

    $id_user = (int)$_SESSION['id_user'];
    $id_alat = (int)($_POST['id_alat'] ?? 0);
    $jumlah = (int)($_POST['jumlah_pinjam'] ?? 0);
    $kondisi = trim($_POST['kondisi_keluar'] ?? 'Baik');

    $simpan = $pinjam_model->tambah_pinjam($id_user, $id_alat, $jumlah, $kondisi);

    if ($simpan) {
        $pinjam_model->log_aktivitas($id_user, 'User melakukan request pinjam alat baru');
        header('Location: ../view/v_peminjaman_user.php?pesan=sukses_tambah');
    } else {
        header('Location: ../view/v_daftar_alat.php?pesan=gagal_tambah');
    }
    exit();
}

// ADMIN: meminjam alat untuk akun admin yang sedang login.
if ($aksi === 'tambah_admin') {
    require_csrf();
    if ($role !== 'admin' || !isset($_SESSION['id_user'])) {
        redirect_role($role);
    }

    $id_user = (int)$_SESSION['id_user'];
    $id_alat = (int)($_POST['id_alat'] ?? 0);
    $jumlah = (int)($_POST['jumlah_pinjam'] ?? 0);
    $kondisi = trim($_POST['kondisi_keluar'] ?? 'Baik');

    if ($id_alat <= 0 || $jumlah <= 0) {
        header("Location: ../view/v_alat.php?pesan=data_tidak_valid");
        exit();
    }

    $simpan = $pinjam_model->tambah_pinjam_admin(
        $id_user, $id_alat, $jumlah, $kondisi, 'KTP', ''
    );

    if ($simpan === 'stok_kurang') {
        header("Location: ../view/v_alat_pinjam_admin.php?tipe=pinjam&id_alat=$id_alat&pesan=stok_kurang");
        exit();
    }

    if ($simpan === 'alat_tidak_ditemukan') {
        header("Location: ../view/v_alat.php?pesan=alat_tidak_ditemukan");
        exit();
    }

    if ($simpan === 'user_tidak_ditemukan') {
        header("Location: ../view/v_login.php?pesan=akun_admin_tidak_valid");
        exit();
    }

    if ($simpan) {
        $pinjam_model->log_aktivitas(
            $id_user,
            "Admin meminjam alat untuk dirinya sendiri, alat ID: $id_alat"
        );
        header('Location: ../view/v_peminjaman_admin.php?tipe=pinjam&pesan=sukses_tambah');
        exit();
    }

    header("Location: ../view/v_alat_pinjam_admin.php?tipe=pinjam&id_alat=$id_alat&pesan=gagal_tambah");
    exit();
}

// PETUGAS: menyetujui pengajuan pending.
if ($aksi === 'setuju') {
    require_csrf();
    if ($role !== 'petugas') {
        redirect_role($role);
    }

    $hasil = $pinjam_model->verifikasi_pinjam($id);

    if ($hasil) {
        $pinjam_model->log_aktivitas(
            (int)$_SESSION['id_user'],
            "Petugas menyetujui peminjaman ID: $id"
        );
        header('Location: ../view/v_peminjaman_petugas.php?pesan=sukses_setuju');
    } else {
        header('Location: ../view/v_peminjaman_petugas.php?pesan=gagal_setuju');
    }
    exit();
}

// ADMIN/PETUGAS: konfirmasi pengembalian.
if ($aksi === 'konfirmasi_kembali') {
    require_csrf();
    if (!in_array($role, ['admin', 'petugas'], true)) {
        redirect_role($role);
    }

    $hasil = $pinjam_model->konfirmasi_kembali($id);
    $pesan = $hasil ? 'sukses_kembali' : 'gagal_kembali';

    if ($hasil) {
        $pinjam_model->log_aktivitas(
            (int)$_SESSION['id_user'],
            ucfirst($role) . " mengonfirmasi pengembalian alat ID: $id"
        );
    }

    if ($role === 'admin') {
        header("Location: ../view/v_peminjaman_admin.php?tipe=kembali&pesan=$pesan");
    } else {
        header("Location: ../view/v_peminjaman_petugas.php?pesan=$pesan");
    }
    exit();
}

// ADMIN/PETUGAS: hapus peminjaman.
if ($aksi === 'hapus') {
    require_csrf();
    if (!in_array($role, ['admin', 'petugas'], true)) {
        redirect_role($role);
    }

    $tipe = $_GET['tipe'] ?? 'pinjam';
    $hasil = $pinjam_model->hapus_data($id);
    $pesan = $hasil ? 'sukses_hapus' : 'gagal_hapus';

    if ($hasil) {
        $pinjam_model->log_aktivitas(
            (int)$_SESSION['id_user'],
            ucfirst($role) . " menghapus data peminjaman ID: $id"
        );
    }

    if ($role === 'admin') {
        header("Location: ../view/v_peminjaman_admin.php?tipe=" . urlencode($tipe) . "&pesan=$pesan");
    } else {
        header("Location: ../view/v_peminjaman_petugas.php?pesan=$pesan");
    }
    exit();
}

// ADMIN: ambil data untuk form edit.
$data_edit = null;
if (in_array($aksi, ['edit_pinjam', 'edit_kembali'], true) && $id > 0) {
    if ($role !== 'admin') {
        redirect_role($role);
    }

    $stmt = $db->prepare(
        "SELECT p.*, u.username, u.no_hp, a.nama_alat
         FROM peminjaman p
         JOIN user u ON p.id_user = u.id_user
         JOIN alat a ON p.id_alat = a.id_alat
         WHERE p.id_peminjaman = ? LIMIT 1"
    );
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $data_edit = $stmt->get_result()->fetch_object();
    $stmt->close();
}

// ADMIN: ubah data peminjaman.
if ($aksi === 'update_pinjam') {
    require_csrf();
    if ($role !== 'admin') {
        redirect_role($role);
    }

    $id_peminjaman = (int)($_POST['id_peminjaman'] ?? 0);
    $jumlah = (int)($_POST['jumlah_pinjam'] ?? 0);
    $status = $_POST['status'] ?? '';

    $simpan = $pinjam_model->update_pinjam($id_peminjaman, $jumlah, $status);
    $pesan = $simpan === 'stok_kurang'
        ? 'stok_kurang'
        : ($simpan ? 'sukses_update' : 'gagal_update');

    if ($simpan) {
        $pinjam_model->log_aktivitas(
            (int)$_SESSION['id_user'],
            "Admin mengubah data peminjaman ID: $id_peminjaman"
        );
    }

    header("Location: ../view/v_peminjaman_admin.php?tipe=pinjam&pesan=$pesan");
    exit();
}

// ADMIN: ubah data pengembalian.
if ($aksi === 'update_kembali') {
    require_csrf();
    if ($role !== 'admin') {
        redirect_role($role);
    }

    $id_peminjaman = (int)($_POST['id_peminjaman'] ?? 0);
    $kondisi = trim($_POST['kondisi_masuk'] ?? '');
    $tgl = trim($_POST['tgl_kembali_asli'] ?? '');

    $simpan = $pinjam_model->update_kembali($id_peminjaman, $kondisi, $tgl);

    if ($simpan) {
        $pinjam_model->log_aktivitas(
            (int)$_SESSION['id_user'],
            "Admin mengubah data pengembalian ID: $id_peminjaman"
        );
    }

    header(
        'Location: ../view/v_peminjaman_admin.php?tipe=kembali&pesan=' .
        ($simpan ? 'sukses_kembali' : 'gagal_update')
    );
    exit();
}

// Jika controller dipanggil untuk mengambil data tampilan.
if ($role === 'petugas') {
    $isi_tabel = $pinjam_model->tampil_data();
} elseif ($role === 'admin') {
    $isi_tabel = $pinjam_model->tampil_data_admin($_GET['tipe'] ?? 'pinjam');
}

if (isset($_SESSION['id_user'])) {
    $isi_tabel_user = $pinjam_model->tampil_data_user((int)$_SESSION['id_user']);
}
?>
