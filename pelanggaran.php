
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "database.php";
require_once "auth.php";

hanyaRole(['admin', 'guru', 'wali_kelas']);

$role = $_SESSION['role'] ?? '';
$nama = $_SESSION['nama'] ?? 'Pengguna';
$email = $_SESSION['email'] ?? '';

function bindParams($stmt, $types, &$params) {
    if (empty($params)) {
        return;
    }

    $refs = [];
    $refs[] = &$types;

    foreach ($params as $key => &$value) {
        $refs[] = &$value;
    }

    call_user_func_array([$stmt, 'bind_param'], $refs);
}

/* =========================
   CSRF TOKEN
========================= */

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$pesan = "";
$jenis_pesan = "";
$tampil_form = isset($_GET['tambah']) || isset($_POST['simpan']);

/* =========================
   DATA GURU LOGIN
========================= */

$guru_login_id = null;
$kelas_wali_id = null;
$tahun_aktif_id = null;
$nama_tahun_aktif = '';
$data_kelas = null;

if ($role !== 'admin') {

    if ($email === '') {
        die("Email akun tidak ditemukan dalam sesi login.");
    }

    $stmt = $koneksi->prepare(
        "SELECT guru_id FROM users WHERE email = ? LIMIT 1"
    );

    $stmt->bind_param("s", $email);
    $stmt->execute();

    $akun = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!empty($akun['guru_id'])) {
        $guru_login_id = (int)$akun['guru_id'];
    } else {

        $stmt = $koneksi->prepare(
            "SELECT id FROM guru WHERE email = ? LIMIT 1"
        );

        $stmt->bind_param("s", $email);
        $stmt->execute();

        $data_guru = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($data_guru) {
            $guru_login_id = (int)$data_guru['id'];
        }
    }

    if (!$guru_login_id) {
        die("Akun ini belum terhubung dengan data guru.");
    }
}

/* =========================
   TAHUN AJARAN AKTIF
========================= */

$stmt_tahun = $koneksi->prepare(
    "SELECT id, nama
     FROM tahun_ajaran
     WHERE aktif = 1
     ORDER BY id DESC
     LIMIT 1"
);

$stmt_tahun->execute();
$data_tahun = $stmt_tahun->get_result()->fetch_assoc();
$stmt_tahun->close();

if ($data_tahun) {
    $tahun_aktif_id = (int)$data_tahun['id'];
    $nama_tahun_aktif = $data_tahun['nama'];
}

/* =========================
   KELAS WALI KELAS
========================= */

if ($role === 'wali_kelas') {

    if (!$tahun_aktif_id) {
        die("Belum ada tahun ajaran aktif.");
    }

    $stmt_kelas = $koneksi->prepare(
        "SELECT w.kelas_id, k.nama_kelas
         FROM wali_kelas w
         JOIN kelas k ON w.kelas_id = k.id
         WHERE w.guru_id = ?
           AND w.tahun_ajaran_id = ?
         LIMIT 1"
    );

    $stmt_kelas->bind_param(
        "ii",
        $guru_login_id,
        $tahun_aktif_id
    );

    $stmt_kelas->execute();
    $data_kelas = $stmt_kelas->get_result()->fetch_assoc();
    $stmt_kelas->close();

    if (!$data_kelas) {
        die("Akun Wali Kelas belum memiliki penempatan pada tahun ajaran aktif.");
    }

    $kelas_wali_id = (int)$data_kelas['kelas_id'];
}

/* =========================
   CEK AKSES SISWA
========================= */

function siswaBolehDiakses(
    $koneksi,
    $role,
    $siswa_id,
    $kelas_wali_id,
    $tahun_aktif_id
) {
    if ($role === 'admin') {
        return true;
    }

    if ($role === 'wali_kelas') {

        $stmt = $koneksi->prepare(
            "SELECT id
             FROM penempatan_siswa
             WHERE siswa_id = ?
               AND kelas_id = ?
               AND tahun_ajaran_id = ?
             LIMIT 1"
        );

        $stmt->bind_param(
            "iii",
            $siswa_id,
            $kelas_wali_id,
            $tahun_aktif_id
        );

        $stmt->execute();
        $hasil = $stmt->get_result()->num_rows > 0;
        $stmt->close();

        return $hasil;
    }

    return true;
}

