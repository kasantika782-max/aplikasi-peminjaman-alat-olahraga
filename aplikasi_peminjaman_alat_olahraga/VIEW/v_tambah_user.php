<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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
    <title>Formulir Pendaftaran Pengguna</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-slate-900 min-h-screen flex items-center justify-center p-4 font-sans">

    <!-- Card Form Tambah User -->
    <div class="w-full max-w-md bg-slate-800 text-slate-100 rounded-2xl shadow-2xl border border-slate-700/50 p-8 sm:p-10 my-6">

        <!-- Header / Judul -->
        <div class="text-center mb-8">
            <h2 class="text-2xl font-extrabold tracking-wider text-white uppercase">Form Tambah User</h2>
            <p class="text-slate-400 text-sm mt-1">Tambahkan pengguna baru ke dalam sistem</p>
        </div>

        <!-- Form Tambah User -->
        <form action="../controller/c_user.php?aksi=tambah" method="post" class="space-y-5">

            <!-- Input Username -->
            <div>
                <label for="username" class="block text-sm font-medium text-slate-300 mb-2">Username</label>
                <input
                    type="text"
                    id="username"
                    name="username"
                    placeholder="Masukkan username"
                    class="w-full px-4 py-3 rounded-lg bg-slate-900/80 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200"
                    required>
            </div>

            <!-- Input Password -->
            <div>
                <label for="password" class="block text-sm font-medium text-slate-300 mb-2">Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="••••••••"
                    class="w-full px-4 py-3 rounded-lg bg-slate-900/80 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200"
                    required>
            </div>

            <div>
                <label for="no_hp" class="block text-sm font-medium text-slate-300 mb-2">nomor hp</label>
                <input
                    type="no_hp"
                    id="no_hp"
                    name="no_hp"
                    placeholder="masukan nomor hp"
                    class="w-full px-4 py-3 rounded-lg bg-slate-900/80 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200"
                    required>
            </div>

            <!-- Input Role (Readonly) -->
            <div>
                <label for="role" class="block text-sm font-medium text-slate-400 mb-2">Role</label>
                <input
                    type="text"
                    id="role"
                    name="role"
                    value="peminjam"
                    readonly
                    class="w-full px-4 py-3 rounded-lg bg-slate-900/40 border border-slate-800 text-slate-400 cursor-not-allowed select-none focus:outline-none">
            </div>

            <!-- Tombol Submit & Batal -->
            <div class="pt-3 space-y-3">
                <button
                    type="submit"
                    name="tambah"
                    value="Daftar"
                    class="w-full py-3 px-4 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold rounded-lg shadow-lg hover:shadow-indigo-500/30 transition duration-200 active:scale-[0.98] uppercase tracking-wider">
                    Daftar
                </button>
                <a
                    href="v_tampilan_user.php"
                    class="block text-center text-xs text-slate-400 hover:text-slate-200 transition duration-150 py-1">
                    ← Batal
                </a>
            </div>

        </form>

    </div>

</body>

</html>