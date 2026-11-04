
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

$tambah = isset($_GET['tambah']) && $_GET['tambah'] == '1';
$error = "";

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

    if ($nis == '' || $nisn == '' || $nama == '') {
        $error = "NIS, NISN, dan nama wajib diisi.";
        $tambah = true;

    } elseif (!ctype_digit($nis) || strlen($nis) > 15) {
        $error = "NIS hanya boleh angka dan maksimal 15 digit.";
        $tambah = true;

    } elseif (!ctype_digit($nisn) || strlen($nisn) > 10) {
        $error = "NISN hanya boleh angka dan maksimal 10 digit.";
        $tambah = true;

    } else {

        $stmt = mysqli_prepare(
            $koneksi,
            "SELECT id FROM siswa WHERE nis = ? LIMIT 1"
        );

        mysqli_stmt_bind_param($stmt, "s", $nis);
        mysqli_stmt_execute($stmt);
        $cek_nis = mysqli_stmt_get_result($stmt);

        $stmt_nisn = mysqli_prepare(
            $koneksi,
            "SELECT id FROM siswa WHERE nisn = ? LIMIT 1"
        );

        mysqli_stmt_bind_param($stmt_nisn, "s", $nisn);
        mysqli_stmt_execute($stmt_nisn);
        $cek_nisn = mysqli_stmt_get_result($stmt_nisn);

        if (mysqli_num_rows($cek_nis) > 0) {
            $error = "NIS tersebut sudah digunakan.";
            $tambah = true;

        } elseif (mysqli_num_rows($cek_nisn) > 0) {
            $error = "NISN tersebut sudah digunakan.";
            $tambah = true;

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
                mysqli_stmt_close($stmt_nisn);

                header("Location: kelola_siswa.php");
                exit;
            } else {
                $error = "Gagal menambahkan siswa: " . mysqli_error($koneksi);
                $tambah = true;
            }

            mysqli_stmt_close($stmt_insert);
        }

        mysqli_stmt_close($stmt);
        mysqli_stmt_close($stmt_nisn);
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
            $error = "Data tidak bisa dihapus. Mungkin masih digunakan pada data pelanggaran atau penempatan siswa.";
        }

        mysqli_stmt_close($stmt);
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
$halaman = isset($_GET['halaman']) ? (int) $_GET['halaman'] : 1;

if ($halaman < 1) {
    $halaman = 1;
}

$mulai = ($halaman - 1) * $batas;

if ($cari != '') {

    $keyword = "%" . $cari . "%";

    $stmt_total = mysqli_prepare(
        $koneksi,
        "SELECT COUNT(*) AS total
         FROM siswa
         WHERE nis LIKE ?
            OR nisn LIKE ?
            OR nama LIKE ?"
    );

    mysqli_stmt_bind_param(
        $stmt_total,
        "sss",
        $keyword,
        $keyword,
        $keyword
    );

    mysqli_stmt_execute($stmt_total);
    $hasil_total = mysqli_stmt_get_result($stmt_total);
    $total_data = mysqli_fetch_assoc($hasil_total)['total'];

    $stmt_data = mysqli_prepare(
        $koneksi,
        "SELECT id, nis, nisn, nama, jenis_kelamin,
                alamat, no_hp_ortu, aktif
         FROM siswa
         WHERE nis LIKE ?
            OR nisn LIKE ?
            OR nama LIKE ?
         ORDER BY id DESC
         LIMIT ? OFFSET ?"
    );

    mysqli_stmt_bind_param(
        $stmt_data,
        "sssii",
        $keyword,
        $keyword,
        $keyword,
        $batas,
        $mulai
    );

    mysqli_stmt_execute($stmt_data);
    $data = mysqli_stmt_get_result($stmt_data);

} else {

    $hasil_total = mysqli_query(
        $koneksi,
        "SELECT COUNT(*) AS total FROM siswa"
    );

    $total_data = mysqli_fetch_assoc($hasil_total)['total'];

    $stmt_data = mysqli_prepare(
        $koneksi,
        "SELECT id, nis, nisn, nama, jenis_kelamin,
                alamat, no_hp_ortu, aktif
         FROM siswa
         ORDER BY id DESC
         LIMIT ? OFFSET ?"
    );

    mysqli_stmt_bind_param(
        $stmt_data,
        "ii",
        $batas,
        $mulai
    );

    mysqli_stmt_execute($stmt_data);
    $data = mysqli_stmt_get_result($stmt_data);
}

$total_halaman = max(1, ceil($total_data / $batas));

if ($halaman > $total_halaman) {
    $halaman = $total_halaman;
}