/* =========================
   PROSES SIMPAN
========================= */

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan'])) {

    $token = $_POST['csrf_token'] ?? '';

    if (!hash_equals($_SESSION['csrf_token'], $token)) {

        $pesan = "Permintaan tidak valid. Silakan muat ulang halaman.";
        $jenis_pesan = "danger";

    } else {

        $siswa_id = (int)($_POST['siswa_id'] ?? 0);
        $jenis_id = (int)($_POST['jenis_id'] ?? 0);
        $tanggal = trim($_POST['tanggal'] ?? '');
        $keterangan = trim($_POST['keterangan'] ?? '');
        $status = trim($_POST['status'] ?? 'tercatat');

        $status_valid = [
            'tercatat',
            'ditindaklanjuti',
            'selesai'
        ];

        $guru_id = null;

        if ($role === 'admin') {
            $pilihan_guru = (int)($_POST['guru_id'] ?? 0);
            $guru_id = $pilihan_guru > 0 ? $pilihan_guru : null;
        } else {
            $guru_id = $guru_login_id;
        }

        $tanggal_obj = DateTime::createFromFormat('!Y-m-d', $tanggal);

        if ($siswa_id <= 0 || $jenis_id <= 0 || $tanggal === '') {

            $pesan = "Siswa, jenis pelanggaran, dan tanggal harus diisi.";
            $jenis_pesan = "danger";

        } elseif (
            !$tanggal_obj ||
            $tanggal_obj->format('Y-m-d') !== $tanggal
        ) {

            $pesan = "Format tanggal tidak valid.";
            $jenis_pesan = "danger";

        } elseif (!in_array($status, $status_valid, true)) {

            $pesan = "Status pelanggaran tidak valid.";
            $jenis_pesan = "danger";

        } elseif (
            !siswaBolehDiakses(
                $koneksi,
                $role,
                $siswa_id,
                $kelas_wali_id,
                $tahun_aktif_id
            )
        ) {

            $pesan = "Kamu tidak memiliki akses untuk mencatat siswa tersebut.";
            $jenis_pesan = "danger";

        } else {

            $stmt = $koneksi->prepare(
                "SELECT id FROM siswa
                 WHERE id = ? AND aktif = 1"
            );

            $stmt->bind_param("i", $siswa_id);
            $stmt->execute();
            $cek_siswa = $stmt->get_result();
            $stmt->close();

            $stmt = $koneksi->prepare(
                "SELECT id FROM jenis_pelanggaran WHERE id = ?"
            );

            $stmt->bind_param("i", $jenis_id);
            $stmt->execute();
            $cek_jenis = $stmt->get_result();
            $stmt->close();

            $cek_guru_valid = true;

            if ($guru_id !== null) {

                $stmt = $koneksi->prepare(
                    "SELECT id FROM guru WHERE id = ?"
                );

                $stmt->bind_param("i", $guru_id);
                $stmt->execute();

                $cek_guru_valid = (
                    $stmt->get_result()->num_rows > 0
                );

                $stmt->close();
            }

            if ($cek_siswa->num_rows === 0) {

                $pesan = "Data siswa tidak ditemukan atau tidak aktif.";
                $jenis_pesan = "danger";

            } elseif ($cek_jenis->num_rows === 0) {

                $pesan = "Jenis pelanggaran tidak ditemukan.";
                $jenis_pesan = "danger";

            } elseif (!$cek_guru_valid) {

                $pesan = "Data guru tidak ditemukan.";
                $jenis_pesan = "danger";

            } else {

                $stmt = $koneksi->prepare(
                    "INSERT INTO pelanggaran
                    (siswa_id, jenis_id, guru_id, tanggal, keterangan, status)
                    VALUES (?, ?, ?, ?, ?, ?)"
                );

                if (!$stmt) {
                    die("Query simpan gagal: " . $koneksi->error);
                }

                $stmt->bind_param(
                    "iiisss",
                    $siswa_id,
                    $jenis_id,
                    $guru_id,
                    $tanggal,
                    $keterangan,
                    $status
                );

                if ($stmt->execute()) {

                    $stmt->close();

                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

                    header("Location: pelanggaran.php?berhasil=1");
                    exit;

                } else {

                    $pesan = "Gagal menyimpan data: " . $stmt->error;
                    $jenis_pesan = "danger";
                    $stmt->close();
                }
            }
        }
    }
}

