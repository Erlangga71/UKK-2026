<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/database.php";
require_once __DIR__ . "/auth.php";

hanyaRole(['admin', 'guru', 'wali_kelas']);

$nama  = $_SESSION['nama'] ?? 'Pengguna';
$role  = $_SESSION['role'] ?? '';
$email = $_SESSION['email'] ?? '-';

if ($role === 'admin') {
    $jabatan = 'Administrator';
} elseif ($role === 'wali_kelas') {
    $jabatan = 'Wali Kelas';
} else {
    $jabatan = 'Guru';
}

/* =========================
   TAHUN AJARAN AKTIF
========================= */

$tahun_ajaran_id = 0;
$nama_tahun_ajaran = '';

$query_tahun = mysqli_query(
    $koneksi,
    "SELECT id, nama
     FROM tahun_ajaran
     WHERE aktif = 1
     LIMIT 1"
);

if ($query_tahun && mysqli_num_rows($query_tahun) > 0) {
    $data_tahun = mysqli_fetch_assoc($query_tahun);
    $tahun_ajaran_id = (int) $data_tahun['id'];
    $nama_tahun_ajaran = $data_tahun['nama'];
}

/* =========================
   IDENTITAS WALI KELAS
========================= */

$guru_id_login = 0;
$kelas_id_login = 0;
$nama_kelas_login = '';

if ($role === 'wali_kelas') {

    $stmt_user = mysqli_prepare(
        $koneksi,
        "SELECT guru_id
         FROM users
         WHERE email = ?
         LIMIT 1"
    );

    if ($stmt_user) {
        mysqli_stmt_bind_param($stmt_user, "s", $email);
        mysqli_stmt_execute($stmt_user);

        $result_user = mysqli_stmt_get_result($stmt_user);

        if ($result_user && mysqli_num_rows($result_user) > 0) {
            $data_user = mysqli_fetch_assoc($result_user);
            $guru_id_login = (int) ($data_user['guru_id'] ?? 0);
        }

        mysqli_stmt_close($stmt_user);
    }

    if ($guru_id_login <= 0) {

        $stmt_guru = mysqli_prepare(
            $koneksi,
            "SELECT id
             FROM guru
             WHERE email = ?
             LIMIT 1"
        );

        if ($stmt_guru) {
            mysqli_stmt_bind_param($stmt_guru, "s", $email);
            mysqli_stmt_execute($stmt_guru);

            $result_guru = mysqli_stmt_get_result($stmt_guru);

            if ($result_guru && mysqli_num_rows($result_guru) > 0) {
                $data_guru = mysqli_fetch_assoc($result_guru);
                $guru_id_login = (int) $data_guru['id'];
            }

            mysqli_stmt_close($stmt_guru);
        }
    }

    if ($guru_id_login > 0 && $tahun_ajaran_id > 0) {

        $stmt_kelas = mysqli_prepare(
            $koneksi,
            "SELECT wk.kelas_id, k.nama_kelas
             FROM wali_kelas wk
             JOIN kelas k ON wk.kelas_id = k.id
             WHERE wk.guru_id = ?
               AND wk.tahun_ajaran_id = ?
             LIMIT 1"
        );

        if ($stmt_kelas) {
            mysqli_stmt_bind_param(
                $stmt_kelas,
                "ii",
                $guru_id_login,
                $tahun_ajaran_id
            );

            mysqli_stmt_execute($stmt_kelas);

            $result_kelas = mysqli_stmt_get_result($stmt_kelas);

            if ($result_kelas && mysqli_num_rows($result_kelas) > 0) {
                $data_kelas = mysqli_fetch_assoc($result_kelas);

                $kelas_id_login = (int) $data_kelas['kelas_id'];
                $nama_kelas_login = $data_kelas['nama_kelas'];
            }

            mysqli_stmt_close($stmt_kelas);
        }
    }
}

/* =========================
   FILTER PELANGGARAN
========================= */

$filter_pelanggaran = "";

if ($role === 'wali_kelas') {

    if ($kelas_id_login > 0 && $tahun_ajaran_id > 0) {

        $filter_pelanggaran = "
            AND EXISTS (
                SELECT 1
                FROM penempatan_siswa ps
                WHERE ps.siswa_id = pelanggaran.siswa_id
                  AND ps.kelas_id = $kelas_id_login
                  AND ps.tahun_ajaran_id = $tahun_ajaran_id
            )
        ";

    } else {

        $filter_pelanggaran = " AND 1 = 0 ";

    }
}

/* =========================
   TOTAL SISWA AKTIF
========================= */

