
<?php
session_start();

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

include "database.php";

$nama = $_SESSION['nama'] ?? 'Pengguna';
$role = $_SESSION['role'] ?? '';
$username = $_SESSION['username'] ?? '-';

if ($role == 'admin') {
    $jabatan = 'Administrator';
} elseif ($role == 'guru') {
    $jabatan = 'Guru';
} else {
    $jabatan = 'Pengguna';
}

/* TOTAL SISWA AKTIF */
$query_siswa = mysqli_query(
    $koneksi,
    "SELECT COUNT(*) AS total FROM siswa WHERE aktif = 1"
);
$total_siswa = $query_siswa ? mysqli_fetch_assoc($query_siswa)['total'] : 0;

/* TOTAL PELANGGARAN */
$query_pelanggaran = mysqli_query(
    $koneksi,
    "SELECT COUNT(*) AS total FROM pelanggaran"
);
$total_pelanggaran = $query_pelanggaran ? mysqli_fetch_assoc($query_pelanggaran)['total'] : 0;

/* PELANGGARAN BULAN INI */
$query_bulan = mysqli_query(
    $koneksi,
    "SELECT COUNT(*) AS total
     FROM pelanggaran
     WHERE MONTH(tanggal) = MONTH(CURDATE())
     AND YEAR(tanggal) = YEAR(CURDATE())"
);
$total_bulan = $query_bulan ? mysqli_fetch_assoc($query_bulan)['total'] : 0;

/* SISWA BERMASALAH */
$query_bermasalah = mysqli_query(
    $koneksi,
    "SELECT COUNT(DISTINCT siswa_id) AS total FROM pelanggaran"
);
$total_bermasalah = $query_bermasalah ? mysqli_fetch_assoc($query_bermasalah)['total'] : 0;

/* DATA PELANGGARAN TERBARU */
$query_terbaru = mysqli_query(
    $koneksi,
    "SELECT
        siswa.nama AS nama_siswa,
        jenis_pelanggaran.nama AS nama_pelanggaran,
        pelanggaran.tanggal,
        pelanggaran.keterangan
     FROM pelanggaran
     JOIN siswa ON pelanggaran.siswa_id = siswa.id
     JOIN jenis_pelanggaran ON pelanggaran.jenis_id = jenis_pelanggaran.id
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
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #f4f6f8;
            font-family: Arial, sans-serif;
            color: #212529;
        }

        /* SIDEBAR - SAMA DENGAN KELola_TAHUN_AJARAN */
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

        /* KONTEN */
        .content {
            margin-left: 220px;
            padding: 30px;
        }

        .header-box,
        .card-box {
            background: white;
            border: 1px solid #dddddd;
            border-radius: 6px;
            padding: 16px;
        }

        .header-box {
            margin-bottom: 18px;
        }

        .header-box h4 {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 4px;
        }

        .header-box p {
            color: #687280;
            font-size: 12px;
            margin-bottom: 8px;
        }

        .info-user {
            font-size: 11px;
        }

        .welcome {
            margin-bottom: 15px;
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
            background: white;
            border: 1px solid #dddddd;
            border-radius: 6px;
            padding: 18px;
            height: 100%;
        }

        .stat-icon {
            font-size: 21px;
            margin-bottom: 8px;
        }

        .stat-label {
            color: #687280;
            font-size: 10px;
            text-transform: uppercase;
        }

        .stat-number {
            color: #687280;
            font-size: 22px;
            font-weight: bold;
            margin: 0;
        }

        .table thead th {
            background: #687280;
            color: white;
            font-size: 11px;
            padding: 8px;
        }

        .table tbody td {
            font-size: 11px;
            padding: 8px;
            vertical-align: middle;
        }

        .btn-lihat {
            background: #687280;
            color: white;
            font-size: 11px;
        }

        .btn-lihat:hover {
            background: #4f5965;
            color: white;
        }

        /* LAYAR KECIL */
        @media(max-width: 700px) {
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

    <a href="dashboard.php" class="active">🏠 Dashboard</a>

    <?php if ($role == 'admin') { ?>
        <a href="kelola_siswa.php">Data Siswa</a>
        <a href="kelola_guru.php">Data Guru</a>
        <a href="kelola_kelas.php">Data Kelas</a>
        <a href="kelola_tahun_ajaran.php">Tahun Ajaran</a>
        <a href="kelola_wali_kelas.php">Wali Kelas</a>
        <a href="penempatan_siswa.php">Penempatan Siswa</a>
        <a href="kelola_pelanggaran_kategori.php">⚠ Kategori Pelanggaran</a>
    <?php } ?>

    <a href="pelanggaran.php">Data Pelanggaran</a>
    <a href="tindakan.php">Data Tindakan</a>
    <a href="laporan.php">Laporan</a>
    <a href="about_me.php">ℹ About Me</a>

    <a href="logout.php" class="logout">🚪 Logout</a>

</div>

<!-- KONTEN -->
<div class="content">

    <div class="header-box">
        <h4>Sistem Informasi Pelanggaran Siswa</h4>
        <p>Dashboard Sistem</p>

        <div class="info-user">
            <strong><?php echo htmlspecialchars($jabatan); ?></strong>
            &nbsp; | &nbsp;
            Username: <?php echo htmlspecialchars($username); ?>
            &nbsp; | &nbsp;
            Role: <?php echo htmlspecialchars($role); ?>
        </div>
    </div>

    <div class="welcome">
        <h5>Selamat Datang, <?php echo htmlspecialchars($jabatan); ?></h5>
        <p>Kelola dan pantau data pelanggaran siswa melalui sistem ini.</p>
    </div>

    <!-- KARTU STATISTIK -->
    <div class="row g-3 mb-3">

        <div class="col-md-3 col-sm-6">
            <div class="stat-card">
                <div class="stat-icon">👤</div>
                <div class="stat-label">Total Siswa Aktif</div>
                <p class="stat-number"><?php echo $total_siswa; ?></p>
            </div>
        </div>

        <div class="col-md-3 col-sm-6">
            <div class="stat-card">
                <div class="stat-icon">⚖️</div>
                <div class="stat-label">Total Pelanggaran</div>
                <p class="stat-number"><?php echo $total_pelanggaran; ?></p>
            </div>
        </div>

        <div class="col-md-3 col-sm-6">
            <div class="stat-card">
                <div class="stat-icon">🗓️</div>
                <div class="stat-label">Pelanggaran Bulan Ini</div>
                <p class="stat-number"><?php echo $total_bulan; ?></p>
            </div>
        </div>

        <div class="col-md-3 col-sm-6">
            <div class="stat-card">
                <div class="stat-icon">❗</div>
                <div class="stat-label">Siswa Bermasalah</div>
                <p class="stat-number"><?php echo $total_bermasalah; ?></p>
            </div>
        </div>

    </div>

    <!-- TABEL PELANGGARAN -->
    <div class="card-box">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h6 class="fw-bold mb-1">Informasi Pelanggaran</h6>
                <small class="text-secondary">Daftar pelanggaran siswa terbaru</small>
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
                            <td><?php echo $no++; ?></td>
                            <td><?php echo htmlspecialchars($data['nama_siswa']); ?></td>
                            <td><?php echo htmlspecialchars($data['nama_pelanggaran']); ?></td>
                            <td><?php echo htmlspecialchars($data['tanggal']); ?></td>
                            <td><?php echo htmlspecialchars($data['keterangan'] ?? '-'); ?></td>
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
