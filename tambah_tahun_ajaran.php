<?php

include "database.php";

$pesan = "";

if (isset($_POST['simpan'])) {

    $nama = $_POST['nama'];
    $tanggal_mulai = $_POST['tanggal_mulai'];
    $tanggal_selesai = $_POST['tanggal_selesai'];
    $aktif = $_POST['aktif'];

    $query = mysqli_query($koneksi, "
        INSERT INTO tahun_ajaran
        (nama, tanggal_mulai, tanggal_selesai, aktif)
        VALUES
        ('$nama', '$tanggal_mulai', '$tanggal_selesai', '$aktif')
    ");

    if ($query) {

        header("Location: tahun_ajaran.php");
        exit;

    } else {

        $pesan = "Data gagal ditambahkan: "
               . mysqli_error($koneksi);
    }
}

?>

<!DOCTYPE html>
<html>

<head>
    <title>Tambah Tahun Ajaran</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

<div class="header">

    <b>SISTEM INFORMASI PELANGGARAN SISWA</b>

    <a href="tahun_ajaran.php">Kembali</a>

</div>

<div class="container">

    <div class="box">

        <h2>Tambah Tahun Ajaran</h2>

        <?php if ($pesan != "") { ?>

            <p class="pesan">
                <?= htmlspecialchars($pesan); ?>
            </p>

        <?php } ?>

        <form method="POST">

            <label>Nama Tahun Ajaran</label>
            <br>

            <input
                type="text"
                name="nama"
                placeholder="Contoh: 2026/2027"
                required
            >

            <br><br>

            <label>Tanggal Mulai</label>
            <br>

            <input
                type="date"
                name="tanggal_mulai"
                required
            >

            <br><br>

            <label>Tanggal Selesai</label>
            <br>

            <input
                type="date"
                name="tanggal_selesai"
                required
            >

            <br><br>

            <label>Status</label>
            <br>

            <select name="aktif">

                <option value="1">
                    Aktif
                </option>

                <option value="0">
                    Tidak Aktif
                </option>

            </select>

            <br><br>

            <button
                type="submit"
                name="simpan"
                class="btn">
                Simpan
            </button>

            <a
                href="tahun_ajaran.php"
                class="btn-tambah">
                Batal
            </a>

        </form>

    </div>

</div>

</body>
</html>