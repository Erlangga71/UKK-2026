
<?php

session_start();
require_once "database.php";

/* =========================
   CEK LOGIN
========================= */

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

/* =========================
   CEK ROLE ADMIN
========================= */

$role = $_SESSION['role'] ?? '';

if ($role != 'admin') {
    die("Akses ditolak.");
}

/* =========================
   AMBIL ID KELAS
========================= */

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    die("ID kelas tidak valid.");
}

/* =========================
   AMBIL DATA KELAS
========================= */

$stmt = mysqli_prepare(
    $koneksi,
    "SELECT id, nama_kelas FROM kelas WHERE id = ? LIMIT 1"
);

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

if (!$data) {
    die("Data kelas tidak ditemukan.");
}

$pesan = "";

/* =========================
   PROSES UPDATE
========================= */

if (isset($_POST['simpan'])) {

    $nama_kelas = trim($_POST['nama_kelas'] ?? '');

    if ($nama_kelas == '') {

        $pesan = "Nama kelas wajib diisi.";

    } else {

        /* CEK NAMA KELAS DUPLIKAT */

        $stmt = mysqli_prepare(
            $koneksi,
            "SELECT id FROM kelas
             WHERE nama_kelas = ?
             AND id != ?
             LIMIT 1"
        );

        mysqli_stmt_bind_param($stmt, "si", $nama_kelas, $id);
        mysqli_stmt_execute($stmt);

        $cek = mysqli_stmt_get_result($stmt);
        $ada = mysqli_num_rows($cek) > 0;

        mysqli_stmt_close($stmt);

        if ($ada) {

            $pesan = "Nama kelas tersebut sudah digunakan.";

        } else {

            /* UPDATE DATA KELAS */

            $stmt = mysqli_prepare(
                $koneksi,
                "UPDATE kelas
                 SET nama_kelas = ?
                 WHERE id = ?"
            );

            mysqli_stmt_bind_param($stmt, "si", $nama_kelas, $id);

            if (mysqli_stmt_execute($stmt)) {

                mysqli_stmt_close($stmt);

                header("Location: kelola_kelas.php");
                exit;

            } else {

                $pesan = "Gagal mengubah data kelas: " . mysqli_error($koneksi);

                mysqli_stmt_close($stmt);
            }
        }
    }

    /* TAMPILKAN KEMBALI INPUT JIKA ADA ERROR */

    $data['nama_kelas'] = $nama_kelas;
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Edit Data Kelas</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body {
    background: #f4f6f8;
    font-family: Arial, sans-serif;
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

        <li>
            <a class="nav-link" href="dashboard.php">
                 Dashboard
            </a>
        </li>

        <li>
            <a class="nav-link" href="kelola_siswa.php">
                Data Siswa
            </a>
        </li>

        <li>
            <a class="nav-link" href="kelola_guru.php">
                Data Guru
            </a>
        </li>

        <li>
            <a class="nav-link active" href="kelola_kelas.php">
                Data Kelas
            </a>
        </li>

        <li>
            <a class="nav-link" href="kelola_tahun_ajaran.php">
                Tahun Ajaran
            </a>
        </li>

        <li>
            <a class="nav-link" href="kelola_wali_kelas.php">
                Wali Kelas
            </a>
        </li>

        <li>
            <a class="nav-link" href="penempatan_siswa.php">
                Penempatan Siswa
            </a>
        </li>

        <li>
            <a class="nav-link" href="kelola_pelanggaran_kategori.php">
                 Kategori Pelanggaran
            </a>
        </li>

        <li>
            <a class="nav-link" href="pelanggaran.php">
                Data Pelanggaran
            </a>
        </li>

        <li>
            <a class="nav-link" href="tindakan.php">
                Data Tindakan
            </a>
        </li>

        <li>
            <a class="nav-link" href="laporan.php">
                Laporan
            </a>
        </li>

        <li>
            <a class="nav-link" href="about_me.php">
                ℹ About Me
            </a>
        </li>

    </ul>

    <a href="logout.php" class="btn btn-danger w-100 mt-4 text-start">
         Logout
    </a>

</div>


<!-- CONTENT -->

<div class="content p-4">

    <!-- HEADER -->

    <div class="card mb-3">

        <div class="card-body">

            <h1 class="h3 mb-1">
                Data Kelas
            </h1>

            <p class="text-muted mb-0">
                Kelola data kelas dalam sistem.
            </p>

        </div>

    </div>


    <!-- FORM EDIT -->

    <div class="card">

        <div class="card-body">

            <h2 class="h5 mb-3">
                Edit Data Kelas
            </h2>

            <p class="text-muted">
                Ubah nama kelas yang terdaftar dalam sistem.
            </p>


            <!-- PESAN ERROR -->

            <?php if ($pesan != '') { ?>

                <div class="alert alert-danger">
                    <?= htmlspecialchars($pesan); ?>
                </div>

            <?php } ?>


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
                            value="<?= htmlspecialchars($data['nama_kelas']); ?>"
                            placeholder="Contoh: XII RPL 1"
                            required
                        >

                    </div>

                </div>


                <div class="mt-3">

                    <button
                        type="submit"
                        name="simpan"
                        class="btn btn-secondary"
                    >
                        Simpan Perubahan
                    </button>

                    <a
                        href="kelola_kelas.php"
                        class="btn btn-outline-secondary"
                    >
                        Batal
                    </a>

                </div>

            </form>

        </div>

    </div>

</div>

</body>
</html>
