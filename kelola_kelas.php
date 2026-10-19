<?php
session_start();
include "database.php";

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

/* TAMBAH */

if (isset($_POST['simpan'])) {

    $nama_kelas = mysqli_real_escape_string(
        $koneksi,
        $_POST['nama_kelas']
    );

    $jurusan = mysqli_real_escape_string(
        $koneksi,
        $_POST['jurusan']
    );

    $tingkat = mysqli_real_escape_string(
        $koneksi,
        $_POST['tingkat']
    );

    $query = mysqli_query(
        $koneksi,
        "INSERT INTO kelas
        (nama_kelas, jurusan, tingkat, status_aktif)
        VALUES
        ('$nama_kelas', '$jurusan', '$tingkat', 1)"
    );

    if ($query) {
        header("Location: kelola_kelas.php");
        exit;
    }

    $error = mysqli_error($koneksi);
}

/* HAPUS */

if (isset($_GET['hapus'])) {

    $id = intval($_GET['hapus']);

    mysqli_query(
        $koneksi,
        "DELETE FROM kelas WHERE id = $id"
    );

    header("Location: kelola_kelas.php");
    exit;
}

/* DATA */

$data = mysqli_query(
    $koneksi,
    "SELECT * FROM kelas ORDER BY id DESC"
);

?>

<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">

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

.sidebar a:hover,
.sidebar a.active {
    background: #4f5965;
}

.logout {
    margin-top: 20px;
    background: #b42323;
}

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

.box h2 {
    margin-top: 0;
    font-size: 18px;
}

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 15px;
}

.form-group {
    display: flex;
    flex-direction: column;
}

label {
    margin-bottom: 6px;
    font-size: 13px;
    font-weight: bold;
}

input,
select {
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
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

<div class="sidebar">

    <div class="sidebar-title">
         Pelanggaran<br>
        Siswa
    </div>

    <a href="dashboard.php">
         Dashboard
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


<div class="content">

    <div class="header">

        <h1>Data Kelas</h1>

        <p>Kelola data kelas dalam sistem.</p>

    </div>


    <div class="box">

        <h2>Tambah Data Kelas</h2>

        <?php if (isset($error)) { ?>

            <p style="color:red;">
                <?= htmlspecialchars($error); ?>
            </p>

        <?php } ?>

        <form method="POST">

            <div class="form-grid">

                <div class="form-group">

                    <label>Nama Kelas</label>

                    <input
                        type="text"
                        name="nama_kelas"
                        placeholder="Contoh: X RPL 1"
                        required
                    >

                </div>


                <div class="form-group">

                    <label>Jurusan</label>

                    <input
                        type="text"
                        name="jurusan"
                        placeholder="Contoh: RPL"
                    >

                </div>


                <div class="form-group">

                    <label>Tingkat</label>

                    <select name="tingkat">

                        <option value="">
                            -- Pilih Tingkat --
                        </option>

                        <option value="X">
                            X
                        </option>

                        <option value="XI">
                            XI
                        </option>

                        <option value="XII">
                            XII
                        </option>

                    </select>

                </div>

            </div>

            <button type="submit" name="simpan">
                Simpan
            </button>

        </form>

    </div>


    <div class="box">

        <h2>Daftar Kelas</h2>

        <table>

            <tr>

                <th>No</th>
                <th>Nama Kelas</th>
                <th>Jurusan</th>
                <th>Tingkat</th>
                <th>Aksi</th>

            </tr>

            <?php

            $no = 1;

            while ($row = mysqli_fetch_assoc($data)) {

            ?>

            <tr>

                <td><?= $no++; ?></td>

                <td>
                    <?= htmlspecialchars($row['nama_kelas']); ?>
                </td>

                <td>
                    <?= htmlspecialchars($row['jurusan'] ?? '-'); ?>
                </td>

                <td>
                    <?= htmlspecialchars($row['tingkat'] ?? '-'); ?>
                </td>

                <td>

                    <a
                        class="btn-hapus"
                        href="kelola_kelas.php?hapus=<?= $row['id']; ?>"
                        onclick="return confirm('Hapus data kelas ini?')"
                    >
                        Hapus
                    </a>

                </td>

            </tr>

            <?php } ?>

        </table>

    </div>

</div>

</body>
</html>