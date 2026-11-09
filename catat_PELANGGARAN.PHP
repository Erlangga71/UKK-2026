
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "database.php";
require_once "auth.php";

hanyaRole(['admin', 'guru']);

$role = $_SESSION['role'] ?? '';
$nama = $_SESSION['nama'] ?? 'Pengguna';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$pesan = "";
$jenis_pesan = "";

/* =========================
   PROSES SIMPAN PELANGGARAN
========================= */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (
        !isset($_POST['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
    ) {
        $pesan = "Permintaan tidak valid. Silakan muat ulang halaman.";
        $jenis_pesan = "danger";

    } else {

        $siswa_id = (int)($_POST['siswa_id'] ?? 0);
        $jenis_id = (int)($_POST['jenis_id'] ?? 0);
        $tanggal = trim($_POST['tanggal'] ?? '');
        $keterangan = trim($_POST['keterangan'] ?? '');

        if ($siswa_id <= 0 || $jenis_id <= 0 || $tanggal === '') {

            $pesan = "Semua data wajib diisi.";
            $jenis_pesan = "danger";

        } elseif (
            !DateTime::createFromFormat('Y-m-d', $tanggal) ||
            DateTime::createFromFormat('Y-m-d', $tanggal)->format('Y-m-d') !== $tanggal
        ) {

            $pesan = "Format tanggal tidak valid.";
            $jenis_pesan = "danger";

        } else {

            // CEK SISWA
            $stmt = $koneksi->prepare(
                "SELECT id FROM siswa WHERE id = ? AND aktif = 1"
            );

            if (!$stmt) {
                die("Query siswa gagal: " . $koneksi->error);
            }

            $stmt->bind_param("i", $siswa_id);
            $stmt->execute();
            $cek_siswa = $stmt->get_result();
            $stmt->close();

            // CEK JENIS PELANGGARAN
            $stmt = $koneksi->prepare(
                "SELECT id FROM jenis_pelanggaran WHERE id = ?"
            );

            if (!$stmt) {
                die("Query jenis pelanggaran gagal: " . $koneksi->error);
            }

            $stmt->bind_param("i", $jenis_id);
            $stmt->execute();
            $cek_jenis = $stmt->get_result();
            $stmt->close();

            if ($cek_siswa->num_rows === 0) {

                $pesan = "Data siswa tidak ditemukan atau tidak aktif.";
                $jenis_pesan = "danger";

            } elseif ($cek_jenis->num_rows === 0) {

                $pesan = "Jenis pelanggaran tidak ditemukan.";
                $jenis_pesan = "danger";

            } else {

                // CARI GURU BERDASARKAN EMAIL AKUN
                $guru_id = null;

                if ($role === 'guru') {

                    $email = $_SESSION['email'] ?? '';

                    if ($email === '') {
                        $pesan = "Email akun guru tidak ditemukan dalam sesi.";
                        $jenis_pesan = "danger";
                    } else {

                        $stmt = $koneksi->prepare(
                            "SELECT id FROM guru WHERE email = ? LIMIT 1"
                        );

                        if (!$stmt) {
                            die("Query guru gagal: " . $koneksi->error);
                        }

                        $stmt->bind_param("s", $email);
                        $stmt->execute();
                        $hasil_guru = $stmt->get_result();

                        if ($hasil_guru->num_rows > 0) {
                            $data_guru = $hasil_guru->fetch_assoc();
                            $guru_id = (int)$data_guru['id'];
                        }

                        $stmt->close();

                        if ($guru_id === null) {
                            $pesan = "Email akun guru belum terdaftar pada data guru. Periksa email di tabel users dan guru.";
                            $jenis_pesan = "danger";
                        }
                    }
                }

                // SIMPAN PELANGGARAN
                if ($jenis_pesan !== "danger") {

                    $stmt = $koneksi->prepare(
                        "INSERT INTO pelanggaran
                        (siswa_id, jenis_id, guru_id, tanggal, keterangan)
                        VALUES (?, ?, ?, ?, ?)"
                    );

                    if (!$stmt) {
                        die("Query simpan gagal: " . $koneksi->error);
                    }

                    $stmt->bind_param(
                        "iiiss",
                        $siswa_id,
                        $jenis_id,
                        $guru_id,
                        $tanggal,
                        $keterangan
                    );

                    if ($stmt->execute()) {

                        $pesan = "Data pelanggaran berhasil disimpan.";
                        $jenis_pesan = "success";

                        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

                    } else {

                        $pesan = "Gagal menyimpan data: " . $stmt->error;
                        $jenis_pesan = "danger";
                    }

                    $stmt->close();
                }
            }
        }
    }
}

/* =========================
   AMBIL DATA SISWA
========================= */

$query_siswa = mysqli_query(
    $koneksi,
    "SELECT id, nis, nama
     FROM siswa
     WHERE aktif = 1
     ORDER BY nama ASC"
);

if (!$query_siswa) {
    die("Gagal mengambil data siswa: " . mysqli_error($koneksi));
}

/* =========================
   AMBIL JENIS PELANGGARAN
========================= */

$query_jenis = mysqli_query(
    $koneksi,
    "SELECT id, nama, poin
     FROM jenis_pelanggaran
     ORDER BY nama ASC"
);

if (!$query_jenis) {
    die("Gagal mengambil jenis pelanggaran: " . mysqli_error($koneksi));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Catat Pelanggaran - Sistem Pelanggaran Siswa</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
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
            color: white;
            font-size: 14px;
            padding: 11px 13px;
            margin-bottom: 5px;
            border-radius: 5px;
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
            background: #941b1b;
        }

        .content {
            margin-left: 220px;
            padding: 30px;
        }

        .header-box,
        .main-box {
            background: white;
            border: 1px solid #ddd;
            border-radius: 7px;
        }

        .header-box {
            padding: 22px;
            margin-bottom: 20px;
        }

        .main-box {
           padding: 20px;
           width: 100%;
       }

        .form-label {
            font-weight: 600;
            font-size: 14px;
        }

        .form-control,
        .form-select {
            font-size: 14px;
        }

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
<div class="sidebar">

    <div class="sidebar-title">
        ⚖ Pelanggaran<br>
        Siswa
    </div>

    <nav class="nav flex-column">

        <a href="dashboard.php" class="nav-link">
            🏠 Dashboard
        </a>

        <?php if ($role === 'admin'): ?>

            <a href="kelola_siswa.php" class="nav-link">Data Siswa</a>
            <a href="kelola_guru.php" class="nav-link">Data Guru</a>
            <a href="kelola_kelas.php" class="nav-link">Data Kelas</a>
            <a href="kelola_tahun_ajaran.php" class="nav-link">Tahun Ajaran</a>
            <a href="kelola_wali_kelas.php" class="nav-link">Wali Kelas</a>
            <a href="penempatan_siswa.php" class="nav-link">Penempatan Siswa</a>
            <a href="kelola_pelanggaran_kategori.php" class="nav-link">Kategori Pelanggaran</a>
            <a href="pelanggaran.php" class="nav-link">Data Pelanggaran</a>
            <a href="tindakan.php" class="nav-link">Data Tindakan</a>
            <a href="laporan.php" class="nav-link">Laporan</a>
            <a href="about_me.php" class="nav-link">About Me</a>

        <?php else: ?>

            <a href="catat_pelanggaran.php" class="nav-link active">
                Catat Pelanggaran
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

        <?php endif; ?>

        <a href="logout.php" class="nav-link logout">
            🚪 Logout
        </a>

    </nav>
</div>

<!-- CONTENT -->
<div class="content">

    <div class="header-box">
        <h3 class="mb-1">Catat Pelanggaran Siswa</h3>
        <p class="text-secondary mb-0">
            Sistem Informasi Pelanggaran Siswa
        </p>
    </div>

    <div class="main-box">

        <h5 class="mb-2">Form Catat Pelanggaran</h5>

        <p class="text-secondary small mb-4">
            Silakan isi data pelanggaran siswa dengan benar.
        </p>

        <?php if ($pesan !== ''): ?>
            <div class="alert alert-<?= htmlspecialchars($jenis_pesan) ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($pesan) ?>

                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
            </div>
        <?php endif; ?>

        <form method="POST">

            <input
                type="hidden"
                name="csrf_token"
                value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>"
            >

            <div class="mb-3">
                <label class="form-label">Pilih Siswa</label>

                <select name="siswa_id" class="form-select" required>
                    <option value="">-- Pilih Siswa --</option>

                    <?php while ($siswa = mysqli_fetch_assoc($query_siswa)): ?>
                        <option value="<?= (int)$siswa['id'] ?>">
                            <?= htmlspecialchars($siswa['nis'] . " - " . $siswa['nama']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Jenis Pelanggaran</label>

                <select name="jenis_id" class="form-select" required>
                    <option value="">-- Pilih Pelanggaran --</option>

                    <?php while ($jenis = mysqli_fetch_assoc($query_jenis)): ?>
                        <option value="<?= (int)$jenis['id'] ?>">
                            <?= htmlspecialchars($jenis['nama']) ?>
                            (<?= (int)$jenis['poin'] ?> poin)
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Tanggal Pelanggaran</label>

                <input
                    type="date"
                    name="tanggal"
                    class="form-control"
                    value="<?= date('Y-m-d') ?>"
                    required
                >
            </div>

            <div class="mb-3">
                <label class="form-label">Keterangan</label>

                <textarea
                    name="keterangan"
                    class="form-control"
                    rows="4"
                    placeholder="Masukkan keterangan pelanggaran..."
                ></textarea>
            </div>

            <button type="submit" class="btn btn-success">
                Simpan Pelanggaran
            </button>

            <a href="dashboard.php" class="btn btn-secondary">
                Kembali
            </a>

        </form>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
