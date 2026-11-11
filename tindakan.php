
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "database.php";
require_once "auth.php";

hanyaRole(['guru', 'wali_kelas']);

$role = $_SESSION['role'] ?? '';
$nama = $_SESSION['nama'] ?? 'Pengguna';

$cari = trim($_GET['cari'] ?? '');

/* =========================
   FILTER DATA
========================= */

$whereParts = ["p.status = 'tercatat'"];
$parameter = [];
$tipe = '';

/* =========================
   BATAS AKSES WALI KELAS
========================= */

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
        LEFT JOIN guru g
            ON g.id = u.guru_id
            OR (u.guru_id IS NULL AND g.email = u.email)
        INNER JOIN wali_kelas wk
            ON wk.guru_id = COALESCE(u.guru_id, g.id)
        INNER JOIN tahun_ajaran ta
            ON wk.tahun_ajaran_id = ta.id
        WHERE u.email = ?
          AND u.role = 'wali_kelas'
          AND u.aktif = 1
          AND ta.aktif = 1
        LIMIT 1
    ");

    if (!$stmt_wali) {
        die("Query Wali Kelas gagal: " . mysqli_error($koneksi));
    }

    mysqli_stmt_bind_param($stmt_wali, "s", $email);
    mysqli_stmt_execute($stmt_wali);

    $hasil_wali = mysqli_stmt_get_result($stmt_wali);
    $data_wali = mysqli_fetch_assoc($hasil_wali);

    mysqli_stmt_close($stmt_wali);

    if (!$data_wali) {
        die("Akses ditolak. Penempatan Wali Kelas aktif tidak ditemukan.");
    }

    $kelas_wali_id = (int)$data_wali['kelas_id'];
    $tahun_aktif_id = (int)$data_wali['tahun_ajaran_id'];

    $whereParts[] = "
        EXISTS (
            SELECT 1
            FROM penempatan_siswa ps
            WHERE ps.siswa_id = s.id
              AND ps.kelas_id = ?
              AND ps.tahun_ajaran_id = ?
        )
    ";

    $parameter[] = $kelas_wali_id;
    $parameter[] = $tahun_aktif_id;
    $tipe .= "ii";
}

/* =========================
   PENCARIAN
========================= */

if ($cari !== '') {

    $whereParts[] = "(
        s.nama LIKE ?
        OR s.nis LIKE ?
        OR jp.nama LIKE ?
        OR p.tanggal LIKE ?
    )";

    $keyword = "%{$cari}%";

    $parameter[] = $keyword;
    $parameter[] = $keyword;
    $parameter[] = $keyword;
    $parameter[] = $keyword;

    $tipe .= "ssss";
}

$where = "WHERE " . implode(" AND ", $whereParts);

/* =========================
   PAGINATION
========================= */

$batas = 5;
$halaman = max(1, (int)($_GET['halaman'] ?? 1));

/* =========================
   HITUNG TOTAL DATA
========================= */

$sql_count = "
    SELECT COUNT(*) AS total
    FROM pelanggaran p
    INNER JOIN siswa s
        ON p.siswa_id = s.id
    INNER JOIN jenis_pelanggaran jp
        ON p.jenis_id = jp.id
    $where
";

$stmt_count = mysqli_prepare($koneksi, $sql_count);

if (!$stmt_count) {
    die("Query hitung data gagal: " . mysqli_error($koneksi));
}

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

/* =========================
   AMBIL DATA PELANGGARAN
   YANG BELUM DIPROSES
========================= */

$sql = "
    SELECT
        p.id,
        p.tanggal,
        s.nama AS nama_siswa,
        s.nis,
        jp.nama AS jenis,
        jp.poin
    FROM pelanggaran p
    INNER JOIN siswa s
        ON p.siswa_id = s.id
    INNER JOIN jenis_pelanggaran jp
        ON p.jenis_id = jp.id
    $where
    ORDER BY p.tanggal DESC, p.id DESC
    LIMIT ?, ?
";

$stmt = mysqli_prepare($koneksi, $sql);

if (!$stmt) {
    die("Query data tindakan gagal: " . mysqli_error($koneksi));
}

$parameter_data = $parameter;
$parameter_data[] = $mulai;
$parameter_data[] = $batas;

$tipe_data = $tipe . "ii";

mysqli_stmt_bind_param($stmt, $tipe_data, ...$parameter_data);
mysqli_stmt_execute($stmt);

$query = mysqli_stmt_get_result($stmt);

if (!$query) {
    die("Query data tindakan gagal: " . mysqli_error($koneksi));
}
?>

<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Tindakan</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

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

/* CARD */

.card {
    border: 1px solid #ddd;
    border-radius: 6px;
    box-shadow: none;
}

.card-body {
    padding: 20px;
}

/* TABLE */

.table {
    font-size: 12px;
    margin-bottom: 10px;
}

.table th,
.table td {
    padding: 10px;
    vertical-align: middle;
}

.table th {
    white-space: nowrap;
}

/* BUTTON */

.btn-proses {
    background: #9dff00;
    color: #1a3300;
    font-weight: bold;
    border: none;
}

.btn-proses:hover {
    background: #86e000;
    color: #1a3300;
}

