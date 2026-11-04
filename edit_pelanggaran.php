
<?php
session_start();
require_once "database.php";

// =========================
// CEK LOGIN DAN ROLE
// =========================
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

if (($_SESSION['role'] ?? '') !== 'admin') {
    die("Akses ditolak.");
}

$error = "";

// =========================
// AMBIL ID PELANGGARAN
// =========================
$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    die("ID pelanggaran tidak valid.");
}

// =========================
// AMBIL DATA PELANGGARAN
// =========================
$stmt = mysqli_prepare(
    $koneksi,
    "SELECT * FROM pelanggaran WHERE id = ? LIMIT 1"
);

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$data) {
    die("Data pelanggaran tidak ditemukan.");
}

// =========================
// PROSES UPDATE
// =========================
if (isset($_POST['update'])) {

    $siswa_id = (int)($_POST['siswa_id'] ?? 0);
    $jenis_id = (int)($_POST['jenis_id'] ?? 0);
    $guru_id = ($_POST['guru_id'] ?? '') === ''
        ? null
        : (int)$_POST['guru_id'];

    $tanggal = trim($_POST['tanggal'] ?? '');
    $keterangan = trim($_POST['keterangan'] ?? '');
    $status = $_POST['status'] ?? '';

    $status_valid = ['tercatat', 'ditindaklanjuti', 'selesai'];

    if ($siswa_id <= 0 || $jenis_id <= 0 || $tanggal === '') {
        $error = "Nama siswa, jenis pelanggaran, dan tanggal wajib diisi.";
    } elseif (!in_array($status, $status_valid, true)) {
        $error = "Status pelanggaran tidak valid.";
    } else {

        $stmt = mysqli_prepare(
            $koneksi,
            "UPDATE pelanggaran SET
                siswa_id = ?,
                jenis_id = ?,
                guru_id = ?,
                tanggal = ?,
                keterangan = ?,
                status = ?
             WHERE id = ?"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "iiisssi",
            $siswa_id,
            $jenis_id,
            $guru_id,
            $tanggal,
            $keterangan,
            $status,
            $id
        );

        if (mysqli_stmt_execute($stmt)) {
            header("Location: pelanggaran.php");
            exit;
        } else {
            $error = "Gagal memperbarui data: " . mysqli_error($koneksi);
        }
    }

    // Perbarui tampilan form dengan input terakhir
    $data['siswa_id'] = $siswa_id;
    $data['jenis_id'] = $jenis_id;
    $data['guru_id'] = $guru_id;
    $data['tanggal'] = $tanggal;
    $data['keterangan'] = $keterangan;
    $data['status'] = $status;
}

// =========================
// AMBIL DATA PILIHAN
// =========================
$siswa = mysqli_query(
    $koneksi,
    "SELECT id, nama, nis FROM siswa ORDER BY nama ASC"
);

$jenis = mysqli_query(
    $koneksi,
    "SELECT id, nama FROM jenis_pelanggaran ORDER BY nama ASC"
);

$guru = mysqli_query(
    $koneksi,
    "SELECT id, nama FROM guru ORDER BY nama ASC"
);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Pelanggaran</title>

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

        .btn-simpan {
            background: #9dff00;
            border: 1px solid #9dff00;
            color: #222;
            font-weight: bold;
        }

        .btn-simpan:hover {
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
    <div class="sidebar-title">⚖ Pelanggaran<br>Siswa</div>

    <a href="dashboard.php">🏠 Dashboard</a>
    <a href="kelola_siswa.php">Data Siswa</a>
    <a href="kelola_guru.php">Data Guru</a>
    <a href="kelola_kelas.php">Data Kelas</a>
    <a href="kelola_tahun_ajaran.php">Tahun Ajaran</a>
    <a href="kelola_wali_kelas.php">Wali Kelas</a>
    <a href="penempatan_siswa.php">Penempatan Siswa</a>
    <a href="kelola_pelanggaran_kategori.php">⚠ Kategori Pelanggaran</a>
    <a href="pelanggaran.php" class="active">Data Pelanggaran</a>
    <a href="tindakan.php">Data Tindakan</a>
    <a href="laporan.php">Laporan</a>
    <a href="about_me.php">ℹ About Me</a>
    <a href="logout.php" class="logout">🚪 Logout</a>
</div>

<div class="content">

    <div class="header">
        <div>
            <h1>Edit Data Pelanggaran</h1>
            <p>Perbarui informasi pelanggaran siswa.</p>
        </div>
    </div>

    <div class="box">

        <?php if ($error !== ''): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST">

            <div class="mb-3">
                <label class="form-label">Nama Siswa</label>
                <select name="siswa_id" class="form-select" required>
                    <option value="">-- Pilih Siswa --</option>

                    <?php while ($item = mysqli_fetch_assoc($siswa)): ?>
                        <option
                            value="<?= $item['id']; ?>"
                            <?= (int)$item['id'] === (int)$data['siswa_id'] ? 'selected' : ''; ?>
                        >
                            <?= htmlspecialchars($item['nama']); ?>
                            - NIS <?= htmlspecialchars($item['nis']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Jenis Pelanggaran</label>
                <select name="jenis_id" class="form-select" required>
                    <option value="">-- Pilih Jenis Pelanggaran --</option>

                    <?php while ($item = mysqli_fetch_assoc($jenis)): ?>
                        <option
                            value="<?= $item['id']; ?>"
                            <?= (int)$item['id'] === (int)$data['jenis_id'] ? 'selected' : ''; ?>
                        >
                            <?= htmlspecialchars($item['nama']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Guru</label>
                <select name="guru_id" class="form-select">
                    <option value="">-- Tidak Dipilih --</option>

                    <?php while ($item = mysqli_fetch_assoc($guru)): ?>
                        <option
                            value="<?= $item['id']; ?>"
                            <?= (string)$item['id'] === (string)$data['guru_id'] ? 'selected' : ''; ?>
                        >
                            <?= htmlspecialchars($item['nama']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Tanggal</label>
                <input
                    type="date"
                    name="tanggal"
                    class="form-control"
                    value="<?= htmlspecialchars($data['tanggal']); ?>"
                    required
                >
            </div>

            <div class="mb-3">
                <label class="form-label">Keterangan</label>
                <textarea
                    name="keterangan"
                    class="form-control"
                    rows="4"
                ><?= htmlspecialchars($data['keterangan'] ?? ''); ?></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select" required>
                    <option value="tercatat"
                        <?= $data['status'] === 'tercatat' ? 'selected' : ''; ?>>
                        Tercatat
                    </option>

                    <option value="ditindaklanjuti"
                        <?= $data['status'] === 'ditindaklanjuti' ? 'selected' : ''; ?>>
                        Ditindaklanjuti
                    </option>

                    <option value="selesai"
                        <?= $data['status'] === 'selesai' ? 'selected' : ''; ?>>
                        Selesai
                    </option>
                </select>
            </div>

            <button type="submit" name="update" class="btn btn-simpan">
                Simpan Perubahan
            </button>

            <a href="pelanggaran.php" class="btn btn-secondary">
                Batal
            </a>

        </form>
    </div>

</div>

</body>
</html>
