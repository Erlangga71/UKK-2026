<?php

session_start();

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

include "database.php";

$role = $_SESSION['role'] ?? '';

/* =====================================================
   HAPUS WALI KELAS
===================================================== */

if (isset($_GET['hapus'])) {

    $id = intval($_GET['hapus']);

    mysqli_query(
        $koneksi,
        "DELETE FROM wali_kelas WHERE id = $id"
    );

    header("Location: kelola_wali_kelas.php");
    exit;
}


/* =====================================================
   SIMPAN WALI KELAS
===================================================== */

if (isset($_POST['simpan'])) {

    $guru_id = intval($_POST['guru_id']);
    $kelas_id = intval($_POST['kelas_id']);
    $tahun_ajaran_id = intval($_POST['tahun_ajaran_id']);

    if ($guru_id > 0 && $kelas_id > 0 && $tahun_ajaran_id > 0) {

        $cek = mysqli_query(
            $koneksi,
            "SELECT id
             FROM wali_kelas
             WHERE guru_id = $guru_id
             AND kelas_id = $kelas_id
             AND tahun_ajaran_id = $tahun_ajaran_id"
        );

        if (mysqli_num_rows($cek) > 0) {

            $pesan = "Data wali kelas tersebut sudah ada.";

        } else {

            mysqli_query(
                $koneksi,
                "INSERT INTO wali_kelas
                (guru_id, kelas_id, tahun_ajaran_id)
                VALUES
                ($guru_id, $kelas_id, $tahun_ajaran_id)"
            );

            header("Location: kelola_wali_kelas.php");
            exit;
        }

    } else {

        $pesan = "Semua data harus dipilih.";
    }
}


/* =====================================================
   DATA GURU
===================================================== */

$query_guru = mysqli_query(
    $koneksi,
    "SELECT id, nama
     FROM guru
     WHERE status_aktif = 1
     ORDER BY nama ASC"
);


/* =====================================================
   DATA KELAS
===================================================== */

$query_kelas = mysqli_query(
    $koneksi,
    "SELECT id, nama_kelas, jurusan, tingkat
     FROM kelas
     WHERE status_aktif = 1
     ORDER BY tingkat ASC, nama_kelas ASC"
);


/* =====================================================
   DATA TAHUN AJARAN
===================================================== */

$query_tahun = mysqli_query(
    $koneksi,
    "SELECT id, tahun, nama, semester
     FROM tahun_ajaran
     WHERE status_aktif = 1
     ORDER BY id DESC"
);


/* =====================================================
   DATA WALI KELAS
===================================================== */

