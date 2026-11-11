
<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "database.php";

$pesan = "";

if (isset($_POST['login'])) {

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === "" || $password === "") {

        $pesan = "Email dan password wajib diisi.";

    } else {

        $stmt = mysqli_prepare(
            $koneksi,
            "SELECT id, nama, email, password, role, aktif
             FROM users
             WHERE email = ?
             LIMIT 1"
        );

        if (!$stmt) {
            die("Error database: " . mysqli_error($koneksi));
        }

        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);

        $query = mysqli_stmt_get_result($stmt);

        if (!$query) {
            die("Error database: " . mysqli_error($koneksi));
        }

        if (mysqli_num_rows($query) === 1) {

            $user = mysqli_fetch_assoc($query);

            if ($user['aktif'] != 1) {

                $pesan = "Akun tidak aktif.";

            } elseif (!password_verify($password, $user['password'])) {

                $pesan = "Email atau password salah.";

            } elseif (!in_array(
                $user['role'],
                ['admin', 'guru', 'wali_kelas'],
                true
            )) {

                $pesan = "Akun ini tidak memiliki akses login.";

            } else {

                session_regenerate_id(true);

                $_SESSION['login'] = true;
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['id'] = $user['id'];
                $_SESSION['nama'] = $user['nama'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];

                header("Location: dashboard.php");
                exit;
            }

        } else {

            $pesan = "Email atau password salah.";

        }

        mysqli_stmt_close($stmt);
    }
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login | Sistem Informasi Pelanggaran Siswa</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: Arial, sans-serif;
            background: #edf2f7;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 25px;
        }

        .login-container {
            width: 100%;
            max-width: 950px;
            min-height: 540px;
            display: flex;
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 15px 45px rgba(30, 50, 70, 0.12);
        }

        /* PANEL KIRI */

        .panel-kiri {
            width: 45%;
            background: linear-gradient(145deg, #173b59, #176b80);
            padding: 55px 40px;
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .label-sistem {
            display: inline-block;
            width: fit-content;
            padding: 8px 13px;
            border: 1px solid rgba(255,255,255,0.35);
            border-radius: 20px;
            font-size: 12px;
            letter-spacing: 1px;
            margin-bottom: 25px;
        }

        .panel-kiri h1 {
            font-size: 32px;
            line-height: 1.35;
            margin: 0 0 18px;
        }

        .panel-kiri p {
            color: #e0edf3;
            font-size: 14px;
            line-height: 1.8;
            margin: 0;
        }

        .garis-panel {
            width: 55px;
            height: 4px;
            background: #a8d8e5;
            border-radius: 5px;
            margin: 28px 0;
        }

        .informasi-panel {
            font-size: 13px;
            line-height: 1.8;
            color: #e0edf3;
        }

        /* PANEL KANAN */

        .panel-kanan {
            width: 55%;
            padding: 55px 55px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .judul-login {
            margin-bottom: 30px;
        }

        .judul-login h2 {
            color: #203448;
            font-size: 27px;
            margin: 0 0 10px;
        }

        .judul-login p {
            color: #7b8794;
            font-size: 14px;
            margin: 0;
            line-height: 1.6;
        }

        .error {
            background: #fff0f0;
            border: 1px solid #f1b9b9;
            color: #a32222;
            padding: 12px 14px;
            border-radius: 7px;
            margin-bottom: 20px;
            font-size: 13px;
        }

        .form-group {
            margin-bottom: 21px;
        }

        label {
            display: block;
            font-size: 13px;
            font-weight: bold;
            color: #344054;
            margin-bottom: 9px;
        }

        input {
            width: 100%;
            padding: 14px 15px;
            border: 1px solid #d6dde5;
            border-radius: 7px;
            font-size: 14px;
            outline: none;
            transition: 0.2s;
            background: #fbfcfe;
        }

        input:focus {
            border-color: #176b80;
            background: white;
            box-shadow: 0 0 0 3px rgba(23, 107, 128, 0.10);
        }

        .password-wrapper {
            position: relative;
        }

        .password-wrapper input {
            padding-right: 75px;
        }

        .lihat-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            border: none;
            background: none;
            color: #176b80;
            cursor: pointer;
            font-size: 12px;
            font-weight: bold;
            padding: 5px;
        }

        .btn-login {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 7px;
            background: #176b80;
            color: white;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 8px;
            transition: 0.2s;
        }

        .btn-login:hover {
            background: #105466;
            box-shadow: 0 5px 15px rgba(23, 107, 128, 0.2);
        }

        .footer {
            text-align: center;
            color: #98a2b3;
            font-size: 11px;
            margin-top: 35px;
            line-height: 1.7;
        }

        /* RESPONSIVE */

        @media (max-width: 700px) {

            body {
                padding: 15px;
            }

            .login-container {
                max-width: 440px;
                flex-direction: column;
            }

            .panel-kiri {
                width: 100%;
                padding: 30px 25px;
            }

            .panel-kiri h1 {
                font-size: 25px;
            }

            .panel-kiri p {
                font-size: 13px;
            }

            .garis-panel,
            .informasi-panel {
                display: none;
            }

            .panel-kanan {
                width: 100%;
                padding: 35px 25px;
            }

            .judul-login h2 {
                font-size: 24px;
            }
        }

    </style>

</head>

<body>

    <div class="login-container">

        <!-- PANEL KIRI -->

        <div class="panel-kiri">

            <div class="label-sistem">
                SISTEM INFORMASI SEKOLAH
            </div>

            <h1>
                Pelanggaran<br>
                Siswa
            </h1>

            <p>
                Sistem Informasi Pelanggaran Siswa merupakan
                sistem untuk membantu sekolah dalam mencatat,
                mengelola, dan memantau pelanggaran siswa
                secara terstruktur.
            </p>

            <div class="garis-panel"></div>

            <div class="informasi-panel">
                Pengelolaan data pelanggaran siswa,
                pencatatan tindakan, dan penyajian laporan
                dalam satu sistem.
            </div>

        </div>

        <!-- PANEL KANAN -->

        <div class="panel-kanan">

            <div class="judul-login">

                <h2>Selamat Datang</h2>

                <p>
                    Silakan masuk menggunakan email dan password
                    akun Anda untuk melanjutkan.
                </p>

            </div>

            <?php if ($pesan != "") { ?>

                <div class="error">
                    <?= htmlspecialchars($pesan); ?>
                </div>

            <?php } ?>

            <form method="POST">

                <div class="form-group">

                    <label>Email</label>

                    <input
                        type="email"
                        name="email"
                        placeholder="Masukkan email Anda"
                        value="<?= htmlspecialchars($_POST['email'] ?? ''); ?>"
                        autocomplete="username"
                        required
                    >

                </div>

                <div class="form-group">

                    <label>Password</label>

                    <div class="password-wrapper">

                        <input
                            type="password"
                            name="password"
                            id="password"
                            placeholder="Masukkan password"
                            autocomplete="current-password"
                            required
                        >

                        <button
                            type="button"
                            class="lihat-password"
                            onclick="togglePassword()"
                        >
                            Lihat
                        </button>

                    </div>

                </div>

                <button
                    type="submit"
                    name="login"
                    class="btn-login"
                >
                    MASUK KE SISTEM
                </button>

            </form>

            <div class="footer">
                Sistem Informasi Pelanggaran Siswa<br>
                © 2026 UKK - Semua Hak Dilindungi
            </div>

        </div>

    </div>

    <script>

        function togglePassword() {

            const password = document.getElementById("password");
            const tombol = document.querySelector(".lihat-password");

            if (password.type === "password") {
                password.type = "text";
                tombol.innerText = "Sembunyi";
            } else {
                password.type = "password";
                tombol.innerText = "Lihat";
            }

        }

    </script>

</body>
</html>
