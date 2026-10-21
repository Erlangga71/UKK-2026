
<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

/* =========================
   DATABASE
========================= */

require_once "database.php";

/* =========================
   SESSION
========================= */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* =========================
   CEK LOGIN
========================= */

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

/* =========================
   CEK ROLE
   ADMIN, GURU, WALI KELAS
========================= */

$role = $_SESSION['role'] ?? '';

if (!in_array($role, ['admin', 'guru', 'wali_kelas'])) {
    die("Akses ditolak.");
}

/* =========================
   SEARCH
========================= */

$search = trim($_GET['search'] ?? '');

/* =========================
   PAGINATION
========================= */

$per_halaman = 5;

$halaman = isset($_GET['halaman'])
    ? (int) $_GET['halaman']
    : 1;

if ($halaman < 1) {
    $halaman = 1;
}

$mulai = ($halaman - 1) * $per_halaman;

/* =========================
   HITUNG TOTAL DATA
========================= */

if ($search != '') {

    $stmt = $koneksi->prepare("
        SELECT COUNT(*) AS total
        FROM guru
        WHERE nip LIKE ?
        OR nama LIKE ?
        OR email LIKE ?
        OR no_hp LIKE ?
    ");

    if (!$stmt) {
        die("Query error: " . $koneksi->error);
    }

    $cari = "%$search%";

    $stmt->bind_param(
        "ssss",
        $cari,
        $cari,
        $cari,
        $cari
    );

    $stmt->execute();

    $hasil_total = $stmt->get_result()->fetch_assoc();

    $total_data = (int) $hasil_total['total'];

} else {

    $hasil_total = mysqli_query(
        $koneksi,
        "SELECT COUNT(*) AS total FROM guru"
    );

    if (!$hasil_total) {
        die("Query total guru error: " . mysqli_error($koneksi));
    }

    $data_total = mysqli_fetch_assoc($hasil_total);

    $total_data = (int) $data_total['total'];
}

/* =========================
   TOTAL HALAMAN
========================= */

$total_halaman = max(
    1,
    (int) ceil($total_data / $per_halaman)
);

if ($halaman > $total_halaman) {
    $halaman = $total_halaman;
    $mulai = ($halaman - 1) * $per_halaman;
}

/* =========================
   AMBIL DATA GURU
========================= */

if ($search != '') {

    $stmt = $koneksi->prepare("
        SELECT
            id,
            nip,
            nama,
            email,
            no_hp
        FROM guru
        WHERE nip LIKE ?
        OR nama LIKE ?
        OR email LIKE ?
        OR no_hp LIKE ?
        ORDER BY nama ASC
        LIMIT ?, ?
    ");

    if (!$stmt) {
        die("Query data guru error: " . $koneksi->error);
    }

    $cari = "%$search%";

    $stmt->bind_param(
        "ssssii",
        $cari,
        $cari,
        $cari,
        $cari,
        $mulai,
        $per_halaman
    );

    $stmt->execute();

    $guru = $stmt->get_result();

} else {

    $stmt = $koneksi->prepare("
        SELECT
            id,
            nip,
            nama,
            email,
            no_hp
        FROM guru
        ORDER BY nama ASC
        LIMIT ?, ?
    ");

    if (!$stmt) {
        die("Query data guru error: " . $koneksi->error);
    }

    $stmt->bind_param(
        "ii",
        $mulai,
        $per_halaman
    );

    $stmt->execute();

    $guru = $stmt->get_result();
}

?>

<!DOCTYPE html>

<html lang="id">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Data Guru</title>

<style>

/* =========================
   DASAR
========================= */

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #f4f4f4;
    color: #333;
}

/* =========================
   LAYOUT
========================= */

.layout {
    display: flex;
    min-height: 100vh;
}

/* =========================
   SIDEBAR
========================= */

.sidebar {
    width: 230px;
    min-height: 100vh;
    background: #004d4d;
    padding: 20px 15px;
    flex-shrink: 0;
}

.sidebar h2 {
    color: white;
    font-size: 20px;
    margin: 0 0 25px 10px;
}

.sidebar a {
    display: block;
    color: white;
    text-decoration: none;
    padding: 11px 14px;
    margin-bottom: 7px;
    border-radius: 5px;
}

.sidebar a:hover,
.sidebar a.active {
    background: #176b80;
}

.sidebar .logout {
    margin-top: 25px;
    background: #7b1e1e;
}

.sidebar .logout:hover {
    background: #a52a2a;
}

/* =========================
   CONTENT
========================= */

.content {
    flex: 1;
    padding: 30px;
}

.judul {
    margin-bottom: 20px;
}

.judul h1 {
    margin: 0;
    font-size: 28px;
}

.judul p {
    color: #777;
}

/* =========================
   BOX
========================= */

.box {
    background: white;
    border: 1px solid #ddd;
    border-radius: 6px;
    padding: 20px;
}

/* =========================
   HEADER TABEL
========================= */

.table-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 18px;
    gap: 10px;
}

.search-form {
    display: flex;
    gap: 7px;
}

.search-form input {
    width: 280px;
    padding: 9px;
    border: 1px solid #ccc;
    border-radius: 5px;
    font-size: 14px;
}

/* =========================
   BUTTON
========================= */

.btn {
    display: inline-block;
    padding: 9px 14px;
    background: #176b80;
    color: white;
    text-decoration: none;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 14px;
}

.btn:hover {
    background: #0d5364;
}

.btn-tambah {
    background: #087f5b;
}

.btn-tambah:hover {
    background: #066b4d;
}

.btn-reset {
    background: #777;
}

.btn-reset:hover {
    background: #555;
}

/* =========================
   TABEL
========================= */

.table-wrapper {
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
    background: white;
}

th {
    background: #006b6b;
    color: white;
    padding: 12px;
    text-align: left;
}

td {
    padding: 11px;
    border-bottom: 1px solid #ddd;
}

tr:hover {
    background: #f1fafa;
}

/* =========================
   AKSI
========================= */

.edit {
    color: #176b80;
    text-decoration: none;
}

.edit:hover {
    text-decoration: underline;
}

.hapus {
    color: #c62828;
    text-decoration: none;
}

.hapus:hover {
    text-decoration: underline;
}

/* =========================
   PAGINATION
========================= */

.pagination {
    margin-top: 20px;
    display: flex;
    gap: 5px;
    flex-wrap: wrap;
}

.pagination a {
    display: inline-block;
    padding: 8px 12px;
    border: 1px solid #ccc;
    background: white;
    color: #333;
    text-decoration: none;
    border-radius: 4px;
}

.pagination a:hover {
    background: #176b80;
    color: white;
}

.pagination a.active {
    background: #176b80;
    color: white;
    border-color: #176b80;
}

.info {
    margin-top: 15px;
    color: #666;
    font-size: 14px;
}

/* =========================
   MOBILE
========================= */

@media (max-width: 800px) {

    .layout {
        display: block;
    }

    .sidebar {
        width: 100%;
        min-height: auto;
    }

    .content {
        padding: 20px;
    }

    .table-header {
        display: block;
    }

    .search-form {
        margin-bottom: 10px;
    }

    .search-form input {
        width: 100%;
    }
}

</style>

</head>

<body>

<div class="layout">

    <!-- =========================
         SIDEBAR
    ========================= -->

    <div class="sidebar">

        <h2>
            SI Pelanggaran
        </h2>

        <a href="dashboard.php">
            Dashboard
        </a>

        <a href="siswa.php">
            Data Siswa
        </a>

        <a href="guru.php" class="active">
            Data Guru
        </a>

        <a href="kelas.php">
            Data Kelas
        </a>

        <a href="tahun_ajaran.php">
            Tahun Ajaran
        </a>

        <a href="penempatan_siswa.php">
            Penempatan Siswa
        </a>

        <a href="pelanggaran.php">
            Data Pelanggaran
        </a>

        <a href="tindakan.php">
            Data Tindakan
        </a>

        <a href="logout.php" class="logout">
            Logout
        </a>

    </div>


    <!-- =========================
         CONTENT
    ========================= -->

    <div class="content">

        <div class="judul">

            <h1>
                Data Guru
            </h1>

            <p>
                Daftar data guru yang terdaftar dalam sistem.
            </p>

        </div>


        <div class="box">

            <!-- =========================
                 SEARCH + TAMBAH
            ========================= -->

            <div class="table-header">

                <form
                    method="GET"
                    class="search-form"
                >

                    <input
                        type="text"
                        name="search"
                        placeholder="Cari NIP, nama, email..."
                        value="<?= htmlspecialchars($search); ?>"
                    >

                    <button
                        type="submit"
                        class="btn"
                    >
                        Cari
                    </button>

                    <?php if ($search != '') { ?>

                        <a
                            href="guru.php"
                            class="btn btn-reset"
                        >
                            Reset
                        </a>

                    <?php } ?>

                </form>


                <!-- ADMIN SAJA -->

                <?php if ($role == 'admin') { ?>

                    <a
                        href="tambah_guru.php"
                        class="btn btn-tambah"
                    >
                        + Tambah Guru
                    </a>

                <?php } ?>

            </div>


            <!-- =========================
                 TABEL
            ========================= -->

            <div class="table-wrapper">

                <table>

                    <thead>

                        <tr>

                            <th>No</th>

                            <th>NIP</th>

                            <th>Nama Guru</th>

                            <th>Email</th>

                            <th>No. HP</th>

                            <th>Aksi</th>

                        </tr>

                    </thead>


                    <tbody>

                    <?php if ($guru->num_rows > 0) { ?>

                        <?php

                        $no = $mulai + 1;

                        while ($g = $guru->fetch_assoc()) {

                        ?>

                            <tr>

                                <td>
                                    <?= $no++; ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($g['nip']); ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($g['nama']); ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($g['email']); ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($g['no_hp']); ?>
                                </td>

                                <td>

                                    <?php if ($role == 'admin') { ?>

                                        <a
                                            href="edit_guru.php?id=<?= (int)$g['id']; ?>"
                                            class="edit"
                                        >
                                            Edit
                                        </a>

                                        |

                                        <a
                                            href="hapus_guru.php?id=<?= (int)$g['id']; ?>"
                                            class="hapus"
                                            onclick="return confirm('Yakin ingin menghapus guru ini?')"
                                        >
                                            Hapus
                                        </a>

                                    <?php } else { ?>

                                        -

                                    <?php } ?>

                                </td>

                            </tr>

                        <?php } ?>

                    <?php } else { ?>

                        <tr>

                            <td
                                colspan="6"
                                style="text-align:center;"
                            >
                                Data guru tidak ditemukan.

                            </td>

                        </tr>

                    <?php } ?>

                    </tbody>

                </table>

            </div>


            <!-- =========================
                 INFO DATA
            ========================= -->

            <div class="info">

                Menampilkan

                <?= $total_data > 0 ? $mulai + 1 : 0; ?>

                -

                <?= min($mulai + $per_halaman, $total_data); ?>

                dari

                <?= $total_data; ?>

                data guru.

            </div>


            <!-- =========================
                 PAGINATION
            ========================= -->

            <?php if ($total_halaman > 1) { ?>

                <div class="pagination">

                    <?php if ($halaman > 1) { ?>

                        <a
                            href="?halaman=<?= $halaman - 1; ?>&search=<?= urlencode($search); ?>"
                        >
                            &laquo;
                        </a>

                    <?php } ?>


                    <?php for ($i = 1; $i <= $total_halaman; $i++) { ?>

                        <a
                            href="?halaman=<?= $i; ?>&search=<?= urlencode($search); ?>"
                            class="<?= $i == $halaman ? 'active' : ''; ?>"
                        >
                            <?= $i; ?>
                        </a>

                    <?php } ?>


                    <?php if ($halaman < $total_halaman) { ?>

                        <a
                            href="?halaman=<?= $halaman + 1; ?>&search=<?= urlencode($search); ?>"
                        >
                            &raquo;
                        </a>

                    <?php } ?>

                </div>

            <?php } ?>

        </div>

    </div>

</div>

</body>

</html>
```