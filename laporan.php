
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
$error = '';

$cari = trim($_GET['cari'] ?? '');
$tanggal_awal = $_GET['tanggal_awal'] ?? '';
$tanggal_akhir = $_GET['tanggal_akhir'] ?? '';

$where = [];
$parameter = [];
$tipe = '';

/* VALIDASI TANGGAL */
foreach (['tanggal_awal', 'tanggal_akhir'] as $field) {
    if ($$field !== '') {
        $tanggal = DateTime::createFromFormat('!Y-m-d', $$field);

        if (!$tanggal || $tanggal->format('Y-m-d') !== $$field) {
            $error = "Format tanggal tidak valid.";
            $$field = '';
        }
    }
}

if (
    $tanggal_awal !== '' &&
    $tanggal_akhir !== '' &&
    $tanggal_awal > $tanggal_akhir
) {
    $error = "Tanggal awal tidak boleh melebihi tanggal akhir.";
}

/* FILTER PENCARIAN */
if ($cari !== '') {
    $where[] = "(
        s.nama LIKE ?
        OR k.nama_kelas LIKE ?
        OR jp.nama LIKE ?
    )";

    $keyword = "%{$cari}%";

    $parameter[] = $keyword;
    $parameter[] = $keyword;
    $parameter[] = $keyword;

    $tipe .= "sss";
}

if ($tanggal_awal !== '') {
    $where[] = "p.tanggal >= ?";
    $parameter[] = $tanggal_awal;
    $tipe .= "s";
}

if ($tanggal_akhir !== '') {
    $where[] = "p.tanggal <= ?";
    $parameter[] = $tanggal_akhir;
    $tipe .= "s";
}

$where_sql = !empty($where)
    ? "WHERE " . implode(" AND ", $where)
    : "";

/* QUERY LAPORAN */
$sql = "
    SELECT
        p.id,
        s.nama AS nama_siswa,
        k.nama_kelas,
        jp.nama AS jenis_pelanggaran,
        jp.poin,
        p.tanggal,
        p.status
    FROM pelanggaran p
    INNER JOIN siswa s ON p.siswa_id = s.id
    LEFT JOIN penempatan_siswa ps
        ON ps.siswa_id = s.id
        AND ps.id = (
            SELECT ps2.id
            FROM penempatan_siswa ps2
            WHERE ps2.siswa_id = s.id
            ORDER BY ps2.tahun_ajaran_id DESC, ps2.id DESC
            LIMIT 1
        )
    LEFT JOIN kelas k ON ps.kelas_id = k.id
    INNER JOIN jenis_pelanggaran jp ON p.jenis_id = jp.id
    $where_sql
    ORDER BY p.tanggal DESC, p.id DESC
";

$query = false;

if ($error === '') {
    $stmt = mysqli_prepare($koneksi, $sql);

    if (!$stmt) {
        die("Gagal menyiapkan laporan: " . mysqli_error($koneksi));
    }

    if (!empty($parameter)) {
        mysqli_stmt_bind_param($stmt, $tipe, ...$parameter);
    }

    mysqli_stmt_execute($stmt);
    $query = mysqli_stmt_get_result($stmt);

    if (!$query) {
        die("Query laporan gagal: " . mysqli_error($koneksi));
    }
}

$total_data = $query ? mysqli_num_rows($query) : 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Laporan Pelanggaran</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
* { box-sizing: border-box; }

body {
    margin: 0;
    background: #f4f6f8;
    font-family: Arial, sans-serif;
    font-size: 14px;
    color: #333;
}

#sidebar-laporan {
    position: fixed;
    top: 0;
    left: 0;
    width: 220px;
    height: 100vh;
    padding: 20px 12px;
    background: #687280;
    overflow-y: auto;
    z-index: 1000;
}

#sidebar-laporan .sidebar-title {
    color: white;
    font-size: 19px;
    font-weight: bold;
    margin: 0 8px 25px;
    line-height: 1.4;
}

#sidebar-laporan .nav-link {
    display: block;
    color: white;
    font-size: 14px;
    padding: 11px 13px;
    margin-bottom: 5px;
    border-radius: 5px;
    text-decoration: none;
}

