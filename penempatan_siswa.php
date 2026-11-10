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
   TAMBAH PENEMPATAN
========================= */

if (isset($_POST['simpan'])) {

    $tampil_form = true;

    $siswa_id = (int)($_POST['siswa_id'] ?? 0);
    $kelas_id = (int)($_POST['kelas_id'] ?? 0);
    $tahun_ajaran_id = (int)($_POST['tahun_ajaran_id'] ?? 0);

    if ($siswa_id <= 0 || $kelas_id <= 0 || $tahun_ajaran_id <= 0) {

        $error = "Semua data wajib dipilih.";

    } else {

        $cek = mysqli_prepare(
            $koneksi,
            "SELECT id FROM penempatan_siswa
             WHERE siswa_id = ?
             AND kelas_id = ?
             AND tahun_ajaran_id = ?
             LIMIT 1"
        );

        mysqli_stmt_bind_param(
            $cek,
            "iii",
            $siswa_id,
            $kelas_id,
            $tahun_ajaran_id
        );

        mysqli_stmt_execute($cek);
        $hasil_cek = mysqli_stmt_get_result($cek);

        $sudah_ada = mysqli_num_rows($hasil_cek) > 0;

        mysqli_stmt_close($cek);

        if ($sudah_ada) {

            $error = "Penempatan siswa tersebut sudah ada.";

        } else {

            $stmt = mysqli_prepare(
                $koneksi,
                "INSERT INTO penempatan_siswa
                 (siswa_id, kelas_id, tahun_ajaran_id)
                 VALUES (?, ?, ?)"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "iii",
                $siswa_id,
                $kelas_id,
                $tahun_ajaran_id
            );

            if (mysqli_stmt_execute($stmt)) {

                mysqli_stmt_close($stmt);

                header("Location: penempatan_siswa.php");
                exit;

            } else {

                $error = "Gagal menyimpan penempatan: " .
                         mysqli_stmt_error($stmt);

                mysqli_stmt_close($stmt);
            }
        }
    }
}

/* =========================
   HAPUS PENEMPATAN
========================= */

