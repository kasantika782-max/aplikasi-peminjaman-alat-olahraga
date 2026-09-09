<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Proteksi Halaman: Hanya Admin atau Petugas yang boleh masuk
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'petugas')) {
    header("Location: v_login.php");
    exit();
}

require_once '../model/m_koneksi.php';
require_once '../model/m_peminjaman.php';

$database = new m_koneksi();
$db = $database->koneksi;
$pinjam_model = new m_peminjaman($db);

// Ambil data user & alat untuk isi dropdown
$list_peminjam = $pinjam_model->get_all_peminjam();
$list_alat     = $pinjam_model->get_all_alat();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Peminjaman Manual - Admin</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-900 min-h-screen flex items-center justify-center p-4 text-slate-100">

    <div class="w-full max-w-lg bg-slate-800 text-slate-100 rounded-2xl shadow-2xl border border-slate-700/50 p-6 sm:p-8 my-6">
        
        <!-- Header Form -->
        <div class="text-center mb-6">
            <div class="w-12 h-12 bg-indigo-600/20 text-indigo-400 rounded-2xl flex items-center justify-center mx-auto mb-3 border border-indigo-500/30">
                <i class="fa-solid fa-hand-holding-box text-xl"></i>
            </div>
            <h2 class="text-2xl font-extrabold tracking-tight text-white">Peminjaman Manual</h2>
            <p class="text-slate-400 text-sm mt-1">Input transaksi peminjaman langsung untuk user</p>
        </div>

        <form action="../controller/c_peminjaman.php?aksi=tambah_admin" method="POST" class="space-y-4">
            
            <!-- Select Nama Peminjam -->
            <div>
                <label class="block text-sm font-medium text-slate-300 mb-2">Nama Peminjam (Siswa)</label>
                <div class="relative">
                    <select name="id_user" class="w-full px-4 py-3 rounded-xl bg-slate-900/80 border border-slate-700 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 appearance-none cursor-pointer" required>
                        <option value="">-- Pilih Peminjam --</option>
                        <?php 
                        if ($list_peminjam && mysqli_num_rows($list_peminjam) > 0):
                            while ($user = mysqli_fetch_assoc($list_peminjam)): 
                        ?>
                            <option value="<?= $user['id_user']; ?>" class="bg-slate-800 text-slate-100">
                                <?= htmlspecialchars($user['username']); ?>
                            </option>
                        <?php 
                            endwhile;
                        endif; 
                        ?>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-400">
                        <i class="fa-solid fa-chevron-down text-xs"></i>
                    </div>
                </div>
            </div>

            <!-- Select Alat/Barang -->
            <div>
                <label class="block text-sm font-medium text-slate-300 mb-2">Pilih Alat</label>
                <div class="relative">
                    <select name="id_alat" class="w-full px-4 py-3 rounded-xl bg-slate-900/80 border border-slate-700 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 appearance-none cursor-pointer" required>
                        <option value="">-- Pilih Alat --</option>
                        <?php 
                        if ($list_alat && mysqli_num_rows($list_alat) > 0):
                            while ($alat = mysqli_fetch_assoc($list_alat)): 
                        ?>
                            <option value="<?= $alat['id_alat']; ?>" class="bg-slate-800 text-slate-100">
                                <?= htmlspecialchars($alat['nama_alat']); ?> (Tersedia: <?= $alat['stok']; ?>)
                            </option>
                        <?php 
                            endwhile;
                        endif; 
                        ?>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-400">
                        <i class="fa-solid fa-chevron-down text-xs"></i>
                    </div>
                </div>
            </div>

            <!-- Jumlah Pinjam -->
            <div>
                <label class="block text-sm font-medium text-slate-300 mb-2">Jumlah Pinjam</label>
                <input 
                    type="number" 
                    name="jumlah_pinjam" 
                    min="1" 
                    placeholder="Masukkan jumlah unit" 
                    class="w-full px-4 py-3 rounded-xl bg-slate-900/80 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500" 
                    required
                >
            </div>

            <!-- Kondisi Alat Keluar -->
            <div>
                <label class="block text-sm font-medium text-slate-300 mb-2">Kondisi Keluar</label>
                <select name="kondisi_keluar" class="w-full px-4 py-3 rounded-xl bg-slate-900/80 border border-slate-700 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="Baik" class="bg-slate-800">Baik</option>
                    <option value="Rusak Ringan" class="bg-slate-800">Rusak Ringan</option>
                </select>
            </div>

            <!-- Action Buttons -->
            <div class="pt-4 space-y-3">
                <button 
                    type="submit" 
                    class="w-full py-3.5 px-4 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold rounded-xl shadow-lg hover:shadow-indigo-500/25 transition duration-200 uppercase tracking-wider text-xs flex items-center justify-center gap-2"
                >
                    <i class="fa-solid fa-floppy-disk"></i>
                    <span>Simpan Peminjaman</span>
                </button>
                <a 
                    href="v_peminjaman_admin.php?tipe=pinjam" 
                    class="block text-center text-sm text-slate-400 hover:text-slate-200 transition duration-150 py-1"
                >
                    ← Batal / Kembali
                </a>
            </div>

        </form>

    </div>

</body>
</html>