#sidebar-laporan .nav-link:hover,
#sidebar-laporan .nav-link.active {
    background: #4f5965;
    color: white;
}

#sidebar-laporan .logout {
    background: #b42323;
    margin-top: 20px;
}

#sidebar-laporan .logout:hover {
    background: #941d1d;
}

.content-laporan {
    margin-left: 220px;
    padding: 30px;
    min-height: 100vh;
}

.header-box,
.main-box {
    background: white;
    border: 1px solid #ddd;
    border-radius: 7px;
    padding: 20px;
}

.header-box {
    margin-bottom: 20px;
}

.header-box h1 {
    font-size: 24px;
    font-weight: bold;
    margin-bottom: 10px;
}

.judul {
    text-align: center;
    margin: 20px 0 30px;
}

.judul h1 {
    font-size: 22px;
    font-weight: bold;
}

.judul h2 {
    font-size: 17px;
    font-weight: bold;
}

.table {
    font-size: 13px;
    vertical-align: middle;
}

.table thead th {
    background: #687280;
    color: white;
    white-space: nowrap;
}

.kosong {
    text-align: center;
    padding: 25px !important;
}

.btn-cetak {
    background: #687280;
    color: white;
}

.btn-cetak:hover {
    background: #4f5965;
    color: white;
}

.ttd {
    margin-top: 60px;
    text-align: right;
    padding-right: 60px;
}

@media (max-width: 700px) {
    #sidebar-laporan {
        position: relative;
        width: 100%;
        height: auto;
    }

    .content-laporan {
        margin-left: 0;
        padding: 15px;
    }
}

