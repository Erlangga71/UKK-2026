<?php

session_start();

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

include "database.php";

$role = $_SESSION['role'] ?? '';

/* =========================
   DATA DASHBOARD
========================= */

// Total siswa aktif
$query_siswa = mysqli_query(
    $koneksi,
    "SELECT COUNT(*) AS total
     FROM siswa
     WHERE aktif = 1"
);

$data_siswa = mysqli_fetch_assoc($query_siswa);
$total_siswa = $data_siswa['total'] ?? 0;


// Total pelanggaran
$query_pelanggaran = mysqli_query(
    $koneksi,
    "SELECT COUNT(*) AS total
     FROM pelanggaran"
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


// Siswa bermasalah
$query_bermasalah = mysqli_query(
    $koneksi,
    "SELECT COUNT(DISTINCT siswa_id) AS total
     FROM pelanggaran"
);

$data_bermasalah = mysqli_fetch_assoc($query_bermasalah);
$siswa_bermasalah = $data_bermasalah['total'] ?? 0;


/* =========================
   PELANGGARAN TERBARU
========================= */

$query_terbaru = mysqli_query(
    $koneksi,
    "SELECT
        p.id,
        s.nama AS nama_siswa,
        j.nama AS jenis_pelanggaran,
        p.tanggal,
        p.keterangan

     FROM pelanggaran p

     LEFT JOIN siswa s
        ON p.siswa_id = s.id

     LEFT JOIN jenis_pelanggaran j
        ON p.jenis_id = j.id

     ORDER BY p.id DESC

     LIMIT 5"
);

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Dashboard - Pelanggaran Siswa</title>

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

        /* =========================
           SIDEBAR
        ========================= */

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

        /* =========================
           CONTENT
        ========================= */

        .content {
            margin-left: 220px;
            padding: 30px;
        }

        .top-header {
            background: white;
            border: 1px solid #ddd;
            border-radius: 7px;
            padding: 18px 22px;
            margin-bottom: 25px;
        }

        .top-header h1 {
            margin: 0;
            font-size: 25px;
            color: #333;
        }

        .top-header p {
            margin: 7px 0 0;
            color: #777;
            font-size: 14px;
        }

        .user-info {
            margin-top: 12px;
            font-size: 13px;
            color: #555;
        }

        .user-info b {
            color: #333;
        }

        /* =========================
           WELCOME
        ========================= */

        .welcome {
            margin-bottom: 20px;
        }

        .welcome h2 {
            margin: 0;
            font-size: 22px;
        }

        .welcome p {
            color: #777;
            margin-top: 7px;
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
            border: 1px solid #ddd;
            border-radius: 7px;
            padding: 20px;
            min-height: 120px;
        }

        .stat-icon {
            font-size: 25px;
            margin-bottom: 12px;
        }

        .stat-title {
            font-size: 12px;
            color: #777;
            font-weight: bold;
            margin-bottom: 7px;
        }

        .stat-number {
            font-size: 27px;
            font-weight: bold;
            color: #687280;
        }

        /* =========================
           BOX
        ========================= */

        .box {
            background: white;
            border: 1px solid #ddd;
            border-radius: 7px;
            padding: 22px;
            margin-bottom: 20px;
        }

        .box-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 18px;
        }

        .box-header h2 {
            margin: 0;
            font-size: 18px;
        }

        .box-header p {
            margin: 5px 0 0;
            font-size: 13px;
            color: #777;
        }

        .lihat-semua {
            text-decoration: none;
            color: white;
            background: #687280;
            padding: 8px 12px;
            border-radius: 4px;
            font-size: 12px;
        }

        .lihat-semua:hover {
            background: #56606d;
        }

        /* =========================
           TABLE
        ========================= */

        .table-container {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th {
            background: #687280;
            color: white;
            padding: 11px;
            text-align: left;
            font-size: 13px;
        }

        table td {
            padding: 11px;
            border-bottom: 1px solid #ddd;
            font-size: 13px;
            vertical-align: top;
        }

        table tr:hover {
            background: #f7f7f7;
        }

        .kosong {
            text-align: center;
            color: #777;
            padding: 25px;
        }

        /* =========================
           RESPONSIVE
        ========================= */

        @media (max-width: 1000px) {

            .statistik {
                grid-template-columns: repeat(2, 1fr);
            }

        }

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

            .statistik {
                grid-template-columns: 1fr;
            }

            .box-header {
                display: block;
            }

            .lihat-semua {
                display: inline-block;
                margin-top: 10px;
            }

        }

    </style>

</head>

<body>


<!-- =====================================================
     SIDEBAR
===================================================== -->

