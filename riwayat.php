
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "database.php";
require_once "auth.php";

hanyaRole(['admin', 'guru']);

$role = $_SESSION['role'] ?? '';
$nama = $_SESSION['nama'] ?? 'Pengguna';

// =========================
// PENCARIAN
// =========================
$cari = trim($_GET['cari'] ?? '');

// =========================
// PAGINATION
// =========================
$batas = 5;
$halaman = max(1, (int)($_GET['halaman'] ?? 1));

$where = "";
$parameter = [];
$tipe = "";

if ($cari !== '') {
    $where = "
        WHERE siswa.nama LIKE ?
        OR jenis_pelanggaran.nama LIKE ?
        OR pelanggaran.status LIKE ?
        OR pelanggaran.keterangan LIKE ?
    ";

    $keyword = "%{$cari}%";

    $parameter = [
        $keyword,
        $keyword,
        $keyword,
        $keyword
    ];

    $tipe = "ssss";
}

// =========================
// HITUNG TOTAL DATA
// =========================
$sql_count = "
    SELECT COUNT(*) AS total
    FROM pelanggaran
    INNER JOIN siswa
        ON pelanggaran.siswa_id = siswa.id
    INNER JOIN jenis_pelanggaran
        ON pelanggaran.jenis_id = jenis_pelanggaran.id
    $where
";

$stmt_count = mysqli_prepare($koneksi, $sql_count);

if ($tipe !== '') {
    mysqli_stmt_bind_param($stmt_count, $tipe, ...$parameter);
}

mysqli_stmt_execute($stmt_count);

$result_count = mysqli_stmt_get_result($stmt_count);
$total_data = (int)mysqli_fetch_assoc($result_count)['total'];

mysqli_stmt_close($stmt_count);

$total_halaman = max(1, (int)ceil($total_data / $batas));

if ($halaman > $total_halaman) {
    $halaman = $total_halaman;
}

$mulai = ($halaman - 1) * $batas;

// =========================
// AMBIL DATA RIWAYAT
// =========================
$sql = "
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
    $where
    ORDER BY pelanggaran.tanggal DESC, pelanggaran.id DESC
    LIMIT ?, ?
";

$stmt = mysqli_prepare($koneksi, $sql);

if ($tipe !== '') {
    $tipe_data = $tipe . "ii";
    $parameter_data = array_merge($parameter, [$mulai, $batas]);

    mysqli_stmt_bind_param(
        $stmt,
        $tipe_data,
        ...$parameter_data
    );
} else {
    mysqli_stmt_bind_param($stmt, "ii", $mulai, $batas);
}

mysqli_stmt_execute($stmt);

