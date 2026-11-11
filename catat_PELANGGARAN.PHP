
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "database.php";
require_once "auth.php";

// ADMIN, GURU, DAN WALI KELAS BOLEH MENGAKSES
hanyaRole(['admin', 'guru', 'wali_kelas']);

$role = $_SESSION['role'] ?? '';
$nama = $_SESSION['nama'] ?? 'Pengguna';
$email = $_SESSION['email'] ?? '';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$pesan = "";
$jenis_pesan = "";

$guru_id = null;
$kelas_id = 0;
$tahun_ajaran_id = 0;
$nama_kelas = "";

/* =========================
   TAHUN AJARAN AKTIF
========================= */

$query_tahun = mysqli_query(
    $koneksi,
    "SELECT id, nama
     FROM tahun_ajaran
     WHERE aktif = 1
     LIMIT 1"
);

$nama_tahun = "";

if ($query_tahun && mysqli_num_rows($query_tahun) > 0) {
    $data_tahun = mysqli_fetch_assoc($query_tahun);
    $tahun_ajaran_id = (int) $data_tahun['id'];
    $nama_tahun = $data_tahun['nama'];
}

/* =========================
   IDENTITAS GURU / WALI KELAS
========================= */

if ($role === 'guru' || $role === 'wali_kelas') {

    // Cari guru_id dari akun users.
    $stmt = $koneksi->prepare(
        "SELECT guru_id
         FROM users
         WHERE email = ?
         LIMIT 1"
    );

    if (!$stmt) {
        die("Query akun gagal: " . $koneksi->error);
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $hasil = $stmt->get_result();

    if ($hasil && $hasil->num_rows > 0) {
        $data = $hasil->fetch_assoc();
        $guru_id = (int) ($data['guru_id'] ?? 0);
    }

    $stmt->close();

    // Jika guru_id belum terhubung, cari berdasarkan email.
    if (!$guru_id) {

        $stmt = $koneksi->prepare(
            "SELECT id
             FROM guru
             WHERE email = ?
             LIMIT 1"
        );

        if (!$stmt) {
            die("Query guru gagal: " . $koneksi->error);
        }

        $stmt->bind_param("s", $email);
        $stmt->execute();
        $hasil = $stmt->get_result();

        if ($hasil && $hasil->num_rows > 0) {
            $data = $hasil->fetch_assoc();
            $guru_id = (int) $data['id'];
        }

        $stmt->close();
    }

    if (!$guru_id) {
        die("Akun belum terhubung dengan data guru. Hubungi administrator.");
    }
}

/* =========================
   IDENTITAS KELAS WALI KELAS
========================= */

if ($role === 'wali_kelas') {

    if ($tahun_ajaran_id <= 0) {
        die("Belum ada tahun ajaran aktif. Hubungi administrator.");
    }

    $stmt = $koneksi->prepare(
        "SELECT wk.kelas_id, k.nama_kelas
         FROM wali_kelas wk
         JOIN kelas k ON k.id = wk.kelas_id
         WHERE wk.guru_id = ?
           AND wk.tahun_ajaran_id = ?
         LIMIT 1"
    );

    if (!$stmt) {
        die("Query kelas gagal: " . $koneksi->error);
    }

    $stmt->bind_param(
        "ii",
        $guru_id,
        $tahun_ajaran_id
    );

    $stmt->execute();
    $hasil = $stmt->get_result();

    if ($hasil && $hasil->num_rows > 0) {
        $data = $hasil->fetch_assoc();
        $kelas_id = (int) $data['kelas_id'];
        $nama_kelas = $data['nama_kelas'];
    }

    $stmt->close();

    if ($kelas_id <= 0) {
        die("Anda belum ditetapkan sebagai wali kelas pada tahun ajaran aktif.");
    }
}

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

        $siswa_id = (int) ($_POST['siswa_id'] ?? 0);
        $jenis_id = (int) ($_POST['jenis_id'] ?? 0);
        $tanggal = trim($_POST['tanggal'] ?? '');
        $keterangan = trim($_POST['keterangan'] ?? '');

        $isi_tindakan = trim($_POST['tindakan'] ?? '');
        $catatan_tindakan = trim($_POST['catatan_tindakan'] ?? '');

        if ($siswa_id <= 0 || $jenis_id <= 0 || $tanggal === '') {

            $pesan = "Siswa, jenis pelanggaran, dan tanggal wajib diisi.";
            $jenis_pesan = "danger";

        } elseif (
            !DateTime::createFromFormat('Y-m-d', $tanggal) ||
            DateTime::createFromFormat('Y-m-d', $tanggal)->format('Y-m-d') !== $tanggal
        ) {

            $pesan = "Format tanggal tidak valid.";
            $jenis_pesan = "danger";

        } else {

            /* CEK SISWA */

            if ($role === 'wali_kelas') {

                $stmt = $koneksi->prepare(
                    "SELECT s.id
                     FROM siswa s
                     JOIN penempatan_siswa ps
                        ON ps.siswa_id = s.id
                     WHERE s.id = ?
                       AND s.aktif = 1
                       AND ps.kelas_id = ?
                       AND ps.tahun_ajaran_id = ?
                     LIMIT 1"
                );

                if (!$stmt) {
                    die("Query siswa gagal: " . $koneksi->error);
                }

                $stmt->bind_param(
                    "iii",
                    $siswa_id,
                    $kelas_id,
                    $tahun_ajaran_id
                );

            } else {

                $stmt = $koneksi->prepare(
                    "SELECT id
                     FROM siswa
                     WHERE id = ?
                       AND aktif = 1
                     LIMIT 1"
                );

                if (!$stmt) {
                    die("Query siswa gagal: " . $koneksi->error);
                }

                $stmt->bind_param("i", $siswa_id);
            }

            $stmt->execute();
            $cek_siswa = $stmt->get_result();
            $stmt->close();

            /* CEK JENIS PELANGGARAN */

            $stmt = $koneksi->prepare(
                "SELECT id
                 FROM jenis_pelanggaran
                 WHERE id = ?
                 LIMIT 1"
            );

            if (!$stmt) {
                die("Query jenis pelanggaran gagal: " . $koneksi->error);
            }

            $stmt->bind_param("i", $jenis_id);
            $stmt->execute();
            $cek_jenis = $stmt->get_result();
            $stmt->close();

            if ($cek_siswa->num_rows === 0) {

                $pesan = "Siswa tidak ditemukan, tidak aktif, atau bukan siswa kelas Anda.";
                $jenis_pesan = "danger";

            } elseif ($cek_jenis->num_rows === 0) {

                $pesan = "Jenis pelanggaran tidak ditemukan.";
                $jenis_pesan = "danger";

            } else {

                $koneksi->begin_transaction();

                try {

                    /* SIMPAN PELANGGARAN */

                    $stmt = $koneksi->prepare(
                        "INSERT INTO pelanggaran
                        (siswa_id, jenis_id, guru_id, tanggal, keterangan)
                        VALUES (?, ?, ?, ?, ?)"
                    );

                    if (!$stmt) {
                        throw new Exception(
                            "Query simpan pelanggaran gagal: " . $koneksi->error
                        );
                    }

                    $stmt->bind_param(
                        "iiiss",
                        $siswa_id,
                        $jenis_id,
                        $guru_id,
                        $tanggal,
                        $keterangan
                    );

                    if (!$stmt->execute()) {
                        throw new Exception(
                            "Gagal menyimpan pelanggaran: " . $stmt->error
                        );
                    }

                    $pelanggaran_id = $koneksi->insert_id;

                    $stmt->close();

                    /* SIMPAN TINDAKAN JIKA DIISI */

                    if ($isi_tindakan !== '') {

                        // Cari ID akun petugas berdasarkan email login.
                        $petugas_id = null;

                        $stmt = $koneksi->prepare(
                            "SELECT id
                             FROM users
                             WHERE email = ?
                             LIMIT 1"
                        );

                        if ($stmt) {
                            $stmt->bind_param("s", $email);
                            $stmt->execute();
                            $hasil_petugas = $stmt->get_result();

                            if ($hasil_petugas && $hasil_petugas->num_rows > 0) {
                                $data_petugas = $hasil_petugas->fetch_assoc();
                                $petugas_id = (int) $data_petugas['id'];
                            }

                            $stmt->close();
                        }

                        $stmt = $koneksi->prepare(
                            "INSERT INTO tindakan
                            (pelanggaran_id, tanggal, tindakan, petugas_id, catatan)
                            VALUES (?, ?, ?, ?, ?)"
                        );

                        if (!$stmt) {
                            throw new Exception(
                                "Query simpan tindakan gagal: " . $koneksi->error
                            );
                        }

                        $stmt->bind_param(
                            "issis",
                            $pelanggaran_id,
                            $tanggal,
                            $isi_tindakan,
                            $petugas_id,
                            $catatan_tindakan
                        );

                        if (!$stmt->execute()) {
                            throw new Exception(
                                "Gagal menyimpan tindakan: " . $stmt->error
                            );
                        }

                        $stmt->close();
                    }

                    $koneksi->commit();

                    if ($isi_tindakan !== '') {
                        $pesan = "Data pelanggaran dan tindakan berhasil disimpan.";
                    } else {
                        $pesan = "Data pelanggaran berhasil disimpan.";
                    }

                    $jenis_pesan = "success";

                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

                } catch (Exception $e) {

                    $koneksi->rollback();

                    $pesan = $e->getMessage();
                    $jenis_pesan = "danger";
                }
            }
        }
    }
}

