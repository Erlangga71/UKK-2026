<?php
session_start();

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

include "database.php";

$role = $_SESSION['role'] ?? '';

/* =========================
   SIMPAN DATA
========================= */

if (isset($_POST['simpan'])) {

    $tahun = mysqli_real_escape_string(
        $koneksi,
        $_POST['tahun']
    );

    $semester = mysqli_real_escape_string(
        $koneksi,
        $_POST['semester']
    );

    if ($tahun != '' && $semester != '') {

        mysqli_query(
            $koneksi,
            "INSERT INTO tahun_ajaran
            (tahun, semester, status_aktif)
            VALUES
            ('$tahun', '$semester', 1)"
        );
    }

    header("Location: kelola_tahun_ajaran.php");
    exit;
}


/* =========================
   HAPUS DATA
========================= */

if (isset($_GET['hapus'])) {

    $id = (int) $_GET['hapus'];

    mysqli_query(
        $koneksi,
        "DELETE FROM tahun_ajaran
         WHERE id = $id"
    );

    header("Location: kelola_tahun_ajaran.php");
    exit;
}


/* =========================
   AMBIL DATA
========================= */

$query = mysqli_query(
    $koneksi,
    "SELECT
        id,
        tahun,
        semester,
        status_aktif
     FROM tahun_ajaran
     ORDER BY id DESC"
);

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Data Tahun Ajaran</title>

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

        /* SIDEBAR */

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

        /* CONTENT */

        .content {
            margin-left: 220px;
            padding: 30px;
        }

        /* HEADER */

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

        /* BOX */

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

        /* FORM */

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            font-weight: bold;
            margin-bottom: 7px;
            font-size: 14px;
        }

        input,
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
        }

        input:focus,
        select:focus {
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

        /* TABLE */

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

        /* TOMBOL HAPUS */

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

        /* STATUS */

        .aktif {
            color: #198754;
            font-weight: bold;
        }

        .nonaktif {
            color: #b42323;
            font-weight: bold;
        }

        /* RESPONSIVE */

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

        <a href="kelola_tahun_ajaran.php" class="active">
             Tahun Ajaran
        </a>

        <a href="kelola_wali_kelas.php">
             Wali Kelas
        </a>

        <a href="penempatan_siswa.php">
             Penempatan Siswa
        </a>

        <a href="kelola_pelanggaran_kategori.php">
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
            Data Tahun Ajaran
        </h1>

        <p>
            Kelola data tahun ajaran sekolah
        </p>

    </div>


    <!-- FORM -->

    <div class="box">

        <h2>
            Tambah Tahun Ajaran
        </h2>

        <form method="POST">


            <div class="form-group">

                <label>
                    Tahun Ajaran
                </label>

                <input
                    type="text"
                    name="tahun"
                    placeholder="Contoh: 2026/2027"
                    required
                >

            </div>


            <div class="form-group">

                <label>
                    Semester
                </label>

                <select name="semester" required>

                    <option value="">
                        -- Pilih Semester --
                    </option>

                    <option value="Ganjil">
                        Ganjil
                    </option>

                    <option value="Genap">
                        Genap
                    </option>

                </select>

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


    <!-- DAFTAR -->

    <div class="box">

        <h2>
            Daftar Tahun Ajaran
        </h2>

        <div class="table-container">

            <table>

                <thead>

                    <tr>

                        <th>No</th>

                        <th>Tahun Ajaran</th>

                        <th>Semester</th>

                        <th>Status</th>

                        <th>Aksi</th>

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
                                    $row['tahun'] ?? '-'
                                ); ?>
                            </b>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $row['semester'] ?? '-'
                            ); ?>
                        </td>

                        <td>

                            <?php if ($row['status_aktif'] == 1) { ?>

                                <span class="aktif">
                                    Aktif
                                </span>

                            <?php } else { ?>

                                <span class="nonaktif">
                                    Tidak Aktif
                                </span>

                            <?php } ?>

                        </td>

                        <td>

                            <a
                                href="kelola_tahun_ajaran.php?hapus=<?= $row['id']; ?>"
                                class="btn-hapus"
                                onclick="return confirm('Yakin ingin menghapus data ini?')"
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
                            colspan="5"
                            style="text-align:center; padding:25px;"
                        >
                            Belum ada data tahun ajaran.

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