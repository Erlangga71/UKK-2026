<?php

include "database.php";

$id = $_GET['id'];

$query = mysqli_query($koneksi, "
    SELECT * FROM penempatan_siswa
    WHERE id='$id'
");

$data = mysqli_fetch_assoc($query);

if (!$data) {
    die("Data penempatan tidak ditemukan.");
}

$siswa = mysqli_query($koneksi, "
    SELECT id, nama, nis
    FROM siswa
    ORDER BY nama ASC
");

$kelas = mysqli_query($koneksi, "
    SELECT id, nama_kelas
    FROM kelas
    ORDER BY nama_kelas ASC
");

$tahun = mysqli_query($koneksi, "
    SELECT id, nama
    FROM tahun_ajaran
    ORDER BY id DESC
");

if (isset($_POST['update'])) {

    $siswa_id = $_POST['siswa_id'];
    $kelas_id = $_POST['kelas_id'];
    $tahun_ajaran_id = $_POST['tahun_ajaran_id'];

    $update = mysqli_query($koneksi, "
        UPDATE penempatan_siswa SET
            siswa_id='$siswa_id',
            kelas_id='$kelas_id',
            tahun_ajaran_id='$tahun_ajaran_id'
        WHERE id='$id'
    ");

    if ($update) {

        header("Location: penempatan_siswa.php");
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
    <title>Edit Penempatan Siswa</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

<div class="header">

    <b>SISTEM INFORMASI PELANGGARAN SISWA</b>

    <a href="penempatan_siswa.php">Kembali</a>

</div>

<div class="container">

    <div class="box">

        <h2>Edit Penempatan Siswa</h2>

        <form method="POST">

            <label>Nama Siswa</label>
            <br>

            <select name="siswa_id" required>

                <?php while ($item = mysqli_fetch_assoc($siswa)) { ?>

                    <option
                        value="<?= $item['id']; ?>"
                        <?= $item['id'] == $data['siswa_id'] ? 'selected' : ''; ?>>

                        <?= htmlspecialchars($item['nama']); ?>
                        - NIS <?= htmlspecialchars($item['nis']); ?>

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