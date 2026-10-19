<?php

session_start();

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

include "database.php";

if (!isset($koneksi)) {
    die("Koneksi database tidak ditemukan.");
}

$role = $_SESSION['role'] ?? '';

/* =====================================================
   TAMBAH WALI KELAS
===================================================== */

if (isset($_POST['simpan'])) {

    $guru_id = intval($_POST['guru_id']);
    $kelas_id = intval($_POST['kelas_id']);
    $tahun_ajaran_id = intval($_POST['tahun_ajaran_id']);

    if ($guru_id > 0 && $kelas_id > 0 && $tahun_ajaran_id > 0) {

        // Cek apakah kombinasi sudah ada
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

            $simpan = mysqli_query(
                $koneksi,
                "INSERT INTO wali_kelas
                (guru_id, kelas_id, tahun_ajaran_id)
                VALUES
                ($guru_id, $kelas_id, $tahun_ajaran_id)"
            );

            if ($simpan) {
                header("Location: wali_kelas.php");
                exit;
            } else {
                $pesan = "Data gagal disimpan.";
            }
        }

    } else {

        $pesan = "Semua pilihan harus diisi.";
    }
}


/* =====================================================
   HAPUS WALI KELAS
===================================================== */

if (isset($_GET['hapus'])) {

    $id = intval($_GET['hapus']);

    if ($id > 0) {

        mysqli_query(
            $koneksi,
            "DELETE FROM wali_kelas WHERE id = $id"
        );
    }

    header("Location: wali_kelas.php");
    exit;
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
    "SELECT id, tahun, semester
     FROM tahun_ajaran
     WHERE status_aktif = 1
     ORDER BY tahun DESC"
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
            margin-bottom: 25px;
        }

        .top-header h1 {
            margin: 0;
            font-size: 25px;
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
            margin-bottom: 20px;
            font-size: 20px;
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

        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background: white;
            font-size: 14px;
        }

        .btn-simpan {
            display: inline-block;
            border: none;
            background: #687280;
            color: white;
            padding: 10px 18px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }

        .btn-simpan:hover {
            background: #56606d;
        }

        /* PESAN */

        .pesan {
            background: #fff3cd;
            border: 1px solid #ffe69c;
            color: #664d03;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
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
            padding: 12px;
            text-align: left;
            font-size: 13px;
        }

        table td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            font-size: 13px;
        }

        table tr:hover {
            background: #f7f7f7;
        }

        /* HAPUS */

        .btn-hapus {
            display: inline-block;
            background: #b42323;
            color: white;
            text-decoration: none;
            padding: 7px 11px;
            border-radius: 4px;
            font-size: 12px;
        }

        .btn-hapus:hover {
            background: #941d1d;
        }

        /* KEMBALI */

        .btn-kembali {
            display: inline-block;
            text-decoration: none;
            color: white;
            background: #687280;
            padding: 9px 14px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 13px;
        }

        .btn-kembali:hover {
            background: #56606d;
        }

        .kosong {
            text-align: center;
            color: #777;
            padding: 25px;
        }

        /* RESPONSIVE */

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


    <?php if ($role === 'admin') { ?>

        <a href="kelola_siswa.php">
            👤 Data Siswa
        </a>

        <a href="kelola_guru.php">
            👥 Data Guru
        </a>

        <a href="kelola_kelas.php">
            🏫 Data Kelas
        </a>

        <a href="kelola_tahun_ajaran.php">
            📅 Data Tahun Ajaran
        </a>

        <a href="wali_kelas.php" class="active">
            👨‍🏫 Data Wali Kelas
        </a>

        <a href="penempatan_siswa.php">
            📋 Penempatan Siswa
        </a>

        <a href="kelola_pelanggaran_kategori.php">
            ⚠ Kategori Pelanggaran
        </a>

        <a href="pelanggaran.php">
            ⚖ Data Pelanggaran
        </a>

        <a href="tindakan.php">
            📝 Data Tindakan
        </a>

        <a href="laporan.php">
            📊 Laporan
        </a>

        <a href="about_me.php">
            ℹ About Me
        </a>

    <?php } ?>


    <?php if ($role === 'guru') { ?>

        <a href="catat_pelanggaran.php">
            📝 Catat Pelanggaran
        </a>

        <a href="tindakan.php">
            ⚖ Tindakan
        </a>

        <a href="riwayat.php">
            📋 Riwayat
        </a>

        <a href="rekap_poin.php">
            📊 Rekap Poin
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


    <!-- HEADER -->

    <div class="top-header">

        <h1>
            Sistem Informasi Pelanggaran Siswa
        </h1>

        <p>
            Data Wali Kelas
        </p>

    </div>


    <!-- KEMBALI -->

    <a href="dashboard.php" class="btn-kembali">
        ← Kembali ke Dashboard
    </a>


    <?php if (isset($pesan)) { ?>

        <div class="pesan">
            <?= htmlspecialchars($pesan); ?>
        </div>

    <?php } ?>


    <!-- =================================================
         FORM TAMBAH
    ================================================== -->

    <div class="box">

        <h2>
            Tambah Wali Kelas
        </h2>


        <form method="post">


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

                    if ($query_guru) {

                        while ($guru = mysqli_fetch_assoc($query_guru)) {

                    ?>

                        <option value="<?= $guru['id']; ?>">

                            <?= htmlspecialchars($guru['nama']); ?>

                        </option>

                    <?php

                        }

                    }

                    ?>

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

                    if ($query_kelas) {

                        while ($kelas = mysqli_fetch_assoc($query_kelas)) {

                    ?>

                        <option value="<?= $kelas['id']; ?>">

                            <?= htmlspecialchars($kelas['nama_kelas']); ?>

                            -

                            <?= htmlspecialchars($kelas['jurusan'] ?? ''); ?>

                            -

                            <?= htmlspecialchars($kelas['tingkat'] ?? ''); ?>

                        </option>

                    <?php

                        }

                    }

                    ?>

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

                    if ($query_tahun) {

                        while ($tahun = mysqli_fetch_assoc($query_tahun)) {

                    ?>

                        <option value="<?= $tahun['id']; ?>">

                            <?= htmlspecialchars($tahun['tahun']); ?>

                            -

                            <?= htmlspecialchars($tahun['semester'] ?? ''); ?>

                        </option>

                    <?php

                        }

                    }

                    ?>

                </select>

            </div>


            <button
                type="submit"
                name="simpan"
                class="btn-simpan"
            >
                Simpan
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


        <div class="table-container">

            <table>

                <thead>

                    <tr>

                        <th>No</th>

                        <th>Guru</th>

                        <th>Kelas</th>

                        <th>Jurusan</th>

                        <th>Tingkat</th>

                        <th>Tahun</th>

                        <th>Semester</th>

                        <th>Aksi</th>

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
                                $row['tahun'] ?? '-'
                            ); ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $row['semester'] ?? '-'
                            ); ?>
                        </td>

                        <td>

                            <a
                                href="wali_kelas.php?hapus=<?= $row['id']; ?>"
                                class="btn-hapus"
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
                            class="kosong"
                        >
                            Belum ada data wali kelas.
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