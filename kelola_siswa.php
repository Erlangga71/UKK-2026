<?php
session_start();
include "database.php";

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

/* =========================
   TAMBAH DATA SISWA
========================= */

if (isset($_POST['simpan'])) {

    $nis = mysqli_real_escape_string($koneksi, $_POST['nis']);
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $jenis_kelamin = mysqli_real_escape_string($koneksi, $_POST['jenis_kelamin']);
    $alamat = mysqli_real_escape_string($koneksi, $_POST['alamat']);
    $no_hp_ortu = mysqli_real_escape_string($koneksi, $_POST['no_hp_ortu']);

    $query = mysqli_query(
        $koneksi,
        "INSERT INTO siswa
        (nis, nama, jenis_kelamin, alamat, no_hp_ortu, aktif)
        VALUES
        ('$nis', '$nama', '$jenis_kelamin', '$alamat', '$no_hp_ortu', 1)"
    );

    if ($query) {
        header("Location: kelola_siswa.php");
        exit;
    } else {
        $error = mysqli_error($koneksi);
    }
}

/* =========================
   HAPUS DATA
========================= */

if (isset($_GET['hapus'])) {

    $id = intval($_GET['hapus']);

    mysqli_query(
        $koneksi,
        "DELETE FROM siswa WHERE id = $id"
    );

    header("Location: kelola_siswa.php");
    exit;
}

/* =========================
   AMBIL DATA
========================= */

$data = mysqli_query(
    $koneksi,
    "SELECT * FROM siswa ORDER BY id DESC"
);

?>

<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Data Siswa</title>

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

.sidebar a:hover {
    background: #56606d;
}

.sidebar a.active {
    background: #4f5965;
    font-weight: bold;
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
    padding: 20px;
    margin-bottom: 20px;
}

.header h1 {
    margin: 0;
    font-size: 24px;
}

.header p {
    margin: 7px 0 0;
    color: #777;
}

/* BOX */

.box {
    background: white;
    border: 1px solid #ddd;
    border-radius: 7px;
    padding: 22px;
    margin-bottom: 20px;
}

.box h2 {
    margin-top: 0;
    font-size: 18px;
}

/* FORM */

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

button {
    margin-top: 15px;
    padding: 10px 18px;
    border: none;
    border-radius: 5px;
    background: #687280;
    color: white;
    cursor: pointer;
}

button:hover {
    background: #56606d;
}

/* TABLE */

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

.btn-hapus {
    color: white;
    background: #b42323;
    padding: 6px 10px;
    text-decoration: none;
    border-radius: 4px;
    font-size: 12px;
}

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

}

</style>

</head>

<body>

<!-- SIDEBAR -->

<div class="sidebar">

    <div class="sidebar-title">
         Pelanggaran<br>
        Siswa
    </div>

    <a href="dashboard.php">
         Dashboard
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


<!-- CONTENT -->

<div class="content">

    <div class="header">

        <h1>Data Siswa</h1>

        <p>Kelola data siswa dalam sistem.</p>

    </div>


    <!-- FORM -->

    <div class="box">

        <h2>Tambah Data Siswa</h2>

        <?php if (isset($error)) { ?>

            <p style="color:red;">
                <?= htmlspecialchars($error); ?>
            </p>

        <?php } ?>

        <form method="POST">

            <div class="form-grid">

                <div class="form-group">

                    <label>NIS</label>

                    <input
                        type="text"
                        name="nis"
                        required
                    >

                </div>


                <div class="form-group">

                    <label>Nama</label>

                    <input
                        type="text"
                        name="nama"
                        required
                    >

                </div>


                <div class="form-group">

                    <label>Jenis Kelamin</label>

                    <select name="jenis_kelamin">

                        <option value="">-- Pilih --</option>

                        <option value="L">
                            Laki-laki
                        </option>

                        <option value="P">
                            Perempuan
                        </option>

                    </select>

                </div>


                <div class="form-group">

                    <label>No HP Orang Tua</label>

                    <input
                        type="text"
                        name="no_hp_ortu"
                    >

                </div>


                <div class="form-group full">

                    <label>Alamat</label>

                    <textarea
                        name="alamat"
                        rows="3"
                    ></textarea>

                </div>

            </div>

            <button type="submit" name="simpan">
                Simpan
            </button>

        </form>

    </div>


    <!-- DATA -->

    <div class="box">

        <h2>Daftar Siswa</h2>

        <div class="table-container">

            <table>

                <tr>

                    <th>No</th>
                    <th>NIS</th>
                    <th>Nama</th>
                    <th>JK</th>
                    <th>No HP Orang Tua</th>
                    <th>Aksi</th>

                </tr>

                <?php

                $no = 1;

                while ($row = mysqli_fetch_assoc($data)) {

                ?>

                <tr>

                    <td><?= $no++; ?></td>

                    <td>
                        <?= htmlspecialchars($row['nis']); ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($row['nama']); ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($row['jenis_kelamin'] ?? '-'); ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($row['no_hp_ortu'] ?? '-'); ?>
                    </td>

                    <td>

                        <a
                            class="btn-hapus"
                            href="kelola_siswa.php?hapus=<?= $row['id']; ?>"
                            onclick="return confirm('Hapus data siswa ini?')"
                        >
                            Hapus
                        </a>

                    </td>

                </tr>

                <?php } ?>

            </table>

        </div>

    </div>

</div>

</body>
</html>