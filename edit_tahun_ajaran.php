
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

$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    header("Location: kelola_tahun_ajaran.php");
    exit;
}

$pesan = "";

/* =========================
   SIMPAN PERUBAHAN
========================= */

if (isset($_POST['update'])) {

    $nama = trim($_POST['nama'] ?? '');
    $tanggal_mulai = $_POST['tanggal_mulai'] ?? '';
    $tanggal_selesai = $_POST['tanggal_selesai'] ?? '';
    $aktif = isset($_POST['aktif']) ? intval($_POST['aktif']) : 0;

    if ($nama == '' || $tanggal_mulai == '' || $tanggal_selesai == '') {

        $pesan = "Semua data harus diisi.";

    } elseif ($tanggal_selesai < $tanggal_mulai) {

        $pesan = "Tanggal selesai tidak boleh sebelum tanggal mulai.";

    } else {

        mysqli_begin_transaction($koneksi);

        try {

            // Jika aktif, nonaktifkan tahun ajaran lainnya
            if ($aktif == 1) {

                $nonaktif = mysqli_query(
                    $koneksi,
                    "UPDATE tahun_ajaran
                     SET aktif = 0
                     WHERE id != $id"
                );

                if (!$nonaktif) {
                    throw new Exception(mysqli_error($koneksi));
                }
            }

            // Update data tahun ajaran
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
                throw new Exception(mysqli_error($koneksi));
            }

            mysqli_stmt_bind_param(
                $stmt,
                "sssii",
                $nama,
                $tanggal_mulai,
                $tanggal_selesai,
                $aktif,
                $id
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

            $pesan = "Gagal menyimpan perubahan: " . $e->getMessage();
        }
    }
}

/* =========================
   AMBIL DATA
========================= */

$query = mysqli_query(
    $koneksi,
    "SELECT id, nama, tanggal_mulai, tanggal_selesai, aktif
     FROM tahun_ajaran
     WHERE id = $id
     LIMIT 1"
);

if (!$query) {
    die("Query error: " . mysqli_error($koneksi));
}

if (mysqli_num_rows($query) == 0) {
    echo "Data tahun ajaran tidak ditemukan.";
    exit;
}

$data = mysqli_fetch_assoc($query);

?>

<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Edit Tahun Ajaran</title>

<!-- BOOTSTRAP 5 -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

/* CSS SENDIRI */

body {
    background: #f4f6f8;
    font-family: Arial, sans-serif;
}

.content {
    width: 700px;
    max-width: 92%;
    margin: 50px auto;
}

.status-box {
    background: #f1f5f9;
    border: 1px solid #ddd;
    border-radius: 5px;
    padding: 12px;
    margin-bottom: 20px;
}

</style>

</head>

<body>

<div class="content">

    <div class="card shadow-sm">

        <div class="card-body p-4">

            <h4 class="mb-2">
                Edit Tahun Ajaran
            </h4>

            <p class="text-secondary mb-4">
                Ubah data tahun ajaran di bawah ini.
            </p>

            <!-- PESAN ERROR -->

            <?php if ($pesan != '') { ?>

                <div class="alert alert-danger">
                    <?= htmlspecialchars($pesan); ?>
                </div>

            <?php } ?>

            <!-- STATUS SAAT INI -->

            <div class="status-box">

                Status saat ini:

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

            <!-- FORM EDIT -->

            <form method="POST">

                <div class="mb-3">

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

                <div class="mb-3">

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

                <div class="mb-3">

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

                <div class="mb-4">

                    <label class="form-label">
                        Status Tahun Ajaran
                    </label>

                    <select
                        name="aktif"
                        class="form-select"
                    >

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
                    Kembali
                </a>

            </form>

        </div>

    </div>

</div>

</body>
</html>
