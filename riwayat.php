
<?php

session_start();

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

include "database.php";

$role = $_SESSION['role'] ?? '';

if (!in_array($role, ['admin', 'guru', 'wali_kelas'])) {
    die("Akses ditolak.");
}

/* =========================
   DATA RIWAYAT PELANGGARAN
========================= */

$query = mysqli_query($koneksi, "
    SELECT
        pelanggaran.id,
        siswa.nama AS nama_siswa,
        jenis_pelanggaran.nama AS jenis,
        jenis_pelanggaran.poin,
        pelanggaran.tanggal,
        pelanggaran.status,
        pelanggaran.keterangan
    FROM pelanggaran

    INNER JOIN siswa
        ON pelanggaran.siswa_id = siswa.id

    INNER JOIN jenis_pelanggaran
        ON pelanggaran.jenis_id = jenis_pelanggaran.id

    ORDER BY pelanggaran.tanggal DESC
");

if (!$query) {
    die("Query error: " . mysqli_error($koneksi));
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Riwayat Pelanggaran</title>

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

        .sidebar a:hover,
        .sidebar a.active {
            background: #4f5965;
        }

        .logout {
            margin-top: 20px;
            background: #b42323;
        }

        /* CONTENT */

        .content {
            margin-left: 220px;
            padding: 30px;
        }

        /* HEADER */

        .header {
            background: white;
            border: 1px solid #ddd;
            border-radius: 7px;
            padding: 22px;
            margin-bottom: 20px;
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
        }

        .header p {
            color: #777;
            margin-bottom: 0;
        }

        /* BOX */

        .box {
            background: white;
            border: 1px solid #ddd;
            border-radius: 7px;
            padding: 22px;
            margin-bottom: 20px;
        }

        .box h2 {
            margin-top: 0;
        }

        .deskripsi {
            color: #666;
            font-size: 14px;
            margin-bottom: 20px;
        }

        /* TABLE */

        .table-container {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #687280;
            color: white;
            padding: 11px;
            text-align: left;
            font-size: 13px;
        }

        td {
            padding: 11px;
            border-bottom: 1px solid #ddd;
            font-size: 13px;
        }

        tr:hover {
            background: #f7f7f7;
        }

        .status {
            display: inline-block;
            padding: 5px 9px;
            border-radius: 5px;
            font-size: 12px;
            background: #e0f2fe;
            color: #075985;
        }

        .kosong {
            text-align: center;
            padding: 25px;
        }

        /* RESPONSIVE */

        @media(max-width:700px) {

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
        ⚖ Pelanggaran<br>
        Siswa
    </div>

    <?php if ($role == 'admin') { ?>

        <!-- MENU ADMIN -->

        <a href="dashboard.php">
            🏠 Dashboard
        </a>

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

        <a href="about_me.php">
            ℹ About Me
        </a>

    <?php } else { ?>

        <!-- MENU GURU DAN WALI KELAS -->

        <a href="dashboard.php">
            🏠 Dashboard
        </a>

        <a href="catat_pelanggaran.php">
            Catat Pelanggaran
        </a>

        <a href="tindakan.php">
            Tindakan
        </a>

        <a href="riwayat.php" class="active">
            Riwayat
        </a>

        <a href="rekap_poin.php">
            Rekap Poin
        </a>

    <?php } ?>

    <a href="logout.php" class="logout">
        🚪 Logout
    </a>

</div>


<!-- CONTENT -->

<div class="content">

    <div class="header">

        <h1>Riwayat Pelanggaran</h1>

        <p>
            Daftar riwayat pelanggaran siswa.
        </p>

    </div>


    <div class="box">

        <h2>Daftar Riwayat Pelanggaran</h2>

        <p class="deskripsi">
            Informasi seluruh catatan riwayat pelanggaran siswa.
        </p>

        <div class="table-container">

            <table>

                <thead>

                    <tr>
                        <th>No</th>
                        <th>Nama Siswa</th>
                        <th>Jenis Pelanggaran</th>
                        <th>Poin</th>
                        <th>Tanggal</th>
                        <th>Status</th>
                        <th>Keterangan</th>
                    </tr>

                </thead>

                <tbody>

                    <?php

                    $no = 1;

                    if (mysqli_num_rows($query) > 0) {

                        while ($data = mysqli_fetch_assoc($query)) {

                    ?>

                        <tr>

                            <td>
                                <?= $no++; ?>
                            </td>

                            <td>
                                <b>
                                    <?= htmlspecialchars($data['nama_siswa']); ?>
                                </b>
                            </td>

                            <td>
                                <?= htmlspecialchars($data['jenis']); ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($data['poin']); ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($data['tanggal']); ?>
                            </td>

                            <td>
                                <span class="status">
                                    <?= htmlspecialchars($data['status']); ?>
                                </span>
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

                            <td colspan="7" class="kosong">
                                Belum ada riwayat pelanggaran.
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