if ($role === 'wali_kelas') {

    if ($kelas_id_login > 0 && $tahun_ajaran_id > 0) {

        $query_siswa = mysqli_query(
            $koneksi,
            "SELECT COUNT(DISTINCT s.id) AS total
             FROM siswa s
             JOIN penempatan_siswa ps
                ON ps.siswa_id = s.id
             WHERE s.aktif = 1
               AND ps.kelas_id = $kelas_id_login
               AND ps.tahun_ajaran_id = $tahun_ajaran_id"
        );

    } else {

        $query_siswa = false;

    }

} else {

    $query_siswa = mysqli_query(
        $koneksi,
        "SELECT COUNT(*) AS total
         FROM siswa
         WHERE aktif = 1"
    );

}

$total_siswa = 0;

if ($query_siswa) {
    $data = mysqli_fetch_assoc($query_siswa);
    $total_siswa = (int) $data['total'];
}

/* =========================
   TOTAL PELANGGARAN
========================= */

$query_pelanggaran = mysqli_query(
    $koneksi,
    "SELECT COUNT(DISTINCT pelanggaran.id) AS total
     FROM pelanggaran
     WHERE 1 = 1
     $filter_pelanggaran"
);

$total_pelanggaran = 0;

if ($query_pelanggaran) {
    $data = mysqli_fetch_assoc($query_pelanggaran);
    $total_pelanggaran = (int) $data['total'];
}

/* =========================
   PELANGGARAN BULAN INI
========================= */

$query_bulan = mysqli_query(
    $koneksi,
    "SELECT COUNT(DISTINCT pelanggaran.id) AS total
     FROM pelanggaran
     WHERE MONTH(pelanggaran.tanggal) = MONTH(CURDATE())
       AND YEAR(pelanggaran.tanggal) = YEAR(CURDATE())
       $filter_pelanggaran"
);

$total_bulan = 0;

if ($query_bulan) {
    $data = mysqli_fetch_assoc($query_bulan);
    $total_bulan = (int) $data['total'];
}

/* =========================
   SISWA BERMASALAH
========================= */

$query_bermasalah = mysqli_query(
    $koneksi,
    "SELECT COUNT(DISTINCT pelanggaran.siswa_id) AS total
     FROM pelanggaran
     WHERE 1 = 1
     $filter_pelanggaran"
);

$total_bermasalah = 0;

if ($query_bermasalah) {
    $data = mysqli_fetch_assoc($query_bermasalah);
    $total_bermasalah = (int) $data['total'];
}

/* =========================
   DATA TABEL DASHBOARD
========================= */

if ($role === 'wali_kelas') {

    if ($kelas_id_login > 0 && $tahun_ajaran_id > 0) {

        $query_terbaru = mysqli_query(
            $koneksi,
            "SELECT
                s.id,
                s.nis,
                s.nama AS nama_siswa,
                COUNT(DISTINCT p.id) AS total_pelanggaran
             FROM siswa s
             JOIN penempatan_siswa ps
                ON ps.siswa_id = s.id
             LEFT JOIN pelanggaran p
                ON p.siswa_id = s.id
             WHERE s.aktif = 1
               AND ps.kelas_id = $kelas_id_login
               AND ps.tahun_ajaran_id = $tahun_ajaran_id
             GROUP BY s.id, s.nis, s.nama
             ORDER BY s.nama ASC"
        );

    } else {

        $query_terbaru = false;

    }

} else {

    $query_terbaru = mysqli_query(
        $koneksi,
        "SELECT
            siswa.nama AS nama_siswa,
            jenis_pelanggaran.nama AS nama_pelanggaran,
            pelanggaran.tanggal,
            pelanggaran.keterangan
         FROM pelanggaran
         JOIN siswa
            ON pelanggaran.siswa_id = siswa.id
         JOIN jenis_pelanggaran
            ON pelanggaran.jenis_id = jenis_pelanggaran.id
         ORDER BY pelanggaran.tanggal DESC,
                  pelanggaran.id DESC
         LIMIT 5"
    );

}

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

<!-- SIDEBAR BERSAMA -->

<?php
$menu_aktif = 'dashboard';
require_once __DIR__ . "/sidebar.php";
?>

<!-- KONTEN -->

