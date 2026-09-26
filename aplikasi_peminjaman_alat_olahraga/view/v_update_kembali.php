<?php
if (session_status() === PHP_SESSION_NONE) { session_start();
require_once __DIR__ . '/../model/m_security.php';
$csrf_token = csrf_token(); }
if (($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: v_daftar_alat.php');
    exit();
}

include '../controller/c_peminjaman.php'; 
// Jika data_edit gagal diambil, kembalikan ke halaman utama agar tidak error property on null
if (!$data_edit) { header("location:v_peminjaman_admin.php"); exit(); }
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Pengembalian</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-900 min-h-screen flex items-center justify-center p-4 font-sans text-slate-100">

    <!-- Card Container -->
    <div class="w-full max-w-md bg-slate-800 text-slate-100 rounded-2xl shadow-2xl border border-slate-700/50 border-t-4 border-t-emerald-500 p-8 sm:p-10 my-6">
        
        <!-- Header / Judul -->
        <div class="text-center mb-8">
            <h2 class="text-2xl font-extrabold tracking-wider text-white uppercase">Update Pengembalian</h2>
            <p class="text-slate-400 text-sm mt-1">
                ID Peminjaman: <span class="text-emerald-400 font-bold">#<?= $data_edit->id_peminjaman; ?></span>
            </p>
        </div>

        <!-- Form Update Pengembalian -->
        <form action="../controller/c_peminjaman.php?aksi=update_kembali" method="POST" class="space-y-5">
            <?= csrf_input(); ?>
            
            <!-- Hidden ID Peminjaman -->
            <input type="hidden" name="id_peminjaman" value="<?= $data_edit->id_peminjaman; ?>">

            <!-- Select Kondisi Barang Masuk -->
            <div>
                <label class="block text-sm font-medium text-slate-300 mb-2">Kondisi Barang Masuk</label>
                <div class="relative">
                    <select 
                        name="kondisi_masuk" 
                        class="w-full px-4 py-3 rounded-lg bg-slate-900/80 border border-slate-700 text-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition duration-200 appearance-none cursor-pointer"
                    >
                        <option value="Baik" <?= ($data_edit->kondisi_masuk == 'Baik') ? 'selected' : ''; ?> class="bg-slate-800 text-emerald-400 font-semibold">
                            Baik
                        </option>
                        <option value="Rusak Ringan" <?= ($data_edit->kondisi_masuk == 'Rusak Ringan') ? 'selected' : ''; ?> class="bg-slate-800 text-amber-400 font-semibold">
                            Rusak Ringan
                        </option>
                        <option value="Rusak Berat" <?= ($data_edit->kondisi_masuk == 'Rusak Berat') ? 'selected' : ''; ?> class="bg-slate-800 text-rose-400 font-semibold">
                            Rusak Berat
                        </option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-400">
                        <i class="fa-solid fa-chevron-down text-xs"></i>
                    </div>
                </div>
            </div>

            <!-- Input Tanggal Kembali Asli -->
            <div>
                <label class="block text-sm font-medium text-slate-300 mb-2">Tanggal Kembali Asli</label>
                <input 
                    type="date" 
                    name="tgl_kembali_asli" 
                    value="<?= $data_edit->tgl_kembali_asli; ?>" 
                    class="w-full px-4 py-3 rounded-lg bg-slate-900/80 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition duration-200 [color-scheme:dark]"
                    required
                >
            </div>

            <!-- Tombol Submit & Batal -->
            <div class="pt-3 space-y-3">
                <button 
                    type="submit" 
                    class="w-full py-3 px-4 bg-emerald-600 hover:bg-emerald-500 text-white font-semibold rounded-lg shadow-lg hover:shadow-emerald-500/30 transition duration-200 active:scale-[0.98] uppercase tracking-wider"
                >
                    Update Data Kembali
                </button>
                <a 
                    href="v_peminjaman_admin.php?tipe=kembali" 
                    class="block text-center text-xs text-slate-400 hover:text-slate-200 transition duration-150 py-1"
                >
                    ← Kembali
                </a>
            </div>

        </form>

    </div>

</body>
</html>