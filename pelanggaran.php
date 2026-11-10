
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
$email = $_SESSION['email'] ?? '';

/* =========================
   CSRF TOKEN
========================= */

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$pesan = "";
$jenis_pesan = "";
$tampil_form = isset($_GET['tambah']) || isset($_POST['simpan']);

/* =========================
   CARI ID GURU YANG LOGIN
========================= */

$guru_login_id = null;

if ($role === 'guru') {

    if ($email === '') {
        die("Email akun guru tidak ditemukan dalam sesi.");
    }

    $stmt_guru = $koneksi->prepare(
        "SELECT id FROM guru WHERE email = ? LIMIT 1"
    );

    if (!$stmt_guru) {
        die("Query guru gagal: " . $koneksi->error);
    }

    $stmt_guru->bind_param("s", $email);
    $stmt_guru->execute();

    $hasil_guru = $stmt_guru->get_result();

    if ($hasil_guru->num_rows > 0) {
        $data_guru = $hasil_guru->fetch_assoc();
        $guru_login_id = (int)$data_guru['id'];
    }

    $stmt_guru->close();

    if ($guru_login_id === null) {
        die("Data guru dengan email akun ini belum terdaftar di tabel guru.");
    }
}

/* =========================
   PROSES SIMPAN
========================= */

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan'])) {

    $token = $_POST['csrf_token'] ?? '';

    if (!hash_equals($_SESSION['csrf_token'], $token)) {

        $pesan = "Permintaan tidak valid. Silakan muat ulang halaman.";
        $jenis_pesan = "danger";

    } else {

        $siswa_id = (int)($_POST['siswa_id'] ?? 0);
        $jenis_id = (int)($_POST['jenis_id'] ?? 0);
        $tanggal = trim($_POST['tanggal'] ?? '');
        $keterangan = trim($_POST['keterangan'] ?? '');
        $status = trim($_POST['status'] ?? 'tercatat');

        $status_valid = [
            'tercatat',
            'ditindaklanjuti',
            'selesai'
        ];

        $guru_id = null;

        if ($role === 'guru') {
            $guru_id = $guru_login_id;
        } else {
            $pilihan_guru = (int)($_POST['guru_id'] ?? 0);
            $guru_id = $pilihan_guru > 0 ? $pilihan_guru : null;
        }

        $tanggal_obj = DateTime::createFromFormat('!Y-m-d', $tanggal);

        if ($siswa_id <= 0 || $jenis_id <= 0 || $tanggal === '') {

            $pesan = "Siswa, jenis pelanggaran, dan tanggal harus diisi.";
            $jenis_pesan = "danger";

        } elseif (
            !$tanggal_obj ||
            $tanggal_obj->format('Y-m-d') !== $tanggal
        ) {

            $pesan = "Format tanggal tidak valid.";
            $jenis_pesan = "danger";

        } elseif (!in_array($status, $status_valid, true)) {

            $pesan = "Status pelanggaran tidak valid.";
            $jenis_pesan = "danger";

        } else {

            /* CEK SISWA */
            $stmt = $koneksi->prepare(
                "SELECT id FROM siswa WHERE id = ? AND aktif = 1"
            );

            $stmt->bind_param("i", $siswa_id);
            $stmt->execute();

            $cek_siswa = $stmt->get_result();
            $stmt->close();

            /* CEK JENIS PELANGGARAN */
            $stmt = $koneksi->prepare(
                "SELECT id FROM jenis_pelanggaran WHERE id = ?"
            );

            $stmt->bind_param("i", $jenis_id);
            $stmt->execute();

            $cek_jenis = $stmt->get_result();
            $stmt->close();

            /* CEK GURU PILIHAN ADMIN */
            $cek_guru_valid = true;

            if ($role === 'admin' && $guru_id !== null) {

                $stmt = $koneksi->prepare(
                    "SELECT id FROM guru WHERE id = ?"
                );

                $stmt->bind_param("i", $guru_id);
                $stmt->execute();

                $hasil_cek_guru = $stmt->get_result();
                $cek_guru_valid = ($hasil_cek_guru->num_rows > 0);

                $stmt->close();
            }

            if ($cek_siswa->num_rows === 0) {

                $pesan = "Data siswa tidak ditemukan atau tidak aktif.";
                $jenis_pesan = "danger";

            } elseif ($cek_jenis->num_rows === 0) {

                $pesan = "Jenis pelanggaran tidak ditemukan.";
                $jenis_pesan = "danger";

            } elseif (!$cek_guru_valid) {

                $pesan = "Data guru tidak ditemukan.";
                $jenis_pesan = "danger";

            } else {

                /* SIMPAN DATA */
                $stmt = $koneksi->prepare(
                    "INSERT INTO pelanggaran
                    (siswa_id, jenis_id, guru_id, tanggal, keterangan, status)
                    VALUES (?, ?, ?, ?, ?, ?)"
                );

                if (!$stmt) {
                    die("Query simpan gagal: " . $koneksi->error);
                }

                $stmt->bind_param(
                    "iiisss",
                    $siswa_id,
                    $jenis_id,
                    $guru_id,
                    $tanggal,
                    $keterangan,
                    $status
                );

                if ($stmt->execute()) {

                    $stmt->close();

                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

                    header("Location: pelanggaran.php?berhasil=1");
                    exit;

                } else {

                    $pesan = "Gagal menyimpan data: " . $stmt->error;
                    $jenis_pesan = "danger";
                    $stmt->close();
                }
            }
        }
    }
}

