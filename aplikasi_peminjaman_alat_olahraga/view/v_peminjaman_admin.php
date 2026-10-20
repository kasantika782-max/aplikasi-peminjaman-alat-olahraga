<?php 

if (session_status() === PHP_SESSION_NONE) { session_start();
require_once __DIR__ . '/../model/m_security.php';
$csrf_token = csrf_token(); }

// 1. Cek apakah sudah login?
if (!isset($_SESSION['role'])) {
    header("Location: v_login.php");
    exit();
}

// 2. Cek Role Admin
if ($_SESSION['role'] !== 'admin') {
    if ($_SESSION['role'] === 'petugas') {
        header("Location: v_peminjaman_petugas.php");
    } else {
        header("Location: v_daftar_alat.php");
    }
    exit();
}

include '../controller/c_peminjaman.php'; 

$tipe_halaman = $_GET['tipe'] ?? 'pinjam'; // Default ke pinjam
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Transaksi - Admin</title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
    </style>
</head>

<body class="bg-slate-100 text-slate-800 antialiased">

<div class="flex min-h-screen">

    <!-- ================= SIDEBAR KIRI ================= -->
    <aside class="w-64 bg-indigo-900 text-white flex flex-col justify-between p-5 shadow-xl">

        <div>

            <!-- Logo & Judul -->
            <div class="flex items-center gap-3 px-2 py-4 border-b border-indigo-800/60 mb-6">

                <div class="bg-indigo-500 text-white p-2.5 rounded-xl shadow-lg">
                    <i class="fa-solid fa-boxes-packing text-xl"></i>
                </div>

                <div>
                    <h1 class="font-bold text-lg leading-tight">
                        Peminjaman
                    </h1>

                    <span class="text-xs text-indigo-300">
                        Admin Dashboard
                    </span>
                </div>

            </div>

            <!-- Menu Navigasi -->
            <nav class="space-y-1.5">

                <a href="v_tampilan_user.php"
                   class="flex items-center gap-3 px-4 py-3 rounded-xl text-indigo-200 hover:bg-indigo-800/50 hover:text-white font-medium transition">

                    <i class="fa-solid fa-users w-5"></i>
                    Data Pengguna

                </a>

                <a href="v_kategori.php"
                   class="flex items-center gap-3 px-4 py-3 rounded-xl text-indigo-200 hover:bg-indigo-800/50 hover:text-white font-medium transition">

                    <i class="fa-solid fa-layer-group w-5"></i>
                    Kategori

                </a>

                <a href="v_alat.php"
                   class="flex items-center gap-3 px-4 py-3 rounded-xl text-indigo-200 hover:bg-indigo-800/50 hover:text-white font-medium transition">

                    <i class="fa-solid fa-toolbox w-5"></i>
                    Data Alat

                </a>

                <a href="v_peminjaman_admin.php?tipe=pinjam"
                   class="flex items-center gap-3 px-4 py-3 rounded-xl bg-indigo-800 text-white font-medium shadow-sm transition">

                    <i class="fa-solid fa-arrow-right-arrow-left w-5"></i>
                    Peminjaman

                </a>

                <a href="v_log_aktivitas.php"
                   class="flex items-center gap-3 px-4 py-3 rounded-xl text-indigo-200 hover:bg-indigo-800/50 hover:text-white font-medium transition">

                    <i class="fa-solid fa-clock-rotate-left w-5"></i>
                    Log Aktivitas

                </a>

            </nav>

        </div>

        <!-- Info Panel -->
        <div class="bg-indigo-800/60 p-4 rounded-2xl border border-indigo-700/50 text-center">

            <i class="fa-solid fa-receipt text-indigo-300 text-2xl mb-2"></i>

            <h4 class="text-sm font-semibold mb-1">
                Transaksi Aktif
            </h4>

            <p class="text-xs text-indigo-300 mb-1">
                Pantau seluruh riwayat peminjaman & pengembalian barang.
            </p>

        </div>

    </aside>


    <!-- ================= KONTEN UTAMA ================= -->

    <main class="flex-1 p-8 overflow-y-auto">

        <!-- Top Header -->
        <header class="flex justify-between items-center mb-6">

            <div>

                <h2 class="text-2xl font-bold text-slate-800">

                    Kelola Transaksi
                    <?= ($tipe_halaman == 'kembali')
                        ? 'Pengembalian'
                        : 'Peminjaman'; ?>

                </h2>

                <p class="text-sm text-slate-500">
                    Monitor dan perbarui status transaksi alat.
                </p>

            </div>


            <!-- Profile & Logout -->

            <div class="flex items-center gap-4 bg-white px-4 py-2 rounded-2xl shadow-sm border border-slate-200/80">

                <div class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold text-lg">

                    <?= strtoupper(
                        substr(
                            $_SESSION['role'] ?? 'A',
                            0,
                            1
                        )
                    ); ?>

                </div>

                <div>

                    <p class="text-sm font-semibold capitalize text-slate-800">
                        <?= htmlspecialchars($_SESSION['role'] ?? 'Admin'); ?>
                    </p>

                    <span class="text-xs text-emerald-500 font-medium">
                        ● Online
                    </span>

                </div>

                <button
                    type="button"
                    onclick="konfirmasiLogout()"
                    class="ml-2 text-rose-500 hover:bg-rose-50 p-2 rounded-lg transition"
                    title="Logout">

                    <i class="fa-solid fa-right-from-bracket text-lg"></i>

                </button>

            </div>

        </header>


        <!-- ================= TAB & ACTION ================= -->

        <div class="flex flex-wrap justify-between items-center gap-4 mb-6">

            <div class="flex items-center gap-3 flex-wrap">

                <!-- Tab -->

                <div class="flex bg-slate-200/80 p-1.5 rounded-2xl gap-1">

                    <a href="v_peminjaman_admin.php?tipe=pinjam" 
   class="px-5 py-2.5 rounded-xl font-semibold text-xs transition flex items-center gap-2 <?= ($tipe_halaman != 'kembali') ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-600 hover:text-slate-900'; ?>">
    <i class="fa-solid fa-hand-holding"></i> Daftar Pengambilan
