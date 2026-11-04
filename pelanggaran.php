
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once "database.php";

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

$role = $_SESSION['role'] ?? '';
$pesan = "";

/* =========================
   PROSES SIMPAN
========================= */

if (isset($_POST['simpan'])) {
    $siswa_id = (int)($_POST['siswa_id'] ?? 0);
    $jenis_id = (int)($_POST['jenis_id'] ?? 0);
    $guru_id = (int)($_POST['guru_id'] ?? 0);

    $tanggal = trim($_POST['tanggal'] ?? '');
    $keterangan = trim($_POST['keterangan'] ?? '');
    $status = trim($_POST['status'] ?? 'tercatat');

    $status_valid = ['tercatat', 'ditindaklanjuti', 'selesai'];

    if ($siswa_id <= 0 || $jenis_id <= 0 || $tanggal == '') {
        $pesan = "Siswa, jenis pelanggaran, dan tanggal harus diisi.";
    } elseif (!in_array($status, $status_valid, true)) {
        $pesan = "Status pelanggaran tidak valid.";
    } else {
        $guru_sql = $guru_id > 0 ? $guru_id : null;

        $stmt = mysqli_prepare(
            $koneksi,
            "INSERT INTO pelanggaran
            (siswa_id, jenis_id, guru_id, tanggal, keterangan, status)
            VALUES (?, ?, ?, ?, ?, ?)"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "iiisss",
            $siswa_id,
            $jenis_id,
            $guru_sql,
            $tanggal,
            $keterangan,
            $status
        );

        if (mysqli_stmt_execute($stmt)) {
            header("Location: pelanggaran.php");
            exit;
        } else {
            $pesan = "Gagal menyimpan data: " . mysqli_error($koneksi);
        }
    }
}

/* =========================
   PROSES HAPUS
========================= */

if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];

    if ($id > 0) {
        $stmt = mysqli_prepare(
            $koneksi,
            "DELETE FROM pelanggaran WHERE id = ?"
        );

        mysqli_stmt_bind_param($stmt, "i", $id);

        if (mysqli_stmt_execute($stmt)) {
            header("Location: pelanggaran.php");
            exit;
        } else {
            $pesan = "Gagal menghapus data. Pastikan data pelanggaran tidak sedang digunakan oleh data tindakan.";
        }
    }
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
    die("Error query jenis pelanggaran: " . mysqli_error($koneksi));
}

$query_guru = mysqli_query(
    $koneksi,
    "SELECT id, nama
     FROM guru
     ORDER BY nama ASC"
);

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

$stmt_total = mysqli_prepare(
    $koneksi,
    "SELECT COUNT(*) AS total " . $sql_filter
);

mysqli_stmt_bind_param(
    $stmt_total,
    "sssss",
    $cari_sql,
    $cari_sql,
    $cari_sql,
    $cari_sql,
    $cari_sql
);

mysqli_stmt_execute($stmt_total);

$total_data = (int)mysqli_fetch_assoc(
    mysqli_stmt_get_result($stmt_total)
)['total'];

/* =========================
   PAGINATION
========================= */

$batas = 5;
$total_halaman = max(1, (int)ceil($total_data / $batas));

$halaman = max(1, (int)($_GET['halaman'] ?? 1));
$halaman = min($halaman, $total_halaman);

$offset = ($halaman - 1) * $batas;

/* =========================
   DATA PELANGGARAN
========================= */

