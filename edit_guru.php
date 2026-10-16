<?php

require_once "cek_role.php";
wajib_role(['admin']);

include "database.php";

if (!isset($_GET['id'])) {
    header("Location: guru.php");
    exit;
}

$id = intval($_GET['id']);

$query = mysqli_query(
    $koneksi,
    "SELECT * FROM guru WHERE id = $id"
);

if (mysqli_num_rows($query) == 0) {
    header("Location: guru.php");
    exit;
}

$data = mysqli_fetch_assoc($query);

if (isset($_POST['simpan'])) {

    $nip = mysqli_real_escape_string($koneksi, $_POST['nip']);
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $email = mysqli_real_escape_string($koneksi, $_POST['email']);
    $no_hp = mysqli_real_escape_string($koneksi, $_POST['no_hp']);

    $update = mysqli_query($koneksi, "
        UPDATE guru SET
            nip = '$nip',
            nama = '$nama',
            email = '$email',
            no_hp = '$no_hp'
        WHERE id = $id
    ");

    if ($update) {

        header("Location: guru.php");
        exit;

    } else {

        $error = mysqli_error($koneksi);

    }
}

?>

<!DOCTYPE html>
<html>

<head>

    <title>Edit Data Guru</title>

    <link rel="stylesheet" href="style.css">

</head>

<body>

<div class="header">

    <b>SISTEM INFORMASI PELANGGARAN SISWA</b>

    <a href="guru.php">
        Kembali
    </a>

</div>

<div class="container">

    <div class="box">

        <h2>Edit Data Guru</h2>

        <?php if (isset($error)) { ?>

            <p style="color:red;">
                <?= htmlspecialchars($error); ?>
            </p>

        <?php } ?>

        <form method="POST">

            <label>NIP</label>

            <br><br>

            <input
                type="text"
                name="nip"
                value="<?= htmlspecialchars($data['nip'] ?? ''); ?>"
                required
            >

            <br><br>

            <label>Nama Guru</label>

            <br><br>

            <input
                type="text"
                name="nama"
                value="<?= htmlspecialchars($data['nama'] ?? ''); ?>"
                required
            >

            <br><br>

            <label>Email</label>

            <br><br>

            <input
                type="email"
                name="email"
                value="<?= htmlspecialchars($data['email'] ?? ''); ?>"
            >

            <br><br>

            <label>No. HP</label>

            <br><br>

            <input
                type="text"
                name="no_hp"
                value="<?= htmlspecialchars($data['no_hp'] ?? ''); ?>"
            >

            <br><br>

            <button
                type="submit"
                name="simpan"
                class="btn"
            >
                Simpan Perubahan
            </button>

        </form>

    </div>

</div>

</body>

</html>