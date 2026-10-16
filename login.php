<?php
session_start();

if (isset($_SESSION['login'])) {
    header("Location: dashboard.php");
    exit;
}

$pesan = $_GET['pesan'] ?? '';
?>

<!DOCTYPE html>
<html>
<head>

    <title>Login</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #eef4f7;
        }

        .login-box {
            width: 370px;
            margin: 120px auto;
            background: white;
            padding: 28px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.12);
        }

        h2 {
            text-align: center;
            color: #086177;
            margin-bottom: 25px;
        }

        label {
            display: block;
            margin-bottom: 7px;
        }

        input {
            width: 100%;
            padding: 11px;
            margin-bottom: 18px;
            border: 1px solid #bbb;
            border-radius: 5px;
        }

        input:focus {
            border-color: #086177;
            outline: none;
        }

        button {
            width: 100%;
            padding: 11px;
            border: none;
            border-radius: 5px;
            background: #086177;
            color: white;
            cursor: pointer;
        }

        button:hover {
            background: #064d5f;
        }

        .pesan {
            background: #fee2e2;
            color: #b91c1c;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 18px;
            text-align: center;
        }

    </style>

</head>

<body>

<div class="login-box">

    <h2>LOGIN</h2>

    <?php if ($pesan != '') { ?>

        <div class="pesan">
            <?= htmlspecialchars($pesan); ?>
        </div>

    <?php } ?>

    <form action="proses_login.php" method="POST">

        <label>Username</label>

        <input
            type="text"
            name="username"
            placeholder="Masukkan username"
            required
        >

        <label>Password</label>

        <input
            type="password"
            name="password"
            placeholder="Masukkan password"
            required
        >

        <button type="submit">
            LOGIN
        </button>

    </form>

</div>

</body>
</html>