
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "database.php";
require_once "auth.php";

// Hanya Guru dan Wali Kelas
hanyaRole(['guru', 'wali_kelas']);

$role = $_SESSION['role'] ?? '';
$nama = $_SESSION['nama'] ?? 'Pengguna';

$cari = trim($_GET['cari'] ?? '');

$whereParts = [];
$parameter = [];
$tipe = "";

// =========================
// BATAS AKSES WALI KELAS
// =========================
if ($role === 'wali_kelas') {

    $email = $_SESSION['email'] ?? '';

    if ($email === '') {
        die("Akses ditolak. Email akun tidak ditemukan.");
    }

    $stmt_wali = mysqli_prepare($koneksi, "
        SELECT
            wk.kelas_id,
            ta.id AS tahun_ajaran_id
        FROM users u
        INNER JOIN wali_kelas wk
            ON u.guru_id = wk.guru_id
        INNER JOIN tahun_ajaran ta
            ON wk.tahun_ajaran_id = ta.id
        WHERE u.email = ?
          AND u.role = 'wali_kelas'
          AND u.aktif = 1
          AND ta.aktif = 1
        LIMIT 1
    ");

    mysqli_stmt_bind_param($stmt_wali, "s", $email);
    mysqli_stmt_execute($stmt_wali);

    $hasil_wali = mysqli_stmt_get_result($stmt_wali);
    $data_wali = mysqli_fetch_assoc($hasil_wali);

    mysqli_stmt_close($stmt_wali);

    if (!$data_wali) {
        die("Akses ditolak. Anda belum memiliki penempatan sebagai Wali Kelas pada tahun ajaran aktif.");
    }

    $kelas_wali_id = (int)$data_wali['kelas_id'];
    $tahun_aktif_id = (int)$data_wali['tahun_ajaran_id'];

    $whereParts[] = "
        EXISTS (
            SELECT 1
            FROM penempatan_siswa ps
            WHERE ps.siswa_id = siswa.id
              AND ps.kelas_id = ?
              AND ps.tahun_ajaran_id = ?
        )
    ";

    $parameter[] = $kelas_wali_id;
    $parameter[] = $tahun_aktif_id;
    $tipe .= "ii";
}

// =========================
// PENCARIAN
// =========================
if ($cari !== '') {

    $whereParts[] = "(
        siswa.nama LIKE ?
        OR jenis_pelanggaran.nama LIKE ?
        OR tindakan.tindakan LIKE ?
        OR users.nama LIKE ?
        OR tindakan.catatan LIKE ?
    )";

    $keyword = "%{$cari}%";

    $parameter[] = $keyword;
    $parameter[] = $keyword;
    $parameter[] = $keyword;
    $parameter[] = $keyword;
    $parameter[] = $keyword;

    $tipe .= "sssss";
}

$where = "";

if (!empty($whereParts)) {
    $where = "WHERE " . implode(" AND ", $whereParts);
}

// =========================
// PAGINATION
// =========================
$batas = 5;
$halaman = max(1, (int)($_GET['halaman'] ?? 1));

// =========================
// HITUNG TOTAL DATA
// =========================
$sql_count = "
    SELECT COUNT(*) AS total
    FROM tindakan
    INNER JOIN pelanggaran
        ON tindakan.pelanggaran_id = pelanggaran.id
    INNER JOIN siswa
        ON pelanggaran.siswa_id = siswa.id
    INNER JOIN jenis_pelanggaran
        ON pelanggaran.jenis_id = jenis_pelanggaran.id
    LEFT JOIN users
        ON tindakan.petugas_id = users.id
    $where
";

$stmt_count = mysqli_prepare($koneksi, $sql_count);

