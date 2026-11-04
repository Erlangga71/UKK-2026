
<?php
session_start();
require_once "database.php";

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

if (($_SESSION['role'] ?? '') !== 'admin') {
    die("Akses ditolak.");
}

$error = "";

/* =========================
   TAMBAH KATEGORI
========================= */

if (isset($_POST['simpan'])) {
    $nama = trim($_POST['nama'] ?? '');

    if ($nama === '') {
        $error = "Nama kategori wajib diisi.";
    } else {
        $cek = mysqli_prepare(
            $koneksi,
            "SELECT id FROM kategori WHERE nama = ? LIMIT 1"
        );

        mysqli_stmt_bind_param($cek, "s", $nama);
        mysqli_stmt_execute($cek);
        $hasil_cek = mysqli_stmt_get_result($cek);

        if (mysqli_num_rows($hasil_cek) > 0) {
            $error = "Kategori tersebut sudah ada.";
        } else {
            $stmt = mysqli_prepare(
                $koneksi,
                "INSERT INTO kategori (nama) VALUES (?)"
            );

            mysqli_stmt_bind_param($stmt, "s", $nama);

            if (mysqli_stmt_execute($stmt)) {
                header("Location: kelola_pelanggaran_kategori.php");
                exit;
            } else {
                $error = "Gagal menambahkan kategori: " . mysqli_error($koneksi);
            }
        }
    }
}

/* =========================
   HAPUS KATEGORI
========================= */

if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];

    if ($id > 0) {
        $stmt = mysqli_prepare(
            $koneksi,
            "DELETE FROM kategori WHERE id = ?"
        );

        mysqli_stmt_bind_param($stmt, "i", $id);

        if (!mysqli_stmt_execute($stmt)) {
            $error = "Kategori tidak dapat dihapus. Pastikan kategori tidak sedang digunakan oleh jenis pelanggaran.";
        } else {
            header("Location: kelola_pelanggaran_kategori.php");
            exit;
        }
    }
}

/* =========================
   PENCARIAN
========================= */

$cari = trim($_GET['cari'] ?? '');
$cari_sql = "%" . $cari . "%";

$stmt_total = mysqli_prepare(
    $koneksi,
    "SELECT COUNT(*) AS total
     FROM kategori
     WHERE nama LIKE ?"
);

mysqli_stmt_bind_param($stmt_total, "s", $cari_sql);
mysqli_stmt_execute($stmt_total);

$total_data = (int)mysqli_fetch_assoc(
    mysqli_stmt_get_result($stmt_total)
)['total'];

/* =========================
   PAGINATION
========================= */

$batas = 5;
$total_halaman = max(1, (int)ceil($total_data / $batas));

$halaman = max(1, (int)($_GET['halaman'] ?? 1));
$halaman = min($halaman, $total_halaman);

$offset = ($halaman - 1) * $batas;

/* =========================
   DATA KATEGORI
========================= */

$stmt_data = mysqli_prepare(
    $koneksi,
    "SELECT id, nama
     FROM kategori
     WHERE nama LIKE ?
     ORDER BY id ASC
     LIMIT ? OFFSET ?"
);

mysqli_stmt_bind_param(
    $stmt_data,
    "sii",
    $cari_sql,
    $batas,
    $offset
);

mysqli_stmt_execute($stmt_data);
$data = mysqli_stmt_get_result($stmt_data);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Kategori Pelanggaran</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            margin: 0;
            background: #f4f6f8;
            font-family: Arial, sans-serif;
            color: #333;
        }

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 220px;
            height: 100vh;
            padding: 20px 12px;
            background: #687280;
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

        .content {
            margin-left: 220px;
            padding: 30px;
        }

        .header,
        .box {
            background: white;
            border: 1px solid #ddd;
            border-radius: 7px;
            padding: 22px;
            margin-bottom: 20px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
        }

        .header p {
            color: #777;
            margin: 5px 0 0;
        }

        .btn-tambah {
            background: #9dff00;
            border: 1px solid #9dff00;
            color: #222;
            font-weight: bold;
        }

        .btn-tambah:hover {
            background: #8be000;
            border-color: #8be000;
            color: #222;
        }

        .btn-hapus {
            background: #b42323;
            color: white;
        }

        .btn-hapus:hover {
            background: #941d1d;
            color: white;
        }

        .table thead th {
            background: #687280;
            color: white;
            white-space: nowrap;
        }

        .table td {
            vertical-align: middle;
        }

        .pagination .page-link {
            color: #333;
        }

        .pagination .active .page-link {
            background: #687280;
            border-color: #687280;
            color: white;
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

            .header {
                display: block;
            }

            .header .btn {
                margin-top: 15px;
            }
        }
    </style>
