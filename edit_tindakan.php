
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

require_once "database.php";
require_once "auth.php";

// =========================
// CEK LOGIN DAN ROLE
// =========================
hanyaRole(['admin']);

$error = "";

// =========================
// TOKEN CSRF
// =========================
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$csrf_token = $_SESSION['csrf_token'];

// =========================
// CEK ID
// =========================
$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    header("Location: tindakan.php");
    exit;
}

// =========================
// AMBIL DATA TINDAKAN
// =========================
$stmt = mysqli_prepare(
    $koneksi,
    "SELECT * FROM tindakan WHERE id = ? LIMIT 1"
);

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

mysqli_stmt_close($stmt);

if (!$data) {
    die("Data tindakan tidak ditemukan.");
}

// =========================
// PROSES UPDATE
// =========================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan'])) {

    $token = $_POST['csrf_token'] ?? '';

    if (!hash_equals($csrf_token, $token)) {
        $error = "Permintaan tidak valid. Silakan muat ulang halaman.";
    } else {

        $pelanggaran_id = (int)($_POST['pelanggaran_id'] ?? 0);
        $tanggal = trim($_POST['tanggal'] ?? '');
        $tindakan = trim($_POST['tindakan'] ?? '');
        $catatan = trim($_POST['catatan'] ?? '');

        $petugas_id = ($_POST['petugas_id'] ?? '') === ''
            ? null
            : (int)$_POST['petugas_id'];

        // Validasi input wajib
        if ($pelanggaran_id <= 0 || $tanggal === '' || $tindakan === '') {
            $error = "Pelanggaran, tanggal, dan tindakan wajib diisi.";
        } elseif (
            !DateTime::createFromFormat('!Y-m-d', $tanggal) ||
            DateTime::createFromFormat('!Y-m-d', $tanggal)->format('Y-m-d') !== $tanggal
        ) {
            $error = "Format tanggal tidak valid.";
        } else {

            // Pastikan pelanggaran tersedia
            $cek_pelanggaran = mysqli_prepare(
                $koneksi,
                "SELECT id FROM pelanggaran WHERE id = ? LIMIT 1"
            );

            mysqli_stmt_bind_param($cek_pelanggaran, "i", $pelanggaran_id);
            mysqli_stmt_execute($cek_pelanggaran);

            $hasil_pelanggaran = mysqli_stmt_get_result($cek_pelanggaran);
            $pelanggaran_ada = mysqli_num_rows($hasil_pelanggaran) > 0;

            mysqli_stmt_close($cek_pelanggaran);

            if (!$pelanggaran_ada) {
                $error = "Data pelanggaran tidak ditemukan.";
            }

            // Pastikan petugas tersedia jika dipilih
            if ($error === '' && $petugas_id !== null) {

                $cek_petugas = mysqli_prepare(
                    $koneksi,
                    "SELECT id FROM users WHERE id = ? AND aktif = 1 LIMIT 1"
                );

                mysqli_stmt_bind_param($cek_petugas, "i", $petugas_id);
                mysqli_stmt_execute($cek_petugas);

                $hasil_petugas = mysqli_stmt_get_result($cek_petugas);
                $petugas_ada = mysqli_num_rows($hasil_petugas) > 0;

                mysqli_stmt_close($cek_petugas);

                if (!$petugas_ada) {
                    $error = "Petugas tidak ditemukan atau sudah tidak aktif.";
                }
            }

            // Simpan perubahan
            if ($error === '') {

                $stmt = mysqli_prepare(
                    $koneksi,
                    "UPDATE tindakan SET
                        pelanggaran_id = ?,
                        tanggal = ?,
                        tindakan = ?,
                        petugas_id = ?,
                        catatan = ?
                     WHERE id = ?"
                );

                mysqli_stmt_bind_param(
                    $stmt,
                    "issisi",
                    $pelanggaran_id,
                    $tanggal,
                    $tindakan,
                    $petugas_id,
                    $catatan,
                    $id
                );

                if (mysqli_stmt_execute($stmt)) {
                    mysqli_stmt_close($stmt);

                    header("Location: tindakan.php?berhasil=edit");
                    exit;
                } else {
                    $error = "Gagal menyimpan perubahan: " . mysqli_stmt_error($stmt);
                    mysqli_stmt_close($stmt);
                }
            }
        }

        // Pertahankan input jika terjadi kesalahan
        $data['pelanggaran_id'] = $pelanggaran_id;
        $data['tanggal'] = $tanggal;
        $data['tindakan'] = $tindakan;
        $data['petugas_id'] = $petugas_id;
        $data['catatan'] = $catatan;
    }
}

