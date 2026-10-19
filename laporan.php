<?php

require_once "cek_role.php";

wajib_role(['admin', 'guru', 'wali_kelas']);

include "database.php";

/*
|--------------------------------------------------------------------------
| AMBIL DATA LAPORAN
|--------------------------------------------------------------------------
*/

$query = mysqli_query($koneksi, "

    SELECT
        pelanggaran.id,
        siswa.nama AS nama_siswa,
        kelas.nama_kelas,
        jenis_pelanggaran.nama AS jenis_pelanggaran,
        jenis_pelanggaran.poin,
        pelanggaran.tanggal,
        pelanggaran.status

    FROM pelanggaran

    JOIN siswa
        ON pelanggaran.siswa_id = siswa.id

    LEFT JOIN penempatan_siswa
        ON siswa.id = penempatan_siswa.siswa_id

    LEFT JOIN kelas
        ON penempatan_siswa.kelas_id = kelas.id

    JOIN jenis_pelanggaran
        ON pelanggaran.jenis_id = jenis_pelanggaran.id

    ORDER BY pelanggaran.tanggal DESC, pelanggaran.id DESC

");

if (!$query) {
    die("Query error: " . mysqli_error($koneksi));
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Laporan Pelanggaran Siswa</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f6f8;
            color: #333;
        }

        .header {
            background: #687280;
            color: white;
            padding: 15px 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header a {
            color: white;
            text-decoration: none;
            background: #56606d;
            padding: 8px 14px;
            border-radius: 4px;
        }

        .container {
            margin-left: 220px;
            padding: 30px;
        }

        .box {
            background: white;
            border: 1px solid #ddd;
            border-radius: 7px;
            padding: 25px;
        }

        .judul {
            text-align: center;
            margin-bottom: 25px;
        }

        .judul h1 {
            margin: 0;
            font-size: 22px;
        }

        .judul h2 {
            margin: 8px 0;
            font-size: 18px;
        }

        .judul p {
            margin: 5px 0;
            color: #666;
        }

        .atas {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .atas h2 {
            margin: 0;
            font-size: 19px;
        }

        .btn {
            display: inline-block;
            background: #687280;
            color: white;
            text-decoration: none;
            border: none;
            padding: 9px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 13px;
        }

        .btn:hover {
            background: #56606d;
        }

        .table-container {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #687280;
            color: white;
            padding: 11px;
            text-align: left;
            font-size: 13px;
        }

        td {
            padding: 11px;
            border-bottom: 1px solid #ddd;
            font-size: 13px;
        }

        tr:hover {
            background: #f7f7f7;
        }

        .kosong {
            text-align: center;
            padding: 25px;
            color: #777;
        }

        .ttd {
            margin-top: 60px;
            text-align: right;
            padding-right: 60px;
        }

        .ttd p {
            margin: 5px;
        }

        @media (max-width: 700px) {

            .container {
                margin-left: 0;
                padding: 15px;
            }

            .header {
                display: block;
            }

            .header a {
                display: inline-block;
                margin-top: 10px;
            }

            .atas {
                display: block;
            }

            .atas .btn {
                margin-top: 10px;
            }

            .ttd {
                padding-right: 10px;
            }

        }

        @media print {

            body {
                background: white;
            }

            .header {
                display: none;
            }

            .container {
                margin-left: 0;
                padding: 0;
            }

            .box {
                border: none;
                padding: 0;
            }

            .btn {
                display: none;
            }

            table th {
                background: #ddd !important;
                color: black;
            }

        }

    </style>

</head>

<body>


<!-- HEADER -->

<div class="header">

    <b>
        SISTEM INFORMASI PELANGGARAN SISWA
    </b>

    <a href="dashboard.php">
        ← Kembali
    </a>

</div>


<!-- CONTENT -->

<div class="container">

    <div class="box">


        <!-- JUDUL LAPORAN -->

        <div class="judul">

            <h1>
                LAPORAN PELANGGARAN SISWA
            </h1>

            <h2>
                SISTEM INFORMASI PELANGGARAN SISWA
            </h2>

            <p>
                SMK
            </p>

            <p>
                Tanggal Cetak:
                <?= date('d-m-Y'); ?>
            </p>

        </div>


        <!-- TOMBOL -->

        <div class="atas">

            <h2>
                Data Pelanggaran
            </h2>

            <button
                onclick="window.print()"
                class="btn"
            >
                🖨 Cetak Laporan
            </button>

        </div>


        <!-- TABEL -->

        <div class="table-container">

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

                if (mysqli_num_rows($query) > 0) {

                    while ($data = mysqli_fetch_assoc($query)) {

                ?>

                    <tr>

                        <td>
                            <?= $no++; ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $data['nama_siswa'] ?? '-'
                            ); ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $data['nama_kelas'] ?? '-'
                            ); ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $data['jenis_pelanggaran'] ?? '-'
                            ); ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $data['poin'] ?? '0'
                            ); ?>
                        </td>

                        <td>
                            <?= !empty($data['tanggal'])
                                ? date(
                                    'd-m-Y',
                                    strtotime($data['tanggal'])
                                )
                                : '-';
                            ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $data['status'] ?? '-'
                            ); ?>
                        </td>

                    </tr>

                <?php

                    }

                } else {

                ?>

                    <tr>

                        <td
                            colspan="7"
                            class="kosong"
                        >
                            Belum ada data pelanggaran.
                        </td>

                    </tr>

                <?php

                }

                ?>

            </table>

        </div>


        <!-- TANDA TANGAN -->

        <div class="ttd">

            <p>
                Mengetahui,
            </p>

            <br><br><br>

            <p>
                __________________________
            </p>

        </div>


    </div>

</div>


</body>

</html>