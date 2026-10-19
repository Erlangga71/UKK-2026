<?php

session_start();

require_once "cek_role.php";
include "database.php";

/*
|--------------------------------------------------------------------------
| CEK LOGIN & ROLE
|--------------------------------------------------------------------------
*/

wajib_role(['admin', 'guru', 'wali_kelas']);


/*
|--------------------------------------------------------------------------
| AMBIL ROLE
|--------------------------------------------------------------------------
*/

$role = $_SESSION['role'] ?? '';


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

    LEFT JOIN siswa
        ON pelanggaran.siswa_id = siswa.id

    LEFT JOIN jenis_pelanggaran
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
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Data Pelanggaran</title>


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


        /* HEADER */

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

            font-size: 13px;

        }


        .header a:hover {

            background: #4f5965;

        }


        /* CONTAINER */

        .container {

            width: 95%;

            max-width: 1200px;

            margin: 25px auto;

        }


        /* BOX */

        .box {

            background: white;

            border: 1px solid #ddd;

            border-radius: 7px;

            padding: 22px;

        }


        .box h2 {

            margin-top: 0;

            margin-bottom: 20px;

            font-size: 22px;

        }


        /* TOMBOL TAMBAH */

        .btn-tambah {

            display: inline-block;

            background: #687280;

            color: white;

            text-decoration: none;

            padding: 9px 14px;

            border-radius: 4px;

            font-size: 13px;

        }


        .btn-tambah:hover {

            background: #56606d;

        }


        /* TABLE */

        .table-container {

            overflow-x: auto;

        }


        table {

            width: 100%;

            border-collapse: collapse;

            margin-top: 15px;

        }


        table th {

            background: #687280;

            color: white;

            padding: 11px;

            text-align: left;

            font-size: 13px;

            border: 1px solid #5c6570;

        }


        table td {

            padding: 11px;

            border: 1px solid #ddd;

            font-size: 13px;

            vertical-align: top;

        }


        table tr:hover {

            background: #f7f7f7;

        }


        /* STATUS */

        .status {

            display: inline-block;

            padding: 5px 8px;

            border-radius: 4px;

            font-size: 12px;

        }


        .tercatat {

            background: #eee;

            color: #444;

        }


        .ditindaklanjuti {

            background: #fff3cd;

            color: #856404;

        }


        .selesai {

            background: #d4edda;

            color: #155724;

        }


        /* AKSI */

        .aksi a {

            text-decoration: none;

            font-size: 12px;

        }


        .edit {

            color: #086177;

        }


        .hapus {

            color: #b42323;

        }


        /* KOSONG */

        .kosong {

            text-align: center;

            color: #777;

            padding: 25px;

        }


        /* RESPONSIVE */

        @media (max-width: 700px) {

            .header {

                display: block;

            }

            .header a {

                display: inline-block;

                margin-top: 10px;

            }

            .container {

                width: 96%;

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

        <h2>
            Data Pelanggaran
        </h2>


        <!-- TOMBOL TAMBAH -->

        <?php if ($role == 'admin' || $role == 'guru') { ?>

            <a href="tambah_pelanggaran.php" class="btn-tambah">
                + Tambah Pelanggaran
            </a>

        <?php } ?>


        <!-- TABLE -->

        <div class="table-container">

            <table>

                <tr>

                    <th>No</th>

                    <th>Siswa</th>

                    <th>Jenis Pelanggaran</th>

                    <th>Guru</th>

                    <th>Tanggal</th>

                    <th>Keterangan</th>

                    <th>Status</th>


                    <?php if ($role == 'admin') { ?>

                        <th>Aksi</th>

                    <?php } ?>

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
                                $data['jenis_pelanggaran'] ?? '-'
                            ); ?>

                        </td>


                        <td>

                            <?= htmlspecialchars(
                                $data['nama_guru'] ?? '-'
                            ); ?>

                        </td>


                        <td>

                            <?= htmlspecialchars(
                                $data['tanggal'] ?? '-'
                            ); ?>

                        </td>


                        <td>

                            <?= htmlspecialchars(
                                $data['keterangan'] ?? '-'
                            ); ?>

                        </td>


                        <td>

                            <?php

                            $status = $data['status'] ?? '';

                            if ($status == 'tercatat') {

                                echo '<span class="status tercatat">
                                        Tercatat
                                      </span>';

                            } elseif ($status == 'ditindaklanjuti') {

                                echo '<span class="status ditindaklanjuti">
                                        Ditindaklanjuti
                                      </span>';

                            } elseif ($status == 'selesai') {

                                echo '<span class="status selesai">
                                        Selesai
                                      </span>';

                            } else {

                                echo '<span class="status tercatat">'
                                    . htmlspecialchars($status) .
                                    '</span>';

                            }

                            ?>

                        </td>


                        <?php if ($role == 'admin') { ?>

                            <td class="aksi">

                                <a
                                    href="edit_pelanggaran.php?id=<?= $data['id']; ?>"
                                    class="edit"
                                >
                                    Edit
                                </a>

                                |

                                <a
                                    href="hapus_pelanggaran.php?id=<?= $data['id']; ?>"
                                    class="hapus"
                                    onclick="return confirm('Yakin ingin menghapus data ini?')"
                                >
                                    Hapus
                                </a>

                            </td>

                        <?php } ?>

                    </tr>

                <?php

                    }

                } else {

                ?>

                    <tr>

                        <td
                            colspan="<?= ($role == 'admin') ? '8' : '7'; ?>"
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

    </div>

</div>


</body>

</html>