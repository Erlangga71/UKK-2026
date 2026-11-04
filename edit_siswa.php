
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
   AMBIL ID SISWA
========================= */

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    header("Location: kelola_siswa.php");
    exit;
}

/* =========================
   AMBIL DATA SISWA
========================= */

$stmt = mysqli_prepare(
    $koneksi,
    "SELECT * FROM siswa WHERE id = ? LIMIT 1"
);

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$hasil = mysqli_stmt_get_result($stmt);
$siswa = mysqli_fetch_assoc($hasil);

mysqli_stmt_close($stmt);

if (!$siswa) {
    header("Location: kelola_siswa.php");
    exit;
}

/* =========================
   PROSES UPDATE
========================= */

if (isset($_POST['update'])) {

    $nis           = trim($_POST['nis'] ?? '');
    $nisn          = trim($_POST['nisn'] ?? '');
    $nama          = trim($_POST['nama'] ?? '');
    $jenis_kelamin = trim($_POST['jenis_kelamin'] ?? '');
    $alamat        = trim($_POST['alamat'] ?? '');
    $no_hp_ortu    = trim($_POST['no_hp_ortu'] ?? '');

    /* VALIDASI WAJIB */

    if ($nis == '' || $nisn == '' || $nama == '') {

        $error = "NIS, NISN, dan nama wajib diisi.";

    } elseif (!ctype_digit($nis) || strlen($nis) > 15) {

        $error = "NIS hanya boleh angka dan maksimal 15 digit.";

    } elseif (!ctype_digit($nisn) || strlen($nisn) > 10) {

        $error = "NISN hanya boleh angka dan maksimal 10 digit.";

    } else {

        /* CEK NIS MILIK SISWA LAIN */

        $stmt_cek_nis = mysqli_prepare(
            $koneksi,
            "SELECT id FROM siswa
             WHERE nis = ? AND id != ?
             LIMIT 1"
        );

        mysqli_stmt_bind_param($stmt_cek_nis, "si", $nis, $id);
        mysqli_stmt_execute($stmt_cek_nis);

        $cek_nis = mysqli_stmt_get_result($stmt_cek_nis);

        /* CEK NISN MILIK SISWA LAIN */

        $stmt_cek_nisn = mysqli_prepare(
            $koneksi,
            "SELECT id FROM siswa
             WHERE nisn = ? AND id != ?
             LIMIT 1"
        );

        mysqli_stmt_bind_param($stmt_cek_nisn, "si", $nisn, $id);
        mysqli_stmt_execute($stmt_cek_nisn);

        $cek_nisn = mysqli_stmt_get_result($stmt_cek_nisn);

        if (mysqli_num_rows($cek_nis) > 0) {

            $error = "NIS tersebut sudah digunakan.";

        } elseif (mysqli_num_rows($cek_nisn) > 0) {

            $error = "NISN tersebut sudah digunakan.";

        } else {

            /* UPDATE DATA SISWA */

            $stmt_update = mysqli_prepare(
                $koneksi,
                "UPDATE siswa SET
                    nis = ?,
                    nisn = ?,
                    nama = ?,
                    jenis_kelamin = ?,
                    alamat = ?,
                    no_hp_ortu = ?
                 WHERE id = ?"
            );

            mysqli_stmt_bind_param(
                $stmt_update,
                "ssssssi",
                $nis,
                $nisn,
                $nama,
                $jenis_kelamin,
                $alamat,
                $no_hp_ortu,
                $id
            );

            if (mysqli_stmt_execute($stmt_update)) {

                mysqli_stmt_close($stmt_update);
                mysqli_stmt_close($stmt_cek_nis);
                mysqli_stmt_close($stmt_cek_nisn);

                header("Location: kelola_siswa.php");
                exit;

            } else {

                $error = "Gagal memperbarui data: " . mysqli_error($koneksi);

            }

            mysqli_stmt_close($stmt_update);
        }

        mysqli_stmt_close($stmt_cek_nis);
        mysqli_stmt_close($stmt_cek_nisn);
    }

    /* SIMPAN KEMBALI NILAI FORM */

    $siswa['nis'] = $nis;
    $siswa['nisn'] = $nisn;
    $siswa['nama'] = $nama;
    $siswa['jenis_kelamin'] = $jenis_kelamin;
    $siswa['alamat'] = $alamat;
    $siswa['no_hp_ortu'] = $no_hp_ortu;
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Data Siswa</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            background: #f4f6f8;
            font-family: Arial, sans-serif;
            font-size: 14px;
            margin: 0;
        }

        /* SIDEBAR */

        .sidebar {
            position: fixed !important;
            top: 0;
            left: 0;
            width: 200px !important;
            min-width: 200px;
            max-width: 200px;
            height: 100vh;
            background: #687280;
            overflow-y: auto;
            padding: 15px;
            z-index: 1000;
        }

        .sidebar .nav-link {
            color: white;
            font-size: 12px;
            padding: 8px 12px;
            border-radius: 4px;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: #4f5965;
            color: white;
        }

        /* CONTENT */

        .content {
            margin-left: 200px;
            width: calc(100% - 200px);
            min-height: 100vh;
            padding: 18px;
        }

        .card {
            border: 1px solid #d8d8d8;
            border-radius: 6px;
            margin-bottom: 12px;
        }

        .card-body {
            padding: 13px;
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

        @media (max-width: 700px) {

            .sidebar {
                position: static !important;
                width: 100% !important;
                min-width: 100%;
                max-width: 100%;
                height: auto !important;
            }

            .content {
                margin-left: 0;
                width: 100%;
            }

        }

    </style>

</head>

<body>

<!-- SIDEBAR -->

<div class="sidebar">

    <h5 class="text-white fw-bold mb-4 px-2">
        ⚖ Pelanggaran<br>Siswa
    </h5>

    <ul class="nav flex-column gap-1">

        <li><a class="nav-link" href="dashboard.php">🏠 Dashboard</a></li>
        <li><a class="nav-link active" href="kelola_siswa.php">Data Siswa</a></li>
        <li><a class="nav-link" href="kelola_guru.php">Data Guru</a></li>
        <li><a class="nav-link" href="kelola_kelas.php">Data Kelas</a></li>
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

<div class="content">

    <!-- HEADER -->

    <div class="card mb-3">

        <div class="card-body">

            <h1 class="h3 mb-1">Data Siswa</h1>

            <p class="text-muted mb-0">
                Kelola data siswa dalam sistem.
            </p>

        </div>

    </div>


    <!-- PESAN ERROR -->

    <?php if ($error != "") { ?>

        <div class="alert alert-danger">
            <?= htmlspecialchars($error); ?>
        </div>

    <?php } ?>


    <!-- FORM EDIT -->

    <div class="card">

        <div class="card-body">

            <h2 class="h5 mb-3">Edit Data Siswa</h2>

            <form method="POST">

                <div class="row g-3">

                    <!-- NIS -->

                    <div class="col-md-6">

                        <label class="form-label fw-bold">NIS</label>

                        <input
                            type="text"
                            name="nis"
                            class="form-control"
                            placeholder="Masukkan NIS"
                            value="<?= htmlspecialchars($siswa['nis'] ?? ''); ?>"
                            maxlength="15"
                            inputmode="numeric"
                            pattern="[0-9]{1,15}"
                            title="NIS hanya angka, maksimal 15 digit."
                            oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 15)"
                            required
                        >

                    </div>


                    <!-- NISN -->

                    <div class="col-md-6">

                        <label class="form-label fw-bold">NISN</label>

                        <input
                            type="text"
                            name="nisn"
                            class="form-control"
                            placeholder="Masukkan NISN (maksimal 10 digit)"
                            value="<?= htmlspecialchars($siswa['nisn'] ?? ''); ?>"
                            maxlength="10"
                            inputmode="numeric"
                            pattern="[0-9]{1,10}"
                            title="NISN hanya boleh angka dan maksimal 10 digit."
                            oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)"
                            required
                        >

                    </div>


                    <!-- NAMA -->

                    <div class="col-md-6">

                        <label class="form-label fw-bold">Nama</label>

                        <input
                            type="text"
                            name="nama"
                            class="form-control"
                            placeholder="Masukkan nama siswa"
                            value="<?= htmlspecialchars($siswa['nama'] ?? ''); ?>"
                            required
                        >

                    </div>


                    <!-- JENIS KELAMIN -->

                    <div class="col-md-6">

                        <label class="form-label fw-bold">Jenis Kelamin</label>

                        <select name="jenis_kelamin" class="form-select">

                            <option value="">-- Pilih --</option>

                            <option value="L" <?= ($siswa['jenis_kelamin'] ?? '') == 'L' ? 'selected' : ''; ?>>
                                Laki-laki
                            </option>

                            <option value="P" <?= ($siswa['jenis_kelamin'] ?? '') == 'P' ? 'selected' : ''; ?>>
                                Perempuan
                            </option>

                        </select>

                    </div>


                    <!-- NO HP -->

                    <div class="col-md-6">

                        <label class="form-label fw-bold">No HP Orang Tua</label>

                        <input
                            type="text"
                            name="no_hp_ortu"
                            class="form-control"
                            placeholder="Masukkan nomor HP"
                            value="<?= htmlspecialchars($siswa['no_hp_ortu'] ?? ''); ?>"
                        >

                    </div>


                    <!-- ALAMAT -->

                    <div class="col-12">

                        <label class="form-label fw-bold">Alamat</label>

                        <textarea
                            name="alamat"
                            rows="3"
                            class="form-control"
                            placeholder="Masukkan alamat"
                        ><?= htmlspecialchars($siswa['alamat'] ?? ''); ?></textarea>

                    </div>

                </div>


                <!-- TOMBOL -->

                <div class="mt-3">

                    <button type="submit" name="update" class="btn btn-secondary">
                        Simpan Perubahan
                    </button>

                    <a href="kelola_siswa.php" class="btn btn-outline-secondary">
                        Batal
                    </a>

                </div>

            </form>

        </div>

    </div>

</div>

</body>
</html>