if (isset($_GET['hapus'])) {

    $id = (int)$_GET['hapus'];

    if ($id > 0) {

        $stmt = mysqli_prepare(
            $koneksi,
            "DELETE FROM penempatan_siswa WHERE id = ?"
        );

        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    header("Location: penempatan_siswa.php");
    exit;
}

/* =========================
   DATA DROPDOWN
========================= */

$siswa = mysqli_query(
    $koneksi,
    "SELECT id, nis, nama
     FROM siswa
     WHERE aktif = 1
     ORDER BY nama ASC"
);

$kelas = mysqli_query(
    $koneksi,
    "SELECT id, nama_kelas
     FROM kelas
     ORDER BY nama_kelas ASC"
);

$tahun = mysqli_query(
    $koneksi,
    "SELECT id, nama, aktif
     FROM tahun_ajaran
     ORDER BY tanggal_mulai DESC"
);

/* =========================
   PENCARIAN
========================= */

$cari = trim($_GET['cari'] ?? '');
$cari_sql = "%" . $cari . "%";

/* =========================
   PAGINATION
========================= */

$batas = 10;

$halaman = max(1, (int)($_GET['halaman'] ?? 1));

/* =========================
   HITUNG DATA
========================= */

$stmt_total = mysqli_prepare(
    $koneksi,
    "SELECT COUNT(*) AS total
     FROM penempatan_siswa p
     INNER JOIN siswa s ON p.siswa_id = s.id
     INNER JOIN kelas k ON p.kelas_id = k.id
     INNER JOIN tahun_ajaran t ON p.tahun_ajaran_id = t.id
     WHERE s.nis LIKE ?
        OR s.nama LIKE ?
        OR k.nama_kelas LIKE ?
        OR t.nama LIKE ?"
);

mysqli_stmt_bind_param(
    $stmt_total,
    "ssss",
    $cari_sql,
    $cari_sql,
    $cari_sql,
    $cari_sql
);

mysqli_stmt_execute($stmt_total);

$total_data = (int)mysqli_fetch_assoc(
    mysqli_stmt_get_result($stmt_total)
)['total'];

mysqli_stmt_close($stmt_total);

$total_halaman = max(
    1,
    (int)ceil($total_data / $batas)
);

if ($halaman > $total_halaman) {
    $halaman = $total_halaman;
}

$mulai = ($halaman - 1) * $batas;

/* =========================
   AMBIL DATA PENEMPATAN
========================= */

$stmt_data = mysqli_prepare(
    $koneksi,
    "SELECT
        p.id,
        s.nis,
        s.nama AS nama_siswa,
        k.nama_kelas,
        t.nama AS tahun_ajaran
     FROM penempatan_siswa p
     INNER JOIN siswa s ON p.siswa_id = s.id
     INNER JOIN kelas k ON p.kelas_id = k.id
     INNER JOIN tahun_ajaran t ON p.tahun_ajaran_id = t.id
     WHERE s.nis LIKE ?
        OR s.nama LIKE ?
        OR k.nama_kelas LIKE ?
        OR t.nama LIKE ?
     ORDER BY p.id DESC
     LIMIT ?, ?"
);

mysqli_stmt_bind_param(
    $stmt_data,
    "ssssii",
    $cari_sql,
    $cari_sql,
    $cari_sql,
    $cari_sql,
    $mulai,
    $batas
);

mysqli_stmt_execute($stmt_data);
$data = mysqli_stmt_get_result($stmt_data);

?>

<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Penempatan Siswa</title>

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
    <a href="kelola_tahun_ajaran.php">Tahun Ajaran</a>
    <a href="kelola_wali_kelas.php">Wali Kelas</a>
    <a href="penempatan_siswa.php" class="active">Penempatan Siswa</a>
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

            <h4 class="mb-1">Penempatan Siswa</h4>

            <p class="text-muted mb-0">
                Mengatur siswa ke kelas dan tahun ajaran.
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

                <h5 class="mb-3">Tambah Penempatan Siswa</h5>

                <form method="POST">

                    <div class="row g-3">

                        <div class="col-md-6">

                            <label class="form-label fw-bold">
                                Siswa
                            </label>

                            <select
                                name="siswa_id"
                                class="form-select"
                                required
                            >

                                <option value="">
                                    -- Pilih Siswa --
                                </option>

                                <?php while ($s = mysqli_fetch_assoc($siswa)) { ?>

                                    <option value="<?= (int)$s['id']; ?>">
                                        <?= htmlspecialchars($s['nis'] . " - " . $s['nama']); ?>
                                    </option>

                                <?php } ?>

                            </select>

                        </div>

                        <div class="col-md-6">

                            <label class="form-label fw-bold">
                                Kelas
                            </label>

                            <select
                                name="kelas_id"
                                class="form-select"
                                required
                            >

                                <option value="">
                                    -- Pilih Kelas --
                                </option>

                                <?php while ($k = mysqli_fetch_assoc($kelas)) { ?>

                                    <option value="<?= (int)$k['id']; ?>">
                                        <?= htmlspecialchars($k['nama_kelas']); ?>
                                    </option>

                                <?php } ?>

                            </select>

                        </div>

                        <div class="col-md-6">

                            <label class="form-label fw-bold">
                                Tahun Ajaran
                            </label>

                            <select
                                name="tahun_ajaran_id"
                                class="form-select"
                                required
                            >

                                <option value="">
                                    -- Pilih Tahun Ajaran --
                                </option>

                                <?php while ($t = mysqli_fetch_assoc($tahun)) { ?>

                                    <option value="<?= (int)$t['id']; ?>">
                                        <?= htmlspecialchars($t['nama']); ?>
                                        <?= ((int)$t['aktif'] === 1) ? '(Aktif)' : ''; ?>
                                    </option>

                                <?php } ?>

                            </select>

                        </div>

                    </div>

                    <div class="mt-3">

                        <button
                            type="submit"
                            name="simpan"
                            class="btn btn-stabilo btn-sm"
                        >
                            Simpan Penempatan
                        </button>

                        <a
                            href="penempatan_siswa.php"
                            class="btn btn-outline-secondary btn-sm"
                        >
                            Batal
                        </a>

                    </div>

                </form>

            </div>

        </div>

    <?php } ?>

    <!-- DAFTAR PENEMPATAN -->

    <div class="card">

        <div class="card-body">

            <div class="d-flex justify-content-between align-items-center mb-3">

                <h5 class="mb-0">Daftar Penempatan Siswa</h5>

                <?php if (!$tampil_form) { ?>

                    <a
                        href="penempatan_siswa.php?tambah=1"
                        class="btn btn-sm btn-stabilo"
                    >
                        + Tambah Penempatan
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
                        placeholder="Cari NIS, nama siswa, kelas, atau tahun ajaran..."
                        value="<?= htmlspecialchars($cari); ?>"
                    >

                </div>

                <div class="col-auto">

                    <button type="submit" class="btn btn-secondary btn-sm">
                        Cari
                    </button>

                    <?php if ($cari != '') { ?>

                        <a
                            href="penempatan_siswa.php"
                            class="btn btn-outline-secondary btn-sm"
                        >
                            Reset
                        </a>

                    <?php } ?>

                </div>

            </form>

            <!-- TABEL PENEMPATAN -->

            <div class="table-responsive">

                <table class="table table-bordered table-hover align-middle">

                    <thead class="table-secondary">

                        <tr>
                            <th width="55">No</th>
                            <th>NIS</th>
                            <th>Nama Siswa</th>
                            <th>Kelas</th>
                            <th>Tahun Ajaran</th>
                            <th width="150">Aksi</th>
                        </tr>

                    </thead>

                    <tbody>

                    <?php if ($data && $data->num_rows > 0) { ?>

                        <?php $no = $mulai + 1; ?>

                        <?php while ($row = mysqli_fetch_assoc($data)) { ?>

                            <tr>

                                <td><?= $no++; ?></td>

                                <td>
                                    <?= htmlspecialchars($row['nis']); ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($row['nama_siswa']); ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($row['nama_kelas']); ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($row['tahun_ajaran']); ?>
                                </td>

                                <td class="text-nowrap">

                                    <a
                                        href="edit_penempatan_siswa.php?id=<?= (int)$row['id']; ?>"
                                        class="btn btn-sm btn-stabilo"
                                    >
                                        Edit
                                    </a>

                                    <a
                                        href="penempatan_siswa.php?hapus=<?= (int)$row['id']; ?>"
                                        class="btn btn-sm btn-danger"
                                        onclick="return confirm('Yakin ingin menghapus penempatan ini?')"
                                    >
                                        Hapus
                                    </a>

                                </td>

                            </tr>

                        <?php } ?>

                    <?php } else { ?>

                        <tr>

                            <td colspan="6" class="text-center py-4">
                                Belum ada data penempatan siswa.
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
                <?= min($mulai + $batas, $total_data); ?>
                dari
                <?= $total_data; ?>
                data penempatan siswa.

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

<?php
mysqli_stmt_close($stmt_data);
?>