
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "database.php";
require_once "auth.php";

hanyaRole(['admin']);

$tambah = isset($_GET['tambah']) && $_GET['tambah'] == '1';
$error = "";

/* =========================
   CSRF TOKEN
========================= */

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/* =========================
   TAMBAH DATA KELAS
========================= */

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan'])) {

    if (
        !isset($_POST['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
    ) {
        $error = "Permintaan tidak valid. Muat ulang halaman.";
        $tambah = true;

    } else {

        $nama_kelas = trim($_POST['nama_kelas'] ?? '');

        if ($nama_kelas === '') {

            $error = "Nama kelas wajib diisi.";
            $tambah = true;

        } else {

            $stmt = mysqli_prepare(
                $koneksi,
                "SELECT id FROM kelas WHERE nama_kelas = ? LIMIT 1"
            );

            mysqli_stmt_bind_param($stmt, "s", $nama_kelas);
            mysqli_stmt_execute($stmt);

            $cek = mysqli_stmt_get_result($stmt);
            $ada = mysqli_num_rows($cek) > 0;

            mysqli_stmt_close($stmt);

            if ($ada) {

                $error = "Nama kelas tersebut sudah ada.";
                $tambah = true;

            } else {

                $stmt = mysqli_prepare(
                    $koneksi,
                    "INSERT INTO kelas (nama_kelas) VALUES (?)"
                );

                mysqli_stmt_bind_param($stmt, "s", $nama_kelas);

                if (mysqli_stmt_execute($stmt)) {

                    mysqli_stmt_close($stmt);

                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

                    header("Location: kelola_kelas.php");
                    exit;

                } else {

                    $error = "Gagal menambahkan kelas: " . mysqli_stmt_error($stmt);
                    $tambah = true;

                    mysqli_stmt_close($stmt);
                }
            }
        }
    }
}

/* =========================
   HAPUS DATA KELAS
========================= */

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['hapus'])) {

    if (
        !isset($_POST['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
    ) {
        $error = "Permintaan tidak valid. Muat ulang halaman.";

    } else {

        $id = (int) $_POST['hapus'];

        if ($id > 0) {

            $stmt = mysqli_prepare(
                $koneksi,
                "SELECT
                    (SELECT COUNT(*) FROM penempatan_siswa WHERE kelas_id = ?) +
                    (SELECT COUNT(*) FROM wali_kelas WHERE kelas_id = ?) AS total"
            );

            mysqli_stmt_bind_param($stmt, "ii", $id, $id);
            mysqli_stmt_execute($stmt);

            $hasil = mysqli_stmt_get_result($stmt);
            $jumlah = (int) mysqli_fetch_assoc($hasil)['total'];

            mysqli_stmt_close($stmt);

            if ($jumlah > 0) {

                $error = "Kelas tidak bisa dihapus karena masih digunakan.";

            } else {

                $stmt = mysqli_prepare(
                    $koneksi,
                    "DELETE FROM kelas WHERE id = ?"
                );

                mysqli_stmt_bind_param($stmt, "i", $id);

                if (mysqli_stmt_execute($stmt)) {

                    mysqli_stmt_close($stmt);

                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

                    header("Location: kelola_kelas.php");
                    exit;

                } else {

                    $error = "Gagal menghapus kelas: " . mysqli_stmt_error($stmt);

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

/* =========================
   PAGINATION
========================= */

$batas = 10;
$halaman = max(1, (int) ($_GET['halaman'] ?? 1));
$mulai = ($halaman - 1) * $batas;

/* =========================
   HITUNG DATA
========================= */

if ($cari !== '') {

    $keyword = "%" . $cari . "%";

    $stmt_total = mysqli_prepare(
        $koneksi,
        "SELECT COUNT(*) AS total
         FROM kelas
         WHERE nama_kelas LIKE ?"
    );

    mysqli_stmt_bind_param($stmt_total, "s", $keyword);
    mysqli_stmt_execute($stmt_total);

    $hasil_total = mysqli_stmt_get_result($stmt_total);
    $total_data = (int) mysqli_fetch_assoc($hasil_total)['total'];

    mysqli_stmt_close($stmt_total);

} else {

    $hasil_total = mysqli_query(
        $koneksi,
        "SELECT COUNT(*) AS total FROM kelas"
    );

    $total_data = (int) mysqli_fetch_assoc($hasil_total)['total'];
}

$total_halaman = max(1, (int) ceil($total_data / $batas));

if ($halaman > $total_halaman) {
    $halaman = $total_halaman;
}

$mulai = ($halaman - 1) * $batas;

/* =========================
   AMBIL DATA KELAS
========================= */

if ($cari !== '') {

    $stmt_data = mysqli_prepare(
        $koneksi,
        "SELECT id, nama_kelas
         FROM kelas
         WHERE nama_kelas LIKE ?
         ORDER BY id DESC
         LIMIT ? OFFSET ?"
    );

    mysqli_stmt_bind_param(
        $stmt_data,
        "sii",
        $keyword,
        $batas,
        $mulai
    );

} else {

    $stmt_data = mysqli_prepare(
        $koneksi,
        "SELECT id, nama_kelas
         FROM kelas
         ORDER BY id DESC
         LIMIT ? OFFSET ?"
    );

    mysqli_stmt_bind_param(
        $stmt_data,
        "ii",
        $batas,
        $mulai
    );
}

mysqli_stmt_execute($stmt_data);
$data = mysqli_stmt_get_result($stmt_data);

/* =========================
   URL PAGINATION
========================= */

function urlHalamanKelas($halaman, $cari) {
    return "kelola_kelas.php?halaman=" . $halaman .
           "&cari=" . urlencode($cari);
}
?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Data Kelas</title>

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

        /* KONTEN */

        .content {
            margin-left: 220px;
            padding: 30px;
        }

        .card {
            border: 1px solid #dddddd;
            border-radius: 6px;
            margin-bottom: 15px;
        }

        .card-body {
            padding: 16px;
        }

        /* TOMBOL */

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

        /* TABEL */

        .table {
            font-size: 12px;
            margin-bottom: 0;
        }

        .table thead th {
            background: #dedede;
            color: #111;
            font-weight: bold;
            border-bottom: 1px solid #bbb;
        }

        .table td,
        .table th {
            padding: 8px;
            vertical-align: middle;
        }

        .table tbody tr:hover {
            background: #f8f9fa;
        }

        /* PAGINATION */

        .pagination .page-link {
            color: #4f5965;
        }

        .pagination .active .page-link {
            background: #687280;
            border-color: #687280;
            color: white;
        }

        /* LAYAR KECIL */

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
        }

    </style>

</head>

<body>

<!-- SIDEBAR -->

<div class="sidebar">

    <div class="sidebar-title">
        ⚖ Pelanggaran<br>Siswa
    </div>

    <a href="dashboard.php">🏠 Dashboard</a>
    <a href="kelola_siswa.php">Data Siswa</a>
    <a href="kelola_guru.php">Data Guru</a>
    <a href="kelola_kelas.php" class="active">Data Kelas</a>
    <a href="kelola_tahun_ajaran.php">Tahun Ajaran</a>
    <a href="kelola_wali_kelas.php">Wali Kelas</a>
    <a href="penempatan_siswa.php">Penempatan Siswa</a>
    <a href="kelola_pelanggaran_kategori.php">⚠ Kategori Pelanggaran</a>
    <a href="pelanggaran.php">Data Pelanggaran</a>
    <a href="tindakan.php">Data Tindakan</a>
    <a href="laporan.php">Laporan</a>
    <a href="about_me.php">ℹ About Me</a>

    <a href="logout.php" class="logout">🚪 Logout</a>

</div>

<!-- KONTEN -->

<div class="content">

    <div class="card">

        <div class="card-body">

            <h1 class="h4 mb-1">Data Kelas</h1>

            <p class="text-muted mb-0">
                Kelola data kelas dalam sistem.
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

    <?php if ($tambah) { ?>

        <div class="card">

            <div class="card-body">

                <h2 class="h5 mb-3">Tambah Data Kelas</h2>

                <form method="POST">

                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= htmlspecialchars($_SESSION['csrf_token']); ?>"
                    >

                    <div class="row g-3">

                        <div class="col-md-6">

                            <label class="form-label fw-bold">
                                Nama Kelas
                            </label>

                            <input
                                type="text"
                                name="nama_kelas"
                                class="form-control"
                                placeholder="Contoh: XII RPL 1"
                                value="<?= htmlspecialchars($_POST['nama_kelas'] ?? ''); ?>"
                                required
                            >

                        </div>

                    </div>

                    <div class="mt-3">

                        <button type="submit" name="simpan" class="btn btn-secondary">
                            Simpan Kelas
                        </button>

                        <a href="kelola_kelas.php" class="btn btn-outline-secondary">
                            Batal
                        </a>

                    </div>

                </form>

            </div>

        </div>

    <?php } ?>

    <!-- DAFTAR KELAS -->

    <div class="card">

        <div class="card-body">

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">

                <h2 class="h5 mb-0">Daftar Kelas</h2>

                <?php if (!$tambah) { ?>

                    <a href="kelola_kelas.php?tambah=1" class="btn btn-sm btn-stabilo">
                        + Tambah Kelas
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
                        placeholder="Cari nama kelas..."
                        value="<?= htmlspecialchars($cari); ?>"
                    >

                </div>

                <div class="col-auto">

                    <button type="submit" class="btn btn-sm btn-secondary">
                        Cari
                    </button>

                    <a href="kelola_kelas.php" class="btn btn-sm btn-outline-secondary">
                        Reset
                    </a>

                </div>

            </form>

            <!-- TABEL KELAS -->

            <div class="table-responsive">

                <table class="table table-bordered table-hover align-middle">

                    <thead>

                        <tr>
                            <th width="10%">No</th>
                            <th width="65%">Nama Kelas</th>
                            <th width="25%">Aksi</th>
                        </tr>

                    </thead>

                    <tbody>

                    <?php
                    $no = $mulai + 1;

                    if ($data && mysqli_num_rows($data) > 0) {

                        while ($row = mysqli_fetch_assoc($data)) {
                    ?>

                        <tr>

                            <td><?= $no++; ?></td>

                            <td>
                                <b><?= htmlspecialchars($row['nama_kelas']); ?></b>
                            </td>

                            <td class="text-nowrap">

                                <a
                                    class="btn btn-sm btn-stabilo"
                                    href="edit_kelas.php?id=<?= (int)$row['id']; ?>"
                                >Edit</a>

                                <form
                                    method="POST"
                                    style="display:inline;"
                                    onsubmit="return confirm('Yakin ingin menghapus kelas ini?')"
                                >

                                    <input
                                        type="hidden"
                                        name="csrf_token"
                                        value="<?= htmlspecialchars($_SESSION['csrf_token']); ?>"
                                    >

                                    <button
                                        type="submit"
                                        name="hapus"
                                        value="<?= (int)$row['id']; ?>"
                                        class="btn btn-sm btn-danger"
                                    >
                                        Hapus
                                    </button>

                                </form>

                            </td>

                        </tr>

                    <?php
                        }

                    } else {
                    ?>

                        <tr>

                            <td colspan="3" class="text-center py-4">
                                Belum ada data kelas.
                            </td>

                        </tr>

                    <?php } ?>

                    </tbody>

                </table>

            </div>

            <!-- INFORMASI DATA -->

            <div class="d-flex justify-content-between align-items-center flex-wrap mt-3">

                <small class="text-muted">

                    Menampilkan <?= $total_data > 0 ? $mulai + 1 : 0; ?>
                    sampai <?= min($mulai + $batas, $total_data); ?>
                    dari <?= $total_data; ?> data kelas

                </small>

                <!-- PAGINATION -->

                <?php if ($total_halaman > 1) { ?>

                    <nav>

                        <ul class="pagination pagination-sm mb-0">

                            <?php if ($halaman > 1) { ?>

                                <li class="page-item">

                                    <a
                                        class="page-link"
                                        href="<?= urlHalamanKelas($halaman - 1, $cari); ?>"
                                    >
                                        Sebelumnya
                                    </a>

                                </li>

                            <?php } ?>

                            <?php for ($i = 1; $i <= $total_halaman; $i++) { ?>

                                <li class="page-item <?= ($i == $halaman) ? 'active' : ''; ?>">

                                    <a
                                        class="page-link"
                                        href="<?= urlHalamanKelas($i, $cari); ?>"
                                    >
                                        <?= $i; ?>
                                    </a>

                                </li>

                            <?php } ?>

                            <?php if ($halaman < $total_halaman) { ?>

                                <li class="page-item">

                                    <a
                                        class="page-link"
                                        href="<?= urlHalamanKelas($halaman + 1, $cari); ?>"
                                    >
                                        Berikutnya
                                    </a>

                                </li>

                            <?php } ?>

                        </ul>

                    </nav>

                <?php } ?>

            </div>

        </div>

    </div>

</div>

</body>
</html>