if (!empty($parameter)) {
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
// AMBIL DATA RIWAYAT TINDAKAN
// =========================
$sql = "
    SELECT
        tindakan.id,
        siswa.nama AS nama_siswa,
        jenis_pelanggaran.nama AS jenis,
        jenis_pelanggaran.poin,
        tindakan.tanggal,
        tindakan.tindakan,
        users.nama AS nama_petugas,
        tindakan.catatan
    FROM tindakan
    INNER JOIN pelanggaran
        ON tindakan.pelanggaran_id = pelanggaran.id
    INNER JOIN siswa
        ON pelanggaran.siswa_id = siswa.id
    INNER JOIN jenis_pelanggaran
        ON pelanggaran.jenis_id = jenis_pelanggaran.id
    LEFT JOIN users
        ON tindakan.petugas_id = users.id
    $where
    ORDER BY tindakan.tanggal DESC, tindakan.id DESC
    LIMIT ?, ?
";

$stmt = mysqli_prepare($koneksi, $sql);

$parameter_data = $parameter;
$parameter_data[] = $mulai;
$parameter_data[] = $batas;
$tipe_data = $tipe . "ii";

mysqli_stmt_bind_param($stmt, $tipe_data, ...$parameter_data);
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
    <title>Riwayat Tindakan</title>

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

<div class="sidebar">

    <div class="sidebar-title">
        Pelanggaran<br>Siswa
    </div>

    <nav class="nav flex-column">

        <a href="dashboard.php" class="nav-link">Dashboard</a>

        <?php if ($role === 'guru'): ?>
            <a href="catat_pelanggaran.php" class="nav-link">Catat Pelanggaran</a>
            <a href="pelanggaran.php" class="nav-link">Data Pelanggaran</a>
            <a href="tindakan.php" class="nav-link">Data Tindakan</a>

        <?php elseif ($role === 'wali_kelas'): ?>
            <a href="pelanggaran.php" class="nav-link">Data Pelanggaran</a>
            <a href="tindakan.php" class="nav-link">Data Tindakan</a>
        <?php endif; ?>

        <a href="riwayat.php" class="nav-link active">Riwayat</a>
        <a href="rekap_poin.php" class="nav-link">Rekap Poin</a>

        <a href="logout.php" class="nav-link logout">Logout</a>

    </nav>
</div>

<div class="content">

    <div class="header-box">
        <h1>Riwayat Pelanggaran</h1>
        <p class="text-secondary mb-0">
            Daftar riwayat tindakan siswa.
        </p>
    </div>

    <div class="main-box">

        <h2>Daftar Riwayat Pelanggaran</h2>

        <p class="deskripsi">
            Informasi riwayat tindakan siswa.
        </p>

        <form method="GET" class="row g-2 mb-3">

            <div class="col-md-5">
                <input
                    type="text"
                    name="cari"
                    class="form-control"
                    placeholder="Cari nama, jenis, tindakan, petugas..."
                    value="<?= htmlspecialchars($cari); ?>"
                >
            </div>

            <div class="col-auto">
                <button type="submit" class="btn btn-cari">Cari</button>
                <a href="riwayat.php" class="btn btn-secondary">Reset</a>
            </div>

        </form>

        <div class="mb-3 text-secondary small">
            Total data: <?= $total_data; ?> riwayat
        </div>

        <div class="table-responsive">

            <table class="table table-bordered table-hover">

                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Siswa</th>
                        <th>Jenis Pelanggaran</th>
                        <th>Poin</th>
                        <th>Tanggal Tindakan</th>
                        <th>Tindakan</th>
                        <th>Petugas</th>
                        <th>Catatan</th>
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
                                    <strong><?= htmlspecialchars($data['nama_siswa']); ?></strong>
                                </td>

                                <td><?= htmlspecialchars($data['jenis']); ?></td>

                                <td><?= (int)$data['poin']; ?></td>

                                <td><?= htmlspecialchars($data['tanggal']); ?></td>

                                <td><?= nl2br(htmlspecialchars($data['tindakan'])); ?></td>

                                <td><?= htmlspecialchars($data['nama_petugas'] ?? '-'); ?></td>

                                <td><?= nl2br(htmlspecialchars($data['catatan'] ?? '-')); ?></td>
                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="8" class="text-center py-4 text-secondary">
                                Belum ada riwayat tindakan.
                            </td>
                        </tr>

                    <?php endif; ?>

                </tbody>
            </table>
        </div>

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

<?php
mysqli_stmt_close($stmt);
?>
