<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Cek Login & Hak Akses Admin
if (!isset($_SESSION['role'])) {
    header("Location: v_login.php");
    exit();
}

if ($_SESSION['role'] !== 'admin') {
    header("Location: v_daftar_alat.php");
    exit();
}

// 2. Sertakan koneksi dan model
require_once '../model/m_koneksi.php';
require_once '../model/m_alat.php';
require_once '../model/m_kategori.php';

$db = (new m_koneksi())->koneksi;

// Jika $data_alat belum diset oleh controller
if (!isset($data_alat)) {
    $id_alat = $_GET['id'] ?? '';

    $alat_model = new m_alat($db);
    $data_alat = $alat_model->tampil_data_by_id($id_alat);
}

// Jika $data_kategori belum diset
if (!isset($data_kategori)) {
    $kategori_model = new m_kategori($db);
    $data_kategori = $kategori_model->tampil_data();
}

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Data Alat</title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Font Awesome Icons -->
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-slate-900 min-h-screen flex items-center justify-center p-4 font-sans text-slate-100">

    <!-- Card Container -->
    <div
        class="w-full max-w-lg bg-slate-800 text-slate-100 rounded-2xl shadow-2xl border border-slate-700/50 p-8 sm:p-10 my-6">

        <!-- Header / Judul -->
        <div class="text-center mb-8">

            <h2 class="text-2xl font-extrabold tracking-wider text-white uppercase">
                Edit Data Alat
            </h2>

            <p class="text-slate-400 text-sm mt-1">
                Perbarui informasi barang/alat inventaris
            </p>

        </div>

        <!-- Form Edit Alat -->
        <form action="../controller/c_alat.php?aksi=update" method="POST" class="space-y-5">

            <!-- Hidden ID Alat -->
            <input
                type="hidden"
                name="id_alat"
                value="<?= htmlspecialchars($data_alat->id_alat ?? ''); ?>"
            >

            <!-- Input Nama Alat -->
            <div>

                <label
                    for="nama_alat"
                    class="block text-sm font-medium text-slate-300 mb-2"
                >
                    Nama Alat
                </label>

                <input
                    type="text"
                    id="nama_alat"
                    name="nama_alat"
                    value="<?= htmlspecialchars($data_alat->nama_alat ?? ''); ?>"
                    placeholder="Masukkan nama alat"
                    class="w-full px-4 py-3 rounded-lg bg-slate-900/80 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200"
                    required
                >

            </div>

            <!-- Dropdown Kategori -->
            <div>

                <label
                    for="id_kategori"
                    class="block text-sm font-medium text-slate-300 mb-2"
                >
                    Kategori
                </label>

                <div class="relative">

                    <select
                        id="id_kategori"
                        name="id_kategori"
                        class="w-full px-4 py-3 rounded-lg bg-slate-900/80 border border-slate-700 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200 appearance-none cursor-pointer"
                        required
                    >

                        <option value="" class="bg-slate-800 text-slate-400">
                            -- Pilih Kategori --
                        </option>

                        <?php
                        if (!empty($data_kategori)) {

                            foreach ($data_kategori as $kat) {

                                $selected = (
                                    isset($data_alat->id_kategori) &&
                                    $kat->id_kategori == $data_alat->id_kategori
                                ) ? "selected" : "";

                                echo "<option
                                    value='" . htmlspecialchars($kat->id_kategori) . "'
                                    $selected
                                    class='bg-slate-800 text-white'
                                >"
                                . htmlspecialchars($kat->nama_kategori) .
                                "</option>";
                            }
                        }
                        ?>

                    </select>

                    <div
                        class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-400"
                    >
                        <i class="fa-solid fa-chevron-down text-xs"></i>
                    </div>

                </div>

            </div>

            <!-- Input Stok -->
            <div>

                <label
                    for="stok"
                    class="block text-sm font-medium text-slate-300 mb-2"
                >
                    Stok Barang
                </label>

                <input
                    type="number"
                    id="stok"
                    name="stok"
                    min="0"
                    value="<?= htmlspecialchars($data_alat->stok ?? '0'); ?>"
                    placeholder="Masukkan jumlah stok"
                    class="w-full px-4 py-3 rounded-lg bg-slate-900/80 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200"
                    required
                >

            </div>

            <!-- Tombol Submit & Batal -->
            <div class="pt-3 space-y-3">

                <button
                    type="submit"
                    class="w-full py-3 px-4 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold rounded-lg shadow-lg hover:shadow-indigo-500/30 transition duration-200 active:scale-[0.98] uppercase tracking-wider"
                >
                    Update Data Alat
                </button>

                <!-- Batal kembali ke v_alat.php -->
                <a
                    href="v_alat.php"
                    class="block text-center text-xs text-slate-400 hover:text-slate-200 transition duration-150 py-1"
                >
                    ← Batal
                </a>

            </div>

        </form>

    </div>

</body>

</html>