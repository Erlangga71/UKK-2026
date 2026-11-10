
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
   TAMBAH KATEGORI
========================= */

if (isset($_POST['simpan'])) {

    $tampil_form = true;
    $nama = trim($_POST['nama'] ?? '');

    if ($nama === '') {

        $error = "Nama kategori wajib diisi.";

    } else {

        $cek = mysqli_prepare(
            $koneksi,
            "SELECT id FROM kategori WHERE nama = ? LIMIT 1"
        );

        mysqli_stmt_bind_param($cek, "s", $nama);
        mysqli_stmt_execute($cek);
        $hasil_cek = mysqli_stmt_get_result($cek);

        if (mysqli_num_rows($hasil_cek) > 0) {

            $error = "Kategori tersebut sudah ada.";

        } else {

            $stmt = mysqli_prepare(
                $koneksi,
                "INSERT INTO kategori (nama) VALUES (?)"
            );

            mysqli_stmt_bind_param($stmt, "s", $nama);

            if (mysqli_stmt_execute($stmt)) {

                mysqli_stmt_close($stmt);

                header("Location: kelola_pelanggaran_kategori.php");
                exit;

            } else {

                $error = "Gagal menambahkan kategori: " . mysqli_error($koneksi);

                mysqli_stmt_close($stmt);
            }
        }

        mysqli_stmt_close($cek);
    }
}

/* =========================
   HAPUS KATEGORI
========================= */

if (isset($_GET['hapus'])) {

    $id = (int)$_GET['hapus'];

    if ($id > 0) {

        $stmt = mysqli_prepare(
            $koneksi,
            "DELETE FROM kategori WHERE id = ?"
        );

        mysqli_stmt_bind_param($stmt, "i", $id);

        if (!mysqli_stmt_execute($stmt)) {

            $error = "Kategori tidak dapat dihapus. Pastikan kategori tidak sedang digunakan oleh jenis pelanggaran.";

        } else {

            mysqli_stmt_close($stmt);

            header("Location: kelola_pelanggaran_kategori.php");
            exit;
        }

        mysqli_stmt_close($stmt);
    }
}

/* =========================
   PENCARIAN
========================= */

$cari = trim($_GET['cari'] ?? '');
$cari_sql = "%" . $cari . "%";

$stmt_total = mysqli_prepare(
    $koneksi,
    "SELECT COUNT(*) AS total
     FROM kategori
     WHERE nama LIKE ?"
);

mysqli_stmt_bind_param($stmt_total, "s", $cari_sql);
mysqli_stmt_execute($stmt_total);

$total_data = (int)mysqli_fetch_assoc(
    mysqli_stmt_get_result($stmt_total)
)['total'];

mysqli_stmt_close($stmt_total);

/* =========================
   PAGINATION
========================= */

$batas = 10;

$total_halaman = max(1, (int)ceil($total_data / $batas));

$halaman = max(1, (int)($_GET['halaman'] ?? 1));
$halaman = min($halaman, $total_halaman);

$offset = ($halaman - 1) * $batas;

/* =========================
   DATA KATEGORI
========================= */

$stmt_data = mysqli_prepare(
    $koneksi,
    "SELECT id, nama
     FROM kategori
     WHERE nama LIKE ?
     ORDER BY id ASC
     LIMIT ? OFFSET ?"
);

mysqli_stmt_bind_param(
    $stmt_data,
    "sii",
    $cari_sql,
    $batas,
    $offset
);

mysqli_stmt_execute($stmt_data);
$data = mysqli_stmt_get_result($stmt_data);
?>

<!DOCTYPE html>
<html lang="id">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Kategori Pelanggaran</title>

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

    <div class="sidebar-title">⚖ Pelanggaran<br>Siswa</div>

    <a href="dashboard.php">Dashboard</a>

    <a href="kelola_siswa.php">Data Siswa</a>

    <a href="kelola_guru.php">Data Guru</a>

    <a href="kelola_kelas.php">Data Kelas</a>

    <a href="kelola_tahun_ajaran.php">Tahun Ajaran</a>

    <a href="kelola_wali_kelas.php">Wali Kelas</a>

    <a href="penempatan_siswa.php">Penempatan Siswa</a>

    <a href="kelola_pelanggaran_kategori.php" class="active">Kategori Pelanggaran</a>

    <a href="pelanggaran.php">Data Pelanggaran</a>

    <a href="tindakan.php">Data Tindakan</a>

    <a href="laporan.php">Laporan</a>

    <a href="about_me.php">About Me</a>

    <a href="logout.php" class="logout">Logout</a>

