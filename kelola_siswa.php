
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
$tambah = isset($_GET['tambah']) && $_GET['tambah'] == '1';

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

    $tambah = true;

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
$cari = trim($_GET['cari'] ?? '');

/* =========================
   PAGINATION
========================= */
$batas = 10;

$halaman = isset($_GET['halaman'])
    ? max(1, (int) $_GET['halaman'])
    : 1;

$mulai = ($halaman - 1) * $batas;

/* HITUNG TOTAL DATA */
if ($cari !== '') {

    $keyword = "%{$cari}%";

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

} else {

    $hasil_total = mysqli_query(
        $koneksi,
        "SELECT COUNT(*) AS total FROM siswa"
    );
}

$total_data = (int) mysqli_fetch_assoc($hasil_total)['total'];

$total_halaman = max(1, (int) ceil($total_data / $batas));

if ($halaman > $total_halaman) {
    $halaman = $total_halaman;
    $mulai = ($halaman - 1) * $batas;
}

/* AMBIL DATA SISWA */
if ($cari !== '') {

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

} else {

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
}

mysqli_stmt_execute($stmt_data);
$data = mysqli_stmt_get_result($stmt_data);

/* URL PAGINATION */
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
            z-index: 1000;
        }

        .sidebar-title {
            color: white;
            font-size: 19px;
            font-weight: bold;
            margin: 0 8px 25px;
            line-height: 1.4;
        }

        .sidebar .nav-link {
            display: block;
            color: white;
            font-size: 14px;
            padding: 11px 13px;
            margin-bottom: 5px;
            border-radius: 5px;
            text-decoration: none;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: #4f5965;
            color: white;
        }

        .sidebar .logout {
            background: #b42323;
            margin-top: 20px;
        }

        .sidebar .logout:hover {
            background: #941d1d;
        }

        /* KONTEN */
        .content {
            margin-left: 220px;
            padding: 30px;
            min-height: 100vh;
        }

        .header-box,
        .main-box {
            background: white;
            border: 1px solid #ddd;
            border-radius: 7px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .header-box h1 {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        /* TOMBOL */
        .btn-tambah {
            background: #198754;
            color: white;
            border: none;
        }

        .btn-tambah:hover {
            background: #146c43;
            color: white;
        }

        /* TABEL */
        .table {
            font-size: 13px;
            margin-bottom: 0;
            vertical-align: middle;
        }

        .table thead th {
            background: #687280;
            color: white;
            padding: 11px 8px;
            white-space: nowrap;
        }

        .table tbody td {
            padding: 10px 8px;
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
<aside class="sidebar">

    <div class="sidebar-title">
        ⚖ Pelanggaran<br>Siswa
    </div>

    <nav class="nav flex-column">

        <a href="dashboard.php" class="nav-link">
             Dashboard
        </a>

        <a href="kelola_siswa.php" class="nav-link active">
            Data Siswa
        </a>

        <a href="kelola_guru.php" class="nav-link">
            Data Guru
        </a>

        <a href="kelola_kelas.php" class="nav-link">
            Data Kelas
        </a>

        <a href="kelola_tahun_ajaran.php" class="nav-link">
            Tahun Ajaran
        </a>

        <a href="kelola_wali_kelas.php" class="nav-link">
            Wali Kelas
        </a>

        <a href="penempatan_siswa.php" class="nav-link">
            Penempatan Siswa
        </a>

        <a href="kelola_pelanggaran_kategori.php" class="nav-link">
            ⚠ Kategori Pelanggaran
        </a>

        <a href="pelanggaran.php" class="nav-link">
            Data Pelanggaran
        </a>

        <a href="tindakan.php" class="nav-link">
            Data Tindakan
        </a>

        <a href="riwayat.php" class="nav-link">
            Riwayat
        </a>

        <a href="rekap_poin.php" class="nav-link">
            Rekap Poin
        </a>

        <a href="laporan.php" class="nav-link">
            Laporan
        </a>

        <a href="about_me.php" class="nav-link">
            ℹ About Me
        </a>

        <a href="logout.php" class="nav-link logout">
             Logout
        </a>

    </nav>
</aside>

<!-- KONTEN -->
<main class="content">

    <div class="header-box">
        <h1>Data Siswa</h1>
        <p class="text-secondary mb-0">
            Kelola data siswa dalam sistem.
        </p>
    </div>

    <?php if ($error !== ''): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <!-- FORM TAMBAH -->
    <?php if ($tambah): ?>

        <div class="main-box">

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
                        class="btn btn-success"
                    >
                        Simpan Siswa
                    </button>

                    <a href="kelola_siswa.php" class="btn btn-secondary">
                        Batal
                    </a>

                </div>

            </form>

        </div>

    <?php endif; ?>

    <!-- DAFTAR SISWA -->
    <div class="main-box">

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">

            <h5 class="mb-0">Daftar Siswa</h5>

            <?php if (!$tambah): ?>
                <a href="kelola_siswa.php?tambah=1" class="btn btn-tambah btn-sm">
                    + Tambah Siswa
                </a>
            <?php endif; ?>

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
                <button type="submit" class="btn btn-secondary btn-sm">
                    Cari
                </button>

                <a href="kelola_siswa.php" class="btn btn-outline-secondary btn-sm">
                    Reset
                </a>
            </div>

        </form>

        <!-- TABEL -->
        <div class="table-responsive">

            <table class="table table-bordered table-hover">

                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th>NIS</th>
                        <th>NISN</th>
                        <th>Nama Siswa</th>
                        <th>JK</th>
                        <th>No HP Orang Tua</th>
                        <th width="15%">Aksi</th>
                    </tr>
                </thead>

                <tbody>

                    <?php
                    $no = $mulai + 1;

                    if ($data && mysqli_num_rows($data) > 0):
                        while ($row = mysqli_fetch_assoc($data)):
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
                                    class="btn btn-success btn-sm"
                                >
                                    Edit
                                </a>

                                <a
                                    href="kelola_siswa.php?hapus=<?= (int)$row['id']; ?>"
                                    class="btn btn-danger btn-sm"
                                    onclick="return confirm('Yakin ingin menghapus siswa ini?')"
                                >
                                    Hapus
                                </a>
                            </td>
                        </tr>

                    <?php
                        endwhile;
                    else:
                    ?>

                        <tr>
                            <td colspan="7" class="text-center py-4">
                                Belum ada data siswa.
                            </td>
                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

        <!-- INFORMASI DAN PAGINATION -->
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3">

            <small class="text-secondary">
                Menampilkan
                <?= $total_data > 0 ? $mulai + 1 : 0; ?>
                sampai
                <?= min($mulai + $batas, $total_data); ?>
                dari <?= $total_data; ?> data siswa
            </small>

            <?php if ($total_halaman > 1): ?>

                <nav>
                    <ul class="pagination pagination-sm mb-0">

                        <?php if ($halaman > 1): ?>
                            <li class="page-item">
                                <a
                                    class="page-link"
                                    href="<?= urlHalamanSiswa($halaman - 1, $cari); ?>"
                                >
                                    Sebelumnya
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $total_halaman; $i++): ?>

                            <li class="page-item <?= $i == $halaman ? 'active' : ''; ?>">
                                <a
                                    class="page-link"
                                    href="<?= urlHalamanSiswa($i, $cari); ?>"
                                >
                                    <?= $i; ?>
                                </a>
                            </li>

                        <?php endfor; ?>

                        <?php if ($halaman < $total_halaman): ?>
                            <li class="page-item">
                                <a
                                    class="page-link"
                                    href="<?= urlHalamanSiswa($halaman + 1, $cari); ?>"
                                >
                                    Berikutnya
                                </a>
                            </li>
                        <?php endif; ?>

                    </ul>
                </nav>

            <?php endif; ?>

        </div>

    </div>

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
