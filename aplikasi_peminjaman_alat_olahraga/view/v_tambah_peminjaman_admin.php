
<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Proteksi halaman
if (
    !isset($_SESSION['role']) ||
    ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'petugas')
) {
    header("Location: v_login.php");
    exit();
}

require_once '../model/m_koneksi.php';
require_once '../model/m_peminjaman.php';

$database = new m_koneksi();
$db = $database->koneksi;

$pinjam_model = new m_peminjaman($db);

// Ambil data peminjam
$result_peminjam = $pinjam_model->get_all_peminjam();

$list_peminjam = [];

if ($result_peminjam) {
    while ($row = mysqli_fetch_assoc($result_peminjam)) {
        $list_peminjam[] = $row;
    }
}

// Ambil data alat
$result_alat = $pinjam_model->get_all_alat();

$list_alat = [];

if ($result_alat) {
    while ($row = mysqli_fetch_assoc($result_alat)) {
        $list_alat[] = $row;
    }
}

// Data untuk JavaScript
$data_peminjam = [];

foreach ($list_peminjam as $user) {
    $data_peminjam[] = [
        'id' => (string)$user['id_user'],
        'nama' => $user['username']
    ];
}

$data_alat = [];

foreach ($list_alat as $alat) {
    $data_alat[] = [
        'id' => (string)$alat['id_alat'],
        'nama' => $alat['nama_alat'],
        'stok' => (int)$alat['stok']
    ];
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Peminjaman Manual</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
        rel="stylesheet">

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
    </style>

</head>

<body class="bg-slate-900 min-h-screen flex items-center justify-center p-4 text-slate-100">

    <div class="w-full max-w-lg bg-slate-800 text-slate-100 rounded-2xl shadow-2xl border border-slate-700/50 p-6 sm:p-8 my-6">

        <!-- HEADER -->

        <div class="text-center mb-6">

            <div class="w-12 h-12 bg-indigo-600/20 text-indigo-400 rounded-2xl flex items-center justify-center mx-auto mb-3 border border-indigo-500/30">

                <i class="fa-solid fa-hand-holding-box text-xl"></i>

            </div>

            <h2 class="text-2xl font-extrabold tracking-tight text-white">
                Peminjaman Manual
            </h2>

            <p class="text-slate-400 text-sm mt-1">
                Input transaksi peminjaman langsung untuk pengguna
            </p>

        </div>


        <!-- FORM -->

        <form
            action="../controller/c_peminjaman.php?aksi=tambah_admin"
            method="POST"
            class="space-y-4"
            id="formPeminjaman"
        >

            <!-- NAMA PEMINJAM -->

            <div>

                <label class="block text-sm font-medium text-slate-300 mb-2">
                    Nama Peminjam
                </label>

                <input
                    type="text"
                    id="nama_peminjam"
                    list="daftar_peminjam"
                    placeholder="Ketik username peminjam..."
                    autocomplete="off"
                    class="w-full px-4 py-3 rounded-xl bg-slate-900/80 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    required
                >

                <datalist id="daftar_peminjam">

                    <?php foreach ($list_peminjam as $user): ?>

                        <option value="<?= htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8'); ?>"></option>

                    <?php endforeach; ?>

                </datalist>

                <input
                    type="hidden"
                    name="id_user"
                    id="id_user"
                >

                <small class="text-slate-400 block mt-2">
                    Ketik username pengguna yang akan meminjam.
                </small>

            </div>


            <!-- NAMA ALAT -->

            <div>

                <label class="block text-sm font-medium text-slate-300 mb-2">
                    Nama Alat
                </label>

                <input
                    type="text"
                    id="nama_alat"
                    list="daftar_alat"
                    placeholder="Ketik nama alat..."
                    autocomplete="off"
                    class="w-full px-4 py-3 rounded-xl bg-slate-900/80 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    required
                >

                <datalist id="daftar_alat">

                    <?php foreach ($list_alat as $alat): ?>

                        <option value="<?= htmlspecialchars($alat['nama_alat'], ENT_QUOTES, 'UTF-8'); ?>"></option>

                    <?php endforeach; ?>

                </datalist>

                <input
                    type="hidden"
                    name="id_alat"
                    id="id_alat"
                >

                <small id="info_stok" class="text-slate-400 block mt-2">
                    Ketik nama alat yang akan dipinjam.
                </small>

            </div>


            <!-- JUMLAH -->

            <div>

                <label class="block text-sm font-medium text-slate-300 mb-2">
                    Jumlah Pinjam
                </label>

                <input
                    type="number"
                    name="jumlah_pinjam"
                    id="jumlah_pinjam"
                    min="1"
                    placeholder="Masukkan jumlah unit"
                    class="w-full px-4 py-3 rounded-xl bg-slate-900/80 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    required
                >

            </div>


            <!-- KONDISI -->

            <div>

                <label class="block text-sm font-medium text-slate-300 mb-2">
                    Kondisi Keluar
                </label>

                <select
                    name="kondisi_keluar"
                    class="w-full px-4 py-3 rounded-xl bg-slate-900/80 border border-slate-700 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                >

                    <option value="Baik">Baik</option>

                    <option value="Rusak Ringan">Rusak Ringan</option>

                </select>

            </div>


            <!-- TOMBOL -->

            <div class="pt-4 space-y-3">

                <button
                    type="submit"
                    class="w-full py-3.5 px-4 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold rounded-xl shadow-lg hover:shadow-indigo-500/25 transition duration-200 uppercase tracking-wider text-xs flex items-center justify-center gap-2"
                >

                    <i class="fa-solid fa-floppy-disk"></i>

                    <span>Simpan Peminjaman</span>

                </button>

                <a
                    href="v_peminjaman_admin.php?tipe=pinjam"
                    class="block text-center text-sm text-slate-400 hover:text-slate-200 transition duration-150 py-1"
                >

                    ← Batal / Kembali

                </a>

            </div>

        </form>

    </div>


    <!-- JAVASCRIPT -->

    <script>

        const dataPeminjam = <?= json_encode(
            $data_peminjam,
            JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
        ); ?>;

        const dataAlat = <?= json_encode(
            $data_alat,
            JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
        ); ?>;


        const namaPeminjam = document.getElementById('nama_peminjam');
        const idUser = document.getElementById('id_user');

        const namaAlat = document.getElementById('nama_alat');
        const idAlat = document.getElementById('id_alat');

        const infoStok = document.getElementById('info_stok');

        const jumlahPinjam = document.getElementById('jumlah_pinjam');


        // Pencarian peminjam berdasarkan username

        namaPeminjam.addEventListener('input', function () {

            idUser.value = '';

            const ditemukan = dataPeminjam.find(user =>
                user.nama.toLowerCase() === this.value.trim().toLowerCase()
            );

            if (ditemukan) {
                idUser.value = ditemukan.id;
            }

        });


        // Pencarian alat berdasarkan nama

        namaAlat.addEventListener('input', function () {

            idAlat.value = '';

            jumlahPinjam.removeAttribute('max');

            infoStok.textContent = 'Ketik nama alat yang akan dipinjam.';

            const ditemukan = dataAlat.find(alat =>
                alat.nama.toLowerCase() === this.value.trim().toLowerCase()
            );

            if (ditemukan) {

                idAlat.value = ditemukan.id;

                infoStok.textContent = 'Stok tersedia: ' + ditemukan.stok;

                jumlahPinjam.max = ditemukan.stok;

            }

        });


        // Validasi sebelum form dikirim

        document.getElementById('formPeminjaman').addEventListener('submit', function (event) {

            if (!idUser.value) {

                event.preventDefault();

                alert('Username peminjam tidak ditemukan. Pilih atau ketik username yang terdaftar.');

                namaPeminjam.focus();

                return;
            }

            if (!idAlat.value) {

                event.preventDefault();

                alert('Nama alat tidak ditemukan. Pilih atau ketik nama alat yang terdaftar.');

                namaAlat.focus();

                return;
            }

            const alatDipilih = dataAlat.find(alat => alat.id === idAlat.value);

            if (!alatDipilih || alatDipilih.stok <= 0) {

                event.preventDefault();

                alert('Stok alat sedang kosong.');

                return;
            }

            if (parseInt(jumlahPinjam.value, 10) > alatDipilih.stok) {

                event.preventDefault();

                alert('Jumlah pinjam melebihi stok yang tersedia.');

                jumlahPinjam.focus();

                return;
            }

        });

    </script>

</body>
</html>
