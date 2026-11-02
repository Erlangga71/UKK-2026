
<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "database.php";
require_once "auth.php";

hanyaRole(['admin', 'guru', 'wali_kelas']);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$role = $_SESSION['role'] ?? '';

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
        $jenis_pesan = "error";

    } else {

        $siswa_id = (int)($_POST['siswa_id'] ?? 0);
        $jenis_id = (int)($_POST['jenis_id'] ?? 0);
        $tanggal = trim($_POST['tanggal'] ?? '');
        $keterangan = trim($_POST['keterangan'] ?? '');

        if ($siswa_id <= 0 || $jenis_id <= 0 || $tanggal == "") {

            $pesan = "Semua data wajib diisi.";
            $jenis_pesan = "error";

        } else {

            // CEK SISWA
            $stmt = $koneksi->prepare(
                "SELECT id FROM siswa WHERE id = ? AND aktif = 1"
            );

            $stmt->bind_param("i", $siswa_id);
            $stmt->execute();

            $cek_siswa = $stmt->get_result();
            $stmt->close();

            // CEK JENIS PELANGGARAN
            $stmt = $koneksi->prepare(
                "SELECT id FROM jenis_pelanggaran WHERE id = ?"
            );

            $stmt->bind_param("i", $jenis_id);
            $stmt->execute();

            $cek_jenis = $stmt->get_result();
            $stmt->close();

            if ($cek_siswa->num_rows == 0) {

                $pesan = "Data siswa tidak ditemukan.";
                $jenis_pesan = "error";

            } elseif ($cek_jenis->num_rows == 0) {

                $pesan = "Jenis pelanggaran tidak ditemukan.";
                $jenis_pesan = "error";

            } else {

                // CARI GURU BERDASARKAN EMAIL
                $guru_id = null;

                if ($role === 'guru') {

                    $email = $_SESSION['email'] ?? '';

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
                        $jenis_pesan = "error";
                    }
                }

                // SIMPAN DATA
                if ($jenis_pesan !== "error") {

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
                        $jenis_pesan = "error";

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

    <title>Catat Pelanggaran</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f6f8;
            color: #333;
        }

        /* SIDEBAR */

        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
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

        .logout {
            margin-top: 20px;
            background: #b42323;
        }

        /* CONTENT */

        .content {
            margin-left: 220px;
            padding: 30px;
        }

        /* HEADER */

        .header {
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
            margin-bottom: 0;
        }

        /* FORM BOX */

        .box {
            background: white;
            border: 1px solid #ddd;
            border-radius: 7px;
            padding: 25px;
            max-width: 850px;
        }

        .box h2 {
            margin-top: 0;
            margin-bottom: 8px;
        }

        .deskripsi {
            color: #777;
            font-size: 14px;
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-top: 17px;
            margin-bottom: 7px;
            font-weight: bold;
            font-size: 14px;
        }

        input,
        select,
        textarea {
            width: 100%;
            padding: 11px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
            font-family: Arial, sans-serif;
        }

        textarea {
            min-height: 100px;
            resize: vertical;
        }

        input:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: #687280;
        }

        button {
            margin-top: 22px;
            padding: 11px 20px;
            background: #087f5b;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }

        button:hover {
            background: #066747;
        }

        /* PESAN */

        .pesan {
            padding: 12px;
            margin-bottom: 15px;
            border-radius: 4px;
            font-size: 14px;
        }

        .success {
            background: #d1fae5;
            color: #065f46;
        }

        .error {
            background: #fee2e2;
            color: #991b1b;
        }

        /* RESPONSIVE */

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
        ⚖ Pelanggaran<br>
        Siswa
    </div>

    <?php if ($role == 'admin') { ?>

        <!-- MENU ADMIN -->

        <a href="dashboard.php">
            🏠 Dashboard
        </a>

        <a href="kelola_siswa.php">
            Data Siswa
        </a>

        <a href="kelola_guru.php">
            Data Guru
        </a>

        <a href="kelola_kelas.php">
            Data Kelas
        </a>

        <a href="kelola_tahun_ajaran.php">
            Tahun Ajaran
        </a>

        <a href="kelola_wali_kelas.php">
            Wali Kelas
        </a>

        <a href="penempatan_siswa.php">
            Penempatan Siswa
        </a>

        <a href="kelola_pelanggaran_kategori.php">
            Kategori Pelanggaran
        </a>

        <a href="pelanggaran.php">
            Data Pelanggaran
        </a>

        <a href="tindakan.php">
            Data Tindakan
        </a>

        <a href="laporan.php">
            Laporan
        </a>

        <a href="about_me.php">
            About Me
        </a>

    <?php } else { ?>

        <!-- MENU GURU DAN WALI KELAS -->

        <a href="dashboard.php">
            🏠 Dashboard
        </a>

        <a href="catat_pelanggaran.php" class="active">
            Catat Pelanggaran
        </a>

        <a href="tindakan.php">
            Tindakan
        </a>

        <a href="riwayat.php">
            Riwayat
        </a>

        <a href="rekap_poin.php">
            Rekap Poin
        </a>

    <?php } ?>

    <a href="logout.php" class="logout">
        🚪 Logout
    </a>

</div>


<!-- CONTENT -->

<div class="content">

    <div class="header">

        <h1>Catat Pelanggaran Siswa</h1>

        <p>
            Sistem Informasi Pelanggaran Siswa
        </p>

    </div>


    <div class="box">

        <h2>Form Catat Pelanggaran</h2>

        <p class="deskripsi">
            Silakan isi data pelanggaran siswa dengan benar.
        </p>

        <?php if ($pesan != ""): ?>

            <div class="pesan <?= htmlspecialchars($jenis_pesan) ?>">
                <?= htmlspecialchars($pesan) ?>
            </div>

        <?php endif; ?>


        <form method="POST">

            <input
                type="hidden"
                name="csrf_token"
                value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>"
            >

            <label>Pilih Siswa</label>

            <select name="siswa_id" required>

                <option value="">
                    -- Pilih Siswa --
                </option>

                <?php while ($siswa = mysqli_fetch_assoc($query_siswa)): ?>

                    <option value="<?= (int)$siswa['id'] ?>">

                        <?= htmlspecialchars($siswa['nis'] . " - " . $siswa['nama']) ?>

                    </option>

                <?php endwhile; ?>

            </select>


            <label>Jenis Pelanggaran</label>

            <select name="jenis_id" required>

                <option value="">
                    -- Pilih Pelanggaran --
                </option>

                <?php while ($jenis = mysqli_fetch_assoc($query_jenis)): ?>

                    <option value="<?= (int)$jenis['id'] ?>">

                        <?= htmlspecialchars($jenis['nama']) ?>
                        (<?= (int)$jenis['poin'] ?> poin)

                    </option>

                <?php endwhile; ?>

            </select>


            <label>Tanggal Pelanggaran</label>

            <input
                type="date"
                name="tanggal"
                value="<?= date('Y-m-d') ?>"
                required
            >


            <label>Keterangan</label>

            <textarea
                name="keterangan"
                placeholder="Masukkan keterangan pelanggaran..."
            ></textarea>


            <button type="submit">
                Simpan Pelanggaran
            </button>

        </form>

    </div>

</div>

</body>
</html>
