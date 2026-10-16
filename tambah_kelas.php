<?php

include "database.php";

$pesan = "";

if (isset($_POST['simpan'])) {

    $nama_kelas = $_POST['nama_kelas'];

    // Cek nama kelas
    $cek = mysqli_query(
        $koneksi,
        "SELECT * FROM kelas WHERE nama_kelas='$nama_kelas'"
    );

    if (mysqli_num_rows($cek) > 0) {

        $pesan = "Nama kelas tersebut sudah ada.";

    } else {

        $query = mysqli_query($koneksi, "
            INSERT INTO kelas (nama_kelas)
            VALUES ('$nama_kelas')
        ");

        if ($query) {

            header("Location: kelas.php");
            exit;

        } else {

            $pesan = "Data kelas gagal ditambahkan: "
                   . mysqli_error($koneksi);
        }
    }
}

?>

<!DOCTYPE html>
<html>

<head>
    <title>Tambah Kelas</title>
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

        <?php if ($pesan != "") { ?>

            <p class="pesan">
                <?= htmlspecialchars($pesan); ?>
            </p>

        <?php } ?>

        <form method="POST">

            <label>Nama Kelas</label>
            <br>

            <input
                type="text"
                name="nama_kelas"
                placeholder="Contoh: X RPL 1"
                required
            >

            <br><br>

            <button
                type="submit"
                name="simpan"
                class="btn">
                Simpan
            </button>

            <a
                href="kelas.php"
                class="btn-tambah">
                Batal
            </a>

        </form>

    </div>

</div>

</body>
</html>