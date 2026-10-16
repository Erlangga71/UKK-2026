<?php

session_start();

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

include "database.php";


// =====================================
// DATA PELANGGARAN
// =====================================

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


// =====================================
// DATA PETUGAS
// =====================================

$petugas = mysqli_query($koneksi, "
    SELECT id, nama, username
    FROM users
    WHERE aktif = 1
    ORDER BY nama ASC
");


// =====================================
// SIMPAN DATA
// =====================================

if (isset($_POST['simpan'])) {

    $pelanggaran_id = (int) $_POST['pelanggaran_id'];

    $tanggal = mysqli_real_escape_string(
        $koneksi,
        $_POST['tanggal']
    );

    $tindakan = mysqli_real_escape_string(
        $koneksi,
        $_POST['tindakan']
    );

    $catatan = mysqli_real_escape_string(
        $koneksi,
        $_POST['catatan']
    );


    // Kalau petugas tidak dipilih, isi NULL
    if (!empty($_POST['petugas_id'])) {

        $petugas_id = (int) $_POST['petugas_id'];

    } else {

        $petugas_id = "NULL";

    }


    $query = mysqli_query($koneksi, "
        INSERT INTO tindakan
        (
            pelanggaran_id,
            tanggal,
            tindakan,
            petugas_id,
            catatan
        )
        VALUES
        (
            '$pelanggaran_id',
            '$tanggal',
            '$tindakan',
            $petugas_id,
            '$catatan'
        )
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

    <title>Tambah Tindakan</title>

    <link rel="stylesheet" href="style.css">

</head>


<body>


<div class="header">

    <b>
        SISTEM INFORMASI PELANGGARAN SISWA
    </b>

    <a href="tindakan.php">
        Kembali
    </a>

</div>


<div class="container">

    <div class="box">

        <h2>Tambah Tindakan</h2>


        <?php if (isset($error)) { ?>

            <p style="color:red;">
                <?= htmlspecialchars($error); ?>
            </p>

        <?php } ?>


        <form method="POST">


            <!-- PELANGGARAN -->

            <label>
                Pelanggaran Siswa
            </label>

            <br><br>

            <select name="pelanggaran_id" required>

                <option value="">
                    -- Pilih Pelanggaran --
                </option>

                <?php while ($data = mysqli_fetch_assoc($pelanggaran)) { ?>

                    <option value="<?= $data['id']; ?>">

                        <?= htmlspecialchars($data['nama_siswa']); ?>

                        -
                        <?= htmlspecialchars($data['jenis']); ?>

                    </option>

                <?php } ?>

            </select>


            <br><br>


            <!-- TANGGAL -->

            <label>
                Tanggal
            </label>

            <br><br>

            <input
                type="date"
                name="tanggal"
                value="<?= date('Y-m-d'); ?>"
                required
            >


            <br><br>


            <!-- TINDAKAN -->

            <label>
                Tindakan
            </label>

            <br><br>

            <textarea
                name="tindakan"
                rows="4"
                placeholder="Masukkan tindakan yang diberikan..."
                required
            ></textarea>


            <br><br>


            <!-- PETUGAS -->

            <label>
                Petugas
            </label>

            <br><br>

            <select name="petugas_id">

                <option value="">
                    -- Tidak Dipilih --
                </option>

                <?php while ($data = mysqli_fetch_assoc($petugas)) { ?>

                    <option value="<?= $data['id']; ?>">

                        <?= htmlspecialchars($data['nama']); ?>

                        -
                        <?= htmlspecialchars($data['username']); ?>

                    </option>

                <?php } ?>

            </select>


            <br><br>


            <!-- CATATAN -->

            <label>
                Catatan
            </label>

            <br><br>

            <textarea
                name="catatan"
                rows="4"
                placeholder="Catatan tambahan..."
            ></textarea>


            <br><br>


            <button
                type="submit"
                name="simpan"
                class="btn"
            >
                Simpan
            </button>


        </form>

    </div>

</div>


</body>

</html>