/* =========================
   PROSES HAPUS
========================= */

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['hapus'])) {

    $token = $_POST['csrf_token'] ?? '';

    if (!hash_equals($_SESSION['csrf_token'], $token)) {

        $pesan = "Permintaan hapus tidak valid.";
        $jenis_pesan = "danger";

    } else {

        $id_hapus = (int)($_POST['id_hapus'] ?? 0);

        if ($id_hapus > 0) {

            $sql_hapus = "DELETE p FROM pelanggaran p
                          WHERE p.id = ?";

            $types_hapus = "i";
            $params_hapus = [$id_hapus];

            if ($role === 'guru') {
                $sql_hapus .= " AND p.guru_id = ?";
                $types_hapus .= "i";
                $params_hapus[] = $guru_login_id;
            }

            if ($role === 'wali_kelas') {
                $sql_hapus .= "
                    AND EXISTS (
                        SELECT 1
                        FROM penempatan_siswa ps
                        WHERE ps.siswa_id = p.siswa_id
                          AND ps.kelas_id = ?
                          AND ps.tahun_ajaran_id = ?
                    )";

                $types_hapus .= "ii";
                $params_hapus[] = $kelas_wali_id;
                $params_hapus[] = $tahun_aktif_id;
            }

            $stmt = $koneksi->prepare($sql_hapus);
            bindParams($stmt, $types_hapus, $params_hapus);

            if ($stmt->execute() && $stmt->affected_rows > 0) {

                $stmt->close();

                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

                header("Location: pelanggaran.php?dihapus=1");
                exit;

            } else {

                $pesan = "Data tidak ditemukan, tidak memiliki akses, atau sedang digunakan oleh data tindakan.";
                $jenis_pesan = "danger";
                $stmt->close();
            }

        } else {

            $pesan = "ID pelanggaran tidak valid.";
            $jenis_pesan = "danger";
        }
    }
}

/* =========================
   NOTIFIKASI
========================= */

if (isset($_GET['berhasil'])) {
    $pesan = "Data pelanggaran berhasil disimpan.";
    $jenis_pesan = "success";
}

if (isset($_GET['dihapus'])) {
    $pesan = "Data pelanggaran berhasil dihapus.";
    $jenis_pesan = "success";
}

/* =========================
   DROPDOWN SISWA
========================= */

if ($role === 'wali_kelas') {

    $stmt = $koneksi->prepare(
        "SELECT s.id, s.nis, s.nama
         FROM siswa s
         JOIN penempatan_siswa ps ON ps.siswa_id = s.id
         WHERE s.aktif = 1
           AND ps.kelas_id = ?
           AND ps.tahun_ajaran_id = ?
         ORDER BY s.nama ASC"
    );

    $stmt->bind_param("ii", $kelas_wali_id, $tahun_aktif_id);
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
}

if (!$query_siswa) {
    die("Gagal mengambil data siswa: " . $koneksi->error);
}

/* =========================
   DROPDOWN JENIS PELANGGARAN
========================= */

$query_jenis = mysqli_query(
    $koneksi,
    "SELECT
        j.id,
        j.nama,
        j.poin,
        k.nama AS nama_kategori
     FROM jenis_pelanggaran j
     LEFT JOIN kategori k ON j.kategori_id = k.id
     ORDER BY k.nama ASC, j.nama ASC"
);

if (!$query_jenis) {
    die("Gagal mengambil jenis pelanggaran: " . $koneksi->error);
}

/* =========================
   DROPDOWN GURU ADMIN
========================= */

$query_guru = null;

if ($role === 'admin') {

    $query_guru = mysqli_query(
        $koneksi,
        "SELECT id, nama
         FROM guru
         ORDER BY nama ASC"
    );

    if (!$query_guru) {
        die("Gagal mengambil data guru: " . $koneksi->error);
    }
}

/* =========================
   FILTER DATA
========================= */

$cari = trim($_GET['cari'] ?? '');
$cari_sql = "%" . $cari . "%";

