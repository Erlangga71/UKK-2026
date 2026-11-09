
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

$where = "";
$parameter = [];
$tipe = "";

if ($cari !== '') {
    $where = "WHERE siswa.nis LIKE ? OR siswa.nama LIKE ?";

    $keyword = "%{$cari}%";

    $parameter = [$keyword, $keyword];
    $tipe = "ss";
}

// =========================
// PAGINATION
// =========================
$batas = 10;
$halaman = max(1, (int)($_GET['halaman'] ?? 1));

// =========================
// HITUNG TOTAL DATA
// =========================
$sql_jumlah = "
    SELECT COUNT(*) AS total
    FROM siswa
    $where
";

$stmt_jumlah = mysqli_prepare($koneksi, $sql_jumlah);

if ($tipe !== '') {
    mysqli_stmt_bind_param($stmt_jumlah, $tipe, ...$parameter);
}

mysqli_stmt_execute($stmt_jumlah);

$result_jumlah = mysqli_stmt_get_result($stmt_jumlah);
$jumlah_data = (int)mysqli_fetch_assoc($result_jumlah)['total'];

mysqli_stmt_close($stmt_jumlah);

$total_halaman = max(1, (int)ceil($jumlah_data / $batas));

if ($halaman > $total_halaman) {
    $halaman = $total_halaman;
}

$mulai = ($halaman - 1) * $batas;

// =========================
// AMBIL DATA REKAP POIN
// =========================
$sql = "
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
    die("Query rekap poin gagal: " . mysqli_error($koneksi));
}
?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Rekap Poin Siswa</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            margin: 0;
            background: #f4f6f8;
            font-family: Arial, sans-serif;
            color: #333;
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

        /* CONTENT */
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
            margin-bottom: 10px;
        }

        .deskripsi {
            color: #666;
            font-size: 14px;
            margin-bottom: 20px;
        }

        /* TABEL */
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

        .poin {
            color: #dc2626;
            font-weight: bold;
        }

        .aman {
            color: #16a34a;
            font-weight: bold;
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
            <a href="riwayat.php" class="nav-link">Riwayat</a>
            <a href="rekap_poin.php" class="nav-link active">Rekap Poin</a>
            <a href="laporan.php" class="nav-link">Laporan</a>
            <a href="about_me.php" class="nav-link">ℹ About Me</a>

        <?php else: ?>

            <a href="catat_pelanggaran.php" class="nav-link">Catat Pelanggaran</a>
            <a href="pelanggaran.php" class="nav-link">Data Pelanggaran</a>
            <a href="tindakan.php" class="nav-link">Data Tindakan</a>
            <a href="riwayat.php" class="nav-link">Riwayat</a>
            <a href="rekap_poin.php" class="nav-link active">Rekap Poin</a>

        <?php endif; ?>

        <a href="logout.php" class="nav-link logout">
             Logout
        </a>

    </nav>

</div>

<!-- CONTENT -->
<div class="content">

    <div class="header-box">

        <h1>Rekap Poin Siswa</h1>

        <p class="text-secondary mb-0">
            Informasi total poin pelanggaran setiap siswa.
        </p>

    </div>

    <div class="main-box">

        <h2>Daftar Rekap Poin</h2>

        <p class="deskripsi">
            Daftar total poin pelanggaran seluruh siswa.
        </p>

        <!-- PENCARIAN -->
        <form method="GET" class="row g-2 mb-3">

            <div class="col-md-5">

                <input
                    type="text"
                    name="cari"
                    class="form-control"
                    placeholder="Cari NIS atau nama siswa..."
                    value="<?= htmlspecialchars($cari); ?>"
                >

            </div>

            <div class="col-auto">

                <button type="submit" class="btn btn-cari">
                    Cari
                </button>

                <a href="rekap_poin.php" class="btn btn-secondary">
                    Reset
                </a>

            </div>

        </form>

        <div class="mb-3 text-secondary small">
            Total data: <?= $jumlah_data; ?> siswa
        </div>

        <!-- TABEL -->
        <div class="table-responsive">

            <table class="table table-bordered table-hover">

                <thead>

                    <tr>
                        <th>No</th>
                        <th>NIS</th>
                        <th>Nama Siswa</th>
                        <th>Total Poin</th>
                    </tr>

                </thead>

                <tbody>

                    <?php if (mysqli_num_rows($query) > 0): ?>

                        <?php
                        $no = $mulai + 1;

                        while ($data = mysqli_fetch_assoc($query)):
                        ?>

                            <tr>

                                <td><?= $no++; ?></td>

                                <td>
                                    <?= htmlspecialchars($data['nis']); ?>
                                </td>

                                <td>
                                    <strong>
                                        <?= htmlspecialchars($data['nama']); ?>
                                    </strong>
                                </td>

                                <td>

                                    <?php if ((int)$data['total_poin'] > 0): ?>

                                        <span class="poin">
                                            <?= (int)$data['total_poin']; ?> poin
                                        </span>

                                    <?php else: ?>

                                        <span class="aman">
                                            0 poin
                                        </span>

                                    <?php endif; ?>

                                </td>

                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="4" class="text-center py-4 text-secondary">
                                Data siswa tidak ditemukan.
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
