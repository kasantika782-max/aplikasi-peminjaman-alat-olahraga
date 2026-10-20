<?php
if (session_status() === PHP_SESSION_NONE) { session_start();
require_once __DIR__ . '/../model/m_security.php';
$csrf_token = csrf_token(); }

// 1. Cek Login
if (!isset($_SESSION['role'])) {
    header("Location: v_login.php");
    exit();
}

// 2. Ambil Data Kategori Berdasarkan ID dari URL jika diakses langsung dari View
require_once '../model/m_koneksi.php';
require_once '../model/m_kategori.php';

// Cek jika variabel $data_edit belum disiapkan oleh controller, kita ambil langsung dari model
if (!isset($data_edit)) {
    $db = (new m_koneksi())->koneksi;
    $kategori_model = new m_kategori($db);
    $id_kategori = $_GET['id'] ?? '';
    $data_edit = $kategori_model->tampil_data_by_id($id_kategori);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Kategori</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-900 min-h-screen flex items-center justify-center p-4 font-sans">

    <!-- Card Form Update Kategori -->
    <div class="w-full max-w-md bg-slate-800 text-slate-100 rounded-2xl shadow-2xl border border-slate-700/50 p-8 sm:p-10 my-6">
        
        <!-- Header / Judul -->
        <div class="text-center mb-8">
            <h2 class="text-2xl font-extrabold tracking-wider text-white uppercase">Form Update Kategori</h2>
            <p class="text-slate-400 text-sm mt-1">Ubah data kategori alat/barang</p>
        </div>

        <!-- Form Update Kategori -->
        <form action="../controller/c_kategori.php?aksi=update" method="post" class="space-y-5">
            <?= csrf_input(); ?>
            
            <!-- Hidden ID Kategori -->
            <input type="hidden" name="id_kategori" value="<?= htmlspecialchars($data_edit->id_kategori ?? ''); ?>">

            <!-- Input Nama Kategori -->
            <div>
                <label for="nama_kategori" class="block text-sm font-medium text-slate-300 mb-2">Nama Kategori</label>
                <input 
                    type="text" 
                    id="nama_kategori" 
                    name="nama_kategori" 
                    value="<?= htmlspecialchars($data_edit->nama_kategori ?? ''); ?>" 
                    placeholder="Masukkan nama kategori"
                    class="w-full px-4 py-3 rounded-lg bg-slate-900/80 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200"
                    required
                >
            </div>

            <!-- Input Keterangan Kategori -->
            <div>
                <label for="keterangan_kategori" class="block text-sm font-medium text-slate-300 mb-2">Keterangan</label>
                <input 
                    type="text" 
                    id="keterangan_kategori" 
                    name="keterangan_kategori" 
                    value="<?= htmlspecialchars($data_edit->keterangan_kategori ?? ''); ?>" 
                    placeholder="Masukkan keterangan"
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
                    Simpan Perubahan
                </button>
                <a 
                    href="v_kategori.php" 
                    class="block text-center text-xs text-slate-400 hover:text-slate-200 transition duration-150 py-1"
                >
                    ← Batal
                </a>
            </div>

        </form>

    </div>

</body>
</html>