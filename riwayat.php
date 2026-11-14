
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "database.php";
require_once "auth.php";

// Role login hanya Admin dan Guru
hanyaRole(['admin', 'guru']);

$role = $_SESSION['role'] ?? '';
$nama = $_SESSION['nama'] ?? 'Pengguna';
$cari = trim($_GET['cari'] ?? '');

// =====================================
// FUNGSI BIND PARAMETER DINAMIS
// =====================================
if (!function_exists('bindParameter')) {
    function bindParameter($stmt, $tipe, &$parameter)
    {
        if ($tipe === '' || count($parameter) === 0) {
            return true;
        }

        $referensi = [];

        foreach ($parameter as $key => &$value) {
            $referensi[$key] = &$value;
        }

        return call_user_func_array(
            'mysqli_stmt_bind_param',
            array_merge([$stmt, $tipe], $referensi)
        );
    }
}

// =====================================
// FILTER AKSES BERDASARKAN PETUGAS
// =====================================
$whereParts = [];
$parameter = [];
$tipe = '';

if ($role === 'guru') {

    $id_petugas = 0;

    // Ambil ID akun Guru berdasarkan email login
    $email = trim($_SESSION['email'] ?? '');

    if ($email !== '') {
        $stmt_user = mysqli_prepare($koneksi, "
            SELECT id
            FROM users
            WHERE email = ?
              AND aktif = 1
              AND role IN ('guru', 'wali_kelas')
            LIMIT 1
        ");

        if (!$stmt_user) {
            die("Gagal mengambil akun Guru: " . mysqli_error($koneksi));
        }

        mysqli_stmt_bind_param($stmt_user, "s", $email);
        mysqli_stmt_execute($stmt_user);

        $hasil_user = mysqli_stmt_get_result($stmt_user);
        $data_user = mysqli_fetch_assoc($hasil_user);

        if ($data_user) {
            $id_petugas = (int)$data_user['id'];
        }

        mysqli_stmt_close($stmt_user);
    }

    // Jika email tidak tersedia, gunakan ID akun dari session
    if ($id_petugas <= 0) {
        $id_session = (int)($_SESSION['user_id'] ?? $_SESSION['id'] ?? 0);

        if ($id_session > 0) {
            $stmt_user = mysqli_prepare($koneksi, "
                SELECT id
                FROM users
                WHERE id = ?
                  AND aktif = 1
                  AND role IN ('guru', 'wali_kelas')
                LIMIT 1
            ");

            if (!$stmt_user) {
                die("Gagal mengambil akun Guru: " . mysqli_error($koneksi));
            }

            mysqli_stmt_bind_param($stmt_user, "i", $id_session);
            mysqli_stmt_execute($stmt_user);

            $hasil_user = mysqli_stmt_get_result($stmt_user);
            $data_user = mysqli_fetch_assoc($hasil_user);

            if ($data_user) {
                $id_petugas = (int)$data_user['id'];
            }

            mysqli_stmt_close($stmt_user);
        }
    }

    // Jangan tampilkan seluruh riwayat jika akun tidak ditemukan
    if ($id_petugas <= 0) {
        die("Akses ditolak. ID akun Guru tidak ditemukan.");
    }

    // Guru hanya melihat tindakan yang dilakukan akun sendiri
    $whereParts[] = "t.petugas_id = ?";
    $parameter[] = $id_petugas;
    $tipe .= "i";
}

// =====================================
// PENCARIAN
// =====================================
if ($cari !== '') {

    $whereParts[] = "(
        s.nama LIKE ?
        OR jp.nama LIKE ?
        OR t.tindakan LIKE ?
        OR u.nama LIKE ?
        OR t.catatan LIKE ?
    )";

    $keyword = "%{$cari}%";

    $parameter[] = $keyword;
    $parameter[] = $keyword;
    $parameter[] = $keyword;
    $parameter[] = $keyword;
    $parameter[] = $keyword;

    $tipe .= "sssss";
}

$where = '';

if (!empty($whereParts)) {
    $where = "WHERE " . implode(" AND ", $whereParts);
}

// =====================================
// PAGINATION
// =====================================
$batas = 5;
$halaman = max(1, (int)($_GET['halaman'] ?? 1));

// =====================================
// HITUNG TOTAL DATA
// =====================================
$sql_count = "
    SELECT COUNT(*) AS total
    FROM tindakan t
    INNER JOIN pelanggaran p
        ON t.pelanggaran_id = p.id
    INNER JOIN siswa s
        ON p.siswa_id = s.id
    INNER JOIN jenis_pelanggaran jp
        ON p.jenis_id = jp.id
    LEFT JOIN users u
        ON t.petugas_id = u.id
    $where
";

$stmt_count = mysqli_prepare($koneksi, $sql_count);

if (!$stmt_count) {
    die("Query hitung riwayat gagal: " . mysqli_error($koneksi));
}

bindParameter($stmt_count, $tipe, $parameter);
mysqli_stmt_execute($stmt_count);

$result_count = mysqli_stmt_get_result($stmt_count);
$data_count = mysqli_fetch_assoc($result_count);

$total_data = (int)($data_count['total'] ?? 0);

mysqli_stmt_close($stmt_count);

$total_halaman = max(1, (int)ceil($total_data / $batas));

if ($halaman > $total_halaman) {
    $halaman = $total_halaman;
}

$mulai = ($halaman - 1) * $batas;

// =====================================
// AMBIL DATA RIWAYAT
// =====================================
$sql = "
    SELECT
        t.id,
        s.nama AS nama_siswa,
        s.nis,
        jp.nama AS jenis,
        jp.poin,
        t.tanggal,
        t.tindakan,
        u.nama AS nama_petugas,
        t.catatan
    FROM tindakan t
    INNER JOIN pelanggaran p
        ON t.pelanggaran_id = p.id
    INNER JOIN siswa s
        ON p.siswa_id = s.id
    INNER JOIN jenis_pelanggaran jp
        ON p.jenis_id = jp.id
    LEFT JOIN users u
        ON t.petugas_id = u.id
    $where
    ORDER BY t.tanggal DESC, t.id DESC
    LIMIT ?, ?
";

$stmt = mysqli_prepare($koneksi, $sql);

if (!$stmt) {
    die("Query riwayat gagal: " . mysqli_error($koneksi));
}

$parameter_data = $parameter;
$parameter_data[] = $mulai;
$parameter_data[] = $batas;

$tipe_data = $tipe . "ii";

bindParameter($stmt, $tipe_data, $parameter_data);
mysqli_stmt_execute($stmt);

$query = mysqli_stmt_get_result($stmt);

if (!$query) {
    die("Gagal mengambil data riwayat.");
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Riwayat Pelanggaran</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
* {
    box-sizing: border-box;
}

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
    min-height: 100vh;
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

.header-box h1 {
    font-size: 24px;
    font-weight: bold;
    margin: 0 0 10px;
}

.main-box {
    padding: 22px;
    width: 100%;
}

.main-box h2 {
    font-size: 21px;
    font-weight: bold;
    margin: 0 0 10px;
}

.deskripsi {
    color: #666;
    font-size: 14px;
    margin-bottom: 20px;
}

.table {
    font-size: 13px;
    vertical-align: middle;
}

.table thead th {
    background: #687280;
    color: white;
    font-weight: 600;
    white-space: nowrap;
    padding: 12px 10px;
}

.table tbody td {
    padding: 11px 10px;
}

.table tbody tr:hover td {
    background: #f7f7f7;
}

.btn-cari {
    background: #687280;
    color: white;
}

.btn-cari:hover {
    background: #4f5965;
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

<?php
$menu_aktif = 'riwayat';
require_once "sidebar.php";
?>

<div class="content">

    <div class="header-box">
        <h1>Riwayat Pelanggaran</h1>

        <p class="text-secondary mb-0">
            Daftar riwayat tindakan siswa.
        </p>
    </div>

    <div class="main-box">

        <h2>Daftar Riwayat Pelanggaran</h2>

        <p class="deskripsi">
            Informasi riwayat tindakan siswa.
        </p>

        <form method="GET" class="row g-2 mb-3">

            <div class="col-md-5">
                <input
                    type="text"
                    name="cari"
                    class="form-control"
                    placeholder="Cari nama, jenis, tindakan, petugas..."
                    value="<?= htmlspecialchars($cari); ?>"
                >
            </div>

            <div class="col-auto">
                <button type="submit" class="btn btn-cari">
                    Cari
                </button>

                <a href="riwayat.php" class="btn btn-secondary">
                    Reset
                </a>
            </div>

        </form>

        <div class="mb-3 text-secondary small">
            Total data: <?= $total_data; ?> riwayat
        </div>

        <div class="table-responsive">

            <table class="table table-bordered table-hover">

                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Siswa</th>
                        <th>NIS</th>
                        <th>Jenis Pelanggaran</th>
                        <th>Poin</th>
                        <th>Tanggal Tindakan</th>
                        <th>Tindakan</th>
                        <th>Petugas</th>
                        <th>Catatan</th>
                    </tr>
                </thead>

                <tbody>

                    <?php if (mysqli_num_rows($query) > 0): ?>

                        <?php
                        $no = $mulai + 1;

                        while ($data = mysqli_fetch_assoc($query)):
                        ?>

                            <tr>
                                <td><?= $no++; ?></td>

                                <td>
                                    <strong>
                                        <?= htmlspecialchars($data['nama_siswa']); ?>
                                    </strong>
                                </td>

                                <td>
                                    <?= htmlspecialchars($data['nis']); ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($data['jenis']); ?>
                                </td>

                                <td>
                                    <?= (int)$data['poin']; ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($data['tanggal']); ?>
                                </td>

                                <td>
                                    <?= nl2br(htmlspecialchars($data['tindakan'])); ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($data['nama_petugas'] ?? '-'); ?>
                                </td>

                                <td>
                                    <?= nl2br(htmlspecialchars($data['catatan'] ?? '-')); ?>
                                </td>
                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="9" class="text-center py-4 text-secondary">
                                Belum ada riwayat tindakan.
                            </td>
                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

        <?php if ($total_halaman > 1): ?>

            <nav class="mt-3">
                <ul class="pagination justify-content-end">

                    <li class="page-item <?= $halaman <= 1 ? 'disabled' : ''; ?>">
                        <a
                            class="page-link"
                            href="?halaman=<?= $halaman - 1; ?>&cari=<?= urlencode($cari); ?>"
                        >
                            Sebelumnya
                        </a>
                    </li>

                    <?php for ($i = 1; $i <= $total_halaman; $i++): ?>

                        <li class="page-item <?= $halaman === $i ? 'active' : ''; ?>">
                            <a
                                class="page-link"
                                href="?halaman=<?= $i; ?>&cari=<?= urlencode($cari); ?>"
                            >
                                <?= $i; ?>
                            </a>
                        </li>

                    <?php endfor; ?>

                    <li class="page-item <?= $halaman >= $total_halaman ? 'disabled' : ''; ?>">
                        <a
                            class="page-link"
                            href="?halaman=<?= $halaman + 1; ?>&cari=<?= urlencode($cari); ?>"
                        >
                            Berikutnya
                        </a>
                    </li>

                </ul>
            </nav>

        <?php endif; ?>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

<?php
mysqli_stmt_close($stmt);
?>
