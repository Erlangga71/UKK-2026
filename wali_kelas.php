<?php

require_once "cek_role.php";

wajib_role(['admin']);

include "database.php";

$query = mysqli_query($koneksi, "
    SELECT 
        wali_kelas.id,
        guru.nama AS nama_guru,
        kelas.nama_kelas,
        tahun_ajaran.nama AS tahun_ajaran
    FROM wali_kelas
    JOIN guru ON wali_kelas.guru_id = guru.id
    JOIN kelas ON wali_kelas.kelas_id = kelas.id
    JOIN tahun_ajaran ON wali_kelas.tahun_ajaran_id = tahun_ajaran.id
");

?>

<!DOCTYPE html>
<html>
<head>
    <title>Data Wali Kelas</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

<div class="header">
    <b>SISTEM INFORMASI PELANGGARAN SISWA</b>

    <a href="dashboard.php">Kembali</a>
</div>

<div class="container">

    <div class="box">

        <h2>Data Wali Kelas</h2>

        <a href="tambah_wali_kelas.php" class="btn-tambah">
            + Tambah Wali Kelas
        </a>

        <table>

            <tr>
                <th>No</th>
                <th>Guru</th>
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
                    <?= htmlspecialchars($data['nama_guru']); ?>
                </td>

                <td>
                    <?= htmlspecialchars($data['nama_kelas']); ?>
                </td>

                <td>
                    <?= htmlspecialchars($data['tahun_ajaran']); ?>
                </td>

                <td>

                    <a href="edit_wali_kelas.php?id=<?= $data['id']; ?>">
                        Edit
                    </a>

                    |

                    <a href="hapus_wali_kelas.php?id=<?= $data['id']; ?>"
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