<?php

require_once "cek_role.php";

wajib_role(['admin', 'guru', 'wali_kelas']);

include "database.php";

$query = mysqli_query($koneksi, "
    SELECT
        pelanggaran.id,
        siswa.nama AS nama_siswa,
        jenis_pelanggaran.nama AS jenis_pelanggaran,
        jenis_pelanggaran.poin,
        pelanggaran.tanggal,
        pelanggaran.status,
        kelas.nama_kelas
    FROM pelanggaran

    JOIN siswa
        ON pelanggaran.siswa_id = siswa.id

    JOIN jenis_pelanggaran
        ON pelanggaran.jenis_id = jenis_pelanggaran.id

    LEFT JOIN penempatan_siswa
        ON siswa.id = penempatan_siswa.siswa_id

    LEFT JOIN kelas
        ON penempatan_siswa.kelas_id = kelas.id

    ORDER BY pelanggaran.tanggal DESC
");

?>

<!DOCTYPE html>
<html>

<head>

    <title>Laporan Pelanggaran</title>

    <link rel="stylesheet" href="style.css">

    <style>

        .judul-laporan {
            text-align: center;
            margin-bottom: 25px;
        }

        .judul-laporan h2 {
            margin-bottom: 5px;
            color: #086177;
        }

        .judul-laporan p {
            margin: 3px;
        }

        .tanggal-cetak {
            text-align: right;
            margin-bottom: 15px;
        }

        .tombol {
            margin-bottom: 20px;
        }

        @media print {

            .header {
                display: none;
            }

            .tombol {
                display: none;
            }

            body {
                background: white;
            }

            .container {
                width: 100%;
                max-width: none;
                margin: 0;
            }

            .box {
                box-shadow: none;
                border: none;
            }

            table {
                width: 100%;
                border-collapse: collapse;
            }

            th,
            td {
                border: 1px solid #000;
                padding: 8px;
            }

        }

    </style>

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

        <div class="tombol">

            <button
                onclick="window.print()"
                class="btn"
            >
                Cetak Laporan
            </button>

        </div>


        <div class="judul-laporan">

            <h2>
                LAPORAN PELANGGARAN SISWA
            </h2>

            <p>
                SISTEM INFORMASI PELANGGARAN SISWA
            </p>

            <p>
                SMK
            </p>

        </div>


        <div class="tanggal-cetak">

            Tanggal Cetak:
            <?= date('d-m-Y'); ?>

        </div>


        <table>

            <tr>

                <th>No</th>

                <th>Nama Siswa</th>

                <th>Kelas</th>

                <th>Jenis Pelanggaran</th>

                <th>Poin</th>

                <th>Tanggal</th>

                <th>Status</th>

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
                    <?= htmlspecialchars($data['nama_kelas'] ?? '-'); ?>
                </td>

                <td>
                    <?= htmlspecialchars($data['jenis_pelanggaran']); ?>
                </td>

                <td>
                    <?= htmlspecialchars($data['poin']); ?>
                </td>

                <td>
                    <?= date(
                        'd-m-Y',
                        strtotime($data['tanggal'])
                    ); ?>
                </td>

                <td>
                    <?= htmlspecialchars($data['status']); ?>
                </td>

            </tr>

            <?php } ?>

        </table>


        <br><br>


        <div style="text-align:right;">

            Mengetahui,

            <br><br><br><br>

            __________________________

            <br>

            Petugas / Admin

        </div>

    </div>

</div>

</body>

</html>