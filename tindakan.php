
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
$user_id = (int)($_SESSION['user_id'] ?? $_SESSION['id'] ?? 0);

$error = "";

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$tampil_form = isset($_GET['tambah']) || $_SERVER['REQUEST_METHOD'] === 'POST';

/* TAMBAH TINDAKAN */
if (isset($_POST['simpan'])) {

    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        die("Permintaan tidak valid.");
    }

    $pelanggaran_id = (int)($_POST['pelanggaran_id'] ?? 0);
    $tanggal = trim($_POST['tanggal'] ?? '');
    $tindakan = trim($_POST['tindakan'] ?? '');
    $catatan = trim($_POST['catatan'] ?? '');

    if ($pelanggaran_id <= 0 || $tanggal === '' || $tindakan === '') {
        $error = "Semua data wajib diisi.";
    } elseif ($user_id <= 0) {
        $error = "ID akun petugas tidak ditemukan. Silakan login kembali.";
    } else {
        $cek = mysqli_prepare(
            $koneksi,
            "SELECT id FROM pelanggaran WHERE id = ? LIMIT 1"
        );

        mysqli_stmt_bind_param($cek, "i", $pelanggaran_id);
        mysqli_stmt_execute($cek);
        $hasil_cek = mysqli_fetch_assoc(mysqli_stmt_get_result($cek));
        mysqli_stmt_close($cek);

        if (!$hasil_cek) {
            $error = "Data pelanggaran tidak ditemukan.";
        } else {
            $stmt = mysqli_prepare(
                $koneksi,
                "INSERT INTO tindakan
                (pelanggaran_id, tanggal, tindakan, petugas_id, catatan)
                VALUES (?, ?, ?, ?, ?)"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "issis",
                $pelanggaran_id,
                $tanggal,
                $tindakan,
                $user_id,
                $catatan
            );

            if (mysqli_stmt_execute($stmt)) {
                header("Location: tindakan.php?berhasil=1");
                exit;
            } else {
                $error = "Gagal menyimpan tindakan: " . mysqli_stmt_error($stmt);
            }

            mysqli_stmt_close($stmt);
        }
    }
}

/* HAPUS TINDAKAN */
if (isset($_POST['hapus'])) {

    if ($role !== 'admin') {
        die("Akses ditolak.");
    }

    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        die("Permintaan tidak valid.");
    }

    $id = (int)($_POST['id'] ?? 0);

    if ($id > 0) {
        $stmt = mysqli_prepare(
            $koneksi,
            "DELETE FROM tindakan WHERE id = ?"
        );

        mysqli_stmt_bind_param($stmt, "i", $id);

        if (mysqli_stmt_execute($stmt)) {
            header("Location: tindakan.php?dihapus=1");
            exit;
        } else {
            $error = "Gagal menghapus tindakan: " . mysqli_stmt_error($stmt);
        }

        mysqli_stmt_close($stmt);
    }
}

/* DATA PELANGGARAN UNTUK FORM */
$pelanggaran = mysqli_query(
    $koneksi,
    "SELECT p.id, s.nama AS nama_siswa,
            jp.nama AS nama_pelanggaran
     FROM pelanggaran p
     INNER JOIN siswa s ON p.siswa_id = s.id
     INNER JOIN jenis_pelanggaran jp ON p.jenis_id = jp.id
     ORDER BY p.id DESC"
);

if (!$pelanggaran) {
    die("Gagal mengambil data pelanggaran: " . mysqli_error($koneksi));
}

/* PENCARIAN */
$cari = trim($_GET['cari'] ?? '');
$where = "";

if ($cari !== '') {
    $cari_sql = mysqli_real_escape_string($koneksi, $cari);

    $where = "WHERE
        s.nama LIKE '%$cari_sql%'
        OR jp.nama LIKE '%$cari_sql%'
        OR u.nama LIKE '%$cari_sql%'
        OR t.tindakan LIKE '%$cari_sql%'
        OR t.catatan LIKE '%$cari_sql%'";
}

/* HITUNG DATA */
$jumlah_data = mysqli_query(
    $koneksi,
    "SELECT COUNT(*) AS total
     FROM tindakan t
     INNER JOIN pelanggaran p ON t.pelanggaran_id = p.id
     INNER JOIN siswa s ON p.siswa_id = s.id
     INNER JOIN jenis_pelanggaran jp ON p.jenis_id = jp.id
     LEFT JOIN users u ON t.petugas_id = u.id
     $where"
);

if (!$jumlah_data) {
    die("Gagal menghitung data: " . mysqli_error($koneksi));
}

$total_data = (int)mysqli_fetch_assoc($jumlah_data)['total'];

$data_per_halaman = 5;
$total_halaman = max(1, (int)ceil($total_data / $data_per_halaman));

