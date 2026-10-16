<?php

session_start();

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

include "database.php";

$query = mysqli_query($koneksi, "
    SELECT
        pelanggaran.id,
        siswa.nama AS nama_siswa,
        jenis_pelanggaran.nama AS jenis,
        jenis_pelanggaran.poin,
        pelanggaran.tanggal,
        pelanggaran.status,
        pelanggaran.keterangan
    FROM pelanggaran

    INNER JOIN siswa
        ON pelanggaran.siswa_id = siswa.id

    INNER JOIN jenis_pelanggaran
        ON pelanggaran.jenis_id = jenis_pelanggaran.id

    ORDER BY pelanggaran.tanggal DESC
");

if (!$query) {
    die("Query error: " . mysqli_error($koneksi));
}

?>

<!DOCTYPE html>
<html>

<head>

    <title>Riwayat Pelanggaran</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #eef4f7;
            color: #333;
        }

        .header {
            background: #086177;
            color: white;
            padding: 18px 30px;

            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header a {
            color: white;
            text-decoration: none;
            background: #dc2626;
            padding: 8px 15px;
            border-radius: 5px;
        }

        .container {
            width: 95%;
            max-width: 1100px;
            margin: 30px auto;
        }

        .box {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }

        h2 {
            color: #086177;
            margin-top: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th {
            background: #086177;
            color: white;
            padding: 12px;
            text-align: left;
        }

        td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }

        tr:hover {
            background: #f1f5f9;
        }

        .status {
            padding: 5px 9px;
            border-radius: 5px;
            font-size: 13px;
            background: #e0f2fe;
        }

        .kosong {
            text-align: center;
            padding: 20px;
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

        <h2>Riwayat Pelanggaran</h2>

        <p>
            Daftar riwayat pelanggaran siswa.
        </p>

        <table>

            <tr>

                <th>No</th>
                <th>Nama Siswa</th>
                <th>Jenis Pelanggaran</th>
                <th>Poin</th>
                <th>Tanggal</th>
                <th>Status</th>
                <th>Keterangan</th>

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
                    <?= htmlspecialchars($data['nama_siswa']); ?>
                </td>

                <td>
                    <?= htmlspecialchars($data['jenis']); ?>
                </td>

                <td>
                    <?= htmlspecialchars($data['poin']); ?>
                </td>

                <td>
                    <?= htmlspecialchars($data['tanggal']); ?>
                </td>

                <td>
                    <span class="status">
                        <?= htmlspecialchars($data['status']); ?>
                    </span>
                </td>

                <td>
                    <?= htmlspecialchars($data['keterangan'] ?? '-'); ?>
                </td>

            </tr>

            <?php

                }

            } else {

            ?>

            <tr>

                <td colspan="7" class="kosong">
                    Belum ada riwayat pelanggaran.
                </td>

            </tr>

            <?php } ?>

        </table>

    </div>

</div>

</body>

</html>