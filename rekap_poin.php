<?php

session_start();

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

include "database.php";

$query = mysqli_query($koneksi, "
    SELECT
        siswa.id,
        siswa.nis,
        siswa.nama,
        COALESCE(SUM(jenis_pelanggaran.poin), 0) AS total_poin
    FROM siswa

    LEFT JOIN pelanggaran
        ON siswa.id = pelanggaran.siswa_id

    LEFT JOIN jenis_pelanggaran
        ON pelanggaran.jenis_id = jenis_pelanggaran.id

    GROUP BY
        siswa.id,
        siswa.nis,
        siswa.nama

    ORDER BY total_poin DESC, siswa.nama ASC
");

if (!$query) {
    die("Query error: " . mysqli_error($koneksi));
}

?>

<!DOCTYPE html>
<html>

<head>

    <title>Rekap Poin</title>

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
            max-width: 1000px;
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

        .poin {
            font-weight: bold;
            color: #dc2626;
        }

        .aman {
            color: #16a34a;
            font-weight: bold;
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

        <h2>Rekap Poin Siswa</h2>

        <p>
            Daftar total poin pelanggaran setiap siswa.
        </p>

        <table>

            <tr>
                <th>No</th>
                <th>NIS</th>
                <th>Nama Siswa</th>
                <th>Total Poin</th>
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
                    <?= htmlspecialchars($data['nis']); ?>
                </td>

                <td>
                    <?= htmlspecialchars($data['nama']); ?>
                </td>

                <td>

                    <?php if ($data['total_poin'] > 0) { ?>

                        <span class="poin">
                            <?= htmlspecialchars($data['total_poin']); ?> poin
                        </span>

                    <?php } else { ?>

                        <span class="aman">
                            0 poin
                        </span>

                    <?php } ?>

                </td>

            </tr>

            <?php

                }

            } else {

            ?>

            <tr>

                <td colspan="4" class="kosong">
                    Belum ada data siswa.
                </td>

            </tr>

            <?php } ?>

        </table>

    </div>

</div>

</body>

</html>