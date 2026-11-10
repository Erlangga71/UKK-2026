
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "database.php";
require_once "auth.php";

hanyaRole(['admin']);

$error = "";
$tampil_form = isset($_GET['tambah']) && $_GET['tambah'] == '1';

/* =========================
   CSRF TOKEN
========================= */

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$csrf_token = $_SESSION['csrf_token'];

/* =========================
   TAMBAH TAHUN AJARAN
========================= */

if (isset($_POST['simpan'])) {

    $tampil_form = true;

    if (!hash_equals($csrf_token, $_POST['csrf_token'] ?? '')) {
        die("Token keamanan tidak valid. Silakan muat ulang halaman.");
    }

    $nama = trim($_POST['nama'] ?? '');
    $tanggal_mulai = $_POST['tanggal_mulai'] ?? '';
    $tanggal_selesai = $_POST['tanggal_selesai'] ?? '';
    $aktif = isset($_POST['aktif']) ? 1 : 0;

    if ($nama == '' || $tanggal_mulai == '' || $tanggal_selesai == '') {

        $error = "Semua data wajib diisi.";

    } elseif ($tanggal_selesai < $tanggal_mulai) {

        $error = "Tanggal selesai tidak boleh sebelum tanggal mulai.";

    } else {

        $cek = mysqli_prepare(
            $koneksi,
            "SELECT id FROM tahun_ajaran WHERE nama = ?"
        );

        mysqli_stmt_bind_param($cek, "s", $nama);
        mysqli_stmt_execute($cek);

        $hasil_cek = mysqli_stmt_get_result($cek);
        $duplikat = mysqli_num_rows($hasil_cek) > 0;

        mysqli_stmt_close($cek);

        if ($duplikat) {

            $error = "Nama tahun ajaran sudah tersedia.";

        } else {

            mysqli_begin_transaction($koneksi);

            try {

                if ($aktif == 1) {

                    $cek_aktif = mysqli_query(
                        $koneksi,
                        "SELECT nama FROM tahun_ajaran
                         WHERE aktif = 1
                         LIMIT 1
                         FOR UPDATE"
                    );

                    if (!$cek_aktif) {
                        throw new Exception(mysqli_error($koneksi));
                    }

                    if (mysqli_num_rows($cek_aktif) > 0) {

                        $tahun_aktif = mysqli_fetch_assoc($cek_aktif)['nama'];

                        throw new Exception(
                            "Tahun ajaran " . $tahun_aktif .
                            " masih aktif. Nonaktifkan terlebih dahulu."
                        );
                    }
                }

                $stmt = mysqli_prepare(
                    $koneksi,
                    "INSERT INTO tahun_ajaran
                    (nama, tanggal_mulai, tanggal_selesai, aktif)
                    VALUES (?, ?, ?, ?)"
                );

                if (!$stmt) {
                    throw new Exception(mysqli_error($koneksi));
                }

                mysqli_stmt_bind_param(
                    $stmt,
                    "sssi",
                    $nama,
                    $tanggal_mulai,
                    $tanggal_selesai,
                    $aktif
                );

                if (!mysqli_stmt_execute($stmt)) {
                    throw new Exception(mysqli_stmt_error($stmt));
                }

                mysqli_stmt_close($stmt);

                mysqli_commit($koneksi);

                header("Location: kelola_tahun_ajaran.php");
                exit;

            } catch (Exception $e) {

                mysqli_rollback($koneksi);
                $error = $e->getMessage();
            }
        }
    }
}

/* =========================
   HAPUS TAHUN AJARAN
========================= */