/* =========================
   AMBIL DATA SISWA
========================= */

if ($role === 'wali_kelas') {

    $stmt = $koneksi->prepare(
        "SELECT DISTINCT s.id, s.nis, s.nama
         FROM siswa s
         JOIN penempatan_siswa ps
            ON ps.siswa_id = s.id
         WHERE s.aktif = 1
           AND ps.kelas_id = ?
           AND ps.tahun_ajaran_id = ?
         ORDER BY s.nama ASC"
    );

    if (!$stmt) {
        die("Gagal mengambil data siswa: " . $koneksi->error);
    }

    $stmt->bind_param(
        "ii",
        $kelas_id,
        $tahun_ajaran_id
    );

    $stmt->execute();
    $query_siswa = $stmt->get_result();

} else {

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
}

/* =========================
   JENIS PELANGGARAN
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

    <title>Catat Pelanggaran - Pelanggaran Siswa</title>

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

        .section-title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 15px;
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
        Pelanggaran<br>Siswa
    </div>

    <nav class="nav flex-column">

        <a href="dashboard.php" class="nav-link">
            Dashboard
        </a>

        <?php if ($role === 'admin') { ?>

            <a href="kelola_siswa.php" class="nav-link">Data Siswa</a>
            <a href="kelola_guru.php" class="nav-link">Data Guru</a>
            <a href="kelola_kelas.php" class="nav-link">Data Kelas</a>
            <a href="kelola_tahun_ajaran.php" class="nav-link">Tahun Ajaran</a>
            <a href="kelola_wali_kelas.php" class="nav-link">Wali Kelas</a>
            <a href="penempatan_siswa.php" class="nav-link">Penempatan Siswa</a>
            <a href="kelola_pelanggaran_kategori.php" class="nav-link">Kategori Pelanggaran</a>
            <a href="pelanggaran.php" class="nav-link">Data Pelanggaran</a>
            <a href="laporan.php" class="nav-link">Laporan</a>
            <a href="about_me.php" class="nav-link">About Me</a>

        <?php } elseif ($role === 'guru') { ?>

            <a href="catat_pelanggaran.php" class="nav-link active">Catat Pelanggaran</a>
            <a href="tindakan.php" class="nav-link">Tindakan</a>
            <a href="riwayat.php" class="nav-link">Riwayat</a>
            <a href="rekap_poin.php" class="nav-link">Rekap Poin</a>

        <?php } elseif ($role === 'wali_kelas') { ?>

            <a href="catat_pelanggaran.php" class="nav-link active">Catat Pelanggaran</a>
            <a href="tindakan.php" class="nav-link">Tindakan</a>
            <a href="riwayat.php" class="nav-link">Riwayat</a>
            <a href="rekap_poin.php" class="nav-link">Rekap Poin</a>
            <a href="laporan.php" class="nav-link">Laporan</a>

        <?php } ?>

        <a href="logout.php" class="nav-link logout">
            Logout
        </a>

    </nav>

</div>

<!-- KONTEN -->

<div class="content">

    <div class="header-box">

        <h3 class="mb-1">Catat Pelanggaran Siswa</h3>

        <p class="text-secondary mb-2">
            Sistem Informasi Pelanggaran Siswa
        </p>

        <small>
            Login sebagai: <strong><?= htmlspecialchars($nama); ?></strong>
            (<?= htmlspecialchars($role); ?>)
        </small>

        <?php if ($role === 'wali_kelas') { ?>
            <br>
            <small>
                Kelas: <strong><?= htmlspecialchars($nama_kelas); ?></strong>
                | Tahun Ajaran: <strong><?= htmlspecialchars($nama_tahun); ?></strong>
            </small>
        <?php } ?>

    </div>

    <div class="main-box">

        <h5 class="mb-2">Form Catat Pelanggaran</h5>

        <p class="text-secondary small mb-4">
            Isi data pelanggaran dan tindakan yang diberikan kepada siswa.
        </p>

        <?php if ($pesan !== '') { ?>

            <div class="alert alert-<?= htmlspecialchars($jenis_pesan); ?> alert-dismissible fade show">

                <?= htmlspecialchars($pesan); ?>

                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>

            </div>

        <?php } ?>

        <form method="POST">

            <input
                type="hidden"
                name="csrf_token"
                value="<?= htmlspecialchars($_SESSION['csrf_token']); ?>"
            >

            <div class="section-title">
                Data Pelanggaran
            </div>

            <div class="mb-3">

                <label class="form-label">Pilih Siswa</label>

                <select name="siswa_id" class="form-select" required>

                    <option value="">-- Pilih Siswa --</option>

                    <?php while ($siswa = $query_siswa->fetch_assoc()) { ?>

                        <option value="<?= (int) $siswa['id']; ?>">

                            <?= htmlspecialchars($siswa['nis'] . " - " . $siswa['nama']); ?>

                        </option>

                    <?php } ?>

                </select>

            </div>

            <div class="mb-3">

                <label class="form-label">Jenis Pelanggaran</label>

                <select name="jenis_id" class="form-select" required>

                    <option value="">-- Pilih Pelanggaran --</option>

                    <?php while ($jenis = mysqli_fetch_assoc($query_jenis)) { ?>

                        <option value="<?= (int) $jenis['id']; ?>">

                            <?= htmlspecialchars($jenis['nama']); ?>
                            (<?= (int) $jenis['poin']; ?> poin)

                        </option>

                    <?php } ?>

                </select>

            </div>

            <div class="mb-3">

                <label class="form-label">Tanggal Pelanggaran</label>

                <input
                    type="date"
                    name="tanggal"
                    class="form-control"
                    value="<?= date('Y-m-d'); ?>"
                    required
                >

            </div>

            <div class="mb-3">

                <label class="form-label">Keterangan Pelanggaran</label>

                <textarea
                    name="keterangan"
                    class="form-control"
                    rows="3"
                    placeholder="Masukkan keterangan pelanggaran..."
                ></textarea>

            </div>

            <hr class="my-4">

            <div class="section-title">
                Tindakan yang Diberikan
            </div>

            <div class="mb-3">

                <label class="form-label">Tindakan</label>

                <textarea
                    name="tindakan"
                    class="form-control"
                    rows="3"
                    placeholder="Contoh: Teguran lisan, pembinaan, pemanggilan orang tua..."
                ></textarea>

                <small class="text-secondary">
                    Boleh dikosongkan jika tindakan belum diberikan.
                </small>

            </div>

            <div class="mb-4">

                <label class="form-label">Catatan Tindakan</label>

                <textarea
                    name="catatan_tindakan"
                    class="form-control"
                    rows="3"
                    placeholder="Masukkan catatan tambahan jika ada..."
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
