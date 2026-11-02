
<?php

session_start();

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

include "database.php";

$role = $_SESSION['role'] ?? '';

if ($role != 'admin') {
    echo "Akses ditolak.";
    exit;
}

$error = "";

/* =========================
   TAMBAH TAHUN AJARAN
========================= */

if (isset($_POST['simpan'])) {

    $nama = trim($_POST['nama'] ?? '');
    $tanggal_mulai = $_POST['tanggal_mulai'] ?? '';
    $tanggal_selesai = $_POST['tanggal_selesai'] ?? '';
    $aktif = isset($_POST['aktif']) ? 1 : 0;

    if ($nama == '' || $tanggal_mulai == '' || $tanggal_selesai == '') {

        $error = "Semua data wajib diisi.";

    } elseif ($tanggal_selesai < $tanggal_mulai) {

        $error = "Tanggal selesai tidak boleh sebelum tanggal mulai.";

    } else {

        mysqli_begin_transaction($koneksi);

        try {

            // Nonaktifkan tahun ajaran lainnya
            if ($aktif == 1) {

                $nonaktif = mysqli_query(
                    $koneksi,
                    "UPDATE tahun_ajaran SET aktif = 0"
                );

                if (!$nonaktif) {
                    throw new Exception(mysqli_error($koneksi));
                }
            }

            // Simpan tahun ajaran
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
            $error = "Gagal menambahkan tahun ajaran: " . $e->getMessage();
        }
    }
}

/* =========================
   HAPUS TAHUN AJARAN
========================= */

if (isset($_GET['hapus'])) {

    $id = intval($_GET['hapus']);

    if ($id > 0) {

        $stmt = mysqli_prepare(
            $koneksi,
            "DELETE FROM tahun_ajaran WHERE id = ?"
        );

        if ($stmt) {

            mysqli_stmt_bind_param($stmt, "i", $id);

            if (!mysqli_stmt_execute($stmt)) {
                $error = "Gagal menghapus tahun ajaran: " . mysqli_stmt_error($stmt);
            }

            mysqli_stmt_close($stmt);

        } else {
            $error = "Query hapus gagal: " . mysqli_error($koneksi);
        }
    }

    if ($error == "") {
        header("Location: kelola_tahun_ajaran.php");
        exit;
    }
}

/* =========================
   AMBIL DATA
========================= */

$data = mysqli_query(
    $koneksi,
    "SELECT id, nama, tanggal_mulai, tanggal_selesai, aktif
     FROM tahun_ajaran
     ORDER BY id DESC"
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

<!-- BOOTSTRAP 5 -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

/* CSS SENDIRI */

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
        ⚖ Pelanggaran<br>
        Siswa
    </div>

    <a href="dashboard.php">🏠 Dashboard</a>

    <a href="kelola_siswa.php">Data Siswa</a>

    <a href="kelola_guru.php">Data Guru</a>

    <a href="kelola_kelas.php">Data Kelas</a>

    <a href="kelola_tahun_ajaran.php" class="active">
        Tahun Ajaran
    </a>

    <a href="kelola_wali_kelas.php">Wali Kelas</a>

    <a href="penempatan_siswa.php">Penempatan Siswa</a>

    <a href="kelola_pelanggaran_kategori.php">
        ⚠ Kategori Pelanggaran
    </a>

    <a href="pelanggaran.php">Data Pelanggaran</a>

    <a href="tindakan.php">Data Tindakan</a>

    <a href="laporan.php">Laporan</a>

    <a href="about_me.php">ℹ About Me</a>

    <a href="logout.php" class="logout">
        🚪 Logout
    </a>

</div>

<!-- CONTENT -->

<div class="content">

    <div class="header-box">

        <h1>Tahun Ajaran</h1>

        <p>Kelola data tahun ajaran sekolah.</p>

    </div>

    <!-- PESAN ERROR -->

    <?php if ($error != "") { ?>

        <div class="alert alert-danger">
            <?= htmlspecialchars($error); ?>
        </div>

    <?php } ?>

    <!-- DAFTAR TAHUN AJARAN -->

    <div class="card shadow-sm mb-4">

        <div class="card-body">

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">

                <h5 class="mb-0">Daftar Tahun Ajaran</h5>

                <button
                    type="button"
                    class="btn btn-success"
                    onclick="tampilkanForm()"
                >
                    + Tambah Tahun Ajaran
                </button>

            </div>

            <div class="table-responsive">

                <table class="table table-bordered table-striped table-hover align-middle">

                    <thead class="table-dark">

                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Tanggal Mulai</th>
                            <th>Tanggal Selesai</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>

                    </thead>

                    <tbody>

                    <?php

                    $no = 1;

                    if (mysqli_num_rows($data) > 0) {

                        while ($row = mysqli_fetch_assoc($data)) {

                    ?>

                        <tr>

                            <td><?= $no++; ?></td>

                            <td>
                                <b><?= htmlspecialchars($row['nama']); ?></b>
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
                                    href="edit_tahun_ajaran.php?id=<?= $row['id']; ?>"
                                    class="btn btn-sm btn-primary"
                                >
                                    Edit
                                </a>

                                <a
                                    href="kelola_tahun_ajaran.php?hapus=<?= $row['id']; ?>"
                                    class="btn btn-sm btn-danger"
                                    onclick="return confirm('Yakin ingin menghapus tahun ajaran ini?')"
                                >
                                    Hapus
                                </a>

                            </td>

                        </tr>

                    <?php

                        }

                    } else {

                    ?>

                        <tr>
                            <td colspan="6" class="text-center">
                                Belum ada data tahun ajaran.
                            </td>
                        </tr>

                    <?php } ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

    <!-- FORM TAMBAH -->

    <div
        class="card shadow-sm mb-4"
        id="form-tambah"
        style="display:none;"
    >

        <div class="card-body">

            <h5 class="mb-3">Tambah Tahun Ajaran</h5>

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
                            >

                            <label
                                class="form-check-label"
                                for="aktif"
                            >
                                Jadikan tahun ajaran aktif
                            </label>

                        </div>

                    </div>

                </div>

                <div class="mt-4">

                    <button
                        type="submit"
                        name="simpan"
                        class="btn btn-primary"
                    >
                        Simpan
                    </button>

                    <button
                        type="button"
                        class="btn btn-secondary"
                        onclick="sembunyikanForm()"
                    >
                        Batal
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

<script>

function tampilkanForm() {

    document.getElementById("form-tambah").style.display = "block";

    document.getElementById("form-tambah").scrollIntoView({
        behavior: "smooth"
    });

}

function sembunyikanForm() {

    document.getElementById("form-tambah").style.display = "none";

}

</script>

</body>
</html>
