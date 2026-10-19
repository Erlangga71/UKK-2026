<?php

session_start();

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

include "database.php";

$role = $_SESSION['role'] ?? '';

/* =========================
   SIMPAN KATEGORI
========================= */

if (isset($_POST['simpan'])) {

    $nama = trim($_POST['nama'] ?? '');

    if ($nama != '') {

        $nama = mysqli_real_escape_string(
            $koneksi,
            $nama
        );

        mysqli_query(
            $koneksi,
            "INSERT INTO kategori (nama)
             VALUES ('$nama')"
        );
    }

    header("Location: kelola_pelanggaran_kategori.php");
    exit;
}


/* =========================
   HAPUS KATEGORI
========================= */

if (isset($_GET['hapus'])) {

    $id = (int) $_GET['hapus'];

    mysqli_query(
        $koneksi,
        "DELETE FROM kategori
         WHERE id = $id"
    );

    header("Location: kelola_pelanggaran_kategori.php");
    exit;
}


/* =========================
   AMBIL DATA
========================= */

$query = mysqli_query(
    $koneksi,
    "SELECT id, nama
     FROM kategori
     ORDER BY id DESC"
);

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Kategori Pelanggaran</title>

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
           SIDEBAR
        ========================= */

        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 220px;
            height: 100vh;
            background: #687280;
            padding: 20px 12px;
            overflow-y: auto;
        }

        .sidebar-title {
            color: white;
            font-size: 19px;
            font-weight: bold;
            margin: 0 8px 25px;
            line-height: 1.4;
        }

        .sidebar a {
            display: block;
            padding: 11px 13px;
            margin-bottom: 5px;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 14px;
        }

        .sidebar a:hover {
            background: #56606d;
        }

        .sidebar a.active {
            background: #4f5965;
            font-weight: bold;
        }

        .sidebar .logout {
            margin-top: 20px;
            background: #b42323;
        }

        .sidebar .logout:hover {
            background: #941d1d;
        }


        /* =========================
           CONTENT
        ========================= */

        .content {
            margin-left: 220px;
            padding: 30px;
        }


        /* =========================
           HEADER
        ========================= */

        .top-header {
            background: white;
            border: 1px solid #ddd;
            border-radius: 7px;
            padding: 18px 22px;
            margin-bottom: 20px;
        }

        .top-header h1 {
            margin: 0;
            font-size: 24px;
        }

        .top-header p {
            margin: 7px 0 0;
            color: #777;
            font-size: 14px;
        }


        /* =========================
           BOX
        ========================= */

        .box {
            background: white;
            border: 1px solid #ddd;
            border-radius: 7px;
            padding: 22px;
            margin-bottom: 20px;
        }

        .box h2 {
            margin-top: 0;
            font-size: 18px;
        }


        /* =========================
           FORM
        ========================= */

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            font-weight: bold;
            margin-bottom: 7px;
            font-size: 14px;
        }

        input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
        }

        input:focus {
            outline: none;
            border-color: #687280;
        }

        .btn {
            display: inline-block;
            padding: 10px 16px;
            border: none;
            border-radius: 5px;
            background: #687280;
            color: white;
            text-decoration: none;
            cursor: pointer;
            font-size: 14px;
        }

        .btn:hover {
            background: #56606d;
        }


        /* =========================
           TABLE
        ========================= */

        .table-container {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th {
            background: #687280;
            color: white;
            padding: 11px;
            text-align: left;
            font-size: 13px;
        }

        table td {
            padding: 11px;
            border-bottom: 1px solid #ddd;
            font-size: 13px;
        }

        table tr:hover {
            background: #f7f7f7;
        }


        /* =========================
           HAPUS
        ========================= */

        .btn-hapus {
            background: #b42323;
            color: white;
            padding: 7px 11px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 12px;
        }

        .btn-hapus:hover {
            background: #941d1d;
        }


        /* =========================
           RESPONSIVE
        ========================= */

        @media (max-width: 700px) {

            .sidebar {
                position: relative;
                width: 100%;
                height: auto;
            }

            .content {
                margin-left: 0;
                padding: 15px;
            }

        }

    </style>

</head>


<body>


<!-- =========================
     SIDEBAR
========================= -->

<div class="sidebar">

    <div class="sidebar-title">
        ⚖ Pelanggaran<br>
        Siswa
    </div>


    <a href="dashboard.php">
        🏠 Dashboard
    </a>


    <?php if ($role == 'admin') { ?>

        <a href="kelola_siswa.php">
             Data Siswa
        </a>

        <a href="kelola_guru.php">
             Data Guru
        </a>

        <a href="kelola_kelas.php">
             Data Kelas
        </a>

        <a href="kelola_tahun_ajaran.php">
             Tahun Ajaran
        </a>

        <a href="kelola_wali_kelas.php">
             Wali Kelas
        </a>

        <a href="penempatan_siswa.php">
             Penempatan Siswa
        </a>

        <a href="kelola_pelanggaran_kategori.php"
           class="active">
            ⚠ Kategori Pelanggaran
        </a>

        <a href="pelanggaran.php">
             Data Pelanggaran
        </a>

        <a href="tindakan.php">
             Data Tindakan
        </a>

        <a href="laporan.php">
             Laporan
        </a>

        <a href="about_me.php">
            ℹ About Me
        </a>

    <?php } ?>


    <?php if ($role == 'guru') { ?>

        <a href="catat_pelanggaran.php">
             Catat Pelanggaran
        </a>

        <a href="tindakan.php">
             Tindakan
        </a>

        <a href="riwayat.php">
             Riwayat
        </a>

        <a href="rekap_poin.php">
             Rekap Poin
        </a>

    <?php } ?>


    <a href="logout.php" class="logout">
        🚪 Logout
    </a>

</div>


<!-- =========================
     CONTENT
========================= -->

<div class="content">


    <!-- HEADER -->

    <div class="top-header">

        <h1>
            Kategori Pelanggaran
        </h1>

        <p>
            Kelola kategori pelanggaran siswa
        </p>

    </div>


    <!-- =========================
         FORM
    ========================= -->

    <div class="box">

        <h2>
            Tambah Kategori Pelanggaran
        </h2>

        <form method="POST">

            <div class="form-group">

                <label>
                    Nama Kategori
                </label>

                <input
                    type="text"
                    name="nama"
                    placeholder="Contoh: Kedisiplinan"
                    required
                >

            </div>


            <button
                type="submit"
                name="simpan"
                class="btn"
            >
                Simpan
            </button>

        </form>

    </div>


    <!-- =========================
         DAFTAR KATEGORI
    ========================= -->

    <div class="box">

        <h2>
            Daftar Kategori Pelanggaran
        </h2>


        <div class="table-container">

            <table>

                <thead>

                    <tr>

                        <th>
                            No
                        </th>

                        <th>
                            Nama Kategori
                        </th>

                        <th>
                            Aksi
                        </th>

                    </tr>

                </thead>


                <tbody>

                <?php

                $no = 1;

                if (
                    $query &&
                    mysqli_num_rows($query) > 0
                ) {

                    while (
                        $row = mysqli_fetch_assoc($query)
                    ) {

                ?>

                    <tr>

                        <td>
                            <?= $no++; ?>
                        </td>

                        <td>

                            <b>
                                <?= htmlspecialchars(
                                    $row['nama'] ?? '-'
                                ); ?>
                            </b>

                        </td>

                        <td>

                            <a
                                href="kelola_pelanggaran_kategori.php?hapus=<?= $row['id']; ?>"
                                class="btn-hapus"
                                onclick="return confirm('Yakin ingin menghapus kategori ini?')"
                            >
                                Hapus
                            </a>

                        </td>

                    </tr>

                <?php

                    }

                } else {

                ?>

                    <tr>

                        <td
                            colspan="3"
                            style="text-align:center; padding:25px;"
                        >
                            Belum ada kategori pelanggaran.

                        </td>

                    </tr>

                <?php

                }

                ?>

                </tbody>

            </table>

        </div>

    </div>


</div>

</body>

</html>