$query = mysqli_stmt_get_result($stmt);

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

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            margin: 0;
            background: #f4f6f8;
            font-family: Arial, sans-serif;
            color: #333;
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
            background: #941b1b;
        }

        .content {
            margin-left: 220px;
            padding: 30px;
            min-height: 100vh;
        }

        .header-box,
        .main-box {
            background: white;
            border: 1px solid #ddd;
            border-radius: 7px;
        }

        .header-box {
            padding: 22px;
            margin-bottom: 20px;
        }

        .header-box h1 {
            font-size: 24px;
            font-weight: bold;
            margin: 0 0 10px;
        }

        .main-box {
            padding: 22px;
            width: 100%;
        }

        .main-box h2 {
            font-size: 21px;
            font-weight: bold;
            margin: 0 0 10px;
        }

        .deskripsi {
            color: #666;
            font-size: 14px;
            margin-bottom: 20px;
        }

        .table {
            font-size: 13px;
            vertical-align: middle;
        }

        .table thead th {
            background: #687280;
            color: white;
            font-weight: 600;
            white-space: nowrap;
            padding: 12px 10px;
        }

        .table tbody td {
            padding: 11px 10px;
        }

        .table tbody tr:hover td {
            background: #f7f7f7;
        }

        .btn-cari {
            background: #687280;
            color: white;
        }

        .btn-cari:hover {
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

<!-- SIDEBAR -->
<div class="sidebar">

    <div class="sidebar-title">
        ⚖ Pelanggaran<br>Siswa
    </div>

    <nav class="nav flex-column">

        <a href="dashboard.php" class="nav-link">
             Dashboard
        </a>

        <?php if ($role === 'admin'): ?>

            <a href="kelola_siswa.php" class="nav-link">Data Siswa</a>
            <a href="kelola_guru.php" class="nav-link">Data Guru</a>
            <a href="kelola_kelas.php" class="nav-link">Data Kelas</a>
            <a href="kelola_tahun_ajaran.php" class="nav-link">Tahun Ajaran</a>
            <a href="kelola_wali_kelas.php" class="nav-link">Wali Kelas</a>
            <a href="penempatan_siswa.php" class="nav-link">Penempatan Siswa</a>
            <a href="kelola_pelanggaran_kategori.php" class="nav-link"> Kategori Pelanggaran</a>
            <a href="pelanggaran.php" class="nav-link">Data Pelanggaran</a>
            <a href="tindakan.php" class="nav-link">Data Tindakan</a>
            <a href="riwayat.php" class="nav-link active">Riwayat</a>
            <a href="laporan.php" class="nav-link">Laporan</a>
            <a href="about_me.php" class="nav-link">ℹ About Me</a>

        <?php else: ?>

            <a href="catat_pelanggaran.php" class="nav-link">Catat Pelanggaran</a>
            <a href="pelanggaran.php" class="nav-link">Data Pelanggaran</a>
            <a href="tindakan.php" class="nav-link">Data Tindakan</a>
            <a href="riwayat.php" class="nav-link active">Riwayat</a>
            <a href="rekap_poin.php" class="nav-link">Rekap Poin</a>

        <?php endif; ?>

        <a href="logout.php" class="nav-link logout">
             Logout
        </a>

    </nav>

</div>

<!-- CONTENT -->
<div class="content">

    <div class="header-box">

        <h1>Riwayat Pelanggaran</h1>

        <p class="text-secondary mb-0">
            Daftar riwayat pelanggaran siswa.
        </p>

    </div>

    <div class="main-box">

        <h2>Daftar Riwayat Pelanggaran</h2>

        <p class="deskripsi">
            Informasi seluruh catatan riwayat pelanggaran siswa.
        </p>

        <!-- PENCARIAN -->
        <form method="GET" class="row g-2 mb-3">

            <div class="col-md-5">
                <input
                    type="text"
                    name="cari"
                    class="form-control"
                    placeholder="Cari nama, jenis, status..."
                    value="<?= htmlspecialchars($cari); ?>"
                >
            </div>

            <div class="col-auto">
                <button type="submit" class="btn btn-cari">
                    Cari
                </button>

                <a href="riwayat.php" class="btn btn-secondary">
                    Reset
                </a>
            </div>

        </form>

        <div class="mb-3 text-secondary small">
            Total data: <?= $total_data; ?> riwayat
        </div>

        <!-- TABEL -->
        <div class="table-responsive">

            <table class="table table-bordered table-hover">

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

                    <?php if (mysqli_num_rows($query) > 0): ?>

                        <?php
                        $no = $mulai + 1;

                        while ($data = mysqli_fetch_assoc($query)):
                            $status = $data['status'] ?? '-';

                            $warna_status = 'bg-info-subtle text-primary';

                            if ($status === 'selesai') {
                                $warna_status = 'bg-success-subtle text-success';
                            } elseif ($status === 'ditindaklanjuti') {
                                $warna_status = 'bg-warning-subtle text-dark';
                            } elseif ($status === 'tercatat') {
                                $warna_status = 'bg-info-subtle text-primary';
                            }
                        ?>

                            <tr>

                                <td><?= $no++; ?></td>

                                <td>
                                    <strong>
                                        <?= htmlspecialchars($data['nama_siswa']); ?>
                                    </strong>
                                </td>

                                <td>
                                    <?= htmlspecialchars($data['jenis']); ?>
                                </td>

                                <td>
                                    <?= (int)$data['poin']; ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($data['tanggal']); ?>
                                </td>

                                <td>
                                    <span class="badge <?= $warna_status; ?> fw-normal">
                                        <?= htmlspecialchars($status); ?>
                                    </span>
                                </td>

                                <td>
                                    <?= htmlspecialchars($data['keterangan'] ?? '-'); ?>
                                </td>

                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="7" class="text-center py-4 text-secondary">
                                Belum ada riwayat pelanggaran.
                            </td>
                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

        <!-- PAGINATION -->
        <?php if ($total_halaman > 1): ?>

            <nav class="mt-3">

                <ul class="pagination justify-content-end">

                    <li class="page-item <?= $halaman <= 1 ? 'disabled' : ''; ?>">
                        <a
                            class="page-link"
                            href="?halaman=<?= $halaman - 1; ?>&cari=<?= urlencode($cari); ?>"
                        >
                            Sebelumnya
                        </a>
                    </li>

                    <?php for ($i = 1; $i <= $total_halaman; $i++): ?>

                        <li class="page-item <?= $halaman === $i ? 'active' : ''; ?>">
                            <a
                                class="page-link"
                                href="?halaman=<?= $i; ?>&cari=<?= urlencode($cari); ?>"
                            >
                                <?= $i; ?>
                            </a>
                        </li>

                    <?php endfor; ?>

                    <li class="page-item <?= $halaman >= $total_halaman ? 'disabled' : ''; ?>">
                        <a
                            class="page-link"
                            href="?halaman=<?= $halaman + 1; ?>&cari=<?= urlencode($cari); ?>"
                        >
                            Berikutnya
                        </a>
                    </li>

                </ul>

            </nav>

        <?php endif; ?>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
