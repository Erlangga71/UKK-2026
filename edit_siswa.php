<?php

include "database.php";

$id = $_GET['id'] ?? 0;

/* Ambil data siswa */
$query = mysqli_query($koneksi, "
    SELECT *
    FROM siswa
    WHERE id='$id'
");

$data = mysqli_fetch_assoc($query);

if (!$data) {
    die("Data siswa tidak ditemukan.");
}


/* Proses update */
if (isset($_POST['update'])) {

    $nis = $_POST['nis'];
    $nama = $_POST['nama'];
    $jenis_kelamin = $_POST['jenis_kelamin'];
    $alamat = $_POST['alamat'];
    $no_hp_ortu = $_POST['no_hp_ortu'];
    $aktif = $_POST['aktif'];

    $update = mysqli_query($koneksi, "
        UPDATE siswa SET
            nis='$nis',
            nama='$nama',
            jenis_kelamin='$jenis_kelamin',
            alamat='$alamat',
            no_hp_ortu='$no_hp_ortu',
            aktif='$aktif'
        WHERE id='$id'
    ");

    if ($update) {

        header("Location: siswa.php");
        exit;

    } else {

        $error = mysqli_error($koneksi);

    }
}

?>

<!DOCTYPE html>
<html>

<head>

    <title>Edit Data Siswa</title>

    <link rel="stylesheet" href="style.css">

</head>

<body>

<div class="header">

    <b>SISTEM INFORMASI PELANGGARAN SISWA</b>

    <a href="siswa.php">
        Kembali
    </a>

</div>


<div class="container">

    <div class="box">

        <h2>Edit Data Siswa</h2>

        <?php if (isset($error)) { ?>

            <p style="color:red;">
                <?= htmlspecialchars($error); ?>
            </p>

        <?php } ?>


        <form method="POST">

            <label>NIS</label>

            <br><br>

            <input
                type="text"
                name="nis"
                value="<?= htmlspecialchars($data['nis']); ?>"
                required
            >

            <br><br>


            <label>Nama Siswa</label>

            <br><br>

            <input
                type="text"
                name="nama"
                value="<?= htmlspecialchars($data['nama']); ?>"
                required
            >

            <br><br>


            <label>Jenis Kelamin</label>

            <br><br>

            <select name="jenis_kelamin" required>

                <option value="L"
                    <?= $data['jenis_kelamin'] == 'L' ? 'selected' : ''; ?>>
                    Laki-laki
                </option>

                <option value="P"
                    <?= $data['jenis_kelamin'] == 'P' ? 'selected' : ''; ?>>
                    Perempuan
                </option>

            </select>

            <br><br>


            <label>Alamat</label>

            <br><br>

            <textarea
                name="alamat"
                rows="4"
            ><?= htmlspecialchars($data['alamat'] ?? ''); ?></textarea>

            <br><br>


            <label>No. HP Orang Tua</label>

            <br><br>

            <input
                type="text"
                name="no_hp_ortu"
                value="<?= htmlspecialchars($data['no_hp_ortu'] ?? ''); ?>"
            >

            <br><br>


            <label>Status</label>

            <br><br>

            <select name="aktif">

                <option value="1"
                    <?= $data['aktif'] == 1 ? 'selected' : ''; ?>>
                    Aktif
                </option>

                <option value="0"
                    <?= $data['aktif'] == 0 ? 'selected' : ''; ?>>
                    Tidak Aktif
                </option>

            </select>

            <br><br>


            <button
                type="submit"
                name="update"
                class="btn"
            >
                Simpan Perubahan
            </button>

        </form>

    </div>

</div>

</body>

</html>