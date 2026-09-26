<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
require_once __DIR__ . '/../model/m_security.php';
$csrf_token = csrf_token();
}

// CEK LOGIN
if (!isset($_SESSION['role'])) {
    header("Location: v_login.php");
    exit();
}

// CEK ROLE
if ($_SESSION['role'] !== 'peminjam') {

    if ($_SESSION['role'] === 'admin') {
        header("Location: v_tampilan_user.php");
    } elseif ($_SESSION['role'] === 'petugas') {
        header("Location: v_peminjaman_petugas.php");
    }

    exit();
}

require_once '../model/m_koneksi.php';
require_once '../model/m_alat.php';

$db = (new m_koneksi())->koneksi;

$alat_model = new m_alat($db);
$data_alat = $alat_model->tampil_data();
?>

<!DOCTYPE html>

<html lang="id">

<head>


<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Pinjam Alat</title>

<script src="https://cdn.tailwindcss.com"></script>

<link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
    rel="stylesheet">

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    body {
        font-family: 'Plus Jakarta Sans', sans-serif;
    }
</style>


</head>

<body class="bg-slate-100 min-h-screen p-4 md:p-8">

<div class="max-w-6xl mx-auto bg-white rounded-2xl shadow-xl p-6 md:p-8 border border-slate-200">


<!-- HEADER -->

<div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 pb-5 border-b border-slate-200 gap-4">

    <div>

        <h1 class="text-2xl md:text-3xl font-extrabold text-purple-900 flex items-center gap-2">

            <i class="fa-solid fa-hand-holding text-purple-600"></i>

            Pinjam Alat

        </h1>

        <p class="text-slate-500 text-sm mt-1">

            Pilih alat olahraga yang ingin kamu pinjam

        </p>

    </div>


    <div class="flex items-center gap-3">

        <a href="v_peminjaman_user.php"
            class="px-4 py-2 bg-purple-700 hover:bg-purple-800 text-white font-medium text-sm rounded-xl shadow-sm transition flex items-center gap-2">

            <i class="fa-solid fa-clock-rotate-left"></i>

            Riwayat Saya

        </a>


        <button
            type="button"
            onclick="konfirmasiLogout()"

            class="px-4 py-2 bg-rose-500 hover:bg-rose-600 text-white font-medium text-sm rounded-xl shadow-sm transition flex items-center gap-2">

            <i class="fa-solid fa-right-from-bracket"></i>

            Logout

        </button>

    </div>

</div>



<!-- SEARCH -->

<div class="mb-5">

    <div class="relative w-full md:w-80">

        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>

        <input
            type="text"
            id="searchInput"
            onkeyup="filterTable()"
            placeholder="Cari alat atau kategori..."

            class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-purple-500">

    </div>

</div>



<!-- TABEL -->

<div class="overflow-x-auto rounded-xl border border-slate-200 shadow-sm">

    <table class="w-full text-left border-collapse">

        <thead>

            <tr class="bg-purple-900 text-white text-xs font-semibold uppercase">

                <th class="py-4 px-4 text-center">No</th>

                <th class="py-4 px-4">Nama Alat</th>

                <th class="py-4 px-4">Jenis Alat</th>

                <th class="py-4 px-4 text-center">Stok</th>

                <th class="py-4 px-4 text-center">Aksi</th>

            </tr>

        </thead>


        <tbody class="divide-y divide-slate-200 text-sm">

            <?php

            $no = 1;

            if (!empty($data_alat)):

                foreach ($data_alat as $row):

                    $data = (object)$row;

            ?>

                <tr class="hover:bg-purple-50 transition search-row">

                    <td class="py-4 px-4 text-center text-slate-500">

                        <?= $no++; ?>

                    </td>


                    <td class="py-4 px-4 font-bold text-slate-800 search-nama">

                        <?= htmlspecialchars($data->nama_alat); ?>

                    </td>


                    <td class="py-4 px-4 text-slate-600 search-kategori">

                        <?= htmlspecialchars($data->nama_kategori); ?>

                    </td>


                    <!-- STOK -->

                    <td class="py-4 px-4 text-center">

                        <?php if ($data->stok <= 0): ?>

                            <span class="inline-block px-3 py-1 bg-rose-100 text-rose-700 rounded-lg text-xs font-bold">

                                Stok Habis

                            </span>

                        <?php else: ?>

                            <span class="inline-block px-3 py-1 bg-emerald-100 text-emerald-700 rounded-lg text-xs font-bold">

                                <?= $data->stok; ?> Tersedia

                            </span>

                        <?php endif; ?>

                    </td>


                    <!-- AKSI -->

                    <td class="py-4 px-4 text-center">

                        <?php if ($data->stok <= 0): ?>

                            <button
                                disabled
                                class="px-4 py-2 bg-slate-200 text-slate-400 text-xs font-semibold rounded-lg cursor-not-allowed">

                                <i class="fa-solid fa-ban mr-1"></i>

                                Stok Habis

                            </button>

                        <?php else: ?>

                            <a
                                href="v_form_pinjam.php?id_alat=<?= $data->id_alat; ?>"

                                class="inline-block px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-xs font-semibold rounded-lg shadow transition">

                                <i class="fa-solid fa-hand-holding mr-1"></i>

                                Pinjam Alat

                            </a>

                        <?php endif; ?>

                    </td>

                </tr>

            <?php

                endforeach;

            else:

            ?>

                <tr>

                    <td colspan="5" class="py-10 text-center text-slate-500">

                        Belum ada data alat.

                    </td>

                </tr>

            <?php endif; ?>

        </tbody>

    </table>

</div>


</div>

<script>

function filterTable() {

    const input = document
        .getElementById('searchInput')
        .value
        .toLowerCase();


    const rows = document.querySelectorAll('.search-row');


    rows.forEach(row => {

        const nama = row.querySelector('.search-nama').textContent.toLowerCase();

        const kategori = row.querySelector('.search-kategori').textContent.toLowerCase();


        if (
            nama.includes(input)
            ||
            kategori.includes(input)
        ) {

            row.style.display = '';

        } else {

            row.style.display = 'none';

        }

    });

}


function konfirmasiLogout() {

    Swal.fire({

        title: 'Logout?',

        text: 'Apakah kamu yakin ingin keluar?',

        icon: 'warning',

        showCancelButton: true,

        confirmButtonColor: '#e11d48',

        cancelButtonColor: '#64748b',

        confirmButtonText: 'Ya, Logout',

        cancelButtonText: 'Batal'

    }).then((result) => {

        if (result.isConfirmed) {

            postAction('../controller/c_login.php?aksi=logout');

        }

    });

}

</script>

</body>
</html>
