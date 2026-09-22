<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/*
|--------------------------------------------------------------------------
| CEK LOGIN & ROLE
|--------------------------------------------------------------------------
*/
if (!isset($_SESSION['role'])) {
    header("Location: v_login.php");
    exit();
}

if ($_SESSION['role'] !== 'admin') {

    if ($_SESSION['role'] === 'petugas') {
        header("Location: v_peminjaman_petugas.php");
    } else {
        header("Location: v_daftar_alat.php");
    }

    exit();
}

/*
|--------------------------------------------------------------------------
| KONEKSI & MODEL
|--------------------------------------------------------------------------
*/
require_once '../model/m_koneksi.php';
require_once '../model/m_alat.php';

$db = (new m_koneksi())->koneksi;

$alat_model = new m_alat($db);

/*
|--------------------------------------------------------------------------
| AMBIL ID ALAT DARI URL
|
| Contoh:
| v_alat_pinjam_admin.php?tipe=pinjam&id_alat=5
|--------------------------------------------------------------------------
*/
$id_alat = isset($_GET['id_alat'])
    ? (int) $_GET['id_alat']
    : 0;

$data_alat = null;

if ($id_alat > 0) {
    $data_alat = $alat_model->tampil_data_by_id($id_alat);
}

/*
|--------------------------------------------------------------------------
| CEK ALAT
|--------------------------------------------------------------------------
*/
if (!$data_alat) {

    $_SESSION['pesan'] = [
        'judul' => 'Alat tidak ditemukan',
        'teks'  => 'Data alat yang dipilih tidak ditemukan.',
        'tipe'  => 'error'
    ];

    header("Location: v_alat.php");
    exit();
}

/*
|--------------------------------------------------------------------------
| CEK STOK
|--------------------------------------------------------------------------
*/
$stok = (int) ($data_alat->stok ?? 0);

