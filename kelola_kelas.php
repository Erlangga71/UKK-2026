```php
<?php

session_start();

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

include "database.php";

$role = $_SESSION['role'] ?? '';

/* =========================
   CEK ADMIN
========================= */

if ($role != 'admin') {
    echo "Akses ditolak.";
    exit;
}


/* =========================
   FORM TAMBAH
========================= */

$tampil_form = isset($_GET['tambah']);


/* =========================
   TAMBAH KELAS
========================= */

if (isset($_POST['simpan'])) {

    $nama_kelas = trim($_POST['nama_kelas'] ?? '');

    if ($nama_kelas == '') {

        $error = "Nama kelas wajib diisi.";
        $tampil_form = true;

    } else {

        $nama_db = mysqli_real_escape_string(
            $koneksi,
            $nama_kelas
        );

        /* CEK KELAS */

        $cek = mysqli_query(
            $koneksi,
            "SELECT id
             FROM kelas
             WHERE nama_kelas = '$nama_db'
             LIMIT 1"
        );

        if (mysqli_num_rows($cek) > 0) {

            $error = "Nama kelas tersebut sudah ada.";
            $tampil_form = true;

        } else {

            $query = mysqli_query(
                $koneksi,
                "INSERT INTO kelas
                (nama_kelas)
                VALUES
                ('$nama_db')"
            );

            if ($query) {

                header("Location: kelola_kelas.php");
                exit;

            } else {

                $error = "Gagal menambahkan kelas: "
                       . mysqli_error($koneksi);

                $tampil_form = true;
            }
        }
    }
}


/* =========================
   HAPUS KELAS
========================= */

if (isset($_GET['hapus'])) {

    $id = intval($_GET['hapus']);

    if ($id > 0) {

        mysqli_query(
            $koneksi,
            "DELETE FROM kelas
             WHERE id = $id"
        );
    }

    header("Location: kelola_kelas.php");
    exit;
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
    ? (int)$_GET['halaman']
    : 1;

if ($halaman < 1) {
    $halaman = 1;
}


/* =========================
   HITUNG DATA
========================= */

if ($search != '') {

    $cari = "%$search%";

    $stmt = $koneksi->prepare(
        "SELECT COUNT(*) AS total
         FROM kelas
         WHERE nama_kelas LIKE ?"
    );

    $stmt->bind_param(
        "s",
        $cari
    );

    $stmt->execute();

    $hasil = $stmt
        ->get_result()
        ->fetch_assoc();

    $total_data = (int)$hasil['total'];

} else {

    $hasil = mysqli_query(
        $koneksi,
        "SELECT COUNT(*) AS total
         FROM kelas"
    );

    $hasil_total = mysqli_fetch_assoc($hasil);

    $total_data = (int)$hasil_total['total'];
}


/* =========================
   TOTAL HALAMAN
========================= */

$total_halaman = max(
    1,
    ceil($total_data / $per_halaman)
);

if ($halaman > $total_halaman) {
    $halaman = $total_halaman;
}

$mulai = ($halaman - 1) * $per_halaman;


/* =========================
   AMBIL DATA KELAS
========================= */

if ($search != '') {

    $cari = "%$search%";

    $stmt = $koneksi->prepare(
        "SELECT
            id,
            nama_kelas
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

    $data = $stmt->get_result();

} else {

    $stmt = $koneksi->prepare(
        "SELECT
            id,
            nama_kelas
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

    $data = $stmt->get_result();
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
    background: #f4f6f8;
    color: #333;
}


/* =========================
   SIDEBAR
========================= */

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


/* =========================
   CONTENT
========================= */

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
}


/* =========================
   TOP DATA
========================= */

.top-data {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 10px;
    margin-bottom: 20px;
}


/* =========================
   BUTTON
========================= */

.btn {
    display: inline-block;
    padding: 10px 15px;
    border: none;
    border-radius: 5px;
    text-decoration: none;
    cursor: pointer;
    font-size: 14px;
}

.btn-tambah {
    background: #087f5b;
    color: white;
}

.btn-tambah:hover {
    background: #066b4d;
}

.btn-simpan {
    background: #687280;
    color: white;
}

.btn-simpan:hover {
    background: #56606d;
}

.btn-batal {
    background: #777;
    color: white;
    margin-left: 5px;
}

.btn-batal:hover {
    background: #555;
}

.btn-cari {
    background: #176b80;
    color: white;
}

.btn-reset {
    background: #777;
    color: white;
}


/* =========================
   SEARCH
========================= */

.search-form {
    display: flex;
    gap: 7px;
    flex-wrap: wrap;
}

.search-form input {
    width: 280px;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
}


/* =========================
   FORM
========================= */

.form-group {
    display: flex;
    flex-direction: column;
    max-width: 500px;
}

label {
    margin-bottom: 6px;
    font-size: 13px;
    font-weight: bold;
}

input {
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
    font-family: Arial;
}

.form-buttons {
    margin-top: 5px;
}


/* =========================
   ERROR
========================= */

.error {
    background: #fee2e2;
    color: #b91c1c;
    border: 1px solid #fecaca;
    padding: 10px;
    margin-bottom: 15px;
    border-radius: 5px;
}


/* =========================
   TABLE
========================= */

.table-container {
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th {
    background: #687280;
    color: white;
    padding: 11px;
    text-align: left;
    font-size: 13px;
}

td {
    padding: 11px;
    border-bottom: 1px solid #ddd;
    font-size: 13px;
}

tr:hover {
    background: #f7f7f7;
}


/* =========================
   AKSI
========================= */

.btn-edit,
.btn-hapus {
    color: white;
    padding: 6px 10px;
    text-decoration: none;
    border-radius: 4px;
    font-size: 12px;
}

.btn-edit {
    background: #086177;
}

.btn-edit:hover {
    background: #064d5f;
}

.btn-hapus {
    background: #b42323;
}

.btn-hapus:hover {
    background: #941d1d;
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
    font-size: 13px;
}


/* =========================
   MOBILE
========================= */

@media(max-width:700px) {

    .sidebar {
        position: relative;
        width: 100%;
        height: auto;
    }

    .content {
        margin-left: 0;
        padding: 15px;
    }

    .top-data {
        display: block;
    }

    .search-form {
        margin-top: 15px;
    }

    .search-form input {
        width: 100%;
    }
}

</style>

</head>

<body>


<!-- =========================
     SIDEBAR
========================= -->

<div class="sidebar">

    <div class="sidebar-title">
        ⚖ Pelanggaran<br>
        Siswa
    </div>

    <a href="dashboard.php">
        🏠 Dashboard
    </a>

    <a href="kelola_siswa.php">
        Data Siswa
    </a>

    <a href="kelola_guru.php">
        Data Guru
    </a>

    <a href="kelola_kelas.php" class="active">
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
        ⚠ Kategori Pelanggaran
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
        ℹ About Me
    </a>

    <a href="logout.php" class="logout">
        🚪 Logout
    </a>

</div>


<!-- =========================
     CONTENT
========================= -->

<div class="content">


    <div class="header">

        <h1>
            Data Kelas
        </h1>

        <p>
            Kelola data kelas dalam sistem.
        </p>

    </div>


    <?php if (isset($error)) { ?>

        <div class="error">
            <?= htmlspecialchars($error); ?>
        </div>

    <?php } ?>


    <!-- =========================
         DAFTAR + TAMBAH
    ========================= -->

    <div class="box">

        <div class="top-data">

            <strong>
                Daftar Kelas
            </strong>

            <a
                href="kelola_kelas.php?tambah=1"
                class="btn btn-tambah"
            >
                + Tambah Kelas
            </a>

        </div>


        <!-- SEARCH -->

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
                class="btn btn-cari"
            >
                Cari
            </button>


            <?php if ($search != '') { ?>

                <a
                    href="kelola_kelas.php"
                    class="btn btn-reset"
                >
                    Reset
                </a>

            <?php } ?>

        </form>

    </div>


    <!-- =========================
         FORM TAMBAH
    ========================= -->

    <?php if ($tampil_form) { ?>

        <div class="box">

            <h2>
                Tambah Data Kelas
            </h2>

            <form method="POST">

                <div class="form-group">

                    <label>
                        Nama Kelas
                    </label>

                    <input
                        type="text"
                        name="nama_kelas"
                        placeholder="Contoh: XII RPL 1"
                        required
                    >

                </div>


                <div class="form-buttons">

                    <button
                        type="submit"
                        name="simpan"
                        class="btn btn-simpan"
                    >
                        Simpan Kelas
                    </button>

                    <a
                        href="kelola_kelas.php"
                        class="btn btn-batal"
                    >
                        Batal
                    </a>

                </div>

            </form>

        </div>

    <?php } ?>


    <!-- =========================
         TABEL KELAS
    ========================= -->

    <div class="box">

        <h2>
            Daftar Kelas
        </h2>

        <div class="table-container">

            <table>

                <thead>

                    <tr>

                        <th>No</th>

                        <th>Nama Kelas</th>

                        <th>Aksi</th>

                    </tr>

                </thead>

                <tbody>


                <?php

                if ($data && $data->num_rows > 0) {

                    $no = $mulai + 1;

                    while ($row = $data->fetch_assoc()) {

                ?>

                    <tr>

                        <td>
                            <?= $no++; ?>
                        </td>

                        <td>
                            <b>
                                <?= htmlspecialchars(
                                    $row['nama_kelas']
                                ); ?>
                            </b>
                        </td>

                        <td>

                            <a
                                class="btn-edit"
                                href="edit_kelas.php?id=<?= (int)$row['id']; ?>"
                            >
                                Edit
                            </a>

                            <a
                                class="btn-hapus"
                                href="kelola_kelas.php?hapus=<?= (int)$row['id']; ?>"
                                onclick="return confirm('Yakin ingin menghapus kelas ini?')"
                            >
                                Hapus
                            </a>

                        </td>

                    </tr>

                <?php

                    }

                } else {

                ?>

                    <tr>

                        <td
                            colspan="3"
                            style="text-align:center;padding:25px;"
                        >
                            Belum ada data kelas.
                        </td>

                    </tr>

                <?php } ?>

                </tbody>

            </table>

        </div>


        <!-- INFO -->

        <div class="info">

            Menampilkan

            <?= $total_data > 0 ? $mulai + 1 : 0; ?>

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

</body>

</html>
```