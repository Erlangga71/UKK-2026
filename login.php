<?php

session_start();
include "database.php";

$pesan = "";

if (isset($_POST['login'])) {

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email == "" || $password == "") {

        $pesan = "Email dan password wajib diisi.";

    } else {

        $email = mysqli_real_escape_string($koneksi, $email);

        $query = mysqli_query(
            $koneksi,
            "SELECT id, nama, email, password, role, aktif
             FROM users
             WHERE email = '$email'
             LIMIT 1"
        );

        if (!$query) {
            die("Error database: " . mysqli_error($koneksi));
        }

        if (mysqli_num_rows($query) == 1) {

            $user = mysqli_fetch_assoc($query);

            if ($user['aktif'] != 1) {

                $pesan = "Akun tidak aktif.";

            } elseif (password_verify($password, $user['password'])) {

                $_SESSION['login'] = true;
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['nama'] = $user['nama'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];

                header("Location: dashboard.php");
                exit;

            } else {

                $pesan = "Email atau password salah.";

            }

        } else {

            $pesan = "Email atau password salah.";

        }
    }
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Login Sistem</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f4f4;
        }

        .login-container {
            min-height: 100vh;

            display: flex;

            justify-content: center;

            align-items: center;
        }

        .login-box {
            width: 360px;

            background: white;

            padding: 30px;

            border: 1px solid #ddd;

            border-radius: 8px;
        }

        h1 {
            text-align: center;

            margin-top: 0;

            font-size: 25px;
        }

        .deskripsi {
            text-align: center;

            color: #777;

            font-size: 14px;

            margin-bottom: 25px;
        }

        .error {
            background: #f8d7da;

            border: 1px solid #f5c2c7;

            color: #842029;

            padding: 10px;

            border-radius: 5px;

            margin-bottom: 15px;

            font-size: 14px;
        }

        label {
            display: block;

            font-weight: bold;

            font-size: 14px;

            margin-bottom: 6px;
        }

        input {
            width: 100%;

            padding: 10px;

            border: 1px solid #ccc;

            border-radius: 5px;

            margin-bottom: 15px;

            font-size: 14px;
        }

        button {
            width: 100%;

            padding: 11px;

            border: none;

            border-radius: 5px;

            background: #176b80;

            color: white;

            cursor: pointer;

            font-size: 14px;
        }

        button:hover {
            background: #0d5364;
        }

    </style>

</head>

<body>

<div class="login-container">

    <div class="login-box">

        <h1>Login Sistem</h1>

        <div class="deskripsi">
            Sistem Informasi Pelanggaran Siswa
        </div>

        <?php if ($pesan != "") { ?>

            <div class="error">
                <?= htmlspecialchars($pesan); ?>
            </div>

        <?php } ?>

        <form method="POST">

            <label>Email</label>

            <input
                type="email"
                name="email"
                placeholder="Masukkan email"
                required
            >

            <label>Password</label>

            <input
                type="password"
                name="password"
                placeholder="Masukkan password"
                required
            >

            <button
                type="submit"
                name="login"
            >
                Login
            </button>

        </form>

    </div>

</div>

</body>

</html>