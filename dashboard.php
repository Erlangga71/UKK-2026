<?php

require_once "cek_role.php";
include "database.php";

$role = $_SESSION['role'] ?? '';


// Total siswa aktif
$query_siswa = mysqli_query(
    $koneksi,
    "SELECT COUNT(*) AS total FROM siswa WHERE aktif = 1"
);

$data_siswa = mysqli_fetch_assoc($query_siswa);
$total_siswa = $data_siswa['total'] ?? 0;


// Total pelanggaran
$query_pelanggaran = mysqli_query(
    $koneksi,
    "SELECT COUNT(*) AS total FROM pelanggaran"
);

$data_pelanggaran = mysqli_fetch_assoc($query_pelanggaran);
$total_pelanggaran = $data_pelanggaran['total'] ?? 0;


// Pelanggaran bulan ini
$bulan_ini = date('m');
$tahun_ini = date('Y');

$query_bulan = mysqli_query(
    $koneksi,
    "SELECT COUNT(*) AS total
     FROM pelanggaran
     WHERE MONTH(tanggal) = '$bulan_ini'
     AND YEAR(tanggal) = '$tahun_ini'"
);

$data_bulan = mysqli_fetch_assoc($query_bulan);
$pelanggaran_bulan = $data_bulan['total'] ?? 0;


// Siswa yang mempunyai pelanggaran
$query_bermasalah = mysqli_query(
    $koneksi,
    "SELECT COUNT(DISTINCT siswa_id) AS total
     FROM pelanggaran"
);