/* PAGINATION */

.page-link {
    color: #687280;
}

.page-item.active .page-link {
    background: #687280;
    border-color: #687280;
    color: white;
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
}

</style>

</head>

<body>

<!-- SIDEBAR BERSAMA -->

<?php
$menu_aktif = 'tindakan';
require_once "sidebar.php";
?>

<!-- CONTENT -->

<div class="content">

    <!-- HEADER -->

    <div class="card mb-3">

        <div class="card-body">

            <h4 class="mb-1">Tindakan</h4>

            <p class="text-muted mb-0">
                Daftar pelanggaran siswa yang belum diproses.
            </p>

        </div>

    </div>

    <!-- DATA TINDAKAN -->

    <div class="card">

        <div class="card-body">

            <h5 class="mb-3">Pelanggaran Menunggu Tindakan</h5>

            <!-- PENCARIAN -->

            <form method="GET" class="row g-2 mb-3">

                <div class="col-md-5">

                    <input
                        type="text"
                        name="cari"
                        class="form-control form-control-sm"
                        placeholder="Cari nama, NIS, atau pelanggaran..."
                        value="<?= htmlspecialchars($cari); ?>"
                    >

                </div>

                <div class="col-auto">

                    <button type="submit" class="btn btn-secondary btn-sm">
                        Cari
                    </button>

                    <?php if ($cari !== ''): ?>

                        <a href="tindakan.php" class="btn btn-outline-secondary btn-sm">
                            Reset
                        </a>

                    <?php endif; ?>

                </div>

            </form>

            <div class="mb-3 text-muted small">
                Total data: <?= $total_data; ?> pelanggaran menunggu tindakan
            </div>

            <!-- TABEL -->

            <div class="table-responsive">

                <table class="table table-bordered table-hover align-middle">

                    <thead class="table-secondary">

                        <tr>
                            <th width="55">No</th>
                            <th>NIS</th>
                            <th>Nama Siswa</th>
                            <th>Jenis Pelanggaran</th>
                            <th>Poin</th>
                            <th>Tanggal</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>

                    </thead>

                    <tbody>

                    <?php if (mysqli_num_rows($query) > 0): ?>

                        <?php $no = $mulai + 1; ?>

                        <?php while ($row = mysqli_fetch_assoc($query)): ?>

                            <tr>

                                <td><?= $no++; ?></td>

                                <td>
                                    <?= htmlspecialchars($row['nis'] ?? '-'); ?>
                                </td>

                                <td>
                                    <strong>
                                        <?= htmlspecialchars($row['nama_siswa'] ?? '-'); ?>
                                    </strong>
                                </td>

                                <td>
                                    <?= htmlspecialchars($row['jenis'] ?? '-'); ?>
                                </td>

                                <td>
                                    <?= (int)$row['poin']; ?>
                                </td>

                                <td>
                                    <?= !empty($row['tanggal'])
                                        ? date('d-m-Y', strtotime($row['tanggal']))
                                        : '-'; ?>
                                </td>

                                <td>
                                    <span class="badge bg-warning text-dark">
                                        Tercatat
                                    </span>
                                </td>

                                <td>

                                    <a
                                        href="tambah_tindakan.php?pelanggaran_id=<?= (int)$row['id']; ?>"
                                        class="btn btn-proses btn-sm"
                                    >
                                        Proses
                                    </a>

                                </td>

                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>

                            <td colspan="8" class="text-center py-4">
                                Tidak ada pelanggaran yang menunggu tindakan.
                            </td>

                        </tr>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

            <!-- INFORMASI DATA -->

            <div class="text-muted small">

                Menampilkan
                <?= $total_data > 0 ? $mulai + 1 : 0; ?>
                -
                <?= min($mulai + $batas, $total_data); ?>
                dari
                <?= $total_data; ?>
                data.

            </div>

            <!-- PAGINATION -->

            <?php if ($total_halaman > 1): ?>

                <nav class="mt-3">

                    <ul class="pagination pagination-sm mb-0">

                        <?php if ($halaman > 1): ?>

                            <li class="page-item">

                                <a
                                    class="page-link"
                                    href="?halaman=<?= $halaman - 1; ?>&cari=<?= urlencode($cari); ?>"
                                >
                                    &laquo;
                                </a>

                            </li>

                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $total_halaman; $i++): ?>

                            <li class="page-item <?= $i == $halaman ? 'active' : ''; ?>">

                                <a
                                    class="page-link"
                                    href="?halaman=<?= $i; ?>&cari=<?= urlencode($cari); ?>"
                                >
                                    <?= $i; ?>
                                </a>

                            </li>

                        <?php endfor; ?>

                        <?php if ($halaman < $total_halaman): ?>

                            <li class="page-item">

                                <a
                                    class="page-link"
                                    href="?halaman=<?= $halaman + 1; ?>&cari=<?= urlencode($cari); ?>"
                                >
                                    &raquo;
                                </a>

                            </li>

                        <?php endif; ?>

                    </ul>

                </nav>

            <?php endif; ?>

        </div>

    </div>

</div>

</body>
</html>

<?php
mysqli_stmt_close($stmt);
?>
