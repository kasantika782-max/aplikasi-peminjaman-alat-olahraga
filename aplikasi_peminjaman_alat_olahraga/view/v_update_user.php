<?php
if (session_status() === PHP_SESSION_NONE) { session_start();
require_once __DIR__ . '/../model/m_security.php';
$csrf_token = csrf_token(); }

// 1. Cek Login
if (!isset($_SESSION['role'])) {
    header("Location: v_login.php");
    exit();
}

// 2. Cek Hak Akses Admin
if ($_SESSION['role'] !== 'admin') {
    header("Location: v_daftar_alat.php");
    exit();
}

// 3. Ambil Data User Berdasarkan ID yang dikirim dari URL
require_once '../model/m_koneksi.php';
require_once '../model/m_user.php';

$db = (new m_koneksi())->koneksi;
$user_model = new m_user($db);

$id_user = $_GET['id'] ?? '';
$users = $user_model->tampil_data_by_id($id_user); // Ambil data spesifik user
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Form User</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-900 min-h-screen flex items-center justify-center p-4 font-sans">

    <!-- Card Form Edit User -->
    <div class="w-full max-w-md bg-slate-800 text-slate-100 rounded-2xl shadow-2xl border border-slate-700/50 p-8 sm:p-10 my-6">
        
        <!-- Header / Judul -->
        <div class="text-center mb-8">
            <h2 class="text-2xl font-extrabold tracking-wider text-white uppercase">Edit Form User</h2>
            <p class="text-slate-400 text-sm mt-1">Ubah data pengguna sistem</p>
        </div>

        <!-- Form Update User -->
        <form action="../controller/c_user.php?aksi=update" method="POST" class="space-y-5">
            <?= csrf_input(); ?>
            
            <!-- Hidden ID User -->
            <input type="hidden" id="id_user" name="id_user" value="<?= htmlspecialchars($users->id_user); ?>">

            <!-- Input Username -->
            <div>
                <label for="username" class="block text-sm font-medium text-slate-300 mb-2">Username</label>
                <input 
                    type="text" 
                    id="username" 
                    name="username" 
                    value="<?= htmlspecialchars($users->username); ?>" 
                    placeholder="Masukkan username"
                    class="w-full px-4 py-3 rounded-lg bg-slate-900/80 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200"
                    required
                >
            </div>

            <!-- Input Password -->
            <div>
                <label for="password" class="block text-sm font-medium text-slate-300 mb-2">Password</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    placeholder="Kosongkan jika tidak ingin merubah"
                    class="w-full px-4 py-3 rounded-lg bg-slate-900/80 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200"
                >
                <p class="text-[11px] text-slate-400 mt-1.5">*Biarkan kosong jika tidak ingin mengubah password</p>
            </div>

            <div>
                <label for="no_hp" class="block text-sm font-medium text-slate-300 mb-2">nomor hp</label>
                <input 
                    type="text" 
                    id="no_hp" 
                    name="no_hp"
                    value="<?= htmlspecialchars($users->no_hp ?? ''); ?>"
                    placeholder="masukan nomor hp"
                    class="w-full px-4 py-3 rounded-lg bg-slate-900/80 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200"
                    required
                >
            </div>

            <!-- Input Role (Readonly) -->
            <div>
                <label for="role" class="block text-sm font-medium text-slate-400 mb-2">Role</label>
                <input 
                    type="text" 
                    id="role" 
                    name="role" 
                    value="<?= htmlspecialchars($users->role); ?>" 
                    readonly 
                    class="w-full px-4 py-3 rounded-lg bg-slate-900/40 border border-slate-800 text-slate-400 cursor-not-allowed select-none focus:outline-none"
                >
            </div>

            <!-- Tombol Submit & Batal -->
            <div class="pt-3 space-y-3">
                <button 
                    type="submit" 
                    class="w-full py-3 px-4 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold rounded-lg shadow-lg hover:shadow-indigo-500/30 transition duration-200 active:scale-[0.98] uppercase tracking-wider"
                >
                    Update
                </button>
                <a 
                    href="v_tampilan_user.php" 
                    class="block text-center text-xs text-slate-400 hover:text-slate-200 transition duration-150 py-1"
                >
                    ← Batal
                </a>
            </div>

        </form>

    </div>

</body>
</html>