
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "database.php";
require_once "auth.php";

hanyaRole(['admin', 'guru', 'wali_kelas']);

$role = $_SESSION['role'] ?? '';
$error = '';

$cari = trim($_GET['cari'] ?? '');
$tanggal_awal = $_GET['tanggal_awal'] ?? '';
$tanggal_akhir = $_GET['tanggal_akhir'] ?? '';

$where = [];
$parameter = [];
$tipe = '';

$kelas_wali_id = 0;
$tahun_aktif_id = 0;

// =====================================
// BATAS AKSES WALI KELAS
// =====================================
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
            ON ta.id = wk.tahun_ajaran_id
        WHERE u.email = ?
          AND u.role = 'wali_kelas'
          AND u.aktif = 1
          AND ta.aktif = 1
        LIMIT 1
    ");

    if (!$stmt_wali) {
        die("Gagal memeriksa penempatan Wali Kelas: " . mysqli_error($koneksi));
    }

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

    $join_penempatan = "
        INNER JOIN penempatan_siswa ps
            ON ps.siswa_id = s.id
            AND ps.kelas_id = ?
            AND ps.tahun_ajaran_id = ?
    ";

    $parameter[] = $kelas_wali_id;
    $parameter[] = $tahun_aktif_id;
    $tipe .= "ii";

} else {

    // ADMIN DAN GURU MENGGUNAKAN PENEMPATAN TERBARU SISWA.
    $join_penempatan = "
        LEFT JOIN penempatan_siswa ps
            ON ps.siswa_id = s.id
            AND ps.id = (
                SELECT ps2.id
                FROM penempatan_siswa ps2
                WHERE ps2.siswa_id = s.id
                ORDER BY ps2.tahun_ajaran_id DESC, ps2.id DESC
                LIMIT 1
            )
    ";
}

// =====================================
// BATAS AKSES GURU
// GURU MELIHAT PELANGGARAN YANG:
// 1. DICATAT OLEH DIRINYA SENDIRI.
// 2. TINDAKANNYA DIPROSES OLEH DIRINYA.
// =====================================
if ($role === 'guru') {

    $email = $_SESSION['email'] ?? '';

    if ($email === '') {
        die("Akses ditolak. Email akun Guru tidak ditemukan.");
    }

    $stmt_guru = mysqli_prepare($koneksi, "
        SELECT
            u.id AS user_id,
            COALESCE(u.guru_id, g.id) AS guru_id
        FROM users u
        LEFT JOIN guru g
            ON g.id = u.guru_id
            OR (u.guru_id IS NULL AND g.email = u.email)
        WHERE u.email = ?
          AND u.role = 'guru'
          AND u.aktif = 1
        LIMIT 1
    ");

    if (!$stmt_guru) {
        die("Gagal mengambil data Guru: " . mysqli_error($koneksi));
    }

    mysqli_stmt_bind_param($stmt_guru, "s", $email);
    mysqli_stmt_execute($stmt_guru);

    $hasil_guru = mysqli_stmt_get_result($stmt_guru);
    $data_guru = mysqli_fetch_assoc($hasil_guru);

    mysqli_stmt_close($stmt_guru);

    if (
        !$data_guru ||
        empty($data_guru['guru_id']) ||
        empty($data_guru['user_id'])
    ) {
        die("Akses ditolak. Data akun Guru tidak ditemukan atau belum terhubung dengan data Guru.");
    }

    $guru_id = (int)$data_guru['guru_id'];
    $user_id = (int)$data_guru['user_id'];

    $where[] = "(
        p.guru_id = ?
        OR EXISTS (
            SELECT 1
            FROM tindakan t
            WHERE t.pelanggaran_id = p.id
              AND t.petugas_id = ?
        )
    )";

    $parameter[] = $guru_id;
    $parameter[] = $user_id;
    $tipe .= "ii";
}

// =====================================
// VALIDASI TANGGAL
// =====================================
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

// =====================================
// FILTER PENCARIAN
// =====================================
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

// =====================================
// FILTER TANGGAL
// =====================================
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

// =====================================
// QUERY LAPORAN
// =====================================
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

    INNER JOIN siswa s
        ON p.siswa_id = s.id

    $join_penempatan

    LEFT JOIN kelas k
        ON ps.kelas_id = k.id

    INNER JOIN jenis_pelanggaran jp
        ON p.jenis_id = jp.id

    $where_sql

    ORDER BY p.tanggal DESC, p.id DESC
";

// =====================================
// AMBIL DATA LAPORAN
// =====================================
$data_laporan = [];

if ($error === '') {

    $stmt = mysqli_prepare($koneksi, $sql);

    if (!$stmt) {
        die("Gagal menyiapkan laporan: " . mysqli_error($koneksi));
    }

    if (!empty($parameter)) {

        $referensi = [];

        foreach ($parameter as $key => &$value) {
            $referensi[$key] = &$value;
        }

        if (!call_user_func_array(
            'mysqli_stmt_bind_param',
            array_merge([$stmt, $tipe], $referensi)
        )) {
            die("Gagal mengikat parameter laporan.");
        }
    }

    if (!mysqli_stmt_execute($stmt)) {
        die("Gagal menjalankan laporan: " . mysqli_stmt_error($stmt));
    }

    $hasil = mysqli_stmt_get_result($stmt);

    if (!$hasil) {
        die("Gagal mengambil hasil laporan: " . mysqli_stmt_error($stmt));
    }

    while ($baris = mysqli_fetch_assoc($hasil)) {
        $data_laporan[] = $baris;
    }

    mysqli_stmt_close($stmt);
}

