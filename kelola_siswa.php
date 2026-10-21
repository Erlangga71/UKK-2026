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
   TAMPILKAN FORM TAMBAH
========================= */

$tambah = isset($_GET['tambah']) && $_GET['tambah'] == '1';


/* =========================
   TAMBAH DATA SISWA
========================= */

if (isset($_POST['simpan'])) {

    $nis = trim($_POST['nis'] ?? '');
    $nama = trim($_POST['nama'] ?? '');
    $jenis_kelamin = trim($_POST['jenis_kelamin'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');
    $no_hp_ortu = trim($_POST['no_hp_ortu'] ?? '');

    if ($nis == '' || $nama == '') {

        $error = "NIS dan nama wajib diisi.";
        $tambah = true;

    } else {

        $nis_db = mysqli_real_escape_string($koneksi, $nis);
        $nama_db = mysqli_real_escape_string($koneksi, $nama);
        $jk_db = mysqli_real_escape_string($koneksi, $jenis_kelamin);
        $alamat_db = mysqli_real_escape_string($koneksi, $alamat);
        $hp_db = mysqli_real_escape_string($koneksi, $no_hp_ortu);

        /* CEK NIS */

        $cek = mysqli_query(
            $koneksi,
            "SELECT id
             FROM siswa
             WHERE nis = '$nis_db'
             LIMIT 1"
        );

        if (mysqli_num_rows($cek) > 0) {

            $error = "NIS tersebut sudah digunakan.";
            $tambah = true;

        } else {

            $query = mysqli_query(
                $koneksi,
                "INSERT INTO siswa
                (
                    nis,
                    nama,
                    jenis_kelamin,
                    alamat,
                    no_hp_ortu,
                    aktif
                )
                VALUES
                (
                    '$nis_db',
                    '$nama_db',
                    '$jk_db',
                    '$alamat_db',
                    '$hp_db',
                    1
                )"
            );

            if ($query) {

                header("Location: kelola_siswa.php");
                exit;

            } else {

                $error = "Gagal menambahkan siswa: "
                       . mysqli_error($koneksi);

                $tambah = true;
            }
        }
    }
}


/* =========================
   HAPUS DATA SISWA
========================= */

if (isset($_GET['hapus'])) {

    $id = intval($_GET['hapus']);

    if ($id > 0) {

        mysqli_query(
            $koneksi,
            "DELETE FROM siswa
             WHERE id = $id"
        );
    }

    header("Location: kelola_siswa.php");
    exit;
}


/* =========================
   AMBIL DATA SISWA
========================= */

$data = mysqli_query(
    $koneksi,
    "SELECT
        id,
        nis,
        nama,
        jenis_kelamin,
        alamat,
        no_hp_ortu,
        aktif
     FROM siswa
     ORDER BY id DESC"
);

?>

<!DOCTYPE html>

<html lang="id">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Data Siswa</title>

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
   BAGIAN ATAS DATA
========================= */

.data-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.data-header h2 {
    margin: 0;
}


/* =========================
   FORM
========================= */

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group.full {
    grid-column: 1 / -1;
}

label {
    margin-bottom: 6px;
    font-size: 13px;
    font-weight: bold;
}

input,
select,
textarea {
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
    font-family: Arial;
}

textarea {
    resize: vertical;
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
    margin-left: 5px;
}

.btn-batal:hover {
    background: #555;
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
   TABEL
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

    .form-grid {
        grid-template-columns: 1fr;
    }

    .data-header {
        display: block;
    }

    .data-header .btn {
        margin-top: 10px;
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

    <a href="kelola_siswa.php" class="active">
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

        <h1>
            Data Siswa
        </h1>

        <p>
            Kelola data siswa dalam sistem.
        </p>

    </div>


    <?php if (isset($error)) { ?>

        <div class="error">

            <?= htmlspecialchars($error); ?>

        </div>

    <?php } ?>


    <!-- =========================
         FORM TAMBAH
         HANYA MUNCUL JIKA
         ?tambah=1
    ========================= -->

    <?php if ($tambah) { ?>

        <div class="box">

            <div class="data-header">

                <h2>
                    Tambah Data Siswa
                </h2>

            </div>


            <form method="POST">

                <div class="form-grid">


                    <div class="form-group">

                        <label>
                            NIS
                        </label>

                        <input
                            type="text"
                            name="nis"
                            placeholder="Masukkan NIS"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label>
                            Nama
                        </label>

                        <input
                            type="text"
                            name="nama"
                            placeholder="Masukkan nama siswa"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label>
                            Jenis Kelamin
                        </label>

                        <select name="jenis_kelamin">

                            <option value="">
                                -- Pilih --
                            </option>

                            <option value="L">
                                Laki-laki
                            </option>

                            <option value="P">
                                Perempuan
                            </option>

                        </select>

                    </div>


                    <div class="form-group">

                        <label>
                            No HP Orang Tua
                        </label>

                        <input
                            type="text"
                            name="no_hp_ortu"
                            placeholder="Masukkan nomor HP"
                        >

                    </div>


                    <div class="form-group full">

                        <label>
                            Alamat
                        </label>

                        <textarea
                            name="alamat"
                            rows="3"
                            placeholder="Masukkan alamat"
                        ></textarea>

                    </div>


                </div>


                <button
                    type="submit"
                    name="simpan"
                    class="btn btn-simpan"
                >
                    Simpan Siswa
                </button>


                <a
                    href="kelola_siswa.php"
                    class="btn btn-batal"
                >
                    Batal
                </a>


            </form>

        </div>

    <?php } ?>


    <!-- =========================
         DAFTAR SISWA
    ========================= -->

    <div class="box">


        <div class="data-header">

            <h2>
                Daftar Siswa
            </h2>


            <?php if (!$tambah) { ?>

                <a
                    href="kelola_siswa.php?tambah=1"
                    class="btn btn-tambah"
                >
                    + Tambah Siswa
                </a>

            <?php } ?>


        </div>


        <div class="table-container">

            <table>

                <thead>

                    <tr>

                        <th>
                            No
                        </th>

                        <th>
                            NIS
                        </th>

                        <th>
                            Nama
                        </th>

                        <th>
                            JK
                        </th>

                        <th>
                            No HP Orang Tua
                        </th>

                        <th>
                            Aksi
                        </th>

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
                            <?= htmlspecialchars(
                                $row['nis']
                            ); ?>
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
                                $row['jenis_kelamin'] ?? '-'
                            ); ?>
                        </td>


                        <td>
                            <?= htmlspecialchars(
                                $row['no_hp_ortu'] ?? '-'
                            ); ?>
                        </td>


                        <td>

                            <a
                                class="btn btn-edit"
                                href="edit_siswa.php?id=<?= $row['id']; ?>"
                            >
                                Edit
                            </a>


                            <a
                                class="btn btn-hapus"
                                href="kelola_siswa.php?hapus=<?= $row['id']; ?>"
                                onclick="return confirm('Hapus data siswa ini?')"
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
                            colspan="6"
                            style="text-align:center;padding:25px;"
                        >

                            Belum ada data siswa.

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