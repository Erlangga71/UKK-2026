<?php

session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$error = $_GET['error'] ?? '';

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <title>Login - Sistem Pelanggaran Siswa</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f4f4;

            display: flex;
            justify-content: center;
            align-items: center;

            min-height: 100vh;
        }

        .login-box {
            width: 380px;

            background: white;

            border: 1px solid #ddd;
            border-radius: 6px;

            padding: 30px;

            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .login-box h2 {
            margin: 0;

            text-align: center;

            font-size: 22px;
        }

        .subtitle {
            text-align: center;

            color: #777;

            margin-top: 8px;
            margin-bottom: 25px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;

            margin-bottom: 7px;

            font-size: 14px;
            font-weight: bold;
        }

        .form-group input {
            width: 100%;

            height: 42px;

            padding: 0 12px;

            border: 1px solid #bbb;
            border-radius: 4px;

            font-size: 14px;

            outline: none;
        }

        .form-group input:focus {
            border-color: #555;
        }

        button {
            width: 100%;

            height: 42px;

            margin-top: 5px;

            background: #333;

            color: white;

            border: none;

            border-radius: 4px;

            font-size: 14px;

            cursor: pointer;
        }

        button:hover {
            background: #222;
        }

        .error {
            background: #f8d7da;

            color: #842029;

            border: 1px solid #f5c2c7;

            padding: 10px;

            margin-bottom: 18px;

            border-radius: 4px;

            font-size: 14px;
        }

    </style>

</head>


<body>


<div class="login-box">

    <h2>
        Sistem Pelanggaran Siswa
    </h2>

    <div class="subtitle">
        Silakan login untuk melanjutkan
    </div>


    <?php if ($error != ''): ?>

        <div class="error">
            <?= htmlspecialchars($error) ?>
        </div>

    <?php endif; ?>


    <form
        action="proses_login.php"
        method="POST"
    >

        <div class="form-group">

            <label>
                Username
            </label>

            <input
                type="text"
                name="username"
                placeholder="Masukkan username"
                required
            >

        </div>


        <div class="form-group">

            <label>
                Password
            </label>

            <input
                type="password"
                name="password"
                placeholder="Masukkan password"
                required
            >

        </div>


        <button type="submit">
            Login
        </button>

    </form>

</div>


</body>

</html>