</head>

<body>

<div class="sidebar">
    <div class="sidebar-title">⚖ Pelanggaran<br>Siswa</div>

    <a href="dashboard.php">🏠 Dashboard</a>
    <a href="kelola_siswa.php">Data Siswa</a>
    <a href="kelola_guru.php">Data Guru</a>
    <a href="kelola_kelas.php">Data Kelas</a>
    <a href="kelola_tahun_ajaran.php">Tahun Ajaran</a>
    <a href="kelola_wali_kelas.php">Wali Kelas</a>
    <a href="penempatan_siswa.php">Penempatan Siswa</a>
    <a href="kelola_pelanggaran_kategori.php" class="active">⚠ Kategori Pelanggaran</a>
    <a href="pelanggaran.php">Data Pelanggaran</a>
    <a href="tindakan.php">Data Tindakan</a>
    <a href="laporan.php">Laporan</a>
    <a href="about_me.php">ℹ About Me</a>
    <a href="logout.php" class="logout">🚪 Logout</a>
</div>

<div class="content">

    <div class="header">
        <div>
            <h1>Kategori Pelanggaran</h1>
            <p>Kelola kategori pelanggaran siswa.</p>
        </div>

        <a href="kelola_pelanggaran_kategori.php?tambah=1"
           class="btn btn-tambah">
            + Tambah Kategori
        </a>
    </div>

    <?php if ($error !== ''): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['tambah']) && $_GET['tambah'] == '1'): ?>
        <div class="box">
            <h5 class="mb-3">Tambah Kategori Pelanggaran</h5>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Nama Kategori</label>
                    <input
                        type="text"
                        name="nama"
                        class="form-control"
                        placeholder="Contoh: Kedisiplinan"
                        required
                    >
                </div>

                <button type="submit" name="simpan" class="btn btn-tambah">
                    Simpan Kategori
                </button>

                <a href="kelola_pelanggaran_kategori.php" class="btn btn-secondary">
                    Batal
                </a>
            </form>
        </div>
    <?php endif; ?>

    <div class="box">
        <h5 class="mb-3">Daftar Kategori Pelanggaran</h5>

        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-5">
                <input
                    type="text"
                    name="cari"
                    class="form-control"
                    placeholder="Cari nama kategori..."
                    value="<?= htmlspecialchars($cari); ?>"
                >
            </div>

            <div class="col-auto">
                <button type="submit" class="btn btn-secondary">Cari</button>
                <a href="kelola_pelanggaran_kategori.php" class="btn btn-outline-secondary">
                    Reset
                </a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Kategori</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if ($data && mysqli_num_rows($data) > 0): ?>
                        <?php $no = $offset + 1; ?>

                        <?php while ($row = mysqli_fetch_assoc($data)): ?>
                            <tr>
                                <td><?= $no++; ?></td>

                                <td>
                                    <b><?= htmlspecialchars($row['nama']); ?></b>
                                </td>

                                <td class="text-nowrap">
                                    <a
                                        href="edit_kategori.php?id=<?= $row['id']; ?>"
                                        class="btn btn-sm btn-tambah"
                                    >Edit</a>

                                    <a
                                        href="kelola_pelanggaran_kategori.php?hapus=<?= $row['id']; ?>"
                                        class="btn btn-sm btn-hapus"
                                        onclick="return confirm('Yakin ingin menghapus kategori ini?')"
                                    >Hapus</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>

                    <?php else: ?>
                        <tr>
                            <td colspan="3" class="text-center py-4">
                                Belum ada kategori pelanggaran.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <small class="text-muted">
                Total <?= $total_data; ?> kategori
            </small>

            <nav>
                <ul class="pagination pagination-sm mb-0">
                    <?php for ($i = 1; $i <= $total_halaman; $i++): ?>
                        <li class="page-item <?= ($halaman == $i) ? 'active' : ''; ?>">
                            <a
                                class="page-link"
                                href="?halaman=<?= $i; ?>&cari=<?= urlencode($cari); ?>"
                            ><?= $i; ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        </div>
    </div>

</div>

</body>
</html>