</div>

<!-- CONTENT -->

<div class="content">

    <!-- HEADER -->

    <div class="card mb-3">

        <div class="card-body">

            <h4 class="mb-1">Kategori Pelanggaran</h4>

            <p class="text-muted mb-0">
                Kelola kategori pelanggaran siswa.
            </p>

        </div>

    </div>

    <!-- PESAN ERROR -->

    <?php if ($error !== '') { ?>

        <div class="alert alert-danger">
            <?= htmlspecialchars($error); ?>
        </div>

    <?php } ?>

    <!-- FORM TAMBAH -->

    <?php if ($tampil_form) { ?>

        <div class="card mb-3">

            <div class="card-body">

                <h5 class="mb-3">Tambah Kategori Pelanggaran</h5>

                <form method="POST">

                    <div class="mb-3">

                        <label class="form-label fw-bold">
                            Nama Kategori
                        </label>

                        <input
                            type="text"
                            name="nama"
                            class="form-control"
                            placeholder="Contoh: Kedisiplinan"
                            value="<?= htmlspecialchars($_POST['nama'] ?? ''); ?>"
                            required
                        >

                    </div>

                    <button
                        type="submit"
                        name="simpan"
                        class="btn btn-stabilo btn-sm"
                    >
                        Simpan
                    </button>

                    <a
                        href="kelola_pelanggaran_kategori.php"
                        class="btn btn-outline-secondary btn-sm"
                    >
                        Batal
                    </a>

                </form>

            </div>

        </div>

    <?php } ?>

    <!-- DAFTAR KATEGORI -->

    <div class="card">

        <div class="card-body">

            <div class="d-flex justify-content-between align-items-center mb-3">

                <h5 class="mb-0">Daftar Kategori Pelanggaran</h5>

                <?php if (!$tampil_form) { ?>

                    <a
                        href="kelola_pelanggaran_kategori.php?tambah=1"
                        class="btn btn-sm btn-stabilo"
                    >
                        + Tambah Kategori
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
                        placeholder="Cari nama kategori..."
                        value="<?= htmlspecialchars($cari); ?>"
                    >

                </div>

                <div class="col-auto">

                    <button type="submit" class="btn btn-secondary btn-sm">
                        Cari
                    </button>

                    <?php if ($cari !== '') { ?>

                        <a
                            href="kelola_pelanggaran_kategori.php"
                            class="btn btn-outline-secondary btn-sm"
                        >
                            Reset
                        </a>

                    <?php } ?>

                </div>

            </form>

            <!-- TABEL -->

            <div class="table-responsive">

                <table class="table table-bordered table-hover align-middle">

                    <thead class="table-secondary">

                        <tr>
                            <th width="55">No</th>
                            <th>Nama Kategori</th>
                            <th width="150">Aksi</th>
                        </tr>

                    </thead>

                    <tbody>

                    <?php if ($data && mysqli_num_rows($data) > 0) { ?>

                        <?php $no = $offset + 1; ?>

                        <?php while ($row = mysqli_fetch_assoc($data)) { ?>

                            <tr>

                                <td><?= $no++; ?></td>

                                <td>
                                    <strong>
                                        <?= htmlspecialchars($row['nama']); ?>
                                    </strong>
                                </td>

                                <td class="text-nowrap">

                                    <a
                                        href="edit_kategori.php?id=<?= (int)$row['id']; ?>"
                                        class="btn btn-sm btn-stabilo"
                                    >
                                        Edit
                                    </a>

                                    <a
                                        href="kelola_pelanggaran_kategori.php?hapus=<?= (int)$row['id']; ?>"
                                        class="btn btn-sm btn-danger"
                                        onclick="return confirm('Yakin ingin menghapus kategori ini?')"
                                    >
                                        Hapus
                                    </a>

                                </td>

                            </tr>

                        <?php } ?>

                    <?php } else { ?>

                        <tr>

                            <td colspan="3" class="text-center py-4">
                                Belum ada kategori pelanggaran.
                            </td>

                        </tr>

                    <?php } ?>

                    </tbody>

                </table>

            </div>

            <!-- INFORMASI DATA -->

            <div class="text-muted small">

                Menampilkan
                <?= $total_data > 0 ? $offset + 1 : 0; ?>
                -
                <?= min($offset + $batas, $total_data); ?>
                dari
                <?= $total_data; ?>
                data kategori.

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
