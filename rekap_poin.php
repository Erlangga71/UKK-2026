
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

// PENGATURAN PENCARIAN
$cari = trim($_GET['cari'] ?? '');
$cari_sql = mysqli_real_escape_string($koneksi, $cari);

// PAGINATION
$batas = 10;
$halaman = isset($_GET['halaman']) ? (int)$_GET['halaman'] : 1;

if ($halaman < 1) {
    $halaman = 1;
}

// FILTER PENCARIAN
$where = "";

if ($cari != "") {
    $where = "WHERE siswa.nis LIKE '%$cari_sql%'
              OR siswa.nama LIKE '%$cari_sql%'";
}

// HITUNG JUMLAH DATA
$query_jumlah = mysqli_query($koneksi, "
    SELECT COUNT(*) AS total
    FROM siswa
    $where
");

if (!$query_jumlah) {
    die("Query jumlah error: " . mysqli_error($koneksi));
}

$jumlah_data = mysqli_fetch_assoc($query_jumlah)['total'];

$total_halaman = max(1, ceil($jumlah_data / $batas));

if ($halaman > $total_halaman) {
    $halaman = $total_halaman;
}

$mulai = ($halaman - 1) * $batas;

// AMBIL DATA REKAP POIN
$query = mysqli_query($koneksi, "
    SELECT
        siswa.id,
        siswa.nis,
        siswa.nama,
        COALESCE(SUM(jenis_pelanggaran.poin), 0) AS total_poin
    FROM siswa

    LEFT JOIN pelanggaran
        ON siswa.id = pelanggaran.siswa_id

    LEFT JOIN jenis_pelanggaran
        ON pelanggaran.jenis_id = jenis_pelanggaran.id

    $where

    GROUP BY
        siswa.id,
        siswa.nis,
        siswa.nama

    ORDER BY total_poin DESC, siswa.nama ASC

    LIMIT $mulai, $batas
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
    <title>Rekap Poin Siswa</title>

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
            width: 220px;
            height: 100vh;
            background: #687280;
            position: fixed;
            top: 0;
            left: 0;
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
            color: white;
            text-decoration: none;
            padding: 11px 13px;
            margin-bottom: 5px;
            border-radius: 5px;
            font-size: 14px;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background: #4f5965;
        }

        .sidebar .logout {
            background: #b42323;
            margin-top: 20px;
        }

        /* CONTENT */
        .content {
            margin-left: 220px;
            padding: 30px;
        }

        .header {
            background: white;
            border: 1px solid #ddd;
            border-radius: 7px;
            padding: 22px;
            margin-bottom: 20px;
        }

        .header h2 {
            margin: 0;
            font-size: 21px;
            color: #333;
        }

        .box {
            background: white;
            border: 1px solid #ddd;
            border-radius: 7px;
            padding: 22px;
        }

        .box h3 {
            margin-top: 0;
            margin-bottom: 8px;
        }

        .deskripsi {
            color: #666;
            font-size: 14px;
            margin-bottom: 20px;
        }

        /* PENCARIAN */
        .pencarian {
            display: flex;
            gap: 8px;
            margin-bottom: 18px;
            flex-wrap: wrap;
        }

        .pencarian input {
            width: 280px;
            max-width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .btn {
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
        }

        .btn-cari {
            background: #087f5b;
            color: white;
        }

        .btn-reset {
            background: #687280;
            color: white;
        }

        /* TABEL */
        .table-container {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th {
            background: #687280;
            color: white;
            padding: 12px;
            text-align: left;
        }

        td {
            padding: 11px;
            border-bottom: 1px solid #ddd;
            font-size: 14px;
        }

        tr:hover {
            background: #f1f5f9;
        }

        .poin {
            font-weight: bold;
            color: #dc2626;
        }

        .aman {
            color: #16a34a;
            font-weight: bold;
        }

        .kosong {
            text-align: center;
            padding: 20px;
        }

        /* PAGINATION */
        .pagination {
            display: flex;
            gap: 5px;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        .pagination a {
            padding: 8px 12px;
            border: 1px solid #ddd;
            text-decoration: none;
            color: #333;
            border-radius: 4px;
            font-size: 14px;
        }

        .pagination a.aktif {
            background: #687280;
            color: white;
            border-color: #687280;
        }

        .info {
            margin-top: 15px;
            font-size: 13px;
            color: #666;
        }

        @media (max-width: 700px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }

            .content {
                margin-left: 0;
                padding: 15px;
            }

            .pencarian input {
                width: 100%;
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

        <a href="dashboard.php">🏠 Dashboard</a>
        <a href="siswa.php">Data Siswa</a>
        <a href="guru.php">Data Guru</a>
        <a href="kelas.php">Data Kelas</a>
        <a href="penempatan_siswa.php">Penempatan Siswa</a>
        <a href="jenis_pelanggaran.php">Jenis Pelanggaran</a>
        <a href="catat_pelanggaran.php">Catat Pelanggaran</a>
        <a href="rekap_poin.php" class="active">Rekap Poin</a>

    <?php } else { ?>

        <a href="dashboard.php">🏠 Dashboard</a>
        <a href="catat_pelanggaran.php">Catat Pelanggaran</a>
        <a href="tindakan.php">Tindakan</a>
        <a href="riwayat.php">Riwayat</a>
        <a href="rekap_poin.php" class="active">Rekap Poin</a>

    <?php } ?>

    <a href="logout.php" class="logout">🚪 Logout</a>

</div>

<!-- CONTENT -->
<div class="content">

    <div class="header">
        <h2>Rekap Poin Siswa</h2>
    </div>

    <div class="box">

        <h3>Daftar Rekap Poin</h3>

        <p class="deskripsi">
            Daftar total poin pelanggaran setiap siswa.
        </p>

        <!-- PENCARIAN -->
        <form method="GET" class="pencarian">

            <input
                type="text"
                name="cari"
                placeholder="Cari NIS atau nama siswa..."
                value="<?= htmlspecialchars($cari); ?>"
            >

            <button type="submit" class="btn btn-cari">
                Cari
            </button>

            <a href="rekap_poin.php" class="btn btn-reset">
                Reset
            </a>

        </form>

        <!-- TABEL -->
        <div class="table-container">

            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>NIS</th>
                        <th>Nama Siswa</th>
                        <th>Total Poin</th>
                    </tr>
                </thead>

                <tbody>

                    <?php
                    $no = $mulai + 1;

                    if (mysqli_num_rows($query) > 0) {

                        while ($data = mysqli_fetch_assoc($query)) {
                    ?>

                        <tr>
                            <td><?= $no++; ?></td>

                            <td>
                                <?= htmlspecialchars($data['nis']); ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($data['nama']); ?>
                            </td>

                            <td>
                                <?php if ($data['total_poin'] > 0) { ?>
                                    <span class="poin">
                                        <?= htmlspecialchars($data['total_poin']); ?> poin
                                    </span>
                                <?php } else { ?>
                                    <span class="aman">0 poin</span>
                                <?php } ?>
                            </td>
                        </tr>

                    <?php
                        }

                    } else {
                    ?>

                        <tr>
                            <td colspan="4" class="kosong">
                                Data siswa tidak ditemukan.
                            </td>
                        </tr>

                    <?php } ?>

                </tbody>
            </table>

        </div>

        <div class="info">
            Total data: <?= $jumlah_data; ?> siswa
        </div>

        <!-- PAGINATION -->
        <?php if ($total_halaman > 1): ?>

            <div class="pagination">

                <?php for ($i = 1; $i <= $total_halaman; $i++): ?>

                    <a
                        href="?halaman=<?= $i; ?>&cari=<?= urlencode($cari); ?>"
                        class="<?= ($halaman == $i) ? 'aktif' : ''; ?>"
                    >
                        <?= $i; ?>
                    </a>

                <?php endfor; ?>

            </div>

        <?php endif; ?>

    </div>

</div>

</body>
</html>
