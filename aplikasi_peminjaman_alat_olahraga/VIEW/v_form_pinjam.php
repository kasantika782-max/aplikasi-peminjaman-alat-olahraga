<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


// CEK LOGIN
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'peminjam') {

    header("Location: v_login.php");

    exit();

}


require_once '../model/m_koneksi.php';


$db = (new m_koneksi())->koneksi;


// CEK ID ALAT
if (!isset($_GET['id_alat'])) {

    header("Location: v_daftar_alat.php");

    exit();

}


$id_alat = (int)$_GET['id_alat'];


// AMBIL DATA ALAT
$query = mysqli_query(

    $db,

    "SELECT a.*, k.nama_kategori
     FROM alat a
     LEFT JOIN kategori k ON a.id_kategori = k.id_kategori
     WHERE a.id_alat = '$id_alat'"

);


$data = mysqli_fetch_object($query);


// JIKA ALAT TIDAK ADA
if (!$data) {

    header("Location: v_daftar_alat.php");

    exit();

}


// JIKA STOK HABIS
if ($data->stok <= 0) {

    header("Location: v_daftar_alat.php");

    exit();

}

?>

<!DOCTYPE html>

<html lang="id">

<head>

```
<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Form Pinjam Alat</title>

<script src="https://cdn.tailwindcss.com"></script>

<link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
```

</head>

<body class="bg-slate-100 min-h-screen p-4 md:p-8">

<div class="max-w-2xl mx-auto">

```
<!-- KEMBALI -->

<a href="v_daftar_alat.php"

    class="inline-flex items-center gap-2 text-purple-700 font-semibold mb-6">

    <i class="fa-solid fa-arrow-left"></i>

    Kembali ke Daftar Alat

</a>



<!-- FORM -->

<div class="bg-white rounded-2xl shadow-xl border border-slate-200 p-6 md:p-8">


    <div class="border-b border-slate-200 pb-5 mb-6">

        <h1 class="text-2xl font-extrabold text-purple-900">

            <i class="fa-solid fa-hand-holding text-purple-600 mr-2"></i>

            Form Peminjaman

        </h1>

        <p class="text-sm text-slate-500 mt-2">

            Isi jumlah alat yang ingin kamu pinjam.

        </p>

    </div>



    <!-- INFORMASI ALAT -->

    <div class="bg-purple-50 border border-purple-200 rounded-xl p-5 mb-6">


        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">


            <div>

                <p class="text-xs text-slate-500">

                    Nama Alat

                </p>

                <p class="font-bold text-slate-800 text-lg">

                    <?= htmlspecialchars($data->nama_alat); ?>

                </p>

            </div>



            <div>

                <p class="text-xs text-slate-500">

                    Kategori

                </p>

                <p class="font-semibold text-slate-700">

                    <?= htmlspecialchars($data->nama_kategori); ?>

                </p>

            </div>



            <div>

                <p class="text-xs text-slate-500">

                    Stok Tersedia

                </p>

                <p class="font-bold text-emerald-600">

                    <?= $data->stok; ?> Alat

                </p>

            </div>


            <div>

                <p class="text-xs text-slate-500">

                    Status Pengajuan

                </p>

                <span class="inline-block mt-1 px-3 py-1 bg-amber-100 text-amber-700 rounded-lg text-xs font-bold">

                    Akan menjadi PENDING

                </span>

            </div>


        </div>

    </div>



    <!-- FORM -->

    <form

        action="../controller/c_peminjaman.php?aksi=proses_pinjam"

        method="POST"


        onsubmit="return konfirmasiPinjam()"


    >


        <!-- ID ALAT -->

        <input

            type="hidden"

            name="id_alat"

            value="<?= $data->id_alat; ?>"

        >



        <!-- JUMLAH -->

        <div class="mb-5">


            <label class="block text-sm font-bold text-slate-700 mb-2">

                Jumlah Pinjam

            </label>


            <input

                type="number"

                name="jumlah_pinjam"

                min="1"

                max="<?= $data->stok; ?>"

                required

                placeholder="Masukkan jumlah alat"


                class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-400"

            >


            <p class="text-xs text-slate-500 mt-2">

                Maksimal <?= $data->stok; ?> alat.

            </p>


        </div>



        <!-- KONDISI -->

        <input

            type="hidden"

            name="kondisi_keluar"

            value="Baik"

        >



        <!-- INFO -->

        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-6">

            <p class="text-sm text-amber-800">

                <i class="fa-solid fa-circle-info mr-1"></i>

                Setelah diajukan, peminjaman akan menunggu persetujuan petugas.

            </p>

        </div>



        <!-- BUTTON -->

        <button

            type="submit"

            class="w-full py-3 bg-purple-700 hover:bg-purple-800 text-white font-bold rounded-xl shadow-lg transition"

        >

            <i class="fa-solid fa-paper-plane mr-2"></i>

            Ajukan Peminjaman

        </button>


    </form>


</div>
```

</div>

<script>

function konfirmasiPinjam() {

    event.preventDefault();


    Swal.fire({

        title: 'Ajukan Peminjaman?',

        text: 'Pengajuan akan dikirim ke petugas untuk disetujui.',

        icon: 'question',

        showCancelButton: true,

        confirmButtonColor: '#7e22ce',

        cancelButtonColor: '#64748b',

        confirmButtonText: 'Ya, Ajukan',

        cancelButtonText: 'Batal'

    }).then((result) => {

        if (result.isConfirmed) {

            document.querySelector('form').submit();

        }

    });


    return false;

}

</script>

</body>

</html>