$data_bermasalah = mysqli_fetch_assoc($query_bermasalah);
$siswa_bermasalah = $data_bermasalah['total'] ?? 0;

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
            background: #F3F6F8;
            color: #333;
        }


        /* =========================
           HEADER
        ========================= */

        .header {
            background: #687280;
            color: white;
            padding: 18px 30px;
        }

        .header b {
            font-size: 18px;
        }

        .logout {
            float: right;
            color: white;
            text-decoration: none;
            background: #B85C5C;
            padding: 8px 14px;
            border-radius: 4px;
        }

        .logout:hover {
            background: #9F4D4D;
        }


        /* =========================
           CONTAINER
        ========================= */

        .container {
            width: 90%;
            max-width: 1100px;
            margin: 30px auto;
        }


        /* =========================
           JUDUL
        ========================= */

        .judul {
            margin-bottom: 20px;
        }

        .judul h2 {
            margin: 0 0 8px 0;
            color: #4F6D7A;
        }

        .judul p {
            margin: 6px 0;
        }


        /* =========================
           STATISTIK
        ========================= */

        .statistik {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 25px;
        }

        .stat-box {
            background: white;
            border: 1px solid #D9E0E5;
            padding: 20px;
            border-radius: 6px;
            border-left: 5px solid #4F6D7A;
        }

        /* Siswa */

        .stat-box:nth-child(1) {
            border-left-color: #5B8C72;
        }

        /* Pelanggaran */

        .stat-box:nth-child(2) {
            border-left-color: #4F6D7A;
        }

        /* Bulan ini */

        .stat-box:nth-child(3) {
            border-left-color: #D5A84A;
        }

        /* Siswa bermasalah */

        .stat-box:nth-child(4) {
            border-left-color: #B85C5C;
        }

        .judul-stat {
            color: #666;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .angka {
            font-size: 28px;
            font-weight: bold;
            color: #4F6D7A;
        }


        /* =========================
           BOX MENU
        ========================= */

        .box {
            background: white;
            border: 1px solid #D9E0E5;
            padding: 20px;
            border-radius: 6px;
        }

        .box h3 {
            margin-top: 0;
            padding-bottom: 12px;
            border-bottom: 2px solid #E5E7EB;
            color: #4F6D7A;
        }


        /* =========================
           MENU
        ========================= */

        .menu {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .menu a {
            display: block;
            text-decoration: none;
            color: #333;
            background: #F8FAFB;
            border: 1px solid #D9E0E5;
            padding: 15px;
            border-radius: 5px;
            transition: 0.2s;
        }

        .menu a:hover {
            background: #E8F1F5;
            border-color: #4F6D7A;
        }

        .menu-nomor {
            display: inline-block;
            width: 30px;
            font-weight: bold;
            color: #4F6D7A;
        }

        .menu-nama {
            font-weight: bold;
        }


        /* =========================
           RESPONSIVE
        ========================= */

        @media (max-width: 800px) {

            .statistik {
                grid-template-columns: 1fr 1fr;
            }

            .menu {
                grid-template-columns: 1fr;
            }

        }


        @media (max-width: 500px) {

            .statistik {
                grid-template-columns: 1fr;
            }

            .container {
                width: 94%;
            }

        }

    </style>

</head>

<body>


<!-- =========================
     HEADER
========================= -->

<div class="header">

    <b>SISTEM INFORMASI PELANGGARAN SISWA</b>

    <a href="logout.php" class="logout">
        Logout
    </a>

</div>


<div class="container">


    <!-- =========================
         INFORMASI USER
    ========================= -->

    <div class="judul">

        <h2>Dashboard</h2>

        <p>
            Selamat datang,
            <b>
                <?= htmlspecialchars($_SESSION['nama'] ?? 'Pengguna'); ?>
            </b>
        </p>

        <p>

            Username:
            <b>
                <?= htmlspecialchars($_SESSION['username'] ?? '-'); ?>
            </b>

            &nbsp;&nbsp;

            Role:
            <b>
                <?= htmlspecialchars($role); ?>
            </b>

        </p>

    </div>


    <!-- =========================
         STATISTIK
    ========================= -->

    <div class="statistik">


        <div class="stat-box">

            <div class="judul-stat">
                Total Siswa Aktif
            </div>

            <div class="angka">
                <?= $total_siswa; ?>
            </div>

        </div>


        <div class="stat-box">

            <div class="judul-stat">
                Total Pelanggaran
            </div>

            <div class="angka">
                <?= $total_pelanggaran; ?>
            </div>

        </div>


        <div class="stat-box">

            <div class="judul-stat">
                Pelanggaran Bulan Ini
            </div>

            <div class="angka">
                <?= $pelanggaran_bulan; ?>
            </div>

        </div>


        <div class="stat-box">

            <div class="judul-stat">
                Siswa Bermasalah
            </div>

            <div class="angka">
                <?= $siswa_bermasalah; ?>
            </div>

        </div>


    </div>


    <!-- =========================
         MENU ADMIN
    ========================= -->

    <?php if ($role == 'admin') { ?>

    <div class="box">

        <h3>Menu Sistem</h3>

        <div class="menu">


            <a href="siswa.php">

                <span class="menu-nomor">
                    1.
                </span>

                <span class="menu-nama">
                    Data Siswa
                </span>

            </a>


            <a href="guru.php">

                <span class="menu-nomor">
                    2.
                </span>

                <span class="menu-nama">
                    Data Guru
                </span>

            </a>


            <a href="kelas.php">

                <span class="menu-nomor">
                    3.
                </span>

                <span class="menu-nama">
                    Data Kelas
                </span>

            </a>


            <a href="tahun_ajaran.php">

                <span class="menu-nomor">
                    4.
                </span>

                <span class="menu-nama">
                    Tahun Ajaran
                </span>

            </a>


            <a href="wali_kelas.php">

                <span class="menu-nomor">
                    5.
                </span>

                <span class="menu-nama">
                    Wali Kelas
                </span>

            </a>


            <a href="penempatan_siswa.php">

                <span class="menu-nomor">
                    6.
                </span>

                <span class="menu-nama">
                    Penempatan Siswa
                </span>

            </a>


            <a href="pelanggaran.php">

                <span class="menu-nomor">
                    7.
                </span>

                <span class="menu-nama">
                    Data Pelanggaran
                </span>

            </a>


            <a href="tindakan.php">

                <span class="menu-nomor">
                    8.
                </span>

                <span class="menu-nama">
                    Data Tindakan
                </span>

            </a>


            <a href="laporan.php">

                <span class="menu-nomor">
                    9.
                </span>

                <span class="menu-nama">
                    Laporan
                </span>

            </a>


        </div>

    </div>

    <?php } ?>


    <!-- =========================
         MENU GURU
    ========================= -->

    <?php if ($role == 'guru') { ?>

    <div class="box">

        <h3>Menu Sistem</h3>

        <div class="menu">


            <a href="pelanggaran.php">

                <span class="menu-nomor">
                    1.
                </span>

                <span class="menu-nama">
                    Data Pelanggaran
                </span>

            </a>


            <a href="tindakan.php">

                <span class="menu-nomor">
                    2.
                </span>

                <span class="menu-nama">
                    Data Tindakan
                </span>

            </a>


            <a href="riwayat.php">

                <span class="menu-nomor">
                    3.
                </span>

                <span class="menu-nama">
                    Riwayat Pelanggaran
                </span>

            </a>


            <a href="rekap_poin.php">

                <span class="menu-nomor">
                    4.
                </span>

                <span class="menu-nama">
                    Rekap Poin
                </span>

            </a>


        </div>

    </div>

    <?php } ?>


    <!-- =========================
         MENU WALI KELAS
    ========================= -->

    <?php if ($role == 'wali_kelas') { ?>

    <div class="box">

        <h3>Menu Sistem</h3>

        <div class="menu">


            <a href="pelanggaran.php">

                <span class="menu-nomor">
                    1.
                </span>

                <span class="menu-nama">
                    Data Pelanggaran
                </span>

            </a>


            <a href="laporan.php">

                <span class="menu-nomor">
                    2.
                </span>

                <span class="menu-nama">
                    Laporan
                </span>

            </a>


        </div>

    </div>

    <?php } ?>


    <!-- ROLE TIDAK DIKENALI -->

    <?php if (
        $role != 'admin' &&
        $role != 'guru' &&
        $role != 'wali_kelas'
    ) { ?>

        <div class="box">

            <b>
                Role tidak dikenali.
            </b>

        </div>

    <?php } ?>


</div>

</body>

</html>