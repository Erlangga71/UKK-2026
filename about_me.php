
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "database.php";
require_once "auth.php";

// About Me hanya untuk Admin
hanyaRole(['admin']);

$role = $_SESSION['role'] ?? '';
$nama = $_SESSION['nama'] ?? 'Pengguna';
$email = $_SESSION['email'] ?? '-';
?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>About Me - Pelanggaran Siswa</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #f4f6f8;
            font-family: Arial, sans-serif;
            font-size: 14px;
            color: #212529;
        }

        /* SIDEBAR */

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 220px;
            height: 100vh;
            background: #687280;
            padding: 20px 12px;
            overflow-y: auto;
            z-index: 1000;
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

        .sidebar a:hover,
        .sidebar a.active {
            background: #4f5965;
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
            min-height: 100vh;
        }

        /* PANEL */

        .panel {
            background: white;
            border: 1px solid #ddd;
            border-radius: 6px;
            margin-bottom: 15px;
        }

        .panel-body {
            padding: 20px;
        }

        /* TABEL INFORMASI */

        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .info-table td {
            padding: 11px;
            border-bottom: 1px solid #ddd;
            font-size: 14px;
        }

        .info-table tr:last-child td {
            border-bottom: none;
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

            .info-table td:first-child {
                width: 120px;
            }
        }

    </style>

</head>

<body>

<!-- SIDEBAR -->

<div class="sidebar">

    <div class="sidebar-title">
        Pelanggaran<br>Siswa
    </div>

    <a href="dashboard.php">Dashboard</a>

    <a href="kelola_siswa.php">Data Siswa</a>

    <a href="kelola_guru.php">Data Guru</a>

    <a href="kelola_kelas.php">Data Kelas</a>

    <a href="kelola_tahun_ajaran.php">Tahun Ajaran</a>

    <a href="kelola_wali_kelas.php">Wali Kelas</a>

    <a href="penempatan_siswa.php">Penempatan Siswa</a>

    <a href="kelola_pelanggaran_kategori.php">Kategori Pelanggaran</a>

    <a href="pelanggaran.php">Data Pelanggaran</a>

    <a href="laporan.php">Laporan</a>

    <a href="riwayat.php">Riwayat</a>

    <a href="rekap_poin.php">Rekap Poin</a>

    <a href="about_me.php" class="active">About Me</a>

    <a href="logout.php" class="logout">Logout</a>

</div>

<!-- CONTENT -->

<div class="content">

    <!-- HEADER -->

    <div class="panel">

        <div class="panel-body">

            <h4 class="mb-1">About Me</h4>

            <p class="text-muted mb-0">
                Informasi mengenai sistem dan pembuat aplikasi.
            </p>

        </div>

    </div>

    <!-- TENTANG SISTEM -->

    <div class="panel">

        <div class="panel-body">

            <h5 class="mb-3">Tentang Sistem</h5>

            <p>
                Sistem Informasi Pelanggaran Siswa merupakan
                aplikasi yang digunakan untuk membantu sekolah
                dalam mencatat, mengelola, dan memantau data
                pelanggaran siswa.
            </p>

            <p class="mb-0">
                Sistem ini dibuat agar pengelolaan data pelanggaran
                dapat dilakukan dengan lebih mudah, teratur, dan
                tersimpan dalam database.
            </p>

        </div>

    </div>

    <!-- INFORMASI PROJECT -->

    <div class="panel">

        <div class="panel-body">

            <h5 class="mb-3">Informasi Project</h5>

            <div class="table-responsive">

                <table class="info-table">

                    <tr>
                        <td>Nama Sistem</td>
                        <td>
                            Sistem Informasi Pelanggaran Siswa
                            SMK MUHAMMADIYAH TASIKMALAYA
                        </td>
                    </tr>

                    <tr>
                        <td>Project</td>
                        <td>UKK RPL 2026</td>
                    </tr>

                    <tr>
                        <td>Teknologi</td>
                        <td>PHP, MySQL, HTML, CSS, Bootstrap</td>
                    </tr>

                    <tr>
                        <td>Database</td>
                        <td>ukk_2026</td>
                    </tr>

                </table>

            </div>

        </div>

    </div>

    <!-- PEMBUAT -->

    <div class="panel">

        <div class="panel-body">

            <h5 class="mb-3">Pembuat</h5>

            <p>
                Aplikasi ini dibuat sebagai project
                Uji Kompetensi Keahlian (UKK) Rekayasa
                Perangkat Lunak.
            </p>

            <div class="table-responsive">

                <table class="info-table">

                    <tr>
                        <td>Nama</td>
                        <td>Erlangga Saputra Hidayat</td>
                    </tr>

                    <tr>
                        <td>Jurusan</td>
                        <td>Rekayasa Perangkat Lunak</td>
                    </tr>

                    <tr>
                        <td>Tahun</td>
                        <td>2026</td>
                    </tr>

                </table>

            </div>

        </div>

    </div>

</div>

</body>
</html>
