<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

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
    <title>Form Tambah Kategori</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-[#111827] text-slate-100 min-h-screen flex items-center justify-center p-4 antialiased">

    <!-- Container Card (Sesuai gaya Tambah User) -->
    <div class="w-full max-w-md bg-[#1f293d] border border-slate-700/50 p-8 rounded-3xl shadow-2xl">
        
        <!-- Header Form -->
        <div class="text-center mb-8">
            <h2 class="text-2xl font-extrabold uppercase tracking-wide text-white mb-2">
                FORM TAMBAH KATEGORI
            </h2>
            <p class="text-xs text-slate-400 font-medium">
                Tambahkan kategori baru ke dalam sistem
            </p>
        </div>

        <!-- Form Input -->
        <form action="../controller/c_kategori.php?aksi=tambah" method="post" class="space-y-5">
            
            <!-- Field Nama Kategori -->
            <div>
                <label for="nama_kategori" class="block text-xs font-medium text-slate-300 mb-2">
                    Nama kategori
                </label>
                <input type="text" 
                       id="nama_kategori" 
                       name="nama kategori" 
                       required 
                       placeholder="masukan nama kategori"
                       class="w-full px-4 py-3 bg-[#e8f0fe] text-slate-900 text-sm rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 placeholder-slate-400 transition font-medium">
            </div>

            <!-- Field Keterangan -->
            <div>
                <label for="keterangan_kategori" class="block text-xs font-medium text-slate-300 mb-2">
                    keterangan
                </label>
                <input type="text" 
                       id="keterangan_kategori" 
                       name="keterangan kategori" 
                       required 
                       placeholder="masukan keterangan"
                       class="w-full px-4 py-3 bg-[#0f172a]/60 border border-slate-700/80 text-slate-200 text-sm rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 placeholder-slate-500 transition">
            </div>

            <!-- Tombol Submit / Daftar -->
            <button type="submit" 
                    name="tambah" 
                    value="Daftar"
                    class="w-full py-3.5 mt-2 bg-[#5551ff] hover:bg-[#4338ca] text-white font-bold text-sm tracking-wider uppercase rounded-xl shadow-lg transition duration-200 cursor-pointer">
                DAFTAR
            </button>

            <!-- Tombol Batal -->
            <div class="text-center pt-1">
                <a href="v_kategori.php" class="text-xs text-slate-400 hover:text-white transition">
                    ← Batal
                </a>
            </div>

        </form>
    </div>

</body>
</html>