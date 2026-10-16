<?php

include "database.php";

$id = $_GET['id'];

$data_query = mysqli_query($koneksi, "
    SELECT * FROM wali_kelas WHERE id='$id'
");

$data = mysqli_fetch_assoc($data_query);

if (!$data) {
    die("Data wali kelas tidak ditemukan.");
}

$guru = mysqli_query($koneksi, "SELECT id, nama FROM guru ORDER BY nama ASC");

$kelas = mysqli_query($koneksi, "SELECT id, nama_kelas FROM kelas ORDER BY nama_kelas ASC");

$tahun = mysqli_query($koneksi, "SELECT id, nama FROM tahun_ajaran ORDER BY id DESC");


if (isset($_POST['update'])) {

    $guru_id = $_POST['guru_id'];
    $kelas_id = $_POST['kelas_id'];
    $tahun_ajaran_id = $_POST['tahun_ajaran_id'];

    $update = mysqli_query($koneksi, "
        UPDATE wali_kelas SET
            guru_id='$guru_id',
            kelas_id='$kelas_id',
            tahun_ajaran_id='$tahun_ajaran_id'
        WHERE id='$id'
    ");

    if ($update) {

        header("Location: wali_kelas.php");
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
    <title>Edit Wali Kelas</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

<div class="header">

    <b>SISTEM INFORMASI PELANGGARAN SISWA</b>

    <a href="wali_kelas.php">Kembali</a>

</div>

<div class="container">

    <div class="box">

        <h2>Edit Wali Kelas</h2>

        <form method="POST">

            <label>Guru</label>
            <br>

            <select name="guru_id" required>

                <?php while ($item = mysqli_fetch_assoc($guru)) { ?>

                    <option
                        value="<?= $item['id']; ?>"
                        <?= $item['id'] == $data['guru_id'] ? 'selected' : ''; ?>>

                        <?= htmlspecialchars($item['nama']); ?>

                    </option>

                <?php } ?>

            </select>

            <br><br>

            <label>Kelas</label>
            <br>

            <select name="kelas_id" required>

                <?php while ($item = mysqli_fetch_assoc($kelas)) { ?>

                    <option
                        value="<?= $item['id']; ?>"
                        <?= $item['id'] == $data['kelas_id'] ? 'selected' : ''; ?>>

                        <?= htmlspecialchars($item['nama_kelas']); ?>

                    </option>

                <?php } ?>

            </select>

            <br><br>

            <label>Tahun Ajaran</label>
            <br>

            <select name="tahun_ajaran_id" required>

                <?php while ($item = mysqli_fetch_assoc($tahun)) { ?>

                    <option
                        value="<?= $item['id']; ?>"
                        <?= $item['id'] == $data['tahun_ajaran_id'] ? 'selected' : ''; ?>>

                        <?= htmlspecialchars($item['nama']); ?>

                    </option>

                <?php } ?>

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