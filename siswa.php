<?php

require_once "cek_role.php";

wajib_role(['admin']);

include "database.php";

$query = mysqli_query($koneksi, "
    SELECT *
    FROM siswa
    ORDER BY id DESC
");

?>

<!DOCTYPE html>
<html>

<head>

    <title>Data Siswa</title>

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

        <h2>Data Siswa</h2>

        <a href="tambah_siswa.php" class="btn-tambah">
            + Tambah Siswa
        </a>

        <br><br>


        <table>

            <tr>

                <th>No</th>
                <th>NIS</th>
                <th>Nama</th>
                <th>Jenis Kelamin</th>
                <th>Alamat</th>
                <th>No. HP Orang Tua</th>
                <th>Status</th>
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
                    <?= htmlspecialchars($data['nis']); ?>
                </td>

                <td>
                    <?= htmlspecialchars($data['nama']); ?>
                </td>

                <td>
                    <?= htmlspecialchars($data['jenis_kelamin']); ?>
                </td>

                <td>
                    <?= htmlspecialchars($data['alamat'] ?? '-'); ?>
                </td>

                <td>
                    <?= htmlspecialchars($data['no_hp_ortu'] ?? '-'); ?>
                </td>

                <td>

                    <?php if ($data['aktif'] == 1) { ?>

                        Aktif

                    <?php } else { ?>

                        Tidak Aktif

                    <?php } ?>

                </td>

                <td>

                    <a href="edit_siswa.php?id=<?= $data['id']; ?>">
                        Edit
                    </a>

                    |

                    <a
                        href="hapus_siswa.php?id=<?= $data['id']; ?>"
                        onclick="return confirm('Yakin ingin menghapus siswa ini?');"
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