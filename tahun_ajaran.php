<?php

require_once "cek_role.php";

wajib_role(['admin']);

include "database.php";

$query = mysqli_query($koneksi, "SELECT * FROM tahun_ajaran");

?>

<!DOCTYPE html>
<html>

<head>
    <title>Data Tahun Ajaran</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

<div class="header">
    <b>SISTEM INFORMASI PELANGGARAN SISWA</b>
    <a href="dashboard.php">Kembali</a>
</div>

<div class="container">

    <div class="box">

        <h2>Data Tahun Ajaran</h2>

        <a href="tambah_tahun_ajaran.php" class="btn-tambah">
            + Tambah Tahun Ajaran
        </a>

        <table>

            <tr>
                <th>No</th>
                <th>Nama Tahun Ajaran</th>
                <th>Semester</th>
                <th>Aksi</th>
            </tr>

            <?php
            $no = 1;

            while ($data = mysqli_fetch_assoc($query)) {
            ?>

            <tr>

                <td><?= $no++; ?></td>

                <td>
                    <?= htmlspecialchars($data['nama']); ?>
                </td>

                <td>
                    <?= htmlspecialchars($data['semester']); ?>
                </td>

                <td>

                    <a
                        href="edit_tahun_ajaran.php?id=<?= $data['id']; ?>"
                        class="btn-edit">
                        Edit
                    </a>

                    |

                    <a
                        href="hapus_tahun_ajaran.php?id=<?= $data['id']; ?>"
                        class="btn-hapus"
                        onclick="return confirm('Yakin ingin menghapus tahun ajaran ini?')">
                        Hapus
                    </a>

                </td>

            </tr>

            <?php
            }
            ?>

        </table>

    </div>

</div>

</body>
</html>