$halaman = max(1, (int)($_GET['halaman'] ?? 1));
$halaman = min($halaman, $total_halaman);
$mulai = ($halaman - 1) * $data_per_halaman;

/* AMBIL DATA TINDAKAN */
$data = mysqli_query(
    $koneksi,
    "SELECT t.id, t.tanggal, t.tindakan, t.catatan,
            s.nama AS nama_siswa,
            jp.nama AS nama_pelanggaran,
            u.nama AS nama_petugas
     FROM tindakan t
     INNER JOIN pelanggaran p ON t.pelanggaran_id = p.id
     INNER JOIN siswa s ON p.siswa_id = s.id
     INNER JOIN jenis_pelanggaran jp ON p.jenis_id = jp.id
     LEFT JOIN users u ON t.petugas_id = u.id
     $where
     ORDER BY t.id DESC
     LIMIT $mulai, $data_per_halaman"
);

if (!$data) {
    die("Gagal mengambil data tindakan: " . mysqli_error($koneksi));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Data Tindakan</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
* { box-sizing: border-box; }

body {
    margin: 0;
    background: #f4f6f8;
    font-family: Arial, sans-serif;
    color: #333;
    font-size: 14px;
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
    z-index: 1000;
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
    min-height: 100vh;
}

.header, .box {
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
    gap: 15px;
}

.header h1 {
    margin: 0;
    font-size: 24px;
}

.header p {
    color: #777;
    margin: 5px 0 0;
}

.btn-tambah, .btn-edit {
    background: #198754;
    color: white;
    border: 1px solid #198754;
}

.btn-tambah:hover, .btn-edit:hover {
    background: #146c43;
    color: white;
}

.btn-hapus {
    background: #b42323;
    color: white;
}

.btn-hapus:hover {
    background: #941d1d;
    color: white;
}

.table th {
    background: #687280;
    color: white;
    white-space: nowrap;
}

.table td {
    vertical-align: middle;
}

.form-label {
    font-weight: bold;
}

@media(max-width: 700px) {
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

<!-- SIDEBAR -->
<div class="sidebar">

    <div class="sidebar-title">
        ⚖ Pelanggaran<br>Siswa
    </div>

    <?php if ($role === 'admin') { ?>

        <a href="dashboard.php">🏠 Dashboard</a>
        <a href="kelola_siswa.php">Data Siswa</a>
        <a href="kelola_guru.php">Data Guru</a>
        <a href="kelola_kelas.php">Data Kelas</a>
        <a href="kelola_tahun_ajaran.php">Tahun Ajaran</a>
        <a href="kelola_wali_kelas.php">Wali Kelas</a>
        <a href="penempatan_siswa.php">Penempatan Siswa</a>
        <a href="kelola_pelanggaran_kategori.php">⚠ Kategori Pelanggaran</a>
        <a href="pelanggaran.php">Data Pelanggaran</a>
        <a href="tindakan.php" class="active">Data Tindakan</a>
        <a href="riwayat.php">Riwayat</a>
        <a href="rekap_poin.php">Rekap Poin</a>
        <a href="laporan.php">Laporan</a>
        <a href="about_me.php">ℹ About Me</a>

    <?php } else { ?>

        <a href="dashboard.php">🏠 Dashboard</a>
        <a href="catat_pelanggaran.php">Catat Pelanggaran</a>
        <a href="pelanggaran.php">Data Pelanggaran</a>
        <a href="tindakan.php" class="active">Data Tindakan</a>
        <a href="riwayat.php">Riwayat</a>
        <a href="rekap_poin.php">Rekap Poin</a>

    <?php } ?>

    <a href="logout.php" class="logout">🚪 Logout</a>
</div>

<!-- KONTEN -->
<div class="content">

    <div class="header">
        <div>
            <h1>Data Tindakan</h1>
            <p>Mengelola tindakan terhadap pelanggaran siswa.</p>
        </div>

        <a href="tindakan.php?tambah=1" class="btn btn-tambah">
            + Tambah Tindakan
        </a>
    </div>

    <?php if (isset($_GET['berhasil'])) { ?>
        <div class="alert alert-success">Tindakan berhasil ditambahkan.</div>
    <?php } ?>

    <?php if (isset($_GET['dihapus'])) { ?>
        <div class="alert alert-success">Tindakan berhasil dihapus.</div>
    <?php } ?>

    <?php if ($error !== '') { ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($error); ?>
        </div>
    <?php } ?>

    <?php if ($tampil_form) { ?>
        <div class="box">
            <h5 class="mb-3">Tambah Tindakan</h5>

            <?php if (mysqli_num_rows($pelanggaran) > 0) { ?>

                <form method="POST">

                    <input type="hidden" name="csrf_token"
                           value="<?= htmlspecialchars($_SESSION['csrf_token']); ?>">

                    <div class="mb-3">
                        <label class="form-label">Pelanggaran Siswa</label>

                        <select name="pelanggaran_id" class="form-select" required>
                            <option value="">-- Pilih Pelanggaran --</option>

                            <?php while ($p = mysqli_fetch_assoc($pelanggaran)) { ?>
                                <option value="<?= (int)$p['id']; ?>"
                                    <?= (int)($_POST['pelanggaran_id'] ?? 0) === (int)$p['id'] ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($p['nama_siswa']); ?> -
                                    <?= htmlspecialchars($p['nama_pelanggaran']); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tanggal Tindakan</label>
                        <input type="date" name="tanggal" class="form-control"
                               value="<?= htmlspecialchars($_POST['tanggal'] ?? date('Y-m-d')); ?>"
                               required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tindakan</label>
                        <textarea name="tindakan" class="form-control" rows="3"
                                  placeholder="Masukkan tindakan yang diberikan..."
                                  required><?= htmlspecialchars($_POST['tindakan'] ?? ''); ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Catatan</label>
                        <textarea name="catatan" class="form-control" rows="3"
                                  placeholder="Catatan tambahan (opsional)"><?= htmlspecialchars($_POST['catatan'] ?? ''); ?></textarea>
                    </div>

                    <button type="submit" name="simpan" class="btn btn-tambah">
                        Simpan Tindakan
                    </button>

                    <a href="tindakan.php" class="btn btn-secondary">Batal</a>
                </form>

            <?php } else { ?>
                <div class="alert alert-warning mb-0">
                    Belum ada data pelanggaran. Catat pelanggaran terlebih dahulu.
                </div>
            <?php } ?>
        </div>
    <?php } ?>

    <div class="box">

        <h5 class="mb-3">Daftar Tindakan Siswa</h5>

        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-5">
                <input type="text" name="cari" class="form-control"
                       placeholder="Cari nama siswa, pelanggaran, petugas..."
                       value="<?= htmlspecialchars($cari); ?>">
            </div>

            <div class="col-auto">
                <button type="submit" class="btn btn-secondary">Cari</button>
                <a href="tindakan.php" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Siswa</th>
                        <th>Jenis Pelanggaran</th>
                        <th>Tanggal</th>
                        <th>Tindakan</th>
                        <th>Petugas</th>
                        <th>Catatan</th>
                        <?php if ($role === 'admin') { ?>
                            <th>Aksi</th>
                        <?php } ?>
                    </tr>
                </thead>

                <tbody>
                    <?php
                    $no = $mulai + 1;

                    if (mysqli_num_rows($data) > 0) {
                        while ($row = mysqli_fetch_assoc($data)) {
                    ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><?= htmlspecialchars($row['nama_siswa']); ?></td>
                            <td><?= htmlspecialchars($row['nama_pelanggaran']); ?></td>
                            <td><?= htmlspecialchars($row['tanggal']); ?></td>
                            <td><?= htmlspecialchars($row['tindakan']); ?></td>
                            <td><?= htmlspecialchars($row['nama_petugas'] ?? '-'); ?></td>
                            <td><?= htmlspecialchars($row['catatan'] ?? '-'); ?></td>

                            <?php if ($role === 'admin') { ?>
                                <td>
                                    <a href="edit_tindakan.php?id=<?= (int)$row['id']; ?>"
                                       class="btn btn-sm btn-edit mb-1">Edit</a>

                                    <form method="POST" class="d-inline"
                                          onsubmit="return confirm('Yakin ingin menghapus tindakan ini?')">
                                        <input type="hidden" name="csrf_token"
                                               value="<?= htmlspecialchars($_SESSION['csrf_token']); ?>">
                                        <input type="hidden" name="id"
                                               value="<?= (int)$row['id']; ?>">

                                        <button type="submit" name="hapus"
                                                class="btn btn-sm btn-hapus mb-1">
                                            Hapus
                                        </button>
                                    </form>
                                </td>
                            <?php } ?>
                        </tr>
                    <?php
                        }
                    } else {
                    ?>
                        <tr>
                            <td colspan="<?= $role === 'admin' ? 8 : 7; ?>"
                                class="text-center py-4">
                                Belum ada data tindakan.
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

        <?php if ($total_halaman > 1) { ?>
            <nav>
                <ul class="pagination mb-3">
                    <?php for ($i = 1; $i <= $total_halaman; $i++) { ?>
                        <li class="page-item <?= $halaman === $i ? 'active' : ''; ?>">
                            <a class="page-link"
                               href="?halaman=<?= $i; ?>&cari=<?= urlencode($cari); ?>">
                                <?= $i; ?>
                            </a>
                        </li>
                    <?php } ?>
                </ul>
            </nav>
        <?php } ?>

        <small class="text-muted">
            Menampilkan <?= mysqli_num_rows($data); ?>
            dari <?= $total_data; ?> data tindakan.
        </small>

    </div>
</div>

</body>
</html>
