
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
   TAMBAH DATA SISWA
========================= */

if (isset($_POST['simpan'])) {

    $nis           = trim($_POST['nis'] ?? '');
    $nisn          = trim($_POST['nisn'] ?? '');
    $nama          = trim($_POST['nama'] ?? '');
    $jenis_kelamin = trim($_POST['jenis_kelamin'] ?? '');
    $alamat        = trim($_POST['alamat'] ?? '');
    $no_hp_ortu    = trim($_POST['no_hp_ortu'] ?? '');

    $tampil_form = true;

    if ($nis === '' || $nisn === '' || $nama === '') {

        $error = "NIS, NISN, dan nama wajib diisi.";

    } elseif (!ctype_digit($nis) || strlen($nis) > 15) {

        $error = "NIS hanya boleh angka dan maksimal 15 digit.";

    } elseif (!ctype_digit($nisn) || strlen($nisn) > 10) {

        $error = "NISN hanya boleh angka dan maksimal 10 digit.";

    } else {

        $stmt = mysqli_prepare(
            $koneksi,
            "SELECT id FROM siswa WHERE nis = ? OR nisn = ? LIMIT 1"
        );

        mysqli_stmt_bind_param($stmt, "ss", $nis, $nisn);
        mysqli_stmt_execute($stmt);

        $cek = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($cek) > 0) {

            $error = "NIS atau NISN tersebut sudah digunakan.";

        } else {

            $stmt_insert = mysqli_prepare(
                $koneksi,
                "INSERT INTO siswa
                (nis, nisn, nama, jenis_kelamin, alamat, no_hp_ortu, aktif)
                VALUES (?, ?, ?, ?, ?, ?, 1)"
            );

            mysqli_stmt_bind_param(
                $stmt_insert,
                "ssssss",
                $nis,
                $nisn,
                $nama,
                $jenis_kelamin,
                $alamat,
                $no_hp_ortu
            );

            if (mysqli_stmt_execute($stmt_insert)) {

                mysqli_stmt_close($stmt_insert);
                mysqli_stmt_close($stmt);

                header("Location: kelola_siswa.php");
                exit;

            } else {

                $error = "Gagal menambahkan siswa: " .
                    mysqli_stmt_error($stmt_insert);
            }

            mysqli_stmt_close($stmt_insert);
        }

        mysqli_stmt_close($stmt);
    }
}

/* =========================
   HAPUS DATA SISWA
========================= */

if (isset($_GET['hapus'])) {

    $id = (int) $_GET['hapus'];

    if ($id > 0) {

        $stmt = mysqli_prepare(
            $koneksi,
            "DELETE FROM siswa WHERE id = ?"
        );

        mysqli_stmt_bind_param($stmt, "i", $id);

        if (mysqli_stmt_execute($stmt)) {

            mysqli_stmt_close($stmt);

            header("Location: kelola_siswa.php");
            exit;

        } else {

            $error = "Data tidak bisa dihapus. Siswa mungkin masih digunakan pada data pelanggaran atau penempatan siswa.";

            mysqli_stmt_close($stmt);
        }
    }
}

/* =========================
   PENCARIAN
========================= */

$search = trim($_GET['cari'] ?? '');
$cari = "%{$search}%";

/* =========================
   PAGINATION
========================= */

$per_halaman = 10;

$halaman = max(1, (int)($_GET['halaman'] ?? 1));

/* =========================
   HITUNG DATA
========================= */

if ($search !== '') {

    $stmt_total = $koneksi->prepare(
        "SELECT COUNT(*) AS total
         FROM siswa
         WHERE nis LIKE ?
            OR nisn LIKE ?
            OR nama LIKE ?"
    );

    $stmt_total->bind_param(
        "sss",
        $cari,
        $cari,
        $cari
    );

} else {

    $stmt_total = $koneksi->prepare(
        "SELECT COUNT(*) AS total FROM siswa"
    );
}

$stmt_total->execute();

$total_data = (int)$stmt_total->get_result()->fetch_assoc()['total'];

$stmt_total->close();

$total_halaman = max(1, (int)ceil($total_data / $per_halaman));

if ($halaman > $total_halaman) {
    $halaman = $total_halaman;
}

