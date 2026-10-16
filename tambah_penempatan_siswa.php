<?php

include "database.php";

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

$pesan = "";

if (isset($_POST['simpan'])) {

    $siswa_id = $_POST['siswa_id'];
    $kelas_id = $_POST['kelas_id'];
    $tahun_ajaran_id = $_POST['tahun_ajaran_id'];

    $query = mysqli_query($koneksi, "
        INSERT INTO penempatan_siswa
        (siswa_id, kelas_id, tahun_ajaran_id)
        VALUES
        ('$siswa_id', '$kelas_id', '$tahun_ajaran_id')
    ");

    if ($query) {

        header("Location: penempatan_siswa.php");
        exit;

    } else {

        $pesan = "Data gagal disimpan: " . mysqli_error($koneksi);

    }
}

?>

<!DOCTYPE html>
<html>

<head>
    <title>Tambah Penempatan Siswa</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

<div class="header">

    <b>SISTEM INFORMASI PELANGGARAN SISWA</b>

    <a href="penempatan_siswa.php">Kembali</a>

</div>

<div class="container">

    <div class="box">

        <h2>Tambah Penempatan Siswa</h2>

        <?php if ($pesan != "") { ?>

            <p style="color:red;">
                <?= htmlspecialchars($pesan); ?>
            </p>

        <?php } ?>

        <form method="POST">

            <label>Nama Siswa</label>
            <br>

            <select name="siswa_id" required>

                <option value="">
                    -- Pilih Siswa --
                </option>

                <?php while ($data = mysqli_fetch_assoc($siswa)) { ?>

                    <option value="<?= $data['id']; ?>">

                        <?= htmlspecialchars($data['nama']); ?>

                        - NIS

                        <?= htmlspecialchars($data['nis']); ?>

                    </option>

                <?php } ?>

            </select>

            <br><br>

            <label>Kelas</label>
            <br>

            <select name="kelas_id" required>

                <option value="">
                    -- Pilih Kelas --
                </option>

                <?php while ($data = mysqli_fetch_assoc($kelas)) { ?>

                    <option value="<?= $data['id']; ?>">

                        <?= htmlspecialchars($data['nama_kelas']); ?>

                    </option>

                <?php } ?>

            </select>

            <br><br>

            <label>Tahun Ajaran</label>
            <br>

            <select name="tahun_ajaran_id" required>

                <option value="">
                    -- Pilih Tahun Ajaran --
                </option>

                <?php while ($data = mysqli_fetch_assoc($tahun)) { ?>

                    <option value="<?= $data['id']; ?>">

                        <?= htmlspecialchars($data['nama']); ?>

                    </option>

                <?php } ?>

            </select>

            <br><br>

            <button
                type="submit"
                name="simpan"
                class="btn">
                Simpan
            </button>

            <a
                href="penempatan_siswa.php"
                class="btn-tambah">
                Batal
            </a>

        </form>

    </div>

</div>

</body>

</html>