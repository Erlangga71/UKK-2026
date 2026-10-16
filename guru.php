<?php

require_once "cek_role.php";

wajib_role(['admin']);

include "database.php";

$query = mysqli_query($koneksi, "
    SELECT *
    FROM guru
    ORDER BY id DESC
");

?>

<!DOCTYPE html>
<html>

<head>

    <title>Data Guru</title>

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

        <h2>Data Guru</h2>

        <a href="tambah_guru.php" class="btn-tambah">
            + Tambah Guru
        </a>

        <br><br>

        <table>

            <tr>
                <th>No</th>
                <th>NIP</th>
                <th>Nama Guru</th>
                <th>Email</th>
                <th>No. HP</th>
                <th>Aksi</th>
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
                    <?= htmlspecialchars($data['nip'] ?? '-'); ?>
                </td>

                <td>
                    <?= htmlspecialchars($data['nama']); ?>
                </td>

                <td>
                    <?= htmlspecialchars($data['email'] ?? '-'); ?>
                </td>

                <td>
                    <?= htmlspecialchars($data['no_hp'] ?? '-'); ?>
                </td>

                <td>

                    <a href="edit_guru.php?id=<?= $data['id']; ?>">
                        Edit
                    </a>

                    |

                    <a
                        href="hapus_guru.php?id=<?= $data['id']; ?>"
                        onclick="return confirm('Yakin ingin menghapus guru ini?');"
                    >
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