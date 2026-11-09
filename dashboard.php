
<?php
session_start();

require_once "database.php";
require_once "auth.php";

hanyaRole(['admin', 'guru']);

$nama  = $_SESSION['nama'] ?? 'Pengguna';
$role  = $_SESSION['role'] ?? '';
$email = $_SESSION['email'] ?? '-';

$jabatan = ($role == 'admin') ? 'Administrator' : 'Guru';

/* TOTAL SISWA AKTIF */
$query_siswa = mysqli_query(
    $koneksi,
    "SELECT COUNT(*) AS total FROM siswa WHERE aktif = 1"
);
$total_siswa = $query_siswa
    ? mysqli_fetch_assoc($query_siswa)['total']
    : 0;

/* TOTAL PELANGGARAN */
$query_pelanggaran = mysqli_query(
    $koneksi,
    "SELECT COUNT(*) AS total FROM pelanggaran"
);
$total_pelanggaran = $query_pelanggaran
    ? mysqli_fetch_assoc($query_pelanggaran)['total']
    : 0;

/* PELANGGARAN BULAN INI */
$query_bulan = mysqli_query(
    $koneksi,
    "SELECT COUNT(*) AS total
     FROM pelanggaran
     WHERE MONTH(tanggal) = MONTH(CURDATE())
     AND YEAR(tanggal) = YEAR(CURDATE())"
);
$total_bulan = $query_bulan
    ? mysqli_fetch_assoc($query_bulan)['total']
    : 0;

/* SISWA BERMASALAH */
$query_bermasalah = mysqli_query(
    $koneksi,
    "SELECT COUNT(DISTINCT siswa_id) AS total
     FROM pelanggaran"
);
$total_bermasalah = $query_bermasalah
    ? mysqli_fetch_assoc($query_bermasalah)['total']
    : 0;