$sql_filter = "
    FROM pelanggaran p
    LEFT JOIN siswa s ON p.siswa_id = s.id
    LEFT JOIN jenis_pelanggaran j ON p.jenis_id = j.id
    LEFT JOIN kategori k ON j.kategori_id = k.id
    LEFT JOIN guru g ON p.guru_id = g.id
    WHERE (
        s.nis LIKE ?
        OR s.nama LIKE ?
        OR j.nama LIKE ?
        OR k.nama LIKE ?
        OR g.nama LIKE ?
    )
";

$params = [
    $cari_sql,
    $cari_sql,
    $cari_sql,
    $cari_sql,
    $cari_sql
];

$types = "sssss";

if ($role === 'guru') {

    $sql_filter .= " AND p.guru_id = ?";
    $params[] = $guru_login_id;
    $types .= "i";

} elseif ($role === 'wali_kelas') {

    $sql_filter .= "
        AND EXISTS (
            SELECT 1
            FROM penempatan_siswa ps
            WHERE ps.siswa_id = p.siswa_id
              AND ps.kelas_id = ?
              AND ps.tahun_ajaran_id = ?
        )";

    $params[] = $kelas_wali_id;
    $params[] = $tahun_aktif_id;
    $types .= "ii";
}

/* =========================
   HITUNG DATA
========================= */

$stmt_total = $koneksi->prepare(
    "SELECT COUNT(*) AS total " . $sql_filter
);

bindParams($stmt_total, $types, $params);
$stmt_total->execute();

$total_data = (int)$stmt_total->get_result()->fetch_assoc()['total'];
$stmt_total->close();

/* =========================
   PAGINATION
========================= */

$per_halaman = 10;

$total_halaman = max(
    1,
    (int)ceil($total_data / $per_halaman)
);

$halaman = max(1, (int)($_GET['halaman'] ?? 1));

if ($halaman > $total_halaman) {
    $halaman = $total_halaman;
}

$mulai = ($halaman - 1) * $per_halaman;

/* =========================
   AMBIL DATA PELANGGARAN
========================= */

$sql_data = "
    SELECT
        p.id,
        s.nis,
        s.nama AS nama_siswa,
        j.nama AS nama_jenis,
        j.poin,
        k.nama AS nama_kategori,
        g.nama AS nama_guru,
        p.tanggal,
        p.keterangan,
        p.status
    " . $sql_filter . "
    ORDER BY p.id DESC
    LIMIT ? OFFSET ?
";

$params_data = $params;
$params_data[] = $per_halaman;
$params_data[] = $mulai;
$types_data = $types . "ii";

$stmt_data = $koneksi->prepare($sql_data);
bindParams($stmt_data, $types_data, $params_data);
$stmt_data->execute();

$query_pelanggaran = $stmt_data->get_result();

?>

<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Data Pelanggaran</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://cdn.jsdelivr.net/npm/tom-select@2.4.3/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">

<style>

