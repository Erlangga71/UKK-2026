<?php


require_once "cek_role.php";

wajib_role(['admin']);

include "database.php";

$query = mysqli_query($koneksi, "
    SELECT 
        penempatan_siswa.id,
        siswa.nama AS nama_siswa,
        kelas.nama_kelas,
        tahun_ajaran.nama AS tahun_ajaran
    FROM penempatan_siswa
    JOIN siswa ON penempatan_siswa.siswa_id = siswa.id
    JOIN kelas ON penempatan_siswa.kelas_id = kelas.id
    JOIN tahun_ajaran ON penempatan_siswa.tahun_ajaran_id = tahun_ajaran.id
    ORDER BY penempatan_siswa.id DESC
");

?>

<!DOCTYPE html>
<html>
<head>
    <title>Penempatan Siswa</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

<div class="header">
    <b>SISTEM INFORMASI PELANGGARAN SISWA</b>
    <a href="dashboard.php">Kembali</a>
</div>

<div class="container">

    <div class="box">

        <h2>Penempatan Siswa</h2>

        <a href="tambah_penempatan_siswa.php" class="btn-tambah">
            + Tambah Penempatan
        </a>

        <table>

            <tr>
                <th>No</th>
                <th>Nama Siswa</th>
                <th>Kelas</th>
                <th>Tahun Ajaran</th>
                <th>Aksi</th>
            </tr>

            <?php
            $no = 1;

            while ($data = mysqli_fetch_assoc($query)) {
            ?>

            <tr>

                <td><?= $no++; ?></td>

                <td>
                    <?= htmlspecialchars($data['nama_siswa']); ?>
                </td>

                <td>
                    <?= htmlspecialchars($data['nama_kelas']); ?>
                </td>

                <td>
                    <?= htmlspecialchars($data['tahun_ajaran']); ?>
                </td>

                <td>

                    <a href="edit_penempatan_siswa.php?id=<?= $data['id']; ?>"
                       class="btn-edit">
                        Edit
                    </a>

                    <a href="hapus_penempatan_siswa.php?id=<?= $data['id']; ?>"
                       class="btn-hapus"
                       onclick="return confirm('Yakin ingin menghapus data ini?')">
                        Hapus
                    </a>

                </td>

            </tr>

            <?php } ?>

        </table>

    </div>

</div>

</body>
</html>