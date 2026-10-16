<?php

require_once "cek_role.php";

$role = $_SESSION['role'] ?? '';

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

        /* HEADER */

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
            border-radius: 5px;
        }

        /* CONTAINER */

        .container {
            width: 90%;
            max-width: 1000px;
            margin: 30px auto;
        }

        /* BOX */

        .box {
            background: white;
            padding: 25px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 6px;
        }

        h2 {
            margin-top: 0;
            color: #086177;
        }

        h3 {
            margin-top: 0;
        }

        /* INFORMASI USER */

        .info {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .info-box {
            background: #f8fafc;
            border: 1px solid #ddd;
            padding: 12px 18px;
            min-width: 220px;
            border-radius: 4px;
        }

        /* MENU */

        .menu {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .menu a {
            display: flex;
            align-items: center;

            text-decoration: none;

            color: #333;

            background: #f8fafc;

            border: 1px solid #ccc;

            padding: 12px;

            border-radius: 5px;
        }

        .menu a:hover {
            background: #086177;
            color: white;
        }

        /* NOMOR */

        .nomor {
            width: 35px;
            height: 35px;

            display: flex;
            align-items: center;
            justify-content: center;

            background: #086177;
            color: white;

            border-radius: 5px;

            margin-right: 15px;

            font-weight: bold;

            flex-shrink: 0;
        }

        /* NAMA MENU */

        .nama-menu {
            font-weight: bold;
        }

        /* JUDUL BAGIAN */

        .judul-menu {
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #ddd;
        }

        @media (max-width: 700px) {

            .container {
                width: 95%;
            }

            .info {
                flex-direction: column;
            }

            .info-box {
                width: 100%;
            }

        }

    </style>

</head>

<body>


<!-- HEADER -->

<div class="header">

    <b>SISTEM INFORMASI PELANGGARAN SISWA</b>

    <a href="logout.php">
        Logout
    </a>

</div>


<div class="container">


    <!-- INFORMASI -->

    <div class="box">

        <h2>Dashboard</h2>

        <p>
            Selamat datang,
            <b>
                <?= htmlspecialchars($_SESSION['nama'] ?? 'Pengguna'); ?>
            </b>
        </p>

        <div class="info">

            <div class="info-box">

                Username:

                <b>
                    <?= htmlspecialchars($_SESSION['username'] ?? '-'); ?>
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


    <!-- MENU SISTEM -->

    <div class="box">

        <h3 class="judul-menu">
            Menu Sistem
        </h3>


        <div class="menu">


            <!-- ================================= -->
            <!-- ADMIN -->
            <!-- URUTAN FITUR ADMIN -->
            <!-- ================================= -->

            <?php if ($role == 'admin') { ?>


                <a href="siswa.php">

                    <div class="nomor">1</div>

                    <div class="nama-menu">
                        Data Siswa
                    </div>

                </a>


                <a href="guru.php">

                    <div class="nomor">2</div>

                    <div class="nama-menu">
                        Data Guru
                    </div>

                </a>


                <a href="kelas.php">

                    <div class="nomor">3</div>

                    <div class="nama-menu">
                        Data Kelas
                    </div>

                </a>


                <a href="tahun_ajaran.php">

                    <div class="nomor">4</div>

                    <div class="nama-menu">
                        Tahun Ajaran
                    </div>

                </a>


                <a href="wali_kelas.php">

                    <div class="nomor">5</div>

                    <div class="nama-menu">
                        Wali Kelas
                    </div>

                </a>


                <a href="penempatan_siswa.php">

                    <div class="nomor">6</div>

                    <div class="nama-menu">
                        Penempatan Siswa
                    </div>

                </a>


                <a href="pelanggaran.php">

                    <div class="nomor">7</div>

                    <div class="nama-menu">
                        Data Pelanggaran
                    </div>

                </a>


                <a href="tindakan.php">

                    <div class="nomor">8</div>

                    <div class="nama-menu">
                        Data Tindakan
                    </div>

                </a>


                <a href="laporan.php">

                    <div class="nomor">9</div>

                    <div class="nama-menu">
                        Laporan
                    </div>

                </a>


            <?php } ?>


            <!-- ================================= -->
            <!-- GURU -->
            <!-- ================================= -->

            <?php if ($role == 'guru') { ?>


                <a href="pelanggaran.php">

                    <div class="nomor">1</div>

                    <div class="nama-menu">
                        Data Pelanggaran
                    </div>

                </a>


                <a href="tindakan.php">

                    <div class="nomor">2</div>

                    <div class="nama-menu">
                        Data Tindakan
                    </div>

                </a>


                <a href="riwayat.php">

                    <div class="nomor">3</div>

                    <div class="nama-menu">
                        Riwayat Pelanggaran
                    </div>

                </a>


                <a href="rekap_poin.php">

                    <div class="nomor">4</div>

                    <div class="nama-menu">
                        Rekap Poin
                    </div>

                </a>


            <?php } ?>


            <!-- ================================= -->
            <!-- WALI KELAS -->
            <!-- ================================= -->

            <?php if ($role == 'wali_kelas') { ?>


                <a href="pelanggaran.php">

                    <div class="nomor">1</div>

                    <div class="nama-menu">
                        Data Pelanggaran
                    </div>

                </a>


                <a href="laporan.php">

                    <div class="nomor">2</div>

                    <div class="nama-menu">
                        Laporan
                    </div>

                </a>


            <?php } ?>


            <!-- ================================= -->
            <!-- ROLE TIDAK DIKENALI -->
            <!-- ================================= -->

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