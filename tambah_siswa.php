<?php

include "database.php";

$pesan = "";

if (isset($_POST['simpan'])) {

    $nis = $_POST['nis'];
    $nama = $_POST['nama'];
    $jenis_kelamin = $_POST['jenis_kelamin'];
    $alamat = $_POST['alamat'];

    // Cek apakah NIS sudah ada
    $cek = mysqli_query($koneksi, "SELECT * FROM siswa WHERE nis='$nis'");

    if (mysqli_num_rows($cek) > 0) {

        $pesan = "NIS $nis sudah digunakan. Silakan gunakan NIS lain.";

    } else {

        $query = mysqli_query($koneksi, "
            INSERT INTO siswa
            (nis, nama, jenis_kelamin, alamat)
            VALUES
            ('$nis', '$nama', '$jenis_kelamin', '$alamat')
        ");

        if ($query) {

            header("Location: siswa.php");
            exit;

        } else {

            $pesan = "Data gagal ditambahkan.";

        }
    }
}

?>

<!DOCTYPE html>
<html>

<head>
    <title>Tambah Siswa</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

<div class="header">

    <b>SISTEM INFORMASI PELANGGARAN SISWA</b>

    <a href="siswa.php">Kembali</a>

</div>

<div class="container">

    <div class="box">

        <h2>Tambah Data Siswa</h2>

        <?php if ($pesan != "") { ?>

            <p class="pesan">
                <?= htmlspecialchars($pesan); ?>
            </p>

        <?php } ?>

        <form method="POST">

            <label>NIS</label>
            <input type="text" name="nis" required>

            <br><br>

            <label>Nama Siswa</label>
            <input type="text" name="nama" required>

            <br><br>

            <label>Jenis Kelamin</label>

            <select name="jenis_kelamin" required>

                <option value="">-- Pilih --</option>
                <option value="L">Laki-laki</option>
                <option value="P">Perempuan</option>

            </select>

            <br><br>

            <label>Alamat</label>

            <textarea name="alamat" rows="4"></textarea>

            <br><br>

            <button type="submit" name="simpan" class="btn">
                Simpan
            </button>

            <a href="siswa.php" class="btn-tambah">
                Batal
            </a>

        </form>

    </div>

</div>

</body>

</html>