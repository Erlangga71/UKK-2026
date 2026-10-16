<?php

include "database.php";

$id = $_GET['id'];

$data = mysqli_fetch_assoc(mysqli_query($koneksi, "
    SELECT * FROM tindakan
    WHERE id='$id'
"));

if (!$data) {
    die("Data tindakan tidak ditemukan.");
}

$pelanggaran = mysqli_query($koneksi, "
    SELECT
        pelanggaran.id,
        siswa.nama AS nama_siswa,
        jenis_pelanggaran.nama AS jenis
    FROM pelanggaran
    JOIN siswa
        ON pelanggaran.siswa_id = siswa.id
    JOIN jenis_pelanggaran
        ON pelanggaran.jenis_id = jenis_pelanggaran.id
    ORDER BY pelanggaran.id DESC
");

$guru = mysqli_query($koneksi, "
    SELECT id, nama
    FROM guru
    ORDER BY nama ASC
");

if (isset($_POST['update'])) {

    $pelanggaran_id = $_POST['pelanggaran_id'];
    $tanggal = $_POST['tanggal'];
    $tindakan = $_POST['tindakan'];
    $petugas_id = $_POST['petugas_id'];
    $catatan = $_POST['catatan'];

    $query = mysqli_query($koneksi, "
        UPDATE tindakan SET
            pelanggaran_id='$pelanggaran_id',
            tanggal='$tanggal',
            tindakan='$tindakan',
            petugas_id='$petugas_id',
            catatan='$catatan'
        WHERE id='$id'
    ");

    if ($query) {

        header("Location: tindakan.php");
        exit;

    } else {

        $error = mysqli_error($koneksi);

    }
}

?>

<!DOCTYPE html>
<html>

<head>

    <title>Edit Tindakan</title>

    <link rel="stylesheet" href="style.css">

</head>

<body>

<div class="header">

    <b>SISTEM INFORMASI PELANGGARAN SISWA</b>

    <a href="tindakan.php">
        Kembali
    </a>

</div>

<div class="container">

    <div class="box">

        <h2>Edit Tindakan</h2>

        <?php if (isset($error)) { ?>

            <p style="color:red;">
                <?= htmlspecialchars($error); ?>
            </p>

        <?php } ?>

        <form method="POST">

            <label>Pelanggaran Siswa</label>

            <br><br>

            <select name="pelanggaran_id" required>

                <?php while ($item = mysqli_fetch_assoc($pelanggaran)) { ?>

                    <option
                        value="<?= $item['id']; ?>"
                        <?= $item['id'] == $data['pelanggaran_id'] ? 'selected' : ''; ?>
                    >

                        <?= htmlspecialchars($item['nama_siswa']); ?>

                        -
                        <?= htmlspecialchars($item['jenis']); ?>

                    </option>

                <?php } ?>

            </select>

            <br><br>

            <label>Tanggal</label>

            <br><br>

            <input
                type="date"
                name="tanggal"
                value="<?= htmlspecialchars($data['tanggal']); ?>"
                required
            >

            <br><br>

            <label>Tindakan</label>

            <br><br>

            <textarea
                name="tindakan"
                rows="4"
                required
            ><?= htmlspecialchars($data['tindakan']); ?></textarea>

            <br><br>

            <label>Petugas / Guru</label>

            <br><br>

            <select name="petugas_id">

                <option value="">
                    -- Pilih Petugas --
                </option>

                <?php while ($item = mysqli_fetch_assoc($guru)) { ?>

                    <option
                        value="<?= $item['id']; ?>"
                        <?= $item['id'] == $data['petugas_id'] ? 'selected' : ''; ?>
                    >

                        <?= htmlspecialchars($item['nama']); ?>

                    </option>

                <?php } ?>

            </select>

            <br><br>

            <label>Catatan</label>

            <br><br>

            <textarea
                name="catatan"
                rows="4"
            ><?= htmlspecialchars($data['catatan'] ?? ''); ?></textarea>

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