
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

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    header("Location: kelola_pelanggaran_kategori.php");
    exit;
}

$error = "";

// Ambil data kategori
$stmt = mysqli_prepare(
    $koneksi,
    "SELECT id, nama FROM kategori WHERE id = ? LIMIT 1"
);

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$kategori = mysqli_fetch_assoc($result);

if (!$kategori) {
    echo "Kategori tidak ditemukan.";
    exit;
}

// Proses update
if (isset($_POST['update'])) {
    $nama = trim($_POST['nama'] ?? '');

    if ($nama === '') {
        $error = "Nama kategori wajib diisi.";
    } else {
        // Cek nama kategori yang sama
        $cek = mysqli_prepare(
            $koneksi,
            "SELECT id FROM kategori
             WHERE nama = ? AND id != ?
             LIMIT 1"
        );

        mysqli_stmt_bind_param($cek, "si", $nama, $id);
        mysqli_stmt_execute($cek);

        $hasil_cek = mysqli_stmt_get_result($cek);

        if (mysqli_num_rows($hasil_cek) > 0) {
            $error = "Nama kategori tersebut sudah digunakan.";
        } else {
            $update = mysqli_prepare(
                $koneksi,
                "UPDATE kategori SET nama = ? WHERE id = ?"
            );

            mysqli_stmt_bind_param($update, "si", $nama, $id);

            if (mysqli_stmt_execute($update)) {
                header("Location: kelola_pelanggaran_kategori.php");
                exit;
            } else {
                $error = "Gagal mengubah kategori: " . mysqli_error($koneksi);
            }
        }
    }

    $kategori['nama'] = $nama;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Kategori Pelanggaran</title>

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

<div class="sidebar">
    <div class="sidebar-title"> Pelanggaran<br>Siswa</div>

    <a href="dashboard.php"> Dashboard</a>
    <a href="kelola_siswa.php">Data Siswa</a>
    <a href="kelola_guru.php">Data Guru</a>
    <a href="kelola_kelas.php">Data Kelas</a>
    <a href="kelola_tahun_ajaran.php">Tahun Ajaran</a>
    <a href="kelola_wali_kelas.php">Wali Kelas</a>
    <a href="penempatan_siswa.php">Penempatan Siswa</a>
    <a href="kelola_pelanggaran_kategori.php" class="active"> Kategori Pelanggaran</a>
    <a href="pelanggaran.php">Data Pelanggaran</a>
    <a href="tindakan.php">Data Tindakan</a>
    <a href="laporan.php">Laporan</a>
    <a href="about_me.php"> About Me</a>
    <a href="logout.php" class="logout"> Logout</a>
</div>

<div class="content">

    <div class="header">
        <div>
            <h1>Edit Kategori Pelanggaran</h1>
            <p>Ubah nama kategori pelanggaran siswa.</p>
        </div>
    </div>

    <?php if ($error !== ''): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <div class="box">

        <h5 class="mb-3">Form Edit Kategori</h5>

        <form method="POST">

            <div class="mb-3">
                <label class="form-label">Nama Kategori</label>

                <input
                    type="text"
                    name="nama"
                    class="form-control"
                    value="<?= htmlspecialchars($kategori['nama']); ?>"
                    placeholder="Masukkan nama kategori"
                    required
                >
            </div>

            <button type="submit" name="update" class="btn btn-tambah">
                Simpan Perubahan
            </button>

            <a href="kelola_pelanggaran_kategori.php" class="btn btn-secondary">
                Kembali
            </a>

        </form>

    </div>

</div>

</body>
</html>
