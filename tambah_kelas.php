<?php

require_once "cek_role.php";

wajib_role(['admin']);

include "database.php";

if (isset($_POST['simpan'])) {

    $nama_kelas = $_POST['nama_kelas'];
    $jurusan = $_POST['jurusan'];
    $tingkat = $_POST['tingkat'];
    $status_aktif = $_POST['status_aktif'];

    mysqli_query($koneksi, "
        INSERT INTO kelas
        (nama_kelas, jurusan, tingkat, status_aktif)
        VALUES
        ('$nama_kelas', '$jurusan', '$tingkat', '$status_aktif')
    ");

    header("Location: kelas.php");
    exit;
}

?>

<!DOCTYPE html>
<html>

<head>

    <title>Tambah Data Kelas</title>

    <link rel="stylesheet" href="style.css">

</head>

<body>

<div class="header">

    <b>SISTEM INFORMASI PELANGGARAN SISWA</b>

    <a href="kelas.php">Kembali</a>

</div>

<div class="container">

    <div class="box">

        <h2>Tambah Data Kelas</h2>

        <form method="POST">

            <label>Nama Kelas</label>
            <br>
            <input
                type="text"
                name="nama_kelas"
                required
            >

            <br><br>

            <label>Jurusan</label>
            <br>
            <input
                type="text"
                name="jurusan"
                placeholder="Contoh: RPL"
                required
            >

            <br><br>

            <label>Tingkat</label>
            <br>

            <select name="tingkat" required>

                <option value="">-- Pilih Tingkat --</option>
                <option value="X">X</option>
                <option value="XI">XI</option>
                <option value="XII">XII</option>

            </select>

            <br><br>

            <label>Status</label>
            <br>

            <select name="status_aktif">

                <option value="1">Aktif</option>
                <option value="0">Tidak Aktif</option>

            </select>

            <br><br>

            <button type="submit" name="simpan">
                Simpan
            </button>

            <a href="kelas.php">
                Batal
            </a>

        </form>

    </div>

</div>

</body>

</html>