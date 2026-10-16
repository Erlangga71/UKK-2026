<?php

require_once "cek_role.php";

/*
|--------------------------------------------------------------------------
| AKSES
|--------------------------------------------------------------------------
| Admin, Guru, dan Wali Kelas boleh melihat data pelanggaran.
*/

wajib_role(['admin', 'guru', 'wali_kelas']);

include "database.php";


/*
|--------------------------------------------------------------------------
| AMBIL DATA PELANGGARAN
|--------------------------------------------------------------------------
*/

$query = mysqli_query($koneksi, "

    SELECT
        pelanggaran.id,
        siswa.nama AS nama_siswa,
        jenis_pelanggaran.nama AS jenis_pelanggaran,
        guru.nama AS nama_guru,
        pelanggaran.tanggal,
        pelanggaran.keterangan,
        pelanggaran.status

    FROM pelanggaran

    JOIN siswa
        ON pelanggaran.siswa_id = siswa.id

    JOIN jenis_pelanggaran
        ON pelanggaran.jenis_id = jenis_pelanggaran.id

    LEFT JOIN guru
        ON pelanggaran.guru_id = guru.id

    ORDER BY pelanggaran.id DESC

");


if (!$query) {

    die("Query error: " . mysqli_error($koneksi));

}

?>

<!DOCTYPE html>

<html>

<head>

    <title>Data Pelanggaran</title>

    <link rel="stylesheet" href="style.css">

</head>


<body>


<div class="header">

    <b>
        SISTEM INFORMASI PELANGGARAN SISWA
    </b>

    <a href="dashboard.php">
        Kembali
    </a>

</div>


<div class="container">

    <div class="box">

        <h2>
            Data Pelanggaran
        </h2>


        <?php if ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'guru') { ?>

            <a
                href="tambah_pelanggaran.php"
                class="btn-tambah"
            >
                + Tambah Pelanggaran
            </a>

        <?php } ?>


        <br>
        <br>


        <table>

            <tr>

                <th>No</th>

                <th>Siswa</th>

                <th>Jenis Pelanggaran</th>

                <th>Guru</th>

                <th>Tanggal</th>

                <th>Keterangan</th>

                <th>Status</th>

                <?php if ($_SESSION['role'] == 'admin') { ?>

                    <th>Aksi</th>

                <?php } ?>

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
                    <?= htmlspecialchars($data['nama_guru'] ?? '-'); ?>
                </td>


                <td>
                    <?= htmlspecialchars($data['tanggal']); ?>
                </td>


                <td>
                    <?= htmlspecialchars($data['keterangan'] ?? '-'); ?>
                </td>


                <td>
                    <?= htmlspecialchars($data['status']); ?>
                </td>


                <?php if ($_SESSION['role'] == 'admin') { ?>

                    <td>

                        <a
                            href="edit_pelanggaran.php?id=<?= $data['id']; ?>"
                        >
                            Edit
                        </a>

                        |

                        <a
                            href="hapus_pelanggaran.php?id=<?= $data['id']; ?>"
                            onclick="return confirm('Yakin ingin menghapus data ini?')"
                        >
                            Hapus
                        </a>

                    </td>

                <?php } ?>


            </tr>

            <?php

            }

            ?>


        </table>

    </div>

</div>


</body>

</html>