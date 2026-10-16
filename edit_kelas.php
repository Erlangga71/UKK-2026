<?php

include "database.php";

$id = $_GET['id'];

$query = mysqli_query(
    $koneksi,
    "SELECT * FROM kelas WHERE id='$id'"
);

$data = mysqli_fetch_assoc($query);

if (!$data) {
    die("Data kelas tidak ditemukan.");
}

if (isset($_POST['update'])) {

    $nama_kelas = $_POST['nama_kelas'];

    $update = mysqli_query($koneksi, "
        UPDATE kelas
        SET nama_kelas='$nama_kelas'
        WHERE id='$id'
    ");

    if ($update) {

        header("Location: kelas.php");
        exit;

    } else {

        echo "Data kelas gagal diubah: "
           . mysqli_error($koneksi);
    }
}

?>

<!DOCTYPE html>
<html>

<head>
    <title>Edit Kelas</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

<div class="header">

    <b>SISTEM INFORMASI PELANGGARAN SISWA</b>

    <a href="kelas.php">Kembali</a>

</div>

<div class="container">

    <div class="box">

        <h2>Edit Data Kelas</h2>

        <form method="POST">

            <label>Nama Kelas</label>
            <br>

            <input
                type="text"
                name="nama_kelas"
                value="<?= htmlspecialchars($data['nama_kelas']); ?>"
                required
            >

            <br><br>

            <button
                type="submit"
                name="update"
                class="btn">
                Simpan Perubahan
            </button>

        </form>

    </div>

</div>

</body>
</html>