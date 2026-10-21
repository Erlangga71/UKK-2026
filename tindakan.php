
<?php

require_once "cek_role.php";

wajib_role(['admin', 'guru', 'wali_kelas']);

include "database.php";


// =========================
// AMBIL DATA TINDAKAN
// =========================

$query = mysqli_query($koneksi, "

    SELECT
        tindakan.id,
        tindakan.tanggal,
        tindakan.tindakan,
        tindakan.catatan,

        siswa.nama AS nama_siswa,

        jenis_pelanggaran.nama AS jenis_pelanggaran,

        users.nama AS nama_petugas

    FROM tindakan

    JOIN pelanggaran
        ON tindakan.pelanggaran_id = pelanggaran.id

    JOIN siswa
        ON pelanggaran.siswa_id = siswa.id

    JOIN jenis_pelanggaran
        ON pelanggaran.jenis_id = jenis_pelanggaran.id

    LEFT JOIN users
        ON tindakan.petugas_id = users.id

    ORDER BY tindakan.id DESC

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

    <title>Data Tindakan</title>


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


        /* =========================
           HEADER
        ========================= */

        .header {

            background: #687280;

            color: white;

            padding: 18px 30px;

            display: flex;

            justify-content: space-between;

            align-items: center;

        }

        .header b {

            font-size: 18px;

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

            background: #454e59;

        }


        /* =========================
           CONTAINER
        ========================= */

        .container {

            width: 95%;

            max-width: 1200px;

            margin: 30px auto;

        }


        /* =========================
           BOX
        ========================= */

        .box {

            background: white;

            border: 1px solid #ddd;

            border-radius: 6px;

            padding: 25px;

        }

        .box h2 {

            margin-top: 0;

            margin-bottom: 20px;

            font-size: 22px;

        }


        /* =========================
           TOMBOL TAMBAH
        ========================= */

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


        /* =========================
           TABLE
        ========================= */

        .table-wrapper {

            width: 100%;

            overflow-x: auto;

            margin-top: 20px;

        }

        table {

            width: 100%;

            border-collapse: collapse;

            min-width: 900px;

        }

        th {

            background: #687280;

            color: white;

            padding: 12px;

            text-align: left;

            font-size: 13px;

            border: 1px solid #59636f;

        }

        td {

            padding: 11px;

            border: 1px solid #ddd;

            font-size: 13px;

            vertical-align: top;

        }

        tr:nth-child(even) {

            background: #f8f9fa;

        }

        tr:hover {

            background: #eef1f4;

        }


        /* =========================
           AKSI
        ========================= */

        .aksi a {

            text-decoration: none;

            font-size: 13px;

            font-weight: bold;

        }

        .edit {

            color: #2563eb;

        }

        .edit:hover {

            text-decoration: underline;

        }

        .hapus {

            color: #dc2626;

        }

        .hapus:hover {

            text-decoration: underline;

        }


        /* =========================
           KOSONG
        ========================= */

        .kosong {

            text-align: center;

            padding: 25px;

            color: #777;

        }


        /* =========================
           RESPONSIVE
        ========================= */

        @media (max-width: 700px) {

            .header {

                padding: 15px;

            }

            .header b {

                font-size: 14px;

            }

            .container {

                width: 96%;

                margin: 20px auto;

            }

            .box {

                padding: 15px;

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
            Data Tindakan
        </h2>


        <!-- TOMBOL TAMBAH -->

        <?php if (
            $_SESSION['role'] == 'admin' ||
            $_SESSION['role'] == 'guru'
        ) { ?>

            <a
                href="tambah_tindakan.php"
                class="btn-tambah"
            >
                + Tambah Tindakan
            </a>

        <?php } ?>


        <!-- TABLE -->

        <div class="table-wrapper">

            <table>

                <tr>

                    <th>No</th>

                    <th>Siswa</th>

                    <th>Jenis Pelanggaran</th>

                    <th>Tanggal</th>

                    <th>Tindakan</th>

                    <th>Petugas</th>

                    <th>Catatan</th>


                    <?php if ($_SESSION['role'] == 'admin') { ?>

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

                            <b>
                                <?= htmlspecialchars(
                                    $data['nama_siswa'] ?? '-'
                                ); ?>
                            </b>

                        </td>


                        <td>

                            <?= htmlspecialchars(
                                $data['jenis_pelanggaran'] ?? '-'
                            ); ?>

                        </td>


                        <td>

                            <?= htmlspecialchars(
                                $data['tanggal'] ?? '-'
                            ); ?>

                        </td>


                        <td>

                            <?= htmlspecialchars(
                                $data['tindakan'] ?? '-'
                            ); ?>

                        </td>


                        <td>

                            <?= htmlspecialchars(
                                $data['nama_petugas'] ?? '-'
                            ); ?>

                        </td>


                        <td>

                            <?= htmlspecialchars(
                                $data['catatan'] ?? '-'
                            ); ?>

                        </td>


                        <!-- AKSI ADMIN -->

                        <?php if ($_SESSION['role'] == 'admin') { ?>

                            <td class="aksi">

                                <a
                                    class="edit"
                                    href="edit_tindakan.php?id=<?= $data['id']; ?>"
                                >
                                    Edit
                                </a>

                                |

                                <a
                                    class="hapus"
                                    href="hapus_tindakan.php?id=<?= $data['id']; ?>"
                                    onclick="return confirm('Yakin ingin menghapus tindakan ini?')"
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
                            colspan="<?= ($_SESSION['role'] == 'admin') ? '8' : '7'; ?>"
                            class="kosong"
                        >

                            Belum ada data tindakan.

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
```