</a>



                    <a
                        href="v_peminjaman_admin.php?tipe=kembali"
                        class="px-5 py-2.5 rounded-xl font-semibold text-xs transition flex items-center gap-2
                        <?= ($tipe_halaman == 'kembali')
                            ? 'bg-white text-indigo-600 shadow-sm'
                            : 'text-slate-600 hover:text-slate-900'; ?>">

                        <i class="fa-solid fa-box-archive"></i>

                        Daftar Pengembalian

                    </a>

                </div>


                <!-- Tambah Peminjaman -->

                <?php if ($tipe_halaman != 'kembali'): ?>

                    <a
                        href="v_alat.php"
                        class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs rounded-2xl shadow-sm transition flex items-center gap-2">

                        <i class="fa-solid fa-plus text-xs"></i>

                        <span>
                            Pinjam Alat
                        </span>

                    </a>

                <?php endif; ?>

            </div>


            <!-- Tombol Dashboard -->

            <a
                href="../view/v_tampilan_user.php"
                class="px-4 py-2 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 font-medium text-xs rounded-xl shadow-sm transition flex items-center gap-2">

                <i class="fa-solid fa-arrow-left"></i>

                Kembali ke Dashboard

            </a>

        </div>


        <!-- ================= TABEL DATA ================= -->

        <section class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">

            <!-- Header Tabel -->

            <div class="p-5 border-b border-slate-100 flex flex-col md:flex-row justify-between items-center gap-4">

                <h3 class="font-bold text-slate-800">

                    Data
                    <?= ($tipe_halaman == 'kembali')
                        ? 'Pengembalian'
                        : 'Peminjaman'; ?>

                    Alat

                </h3>


                <!-- Search -->

                <div class="relative w-full md:w-64">

                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>

                    <input
                        type="text"
                        id="searchInput"
                        onkeyup="filterTable()"
                        placeholder="Cari peminjam / alat..."
                        class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-indigo-500 focus:bg-white transition">

                </div>

            </div>


            <!-- Tabel -->

            <div class="overflow-x-auto">

                <table
                    class="w-full text-left border-collapse"
                    id="dataTable">

                    <thead>

                        <tr class="bg-slate-50 border-b border-slate-100 text-slate-400 text-xs uppercase font-semibold">

                            <th class="p-4 pl-6 text-center">
                                No
                            </th>

                            <th class="p-4">
                                Peminjam
                            </th>

                            <th class="p-4">
                                Alat & Kategori
                            </th>

                            <th class="p-4 text-center">
                                Jumlah
                            </th>


                            <?php if ($tipe_halaman == 'kembali'): ?>

                                <th class="p-4 text-center">
                                    Kondisi Masuk
                                </th>

                                <th class="p-4 text-center">
                                    Tgl Kembali
                                </th>

                            <?php else: ?>

                                <th class="p-4 text-center">
                                    Tgl Pinjam
                                </th>

                            <?php endif; ?>


                            <th class="p-4 text-center">
                                Status
                            </th>

                            <th class="p-4 text-center pr-6">
                                Aksi
                            </th>

                        </tr>

                    </thead>


                    <tbody class="divide-y divide-slate-100 text-sm">

                    <?php

                    $no = 1;

                    if (
                        $isi_tabel &&
                        mysqli_num_rows($isi_tabel) > 0
                    ):

                        while (
                            $row = mysqli_fetch_object($isi_tabel)
                        ):

                            $nama_user =
                                !empty($row->username)
                                ? $row->username
                                : ($row->nama_peminjam ?? 'User');

                            $st = strtolower(
                                trim($row->status)
                            );

                    ?>

                        <tr class="hover:bg-slate-50/80 transition search-row">


                            <!-- NOMOR -->

                            <td class="p-4 pl-6 text-center font-medium text-slate-500">

                                <?= $no++; ?>

                            </td>


                            <!-- PEMINJAM -->

                            <td class="p-4 font-semibold text-slate-800">

                                <div class="flex items-center gap-3">

                                    <div class="w-8 h-8 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center font-bold text-xs border">

                                        <?= strtoupper(
                                            substr(
                                                $nama_user,
                                                0,
                                                1
                                            )
                                        ); ?>

                                    </div>

                                    <span class="search-peminjam">

                                        <?= htmlspecialchars(
                                            $nama_user,
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ); ?>

                                    </span>

                                </div>

                            </td>


                            <!-- ALAT -->

                            <td class="p-4">

                                <p class="font-bold text-slate-800 mb-0.5 search-alat">

                                    <?= htmlspecialchars(
                                        $row->nama_alat,
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ); ?>

                                </p>

                                <span class="text-xs text-slate-400 font-medium">

                                    <?= htmlspecialchars(
                                        $row->nama_kategori ?? 'Umum',
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ); ?>

                                </span>

                            </td>


                            <!-- JUMLAH -->

                            <td class="p-4 text-center font-semibold text-slate-700">

                                <?= $row->jumlah_pinjam; ?> unit

                            </td>


                            <!-- DATA KEMBALI / PINJAM -->

                            <?php if ($tipe_halaman == 'kembali'): ?>

                                <!-- KONDISI -->

                                <td class="p-4 text-center">

                                    <?php

                                    $k_masuk =
                                        $row->kondisi_masuk ?? 'Baik';

                                    $bg_k =
                                        ($k_masuk == 'Baik')
                                        ? 'bg-emerald-50 text-emerald-600 border-emerald-200'
                                        : 'bg-amber-50 text-amber-600 border-amber-200';

                                    ?>

                                    <span
                                        class="px-2.5 py-1 rounded-lg text-xs font-semibold border <?= $bg_k; ?>">

                                        <?= htmlspecialchars(
                                            $k_masuk,
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ); ?>

                                    </span>

                                </td>


                                <!-- TGL KEMBALI -->

                                <td class="p-4 text-center text-xs text-slate-500 font-medium">

                                    <?= !empty($row->tgl_kembali_asli)
                                        ? date(
                                            'd/m/Y H:i',
                                            strtotime(
                                                $row->tgl_kembali_asli
                                            )
                                        )
                                        : '-'; ?>

                                </td>

                            <?php else: ?>

                                <!-- TGL PINJAM -->

                                <td class="p-4 text-center text-xs text-slate-500 font-medium">

                                    <?= !empty($row->tgl_pinjam)
                                        ? date(
                                            'd/m/Y',
                                            strtotime(
                                                $row->tgl_pinjam
                                            )
                                        )
                                        : '-'; ?>

                                </td>

                            <?php endif; ?>


                            <!-- STATUS -->

                            <td class="p-4 text-center">

                                <?php if ($st == 'kembali'): ?>

                                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700 border border-emerald-200">

                                        KEMBALI

                                    </span>

                                <?php elseif ($st == 'dipinjam'): ?>

                                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-700 border border-blue-200">

                                        DIPINJAM

                                    </span>

                                <?php elseif ($st == 'pending'): ?>

                                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-700 border border-amber-200">

                                        PENDING

                                    </span>

                                <?php else: ?>

                                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200">

                                        <?= strtoupper(
                                            htmlspecialchars(
                                                $row->status,
                                                ENT_QUOTES,
                                                'UTF-8'
                                            )
                                        ); ?>

                                    </span>

                                <?php endif; ?>

                            </td>


                            <!-- ================= AKSI ================= -->

                            <td class="p-4 text-center pr-6">

                                <div class="flex justify-center items-center gap-2 flex-wrap">

                                    <?php

                                    // Link Edit
                                    if ($tipe_halaman == 'kembali') {

                                        $link_edit =
                                            "v_update_kembali.php?aksi=edit_kembali&id="
                                            . $row->id_peminjaman;

                                    } else {

                                        $link_edit =
                                            "v_update_pinjam.php?aksi=edit_pinjam&id="
                                            . $row->id_peminjaman;

                                    }


                                    // Link Hapus
                                    $link_hapus =
                                        "../controller/c_peminjaman.php?aksi=hapus&id="
                                        . $row->id_peminjaman
                                        . "&tipe="
                                        . urlencode($tipe_halaman);

                                    ?>


                                    <!-- ================= TOMBOL KEMBALI ================= -->

                                    <?php if ($st == 'dipinjam'): ?>

                                        <button
                                            type="button"
                                            onclick="konfirmasiKembali(
                                                '<?= $row->id_peminjaman; ?>',
                                                '<?= htmlspecialchars(
                                                    $row->nama_alat,
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ); ?>'
                                            )"
                                            class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-medium text-xs rounded-lg shadow-sm transition flex items-center gap-1">

                                            <i class="fa-solid fa-rotate-left"></i>

                                            Kembali

                                        </button>

                                    <?php endif; ?>


                                    <!-- ================= TOMBOL EDIT ================= -->

                                    <a
                                        href="<?= htmlspecialchars(
                                            $link_edit,
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ); ?>"
                                        class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white font-medium text-xs rounded-lg shadow-sm transition flex items-center gap-1">

                                        <i class="fa-solid fa-pen-to-square"></i>

                                        Edit

                                    </a>


                                    <!-- ================= TOMBOL HAPUS ================= -->

                                    <button
                                        type="button"
                                        onclick="konfirmasiHapus(
                                            '<?= htmlspecialchars(
                                                $link_hapus,
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ); ?>'
                                        )"
                                        class="px-3 py-1.5 bg-rose-500 hover:bg-rose-600 text-white font-medium text-xs rounded-lg shadow-sm transition flex items-center gap-1">

                                        <i class="fa-solid fa-trash"></i>

                                        Hapus

                                    </button>

                                </div>

                            </td>

                        </tr>


                    <?php

                        endwhile;

                    else:

                    ?>

                        <tr>

                            <td
                                colspan="7"
                                class="p-8 text-center text-slate-400">

                                <i class="fa-solid fa-inbox text-3xl mb-2"></i>

                                <p>
                                    Tidak ada data
                                    <strong>
                                        <?= htmlspecialchars(
                                            $tipe_halaman,
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ); ?>
                                    </strong>
                                    untuk ditampilkan saat ini.
                                </p>

                            </td>

                        </tr>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </section>

    </main>

