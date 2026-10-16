<?php

include "database.php";

$id = $_GET['id'];

$query = mysqli_query(
    $koneksi,
    "SELECT * FROM tahun_ajaran WHERE id='$id'"
);

$data = mysqli_fetch_assoc($query);

if (!$data) {
    die("Data tahun ajaran tidak ditemukan.");
}

if (isset($_POST['update'])) {

    $nama = $_POST['nama'];
    $tanggal_mulai = $_POST['tanggal_mulai'];
    $tanggal_selesai = $_POST['tanggal_selesai'];
    $aktif = $_POST['aktif'];

    $update = mysqli_query($koneksi, "
        UPDATE tahun_ajaran SET
            nama='$nama',
            tanggal_mulai='$tanggal_mulai',
            tanggal_selesai='$tanggal_selesai',
            aktif='$aktif'
        WHERE id='$id'
    ");

    if ($update) {

        header("Location: tahun_ajaran.php");
        exit;

    } else {

        echo "Data gagal diubah: "
           . mysqli_error($koneksi);
    }
}

?>

<!DOCTYPE html>
<html>

<head>
    <title>Edit Tahun Ajaran</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

<div class="header">

    <b>SISTEM INFORMASI PELANGGARAN SISWA</b>

    <a href="tahun_ajaran.php">Kembali</a>

</div>

<div class="container">

    <div class="box">

        <h2>Edit Tahun Ajaran</h2>

        <form method="POST">

            <label>Nama Tahun Ajaran</label>
            <br>

            <input
                type="text"
                name="nama"
                value="<?= htmlspecialchars($data['nama']); ?>"
                required
            >

            <br><br>

            <label>Tanggal Mulai</label>
            <br>

            <input
                type="date"
                name="tanggal_mulai"
                value="<?= $data['tanggal_mulai']; ?>"
                required
            >

            <br><br>

            <label>Tanggal Selesai</label>
            <br>

            <input
                type="date"
                name="tanggal_selesai"
                value="<?= $data['tanggal_selesai']; ?>"
                required
            >

            <br><br>

            <label>Status</label>
            <br>

            <select name="aktif">

                <option
                    value="1"
                    <?= $data['aktif'] == 1 ? 'selected' : ''; ?>>
                    Aktif
                </option>

                <option
                    value="0"
                    <?= $data['aktif'] == 0 ? 'selected' : ''; ?>>
                    Tidak Aktif
                </option>

            </select>

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