// =========================
// DATA PELANGGARAN
// =========================
$query_pelanggaran = mysqli_query(
    $koneksi,
    "SELECT
        p.id,
        s.nama AS nama_siswa,
        jp.nama AS jenis_pelanggaran
     FROM pelanggaran p
     INNER JOIN siswa s ON p.siswa_id = s.id
     INNER JOIN jenis_pelanggaran jp ON p.jenis_id = jp.id
     ORDER BY p.id DESC"
);

if (!$query_pelanggaran) {
    die("Gagal mengambil data pelanggaran: " . mysqli_error($koneksi));
}

// =========================
// DATA PETUGAS
// =========================
$query_petugas = mysqli_prepare(
    $koneksi,
    "SELECT id, nama, email
     FROM users
     WHERE aktif = 1 OR id = ?
     ORDER BY nama ASC"
);

mysqli_stmt_bind_param(
    $query_petugas,
    "i",
    $data['petugas_id']
);

mysqli_stmt_execute($query_petugas);

$result_petugas = mysqli_stmt_get_result($query_petugas);

if (!$result_petugas) {
    die("Gagal mengambil data petugas.");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Data Tindakan</title>

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
            min-height: 100vh;
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

        .form-label {
            font-weight: bold;
            font-size: 14px;
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

<!-- SIDEBAR ADMIN -->
<div class="sidebar">

    <div class="sidebar-title">
        ⚖ Pelanggaran<br>Siswa
    </div>

    <a href="dashboard.php">🏠 Dashboard</a>
    <a href="kelola_siswa.php">Data Siswa</a>
    <a href="kelola_guru.php">Data Guru</a>
    <a href="kelola_kelas.php">Data Kelas</a>
    <a href="kelola_tahun_ajaran.php">Tahun Ajaran</a>
    <a href="kelola_wali_kelas.php">Wali Kelas</a>
    <a href="penempatan_siswa.php">Penempatan Siswa</a>
    <a href="kelola_pelanggaran_kategori.php">⚠ Kategori Pelanggaran</a>
    <a href="pelanggaran.php">Data Pelanggaran</a>
    <a href="tindakan.php" class="active">Data Tindakan</a>
    <a href="laporan.php">Laporan</a>
    <a href="about_me.php">ℹ About Me</a>
    <a href="logout.php" class="logout">🚪 Logout</a>

</div>

<!-- KONTEN -->
<div class="content">

    <div class="header">
        <h1>Edit Data Tindakan</h1>
        <p>Perbarui tindakan terhadap pelanggaran siswa.</p>
    </div>

    <div class="box">

        <?php if ($error !== ''): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST">

            <input
                type="hidden"
                name="csrf_token"
                value="<?= htmlspecialchars($csrf_token); ?>"
            >

            <div class="mb-3">
                <label class="form-label">Pelanggaran Siswa</label>

                <select name="pelanggaran_id" class="form-select" required>
                    <option value="">-- Pilih Pelanggaran --</option>

                    <?php while ($pel = mysqli_fetch_assoc($query_pelanggaran)): ?>
                        <option
                            value="<?= (int)$pel['id']; ?>"
                            <?= (int)$pel['id'] === (int)$data['pelanggaran_id'] ? 'selected' : ''; ?>
                        >
                            <?= htmlspecialchars($pel['nama_siswa']); ?>
                            -
                            <?= htmlspecialchars($pel['jenis_pelanggaran']); ?>
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
                <label class="form-label">Tindakan</label>

                <textarea
                    name="tindakan"
                    class="form-control"
                    rows="3"
                    placeholder="Masukkan tindakan"
                    required
                ><?= htmlspecialchars($data['tindakan']); ?></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Petugas / Guru</label>

                <select name="petugas_id" class="form-select">
                    <option value="">-- Tidak Dipilih --</option>

                    <?php while ($petugas = mysqli_fetch_assoc($result_petugas)): ?>
                        <option
                            value="<?= (int)$petugas['id']; ?>"
                            <?= (string)$petugas['id'] === (string)$data['petugas_id'] ? 'selected' : ''; ?>
                        >
                            <?= htmlspecialchars($petugas['nama']); ?>
                            -
                            <?= htmlspecialchars($petugas['email'] ?? '-'); ?>
                        </option>
                    <?php endwhile; ?>

                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Catatan</label>

                <textarea
                    name="catatan"
                    class="form-control"
                    rows="3"
                    placeholder="Catatan tambahan (opsional)"
                ><?= htmlspecialchars($data['catatan'] ?? ''); ?></textarea>
            </div>

            <button
                type="submit"
                name="simpan"
                class="btn btn-simpan"
            >
                Simpan Perubahan
            </button>

            <a href="tindakan.php" class="btn btn-secondary">
                Batal
            </a>

        </form>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