body {
    margin: 0;
    background: #f4f6f8;
    font-family: Arial, sans-serif;
    font-size: 14px;
    color: #212529;
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

.content {
    margin-left: 220px;
    padding: 30px;
}

.card {
    border: 1px solid #ddd;
    border-radius: 6px;
    box-shadow: none;
}

.card-body {
    padding: 20px;
}

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

.table {
    font-size: 12px;
    margin-bottom: 10px;
}

.table th,
.table td {
    padding: 10px;
    vertical-align: middle;
}

.status {
    display: inline-block;
    padding: 5px 8px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: bold;
    white-space: nowrap;
}

.tercatat {
    background: #fff3cd;
    color: #856404;
}

.ditindaklanjuti {
    background: #d1ecf1;
    color: #0c5460;
}

.selesai {
    background: #d4edda;
    color: #155724;
}

.ts-control {
    min-height: 38px;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    font-size: 14px;
    padding: 8px 12px;
}

.ts-control input {
    font-size: 14px;
}

.ts-dropdown {
    font-size: 13px;
}

.ts-dropdown .option {
    padding: 9px 12px;
}

.ts-dropdown .active {
    background: #687280;
    color: white;
}

.page-link {
    color: #687280;
}

.page-item.active .page-link {
    background: #687280;
    border-color: #687280;
    color: white;
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

<div class="sidebar">

    <div class="sidebar-title">
        Pelanggaran<br>Siswa
    </div>

    <a href="dashboard.php">Dashboard</a>

    <?php if ($role === 'admin') { ?>

        <a href="kelola_siswa.php">Data Siswa</a>
        <a href="kelola_guru.php">Data Guru</a>
        <a href="kelola_kelas.php">Data Kelas</a>
        <a href="kelola_tahun_ajaran.php">Tahun Ajaran</a>
        <a href="kelola_wali_kelas.php">Wali Kelas</a>
        <a href="penempatan_siswa.php">Penempatan Siswa</a>
        <a href="kelola_pelanggaran_kategori.php">Kategori Pelanggaran</a>
        <a href="pelanggaran.php" class="active">Data Pelanggaran</a>
        <a href="laporan.php">Laporan</a>
        <a href="about_me.php">About Me</a>

    <?php } elseif ($role === 'guru') { ?>

        <a href="catat_pelanggaran.php">Catat Pelanggaran</a>
        <a href="pelanggaran.php" class="active">Data Pelanggaran</a>
        <a href="tindakan.php">Data Tindakan</a>
        <a href="riwayat.php">Riwayat</a>
        <a href="rekap_poin.php">Rekap Poin</a>

    <?php } elseif ($role === 'wali_kelas') { ?>

        <a href="pelanggaran.php" class="active">Data Pelanggaran</a>
        <a href="tindakan.php">Data Tindakan</a>
        <a href="riwayat.php">Riwayat</a>
        <a href="rekap_poin.php">Rekap Poin</a>

    <?php } ?>

    <a href="logout.php" class="logout">Logout</a>

</div>

<div class="content">

    <div class="card mb-3">
        <div class="card-body">

            <h4 class="mb-1">Data Pelanggaran</h4>

            <p class="text-muted mb-0">
                Kelola data pelanggaran siswa.
            </p>

            <?php if ($role === 'wali_kelas') { ?>

                <p class="mt-2 mb-0">
                    Kelas:
                    <strong><?= htmlspecialchars($data_kelas['nama_kelas']); ?></strong>
                    |
                    Tahun Ajaran:
                    <strong><?= htmlspecialchars($nama_tahun_aktif); ?></strong>
                </p>

            <?php } ?>

        </div>
    </div>

    <?php if ($pesan !== '') { ?>

        <div class="alert alert-<?= htmlspecialchars($jenis_pesan); ?>">
            <?= htmlspecialchars($pesan); ?>
        </div>

    <?php } ?>

    <?php if ($tampil_form) { ?>

        <div class="card mb-3">

            <div class="card-body">

                <h5 class="mb-3">Tambah Data Pelanggaran</h5>

                <form method="POST">

                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= htmlspecialchars($_SESSION['csrf_token']); ?>"
                    >

                    <div class="row g-3">

                        <div class="col-md-6">

                            <label class="form-label fw-bold">
                                Pilih Siswa
                            </label>

                            <select
                                name="siswa_id"
                                id="siswa_id"
                                class="form-select"
                                required
                            >

                                <option value="">
                                    Cari nama atau NIS siswa...
                                </option>

                                <?php while ($siswa = $query_siswa->fetch_assoc()) { ?>

                                    <option value="<?= (int)$siswa['id']; ?>">

                                        <?= htmlspecialchars(
                                            $siswa['nis'] . ' - ' . $siswa['nama']
                                        ); ?>

                                    </option>

                                <?php } ?>

                            </select>

                        </div>

                        <div class="col-md-6">

                            <label class="form-label fw-bold">
                                Jenis Pelanggaran
                            </label>

                            <select
                                name="jenis_id"
                                class="form-select"
                                required
                            >

                                <option value="">
                                    -- Pilih Jenis Pelanggaran --
                                </option>

                                <?php while ($jenis = mysqli_fetch_assoc($query_jenis)) { ?>

                                    <option value="<?= (int)$jenis['id']; ?>">

                                        <?= htmlspecialchars($jenis['nama_kategori'] ?? '-'); ?>
                                        -
                                        <?= htmlspecialchars($jenis['nama']); ?>
                                        (<?= (int)$jenis['poin']; ?> poin)

                                    </option>

                                <?php } ?>

                            </select>

                        </div>

                        <?php if ($role === 'admin') { ?>

                            <div class="col-md-6">

                                <label class="form-label fw-bold">
                                    Guru
                                </label>

                                <select name="guru_id" class="form-select">

                                    <option value="0">
                                        -- Pilih Guru (Opsional) --
                                    </option>

                                    <?php while ($guru = mysqli_fetch_assoc($query_guru)) { ?>

                                        <option value="<?= (int)$guru['id']; ?>">
                                            <?= htmlspecialchars($guru['nama']); ?>
                                        </option>

                                    <?php } ?>

                                </select>

                            </div>

                        <?php } else { ?>

                            <div class="col-md-6">

                                <label class="form-label fw-bold">
                                    Guru Pencatat
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    value="<?= htmlspecialchars($nama); ?>"
                                    readonly
                                >

                            </div>

                        <?php } ?>

                        <div class="col-md-6">

                            <label class="form-label fw-bold">
                                Tanggal
                            </label>

                            <input
                                type="date"
                                name="tanggal"
                                class="form-control"
                                value="<?= date('Y-m-d'); ?>"
                                required
                            >

                        </div>

                        <div class="col-md-6">

                            <label class="form-label fw-bold">
                                Status
                            </label>

                            <select name="status" class="form-select">

                                <option value="tercatat">Tercatat</option>
                                <option value="ditindaklanjuti">Ditindaklanjuti</option>
                                <option value="selesai">Selesai</option>

                            </select>

                        </div>

                        <div class="col-md-6">

                            <label class="form-label fw-bold">
                                Keterangan
                            </label>

                            <textarea
                                name="keterangan"
                                rows="3"
                                class="form-control"
                                placeholder="Keterangan pelanggaran..."
                            ></textarea>

                        </div>

                    </div>

                    <div class="mt-3">

                        <button
                            type="submit"
                            name="simpan"
                            class="btn btn-stabilo btn-sm"
                        >
                            Simpan Pelanggaran
                        </button>

                        <a
                            href="pelanggaran.php"
                            class="btn btn-outline-secondary btn-sm"
                        >
                            Batal
                        </a>

                    </div>

                </form>

            </div>

        </div>

    <?php } ?>

    <div class="card">

        <div class="card-body">

            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">

                <h5 class="mb-0">Daftar Pelanggaran</h5>

                <?php if (!$tampil_form) { ?>

                    <a
                        href="pelanggaran.php?tambah=1"
                        class="btn btn-sm btn-stabilo"
                    >
                        + Tambah Pelanggaran
                    </a>

                <?php } ?>

            </div>

            <form method="GET" class="row g-2 mb-3">

                <div class="col-md-5">

                    <input
                        type="text"
                        name="cari"
                        class="form-control form-control-sm"
                        placeholder="Cari NIS, siswa, kategori, jenis, atau guru..."
                        value="<?= htmlspecialchars($cari); ?>"
                    >

                </div>

                <div class="col-auto">

                    <button type="submit" class="btn btn-secondary btn-sm">
                        Cari
                    </button>

                    <?php if ($cari !== '') { ?>

                        <a
                            href="pelanggaran.php"
                            class="btn btn-outline-secondary btn-sm"
                        >
                            Reset
                        </a>

                    <?php } ?>

                </div>

            </form>

            <div class="table-responsive">

                <table class="table table-bordered table-hover align-middle">

                    <thead class="table-secondary">

                        <tr>
                            <th>No</th>
                            <th>NIS</th>
                            <th>Nama Siswa</th>
                            <th>Kategori</th>
                            <th>Jenis Pelanggaran</th>
                            <th>Poin</th>
                            <th>Guru</th>
                            <th>Tanggal</th>
                            <th>Keterangan</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>

                    </thead>

                    <tbody>

                    <?php if ($query_pelanggaran->num_rows > 0) { ?>

                        <?php $no = $mulai + 1; ?>

                        <?php while ($row = $query_pelanggaran->fetch_assoc()) { ?>

                            <?php
                            $status = $row['status'] ?? 'tercatat';

                            $kelas_status = in_array(
                                $status,
                                ['tercatat', 'ditindaklanjuti', 'selesai'],
                                true
                            ) ? $status : 'tercatat';
                            ?>

                            <tr>

                                <td><?= $no++; ?></td>

                                <td>
                                    <?= htmlspecialchars($row['nis'] ?? '-'); ?>
                                </td>

                                <td>
                                    <strong>
                                        <?= htmlspecialchars($row['nama_siswa'] ?? '-'); ?>
                                    </strong>
                                </td>

                                <td>
                                    <?= htmlspecialchars($row['nama_kategori'] ?? '-'); ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($row['nama_jenis'] ?? '-'); ?>
                                </td>

                                <td>
                                    <strong><?= (int)($row['poin'] ?? 0); ?></strong>
                                </td>

                                <td>
                                    <?= htmlspecialchars($row['nama_guru'] ?? '-'); ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($row['tanggal'] ?? '-'); ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($row['keterangan'] ?? '-'); ?>
                                </td>

                                <td>

                                    <span class="status <?= htmlspecialchars($kelas_status); ?>">
                                        <?= htmlspecialchars(ucfirst($status)); ?>
                                    </span>

                                </td>

                                <td class="text-nowrap">

                                    <?php if ($role === 'admin') { ?>

                                        <a
                                            href="edit_pelanggaran.php?id=<?= (int)$row['id']; ?>"
                                            class="btn btn-sm btn-stabilo"
                                        >
                                            Edit
                                        </a>

                                    <?php } ?>

                                    <form
                                        method="POST"
                                        class="d-inline"
                                        onsubmit="return confirm('Yakin ingin menghapus data pelanggaran ini?');"
                                    >

                                        <input
                                            type="hidden"
                                            name="csrf_token"
                                            value="<?= htmlspecialchars($_SESSION['csrf_token']); ?>"
                                        >

                                        <input
                                            type="hidden"
                                            name="id_hapus"
                                            value="<?= (int)$row['id']; ?>"
                                        >

                                        <button
                                            type="submit"
                                            name="hapus"
                                            class="btn btn-sm btn-danger"
                                        >
                                            Hapus
                                        </button>

                                    </form>

                                </td>

                            </tr>

                        <?php } ?>

                    <?php } else { ?>

                        <tr>

                            <td colspan="11" class="text-center py-4">
                                Belum ada data pelanggaran.
                            </td>

                        </tr>

                    <?php } ?>

                    </tbody>

                </table>

            </div>

            <div class="text-muted small">

                Menampilkan
                <?= $total_data > 0 ? $mulai + 1 : 0; ?>
                -
                <?= min($mulai + $per_halaman, $total_data); ?>
                dari
                <?= $total_data; ?>
                data pelanggaran.

            </div>

            <?php if ($total_halaman > 1) { ?>

                <nav class="mt-3">

                    <ul class="pagination pagination-sm mb-0">

                        <?php if ($halaman > 1) { ?>

                            <li class="page-item">

                                <a
                                    class="page-link"
                                    href="?halaman=<?= $halaman - 1; ?>&cari=<?= urlencode($cari); ?>"
                                >
                                    &laquo;
                                </a>

                            </li>

                        <?php } ?>

                        <?php for ($i = 1; $i <= $total_halaman; $i++) { ?>

                            <li class="page-item <?= $i == $halaman ? 'active' : ''; ?>">

                                <a
                                    class="page-link"
                                    href="?halaman=<?= $i; ?>&cari=<?= urlencode($cari); ?>"
                                >
                                    <?= $i; ?>
                                </a>

                            </li>

                        <?php } ?>

                        <?php if ($halaman < $total_halaman) { ?>

                            <li class="page-item">

                                <a
                                    class="page-link"
                                    href="?halaman=<?= $halaman + 1; ?>&cari=<?= urlencode($cari); ?>"
                                >
                                    &raquo;
                                </a>

                            </li>

                        <?php } ?>

                    </ul>

                </nav>

            <?php } ?>

        </div>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/tom-select@2.4.3/dist/js/tom-select.complete.min.js"></script>

<script>

document.addEventListener("DOMContentLoaded", function () {

    const pilihanSiswa = document.getElementById("siswa_id");

    if (pilihanSiswa) {

        new TomSelect("#siswa_id", {
            create: false,
            maxOptions: 100,
            placeholder: "Cari nama atau NIS siswa...",
            searchField: ["text"],
            sortField: {
                field: "text",
                direction: "asc"
            },
            allowEmptyOption: true
        });

    }

});

</script>

</body>
</html>

<?php
$stmt_data->close();
?>
