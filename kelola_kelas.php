
<?php
session_start();

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

include "database.php";

$role = $_SESSION['role'] ?? '';

if ($role != 'admin') {
    die("Akses ditolak.");
}

$error = "";
$tampil_form = isset($_GET['tambah']);

/* =========================
   TAMBAH KELAS
========================= */

if (isset($_POST['simpan'])) {

    $nama_kelas = trim($_POST['nama_kelas'] ?? '');

    if ($nama_kelas == '') {

        $error = "Nama kelas wajib diisi.";
        $tampil_form = true;

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
            $tampil_form = true;

        } else {

            $stmt = mysqli_prepare(
                $koneksi,
                "INSERT INTO kelas (nama_kelas) VALUES (?)"
            );

            mysqli_stmt_bind_param($stmt, "s", $nama_kelas);

            if (mysqli_stmt_execute($stmt)) {

                mysqli_stmt_close($stmt);

                header("Location: kelola_kelas.php");
                exit;

            } else {

                $error = "Gagal menambahkan kelas: " . mysqli_error($koneksi);
                $tampil_form = true;
                mysqli_stmt_close($stmt);
            }
        }
    }
}


/* =========================
   HAPUS KELAS
========================= */

if (isset($_GET['hapus'])) {

    $id = (int)$_GET['hapus'];

    if ($id > 0) {

        // Cek apakah kelas masih digunakan
        $stmt = mysqli_prepare(
            $koneksi,
            "SELECT
                (SELECT COUNT(*) FROM penempatan_siswa WHERE kelas_id = ?) +
                (SELECT COUNT(*) FROM wali_kelas WHERE kelas_id = ?) AS total"
        );

        mysqli_stmt_bind_param($stmt, "ii", $id, $id);
        mysqli_stmt_execute($stmt);

        $hasil = mysqli_stmt_get_result($stmt);
        $jumlah = (int)mysqli_fetch_assoc($hasil)['total'];

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
                header("Location: kelola_kelas.php");
                exit;
            } else {
                $error = "Gagal menghapus kelas: " . mysqli_error($koneksi);
                mysqli_stmt_close($stmt);
            }
        }
    }
}


/* =========================
   PENCARIAN
========================= */

$search = trim($_GET['search'] ?? '');
$cari = "%$search%";


/* =========================
   PAGINATION
========================= */

$per_halaman = 5;
$halaman = max(1, (int)($_GET['halaman'] ?? 1));


/* =========================
   HITUNG DATA
========================= */

if ($search != '') {

    $stmt = $koneksi->prepare(
        "SELECT COUNT(*) AS total
         FROM kelas
         WHERE nama_kelas LIKE ?"
    );

    $stmt->bind_param("s", $cari);

} else {

    $stmt = $koneksi->prepare(
        "SELECT COUNT(*) AS total FROM kelas"
    );
}

$stmt->execute();
$total_data = (int)$stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

$total_halaman = max(1, (int)ceil($total_data / $per_halaman));

if ($halaman > $total_halaman) {
    $halaman = $total_halaman;
}

$mulai = ($halaman - 1) * $per_halaman;


/* =========================
   AMBIL DATA KELAS
========================= */

if ($search != '') {

    $stmt = $koneksi->prepare(
        "SELECT id, nama_kelas
         FROM kelas
         WHERE nama_kelas LIKE ?
         ORDER BY nama_kelas ASC
         LIMIT ?, ?"
    );

    $stmt->bind_param("sii", $cari, $mulai, $per_halaman);

} else {

    $stmt = $koneksi->prepare(
        "SELECT id, nama_kelas
         FROM kelas
         ORDER BY nama_kelas ASC
         LIMIT ?, ?"
    );

    $stmt->bind_param("ii", $mulai, $per_halaman);
}

$stmt->execute();
$data = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Data Kelas</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body {
    background: #f4f6f8;
}

.sidebar {
    width: 220px;
    background: #687280;
}

.sidebar .nav-link {
    color: white;
    font-size: 14px;
}

.sidebar .nav-link:hover,
.sidebar .nav-link.active {
    background: #4f5965;
}

