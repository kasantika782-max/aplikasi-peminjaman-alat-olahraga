<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
require_once __DIR__ . '/../model/m_security.php';
$csrf_token = csrf_token();
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Daftar - Peminjaman Alat</title>

    <!-- CSS REGISTER -->
    <link rel="stylesheet" href="../ASSETS/style_register.css">

    <!-- SWEETALERT -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>


<body>

    <!-- =========================================
         BACKGROUND
    ========================================== -->

    <div class="background">

        <div class="blob blob-1"></div>

        <div class="blob blob-2"></div>

        <div class="blob blob-3"></div>

        <div class="particles">

            <span></span>
            <span></span>
            <span></span>
            <span></span>
            <span></span>
            <span></span>
            <span></span>
            <span></span>

        </div>

    </div>


    <!-- =========================================
         REGISTER CONTAINER
    ========================================== -->

    <main class="register-container">

        <div class="register-card">


            <!-- =================================
                 LOGO
            ================================== -->

            <div class="logo-wrapper">

                <div class="logo">
                    📝
                </div>

            </div>


            <!-- =================================
                 HEADER
            ================================== -->

            <div class="register-header">

                <h1>
                    Buat Akun
                </h1>

                <p>
                    Daftarkan akun baru untuk mulai meminjam
                </p>

            </div>


            <!-- =================================
                 FORM REGISTER
            ================================== -->

            <form
                action="../controller/c_user.php?aksi=tambah"
                method="post"
                class="register-form"
            >
            <?= csrf_input(); ?>


                <!-- USERNAME -->

                <div class="form-group">

                    <label for="username">
                        Username
                    </label>

                    <div class="input-wrapper">

                        <span class="input-icon">
                            👤
                        </span>

                        <input
                            type="text"
                            id="username"
                            name="username"
                            placeholder="Masukkan username"
                            autocomplete="username"
                            required
                        >

                    </div>

                </div>


                <!-- PASSWORD -->

                <div class="form-group">

                    <label for="password">
                        Password
                    </label>

                    <div class="input-wrapper">

                        <span class="input-icon">
                            🔑
                        </span>

                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Masukkan password"
                            autocomplete="new-password"
                            required
                        >

                        <button
                            type="button"
                            class="show-password"
                            onclick="togglePassword()"
                        >
                            👁
                        </button>

                    </div>

                </div>


                <!-- NOMOR HP -->

                <div class="form-group">

                    <label for="no_hp">
                        Nomor HP
                    </label>

                    <div class="input-wrapper">

                        <span class="input-icon">
                            📱
                        </span>

                        <input
                            type="tel"
                            id="no_hp"
                            name="no_hp"
                            placeholder="Masukkan nomor HP"
                            autocomplete="tel"
                            required
                        >

                    </div>

                </div>


                <!-- ROLE -->

                <div class="form-group">

                    <label for="role">
                        Role
                    </label>

                    <div class="input-wrapper">

                        <span class="input-icon">
                            🛡️
                        </span>

                        <input
                            type="text"
                            id="role"
                            name="role"
                            value="peminjam"
                            readonly
                            class="readonly-input"
                        >

                    </div>

                    <small>
                        Role otomatis sebagai peminjam
                    </small>

                </div>


                <!-- BUTTON -->

                <button
                    type="submit"
                    name="tambah"
                    value="Daftar"
                    class="register-button"
                >

                    <span>
                        Daftar Sekarang
                    </span>

                    <span class="button-arrow">
                        →
                    </span>

                </button>


            </form>


            <!-- =================================
                 LOGIN
            ================================== -->

            <div class="login-link">

                <span>
                    Sudah punya akun?
                </span>

                <a href="v_login.php">
                    Login di sini
                </a>

            </div>


        </div>

    </main>


    <!-- =========================================
         SWEETALERT SESSION
    ========================================== -->

    <?php if (isset($_SESSION['pesan'])): ?>

        <script>

            Swal.fire({

                title: <?= json_encode($_SESSION['pesan']['judul']); ?>,

                text: <?= json_encode($_SESSION['pesan']['teks']); ?>,

                icon: <?= json_encode($_SESSION['pesan']['tipe']); ?>,

                confirmButtonColor: '#6366f1',

                confirmButtonText: 'OK',

                background: '#0f172a',

                color: '#ffffff',

                customClass: {
                    popup: 'custom-alert'
                }

            });

        </script>

    <?php

        unset($_SESSION['pesan']);

    endif;

    ?>


    <!-- =========================================
         SHOW / HIDE PASSWORD
    ========================================== -->

    <script>

        function togglePassword() {

            const password =
                document.getElementById("password");

            const button =
                document.querySelector(".show-password");


            if (password.type === "password") {

                password.type = "text";

                button.textContent = "🙈";

            } else {

                password.type = "password";

                button.textContent = "👁";

            }

        }

    </script>


</body>

</html>