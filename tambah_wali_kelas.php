<?php

include "database.php";

$guru = mysqli_query($koneksi, "SELECT id, nama FROM guru ORDER BY nama ASC");

$kelas = mysqli_query($koneksi, "SELECT id, nama_kelas FROM kelas ORDER BY nama_kelas ASC");

$tahun = mysqli_query($koneksi, "SELECT id, nama FROM tahun_ajaran ORDER BY id DESC");

$pesan = "";

if (isset($_POST['simpan'])) {

    $guru_id = $_POST['guru_id'];
    $kelas_id = $_POST['kelas_id'];
    $tahun_ajaran_id = $_POST['tahun_ajaran_id'];

    // Cek apakah kombinasi sudah ada
    $cek = mysqli_query($koneksi, "
        SELECT * FROM wali_kelas
        WHERE guru_id='$guru_id'
        AND kelas_id='$kelas_id'
        AND tahun_ajaran_id='$tahun_ajaran_id'
    ");

    if (mysqli_num_rows($cek) > 0) {

        $pesan = "Data wali kelas tersebut sudah ada.";

    } else {

        $query = mysqli_query($koneksi, "
            INSERT INTO wali_kelas
            (guru_id, kelas_id, tahun_ajaran_id)
            VALUES
            ('$guru_id', '$kelas_id', '$tahun_ajaran_id')
        ");

        if ($query) {

            header("Location: wali_kelas.php");
            exit;

        } else {

            $pesan = "Data gagal disimpan: "
                   . mysqli_error($koneksi);
        }
    }
}

?>

<!DOCTYPE html>
<html>

<head>
    <title>Tambah Wali Kelas</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

<div class="header">

    <b>SISTEM INFORMASI PELANGGARAN SISWA</b>

    <a href="wali_kelas.php">Kembali</a>

</div>

<div class="container">

    <div class="box">

        <h2>Tambah Wali Kelas</h2>

        <?php if ($pesan != "") { ?>

            <p style="color:red;">
                <?= htmlspecialchars($pesan); ?>
            </p>

        <?php } ?>

        <form method="POST">

            <label>Guru</label>
            <br>

            <select name="guru_id" required>

                <option value="">
                    -- Pilih Guru --
                </option>

                <?php while ($data = mysqli_fetch_assoc($guru)) { ?>

                    <option value="<?= $data['id']; ?>">
                        <?= htmlspecialchars($data['nama']); ?>
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
                href="wali_kelas.php"
                class="btn-tambah">
                Batal
            </a>

        </form>

    </div>

</div>

</body>
</html>