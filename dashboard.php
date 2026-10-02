<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$nama = $_SESSION['nama'];
$role = $_SESSION['role'];

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <title>Dashboard</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            color: #222;
        }

        /* HEADER */

        .header {
            background: white;
            border-bottom: 1px solid #ccc;

            padding: 15px 30px;

            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h2 {
            margin: 0;
            font-size: 20px;
        }

        .header small {
            color: #777;
        }

        .user {
            text-align: right;
        }

        .logout {
            display: inline-block;
            margin-top: 5px;

            padding: 7px 15px;

            background: #333;
            color: white;

            text-decoration: none;
            border-radius: 3px;
        }

        .logout:hover {
            background: #555;
        }


        /* CONTENT */

        .content {
            padding: 30px;
        }

        .content h3 {
            margin-top: 0;
        }


        /* MENU */

        .menu {
            display: grid;

            grid-template-columns: repeat(3, 1fr);

            gap: 15px;

            margin-top: 25px;
        }

        .menu a {
            background: white;

            border: 1px solid #ccc;

            padding: 25px;

            text-decoration: none;

            color: #222;

            text-align: center;

            border-radius: 3px;
        }

        .menu a:hover {
            background: #eee;
        }


        /* RESPONSIVE */

        @media (max-width: 700px) {

            .menu {
                grid-template-columns: repeat(2, 1fr);
            }

            .header {
                padding: 15px;
            }

            .content {
                padding: 20px;
            }

        }

        @media (max-width: 450px) {

            .menu {
                grid-template-columns: 1fr;
            }

            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            .user {
                text-align: left;
            }

        }

    </style>

</head>


<body>


<!-- HEADER -->

<div class="header">

    <div>

        <h2>
            Sistem Pelanggaran Siswa
        </h2>

        <small>
            Dashboard
        </small>

    </div>


    <div class="user">

        <div>
            <?= htmlspecialchars($nama) ?>
            (<?= htmlspecialchars($role) ?>)
        </div>

        <a
            href="logout.php"
            class="logout"
        >
            Logout
        </a>

    </div>

</div>


<!-- CONTENT -->

<div class="content">

    <h3>
        Dashboard
    </h3>

    <p>
        Selamat datang,
        <strong><?= htmlspecialchars($nama) ?></strong>
    </p>


    <!-- MENU -->

    <div class="menu">

        <a href="siswa/index.php">
         Kelola Siswa
         </a>


        <a href="#">
            Kelola Guru
        </a>

        <a href="#">
            Kelola Kelas
        </a>

        <a href="#">
            Tahun Ajaran
        </a>

        <a href="#">
            Wali Kelas
        </a>

        <a href="#">
            Kategori Pelanggaran
        </a>

        <a href="#">
            Jenis Pelanggaran
        </a>

        <a href="#">
            Catat Pelanggaran
        </a>

        <a href="#">
            Tindakan
        </a>

        <a href="#">
            Riwayat Pelanggaran
        </a>

        <a href="#">
            Rekap Poin
        </a>

        <a href="#">
            Laporan
        </a>

    </div>

</div>


</body>

</html>
