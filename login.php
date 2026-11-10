
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

    <title>Login | Sistem Pelanggaran Siswa</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #123c50, #176b80, #234c70);
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .login-container {
            width: 100%;
            max-width: 420px;
        }

        .login-box {
            background: white;
            padding: 38px 35px;
            border-radius: 12px;
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.2);
        }

        .logo {
            width: 65px;
            height: 65px;
            background: #e5f2f5;
            color: #176b80;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
            margin: 0 auto 18px;
        }

        h1 {
            text-align: center;
            font-size: 23px;
            margin: 0 0 10px;
            color: #263746;
        }

        .deskripsi {
            text-align: center;
            color: #777;
            font-size: 13px;
            line-height: 1.6;
            margin-bottom: 28px;
        }

        .garis {
            width: 45px;
            height: 3px;
            background: #176b80;
            margin: 0 auto 25px;
            border-radius: 5px;
        }

        .error {
            background: #fce8e8;
            border: 1px solid #f5bcbc;
            color: #a32222;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 18px;
            font-size: 13px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        label {
            display: block;
            font-size: 14px;
            font-weight: bold;
            color: #344054;
            margin-bottom: 8px;
        }

        input {
            width: 100%;
            padding: 13px;
            border: 1px solid #d0d5dd;
            border-radius: 6px;
            font-size: 14px;
            outline: none;
            transition: 0.2s;
        }

        input:focus {
            border-color: #176b80;
            box-shadow: 0 0 0 3px rgba(23, 107, 128, 0.12);
        }

        .password-wrapper {
            position: relative;
        }

        .password-wrapper input {
            padding-right: 65px;
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
            width: auto;
        }

        .btn-login {
            width: 100%;
            padding: 13px;
            border: none;
            border-radius: 6px;
            background: #176b80;
            color: white;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 8px;
            transition: 0.2s;
        }

        .btn-login:hover {
            background: #105466;
            transform: translateY(-1px);
        }

        .footer {
            text-align: center;
            color: #888;
            font-size: 12px;
            margin-top: 25px;
            line-height: 1.6;
        }

        @media (max-width: 480px) {

            .login-box {
                padding: 30px 23px;
            }

            h1 {
                font-size: 21px;
            }

        }

    </style>

</head>

<body>

<div class="login-container">

    <div class="login-box">

        <div class="logo">
            ⚖
        </div>

        <h1>Selamat Datang</h1>

        <div class="deskripsi">
            Sistem Informasi<br>
            Pelanggaran Siswa
        </div>

        <div class="garis"></div>

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
                LOGIN
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
