<?php 
if (session_status() === PHP_SESSION_NONE) { session_start();
require_once __DIR__ . '/../model/m_security.php';
$csrf_token = csrf_token(); }

// 1. Cek apakah sudah login?
if (!isset($_SESSION['role'])) {
    header("Location: v_login.php");
    exit();
}

// 2. Jika yang masuk BUKAN petugas, maka ditendang balik
if ($_SESSION['role'] !== 'petugas') {
    if ($_SESSION['role'] === 'admin') {
        // Jika admin nyasar ke sini, balikin ke dashboard admin
        header("Location: v_tampilan_user.php");
    } else {
        // Jika peminjam nyasar ke sini, balikin ke dashboard user
        header("Location: v_daftar_alat.php");
    }
    exit();
}

include_once '../controller/c_peminjaman.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoring Peminjaman - Petugas</title>
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

        /* Hide Print-only Header by default in browser */
        .print-header { display: none; }

        /* ================= CSS KHUSUS PRINT ================= */
        @media print {
            aside, header, .no-print, button, a, .navigation-tabs {
                display: none !important;
            }

            body {
                background: #fff !important;
                color: #000 !important;
                padding: 0 !important;
                margin: 0 !important;
            }

            .main-container {
                border: none !important;
                box-shadow: none !important;
                padding: 0 !important;
                max-width: 100% !important;
            }

            /* Tampilkan Kop Laporan saat dicetak */
            .print-header {
                display: block !important;
                margin-bottom: 20px;
                border-bottom: 2px solid #000;
                padding-bottom: 10px;
            }

            section, .overflow-x-auto {
                border: none !important;
                box-shadow: none !important;
                width: 100% !important;
                overflow: visible !important;
            }

            table {
                width: 100% !important;
                border-collapse: collapse !important;
                font-size: 10pt !important;
            }

            th, td {
                border: 1px solid #64748b !important;
                padding: 6px 8px !important;
                color: #000 !important;
            }

            th {
                background-color: #f1f5f9 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            @page {
                size: A4 portrait;
                margin: 1.5cm 1cm 1.5cm 1cm;
            }
        }
    </style>