/* =========================
   PROSES HAPUS
========================= */

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['hapus'])) {

    $token = $_POST['csrf_token'] ?? '';

    if (!hash_equals($_SESSION['csrf_token'], $token)) {

        $pesan = "Permintaan hapus tidak valid.";
        $jenis_pesan = "danger";

    } else {

        $id_hapus = (int)($_POST['id_hapus'] ?? 0);

        if ($id_hapus > 0) {

            $stmt = $koneksi->prepare(
                "DELETE FROM pelanggaran WHERE id = ?"
            );

            $stmt->bind_param("i", $id_hapus);

            if ($stmt->execute()) {

                $stmt->close();

                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

                header("Location: pelanggaran.php?dihapus=1");
                exit;

            } else {

                $pesan = "Gagal menghapus data. Pastikan data pelanggaran tidak sedang digunakan oleh data tindakan.";
                $jenis_pesan = "danger";
                $stmt->close();
            }

        } else {

            $pesan = "ID pelanggaran tidak valid.";
            $jenis_pesan = "danger";
        }
    }
}

/* =========================
   NOTIFIKASI
========================= */

if (isset($_GET['berhasil'])) {
    $pesan = "Data pelanggaran berhasil disimpan.";
    $jenis_pesan = "success";
}

if (isset($_GET['dihapus'])) {
    $pesan = "Data pelanggaran berhasil dihapus.";
    $jenis_pesan = "success";
}

/* =========================
   DATA DROPDOWN
========================= */

$query_siswa = mysqli_query(
    $koneksi,
    "SELECT id, nis, nama
     FROM siswa
     WHERE aktif = 1
     ORDER BY nama ASC"
);

if (!$query_siswa) {
    die("Gagal mengambil data siswa: " . $koneksi->error);
}

$query_jenis = mysqli_query(
    $koneksi,
    "SELECT
        j.id,
        j.nama,
        j.poin,
        k.nama AS nama_kategori
     FROM jenis_pelanggaran j
     LEFT JOIN kategori k ON j.kategori_id = k.id
     ORDER BY k.nama ASC, j.nama ASC"
);

if (!$query_jenis) {
    die("Gagal mengambil jenis pelanggaran: " . $koneksi->error);
}

$query_guru = mysqli_query(
    $koneksi,
    "SELECT id, nama
     FROM guru
     ORDER BY nama ASC"
);

if (!$query_guru) {
    die("Gagal mengambil data guru: " . $koneksi->error);
}

/* =========================
   PENCARIAN
========================= */

$cari = trim($_GET['cari'] ?? '');
$cari_sql = "%" . $cari . "%";

$sql_filter = "
    FROM pelanggaran p
    LEFT JOIN siswa s ON p.siswa_id = s.id
    LEFT JOIN jenis_pelanggaran j ON p.jenis_id = j.id
    LEFT JOIN kategori k ON j.kategori_id = k.id
    LEFT JOIN guru g ON p.guru_id = g.id
    WHERE s.nis LIKE ?
       OR s.nama LIKE ?
       OR j.nama LIKE ?
       OR k.nama LIKE ?
       OR g.nama LIKE ?
";

