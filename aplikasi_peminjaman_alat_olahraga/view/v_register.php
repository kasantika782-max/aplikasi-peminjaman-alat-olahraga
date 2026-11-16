
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

    <title>Daftar - Peminjaman Alat</title>

    <!-- CSS REGISTER -->
    <link rel="stylesheet" href="../assets/style_register.css">

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


    <!-- REGISTER CONTAINER -->

    <main class="register-container">

        <div class="register-card">

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

            <div class="register-header">

                <h1>
                    Buat Akun
                </h1>

                <p>
                    Daftarkan akun baru untuk mulai meminjam
                </p>

            </div>


            <!-- FORM REGISTER -->

            <form
                action="../controller/c_user.php?aksi=tambah"
                method="post"
                class="register-form"
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

                    <small>
                        Username harus terdiri dari 4-20 karakter.
                    </small>

                </div>


                <!-- NOMOR TELEPON -->

                <div class="form-group">

                    <label for="no_telp">
                        Nomor Telepon
                    </label>

                    <div class="input-wrapper">

                        <span class="input-icon">
                            📱
                        </span>

                        <input
                            type="tel"
                            id="no_telp"
                            name="no_telp"
                            placeholder="Masukkan nomor telepon"
                            autocomplete="tel"
                            inputmode="numeric"
                            pattern="[0-9]{10,15}"
                            minlength="10"
                            maxlength="15"
                            title="Nomor telepon harus terdiri dari 10 sampai 15 angka."
                            oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 15)"
                            required
                        >

                    </div>

                    <small>
                        Nomor telepon harus terdiri dari 10-15 angka.
                    </small>

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
                            placeholder="Password minimal 4 karakter"
                            autocomplete="new-password"
                            minlength="4"
                            maxlength="100"
                            title="Password minimal harus 4 karakter."
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

                    <small>
                        Password minimal harus terdiri dari 4 karakter.
                    </small>

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


            <!-- LOGIN -->

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


    <!-- SWEETALERT SESSION -->

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