$stmt_data = mysqli_prepare(
    $koneksi,
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

mysqli_stmt_bind_param(
    $stmt_data,
    "sssssii",
    $cari_sql,
    $cari_sql,
    $cari_sql,
    $cari_sql,
    $cari_sql,
    $batas,
    $offset
);

mysqli_stmt_execute($stmt_data);
$query_pelanggaran = mysqli_stmt_get_result($stmt_data);
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
            color: #333;
        }

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 220px;
            height: 100vh;
            padding: 20px 12px;
            background: #687280;
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

        .content {
            margin-left: 220px;
            padding: 30px;
        }

        .header,
        .box {
            background: white;
            border: 1px solid #ddd;
            border-radius: 7px;
            padding: 22px;
            margin-bottom: 20px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
        }

        .header p {
            color: #777;
            margin: 5px 0 0;
        }

        .btn-tambah {
            background: #9dff00;
            border: 1px solid #9dff00;
            color: #222;
            font-weight: bold;
        }

        .btn-tambah:hover {
            background: #8be000;
            border-color: #8be000;
            color: #222;
        }

        .btn-hapus {
            background: #b42323;
            color: white;
        }

        .btn-hapus:hover {
            background: #941d1d;
            color: white;
        }

        .table thead th {
            background: #687280;
            color: white;
            white-space: nowrap;
        }

        .table td {
            vertical-align: middle;
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

        .pagination .page-link {
            color: #333;
        }

        .pagination .active .page-link {
            background: #687280;
            border-color: #687280;
            color: white;
        }

        @media(max-width: 800px) {
            .sidebar {
                position: relative;
                width: 100%;
                height: auto;
            }

            .content {
                margin-left: 0;
                padding: 15px;
            }

            .header {
                display: block;
            }

            .header .btn {
                margin-top: 15px;
            }
        }
    </style>
</head>

<body>

<div class="sidebar">
    <div class="sidebar-title">⚖ Pelanggaran<br>Siswa</div>

    <a href="dashboard.php">🏠 Dashboard</a>

    <?php if ($role == 'admin') { ?>
        <a href="kelola_siswa.php">Data Siswa</a>
        <a href="kelola_guru.php">Data Guru</a>
        <a href="kelola_kelas.php">Data Kelas</a>
        <a href="kelola_tahun_ajaran.php">Tahun Ajaran</a>
        <a href="kelola_wali_kelas.php">Wali Kelas</a>
        <a href="penempatan_siswa.php">Penempatan Siswa</a>
        <a href="kelola_pelanggaran_kategori.php">⚠ Kategori Pelanggaran</a>
    <?php } ?>

    <a href="pelanggaran.php" class="active">Data Pelanggaran</a>
    <a href="tindakan.php">Data Tindakan</a>
    <a href="laporan.php">Laporan</a>
    <a href="about_me.php">ℹ About Me</a>
    <a href="logout.php" class="logout">🚪 Logout</a>
</div>

<div class="content">

    <div class="header">
        <div>
            <h1>Data Pelanggaran</h1>
            <p>Kelola data pelanggaran siswa.</p>
        </div>

        <a href="pelanggaran.php?tambah=1" class="btn btn-tambah">
            + Tambah Pelanggaran
        </a>
    </div>

    <?php if ($pesan != ''): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($pesan); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['tambah']) && $_GET['tambah'] == '1'): ?>
        <div class="box">
            <h5 class="mb-3">Tambah Data Pelanggaran</h5>

            <form method="POST">

                <div class="row g-3">

                    <div class="col-md-6">
                        <label class="form-label">Siswa</label>
                        <select name="siswa_id" class="form-select" required>
                            <option value="">-- Pilih Siswa --</option>

                            <?php while ($siswa = mysqli_fetch_assoc($query_siswa)): ?>
                                <option value="<?= $siswa['id']; ?>">
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
                                <option value="<?= $jenis['id']; ?>">
                                    <?= htmlspecialchars($jenis['nama_kategori'] ?? '-'); ?>
                                    - <?= htmlspecialchars($jenis['nama']); ?>
                                    (<?= htmlspecialchars($jenis['poin']); ?> poin)
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Guru</label>
                        <select name="guru_id" class="form-select">
                            <option value="0">-- Pilih Guru (Opsional) --</option>

                            <?php while ($guru = mysqli_fetch_assoc($query_guru)): ?>
                                <option value="<?= $guru['id']; ?>">
                                    <?= htmlspecialchars($guru['nama']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

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
                    <button type="submit" name="simpan" class="btn btn-tambah">
                        Simpan Pelanggaran
                    </button>

                    <a href="pelanggaran.php" class="btn btn-secondary">
                        Batal
                    </a>
                </div>

            </form>
        </div>
    <?php endif; ?>

    <div class="box">
        <h5 class="mb-3">Daftar Pelanggaran</h5>

        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-5">
                <input
                    type="text"
                    name="cari"
                    class="form-control"
                    placeholder="Cari NIS, siswa, kategori, jenis, atau guru..."
                    value="<?= htmlspecialchars($cari); ?>"
                >
            </div>

            <div class="col-auto">
                <button type="submit" class="btn btn-secondary">Cari</button>
                <a href="pelanggaran.php" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
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
                    <?php if ($query_pelanggaran && mysqli_num_rows($query_pelanggaran) > 0): ?>
                        <?php $no = $offset + 1; ?>

                        <?php while ($row = mysqli_fetch_assoc($query_pelanggaran)): ?>
                            <?php $status = $row['status'] ?? 'tercatat'; ?>

                            <tr>
                                <td><?= $no++; ?></td>

                                <td><?= htmlspecialchars($row['nis'] ?? '-'); ?></td>

                                <td>
                                    <b><?= htmlspecialchars($row['nama_siswa'] ?? '-'); ?></b>
                                </td>

                                <td><?= htmlspecialchars($row['nama_kategori'] ?? '-'); ?></td>

                                <td><?= htmlspecialchars($row['nama_jenis'] ?? '-'); ?></td>

                                <td><b><?= htmlspecialchars($row['poin'] ?? '0'); ?></b></td>

                                <td><?= htmlspecialchars($row['nama_guru'] ?? '-'); ?></td>

                                <td><?= htmlspecialchars($row['tanggal'] ?? '-'); ?></td>

                                <td><?= htmlspecialchars($row['keterangan'] ?? '-'); ?></td>

                                <td>
                                    <span class="status <?= htmlspecialchars($status); ?>">
                                        <?= htmlspecialchars(ucfirst($status)); ?>
                                    </span>
                                </td>

                                <td class="text-nowrap">
                                    <a
                                        href="edit_pelanggaran.php?id=<?= $row['id']; ?>"
                                        class="btn btn-sm btn-tambah"
                                    >Edit</a>

                                    <a
                                        href="pelanggaran.php?hapus=<?= $row['id']; ?>"
                                        class="btn btn-sm btn-hapus"
                                        onclick="return confirm('Yakin ingin menghapus data pelanggaran ini?')"
                                    >Hapus</a>
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

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <small class="text-muted">
                Total <?= $total_data; ?> data pelanggaran
            </small>

            <nav>
                <ul class="pagination pagination-sm mb-0">
                    <?php for ($i = 1; $i <= $total_halaman; $i++): ?>
                        <li class="page-item <?= ($halaman == $i) ? 'active' : ''; ?>">
                            <a
                                class="page-link"
                                href="?halaman=<?= $i; ?>&cari=<?= urlencode($cari); ?>"
                            ><?= $i; ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        </div>
    </div>

</div>

</body>
</html>
