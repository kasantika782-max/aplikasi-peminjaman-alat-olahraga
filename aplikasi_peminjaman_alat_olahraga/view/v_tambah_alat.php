<?php
if (session_status() === PHP_SESSION_NONE) { session_start();
require_once __DIR__ . '/../model/m_security.php';
$csrf_token = csrf_token(); }

// 1. Cek apakah sudah login? Jika belum, balik ke login.php
if (!isset($_SESSION['role'])) {
    header("Location: v_login.php");
    exit();
}

// 2. Jika yang masuk BUKAN admin, maka dialihkan/ditendang balik
if ($_SESSION['role'] !== 'admin') {
    if ($_SESSION['role'] === 'petugas') {
        header("Location: v_peminjaman_petugas.php");
    } else {
        header("Location: v_daftar_alat.php");
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Alat - Admin</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-[#111827] text-slate-100 min-h-screen flex items-center justify-center p-4 antialiased">

    <!-- Container Card -->
    <div class="w-full max-w-md bg-[#1f293d] border border-slate-700/50 p-8 rounded-3xl shadow-2xl">
        
        <!-- Header Form -->
        <div class="text-center mb-6">
            <h2 class="text-2xl font-extrabold uppercase tracking-wide text-white mb-2">
                FORM TAMBAH ALAT
            </h2>
            <p class="text-xs text-slate-400 font-medium">
                Tambahkan data alat baru ke dalam sistem
            </p>
        </div>

        <!-- Form Input -->
        <form action="../controller/c_alat.php?aksi=proses_tambah" method="post" class="space-y-4">
            <?= csrf_input(); ?>
            
            <!-- Nama Alat -->
            <div>
                <label class="block text-xs font-medium text-slate-300 mb-2">
                    Nama Alat
                </label>
                <input type="text" 
                       name="nama_alat" 
                       required 
                       placeholder="Masukkan nama alat"
                       class="w-full px-4 py-3 bg-[#e8f0fe] text-slate-900 text-sm rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 placeholder-slate-400 font-medium transition">
            </div>

            <!-- Kategori (Dropdown Select) -->
            <div>
                <label class="block text-xs font-medium text-slate-300 mb-2">
                    Kategori
                </label>
                <div class="relative">
                    <select name="id_kategori" 
                            required 
                            class="w-full px-4 py-3 bg-[#0f172a]/60 border border-slate-700/80 text-slate-200 text-sm rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 appearance-none transition cursor-pointer">
                        <option value="" disabled selected class="text-slate-500">Pilih Kategori</option>
                        <?php foreach ($data_kategori as $kat): ?>
                            <option value="<?= $kat->id_kategori ?>" class="bg-[#1f293d] text-white">
                                <?= $kat->nama_kategori ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <!-- Panah Dropdown kustom -->
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-400">
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20">
                            <path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Stok -->
            <div>
                <label class="block text-xs font-medium text-slate-300 mb-2">
                    Stok
                </label>
                <input type="number" 
                       name="stok" 
                       required 
                       placeholder="0"
                       class="w-full px-4 py-3 bg-[#0f172a]/60 border border-slate-700/80 text-slate-200 text-sm rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 placeholder-slate-500 transition">
            </div>

            <!-- URL Foto Alat -->
            <div>
                <label class="block text-xs font-medium text-slate-300 mb-2">
                    URL Foto Alat
                </label>
                <input type="text" 
                       name="url_foto" 
                       required 
                       placeholder="Paste link foto di sini"
                       class="w-full px-4 py-3 bg-[#0f172a]/60 border border-slate-700/80 text-slate-200 text-sm rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 placeholder-slate-500 transition">
            </div>

            <!-- Tombol Simpan -->
            <button type="submit" 
                    class="w-full py-3.5 mt-2 bg-[#5551ff] hover:bg-[#4338ca] text-white font-bold text-sm tracking-wider uppercase rounded-xl shadow-lg transition duration-200 cursor-pointer">
                SIMPAN ALAT
            </button>

            <!-- Tombol Kembali / Batal -->
            <div class="text-center pt-1">
                <a href="../controller/c_alat.php?aksi=tampil" class="text-xs text-slate-400 hover:text-white transition">
                    ← Kembali
                </a>
            </div>

        </form>
    </div>

</body>
</html>