.content {
    margin-left: 220px;
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

@media (max-width: 700px) {
    .sidebar {
        position: static !important;
        width: 100%;
        height: auto !important;
    }

    .content {
        margin-left: 0;
    }
}

</style>

</head>

<body>

<!-- SIDEBAR -->

<div class="sidebar position-fixed top-0 start-0 vh-100 overflow-auto p-3">

    <h5 class="text-white fw-bold mb-4 px-2">
        ⚖ Pelanggaran<br>Siswa
    </h5>

    <ul class="nav flex-column gap-1">

        <li><a class="nav-link" href="dashboard.php">🏠 Dashboard</a></li>
        <li><a class="nav-link" href="kelola_siswa.php">Data Siswa</a></li>
        <li><a class="nav-link" href="kelola_guru.php">Data Guru</a></li>
        <li><a class="nav-link active" href="kelola_kelas.php">Data Kelas</a></li>
        <li><a class="nav-link" href="kelola_tahun_ajaran.php">Tahun Ajaran</a></li>
        <li><a class="nav-link" href="kelola_wali_kelas.php">Wali Kelas</a></li>
        <li><a class="nav-link" href="penempatan_siswa.php">Penempatan Siswa</a></li>
        <li><a class="nav-link" href="kelola_pelanggaran_kategori.php">⚠ Kategori Pelanggaran</a></li>
        <li><a class="nav-link" href="pelanggaran.php">Data Pelanggaran</a></li>
        <li><a class="nav-link" href="tindakan.php">Data Tindakan</a></li>
        <li><a class="nav-link" href="laporan.php">Laporan</a></li>
        <li><a class="nav-link" href="about_me.php">ℹ About Me</a></li>

    </ul>

    <a href="logout.php" class="btn btn-danger w-100 mt-4 text-start">
        🚪 Logout
    </a>

</div>


<!-- CONTENT -->

<div class="content p-4">

    <!-- HEADER -->

    <div class="card mb-3">

        <div class="card-body">

            <h1 class="h3 mb-1">Data Kelas</h1>

            <p class="text-muted mb-0">
                Kelola data kelas dalam sistem.
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

                <h2 class="h5 mb-3">Tambah Data Kelas</h2>

                <form method="POST">

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

            <div class="d-flex justify-content-between align-items-center mb-3">

                <h2 class="h5 mb-0">Daftar Kelas</h2>

                <?php if (!$tampil_form) { ?>

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
                        name="search"
                        class="form-control"
                        placeholder="Cari nama kelas..."
                        value="<?= htmlspecialchars($search); ?>"
                    >

                </div>

                <div class="col-auto">

                    <button type="submit" class="btn btn-secondary">
                        Cari
                    </button>

                    <?php if ($search != '') { ?>

                        <a href="kelola_kelas.php" class="btn btn-outline-secondary">
                            Reset
                        </a>

                    <?php } ?>

                </div>

            </form>


            <!-- TABEL KELAS -->

            <div class="table-responsive">

                <table class="table table-hover align-middle">

                    <thead class="table-secondary">

                        <tr>
                            <th>No</th>
                            <th>Nama Kelas</th>
                            <th>Aksi</th>
                        </tr>

                    </thead>

                    <tbody>

                    <?php if ($data && $data->num_rows > 0) { ?>

                        <?php $no = $mulai + 1; ?>

                        <?php while ($row = $data->fetch_assoc()) { ?>

                            <tr>

                                <td><?= $no++; ?></td>

                                <td>
                                    <b><?= htmlspecialchars($row['nama_kelas']); ?></b>
                                </td>

                                <td class="text-nowrap">

                                    <a
                                        class="btn btn-sm btn-stabilo"
                                        href="edit_kelas.php?id=<?= (int)$row['id']; ?>"
                                    >
                                        Edit
                                    </a>

                                    <a
                                        class="btn btn-sm btn-danger"
                                        href="kelola_kelas.php?hapus=<?= (int)$row['id']; ?>"
                                        onclick="return confirm('Yakin ingin menghapus kelas ini?')"
                                    >
                                        Hapus
                                    </a>

                                </td>

                            </tr>

                        <?php } ?>

                    <?php } else { ?>

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

            <div class="text-muted small">

                Menampilkan
                <?= $total_data > 0 ? $mulai + 1 : 0; ?>
                -
                <?= min($mulai + $per_halaman, $total_data); ?>
                dari
                <?= $total_data; ?>
                data kelas.

            </div>


            <!-- PAGINATION -->

            <?php if ($total_halaman > 1) { ?>

                <nav class="mt-3">

                    <ul class="pagination mb-0">

                        <?php if ($halaman > 1) { ?>

                            <li class="page-item">

                                <a class="page-link"
                                   href="?halaman=<?= $halaman - 1; ?>&search=<?= urlencode($search); ?>">
                                    &laquo;
                                </a>

                            </li>

                        <?php } ?>


                        <?php for ($i = 1; $i <= $total_halaman; $i++) { ?>

                            <li class="page-item <?= $i == $halaman ? 'active' : ''; ?>">

                                <a class="page-link"
                                   href="?halaman=<?= $i; ?>&search=<?= urlencode($search); ?>">
                                    <?= $i; ?>
                                </a>

                            </li>

                        <?php } ?>


                        <?php if ($halaman < $total_halaman) { ?>

                            <li class="page-item">

                                <a class="page-link"
                                   href="?halaman=<?= $halaman + 1; ?>&search=<?= urlencode($search); ?>">
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