$query_wali = mysqli_query(
    $koneksi,
    "SELECT
        w.id,
        g.nama AS nama_guru,
        k.nama_kelas,
        k.jurusan,
        k.tingkat,
        ta.tahun,
        ta.nama AS nama_tahun,
        ta.semester

     FROM wali_kelas w

     INNER JOIN guru g
        ON w.guru_id = g.id

     INNER JOIN kelas k
        ON w.kelas_id = k.id

     INNER JOIN tahun_ajaran ta
        ON w.tahun_ajaran_id = ta.id

     ORDER BY w.id DESC"
);

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Data Wali Kelas</title>

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

        /* =================================================
           SIDEBAR
        ================================================= */

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


        /* =================================================
           CONTENT
        ================================================= */

        .content {
            margin-left: 220px;
            padding: 30px;
        }


        /* =================================================
           JUDUL
        ================================================= */

        .page-title {
            margin-bottom: 18px;
        }

        .page-title h1 {
            margin: 0;
            font-size: 25px;
        }

        .page-title p {
            margin: 7px 0 0;
            color: #777;
            font-size: 14px;
        }


        /* =================================================
           BOX
        ================================================= */

        .box {
            background: white;
            border: 1px solid #ddd;
            border-radius: 7px;
            padding: 18px;
            margin-bottom: 20px;
        }

        .box h2 {
            margin: 0 0 18px;
            font-size: 18px;
        }


        /* =================================================
           FORM
        ================================================= */

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .form-group {
            margin-bottom: 5px;
        }

        .form-group.full {
            grid-column: 1 / 3;
        }

        label {
            display: block;
            margin-bottom: 7px;
            font-size: 13px;
            font-weight: bold;
        }

        select {
            width: 100%;
            padding: 9px 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            background: white;
            font-size: 13px;
        }

        select:focus {
            outline: none;
            border-color: #687280;
        }

        .btn-simpan {
            margin-top: 10px;
            background: #1783a3;
            color: white;
            border: none;
            padding: 10px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 13px;
            font-weight: bold;
        }

        .btn-simpan:hover {
            background: #126d88;
        }


        /* =================================================
           PESAN
        ================================================= */

        .pesan {
            background: #ffe5e5;
            border: 1px solid #ffb3b3;
            color: #a00000;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
            font-size: 13px;
        }


        /* =================================================
           TABEL
        ================================================= */

        .table-info {
            font-size: 13px;
            color: #777;
            margin-bottom: 10px;
        }

        .table-container {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 750px;
        }

        th {
            background: #687280;
            color: white;
            padding: 11px;
            text-align: left;
            font-size: 13px;
            border: 1px solid #687280;
        }

        td {
            padding: 11px;
            border: 1px solid #ddd;
            font-size: 13px;
            background: white;
        }

        tr:hover td {
            background: #f7f7f7;
        }

        .hapus {
            color: #c00000;
            text-decoration: none;
            font-weight: bold;
        }

        .hapus:hover {
            text-decoration: underline;
        }


        /* =================================================
           RESPONSIVE
        ================================================= */

        @media (max-width: 800px) {

            .sidebar {
                position: relative;
                width: 100%;
                height: auto;
            }

            .content {
                margin-left: 0;
                padding: 15px;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .form-group.full {
                grid-column: auto;
            }

        }

    </style>

</head>


<body>


<!-- =====================================================
     SIDEBAR
===================================================== -->

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

        <a href="kelola_wali_kelas.php" class="active">
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


<!-- =====================================================
     CONTENT
===================================================== -->

<div class="content">


    <!-- JUDUL -->

    <div class="page-title">

        <h1>
            Data Wali Kelas
        </h1>

        <p>
            Kelola data wali kelas dalam sistem pelanggaran siswa.
        </p>

    </div>


    <!-- =================================================
         FORM TAMBAH
    ================================================== -->

    <div class="box">

        <h2>
            Tambah Data Wali Kelas
        </h2>


        <?php if (isset($pesan)) { ?>

            <div class="pesan">
                <?= htmlspecialchars($pesan); ?>
            </div>

        <?php } ?>


        <form method="POST">

            <div class="form-grid">


                <!-- GURU -->

                <div class="form-group">

                    <label>
                        Guru
                    </label>

                    <select name="guru_id" required>

                        <option value="">
                            -- Pilih Guru --
                        </option>

                        <?php

                        while ($guru = mysqli_fetch_assoc($query_guru)) {

                        ?>

                            <option value="<?= $guru['id']; ?>">

                                <?= htmlspecialchars(
                                    $guru['nama']
                                ); ?>

                            </option>

                        <?php } ?>

                    </select>

                </div>


                <!-- KELAS -->

                <div class="form-group">

                    <label>
                        Kelas
                    </label>

                    <select name="kelas_id" required>

                        <option value="">
                            -- Pilih Kelas --
                        </option>

                        <?php

                        while ($kelas = mysqli_fetch_assoc($query_kelas)) {

                        ?>

                            <option value="<?= $kelas['id']; ?>">

                                <?= htmlspecialchars(
                                    $kelas['nama_kelas']
                                ); ?>

                                <?php if (!empty($kelas['jurusan'])) { ?>

                                    - <?= htmlspecialchars(
                                        $kelas['jurusan']
                                    ); ?>

                                <?php } ?>

                                <?php if (!empty($kelas['tingkat'])) { ?>

                                    - <?= htmlspecialchars(
                                        $kelas['tingkat']
                                    ); ?>

                                <?php } ?>

                            </option>

                        <?php } ?>

                    </select>

                </div>


                <!-- TAHUN AJARAN -->

                <div class="form-group">

                    <label>
                        Tahun Ajaran
                    </label>

                    <select name="tahun_ajaran_id" required>

                        <option value="">
                            -- Pilih Tahun Ajaran --
                        </option>

                        <?php

                        while ($tahun = mysqli_fetch_assoc($query_tahun)) {

                        ?>

                            <option value="<?= $tahun['id']; ?>">

                                <?= htmlspecialchars(
                                    !empty($tahun['tahun'])
                                    ? $tahun['tahun']
                                    : $tahun['nama']
                                ); ?>

                                <?php if (!empty($tahun['semester'])) { ?>

                                    - <?= htmlspecialchars(
                                        $tahun['semester']
                                    ); ?>

                                <?php } ?>

                            </option>

                        <?php } ?>

                    </select>

                </div>


            </div>


            <button
                type="submit"
                name="simpan"
                class="btn-simpan"
            >
                + Tambah Wali Kelas
            </button>


        </form>

    </div>


    <!-- =================================================
         DAFTAR WALI KELAS
    ================================================== -->

    <div class="box">

        <h2>
            Daftar Wali Kelas
        </h2>

        <div class="table-info">
            Data wali kelas yang sudah terdaftar.
        </div>


        <div class="table-container">

            <table>

                <thead>

                    <tr>

                        <th style="width:50px;">
                            No
                        </th>

                        <th>
                            Guru
                        </th>

                        <th>
                            Kelas
                        </th>

                        <th>
                            Jurusan
                        </th>

                        <th>
                            Tingkat
                        </th>

                        <th>
                            Tahun
                        </th>

                        <th>
                            Semester
                        </th>

                        <th style="width:80px;">
                            Aksi
                        </th>

                    </tr>

                </thead>


                <tbody>

                    <?php

                    $no = 1;

                    if (
                        $query_wali &&
                        mysqli_num_rows($query_wali) > 0
                    ) {

                        while ($row = mysqli_fetch_assoc($query_wali)) {

                    ?>

                            <tr>

                                <td>
                                    <?= $no++; ?>
                                </td>

                                <td>
                                    <b>
                                        <?= htmlspecialchars(
                                            $row['nama_guru'] ?? '-'
                                        ); ?>
                                    </b>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        $row['nama_kelas'] ?? '-'
                                    ); ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        $row['jurusan'] ?? '-'
                                    ); ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        $row['tingkat'] ?? '-'
                                    ); ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        $row['tahun']
                                        ?? $row['nama_tahun']
                                        ?? '-'
                                    ); ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        $row['semester'] ?? '-'
                                    ); ?>
                                </td>

                                <td>

                                    <a
                                        href="kelola_wali_kelas.php?hapus=<?= $row['id']; ?>"
                                        class="hapus"
                                        onclick="return confirm('Yakin ingin menghapus data wali kelas ini?')"
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
                                colspan="8"
                                style="text-align:center; padding:25px;"
                            >
                                Belum ada data wali kelas.
                            </td>

                        </tr>

                    <?php } ?>

                </tbody>

            </table>

        </div>

    </div>


</div>


</body>

</html>