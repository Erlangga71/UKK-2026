<?php

require_once "cek_role.php";

wajib_role(['admin', 'guru']);

include "database.php";

$query = mysqli_query($koneksi, "
    SELECT
        tindakan.id,
        siswa.nama AS nama_siswa,
        jenis_pelanggaran.nama AS jenis_pelanggaran,
        tindakan.tanggal,
        tindakan.tindakan,
        users.nama AS petugas,
        tindakan.catatan
    FROM tindakan

    JOIN pelanggaran
        ON tindakan.pelanggaran_id = pelanggaran.id

    JOIN siswa
        ON pelanggaran.siswa_id = siswa.id

    JOIN jenis_pelanggaran
        ON pelanggaran.jenis_id = jenis_pelanggaran.id

    LEFT JOIN users
        ON tindakan.petugas_id = users.id

    ORDER BY tindakan.id DESC
");

?>

<!DOCTYPE html>
<html>

<head>

    <title>Data Tindakan</title>

    <link rel="stylesheet" href="style.css">

</head>

<body>

<div class="header">

    <b>SISTEM INFORMASI PELANGGARAN SISWA</b>

    <a href="dashboard.php">
        Kembali
    </a>

</div>


<div class="container">

    <div class="box">

        <h2>Data Tindakan</h2>

        <a href="tambah_tindakan.php" class="btn-tambah">
            + Tambah Tindakan
        </a>

        <br><br>

        <table>

            <tr>

                <th>No</th>
                <th>Siswa</th>
                <th>Pelanggaran</th>
                <th>Tanggal</th>
                <th>Tindakan</th>
                <th>Petugas</th>
                <th>Catatan</th>

            </tr>


            <?php

            $no = 1;

            while ($data = mysqli_fetch_assoc($query)) {

            ?>

            <tr>

                <td>
                    <?= $no++; ?>
                </td>

                <td>
                    <?= htmlspecialchars($data['nama_siswa']); ?>
                </td>

                <td>
                    <?= htmlspecialchars($data['jenis_pelanggaran']); ?>
                </td>

                <td>
                    <?= htmlspecialchars($data['tanggal']); ?>
                </td>

                <td>
                    <?= htmlspecialchars($data['tindakan']); ?>
                </td>

                <td>
                    <?= htmlspecialchars($data['petugas'] ?? '-'); ?>
                </td>

                <td>
                    <?= htmlspecialchars($data['catatan'] ?? '-'); ?>
                </td>

            </tr>

            <?php } ?>

        </table>

    </div>

</div>

</body>

</html>