/* =========================
   HITUNG DATA
========================= */

$stmt_total = $koneksi->prepare(
    "SELECT COUNT(*) AS total " . $sql_filter
);

$stmt_total->bind_param(
    "sssss",
    $cari_sql,
    $cari_sql,
    $cari_sql,
    $cari_sql,
    $cari_sql
);

$stmt_total->execute();

$total_data = (int)$stmt_total->get_result()->fetch_assoc()['total'];

$stmt_total->close();

/* =========================
   PAGINATION
========================= */

$batas = 10;

$total_halaman = max(
    1,
    (int)ceil($total_data / $batas)
);

$halaman = max(1, (int)($_GET['halaman'] ?? 1));
$halaman = min($halaman, $total_halaman);

$offset = ($halaman - 1) * $batas;

/* =========================
   DATA PELANGGARAN
========================= */

$stmt_data = $koneksi->prepare(
    "SELECT
        p.id,
        s.nis,
        s.nama AS nama_siswa,
        j.nama AS nama_jenis,
        j.poin,
        k.nama AS nama_kategori,
        g.nama AS nama_guru,
        p.tanggal,
        p.keterangan,
        p.status
     " . $sql_filter . "
     ORDER BY p.id DESC
     LIMIT ? OFFSET ?"
);

$stmt_data->bind_param(
    "sssssii",
    $cari_sql,
    $cari_sql,
    $cari_sql,
    $cari_sql,
    $cari_sql,
    $batas,
    $offset
);

$stmt_data->execute();