<div class="content">

    <div class="header-box">

        <h4>Sistem Informasi Pelanggaran Siswa</h4>

        <p>
            Dashboard Sistem
            <?php if ($nama_tahun_ajaran !== '') { ?>
                | Tahun Ajaran:
                <?= htmlspecialchars($nama_tahun_ajaran); ?>
            <?php } ?>
        </p>

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

        <?php if ($role === 'wali_kelas') { ?>

            <p>

                <?php if ($kelas_id_login > 0) { ?>

                    Pantau informasi seluruh siswa kelas
                    <strong><?= htmlspecialchars($nama_kelas_login); ?></strong>.

                <?php } else { ?>

                    Data kelas wali kelas belum ditemukan pada tahun ajaran aktif.

                <?php } ?>

            </p>

        <?php } else { ?>

            <p>
                Kelola dan pantau data pelanggaran siswa melalui sistem ini.
            </p>

        <?php } ?>

    </div>

    <!-- STATISTIK -->

    <div class="row g-3 mb-4">

        <div class="col-md-3 col-sm-6">
            <div class="stat-card">
                <div class="stat-label">Total Siswa Aktif</div>
                <p class="stat-number"><?= $total_siswa; ?></p>
            </div>
        </div>

        <div class="col-md-3 col-sm-6">
            <div class="stat-card">
                <div class="stat-label">Total Pelanggaran</div>
                <p class="stat-number"><?= $total_pelanggaran; ?></p>
            </div>
        </div>

        <div class="col-md-3 col-sm-6">
            <div class="stat-card">
                <div class="stat-label">Pelanggaran Bulan Ini</div>
                <p class="stat-number"><?= $total_bulan; ?></p>
            </div>
        </div>

        <div class="col-md-3 col-sm-6">
            <div class="stat-card">
                <div class="stat-label">Siswa Bermasalah</div>
                <p class="stat-number"><?= $total_bermasalah; ?></p>
            </div>
        </div>

    </div>

    <!-- TABEL DASHBOARD -->

    <div class="main-box">

        <div class="d-flex justify-content-between align-items-center mb-3">

            <div>

                <h6 class="fw-bold mb-1">

                    <?= $role === 'wali_kelas'
                        ? 'Data Seluruh Siswa Kelas'
                        : 'Informasi Pelanggaran'; ?>

                </h6>

                <small class="text-secondary">

                    <?= $role === 'wali_kelas'
                        ? 'Daftar seluruh siswa, termasuk yang belum melakukan pelanggaran'
                        : 'Daftar pelanggaran siswa terbaru'; ?>

                </small>

            </div>

            <!-- TOMBOL SESUAI ROLE -->

            <?php if ($role === 'admin') { ?>

                <a href="pelanggaran.php" class="btn btn-sm btn-lihat">
                    Lihat Semua
                </a>

            <?php } elseif ($role === 'guru') { ?>

                <a href="catat_pelanggaran.php" class="btn btn-sm btn-lihat">
                    Catat Pelanggaran
                </a>

            <?php } elseif ($role === 'wali_kelas') { ?>

                <a href="laporan.php" class="btn btn-sm btn-lihat">
                    Lihat Laporan
                </a>

            <?php } ?>

        </div>

        <div class="table-responsive">

            <table class="table table-bordered table-hover mb-0">

                <thead>

                    <?php if ($role === 'wali_kelas') { ?>

                        <tr>
                            <th width="6%">No</th>
                            <th>NIS</th>
                            <th>Nama Siswa</th>
                            <th>Jumlah Pelanggaran</th>
                            <th>Status</th>
                        </tr>

                    <?php } else { ?>

                        <tr>
                            <th width="6%">No</th>
                            <th>Nama Siswa</th>
                            <th>Jenis Pelanggaran</th>
                            <th>Tanggal</th>
                            <th>Keterangan</th>
                        </tr>

                    <?php } ?>

                </thead>

                <tbody>

                    <?php

                    $no = 1;

                    if ($query_terbaru && mysqli_num_rows($query_terbaru) > 0) {

                        while ($data = mysqli_fetch_assoc($query_terbaru)) {

                    ?>

                        <tr>

                            <td><?= $no++; ?></td>

                            <?php if ($role === 'wali_kelas') { ?>

                                <td>
                                    <?= htmlspecialchars($data['nis'] ?? '-'); ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($data['nama_siswa']); ?>
                                </td>

                                <td>
                                    <?= (int) $data['total_pelanggaran']; ?>
                                </td>

                                <td>

                                    <?php if ((int) $data['total_pelanggaran'] > 0) { ?>

                                        <span class="badge bg-danger">
                                            Ada Pelanggaran
                                        </span>

                                    <?php } else { ?>

                                        <span class="badge bg-success">
                                            Belum Ada Pelanggaran
                                        </span>

                                    <?php } ?>

                                </td>

                            <?php } else { ?>

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

                            <?php } ?>

                        </tr>

                    <?php

                        }

                    } else {

                    ?>

                        <tr>
                            <td colspan="5" class="text-center">
                                <?= $role === 'wali_kelas'
                                    ? 'Belum ada siswa yang ditempatkan di kelas ini.'
                                    : 'Belum ada data pelanggaran.'; ?>
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