</head>
<body class="bg-slate-100 min-h-screen p-4 md:p-8 text-slate-800 antialiased">

    <!-- Container Utama -->
    <div class="max-w-7xl mx-auto bg-white rounded-2xl shadow-xl p-6 md:p-8 border border-slate-200 main-container">
        
        <!-- Header Laporan Khusus Hasil Print -->
        <div class="print-header text-center">
            <h1 class="text-xl font-bold uppercase tracking-wide">Laporan Monitoring Peminjaman Alat</h1>
            <p class="text-xs text-slate-600">Dicetak oleh: Petugas | Tanggal: <?= date('d/m/Y H:i'); ?></p>
        </div>

        <!-- Header & Tombol Aksi Web -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 pb-4 border-b border-slate-200 gap-4 no-print">
            <div>
                <h1 class="text-2xl md:text-3xl font-extrabold text-indigo-900 tracking-tight flex items-center gap-2">
                    <i class="fa-solid fa-clipboard-check text-indigo-600"></i>
                    Laporan Peminjaman Alat
                </h1>
                <p class="text-slate-500 text-sm mt-1">Monitoring dan konfirmasi persetujuan data peminjaman</p>
            </div>
            
            <div class="flex items-center gap-3">
                <button onclick="window.print()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl shadow-sm transition flex items-center gap-2 cursor-pointer">
                    <i class="fa-solid fa-print"></i> Cetak Laporan
                </button>
                <!-- Tombol Logout Modern -->
                <button type="button" 
                        onclick="konfirmasiLogout()" 
                        class="px-4 py-2 bg-rose-500 hover:bg-rose-600 text-white text-sm font-semibold rounded-xl shadow-sm transition flex items-center gap-2 cursor-pointer">
                    <i class="fa-solid fa-right-from-bracket"></i> Logout
                </button>
            </div>
        </div>

        <!-- Bar Search Client Side -->
        <div class="mb-4 flex justify-between items-center no-print">
            <div class="relative w-full md:w-72">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" id="searchInput" onkeyup="filterTable()" placeholder="Cari peminjam atau alat..." 
                       class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-indigo-500 focus:bg-white transition">
            </div>
        </div>

        <!-- Tabel Monitoring Peminjaman -->
        <div class="overflow-x-auto rounded-xl border border-slate-200 shadow-sm">
            <table class="w-full text-left border-collapse" id="dataTable">
                <thead>
                    <tr class="bg-indigo-900 text-white text-xs uppercase font-semibold tracking-wider">
                        <th class="py-3 px-4 text-center border-b border-indigo-800">No</th>
                        <th class="py-3 px-4 border-b border-indigo-800">Nama Peminjam</th>
                        <th class="py-3 px-4 border-b border-indigo-800">Nama Alat</th>
                        <th class="py-3 px-4 text-center border-b border-indigo-800">Tgl Pinjam</th>
                        <th class="py-3 px-4 text-center border-b border-indigo-800">Tgl Kembali</th>
                        <th class="py-3 px-4 text-center border-b border-indigo-800">Status</th>
                        <th class="py-3 px-4 text-center border-b border-indigo-800 no-print">Konfirmasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 text-slate-700 text-sm">
                    <?php 
                    $no = 1;
                    if ($isi_tabel && mysqli_num_rows($isi_tabel) > 0):
                        while($row = mysqli_fetch_object($isi_tabel)): 
                    ?>
                    <tr class="hover:bg-slate-50 transition duration-150 search-row">
                        <td class="py-3 px-4 text-center font-medium text-slate-500"><?= $no++; ?></td>
                        <td class="py-3 px-4 font-semibold text-slate-800 search-peminjam"><?= htmlspecialchars($row->nama_peminjam); ?></td>
                        <td class="py-3 px-4 search-alat"><?= htmlspecialchars($row->nama_alat); ?></td>
                        <td class="py-3 px-4 text-center whitespace-nowrap text-xs"><?= !empty($row->tgl_pinjam) ? date('d-m-Y', strtotime($row->tgl_pinjam)) : '-'; ?></td>
                        <td class="py-3 px-4 text-center whitespace-nowrap text-xs">
                            <?= ($row->status == 'kembali' && !empty($row->tgl_kembali_asli)) ? date('d-m-Y H:i', strtotime($row->tgl_kembali_asli)) : '<span class="text-slate-400">-</span>'; ?>
                        </td>
                        
                        <!-- Badge Status -->
                        <td class="py-3 px-4 text-center whitespace-nowrap">
                            <?php 
                                $badge_bg = 'bg-slate-100 text-slate-600 border-slate-300';
                                if ($row->status == 'pending') {
                                    $badge_bg = 'bg-amber-100 text-amber-800 border-amber-300';
                                } elseif ($row->status == 'dipinjam') {
                                    $badge_bg = 'bg-sky-100 text-sky-800 border-sky-300';
                                } elseif ($row->status == 'kembali') {
                                    $badge_bg = 'bg-emerald-100 text-emerald-800 border-emerald-300';
                                }
                            ?>
                            <span class="inline-block px-3 py-1 rounded-md text-xs font-bold border uppercase tracking-wider <?= $badge_bg; ?>">
                                <?= strtoupper($row->status); ?>
                            </span>
                        </td>

                        <!-- Tombol Konfirmasi & WhatsApp -->
                        <td class="py-3 px-4 text-center whitespace-nowrap no-print">
                            <?php if ($row->status == 'pending'): ?>
                                <button type="button" 
                                        onclick="konfirmasiSetuju(<?= $row->id_peminjaman; ?>, '<?= htmlspecialchars($row->nama_peminjam); ?>', '<?= htmlspecialchars($row->nama_alat); ?>')" 
                                        class="inline-block px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded shadow transition duration-150 cursor-pointer">
                                    <i class="fa-solid fa-check text-xs mr-1"></i> Setuju
                                </button>

                            <?php elseif ($row->status == 'dipinjam'): ?>
                                <div class="flex items-center justify-center gap-1.5">
                                    <!-- Tombol Konfirmasi Kembali (SweetAlert) -->
                                    <button type="button" 
                                            onclick="konfirmasiKembali(<?= $row->id_peminjaman; ?>, '<?= htmlspecialchars($row->nama_alat); ?>')" 
                                            class="inline-block px-3 py-1 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded shadow transition duration-150 cursor-pointer">
                                        <i class="fa-solid fa-rotate-left text-xs mr-1"></i> Kembali
                                    </button>

                                    <!-- Tombol Pengingat WA -->
                                    <?php 
                                        $no_wa = $row->no_hp ?? '';
                                        if (substr($no_wa, 0, 1) === '0') {
                                            $no_wa = '62' . substr($no_wa, 1);
                                        }

                                        $tgl_kembali_fmt = !empty($row->tgl_kembali) ? date('d-m-Y', strtotime($row->tgl_kembali)) : '-';
                                        $pesan_wa = "Halo " . $row->nama_peminjam . ",\n\n" .
                                                    "Ini pengingat bahwa alat *" . $row->nama_alat . "* yang Anda pinjam jatuh tempo pengembalian pada tanggal *" . $tgl_kembali_fmt . "*.\n\n" .
                                                    "Mohon untuk segera mengembalikan alat tersebut ke petugas inventaris. Terima kasih!";

                                        $url_wa = "https://wa.me/" . $no_wa . "?text=" . urlencode($pesan_wa);
                                    ?>

                                    <?php if (!empty($no_wa)): ?>
                                        <a href="<?= $url_wa; ?>" target="_blank" title="Ingatkan via WhatsApp"
                                           class="inline-flex items-center gap-1 px-2.5 py-1 bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-semibold rounded shadow transition duration-150">
                                            <i class="fa-brands fa-whatsapp text-sm"></i> WA
                                        </a>
                                    <?php endif; ?>
                                </div>

                            <?php else: ?>
                                <span class="text-slate-400 text-xs italic"><i class="fa-solid fa-circle-check text-emerald-500 mr-1"></i>Selesai</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php 
                        endwhile; 
                    else: 
                    ?>
                    <tr>
                        <td colspan="7" class="py-6 text-center text-slate-500 font-medium">
                            Tidak ada data riwayat peminjaman.
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>

    <!-- ================= SCRIPT JAVASCRIPT ================= -->
    <script>
        // 1. Live Filter Search
        function filterTable() {
            const input = document.getElementById('searchInput').value.toLowerCase();
            const rows = document.querySelectorAll('.search-row');

            rows.forEach(row => {
                const peminjam = row.querySelector('.search-peminjam')?.textContent.toLowerCase() || '';
                const alat = row.querySelector('.search-alat')?.textContent.toLowerCase() || '';

                if (peminjam.includes(input) || alat.includes(input)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        // 2. Konfirmasi Logout
        function konfirmasiLogout() {
            Swal.fire({
                title: 'Konfirmasi Sesi',
                text: 'Apakah Anda yakin ingin keluar dari sistem?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#f43f5e', // Rose-500
                cancelButtonColor: '#6b7280',  // Slate-500
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

        // 3. Konfirmasi Setujui Peminjaman
        function konfirmasiSetuju(id, namaPeminjam, namaAlat) {
            Swal.fire({
                title: 'Setujui Peminjaman?',
                html: `Apakah Anda ingin menyetujui peminjaman alat <b>"${namaAlat}"</b> untuk peminjam <b>"${namaPeminjam}"</b>?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#10b981', // Emerald-500
                cancelButtonColor: '#6b7280',  // Slate-500
                confirmButtonText: '<i class="fa-solid fa-check mr-1"></i> Ya, Setujui',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                customClass: {
                    popup: 'rounded-2xl shadow-xl',
                    confirmButton: 'px-4 py-2 rounded-xl text-sm font-semibold',
                    cancelButton: 'px-4 py-2 rounded-xl text-sm font-semibold'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    postAction(`../controller/c_peminjaman.php?aksi=setuju&id=${id}`);
                }
            });
        }

        // 4. Konfirmasi Pengembalian Alat
        function konfirmasiKembali(id, namaAlat) {
            Swal.fire({
                title: 'Konfirmasi Pengembalian',
                html: `Konfirmasi bahwa alat <b>"${namaAlat}"</b> sudah dikembalikan?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#4f46e5', // Indigo-600
                cancelButtonColor: '#6b7280',  // Slate-500
                confirmButtonText: '<i class="fa-solid fa-rotate-left mr-1"></i> Ya, Sudah Kembali',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                customClass: {
                    popup: 'rounded-2xl shadow-xl',
                    confirmButton: 'px-4 py-2 rounded-xl text-sm font-semibold',
                    cancelButton: 'px-4 py-2 rounded-xl text-sm font-semibold'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    postAction(`../controller/c_peminjaman.php?aksi=konfirmasi_kembali&id=${id}`);
                }
            });
        }
    </script>

    <!-- Notifikasi Flash Session via SweetAlert2 -->
    <?php if (isset($_SESSION['pesan'])): ?>
    <script>
        Swal.fire({
            title: '<?= $_SESSION['pesan']['judul']; ?>',
            text: '<?= $_SESSION['pesan']['teks']; ?>',
            icon: '<?= $_SESSION['pesan']['tipe']; ?>',
            confirmButtonColor: '#4f46e5',
            confirmButtonText: 'OK',
            customClass: {
                popup: 'rounded-2xl shadow-xl',
                confirmButton: 'px-5 py-2 rounded-xl text-sm font-semibold'
            }
        });
    </script>
    <?php 
        unset($_SESSION['pesan']); 
    endif; 
    ?>


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