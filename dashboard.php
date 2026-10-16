<?php

require_once "cek_role.php";

$role = $_SESSION['role'];

?>

<!DOCTYPE html>
<html>

<head>

    <title>Dashboard</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f1f5f9;
            color: #333;
        }

        .header {
            background: #086177;
            color: white;
            padding: 18px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header b {
            font-size: 18px;
        }

        .header a {
            color: white;
            text-decoration: none;
            background: #dc2626;
            padding: 8px 15px;
            border-radius: 6px;
        }

        .container {
            width: 90%;
            max-width: 1000px;
            margin: 30px auto;
        }

        .box {
            background: white;
            padding: 25px;
            margin-bottom: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }

        h2 {
            color: #2563eb;
            margin-top: 0;
        }

        .info {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .info-box {
            background: #eff6ff;
            border-left: 4px solid #2563eb;
            padding: 12px 18px;
            border-radius: 6px;
            min-width: 200px;
        }

        .menu {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
        }

        .menu a {
            text-decoration: none;
            color: #1e293b;
            background: #f8fafc;
            border: 1px solid #dbeafe;
            border-top: 4px solid #2563eb;
            padding: 18px;
            border-radius: 8px;
            text-align: center;
        }

        .menu a:hover {
            background: #2563eb;
            color: white;
        }

        @media (max-width: 700px) {

            .menu {
                grid-template-columns: 1fr;
            }

        }

    </style>

</head>

<body>

<div class="header">

    <b>SISTEM INFORMASI PELANGGARAN SISWA</b>

    <a href="logout.php">
        Logout
    </a>

</div>


<div class="container">

    <div class="box">

        <h2>Dashboard</h2>

        <p>
            Selamat datang,
            <b><?= htmlspecialchars($_SESSION['nama']); ?></b>
        </p>

        <div class="info">

            <div class="info-box">

                Username:

                <b>
                    <?= htmlspecialchars($_SESSION['username']); ?>
                </b>

            </div>


            <div class="info-box">

                Role:

                <b>
                    <?= htmlspecialchars($role); ?>
                </b>

            </div>

        </div>

    </div>


    <div class="box">

        <h3>Menu Sistem</h3>

        <div class="menu">


            <!-- ========================= -->
            <!-- ADMIN -->
            <!-- ========================= -->

            <?php if ($role == 'admin') { ?>

                <a href="siswa.php">
                    Data Siswa
                </a>

                <a href="guru.php">
                    Data Guru
                </a>

                <a href="kelas.php">
                    Data Kelas
                </a>

                <a href="tahun_ajaran.php">
                    Tahun Ajaran
                </a>

                <a href="wali_kelas.php">
                    Wali Kelas
                </a>

                <a href="penempatan_siswa.php">
                    Penempatan Siswa
                </a>

                <a href="pelanggaran.php">
                    Data Pelanggaran
                </a>

                <a href="tindakan.php">
                    Data Tindakan
                </a>

                <a href="laporan.php">
                    Laporan
                </a>

            <?php } ?>


            <!-- ========================= -->
            <!-- GURU -->
            <!-- ========================= -->

            <?php if ($role == 'guru') { ?>

                <a href="pelanggaran.php">
                    Data Pelanggaran
                </a>

                <a href="tindakan.php">
                    Data Tindakan
                </a>

                <a href="riwayat.php">
                    Riwayat Pelanggaran
                </a>

                <a href="rekap_poin.php">
                    Rekap Poin
                </a>

            <?php } ?>


            <!-- ========================= -->
            <!-- WALI KELAS -->
            <!-- ========================= -->

            <?php if ($role == 'wali_kelas') { ?>

                <a href="pelanggaran.php">
                    Data Pelanggaran
                </a>

                <a href="laporan.php">
                    Laporan
                </a>

            <?php } ?>


            <!-- ========================= -->
            <!-- JIKA ROLE SALAH -->
            <!-- ========================= -->

            <?php if (
                $role != 'admin' &&
                $role != 'guru' &&
                $role != 'wali_kelas'
            ) { ?>

                <p>
                    Role tidak dikenali.
                </p>

            <?php } ?>


        </div>

    </div>

</div>

</body>

</html>