@media print {
    @page {
        size: landscape;
        margin: 15mm;
    }

    #sidebar-laporan,
    .no-print {
        display: none !important;
    }

    .content-laporan {
        margin: 0;
        padding: 0;
    }

    body {
        background: white;
    }

    .main-box {
        border: none;
    }

    .table thead th {
        background: #ddd !important;
        color: black !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    .table th,
    .table td {
        border: 1px solid black !important;
        padding: 8px !important;
    }

    .ttd {
        page-break-inside: avoid;
    }
}
</style>
</head>

<body>

<!-- SIDEBAR -->
<aside id="sidebar-laporan">

    <div class="sidebar-title">
        ⚖ Pelanggaran<br>Siswa
    </div>

    <nav class="nav flex-column">

        <a href="dashboard.php" class="nav-link">🏠 Dashboard</a>

        <?php if ($role === 'admin'): ?>

            <a href="kelola_siswa.php" class="nav-link">Data Siswa</a>
            <a href="kelola_guru.php" class="nav-link">Data Guru</a>
            <a href="kelola_kelas.php" class="nav-link">Data Kelas</a>
            <a href="kelola_tahun_ajaran.php" class="nav-link">Tahun Ajaran</a>
            <a href="kelola_wali_kelas.php" class="nav-link">Wali Kelas</a>
            <a href="penempatan_siswa.php" class="nav-link">Penempatan Siswa</a>
            <a href="kelola_pelanggaran_kategori.php" class="nav-link">⚠ Kategori Pelanggaran</a>
            <a href="pelanggaran.php" class="nav-link">Data Pelanggaran</a>
            <a href="tindakan.php" class="nav-link">Data Tindakan</a>
            <a href="riwayat.php" class="nav-link">Riwayat</a>
            <a href="rekap_poin.php" class="nav-link">Rekap Poin</a>
            <a href="laporan.php" class="nav-link active">Laporan</a>
            <a href="about_me.php" class="nav-link">ℹ About Me</a>

        <?php else: ?>

            <a href="catat_pelanggaran.php" class="nav-link">Catat Pelanggaran</a>
            <a href="pelanggaran.php" class="nav-link">Data Pelanggaran</a>
            <a href="tindakan.php" class="nav-link">Data Tindakan</a>
            <a href="riwayat.php" class="nav-link">Riwayat</a>
            <a href="rekap_poin.php" class="nav-link">Rekap Poin</a>

        <?php endif; ?>

        <a href="logout.php" class="nav-link logout">🚪 Logout</a>

    </nav>
</aside>

<!-- KONTEN -->
<main class="content-laporan">

    <div class="header-box no-print">
        <h1>Laporan Pelanggaran</h1>
        <p class="text-secondary mb-0">
            Menampilkan laporan pelanggaran siswa.
        </p>
    </div>

    <!-- FILTER -->
    <div class="main-box mb-3 no-print">

        <h5 class="mb-3">Filter Laporan</h5>

        <?php if ($error !== ''): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="GET" class="row g-3">

            <div class="col-md-4">
                <label class="form-label">Cari Data</label>
                <input type="text" name="cari" class="form-control"
                       placeholder="Nama siswa, kelas, jenis..."
                       value="<?= htmlspecialchars($cari); ?>">
            </div>

            <div class="col-md-3">
                <label class="form-label">Tanggal Awal</label>
                <input type="date" name="tanggal_awal" class="form-control"
                       value="<?= htmlspecialchars($tanggal_awal); ?>">
            </div>

            <div class="col-md-3">
                <label class="form-label">Tanggal Akhir</label>
                <input type="date" name="tanggal_akhir" class="form-control"
                       value="<?= htmlspecialchars($tanggal_akhir); ?>">
            </div>

            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-secondary me-2">Cari</button>
                <a href="laporan.php" class="btn btn-outline-secondary">Reset</a>
            </div>

        </form>
    </div>

    <!-- TABEL LAPORAN -->
    <div class="main-box">

        <div class="d-flex justify-content-between align-items-center mb-3 no-print">
            <h5 class="mb-0">Data Pelanggaran</h5>

            <button onclick="window.print()" class="btn btn-cetak btn-sm"
                <?= $error !== '' ? 'disabled' : ''; ?>>
                🖨 Cetak Laporan
            </button>
        </div>

        <div class="judul">
            <h1>LAPORAN PELANGGARAN SISWA</h1>
            <h2>SISTEM INFORMASI PELANGGARAN SISWA</h2>
            <p>SMK</p>

            <?php if ($tanggal_awal !== '' || $tanggal_akhir !== ''): ?>
                <p>
                    Periode:
                    <?= $tanggal_awal !== '' ? date('d-m-Y', strtotime($tanggal_awal)) : 'Awal'; ?>
                    -
                    <?= $tanggal_akhir !== '' ? date('d-m-Y', strtotime($tanggal_akhir)) : 'Sekarang'; ?>
                </p>
            <?php endif; ?>

            <p>Tanggal Cetak: <?= date('d-m-Y'); ?></p>
        </div>

        <div class="mb-2 text-secondary small">
            Total laporan: <?= $total_data; ?> data
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-hover">

                <thead>
                    <tr>
                        <th width="45">No</th>
                        <th>Nama Siswa</th>
                        <th>Kelas</th>
                        <th>Jenis Pelanggaran</th>
                        <th>Poin</th>
                        <th>Tanggal</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if ($query && mysqli_num_rows($query) > 0): ?>

                        <?php $no = 1; ?>

                        <?php while ($data = mysqli_fetch_assoc($query)): ?>
                            <tr>
                                <td><?= $no++; ?></td>
                                <td><?= htmlspecialchars($data['nama_siswa'] ?? '-'); ?></td>
                                <td><?= htmlspecialchars($data['nama_kelas'] ?? '-'); ?></td>
                                <td><?= htmlspecialchars($data['jenis_pelanggaran'] ?? '-'); ?></td>
                                <td><?= (int)($data['poin'] ?? 0); ?></td>
                                <td>
                                    <?= !empty($data['tanggal'])
                                        ? date('d-m-Y', strtotime($data['tanggal']))
                                        : '-'; ?>
                                </td>
                                <td><?= htmlspecialchars($data['status'] ?? '-'); ?></td>
                            </tr>
                        <?php endwhile; ?>

                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="kosong">
                                Tidak ada data pelanggaran yang ditemukan.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>

            </table>
        </div>

        <!-- TANDA TANGAN -->
        <div class="ttd">
            <div>Mengetahui,</div>
            <div>Kepala Sekolah</div>

            <br><br><br>

            <strong>Drs. H. Wawan Kurniawan, M.Pd.</strong><br>
            <strong>NIP. 19650101 199003 1 005</strong>
        </div>

    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
