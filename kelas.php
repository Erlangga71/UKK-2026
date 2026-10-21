```php
<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "database.php";

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

$role = $_SESSION['role'] ?? '';

/* =========================
   SEARCH
========================= */

$search = trim($_GET['search'] ?? '');

/* =========================
   PAGINATION
========================= */

$per_halaman = 10;

$halaman = isset($_GET['halaman'])
    ? (int) $_GET['halaman']
    : 1;

if ($halaman < 1) {
    $halaman = 1;
}

$mulai = ($halaman - 1) * $per_halaman;

/* =========================
   HITUNG TOTAL
========================= */

if ($search != '') {

    $stmt = $koneksi->prepare(
        "SELECT COUNT(*) AS total
         FROM kelas
         WHERE nama_kelas LIKE ?"
    );

    $cari = "%$search%";

    $stmt->bind_param(
        "s",
        $cari
    );

    $stmt->execute();

    $hasil = $stmt->get_result()->fetch_assoc();

    $total_data = (int) $hasil['total'];

} else {

    $hasil = mysqli_query(
        $koneksi,
        "SELECT COUNT(*) AS total
         FROM kelas"
    );

    if (!$hasil) {
        die(
            "Query total kelas error: " .
            mysqli_error($koneksi)
        );
    }

    $data_total = mysqli_fetch_assoc($hasil);

    $total_data = (int) $data_total['total'];
}

/* =========================
   TOTAL HALAMAN
========================= */

$total_halaman = ceil(
    $total_data / $per_halaman
);

if (
    $total_halaman > 0 &&
    $halaman > $total_halaman
) {
    $halaman = $total_halaman;

    $mulai =
        ($halaman - 1) *
        $per_halaman;
}

/* =========================
   AMBIL DATA KELAS
========================= */

if ($search != '') {

    $stmt = $koneksi->prepare(
        "SELECT id, nama_kelas
         FROM kelas
         WHERE nama_kelas LIKE ?
         ORDER BY nama_kelas ASC
         LIMIT ?, ?"
    );

    $stmt->bind_param(
        "sii",
        $cari,
        $mulai,
        $per_halaman
    );

    $stmt->execute();

    $kelas = $stmt->get_result();

} else {

    $stmt = $koneksi->prepare(
        "SELECT id, nama_kelas
         FROM kelas
         ORDER BY nama_kelas ASC
         LIMIT ?, ?"
    );

    $stmt->bind_param(
        "ii",
        $mulai,
        $per_halaman
    );

    $stmt->execute();

    $kelas = $stmt->get_result();
}

?>

<!DOCTYPE html>

<html lang="id">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Data Kelas</title>

<style>

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
    gap: 10px;
    margin-bottom: 18px;
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

/* =========================
   TABEL
========================= */

.table-wrapper {
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
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

.hapus {
    color: #c62828;
    text-decoration: none;
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

.pagination a:hover,
.pagination a.active {
    background: #176b80;
    color: white;
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

    <a href="guru.php">
        Data Guru
    </a>

    <a href="kelas.php" class="active">
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
            Data Kelas
        </h1>

        <p>
            Daftar kelas yang terdaftar dalam sistem.
        </p>

    </div>


    <div class="box">


        <!-- SEARCH + TAMBAH -->

        <div class="table-header">

            <form
                method="GET"
                class="search-form"
            >

                <input
                    type="text"
                    name="search"
                    placeholder="Cari nama kelas..."
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
                        href="kelas.php"
                        class="btn btn-reset"
                    >
                        Reset
                    </a>

                <?php } ?>

            </form>


            <?php if ($role == 'admin') { ?>

                <a
                    href="tambah_kelas.php"
                    class="btn btn-tambah"
                >
                    + Tambah Kelas
                </a>

            <?php } ?>

        </div>


        <!-- TABEL -->

        <div class="table-wrapper">

            <table>

                <thead>

                    <tr>

                        <th>
                            No
                        </th>

                        <th>
                            Nama Kelas
                        </th>

                        <th>
                            Aksi
                        </th>

                    </tr>

                </thead>


                <tbody>

                <?php

                if ($kelas->num_rows > 0) {

                    $no = $mulai + 1;

                    while (
                        $k = $kelas->fetch_assoc()
                    ) {

                ?>

                    <tr>

                        <td>
                            <?= $no++; ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $k['nama_kelas']
                            ); ?>
                        </td>

                        <td>

                            <?php if ($role == 'admin') { ?>

                                <a
                                    href="edit_kelas.php?id=<?= (int)$k['id']; ?>"
                                    class="edit"
                                >
                                    Edit
                                </a>

                                |

                                <a
                                    href="hapus_kelas.php?id=<?= (int)$k['id']; ?>"
                                    class="hapus"
                                    onclick="return confirm('Yakin ingin menghapus kelas ini?')"
                                >
                                    Hapus
                                </a>

                            <?php } else { ?>

                                -

                            <?php } ?>

                        </td>

                    </tr>

                <?php

                    }

                } else {

                ?>

                    <tr>

                        <td
                            colspan="3"
                            style="text-align:center;"
                        >

                            Data kelas tidak ditemukan.

                        </td>

                    </tr>

                <?php } ?>

                </tbody>

            </table>

        </div>


        <!-- INFO -->

        <div class="info">

            Menampilkan

            <?= $total_data > 0
                ? $mulai + 1
                : 0;
            ?>

            -

            <?= min(
                $mulai + $per_halaman,
                $total_data
            ); ?>

            dari

            <?= $total_data; ?>

            data kelas.

        </div>


        <!-- PAGINATION -->

        <?php if ($total_halaman > 1) { ?>

            <div class="pagination">

                <?php if ($halaman > 1) { ?>

                    <a
                        href="?halaman=<?= $halaman - 1; ?>&search=<?= urlencode($search); ?>"
                    >
                        &laquo;
                    </a>

                <?php } ?>


                <?php for (
                    $i = 1;
                    $i <= $total_halaman;
                    $i++
                ) { ?>

                    <a
                        href="?halaman=<?= $i; ?>&search=<?= urlencode($search); ?>"
                        class="<?= $i == $halaman
                            ? 'active'
                            : ''; ?>"
                    >
                        <?= $i; ?>
                    </a>

                <?php } ?>


                <?php if (
                    $halaman < $total_halaman
                ) { ?>

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