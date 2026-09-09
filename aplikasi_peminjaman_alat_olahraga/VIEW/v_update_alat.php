<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// 1. Cek Login & Hak Akses Admin
if (!isset($_SESSION['role'])) {
    header("Location: v_login.php");
    exit();
}

if ($_SESSION['role'] !== 'admin') {
    header("Location: v_daftar_alat.php");
    exit();
}

// 2. Sertakan koneksi dan model jika data belum dipersiapkan dari controller
require_once '../model/m_koneksi.php';
require_once '../model/m_alat.php';
require_once '../model/m_kategori.php';

$db = (new m_koneksi())->koneksi;

// Jika $data_alat belum diset oleh controller, ambil langsung berdasarkan ID di URL
if (!isset($data_alat)) {
    $id_alat = $_GET['id'] ?? '';
    $alat_model = new m_alat($db);
    $data_alat = $alat_model->tampil_data_by_id($id_alat);
}

// Jika $data_kategori belum diset, ambil semua list kategori untuk dropdown select
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-900 min-h-screen flex items-center justify-center p-4 font-sans text-slate-100">

    <!-- Card Container -->
    <div class="w-full max-w-lg bg-slate-800 text-slate-100 rounded-2xl shadow-2xl border border-slate-700/50 p-8 sm:p-10 my-6">
        
        <!-- Header / Judul -->
        <div class="text-center mb-8">
            <h2 class="text-2xl font-extrabold tracking-wider text-white uppercase">Edit Data Alat</h2>
            <p class="text-slate-400 text-sm mt-1">Perbarui informasi barang/alat inventaris</p>
        </div>

        <!-- Form Edit Alat -->
        <form action="../controller/c_alat.php?aksi=update" method="POST" class="space-y-5">
            
            <!-- Hidden ID Alat -->
            <input type="hidden" name="id_alat" value="<?= htmlspecialchars($data_alat->id_alat ?? ''); ?>">

            <!-- Input Nama Alat -->
            <div>
                <label for="nama_alat" class="block text-sm font-medium text-slate-300 mb-2">Nama Alat</label>
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
                <label for="id_kategori" class="block text-sm font-medium text-slate-300 mb-2">Kategori</label>
                <div class="relative">
                    <select 
                        id="id_kategori" 
                        name="id_kategori" 
                        class="w-full px-4 py-3 rounded-lg bg-slate-900/80 border border-slate-700 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200 appearance-none cursor-pointer"
                        required
                    >
                        <option value="" class="bg-slate-800 text-slate-400">-- Pilih Kategori --</option>
                        <?php 
                        if (!empty($data_kategori)) {
                            foreach ($data_kategori as $kat) {
                                $selected = (isset($data_alat->id_kategori) && $kat->id_kategori == $data_alat->id_kategori) ? "selected" : "";
                                echo "<option value='" . htmlspecialchars($kat->id_kategori) . "' $selected class='bg-slate-800 text-white'>" . htmlspecialchars($kat->nama_kategori) . "</option>";
                            }
                        }
                        ?>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-400">
                        <i class="fa-solid fa-chevron-down text-xs"></i>
                    </div>
                </div>
            </div>

            <!-- Input Stok -->
            <div>
                <label for="stok" class="block text-sm font-medium text-slate-300 mb-2">Stok Barang</label>
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

            <!-- Pratinjau & Link URL Foto -->
            <div class="p-4 bg-slate-900/50 rounded-xl border border-slate-700/60 space-y-3">
                <label class="block text-sm font-medium text-slate-300">Pratinjau Foto Saat Ini</label>
                
                <div class="flex items-center gap-4">
                    <?php if(!empty($data_alat->foto)): ?>
                        <img 
                            src="<?= htmlspecialchars($data_alat->foto); ?>" 
                            alt="Foto Alat" 
                            class="w-20 h-20 object-cover rounded-lg border border-slate-700 shadow-md flex-shrink-0"
                            onerror="this.onerror=null; this.src='https://via.placeholder.com/150?text=Error+Image';"
                        >
                    <?php else: ?>
                        <div class="w-20 h-20 rounded-lg bg-slate-800 border border-dashed border-slate-700 flex items-center justify-center text-slate-500 flex-shrink-0">
                            <i class="fa-solid fa-image text-xl"></i>
                        </div>
                    <?php endif; ?>

                    <div class="flex-1 min-w-0">
                        <label for="url_foto" class="block text-xs font-medium text-slate-400 mb-1">URL Foto (Link Google / Web)</label>
                        <input 
                            type="text" 
                            id="url_foto"
                            name="url_foto" 
                            value="<?= htmlspecialchars($data_alat->foto ?? ''); ?>" 
                            placeholder="https://..." 
                            class="w-full px-3 py-2 text-xs rounded-lg bg-slate-900 border border-slate-700 text-slate-200 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200"
                        >
                        <p class="text-[10px] text-slate-500 mt-1">*Kosongkan atau biarkan tetap jika tidak ingin mengganti gambar.</p>
                    </div>
                </div>
            </div>

            <!-- Tombol Submit & Batal -->
            <div class="pt-3 space-y-3">
                <button 
                    type="submit" 
                    class="w-full py-3 px-4 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold rounded-lg shadow-lg hover:shadow-indigo-500/30 transition duration-200 active:scale-[0.98] uppercase tracking-wider"
                >
                    Update Data Alat
                </button>
                <a 
                    href="v_daftar_alat.php" 
                    class="block text-center text-xs text-slate-400 hover:text-slate-200 transition duration-150 py-1"
                >
                    ← Batal
                </a>
            </div>

        </form>

    </div>

</body>
</html>