if (isset($_POST['hapus'])) {

    if (!hash_equals($csrf_token, $_POST['csrf_token'] ?? '')) {
        die("Token keamanan tidak valid. Silakan muat ulang halaman.");
    }

    $id = intval($_POST['id'] ?? 0);

    if ($id <= 0) {

        $error = "ID tahun ajaran tidak valid.";

    } else {

        $stmt = mysqli_prepare(
            $koneksi,
            "SELECT aktif FROM tahun_ajaran WHERE id = ?"
        );

        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);

        $hasil = mysqli_stmt_get_result($stmt);
        $tahun = mysqli_fetch_assoc($hasil);

        mysqli_stmt_close($stmt);

        if (!$tahun) {

            $error = "Data tahun ajaran tidak ditemukan.";

        } elseif ($tahun['aktif'] == 1) {

            $error = "Tahun ajaran yang sedang aktif tidak boleh dihapus.";

        } else {

            $stmt = mysqli_prepare(
                $koneksi,
                "SELECT id FROM penempatan_siswa
                 WHERE tahun_ajaran_id = ?
                 LIMIT 1"
            );

            mysqli_stmt_bind_param($stmt, "i", $id);
            mysqli_stmt_execute($stmt);

            $hasil_penempatan = mysqli_stmt_get_result($stmt);
            $ada_penempatan = mysqli_num_rows($hasil_penempatan) > 0;

            mysqli_stmt_close($stmt);

            $stmt = mysqli_prepare(
                $koneksi,
                "SELECT id FROM wali_kelas
                 WHERE tahun_ajaran_id = ?
                 LIMIT 1"
            );

            mysqli_stmt_bind_param($stmt, "i", $id);
            mysqli_stmt_execute($stmt);

            $hasil_wali = mysqli_stmt_get_result($stmt);
            $ada_wali = mysqli_num_rows($hasil_wali) > 0;

            mysqli_stmt_close($stmt);

            if ($ada_penempatan || $ada_wali) {

                $error = "Tahun ajaran tidak bisa dihapus karena masih digunakan pada data penempatan siswa atau wali kelas.";

            } else {

                $stmt = mysqli_prepare(
                    $koneksi,
                    "DELETE FROM tahun_ajaran WHERE id = ?"
                );

                mysqli_stmt_bind_param($stmt, "i", $id);

                if (mysqli_stmt_execute($stmt)) {

                    mysqli_stmt_close($stmt);

                    header("Location: kelola_tahun_ajaran.php");
                    exit;

                } else {

                    $error = "Gagal menghapus tahun ajaran: " .
                             mysqli_stmt_error($stmt);

                    mysqli_stmt_close($stmt);
                }
            }
        }
    }
}

/* =========================
   PENCARIAN
========================= */

$cari = trim($_GET['cari'] ?? '');
$cari_aman = mysqli_real_escape_string($koneksi, $cari);

$where = "";

if ($cari != '') {
    $where = "WHERE nama LIKE '%$cari_aman%'";
}

/* =========================
   PAGINATION
========================= */

$per_halaman = 10;

$halaman = max(1, (int)($_GET['halaman'] ?? 1));

/* =========================
   HITUNG DATA
========================= */

$query_jumlah = mysqli_query(
    $koneksi,
    "SELECT COUNT(*) AS total
     FROM tahun_ajaran
     $where"
);

if (!$query_jumlah) {
    die("Gagal menghitung data: " . mysqli_error($koneksi));
}

$total_data = (int)mysqli_fetch_assoc($query_jumlah)['total'];

$total_halaman = max(1, (int)ceil($total_data / $per_halaman));

if ($halaman > $total_halaman) {
    $halaman = $total_halaman;
}

$mulai = ($halaman - 1) * $per_halaman;

/* =========================
   AMBIL DATA TAHUN AJARAN
========================= */

$data = mysqli_query(
    $koneksi,
    "SELECT id, nama, tanggal_mulai, tanggal_selesai, aktif
     FROM tahun_ajaran
     $where
     ORDER BY id DESC
     LIMIT $mulai, $per_halaman"
);

if (!$data) {
    die("Gagal mengambil data: " . mysqli_error($koneksi));
}
?>

<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Tahun Ajaran</title>

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
}

/* PANEL */

.card {
    border: 1px solid #ddd;
    border-radius: 6px;
    box-shadow: none;
}

.card-body {
    padding: 20px;
}

/* BUTTON */

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

<!-- SIDEBAR -->

