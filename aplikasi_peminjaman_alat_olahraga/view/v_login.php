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

    <title>Login - Peminjaman Alat</title>

    <!-- CSS LOGIN -->
    <link rel="stylesheet" href="../ASSETS/style_login.css?v=10">

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
         LOGIN CONTAINER
    ========================================== -->

    <main class="login-container">

        <div class="login-card">

            <!-- LOGO -->

            <div class="logo-wrapper">

                <div class="logo">
                    🔐
                </div>

            </div>


            <!-- HEADER -->

            <div class="login-header">

                <h1>
                    Selamat Datang
                </h1>

                <p>
                    Silakan login ke akun Anda
                </p>

            </div>


            <!-- FORM LOGIN -->

            <form
                action="../controller/c_login.php?aksi=proses_login"
                method="post"
                class="login-form"
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
                            autocomplete="current-password"
                            required
                        >

                        <button
                            type="button"
                            class="show-password"
                            onclick="togglePassword()"
                            aria-label="Tampilkan password"
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


                <!-- BUTTON LOGIN -->

                <button
                    type="submit"
                    class="login-button"
                >

                    <span>
                        Login
                    </span>

                    <span class="button-arrow">
                        →
                    </span>

                </button>

            </form>


            <!-- REGISTER -->

            <div class="register">

                <span>
                    Belum punya akun?
                </span>

                <a href="v_register.php">
                    Daftar disini
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

                background: '#111827',

                color: '#ffffff',

                customClass: {

                    popup: 'custom-alert',

                    confirmButton: 'alert-button'

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