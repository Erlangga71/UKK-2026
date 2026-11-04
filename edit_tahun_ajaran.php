
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

if (($_SESSION['role'] ?? '') != 'admin') {
    die("Akses ditolak.");
}

/* =========================
   AMBIL ID
========================= */

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    header("Location: kelola_tahun_ajaran.php");
    exit;
}

/* =========================
   AMBIL DATA AWAL
========================= */

$stmt = mysqli_prepare(
    $koneksi,
    "SELECT id, nama, tanggal_mulai, tanggal_selesai, aktif
     FROM tahun_ajaran
     WHERE id = ?
     LIMIT 1"
);

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

if (!$data) {
    die("Data tahun ajaran tidak ditemukan.");
}

$pesan = "";

/* =========================
   PROSES UPDATE
========================= */

if (isset($_POST['update'])) {

    $nama = trim($_POST['nama'] ?? '');
    $tanggal_mulai = $_POST['tanggal_mulai'] ?? '';
    $tanggal_selesai = $_POST['tanggal_selesai'] ?? '';
    $aktif = isset($_POST['aktif']) ? (int)$_POST['aktif'] : 0;

    // Pertahankan input jika validasi gagal
    $data['nama'] = $nama;
    $data['tanggal_mulai'] = $tanggal_mulai;
    $data['tanggal_selesai'] = $tanggal_selesai;
    $data['aktif'] = $aktif;

    if ($nama == '' || $tanggal_mulai == '' || $tanggal_selesai == '') {

        $pesan = "Semua data wajib diisi.";

    } elseif ($tanggal_selesai < $tanggal_mulai) {

        $pesan = "Tanggal selesai tidak boleh sebelum tanggal mulai.";

    } elseif (!in_array($aktif, [0, 1], true)) {

        $pesan = "Status tahun ajaran tidak valid.";

    } else {

        /* CEK NAMA DUPLIKAT */

        $stmt = mysqli_prepare(
            $koneksi,
            "SELECT id
             FROM tahun_ajaran
             WHERE nama = ?
             AND id != ?
             LIMIT 1"
        );

        mysqli_stmt_bind_param($stmt, "si", $nama, $id);
        mysqli_stmt_execute($stmt);

        $cek_nama = mysqli_stmt_get_result($stmt);
        $duplikat = mysqli_num_rows($cek_nama) > 0;

        mysqli_stmt_close($stmt);

        if ($duplikat) {

            $pesan = "Nama tahun ajaran sudah digunakan.";

        } else {

            /* CEK TAHUN AJARAN AKTIF LAIN */

            if ($aktif == 1) {

                $stmt = mysqli_prepare(
                    $koneksi,
                    "SELECT id, nama
                     FROM tahun_ajaran
                     WHERE aktif = 1
                     AND id != ?
                     LIMIT 1"
                );

                mysqli_stmt_bind_param($stmt, "i", $id);
                mysqli_stmt_execute($stmt);

                $cek_aktif = mysqli_stmt_get_result($stmt);
                $tahun_aktif = mysqli_fetch_assoc($cek_aktif);

                mysqli_stmt_close($stmt);

                if ($tahun_aktif) {

                    $pesan = "Tidak bisa mengaktifkan tahun ajaran ini karena tahun ajaran "
                        . $tahun_aktif['nama']
                        . " masih aktif. Nonaktifkan terlebih dahulu tahun ajaran tersebut.";

                }
            }

            /* SIMPAN JIKA TIDAK ADA ERROR */

            if ($pesan == '') {

                $stmt = mysqli_prepare(
                    $koneksi,
                    "UPDATE tahun_ajaran
                     SET nama = ?,
                         tanggal_mulai = ?,
                         tanggal_selesai = ?,
                         aktif = ?
                     WHERE id = ?"
                );

                if (!$stmt) {

                    $pesan = "Gagal menyiapkan query: " . mysqli_error($koneksi);

                } else {

                    mysqli_stmt_bind_param(
                        $stmt,
                        "sssii",
                        $nama,
                        $tanggal_mulai,
                        $tanggal_selesai,
                        $aktif,
                        $id
                    );

                    if (mysqli_stmt_execute($stmt)) {

                        mysqli_stmt_close($stmt);

                        header("Location: kelola_tahun_ajaran.php");
                        exit;

                    } else {

                        $pesan = "Gagal menyimpan perubahan: "
                            . mysqli_stmt_error($stmt);

                        mysqli_stmt_close($stmt);
                    }
                }
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Edit Tahun Ajaran</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body {
    margin: 0;
    background: #f4f6f8;
    font-family: Arial, sans-serif;
}

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

.content {
    margin-left: 220px;
    padding: 30px;
}

.header-box {
    background: white;
    border: 1px solid #ddd;
    border-radius: 7px;
    padding: 22px;
    margin-bottom: 20px;
}

.header-box h1 {
    font-size: 24px;
    margin: 0;
}

.header-box p {
    color: #777;
    margin: 8px 0 0;
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
    <a href="kelola_kelas.php">Data Kelas</a>
    <a href="kelola_tahun_ajaran.php" class="active">Tahun Ajaran</a>
    <a href="kelola_wali_kelas.php">Wali Kelas</a>
    <a href="penempatan_siswa.php">Penempatan Siswa</a>
    <a href="kelola_pelanggaran_kategori.php">⚠ Kategori Pelanggaran</a>
    <a href="pelanggaran.php">Data Pelanggaran</a>
    <a href="tindakan.php">Data Tindakan</a>
    <a href="laporan.php">Laporan</a>
    <a href="about_me.php">ℹ About Me</a>

    <a href="logout.php" class="logout">🚪 Logout</a>

</div>


<!-- CONTENT -->

<div class="content">

    <!-- HEADER -->

    <div class="header-box">

        <h1>Tahun Ajaran</h1>

        <p>Kelola data tahun ajaran sekolah.</p>

    </div>


    <!-- FORM EDIT -->

    <div class="card shadow-sm">

        <div class="card-body">

            <h5 class="mb-3">Edit Tahun Ajaran</h5>

            <p class="text-secondary mb-4">
                Ubah data tahun ajaran di bawah ini.
            </p>


            <!-- PESAN -->

            <?php if ($pesan != '') { ?>

                <div class="alert alert-danger">
                    <?= htmlspecialchars($pesan); ?>
                </div>

            <?php } ?>


            <!-- STATUS SAAT INI -->

            <div class="mb-3">

                <label class="form-label fw-bold">
                    Status Tahun Ajaran Saat Ini
                </label>

                <div>

                    <?php if ($data['aktif'] == 1) { ?>

                        <span class="badge bg-success">
                            Aktif
                        </span>

                    <?php } else { ?>

                        <span class="badge bg-danger">
                            Tidak Aktif
                        </span>

                    <?php } ?>

                </div>

            </div>


            <!-- FORM -->

            <form method="POST">

                <div class="row g-3">

                    <div class="col-md-12">

                        <label class="form-label">
                            Nama Tahun Ajaran
                        </label>

                        <input
                            type="text"
                            name="nama"
                            class="form-control"
                            value="<?= htmlspecialchars($data['nama']); ?>"
                            placeholder="Contoh: 2026/2027"
                            required
                        >

                    </div>


                    <div class="col-md-6">

                        <label class="form-label">
                            Tanggal Mulai
                        </label>

                        <input
                            type="date"
                            name="tanggal_mulai"
                            class="form-control"
                            value="<?= htmlspecialchars($data['tanggal_mulai']); ?>"
                            required
                        >

                    </div>


                    <div class="col-md-6">

                        <label class="form-label">
                            Tanggal Selesai
                        </label>

                        <input
                            type="date"
                            name="tanggal_selesai"
                            class="form-control"
                            value="<?= htmlspecialchars($data['tanggal_selesai']); ?>"
                            required
                        >

                    </div>


                    <div class="col-md-12">

                        <label class="form-label">
                            Status Tahun Ajaran
                        </label>

                        <select name="aktif" class="form-select">

                            <option
                                value="1"
                                <?= $data['aktif'] == 1 ? 'selected' : ''; ?>
                            >
                                Aktif
                            </option>

                            <option
                                value="0"
                                <?= $data['aktif'] == 0 ? 'selected' : ''; ?>
                            >
                                Tidak Aktif
                            </option>

                        </select>

                    </div>

                </div>


                <div class="mt-4">

                    <button
                        type="submit"
                        name="update"
                        class="btn btn-primary"
                    >
                        Simpan Perubahan
                    </button>

                    <a
                        href="kelola_tahun_ajaran.php"
                        class="btn btn-secondary"
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