<div class="sidebar">

    <div class="sidebar-title">
        Pelanggaran<br>Siswa
    </div>

    <a href="dashboard.php">Dashboard</a>
    <a href="kelola_siswa.php">Data Siswa</a>
    <a href="kelola_guru.php">Data Guru</a>
    <a href="kelola_kelas.php">Data Kelas</a>
    <a href="kelola_tahun_ajaran.php" class="active">Tahun Ajaran</a>
    <a href="kelola_wali_kelas.php">Wali Kelas</a>
    <a href="penempatan_siswa.php">Penempatan Siswa</a>
    <a href="kelola_pelanggaran_kategori.php">Kategori Pelanggaran</a>
    <a href="pelanggaran.php">Data Pelanggaran</a>
    <a href="laporan.php">Laporan</a>
    <a href="about_me.php">About Me</a>

    <a href="logout.php" class="logout">Logout</a>

</div>

<!-- CONTENT -->

<div class="content">

    <!-- HEADER -->

    <div class="card mb-3">

        <div class="card-body">

            <h4 class="mb-1">Tahun Ajaran</h4>

            <p class="text-muted mb-0">
                Kelola data tahun ajaran sekolah.
            </p>

        </div>

    </div>

    <!-- PESAN ERROR -->

    <?php if ($error != '') { ?>

        <div class="alert alert-danger">
            <?= htmlspecialchars($error); ?>
        </div>

    <?php } ?>

    <!-- FORM TAMBAH -->

    <?php if ($tampil_form) { ?>

        <div class="card mb-3">

            <div class="card-body">

                <h5 class="mb-3">Tambah Tahun Ajaran</h5>

                <form method="POST">

                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= htmlspecialchars($csrf_token); ?>"
                    >

                    <div class="row g-3">

                        <div class="col-md-6">

                            <label class="form-label fw-bold">
                                Nama Tahun Ajaran
                            </label>

                            <input
                                type="text"
                                name="nama"
                                class="form-control"
                                placeholder="Contoh: 2026/2027"
                                value="<?= htmlspecialchars($_POST['nama'] ?? ''); ?>"
                                required
                            >

                        </div>

                        <div class="col-md-6">

                            <label class="form-label fw-bold">
                                Tanggal Mulai
                            </label>

                            <input
                                type="date"
                                name="tanggal_mulai"
                                class="form-control"
                                value="<?= htmlspecialchars($_POST['tanggal_mulai'] ?? ''); ?>"
                                required
                            >

                        </div>

                        <div class="col-md-6">

                            <label class="form-label fw-bold">
                                Tanggal Selesai
                            </label>

                            <input
                                type="date"
                                name="tanggal_selesai"
                                class="form-control"
                                value="<?= htmlspecialchars($_POST['tanggal_selesai'] ?? ''); ?>"
                                required
                            >

                        </div>

                        <div class="col-md-12">

                            <div class="form-check">

                                <input
                                    type="checkbox"
                                    name="aktif"
                                    value="1"
                                    class="form-check-input"
                                    id="aktif"
                                    <?= isset($_POST['aktif']) ? 'checked' : ''; ?>
                                >

                                <label class="form-check-label" for="aktif">
                                    Jadikan tahun ajaran aktif
                                </label>

                            </div>

                        </div>

                    </div>

                    <div class="mt-3">

                        <button
                            type="submit"
                            name="simpan"
                            class="btn btn-stabilo btn-sm"
                        >
                            Simpan
                        </button>

                        <a
                            href="kelola_tahun_ajaran.php"
                            class="btn btn-outline-secondary btn-sm"
                        >
                            Batal
                        </a>

                    </div>

                </form>

            </div>

        </div>

    <?php } ?>

    <!-- DAFTAR TAHUN AJARAN -->

    <div class="card">

        <div class="card-body">

            <div class="d-flex justify-content-between align-items-center mb-3">

                <h5 class="mb-0">Daftar Tahun Ajaran</h5>

                <?php if (!$tampil_form) { ?>

                    <a
                        href="kelola_tahun_ajaran.php?tambah=1"
                        class="btn btn-sm btn-stabilo"
                    >
                        + Tambah Tahun Ajaran
                    </a>

                <?php } ?>

            </div>

            <!-- PENCARIAN -->

            <form method="GET" class="row g-2 mb-3">

                <div class="col-md-5">

                    <input
                        type="text"
                        name="cari"
                        class="form-control form-control-sm"
                        placeholder="Cari nama tahun ajaran..."
                        value="<?= htmlspecialchars($cari); ?>"
                    >

                </div>

                <div class="col-auto">

                    <button type="submit" class="btn btn-secondary btn-sm">
                        Cari
                    </button>

                    <?php if ($cari != '') { ?>

                        <a
                            href="kelola_tahun_ajaran.php"
                            class="btn btn-outline-secondary btn-sm"
                        >
                            Reset
                        </a>

                    <?php } ?>

                </div>

            </form>

            <!-- TABEL TAHUN AJARAN -->

            <div class="table-responsive">

                <table class="table table-bordered table-hover align-middle">

                    <thead class="table-secondary">

                        <tr>
                            <th width="55">No</th>
                            <th>Nama</th>
                            <th>Tanggal Mulai</th>
                            <th>Tanggal Selesai</th>
                            <th>Status</th>
                            <th width="150">Aksi</th>
                        </tr>

                    </thead>

                    <tbody>

                    <?php if ($data && mysqli_num_rows($data) > 0) { ?>

                        <?php $no = $mulai + 1; ?>

                        <?php while ($row = mysqli_fetch_assoc($data)) { ?>

                            <tr>

                                <td><?= $no++; ?></td>

                                <td>
                                    <strong>
                                        <?= htmlspecialchars($row['nama']); ?>
                                    </strong>
                                </td>

                                <td>
                                    <?= htmlspecialchars($row['tanggal_mulai']); ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($row['tanggal_selesai']); ?>
                                </td>

                                <td>

                                    <?php if ($row['aktif'] == 1) { ?>

                                        <span class="badge bg-success">
                                            Aktif
                                        </span>

                                    <?php } else { ?>

                                        <span class="badge bg-danger">
                                            Tidak Aktif
                                        </span>

                                    <?php } ?>

                                </td>

                                <td>

                                    <a
                                        href="edit_tahun_ajaran.php?id=<?= (int)$row['id']; ?>"
                                        class="btn btn-sm btn-stabilo"
                                    >
                                        Edit
                                    </a>

                                    <form
                                        method="POST"
                                        style="display:inline;"
                                        onsubmit="return confirm('Yakin ingin menghapus tahun ajaran ini?')"
                                    >

                                        <input
                                            type="hidden"
                                            name="csrf_token"
                                            value="<?= htmlspecialchars($csrf_token); ?>"
                                        >

                                        <input
                                            type="hidden"
                                            name="id"
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

                        <?php } ?>

                    <?php } else { ?>

                        <tr>

                            <td colspan="6" class="text-center py-4">
                                Data tahun ajaran tidak ditemukan.
                            </td>

                        </tr>

                    <?php } ?>

                    </tbody>

                </table>

            </div>

            <!-- INFORMASI DATA -->

            <div class="text-muted small">

                Menampilkan
                <?= $total_data > 0 ? $mulai + 1 : 0; ?>
                -
                <?= min($mulai + $per_halaman, $total_data); ?>
                dari
                <?= $total_data; ?>
                data tahun ajaran.

            </div>

            <!-- PAGINATION -->

            <?php if ($total_halaman > 1) { ?>

                <nav class="mt-3">

                    <ul class="pagination pagination-sm mb-0">

                        <?php if ($halaman > 1) { ?>

                            <li class="page-item">

                                <a
                                    class="page-link"
                                    href="?halaman=<?= $halaman - 1; ?>&cari=<?= urlencode($cari); ?>"
                                >
                                    &laquo;
                                </a>

                            </li>

                        <?php } ?>

                        <?php for ($i = 1; $i <= $total_halaman; $i++) { ?>

                            <li class="page-item <?= $i == $halaman ? 'active' : ''; ?>">

                                <a
                                    class="page-link"
                                    href="?halaman=<?= $i; ?>&cari=<?= urlencode($cari); ?>"
                                >
                                    <?= $i; ?>
                                </a>

                            </li>

                        <?php } ?>

                        <?php if ($halaman < $total_halaman) { ?>

                            <li class="page-item">

                                <a
                                    class="page-link"
                                    href="?halaman=<?= $halaman + 1; ?>&cari=<?= urlencode($cari); ?>"
                                >
                                    &raquo;
                                </a>

                            </li>

                        <?php } ?>

                    </ul>

                </nav>

            <?php } ?>

        </div>

    </div>

</div>

</body>
</html>
