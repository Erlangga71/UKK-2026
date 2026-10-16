<?php

include "database.php";

$id = $_GET['id'];

$data = mysqli_fetch_assoc(mysqli_query($koneksi, "
    SELECT * FROM pelanggaran
    WHERE id='$id'
"));

if (!$data) {
    die("Data pelanggaran tidak ditemukan.");
}

$siswa = mysqli_query($koneksi, "
    SELECT id, nama, nis
    FROM siswa
    ORDER BY nama ASC
");

$jenis = mysqli_query($koneksi, "
    SELECT id, nama
    FROM jenis_pelanggaran
    ORDER BY nama ASC
");

$guru = mysqli_query($koneksi, "
    SELECT id, nama
    FROM guru
    ORDER BY nama ASC
");

if (isset($_POST['update'])) {

    $siswa_id = $_POST['siswa_id'];
    $jenis_id = $_POST['jenis_id'];
    $guru_id = $_POST['guru_id'];
    $tanggal = $_POST['tanggal'];
    $keterangan = $_POST['keterangan'];
    $status = $_POST['status'];

    $query = mysqli_query($koneksi, "
        UPDATE pelanggaran SET
            siswa_id='$siswa_id',
            jenis_id='$jenis_id',
            guru_id='$guru_id',
            tanggal='$tanggal',
            keterangan='$keterangan',
            status='$status'
        WHERE id='$id'
    ");

    if ($query) {

        header("Location: pelanggaran.php");
        exit;

    } else {

        $error = mysqli_error($koneksi);

    }
}

?>

<!DOCTYPE html>
<html>

<head>

    <title>Edit Pelanggaran</title>

    <link rel="stylesheet" href="style.css">

</head>

<body>

<div class="header">

    <b>SISTEM INFORMASI PELANGGARAN SISWA</b>

    <a href="pelanggaran.php">
        Kembali
    </a>

</div>

<div class="container">

    <div class="box">

        <h2>Edit Pelanggaran</h2>

        <?php if (isset($error)) { ?>

            <p style="color:red;">
                <?= htmlspecialchars($error); ?>
            </p>

        <?php } ?>

        <form method="POST">

            <label>Nama Siswa</label>

            <br><br>

            <select name="siswa_id" required>

                <?php while ($item = mysqli_fetch_assoc($siswa)) { ?>

                    <option
                        value="<?= $item['id']; ?>"
                        <?= $item['id'] == $data['siswa_id'] ? 'selected' : ''; ?>
                    >

                        <?= htmlspecialchars($item['nama']); ?>
                        - NIS <?= htmlspecialchars($item['nis']); ?>

                    </option>

                <?php } ?>

            </select>

            <br><br>

            <label>Jenis Pelanggaran</label>

            <br><br>

            <select name="jenis_id" required>

                <?php while ($item = mysqli_fetch_assoc($jenis)) { ?>

                    <option
                        value="<?= $item['id']; ?>"
                        <?= $item['id'] == $data['jenis_id'] ? 'selected' : ''; ?>
                    >

                        <?= htmlspecialchars($item['nama']); ?>

                    </option>

                <?php } ?>

            </select>

            <br><br>

            <label>Guru</label>

            <br><br>

            <select name="guru_id">

                <option value="">-- Tidak Dipilih --</option>

                <?php while ($item = mysqli_fetch_assoc($guru)) { ?>

                    <option
                        value="<?= $item['id']; ?>"
                        <?= $item['id'] == $data['guru_id'] ? 'selected' : ''; ?>
                    >

                        <?= htmlspecialchars($item['nama']); ?>

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

            <label>Keterangan</label>

            <br><br>

            <textarea
                name="keterangan"
                rows="4"
            ><?= htmlspecialchars($data['keterangan'] ?? ''); ?></textarea>

            <br><br>

            <label>Status</label>

            <br><br>

            <select name="status" required>

                <option
                    value="tercatat"
                    <?= $data['status'] == 'tercatat' ? 'selected' : ''; ?>
                >
                    Tercatat
                </option>

                <option
                    value="ditindaklanjuti"
                    <?= $data['status'] == 'ditindaklanjuti' ? 'selected' : ''; ?>
                >
                    Ditindaklanjuti
                </option>

                <option
                    value="selesai"
                    <?= $data['status'] == 'selesai' ? 'selected' : ''; ?>
                >
                    Selesai
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