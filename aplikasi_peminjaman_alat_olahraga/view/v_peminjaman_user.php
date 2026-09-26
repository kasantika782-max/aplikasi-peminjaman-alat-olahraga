<?php 

if (session_status() === PHP_SESSION_NONE) {
    session_start();
require_once __DIR__ . '/../model/m_security.php';
$csrf_token = csrf_token();
}

// 1. Cek login
if (!isset($_SESSION['role'])) {
    header("Location: v_login.php");
    exit();
}

// 2. Cek role
if ($_SESSION['role'] !== 'peminjam') {

    if ($_SESSION['role'] === 'admin') {
        header("Location: v_tampilan_user.php");
    } elseif ($_SESSION['role'] === 'petugas') {
        header("Location: v_peminjaman_petugas.php");
    }

    exit();
}

include '../controller/c_peminjaman.php';

?>

<!DOCTYPE html>

<html lang="id">

<head>


<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Riwayat Peminjaman</title>

<!-- Tailwind CSS -->
<script src="https://cdn.tailwindcss.com"></script>

<!-- Font Awesome -->
<link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


</head>

<body class="bg-slate-100 min-h-screen p-4 md:p-8 font-sans">


<!-- CONTAINER -->
<div class="max-w-6xl mx-auto bg-white rounded-2xl shadow-xl p-6 md:p-8 border border-slate-200">


    <!-- HEADER -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 pb-5 border-b border-slate-200 gap-4">


        <!-- JUDUL -->
        <div>

            <h1 class="text-2xl md:text-3xl font-extrabold text-purple-900 tracking-tight flex items-center gap-2">

                <i class="fa-solid fa-clock-rotate-left text-purple-600"></i>

                Riwayat Peminjaman

            </h1>


            <p class="text-slate-500 text-sm mt-2">

                Pengguna Aktif:

                <span class="font-semibold text-purple-700">

                    <?= htmlspecialchars($_SESSION['username']); ?>

                </span>

            </p>

        </div>


        <!-- BUTTON -->
        <div class="flex items-center gap-3">

            <a href="v_daftar_alat.php"
               class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium text-sm rounded-xl shadow-sm transition duration-150 flex items-center gap-2">

                <i class="fa-solid fa-plus"></i>

                Pinjam Alat

            </a>


            <button
                type="button"
                onclick="konfirmasiLogout()"

                class="px-4 py-2 bg-rose-500 hover:bg-rose-600 text-white font-medium text-sm rounded-xl shadow-sm transition duration-150 flex items-center gap-2">

                <i class="fa-solid fa-right-from-bracket"></i>

                Logout

            </button>

        </div>

    </div>



    <!-- INFO STATUS -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">


        <div class="bg-amber-50 border border-amber-200 rounded-xl p-3">

            <p class="text-xs text-amber-700 font-semibold">

                <i class="fa-solid fa-clock"></i>

                Pending

            </p>

            <p class="text-xs text-slate-500 mt-1">

                Menunggu persetujuan

            </p>

        </div>


        <div class="bg-sky-50 border border-sky-200 rounded-xl p-3">

            <p class="text-xs text-sky-700 font-semibold">

                <i class="fa-solid fa-hand-holding"></i>

                Dipinjam

            </p>

            <p class="text-xs text-slate-500 mt-1">

                Sedang dipinjam

            </p>

        </div>


        <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-3">

            <p class="text-xs text-emerald-700 font-semibold">

                <i class="fa-solid fa-circle-check"></i>

                Kembali

            </p>

            <p class="text-xs text-slate-500 mt-1">

                Sudah dikembalikan

            </p>

        </div>


        <div class="bg-rose-50 border border-rose-200 rounded-xl p-3">

            <p class="text-xs text-rose-700 font-semibold">

                <i class="fa-solid fa-circle-xmark"></i>

                Dibatalkan

            </p>

            <p class="text-xs text-slate-500 mt-1">

                Peminjaman tidak jadi

            </p>

        </div>


    </div>



    <!-- TABEL -->
    <div class="overflow-x-auto rounded-xl border border-slate-200 shadow-sm">


        <table class="w-full text-left border-collapse">


            <thead>

                <tr class="bg-purple-900 text-white text-xs font-semibold uppercase tracking-wider">

                    <th class="py-4 px-4 text-center">

                        No

                    </th>


                    <th class="py-4 px-4">

                        Nama Alat

                    </th>


                    <th class="py-4 px-4 text-center">

                        Tanggal Pinjam

                    </th>


                    <th class="py-4 px-4 text-center">

                        Deadline

                    </th>


                    <th class="py-4 px-4 text-center">

                        Status

                    </th>


                </tr>

            </thead>



            <tbody class="divide-y divide-slate-200 text-slate-700 text-sm">


                <?php  

                $no = 1; 

                if (isset($isi_tabel_user) && mysqli_num_rows($isi_tabel_user) > 0): 

                    while($row = mysqli_fetch_object($isi_tabel_user)):  

                ?>


                <tr class="hover:bg-purple-50/50 transition duration-150">


                    <!-- NOMOR -->
                    <td class="py-4 px-4 text-center font-medium text-slate-500">

                        <?= $no++; ?>

                    </td>



                    <!-- NAMA ALAT -->
                    <td class="py-4 px-4 font-bold text-slate-800">

                        <i class="fa-solid fa-box text-purple-500 mr-2"></i>

                        <?= htmlspecialchars($row->nama_alat); ?>

                    </td>



                    <!-- TANGGAL PINJAM -->
                    <td class="py-4 px-4 text-center whitespace-nowrap">

                        <i class="fa-solid fa-calendar-days text-slate-400 mr-1"></i>

                        <?= date('d-m-Y', strtotime($row->tgl_pinjam)); ?>

                    </td>



                    <!-- DEADLINE -->
                    <td class="py-4 px-4 text-center whitespace-nowrap">


                        <?php if (!empty($row->deadline)): ?>


                            <span class="text-rose-600 font-semibold">

                                <i class="fa-solid fa-calendar-xmark mr-1"></i>

                                <?= date('d-m-Y', strtotime($row->deadline)); ?>

                            </span>


                        <?php else: ?>


                            <span class="text-slate-400">

                                Belum ditentukan

                            </span>


                        <?php endif; ?>


                    </td>



                    <!-- STATUS -->
                    <td class="py-4 px-4 text-center whitespace-nowrap">


                        <?php  

                        $badge_bg = 'bg-amber-100 text-amber-800 border-amber-300';

                        $icon_status = 'fa-clock';


                        // DIPINJAM
                        if ($row->status == 'dipinjam') {

                            $badge_bg = 'bg-sky-100 text-sky-800 border-sky-300';

                            $icon_status = 'fa-hand-holding';

                        }


                        // KEMBALI
                        elseif ($row->status == 'kembali') {

                            $badge_bg = 'bg-emerald-100 text-emerald-800 border-emerald-300';

                            $icon_status = 'fa-circle-check';

                        }


                        // DIBATALKAN
                        elseif (
                            $row->status == 'dibatalkan'
                            || $row->status == 'ditolak'
                        ) {

                            $badge_bg = 'bg-rose-100 text-rose-800 border-rose-300';

                            $icon_status = 'fa-circle-xmark';

                        }

                        ?>


                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-md text-xs font-bold border uppercase tracking-wider <?= $badge_bg; ?>">


                            <i class="fa-solid <?= $icon_status; ?>"></i>


                            <?= strtoupper($row->status); ?>


                        </span>


                    </td>


                </tr>


                <?php  

                    endwhile;  

                else:  

                ?>


                <tr>


                    <td colspan="5" class="py-10 text-center text-slate-500 font-medium">


                        <i class="fa-solid fa-folder-open text-4xl text-slate-300 block mb-3"></i>


                        Belum ada riwayat peminjaman.


                    </td>


                </tr>


                <?php endif; ?>


            </tbody>


        </table>


    </div>


</div>



<!-- LOGOUT -->

<script>

    function konfirmasiLogout() {

        Swal.fire({

            title: 'Konfirmasi sesi',

            text: 'Apakah Anda yakin ingin keluar dari sistem?',

            icon: 'warning',

            showCancelButton: true,

            confirmButtonColor: '#f43f5e',

            cancelButtonColor: '#6b7280',

            confirmButtonText: 'Ya, Logout',

            cancelButtonText: 'Batal',

            reverseButtons: true

        }).then((result) => {

            if (result.isConfirmed) {

                postAction('../controller/c_login.php?aksi=logout');

            }

        });

    }

</script>


</body>

</html>