$query_pelanggaran = $stmt_data->get_result();
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Data Pelanggaran</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            margin: 0;
            background: #f4f6f8;
            font-family: Arial, sans-serif;
            color: #212529;
            font-size: 14px;
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

        .sidebar a {
            display: block;
            color: white;
            font-size: 14px;
            padding: 11px 13px;
            margin-bottom: 5px;
            border-radius: 5px;
            text-decoration: none;
        }

        .sidebar a:hover,
        .sidebar a.active {
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

        /* KONTEN */
        .content {
            margin-left: 220px;
            padding: 30px;
        }

        .card {
            border: 1px solid #ddd;
            border-radius: 6px;
            box-shadow: none;
        }

        .card-body {
            padding: 20px;
        }

        .btn-stabilo {
            background: #9dff00;
            color: #1a3300;
            font-weight: bold;
            border: none;
        }

        .btn-stabilo:hover {
            background: #86e000;
            color: #1a3300;
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

        .table th {
            white-space: nowrap;
        }

        .form-label {
            font-size: 14px;
            font-weight: 600;
        }

        .form-control,
        .form-select {
            font-size: 14px;
        }

        .status {
            display: inline-block;
            padding: 5px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
            white-space: nowrap;
        }

        .tercatat {
            background: #fff3cd;
            color: #856404;
        }

        .ditindaklanjuti {
            background: #d1ecf1;
            color: #0c5460;
        }

        .selesai {
            background: #d4edda;
            color: #155724;
        }

        .page-link {
            color: #687280;
        }

        .page-item.active .page-link {
            background: #687280;
            border-color: #687280;
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

    <a href="dashboard.php">Dashboard</a>

    <?php if ($role === 'admin'): ?>

        <a href="kelola_siswa.php">Data Siswa</a>
        <a href="kelola_guru.php">Data Guru</a>
        <a href="kelola_kelas.php">Data Kelas</a>
        <a href="kelola_tahun_ajaran.php">Tahun Ajaran</a>
        <a href="kelola_wali_kelas.php">Wali Kelas</a>
        <a href="penempatan_siswa.php">Penempatan Siswa</a>
        <a href="kelola_pelanggaran_kategori.php">Kategori Pelanggaran</a>
        <a href="pelanggaran.php" class="active">Data Pelanggaran</a>
        <a href="tindakan.php">Data Tindakan</a>
        <a href="laporan.php">Laporan</a>
        <a href="about_me.php">About Me</a>

    <?php elseif ($role === 'guru'): ?>

        <a href="catat_pelanggaran.php">Catat Pelanggaran</a>
        <a href="pelanggaran.php" class="active">Data Pelanggaran</a>
        <a href="tindakan.php">Data Tindakan</a>

    <?php endif; ?>

    <a href="logout.php" class="logout">Logout</a>

</div>

<!-- KONTEN -->
<div class="content">

    <!-- HEADER -->
    <div class="card mb-3">
        <div class="card-body">
            <h4 class="mb-1">Data Pelanggaran</h4>
            <p class="text-muted mb-0">
                Kelola data pelanggaran siswa.
            </p>
        </div>
    </div>

    <!-- NOTIFIKASI -->
    <?php if ($pesan !== ''): ?>
        <div class="alert alert-<?= htmlspecialchars($jenis_pesan); ?> alert-dismissible fade show">
            <?= htmlspecialchars($pesan); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- FORM TAMBAH -->
    <?php if ($tampil_form): ?>

        <div class="card mb-3">
            <div class="card-body">

                <h5 class="mb-3">Tambah Data Pelanggaran</h5>

                <form method="POST">

                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= htmlspecialchars($_SESSION['csrf_token']); ?>"
                    >

                    <div class="row g-3">

                        <div class="col-md-6">
                            <label class="form-label">Siswa</label>

                            <select name="siswa_id" class="form-select" required>
                                <option value="">-- Pilih Siswa --</option>

                                <?php while ($siswa = mysqli_fetch_assoc($query_siswa)): ?>
                                    <option value="<?= (int)$siswa['id']; ?>">
                                        <?= htmlspecialchars($siswa['nis'] . ' - ' . $siswa['nama']); ?>
                                    </option>
                                <?php endwhile; ?>

                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Jenis Pelanggaran</label>

                            <select name="jenis_id" class="form-select" required>
                                <option value="">-- Pilih Jenis Pelanggaran --</option>

                                <?php while ($jenis = mysqli_fetch_assoc($query_jenis)): ?>
                                    <option value="<?= (int)$jenis['id']; ?>">
                                        <?= htmlspecialchars($jenis['nama_kategori'] ?? '-'); ?>
                                        -
                                        <?= htmlspecialchars($jenis['nama']); ?>
                                        (<?= (int)$jenis['poin']; ?> poin)
                                    </option>
                                <?php endwhile; ?>

                            </select>
                        </div>

                        <?php if ($role === 'admin'): ?>

                            <div class="col-md-6">
                                <label class="form-label">Guru</label>

                                <select name="guru_id" class="form-select">
                                    <option value="0">-- Pilih Guru (Opsional) --</option>

                                    <?php while ($guru = mysqli_fetch_assoc($query_guru)): ?>
                                        <option value="<?= (int)$guru['id']; ?>">
                                            <?= htmlspecialchars($guru['nama']); ?>
                                        </option>
                                    <?php endwhile; ?>

                                </select>
                            </div>

                        <?php else: ?>

                            <div class="col-md-6">
                                <label class="form-label">Guru Pencatat</label>

                                <input
                                    type="text"
                                    class="form-control"
                                    value="<?= htmlspecialchars($nama); ?>"
                                    readonly
                                >
                            </div>

                        <?php endif; ?>

                        <div class="col-md-6">
                            <label class="form-label">Tanggal</label>

                            <input
                                type="date"
                                name="tanggal"
                                class="form-control"
                                value="<?= date('Y-m-d'); ?>"
                                required
                            >
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Status</label>

                            <select name="status" class="form-select">
                                <option value="tercatat">Tercatat</option>
                                <option value="ditindaklanjuti">Ditindaklanjuti</option>
                                <option value="selesai">Selesai</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Keterangan</label>

                            <textarea
                                name="keterangan"
                                rows="3"
                                class="form-control"
                                placeholder="Keterangan pelanggaran..."
                            ></textarea>
                        </div>

                    </div>

                    <div class="mt-4">
                        <button type="submit" name="simpan" class="btn btn-stabilo">
                            Simpan Pelanggaran
                        </button>

                        <a href="pelanggaran.php" class="btn btn-secondary">
                            Batal
                        </a>
                    </div>

                </form>

            </div>
        </div>

    <?php endif; ?>

    <!-- DAFTAR PELANGGARAN -->
    <div class="card">

        <div class="card-body">

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">

                <h5 class="mb-0">Daftar Pelanggaran</h5>

                <?php if (!$tampil_form): ?>
                    <a href="pelanggaran.php?tambah=1" class="btn btn-sm btn-stabilo">
                        + Tambah Pelanggaran
                    </a>
                <?php endif; ?>

            </div>

            <!-- PENCARIAN -->
            <form method="GET" class="row g-2 mb-3">

                <div class="col-md-5">
                    <input
                        type="text"
                        name="cari"
                        class="form-control form-control-sm"
                        placeholder="Cari NIS, siswa, kategori, jenis, atau guru..."
                        value="<?= htmlspecialchars($cari); ?>"
                    >
                </div>

                <div class="col-auto">
                    <button type="submit" class="btn btn-secondary btn-sm">
                        Cari
                    </button>

                    <?php if ($cari !== ''): ?>
                        <a href="pelanggaran.php" class="btn btn-outline-secondary btn-sm">
                            Reset
                        </a>
                    <?php endif; ?>
                </div>

            </form>

            <!-- TABEL -->
            <div class="table-responsive">

                <table class="table table-bordered table-hover align-middle">

                    <thead class="table-secondary">
                        <tr>
                            <th>No</th>
                            <th>NIS</th>
                            <th>Nama Siswa</th>
                            <th>Kategori</th>
                            <th>Jenis Pelanggaran</th>
                            <th>Poin</th>
                            <th>Guru</th>
                            <th>Tanggal</th>
                            <th>Keterangan</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php if ($query_pelanggaran && $query_pelanggaran->num_rows > 0): ?>

                            <?php $no = $offset + 1; ?>

                            <?php while ($row = $query_pelanggaran->fetch_assoc()): ?>

                                <?php
                                $status = $row['status'] ?? 'tercatat';

                                $kelas_status = in_array(
                                    $status,
                                    ['tercatat', 'ditindaklanjuti', 'selesai'],
                                    true
                                ) ? $status : 'tercatat';
                                ?>

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
                                        <?= htmlspecialchars($row['nama_kategori'] ?? '-'); ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($row['nama_jenis'] ?? '-'); ?>
                                    </td>

                                    <td>
                                        <strong>
                                            <?= htmlspecialchars($row['poin'] ?? '0'); ?>
                                        </strong>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($row['nama_guru'] ?? '-'); ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($row['tanggal'] ?? '-'); ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($row['keterangan'] ?? '-'); ?>
                                    </td>

                                    <td>
                                        <span class="status <?= htmlspecialchars($kelas_status); ?>">
                                            <?= htmlspecialchars(ucfirst($status)); ?>
                                        </span>
                                    </td>

                                    <td class="text-nowrap">

                                        <a
                                            href="edit_pelanggaran.php?id=<?= (int)$row['id']; ?>"
                                            class="btn btn-sm btn-stabilo"
                                        >
                                            Edit
                                        </a>

                                        <form
                                            method="POST"
                                            class="d-inline"
                                            onsubmit="return confirm('Yakin ingin menghapus data pelanggaran ini?');"
                                        >

                                            <input
                                                type="hidden"
                                                name="csrf_token"
                                                value="<?= htmlspecialchars($_SESSION['csrf_token']); ?>"
                                            >

                                            <input
                                                type="hidden"
                                                name="id_hapus"
                                                value="<?= (int)$row['id']; ?>"
                                            >

                                            <button
                                                type="submit"
                                                name="hapus"
                                                class="btn btn-sm btn-danger"
                                            >
                                                Hapus
                                            </button>

                                        </form>

                                    </td>
                                </tr>

                            <?php endwhile; ?>

                        <?php else: ?>

                            <tr>
                                <td colspan="11" class="text-center py-4">
                                    Belum ada data pelanggaran.
                                </td>
                            </tr>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

            <!-- INFO DATA DAN PAGINATION -->
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">

                <small class="text-muted">
                    Menampilkan
                    <?= $total_data > 0 ? $offset + 1 : 0; ?>
                    -
                    <?= min($offset + $batas, $total_data); ?>
                    dari <?= $total_data; ?> data pelanggaran.
                </small>

                <nav>
                    <ul class="pagination pagination-sm mb-0">

                        <?php for ($i = 1; $i <= $total_halaman; $i++): ?>

                            <li class="page-item <?= ($halaman == $i) ? 'active' : ''; ?>">

                                <a
                                    class="page-link"
                                    href="?halaman=<?= $i; ?>&cari=<?= urlencode($cari); ?>"
                                >
                                    <?= $i; ?>
                                </a>

                            </li>

                        <?php endfor; ?>

                    </ul>
                </nav>

            </div>

        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