$mulai = ($halaman - 1) * $per_halaman;

/* =========================
   AMBIL DATA SISWA
========================= */

if ($search !== '') {

    $stmt_data = $koneksi->prepare(
        "SELECT id, nis, nisn, nama, jenis_kelamin,
                alamat, no_hp_ortu, aktif
         FROM siswa
         WHERE nis LIKE ?
            OR nisn LIKE ?
            OR nama LIKE ?
         ORDER BY id DESC
         LIMIT ?, ?"
    );

    $stmt_data->bind_param(
        "sssii",
        $cari,
        $cari,
        $cari,
        $mulai,
        $per_halaman
    );

} else {

    $stmt_data = $koneksi->prepare(
        "SELECT id, nis, nisn, nama, jenis_kelamin,
                alamat, no_hp_ortu, aktif
         FROM siswa
         ORDER BY id DESC
         LIMIT ?, ?"
    );

    $stmt_data->bind_param(
        "ii",
        $mulai,
        $per_halaman
    );
}

$stmt_data->execute();
$data = $stmt_data->get_result();

?>

<!DOCTYPE html>
<html lang="id">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Data Siswa</title>

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

    <div class="sidebar-title">
        Pelanggaran<br>Siswa
    </div>

    <a href="dashboard.php">Dashboard</a>

    <a href="kelola_siswa.php" class="active">Data Siswa</a>

    <a href="kelola_guru.php">Data Guru</a>

    <a href="kelola_kelas.php">Data Kelas</a>

    <a href="kelola_tahun_ajaran.php">Tahun Ajaran</a>

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

            <h4 class="mb-1">Data Siswa</h4>

            <p class="text-muted mb-0">
                Kelola data siswa dalam sistem.
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

                <h5 class="mb-3">Tambah Data Siswa</h5>

                <form method="POST">

                    <div class="row g-3">

                        <div class="col-md-6">

                            <label class="form-label fw-bold">NIS</label>

                            <input
                                type="text"
                                name="nis"
                                class="form-control"
                                placeholder="Masukkan NIS"
                                maxlength="15"
                                inputmode="numeric"
                                pattern="[0-9]{1,15}"
                                title="NIS hanya boleh angka dan maksimal 15 digit."
                                oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,15)"
                                required
                            >

                        </div>

                        <div class="col-md-6">

                            <label class="form-label fw-bold">NISN</label>

                            <input
                                type="text"
                                name="nisn"
                                class="form-control"
                                placeholder="Masukkan NISN"
                                maxlength="10"
                                inputmode="numeric"
                                pattern="[0-9]{1,10}"
                                title="NISN hanya boleh angka dan maksimal 10 digit."
                                oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,10)"
                                required
                            >

                        </div>

                        <div class="col-md-6">

                            <label class="form-label fw-bold">Nama Siswa</label>

                            <input
                                type="text"
                                name="nama"
                                class="form-control"
                                placeholder="Masukkan nama siswa"
                                required
                            >

                        </div>

                        <div class="col-md-6">

                            <label class="form-label fw-bold">Jenis Kelamin</label>

                            <select name="jenis_kelamin" class="form-select">

                                <option value="">-- Pilih Jenis Kelamin --</option>
                                <option value="L">Laki-laki</option>
                                <option value="P">Perempuan</option>

                            </select>

                        </div>

                        <div class="col-md-6">

                            <label class="form-label fw-bold">No HP Orang Tua</label>

                            <input
                                type="text"
                                name="no_hp_ortu"
                                class="form-control"
                                placeholder="Masukkan nomor HP orang tua"
                            >

                        </div>

                        <div class="col-12">

                            <label class="form-label fw-bold">Alamat</label>

                            <textarea
                                name="alamat"
                                class="form-control"
                                rows="3"
                                placeholder="Masukkan alamat siswa"
                            ></textarea>

                        </div>

                    </div>

                    <div class="mt-3">

                        <button
                            type="submit"
                            name="simpan"
                            class="btn btn-stabilo btn-sm"
                        >
                            Simpan Siswa
                        </button>

                        <a
                            href="kelola_siswa.php"
                            class="btn btn-outline-secondary btn-sm"
                        >
                            Batal
                        </a>

                    </div>

                </form>

            </div>

        </div>

    <?php } ?>

    <!-- DAFTAR SISWA -->

    <div class="card">

        <div class="card-body">

            <div class="d-flex justify-content-between align-items-center mb-3">

                <h5 class="mb-0">Daftar Siswa</h5>

                <?php if (!$tampil_form) { ?>

                    <a
                        href="kelola_siswa.php?tambah=1"
                        class="btn btn-sm btn-stabilo"
                    >
                        + Tambah Siswa
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
                        placeholder="Cari NIS, NISN, atau nama siswa..."
                        value="<?= htmlspecialchars($search); ?>"
                    >

                </div>

                <div class="col-auto">

                    <button type="submit" class="btn btn-secondary btn-sm">
                        Cari
                    </button>

                    <?php if ($search != '') { ?>

                        <a
                            href="kelola_siswa.php"
                            class="btn btn-outline-secondary btn-sm"
                        >
                            Reset
                        </a>

                    <?php } ?>

                </div>

            </form>

            <!-- TABEL SISWA -->

            <div class="table-responsive">

                <table class="table table-bordered table-hover align-middle">

                    <thead class="table-secondary">

                        <tr>
                            <th width="55">No</th>
                            <th>NIS</th>
                            <th>NISN</th>
                            <th>Nama Siswa</th>
                            <th>JK</th>
                            <th>No HP Orang Tua</th>
                            <th width="150">Aksi</th>
                        </tr>

                    </thead>

                    <tbody>

                    <?php if ($data && $data->num_rows > 0) { ?>

                        <?php $no = $mulai + 1; ?>

                        <?php while ($row = $data->fetch_assoc()) { ?>

                            <tr>

                                <td><?= $no++; ?></td>

                                <td>
                                    <?= htmlspecialchars($row['nis']); ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($row['nisn'] ?? '-'); ?>
                                </td>

                                <td>
                                    <strong>
                                        <?= htmlspecialchars($row['nama']); ?>
                                    </strong>
                                </td>

                                <td>
                                    <?= htmlspecialchars($row['jenis_kelamin'] ?? '-'); ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($row['no_hp_ortu'] ?? '-'); ?>
                                </td>

                                <td>

                                    <a
                                        href="edit_siswa.php?id=<?= (int)$row['id']; ?>"
                                        class="btn btn-sm btn-stabilo"
                                    >
                                        Edit
                                    </a>

                                    <a
                                        href="kelola_siswa.php?hapus=<?= (int)$row['id']; ?>"
                                        class="btn btn-sm btn-danger"
                                        onclick="return confirm('Yakin ingin menghapus siswa ini?')"
                                    >
                                        Hapus
                                    </a>

                                </td>

                            </tr>

                        <?php } ?>

                    <?php } else { ?>

                        <tr>

                            <td colspan="7" class="text-center py-4">
                                Belum ada data siswa.
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
                data siswa.

            </div>

            <!-- PAGINATION -->

            <?php if ($total_halaman > 1) { ?>

                <nav class="mt-3">

                    <ul class="pagination pagination-sm mb-0">

                        <?php if ($halaman > 1) { ?>

                            <li class="page-item">

                                <a
                                    class="page-link"
                                    href="?halaman=<?= $halaman - 1; ?>&cari=<?= urlencode($search); ?>"
                                >
                                    &laquo;
                                </a>

                            </li>

                        <?php } ?>

                        <?php for ($i = 1; $i <= $total_halaman; $i++) { ?>

                            <li class="page-item <?= $i == $halaman ? 'active' : ''; ?>">

                                <a
                                    class="page-link"
                                    href="?halaman=<?= $i; ?>&cari=<?= urlencode($search); ?>"
                                >
                                    <?= $i; ?>
                                </a>

                            </li>

                        <?php } ?>

                        <?php if ($halaman < $total_halaman) { ?>

                            <li class="page-item">

                                <a
                                    class="page-link"
                                    href="?halaman=<?= $halaman + 1; ?>&cari=<?= urlencode($search); ?>"
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

<?php
$stmt_data->close();
?>