<div class="sidebar">

    <div class="sidebar-title">
         Pelanggaran<br>
        Siswa
    </div>


    <!-- DASHBOARD -->

    <a href="dashboard.php" class="active">
        🏠 Dashboard
    </a>


    <?php if ($role == 'admin') { ?>

        <!-- DATA SISWA -->

        <a href="kelola_siswa.php">
             Data Siswa
        </a>


        <!-- DATA GURU -->

        <a href="kelola_guru.php">
             Data Guru
        </a>


        <!-- DATA KELAS -->

        <a href="kelola_kelas.php">
             Data Kelas
        </a>


        <!-- TAHUN AJARAN -->

        <a href="kelola_tahun_ajaran.php">
             Tahun Ajaran
        </a>


        <!-- WALI KELAS -->

        <a href="kelola_wali_kelas.php">
             Wali Kelas
        </a>


        <!-- PENEMPATAN SISWA -->

        <a href="penempatan_siswa.php">
             Penempatan Siswa
        </a>


        <!-- KATEGORI -->

        <a href="kelola_pelanggaran_kategori.php">
            ⚠ Kategori Pelanggaran
        </a>


        <!-- PELANGGARAN -->

        <a href="pelanggaran.php">
             Data Pelanggaran
        </a>


        <!-- TINDAKAN -->

        <a href="tindakan.php">
         Data Tindakan
        </a>


        <!-- LAPORAN -->

        <a href="laporan.php">
             Laporan
        </a>


        <!-- ABOUT -->

        <a href="about_me.php">
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


    <!-- LOGOUT -->

    <a href="logout.php" class="logout">
        🚪 Logout
    </a>

</div>


<!-- =====================================================
     CONTENT
===================================================== -->

<div class="content">


    <!-- HEADER -->

    <div class="top-header">

        <h1>
            Sistem Informasi Pelanggaran Siswa
        </h1>

        <p>
            Dashboard Sistem
        </p>

        <div class="user-info">

            <b>
                <?= htmlspecialchars(
                    $_SESSION['nama'] ?? 'Pengguna'
                ); ?>
            </b>

            &nbsp; | &nbsp;

            Username:

            <b>
                <?= htmlspecialchars(
                    $_SESSION['username'] ?? '-'
                ); ?>
            </b>

            &nbsp; | &nbsp;

            Role:

            <b>
                <?= htmlspecialchars($role); ?>
            </b>

        </div>

    </div>


    <!-- WELCOME -->

    <div class="welcome">

        <h2>
            Selamat Datang,
            <?= htmlspecialchars(
                $_SESSION['nama'] ?? 'Pengguna'
            ); ?>
        </h2>

        <p>
            Kelola dan pantau data pelanggaran siswa
            melalui sistem ini.
        </p>

    </div>


    <!-- STATISTIK -->

    <div class="statistik">


        <div class="stat-box">

            <div class="stat-icon">
                👤
            </div>

            <div class="stat-title">
                TOTAL SISWA AKTIF
            </div>

            <div class="stat-number">
                <?= $total_siswa; ?>
            </div>

        </div>


        <div class="stat-box">

            <div class="stat-icon">
                ⚖
            </div>

            <div class="stat-title">
                TOTAL PELANGGARAN
            </div>

            <div class="stat-number">
                <?= $total_pelanggaran; ?>
            </div>

        </div>


        <div class="stat-box">

            <div class="stat-icon">
                📅
            </div>

            <div class="stat-title">
                PELANGGARAN BULAN INI
            </div>

            <div class="stat-number">
                <?= $pelanggaran_bulan; ?>
            </div>

        </div>


        <div class="stat-box">

            <div class="stat-icon">
                !
            </div>

            <div class="stat-title">
                SISWA BERMASALAH
            </div>

            <div class="stat-number">
                <?= $siswa_bermasalah; ?>
            </div>

        </div>


    </div>


    <!-- INFORMASI PELANGGARAN -->

    <div class="box">

        <div class="box-header">

            <div>

                <h2>
                    Informasi Pelanggaran
                </h2>

                <p>
                    Daftar pelanggaran siswa terbaru
                </p>

            </div>

            <a href="pelanggaran.php" class="lihat-semua">
                Lihat Semua
            </a>

        </div>


        <div class="table-container">

            <table>

                <thead>

                    <tr>

                        <th>No</th>

                        <th>Nama Siswa</th>

                        <th>Jenis Pelanggaran</th>

                        <th>Tanggal</th>

                        <th>Keterangan</th>

                    </tr>

                </thead>


                <tbody>

                <?php

                $no = 1;

                if (
                    $query_terbaru &&
                    mysqli_num_rows($query_terbaru) > 0
                ) {

                    while (
                        $row = mysqli_fetch_assoc(
                            $query_terbaru
                        )
                    ) {

                ?>

                    <tr>

                        <td>
                            <?= $no++; ?>
                        </td>

                        <td>
                            <b>
                                <?= htmlspecialchars(
                                    $row['nama_siswa'] ?? '-'
                                ); ?>
                            </b>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $row['jenis_pelanggaran'] ?? '-'
                            ); ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $row['tanggal'] ?? '-'
                            ); ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $row['keterangan'] ?? '-'
                            ); ?>
                        </td>

                    </tr>

                <?php

                    }

                } else {

                ?>

                    <tr>

                        <td
                            colspan="5"
                            class="kosong"
                        >
                            Belum ada data pelanggaran.
                        </td>

                    </tr>

                <?php

                }

                ?>

                </tbody>

            </table>

        </div>

    </div>


</div>


</body>

</html>