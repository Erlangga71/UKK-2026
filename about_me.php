<?php

require_once "cek_role.php";

wajib_login();

include "database.php";

$role = $_SESSION['role'] ?? '';

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>About Me - Pelanggaran Siswa</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f6f8;
            color: #333;
        }

        /* SIDEBAR */

        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 220px;
            height: 100vh;
            background: #687280;
            padding: 20px 12px;
            overflow-y: auto;
        }

        .sidebar-title {
            color: white;
            font-size: 19px;
            font-weight: bold;
            margin: 0 8px 25px;
            line-height: 1.4;
        }

        .sidebar a {
            display: block;
            padding: 11px 13px;
            margin-bottom: 5px;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 14px;
        }

        .sidebar a:hover {
            background: #56606d;
        }

        .sidebar a.active {
            background: #4f5965;
            font-weight: bold;
        }

        .sidebar .logout {
            margin-top: 20px;
            background: #b42323;
        }

        .sidebar .logout:hover {
            background: #941d1d;
        }

        /* CONTENT */

        .content {
            margin-left: 220px;
            padding: 30px;
        }

        .top-header {
            background: white;
            border: 1px solid #ddd;
            border-radius: 7px;
            padding: 18px 22px;
            margin-bottom: 20px;
        }

        .top-header h1 {
            margin: 0;
            font-size: 24px;
        }

        .top-header p {
            margin: 7px 0 0;
            color: #777;
            font-size: 14px;
        }

        /* BOX */

        .box {
            background: white;
            border: 1px solid #ddd;
            border-radius: 7px;
            padding: 25px;
            margin-bottom: 20px;
        }

        .box h2 {
            margin-top: 0;
            font-size: 20px;
        }

        .box p {
            line-height: 1.7;
            color: #555;
        }

        /* INFO */

        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .info-table td {
            padding: 11px;
            border-bottom: 1px solid #ddd;
        }

        .info-table td:first-child {
            width: 180px;
            font-weight: bold;
        }

        /* RESPONSIVE */

        @media (max-width: 700px) {

            .sidebar {
                position: relative;
                width: 100%;
                height: auto;
            }

            .content {
                margin-left: 0;
                padding: 15px;
            }

        }

    </style>

</head>


<body>


<!-- SIDEBAR -->

<div class="sidebar">

    <div class="sidebar-title">
         Pelanggaran<br>
        Siswa
    </div>


    <a href="dashboard.php">
         Dashboard
    </a>


    <?php if ($role == 'admin') { ?>

        <a href="kelola_siswa.php">
             Data Siswa
        </a>

        <a href="kelola_guru.php">
             Data Guru
        </a>

        <a href="kelola_kelas.php">
             Data Kelas
        </a>

        <a href="kelola_tahun_ajaran.php">
             Tahun Ajaran
        </a>

        <a href="kelola_wali_kelas.php">
             Wali Kelas
        </a>

        <a href="penempatan_siswa.php">
             Penempatan Siswa
        </a>

        <a href="kelola_pelanggaran_kategori.php">
            ⚠ Kategori Pelanggaran
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

        <a href="about_me.php" class="active">
            ℹ About Me
        </a>

    <?php } ?>


    <?php if ($role == 'guru') { ?>

        <a href="catat_pelanggaran.php">
             Catat Pelanggaran
        </a>

        <a href="tindakan.php">
             Tindakan
        </a>

        <a href="riwayat.php">
             Riwayat
        </a>

        <a href="rekap_poin.php">
             Rekap Poin
        </a>

    <?php } ?>


    <?php if ($role == 'wali_kelas') { ?>

        <a href="pelanggaran.php">
             Data Pelanggaran
        </a>

        <a href="laporan.php">
             Laporan
        </a>

    <?php } ?>


    <a href="logout.php" class="logout">
        🚪 Logout
    </a>

</div>


<!-- CONTENT -->

<div class="content">


    <div class="top-header">

        <h1>
            Sistem Informasi Pelanggaran Siswa
        </h1>

        <p>
            About Me
        </p>

    </div>


    <!-- TENTANG SISTEM -->

    <div class="box">

        <h2>
            Tentang Sistem
        </h2>

        <p>
            Sistem Informasi Pelanggaran Siswa merupakan
            aplikasi yang digunakan untuk membantu sekolah
            dalam mencatat, mengelola, dan memantau data
            pelanggaran siswa.
        </p>

        <p>
            Sistem ini dibuat agar pengelolaan data pelanggaran
            dapat dilakukan dengan lebih mudah, teratur, dan
            tersimpan dalam database.
        </p>

    </div>


    <!-- INFORMASI PROJECT -->

    <div class="box">

        <h2>
            Informasi Project
        </h2>

        <table class="info-table">

            <tr>

                <td>
                    Nama Sistem
                </td>

                <td>
                    Sistem Informasi Pelanggaran Siswa
                </td>

            </tr>

            <tr>

                <td>
                    Project
                </td>

                <td>
                    UKK RPL 2026
                </td>

            </tr>

            <tr>

                <td>
                    Teknologi
                </td>

                <td>
                    PHP, MySQL, HTML, CSS
                </td>

            </tr>

            <tr>

                <td>
                    Database
                </td>

                <td>
                    ukk-2026
                </td>

            </tr>

        </table>

    </div>


    <!-- PEMBUAT -->

    <div class="box">

        <h2>
            Pembuat
        </h2>

        <p>
            Aplikasi ini dibuat sebagai project
            Uji Kompetensi Keahlian (UKK) Rekayasa
            Perangkat Lunak.
        </p>

        <table class="info-table">

            <tr>

                <td>
                    Nama
                </td>

                <td>
                    Erlangga Saputra Hidayat
                </td>

            </tr>

            <tr>

                <td>
                    Jurusan
                </td>

                <td>
                    Rekayasa Perangkat Lunak
                </td>

            </tr>

            <tr>

                <td>
                    Tahun
                </td>

                <td>
                    2026
                </td>

            </tr>

        </table>

    </div>


</div>


</body>

</html>