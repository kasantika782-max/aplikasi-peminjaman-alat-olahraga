
<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login - Peminjaman Alat</title>

    <!-- CSS LOGIN -->
    <link rel="stylesheet" href="../assets/style_login.css?v=10">

    <!-- SWEETALERT -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>

<body>

    <!-- BACKGROUND -->

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


    <!-- LOGIN CONTAINER -->

    <main class="login-container">

        <div class="login-card">

            <!-- LOGO -->

<!-- LOGO SANGKURIANG -->

<div class="logo-wrapper">

    <img
        src="../assets/IMG_20170516_132915.JPG"
        alt="Logo SMK Sangkuriang 1 Cimahi"
        style="
            width: 100px;
            height: 100px;
            object-fit: contain;
            border-radius: 50%;
            background: white;
            padding: 5px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        "
    >

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
                            placeholder="Username minimal 4 karakter"
                            autocomplete="username"
                            pattern="[A-Za-z0-9]{4,20}"
                            minlength="4"
                            maxlength="20"
                            title="Username minimal 4 dan maksimal 20 karakter. Hanya boleh menggunakan huruf dan angka."
                            oninput="this.value = this.value.replace(/[^A-Za-z0-9]/g, '').slice(0, 20)"
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


    <!-- SWEETALERT SESSION -->

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


    <!-- SHOW / HIDE PASSWORD -->

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