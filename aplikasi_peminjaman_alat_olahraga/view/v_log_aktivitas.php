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

include '../controller/c_peminjaman.php'; // Ini otomatis memanggil m_peminjaman dan koneksi

// Ambil data log dari model menggunakan fungsi yang tadi kita buat
$isi_log = $pinjam_model->tampil_log_aktivitas();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log Aktivitas Sistem - Admin</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 antialiased">

    <div class="flex min-h-screen">

        <!-- ================= SIDEBAR KIRI ================= -->
        <aside class="w-64 bg-indigo-900 text-white flex flex-col justify-between p-5 shadow-xl">
            <div>
                <!-- Logo & Judul Aplikasi -->
                <div class="flex items-center gap-3 px-2 py-4 border-b border-indigo-800/60 mb-6">
                    <div class="bg-indigo-500 text-white p-2.5 rounded-xl shadow-lg">
                        <i class="fa-solid fa-boxes-packing text-xl"></i>
                    </div>
                    <div>
                        <h1 class="font-bold text-lg leading-tight">Peminjaman</h1>
                        <span class="text-xs text-indigo-300">Admin Dashboard</span>
                    </div>
                </div>

                <!-- Menu Navigasi -->
                <nav class="space-y-1.5">
                    <a href="v_tampilan_user.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-indigo-200 hover:bg-indigo-800/50 hover:text-white font-medium transition">
                        <i class="fa-solid fa-users w-5"></i> Data Pengguna
                    </a>
                    <a href="v_kategori.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-indigo-200 hover:bg-indigo-800/50 hover:text-white font-medium transition">
                        <i class="fa-solid fa-layer-group w-5"></i> Kategori
                    </a>
                    <a href="v_alat.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-indigo-200 hover:bg-indigo-800/50 hover:text-white font-medium transition">
                        <i class="fa-solid fa-toolbox w-5"></i> Data Alat
                    </a>
                    <a href="v_peminjaman_admin.php?tipe=pinjam" class="flex items-center gap-3 px-4 py-3 rounded-xl text-indigo-200 hover:bg-indigo-800/50 hover:text-white font-medium transition">
                        <i class="fa-solid fa-arrow-right-arrow-left w-5"></i> Peminjaman
                    </a>
                    <a href="v_log_aktivitas.php" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-indigo-800 text-white font-medium shadow-sm transition">
                        <i class="fa-solid fa-clock-rotate-left w-5"></i> Log Aktivitas
                    </a>
                </nav>
            </div>

            <!-- Panel Info Samping (Bawah Sidebar) -->
            <div class="bg-indigo-800/60 p-4 rounded-2xl border border-indigo-700/50 text-center">
                <i class="fa-solid fa-shield-halved text-indigo-300 text-2xl mb-2"></i>
                <h4 class="text-sm font-semibold mb-1">Audit Trail Active</h4>
                <p class="text-xs text-indigo-300 mb-1">Semua aksi user recorded secara otomatis oleh sistem.</p>
            </div>
        </aside>

        <!-- ================= KONTEN UTAMA ================= -->
        <main class="flex-1 p-8 overflow-y-auto">

            <!-- Top Header Bar -->
            <header class="flex justify-between items-center mb-8">
                <div>
                    <h2 class="text-2xl font-bold text-slate-800">Riwayat Aktivitas Sistem</h2>
                    <p class="text-sm text-slate-500">Catatan jejak aktivitas (audit log) pengguna pada aplikasi.</p>
                </div>

                <!-- Profile Badge & Logout -->
                <div class="flex items-center gap-4 bg-white px-4 py-2 rounded-2xl shadow-sm border border-slate-200/80">
                    <div class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold text-lg">
                        <?= strtoupper(substr($_SESSION['role'] ?? 'A', 0, 1)); ?>
                    </div>
                    <div>
                        <p class="text-sm font-semibold capitalize text-slate-800"><?= $_SESSION['role'] ?? 'Admin'; ?></p>
                        <span class="text-xs text-emerald-500 font-medium">● Online</span>
                    </div>
                    <!-- Tombol Logout yang Memanggil SweetAlert2 -->
                    <button type="button" onclick="konfirmasiLogout()" class="ml-2 text-rose-500 hover:bg-rose-50 p-2 rounded-lg transition" title="Logout">
                        <i class="fa-solid fa-right-from-bracket text-lg"></i>
                    </button>
                </div>
            </header>

            <!-- Card Banner Ringkasan -->
            <section class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-8">
                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl">
                        <i class="fa-solid fa-list-check"></i>
                    </div>
                    <div>
                        <span class="text-xs font-medium text-slate-400">Total Log Terekam</span>
                        <h3 class="text-xl font-bold text-slate-800">
                            <?= ($isi_log) ? mysqli_num_rows($isi_log) : 0; ?> Catatan
                        </h3>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
                        <i class="fa-solid fa-lock"></i>
                    </div>
                    <div>
                        <span class="text-xs font-medium text-slate-400">Keamanan Sistem</span>
                        <h3 class="text-xl font-bold text-slate-800">Sistem Terenkripsi</h3>
                    </div>
                </div>
            </section>

            <!-- ================= TABEL DATA ================= -->
            <section class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
                <div class="p-5 border-b border-slate-100 flex justify-between items-center">
                    <h3 class="font-bold text-slate-800">Audit Trail / Log Aktivitas</h3>
                    <a href="v_tampilan_user.php" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium text-xs rounded-xl transition flex items-center gap-2">
                        <i class="fa-solid fa-arrow-left"></i> Kembali ke Dashboard
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-100 text-slate-400 text-xs uppercase font-semibold">
                                <th class="p-4 pl-6 text-center w-16">No</th>
                                <th class="p-4 w-48">Waktu / Jam</th>
                                <th class="p-4 w-48">User / Aktor</th>
                                <th class="p-4 pr-6">Aksi / Kegiatan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            <?php 
                            $no = 1;
                            if ($isi_log && mysqli_num_rows($isi_log) > 0) :
                                while($row = mysqli_fetch_object($isi_log)): 
                            ?>
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="p-4 pl-6 text-center font-medium text-slate-500"><?= $no++; ?></td>
                                    <td class="p-4">
                                        <div class="flex items-center gap-2 text-xs font-semibold text-indigo-600 bg-indigo-50 px-3 py-1.5 rounded-lg w-fit border border-indigo-100">
                                            <i class="fa-regular fa-clock"></i>
                                            <?= date('d-m-Y | H:i:s', strtotime($row->waktu)); ?>
                                        </div>
                                    </td>
                                    <td class="p-4 font-semibold text-slate-800">
                                        <div class="flex items-center gap-2">
                                            <div class="w-7 h-7 rounded-full bg-slate-200 text-slate-600 flex items-center justify-center font-bold text-xs">
                                                <?= strtoupper(substr($row->username, 0, 1)); ?>
                                            </div>
                                            <span><?= htmlspecialchars($row->username); ?></span>
                                        </div>
                                    </td>
                                    <td class="p-4 pr-6 text-slate-600 font-medium">
                                        <?= htmlspecialchars($row->aksi); ?>
                                    </td>
                                </tr>
                            <?php 
                                endwhile; 
                            else : 
                            ?>
                                <tr>
                                    <td colspan="4" class="p-8 text-center text-slate-400">
                                        <i class="fa-solid fa-clock-rotate-left text-3xl mb-2"></i>
                                        <p>Belum ada catatan aktivitas sistem yang terekam.</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>

        </main>
    </div>

    <!-- Script SweetAlert2 -->
    <script>
        function konfirmasiLogout() {
            Swal.fire({
                title: 'Konfirmasi sesi',
                text: 'Apakah Anda yakin ingin keluar dari sistem?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#f43f5e',
                cancelButtonColor: '#6b7280',
                confirmButtonText: '<i class="fa-solid fa-right-from-bracket mr-1"></i> Ya, Logout',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                customClass: {
                    popup: 'rounded-2xl shadow-xl',
                    confirmButton: 'px-4 py-2 rounded-xl text-sm font-semibold',
                    cancelButton: 'px-4 py-2 rounded-xl text-sm font-semibold'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    postAction('../controller/c_login.php?aksi=logout');
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
    token.type = 'hidden';
    token.name = 'csrf_token';
    token.value = <?= json_encode($csrf_token ?? csrf_token()) ?>;
    form.appendChild(token);
    document.body.appendChild(form);
    form.submit();
}
</script>

</body>
</html>