</div>


<!-- ================= JAVASCRIPT ================= -->

<script>

    // =====================================================
    // CUSTOM SWEETALERT
    // =====================================================

    const swalTailwind = Swal.mixin({

        customClass: {

            confirmButton:
                'bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-5 py-2.5 rounded-xl text-sm shadow-md transition mx-1',

            cancelButton:
                'bg-slate-200 hover:bg-slate-300 text-slate-700 font-semibold px-5 py-2.5 rounded-xl text-sm transition mx-1'

        },

        buttonsStyling: false

    });


    // =====================================================
    // NOTIFIKASI SUKSES / GAGAL
    // =====================================================

    <?php if (isset($_GET['pesan'])): ?>

        <?php

        $pesan = $_GET['pesan'];

        $judul = "Berhasil!";
        $teks = "";
        $icon = "success";

        switch ($pesan) {

            case 'sukses_tambah':

                $teks =
                    "Data peminjaman baru berhasil ditambahkan.";

                break;


            case 'sukses_update':

                $teks =
                    "Data peminjaman berhasil diperbarui.";

                break;


            case 'sukses_kembali':

                $teks =
                    "Data pengembalian berhasil diperbarui.";

                break;


            case 'sukses_hapus':

                $teks =
                    "Data peminjaman berhasil dihapus oleh Admin.";

                break;


            case 'stok_kurang':

                $judul = "Gagal!";

                $teks =
                    "Stok alat yang tersedia tidak mencukupi.";

                $icon = "error";

                break;


            default:

                $judul = "Gagal!";

                $teks =
                    "Terjadi kesalahan saat memproses data.";

                $icon = "error";

                break;

        }

        ?>

        swalTailwind.fire({

            title:
                '<?= htmlspecialchars(
                    $judul,
                    ENT_QUOTES,
                    'UTF-8'
                ); ?>',

            text:
                '<?= htmlspecialchars(
                    $teks,
                    ENT_QUOTES,
                    'UTF-8'
                ); ?>',

            icon:
                '<?= $icon; ?>',

            confirmButtonText:
                'OK'

        });

    <?php endif; ?>


    // =====================================================
    // LOGOUT
    // =====================================================

    function konfirmasiLogout() {

        Swal.fire({

            title:
                'Konfirmasi sesi',

            text:
                'Apakah Anda yakin ingin keluar dari sistem?',

            icon:
                'warning',

            showCancelButton:
                true,

            confirmButtonColor:
                '#f43f5e',

            cancelButtonColor:
                '#6b7280',

            confirmButtonText:
                '<i class="fa-solid fa-right-from-bracket mr-1"></i> Ya, Logout',

            cancelButtonText:
                'Batal',

            reverseButtons:
                true,

            customClass: {

                popup:
                    'rounded-2xl shadow-xl',

                confirmButton:
                    'px-4 py-2 rounded-xl text-sm font-semibold',

                cancelButton:
                    'px-4 py-2 rounded-xl text-sm font-semibold'

            }

        }).then((result) => {

            if (result.isConfirmed) {

                postAction('../controller/c_login.php?aksi=logout');

            }

        });

    }


    // =====================================================
    // KONFIRMASI PENGEMBALIAN
    // =====================================================

    function konfirmasiKembali(id, namaAlat) {

        swalTailwind.fire({

            title:
                'Konfirmasi Pengembalian',

            html:
                `Konfirmasi bahwa alat <b>"${namaAlat}"</b> sudah dikembalikan?`,

            icon:
                'question',

            showCancelButton:
                true,

            confirmButtonText:
                '<i class="fa-solid fa-rotate-left mr-1"></i> Ya, Sudah Kembali',

            cancelButtonText:
                'Batal',

            reverseButtons:
                true

        }).then((result) => {

            if (result.isConfirmed) {

                postAction(`../controller/c_peminjaman.php?aksi=konfirmasi_kembali&id=${id}`);

            }

        });

    }


    // =====================================================
    // KONFIRMASI HAPUS
    // =====================================================

    function konfirmasiHapus(urlHapus) {

        swalTailwind.fire({

            title:
                'Apakah Anda yakin?',

            text:
                'Data transaksi peminjaman ini akan dihapus secara permanen!',

            icon:
                'warning',

            showCancelButton:
                true,

            confirmButtonText:
                'Ya, Hapus!',

            cancelButtonText:
                'Batal',

            reverseButtons:
                true

        }).then((result) => {

            if (result.isConfirmed) {

                postAction(urlHapus);

            }

        });

    }


    // =====================================================
    // SEARCH / FILTER
    // =====================================================

    function filterTable() {

        const input =
            document
            .getElementById('searchInput')
            .value
            .toLowerCase();

        const rows =
            document.querySelectorAll('.search-row');


        rows.forEach(row => {

            const peminjam =
                row
                .querySelector('.search-peminjam')
                ?.textContent
                .toLowerCase() || '';


            const alat =
                row
                .querySelector('.search-alat')
                ?.textContent
                .toLowerCase() || '';


            if (
                peminjam.includes(input) ||
                alat.includes(input)
            ) {

                row.style.display = '';

            } else {

                row.style.display = 'none';

            }

        });

    }

</script>


<script>
function postAction(url) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = url;
    const token = document.createElement('input');
    token.type = 'hidden'; token.name = 'csrf_token';
    token.value = <?= json_encode($csrf_token ?? csrf_token()) ?>;
    form.appendChild(token); document.body.appendChild(form); form.submit();
}
</script>

</body>
</html>