if ($stok < 1) {

    $_SESSION['pesan'] = [
        'judul' => 'Stok habis',
        'teks'  => 'Alat tersebut sedang tidak tersedia.',
        'tipe'  => 'warning'
    ];

    header("Location: v_alat.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Peminjaman Alat - Admin</title>

    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Font Awesome -->
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- SweetAlert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
          rel="stylesheet">

    <style>

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

    </style>

</head>

<body class="bg-slate-100 min-h-screen p-4 md:p-8">

<div class="max-w-2xl mx-auto">

    <!-- KEMBALI -->
    <a href="v_alat.php"
       class="inline-flex items-center gap-2 text-indigo-700 font-semibold mb-6">
        <i class="fa-solid fa-arrow-left"></i>
        Kembali ke Daftar Alat
    </a>

    <!-- FORM -->
    <div class="bg-white rounded-2xl shadow-xl border border-slate-200 p-6 md:p-8">

        <div class="border-b border-slate-200 pb-5 mb-6">
            <h1 class="text-2xl font-extrabold text-indigo-900">
                <i class="fa-solid fa-hand-holding text-indigo-600 mr-2"></i>
                Form Peminjaman
            </h1>
            <p class="text-sm text-slate-500 mt-2">
                Isi data peminjaman alat oleh Admin.
            </p>
        </div>

        <!-- INFORMASI ALAT -->
        <div class="bg-indigo-50 border border-indigo-200 rounded-xl p-5 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                <div>
                    <p class="text-xs text-indigo-500">Alat yang Dipilih</p>
                    <p class="font-bold text-slate-800 text-lg">
                        <?= htmlspecialchars($data_alat->nama_alat); ?>
                    </p>
                </div>

                <div>
                    <p class="text-xs text-slate-500">Kategori</p>
                    <p class="font-semibold text-slate-700">
                        <?= htmlspecialchars($data_alat->nama_kategori ?? '-'); ?>
                    </p>
                </div>

                <div>
                    <p class="text-xs text-slate-500">Stok Tersedia</p>
                    <p class="font-bold text-emerald-600">
                        <?= $stok; ?> Alat
                    </p>
                </div>

                <div>
                    <p class="text-xs text-slate-500">Status Pengajuan</p>
                    <span class="inline-block mt-1 px-3 py-1 bg-amber-100 text-amber-700 rounded-lg text-xs font-bold">
                        Akan menjadi PENDING
                    </span>
                </div>

            </div>
        </div>

        <form action="../controller/c_peminjaman.php?aksi=tambah_admin"
              method="POST"
              id="formPeminjaman">

            <input type="hidden"
                   name="id_alat"
                   value="<?= (int)$data_alat->id_alat; ?>">

            <!-- JAMINAN - KHUSUS ADMIN, TETAP KTP -->
            <div class="mb-5">
                <label class="block text-sm font-bold text-slate-700 mb-2">
                    Jaminan
                </label>

                <input type="hidden" name="jaminan" value="KTP">

                <div class="w-full px-4 py-3 border border-indigo-200 bg-indigo-50 text-indigo-700 rounded-xl font-semibold">
                    <i class="fa-solid fa-id-card mr-2"></i>
                    KTP
                </div>
            </div>

            <!-- NAMA PEMINJAM -->
            <div class="mb-5">
                <label for="nama_peminjam" class="block text-sm font-bold text-slate-700 mb-2">
                    Nama Peminjam
                </label>

                <input type="text"
                       name="nama_peminjam"
                       id="nama_peminjam"
                       list="daftar_peminjam"
                       required
                       autocomplete="off"
                       placeholder="Ketik nama/username peminjam"
                       class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-400">

                <datalist id="daftar_peminjam">
                    <?php
                    require_once '../model/m_peminjaman.php';
                    $pinjam_model = new m_peminjaman($db);
                    $list_peminjam = $pinjam_model->get_all_peminjam();

                    while ($user = mysqli_fetch_assoc($list_peminjam)):
                    ?>
                        <option value="<?= htmlspecialchars($user['username']); ?>"
                                data-id="<?= (int)$user['id_user']; ?>"></option>
                    <?php endwhile; ?>
                </datalist>

                <p class="text-xs text-slate-500 mt-2">
                    Ketik nama/username peminjam yang sudah terdaftar.
                </p>
            </div>

            <!-- JUMLAH -->
            <div class="mb-5">
                <label for="jumlah" class="block text-sm font-bold text-slate-700 mb-2">
                    Jumlah Pinjam
                </label>

                <div class="relative">
                    <input type="number"
                           name="jumlah_pinjam"
                           id="jumlah"
                           min="1"
                           max="<?= $stok; ?>"
                           value="1"
                           required
                           placeholder="Masukkan jumlah alat"
                           class="w-full px-4 py-3 pr-16 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-400">

                    <span class="absolute right-4 top-1/2 -translate-y-1/2 text-xs text-slate-400">
                        unit
                    </span>
                </div>

                <p class="text-xs text-slate-500 mt-2">
                    Maksimal <?= $stok; ?> alat.
                </p>
            </div>

            <!-- TANGGAL -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
                <div>
                    <label for="tanggal_pinjam" class="block text-sm font-bold text-slate-700 mb-2">
                        Tanggal Pinjam
                    </label>
                    <input type="date"
                           name="tanggal_pinjam"
                           id="tanggal_pinjam"
                           required
                           class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-400">
                </div>

                <div>
                    <label for="tanggal_kembali" class="block text-sm font-bold text-slate-700 mb-2">
                        Tanggal Kembali
                    </label>
                    <input type="date"
                           name="tanggal_kembali"
                           id="tanggal_kembali"
                           required
                           class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-400">
                </div>
            </div>

            <!-- KONDISI -->
            <input type="hidden" name="kondisi_keluar" value="Baik">

            <!-- KEPERLUAN -->
            <div class="mb-6">
                <label for="keperluan" class="block text-sm font-bold text-slate-700 mb-2">
                    Keperluan
                </label>
                <textarea name="keperluan"
                          id="keperluan"
                          rows="4"
                          required
                          placeholder="Masukkan keperluan peminjaman"
                          class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-400"></textarea>
            </div>

            <!-- INFO -->
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-6">
                <p class="text-sm text-amber-800">
                    <i class="fa-solid fa-circle-info mr-1"></i>
                    Peminjaman oleh Admin akan langsung dicatat sebagai pengajuan.
                </p>
            </div>

            <!-- BUTTON -->
            <div class="flex flex-col-reverse sm:flex-row justify-end gap-3">
                <a href="v_alat.php"
                   class="px-5 py-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold transition text-center">
                    <i class="fa-solid fa-arrow-left mr-1"></i>
                    Kembali
                </a>

                <button type="submit"
                        class="px-5 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold shadow-md transition">
                    <i class="fa-solid fa-check mr-1"></i>
                    Simpan Peminjaman
                </button>
            </div>

        </form>
    </div>
</div>

<script>
const stokMaksimal = <?= $stok; ?>;

document.addEventListener('DOMContentLoaded', function () {
    const tanggalPinjam = document.getElementById('tanggal_pinjam');
    const tanggalKembali = document.getElementById('tanggal_kembali');

    const sekarang = new Date();
    const tahun = sekarang.getFullYear();
    const bulan = String(sekarang.getMonth() + 1).padStart(2, '0');
    const tanggal = String(sekarang.getDate()).padStart(2, '0');
    const hariIni = `${tahun}-${bulan}-${tanggal}`;

    tanggalPinjam.value = hariIni;
    tanggalPinjam.min = hariIni;
    tanggalKembali.min = hariIni;
});

document.getElementById('jumlah').addEventListener('input', function () {
    let nilai = parseInt(this.value) || 1;

    if (nilai < 1) this.value = 1;

    if (nilai > stokMaksimal) {
        this.value = stokMaksimal;
        Swal.fire({
            icon: 'warning',
            title: 'Stok tidak mencukupi',
            text: `Stok alat yang tersedia hanya ${stokMaksimal} unit.`,
            confirmButtonColor: '#4f46e5'
        });
    }
});

document.getElementById('tanggal_pinjam').addEventListener('change', function () {
    const tanggalPinjam = this.value;
    const tanggalKembali = document.getElementById('tanggal_kembali');

    tanggalKembali.min = tanggalPinjam;

    if (tanggalKembali.value && tanggalKembali.value < tanggalPinjam) {
        tanggalKembali.value = '';
    }
});

document.getElementById('formPeminjaman').addEventListener('submit', function (e) {
    const jumlah = parseInt(document.getElementById('jumlah').value) || 0;
    const tanggalPinjam = document.getElementById('tanggal_pinjam').value;
    const tanggalKembali = document.getElementById('tanggal_kembali').value;

    if (jumlah < 1 || jumlah > stokMaksimal) {
        e.preventDefault();
        Swal.fire({
            icon: 'warning',
            title: 'Jumlah tidak valid',
            text: `Jumlah pinjaman maksimal ${stokMaksimal} unit.`,
            confirmButtonColor: '#4f46e5'
        });
        return;
    }

    if (tanggalKembali < tanggalPinjam) {
        e.preventDefault();
        Swal.fire({
            icon: 'warning',
            title: 'Tanggal tidak valid',
            text: 'Tanggal kembali tidak boleh sebelum tanggal pinjam.',
            confirmButtonColor: '#4f46e5'
        });
    }
});
</script>

</body>
</html>
