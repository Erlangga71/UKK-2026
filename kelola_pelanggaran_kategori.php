<?php

session_start();

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

include "database.php";

$role = $_SESSION['role'] ?? '';

/* =========================
   HANYA ADMIN
========================= */

if ($role != 'admin') {
    die("Akses ditolak.");
}


/* =========================
   TAMBAH KATEGORI
========================= */

if (isset($_POST['simpan'])) {

    $nama = trim($_POST['nama'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');

    if ($nama == '') {

        $error = "Nama kategori wajib diisi.";

    } else {

        $nama_db = mysqli_real_escape_string(
            $koneksi,
            $nama
        );

        $deskripsi_db = mysqli_real_escape_string(
            $koneksi,
            $deskripsi
        );


        /* CEK DUPLIKAT */

        $cek = mysqli_query(
            $koneksi,
            "SELECT id
             FROM kategori_pelanggaran
             WHERE nama = '$nama_db'
             LIMIT 1"
        );

        if (mysqli_num_rows($cek) > 0) {

            $error = "Kategori tersebut sudah ada.";

        } else {

            $query = mysqli_query(
                $koneksi,
                "INSERT INTO kategori_pelanggaran
                (
                    nama,
                    deskripsi
                )
                VALUES
                (
                    '$nama_db',
                    '$deskripsi_db'
                )"
            );

            if ($query) {

                header(
                    "Location: kelola_pelanggaran_kategori.php"
                );
                exit;

            } else {

                $error =
                    "Gagal menambahkan kategori: " .
                    mysqli_error($koneksi);
            }
        }
    }
}


/* =========================
   HAPUS KATEGORI
========================= */

if (isset($_GET['hapus'])) {

    $id = (int)$_GET['hapus'];

    if ($id > 0) {

        mysqli_query(
            $koneksi,
            "DELETE FROM kategori_pelanggaran
             WHERE id = $id"
        );
    }

    header(
        "Location: kelola_pelanggaran_kategori.php"
    );
    exit;
}


/* =========================
   DATA KATEGORI
========================= */

$data = mysqli_query(
    $koneksi,
    "SELECT
        id,
        nama,
        deskripsi
     FROM kategori_pelanggaran
     ORDER BY id ASC"
);

?>

<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Kategori Pelanggaran</title>

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


/* =========================
   HEADER
========================= */

.header {
    background: white;
    border: 1px solid #ddd;
    border-radius: 7px;
    padding: 22px;
    margin-bottom: 20px;

    display: flex;
    justify-content: space-between;
    align-items: center;
}

.header h1 {
    margin: 0;
    font-size: 24px;
}

.header p {
    color: #777;
    margin-bottom: 0;
}


/* =========================
   BOX
========================= */

.box {
    background: white;
    border: 1px solid #ddd;
    border-radius: 7px;
    padding: 22px;
    margin-bottom: 20px;
}

.box h2 {
    margin-top: 0;
}


/* =========================
   TOMBOL
========================= */

.btn {
    display: inline-block;
    padding: 9px 14px;
    border: none;
    border-radius: 5px;
    color: white;
    text-decoration: none;
    cursor: pointer;
    font-size: 13px;
}

.btn-tambah {
    background: #087f5b;
}

.btn-tambah:hover {
    background: #066b4d;
}

.btn-simpan {
    background: #687280;
}

.btn-simpan:hover {
    background: #56606d;
}

.btn-batal {
    background: #777;
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
   FORM
========================= */

.form-group {
    margin-bottom: 16px;
}

label {
    display: block;
    margin-bottom: 6px;
    font-size: 13px;
    font-weight: bold;
}

input,
textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
    font-family: Arial, sans-serif;
}

textarea {
    resize: vertical;
}

.form-actions {
    margin-top: 20px;
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
   RESPONSIVE
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

    .header {
        display: block;
    }

    .header .btn {
        margin-top: 15px;
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

    <a
        href="kelola_pelanggaran_kategori.php"
        class="active"
    >
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


    <!-- HEADER -->

    <div class="header">

        <div>

            <h1>
                Kategori Pelanggaran
            </h1>

            <p>
                Kelola kategori pelanggaran siswa.
            </p>

        </div>


        <!--
            FORM TIDAK LANGSUNG MUNCUL.
            HARUS KLIK TOMBOL INI.
        -->

        <a
            href="kelola_pelanggaran_kategori.php?tambah=1"
            class="btn btn-tambah"
        >
            + Tambah Kategori
        </a>

    </div>


    <!-- ERROR -->

    <?php if (isset($error)) { ?>

        <div class="error">

            <?= htmlspecialchars($error); ?>

        </div>

    <?php } ?>


    <!-- =========================
         FORM TAMBAH
         HANYA MUNCUL SAAT
         ?tambah=1
    ========================= -->

    <?php if (isset($_GET['tambah'])) { ?>

        <div class="box">

            <h2>
                Tambah Kategori Pelanggaran
            </h2>


            <form method="POST">


                <div class="form-group">

                    <label>
                        Nama Kategori
                    </label>

                    <input
                        type="text"
                        name="nama"
                        placeholder="Contoh: Kedisiplinan"
                        required
                    >

                </div>


                <div class="form-group">

                    <label>
                        Deskripsi
                    </label>

                    <textarea
                        name="deskripsi"
                        rows="4"
                        placeholder="Masukkan deskripsi kategori"
                    ></textarea>

                </div>


                <div class="form-actions">

                    <button
                        type="submit"
                        name="simpan"
                        class="btn btn-simpan"
                    >
                        Simpan Kategori
                    </button>


                    <a
                        href="kelola_pelanggaran_kategori.php"
                        class="btn btn-batal"
                    >
                        Batal
                    </a>

                </div>


            </form>

        </div>

    <?php } ?>


    <!-- =========================
         DAFTAR KATEGORI
    ========================= -->

    <div class="box">

        <h2>
            Daftar Kategori Pelanggaran
        </h2>


        <div class="table-container">

            <table>

                <thead>

                    <tr>

                        <th>No</th>

                        <th>Nama Kategori</th>

                        <th>Deskripsi</th>

                        <th>Aksi</th>

                    </tr>

                </thead>


                <tbody>

                <?php

                $no = 1;

                if (
                    $data &&
                    mysqli_num_rows($data) > 0
                ) {

                    while (
                        $row = mysqli_fetch_assoc($data)
                    ) {

                ?>

                    <tr>

                        <td>
                            <?= $no++; ?>
                        </td>

                        <td>

                            <b>
                                <?= htmlspecialchars(
                                    $row['nama']
                                ); ?>
                            </b>

                        </td>

                        <td>

                            <?= htmlspecialchars(
                                $row['deskripsi'] ?? '-'
                            ); ?>

                        </td>

                        <td>

                            <a
                                href="edit_kategori.php?id=<?= $row['id']; ?>"
                                class="btn btn-edit"
                            >
                                Edit
                            </a>


                            <a
                                href="kelola_pelanggaran_kategori.php?hapus=<?= $row['id']; ?>"
                                class="btn btn-hapus"
                                onclick="return confirm('Yakin ingin menghapus kategori ini?')"
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
                            colspan="4"
                            style="text-align:center;padding:25px;"
                        >

                            Belum ada kategori pelanggaran.

                        </td>

                    </tr>

                <?php } ?>

                </tbody>

            </table>

        </div>

    </div>


</div>

</body>

</html>