/* Fungsi URL pagination */
function urlHalamanSiswa($halaman, $cari) {
    return "kelola_siswa.php?halaman=" . $halaman .
           "&cari=" . urlencode($cari);
}
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

        /* TOMBOL TAMBAH */
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
    <a href="kelola_siswa.php" class="active">Data Siswa</a>
    <a href="kelola_guru.php">Data Guru</a>
    <a href="kelola_kelas.php">Data Kelas</a>
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
            <h1 class="h4 mb-1">Data Siswa</h1>
            <p class="text-muted mb-0">Kelola data siswa dalam sistem.</p>
        </div>
    </div>

    <?php if ($error != "") { ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($error); ?>
        </div>
    <?php } ?>

    <!-- FORM TAMBAH -->
    <?php if ($tambah) { ?>

        <div class="card">
            <div class="card-body">

                <h2 class="h5 mb-3">Tambah Data Siswa</h2>

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
                                title="NIS hanya angka, maksimal 15 digit."
                                oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 15)"
                                required
                            >
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">NISN</label>
                            <input
                                type="text"
                                name="nisn"
                                class="form-control"
                                placeholder="Masukkan NISN (maksimal 10 digit)"
                                maxlength="10"
                                inputmode="numeric"
                                pattern="[0-9]{1,10}"
                                title="NISN hanya boleh angka dan maksimal 10 digit."
                                oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)"
                                required
                            >
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Nama</label>
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
                                <option value="">-- Pilih --</option>
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
                                placeholder="Masukkan nomor HP"
                            >
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold">Alamat</label>
                            <textarea
                                name="alamat"
                                rows="3"
                                class="form-control"
                                placeholder="Masukkan alamat"
                            ></textarea>
                        </div>

                    </div>

                    <div class="mt-3">
                        <button type="submit" name="simpan" class="btn btn-secondary">
                            Simpan Siswa
                        </button>

                        <a href="kelola_siswa.php" class="btn btn-outline-secondary">
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

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">

                <h2 class="h5 mb-0">Daftar Siswa</h2>

                <?php if (!$tambah) { ?>
                    <a href="kelola_siswa.php?tambah=1" class="btn btn-sm btn-stabilo">
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
                        value="<?= htmlspecialchars($cari); ?>"
                    >
                </div>

                <div class="col-auto">
                    <button type="submit" class="btn btn-sm btn-secondary">
                        Cari
                    </button>

                    <a href="kelola_siswa.php" class="btn btn-sm btn-outline-secondary">
                        Reset
                    </a>
                </div>

            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">

                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th width="12%">NIS</th>
                            <th width="12%">NISN</th>
                            <th width="20%">Nama</th>
                            <th width="7%">JK</th>
                            <th width="22%">No HP Orang Tua</th>
                            <th width="22%">Aksi</th>
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
                                <?= htmlspecialchars($row['nis']); ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($row['nisn'] ?? '-'); ?>
                            </td>

                            <td>
                                <b><?= htmlspecialchars($row['nama']); ?></b>
                            </td>

                            <td>
                                <?= htmlspecialchars($row['jenis_kelamin'] ?? '-'); ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($row['no_hp_ortu'] ?? '-'); ?>
                            </td>

                            <td>
                                <a
                                    class="btn btn-sm btn-stabilo"
                                    href="edit_siswa.php?id=<?= (int)$row['id']; ?>"
                                >Edit</a>

                                <a
                                    class="btn btn-sm btn-danger"
                                    href="kelola_siswa.php?hapus=<?= (int)$row['id']; ?>"
                                    onclick="return confirm('Hapus data siswa ini?')"
                                >Hapus</a>
                            </td>
                        </tr>

                    <?php
                        }
                    } else {
                    ?>

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
            <div class="d-flex justify-content-between align-items-center flex-wrap mt-3">

                <small class="text-muted">
                    Menampilkan <?= $total_data > 0 ? $mulai + 1 : 0; ?>
                    sampai <?= min($mulai + $batas, $total_data); ?>
                    dari <?= $total_data; ?> data siswa
                </small>

                <!-- PAGINATION -->
                <?php if ($total_halaman > 1) { ?>

                    <nav>
                        <ul class="pagination pagination-sm mb-0">

                            <?php if ($halaman > 1) { ?>
                                <li class="page-item">
                                    <a class="page-link"
                                       href="<?= urlHalamanSiswa($halaman - 1, $cari); ?>">
                                        Sebelumnya
                                    </a>
                                </li>
                            <?php } ?>

                            <?php for ($i = 1; $i <= $total_halaman; $i++) { ?>

                                <li class="page-item <?= ($i == $halaman) ? 'active' : ''; ?>">
                                    <a class="page-link"
                                       href="<?= urlHalamanSiswa($i, $cari); ?>">
                                        <?= $i; ?>
                                    </a>
                                </li>

                            <?php } ?>

                            <?php if ($halaman < $total_halaman) { ?>
                                <li class="page-item">
                                    <a class="page-link"
                                       href="<?= urlHalamanSiswa($halaman + 1, $cari); ?>">
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
