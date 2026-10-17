<?php

require_once "cek_role.php";

wajib_role(['admin']);

include "database.php";

$query = mysqli_query($koneksi, "SELECT * FROM kelas");

?>

<!DOCTYPE html>
<html>

<head>

    <title>Data Kelas</title>

    <link rel="stylesheet" href="style.css">

</head>

<body>

<div class="header">

    <b>SISTEM INFORMASI PELANGGARAN SISWA</b>

    <a href="dashboard.php">Kembali</a>

</div>

<div class="container">

    <div class="box">

        <h2>Data Kelas</h2>

        <a href="tambah_kelas.php" class="btn-tambah">
            + Tambah Kelas
        </a>

        <br><br>

        <table>

            <tr>
                <th>No</th>
                <th>Nama Kelas</th>
                <th>Jurusan</th>
                <th>Tingkat</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>

            <?php
            $no = 1;

            while ($data = mysqli_fetch_assoc($query)) {
            ?>

            <tr>

                <td><?= $no++; ?></td>

                <td>
                    <?= htmlspecialchars($data['nama_kelas']); ?>
                </td>

                <td>
                    <?= htmlspecialchars($data['jurusan'] ?? '-'); ?>
                </td>

                <td>
                    <?= htmlspecialchars($data['tingkat'] ?? '-'); ?>
                </td>

                <td>
                    <?= $data['status_aktif'] == 1
                        ? 'Aktif'
                        : 'Tidak Aktif'; ?>
                </td>

                <td>

                    <a
                        href="edit_kelas.php?id=<?= $data['id']; ?>"
                        class="btn-edit">
                        Edit
                    </a>

                    |

                    <a
                        href="hapus_kelas.php?id=<?= $data['id']; ?>"
                        class="btn-hapus"
                        onclick="return confirm('Yakin ingin menghapus kelas ini?')">
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