/* PELANGGARAN TERBARU */
$query_terbaru = mysqli_query(
    $koneksi,
    "SELECT
        siswa.nama AS nama_siswa,
        jenis_pelanggaran.nama AS nama_pelanggaran,
        pelanggaran.tanggal,
        pelanggaran.keterangan
     FROM pelanggaran
     JOIN siswa ON pelanggaran.siswa_id = siswa.id
     JOIN jenis_pelanggaran
        ON pelanggaran.jenis_id = jenis_pelanggaran.id
     ORDER BY pelanggaran.tanggal DESC, pelanggaran.id DESC
     LIMIT 5"
);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Dashboard - Pelanggaran Siswa</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            margin: 0;
            background: #f4f6f8;
            font-family: Arial, sans-serif;
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
        }

        .sidebar-title {
            color: white;
            font-size: 19px;
            font-weight: bold;
            margin: 0 8px 25px;
            line-height: 1.4;
        }

        .sidebar .nav-link {
            color: white;
            font-size: 14px;
            padding: 11px 13px;
            margin-bottom: 5px;
            border-radius: 5px;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: #4f5965;
            color: white;
        }

        .sidebar .logout {
            background: #b42323;
            margin-top: 20px;
        }

        .sidebar .logout:hover {
            background: #941d1d;
        }

        /* KONTEN */
        .content {
            margin-left: 220px;
            padding: 30px;
        }

        .header-box,
        .main-box,
        .stat-card {
            background: white;
            border: 1px solid #dddddd;
            border-radius: 6px;
        }

        .header-box {
            padding: 18px;
            margin-bottom: 20px;
        }

        .header-box h4 {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .header-box p {
            color: #687280;
            font-size: 12px;
            margin-bottom: 8px;
        }

        .info-user {
            font-size: 12px;
        }

        .welcome {
            margin-bottom: 18px;
        }

        .welcome h5 {
            font-size: 18px;
            font-weight: bold;
        }

        .welcome p {
            color: #687280;
            font-size: 13px;
        }

        .stat-card {
            padding: 18px;
            height: 100%;
        }

        .stat-icon {
            font-size: 21px;
            margin-bottom: 8px;
        }

        .stat-label {
            color: #687280;
            font-size: 11px;
            text-transform: uppercase;
        }

        .stat-number {
            color: #687280;
            font-size: 23px;
            font-weight: bold;
            margin: 0;
        }

        .main-box {
            padding: 20px;
        }

        .table thead th {
            background: #687280;
            color: white;
            font-size: 12px;
        }

        .table tbody td {
            font-size: 12px;
            vertical-align: middle;
        }

        .btn-lihat {
            background: #687280;
            color: white;
            font-size: 12px;
        }

        .btn-lihat:hover {
            background: #4f5965;
            color: white;
        }

        /* TAMPILAN HP */
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
        ⚖ Pelanggaran<br>Siswa
    </div>

    <nav class="nav flex-column">

        <a href="dashboard.php" class="nav-link active">
             Dashboard
        </a>

        <?php if ($role == 'admin') { ?>

            <!-- MENU ADMIN -->
            <a href="kelola_siswa.php" class="nav-link">Data Siswa</a>
            <a href="kelola_guru.php" class="nav-link">Data Guru</a>
            <a href="kelola_kelas.php" class="nav-link">Data Kelas</a>
            <a href="kelola_tahun_ajaran.php" class="nav-link">Tahun Ajaran</a>
            <a href="kelola_wali_kelas.php" class="nav-link">Wali Kelas</a>
            <a href="penempatan_siswa.php" class="nav-link">Penempatan Siswa</a>
            <a href="kelola_pelanggaran_kategori.php" class="nav-link">
                 Kategori Pelanggaran
            </a>
            <a href="pelanggaran.php" class="nav-link">Data Pelanggaran</a>
            <a href="tindakan.php" class="nav-link">Data Tindakan</a>
            <a href="laporan.php" class="nav-link">Laporan</a>
            <a href="about_me.php" class="nav-link">ℹ About Me</a>

        <?php } elseif ($role == 'guru') { ?>

            <!-- MENU GURU -->
            <a href="catat_pelanggaran.php" class="nav-link">
                Catat Pelanggaran
            </a>
            <a href="pelanggaran.php" class="nav-link">
                Data Pelanggaran
            </a>
            <a href="tindakan.php" class="nav-link">
                Data Tindakan
            </a>
            <a href="riwayat.php" class="nav-link">
                Riwayat
            </a>
            <a href="rekap_poin.php" class="nav-link">
                Rekap Poin
            </a>

        <?php } ?>

        <a href="logout.php" class="nav-link logout">
             Logout
        </a>

    </nav>

</div>

<!-- KONTEN -->
<div class="content">

    <!-- HEADER -->
    <div class="header-box">

        <h4>Sistem Informasi Pelanggaran Siswa</h4>

        <p>Dashboard Sistem</p>

        <div class="info-user">
            <strong><?= htmlspecialchars($jabatan); ?></strong>

            &nbsp; | &nbsp;

            Email: <?= htmlspecialchars($email); ?>

            &nbsp; | &nbsp;

            Role: <?= htmlspecialchars($role); ?>
        </div>

    </div>

    <!-- SELAMAT DATANG -->
    <div class="welcome">

        <h5>
            Selamat Datang, <?= htmlspecialchars($nama); ?>
        </h5>

        <p>
            Kelola dan pantau data pelanggaran siswa melalui sistem ini.
        </p>

    </div>

    <!-- STATISTIK -->
    <div class="row g-3 mb-4">

        <div class="col-md-3 col-sm-6">
            <div class="stat-card">
                <div class="stat-icon"></div>
                <div class="stat-label">Total Siswa Aktif</div>
                <p class="stat-number"><?= $total_siswa; ?></p>
            </div>
        </div>

        <div class="col-md-3 col-sm-6">
            <div class="stat-card">
                <div class="stat-icon"></div>
                <div class="stat-label">Total Pelanggaran</div>
                <p class="stat-number"><?= $total_pelanggaran; ?></p>
            </div>
        </div>

        <div class="col-md-3 col-sm-6">
            <div class="stat-card">
                <div class="stat-icon"></div>
                <div class="stat-label">Pelanggaran Bulan Ini</div>
                <p class="stat-number"><?= $total_bulan; ?></p>
            </div>
        </div>

        <div class="col-md-3 col-sm-6">
            <div class="stat-card">
                <div class="stat-icon"></div>
                <div class="stat-label">Siswa Bermasalah</div>
                <p class="stat-number"><?= $total_bermasalah; ?></p>
            </div>
        </div>

    </div>

    <!-- TABEL PELANGGARAN -->
    <div class="main-box">

        <div class="d-flex justify-content-between align-items-center mb-3">

            <div>
                <h6 class="fw-bold mb-1">
                    Informasi Pelanggaran
                </h6>

                <small class="text-secondary">
                    Daftar pelanggaran siswa terbaru
                </small>
            </div>

            <a href="pelanggaran.php" class="btn btn-sm btn-lihat">
                Lihat Semua
            </a>

        </div>

        <div class="table-responsive">

            <table class="table table-bordered table-hover mb-0">

                <thead>
                    <tr>
                        <th width="6%">No</th>
                        <th>Nama Siswa</th>
                        <th>Jenis Pelanggaran</th>
                        <th>Tanggal</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>

                <tbody>

                    <?php
                    $no = 1;

                    if ($query_terbaru && mysqli_num_rows($query_terbaru) > 0) {

                        while ($data = mysqli_fetch_assoc($query_terbaru)) {
                    ?>

                        <tr>
                            <td><?= $no++; ?></td>

                            <td>
                                <?= htmlspecialchars($data['nama_siswa']); ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($data['nama_pelanggaran']); ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($data['tanggal']); ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($data['keterangan'] ?? '-'); ?>
                            </td>
                        </tr>

                    <?php
                        }

                    } else {
                    ?>

                        <tr>
                            <td colspan="5" class="text-center">
                                Belum ada data pelanggaran.
                            </td>
                        </tr>

                    <?php } ?>

                </tbody>

            </table>

        </div>

    </div>

</div>

</body>
</html>