$total_data = count($data_laporan);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Pelanggaran</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #f4f6f8;
            font-family: Arial, sans-serif;
            font-size: 14px;
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
            display: block;
            padding: 11px 13px;
            margin-bottom: 5px;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 14px;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: #4f5965;
            color: white;
        }

        .sidebar .logout {
            margin-top: 20px;
            background: #b42323;
        }

        .sidebar .logout:hover {
            background: #941d1d;
        }

        .content {
            margin-left: 220px;
            padding: 30px;
            min-height: 100vh;
        }

        .card {
            border: 1px solid #ddd;
            border-radius: 6px;
            box-shadow: none;
        }

        .card-body {
            padding: 20px;
        }

        .btn-cetak {
            background: #687280;
            color: white;
            border: none;
        }

        .btn-cetak:hover {
            background: #4f5965;
            color: white;
        }

        .table {
            font-size: 12px;
            margin-bottom: 10px;
        }

        .table th,
        .table td {
            padding: 10px;
            vertical-align: middle;
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

        .kosong {
            text-align: center;
            padding: 25px !important;
        }

        .ttd {
            margin-top: 60px;
            text-align: right;
            padding-right: 60px;
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

        @media print {
            @page {
                size: landscape;
                margin: 15mm;
            }

            .sidebar,
            .no-print {
                display: none !important;
            }

            .content {
                margin: 0;
                padding: 0;
            }

            body {
                background: white;
            }

            .card {
                border: none;
            }

            .card-body {
                padding: 0;
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

    <?php
    $menu_aktif = 'laporan';
    require_once "sidebar.php";
    ?>

    <div class="content">

        <div class="card mb-3 no-print">
            <div class="card-body">
                <h4 class="mb-1">Laporan Pelanggaran</h4>
                <p class="text-muted mb-0">
                    Menampilkan laporan pelanggaran siswa.
                </p>
            </div>
        </div>

        <div class="card mb-3 no-print">
            <div class="card-body">

                <h5 class="mb-3">Filter Laporan</h5>

                <?php if ($error !== ''): ?>
                    <div class="alert alert-danger">
                        <?= htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <form method="GET" class="row g-3">

                    <div class="col-md-4">
                        <label class="form-label fw-bold">
                            Cari Data
                        </label>

                        <input
                            type="text"
                            name="cari"
                            class="form-control form-control-sm"
                            placeholder="Nama siswa, kelas, jenis..."
                            value="<?= htmlspecialchars($cari); ?>"
                        >
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-bold">
                            Tanggal Awal
                        </label>

                        <input
                            type="date"
                            name="tanggal_awal"
                            class="form-control form-control-sm"
                            value="<?= htmlspecialchars($tanggal_awal); ?>"
                        >
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-bold">
                            Tanggal Akhir
                        </label>

                        <input
                            type="date"
                            name="tanggal_akhir"
                            class="form-control form-control-sm"
                            value="<?= htmlspecialchars($tanggal_akhir); ?>"
                        >
                    </div>

                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-secondary btn-sm me-2">
                            Cari
                        </button>

                        <?php if (
                            $cari !== '' ||
                            $tanggal_awal !== '' ||
                            $tanggal_akhir !== ''
                        ): ?>
                            <a href="laporan.php" class="btn btn-outline-secondary btn-sm">
                                Reset
                            </a>
                        <?php endif; ?>
                    </div>

                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body">

                <div class="d-flex justify-content-between align-items-center mb-3 no-print">
                    <h5 class="mb-0">Data Pelanggaran</h5>

                    <button
                        onclick="window.print()"
                        class="btn btn-cetak btn-sm"
                        <?= $error !== '' ? 'disabled' : ''; ?>
                    >
                        Cetak Laporan
                    </button>
                </div>

                <div class="judul">
                    <h1>LAPORAN PELANGGARAN SISWA</h1>
                    <h2>SISTEM INFORMASI PELANGGARAN SISWA</h2>
                    <p>SMK</p>

                    <?php if ($tanggal_awal !== '' || $tanggal_akhir !== ''): ?>
                        <p>
                            Periode:

                            <?= $tanggal_awal !== ''
                                ? date('d-m-Y', strtotime($tanggal_awal))
                                : 'Awal'; ?>

                            -

                            <?= $tanggal_akhir !== ''
                                ? date('d-m-Y', strtotime($tanggal_akhir))
                                : 'Sekarang'; ?>
                        </p>
                    <?php endif; ?>

                    <p>
                        Tanggal Cetak: <?= date('d-m-Y'); ?>
                    </p>
                </div>

                <div class="mb-2 text-secondary small">
                    Total laporan: <?= $total_data; ?> data
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">

                        <thead class="table-secondary">
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
                            <?php if (!empty($data_laporan)): ?>

                                <?php $no = 1; ?>

                                <?php foreach ($data_laporan as $data): ?>
                                    <tr>
                                        <td><?= $no++; ?></td>

                                        <td>
                                            <?= htmlspecialchars($data['nama_siswa'] ?? '-'); ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($data['nama_kelas'] ?? '-'); ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($data['jenis_pelanggaran'] ?? '-'); ?>
                                        </td>

                                        <td>
                                            <?= (int)($data['poin'] ?? 0); ?>
                                        </td>

                                        <td>
                                            <?= !empty($data['tanggal'])
                                                ? date('d-m-Y', strtotime($data['tanggal']))
                                                : '-'; ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($data['status'] ?? '-'); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>

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

                <div class="ttd">
                    <div>Mengetahui,</div>
                    <div>Kepala Sekolah</div>

                    <br><br><br>

                    <strong>Drs. H. Wawan Kurniawan, M.Pd.</strong><br>
                    <strong>NIP. 19650101 199003 1 005</strong>
                </div>

            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
