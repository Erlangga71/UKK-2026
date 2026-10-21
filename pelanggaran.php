<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

include "database.php";

$role = $_SESSION['role'] ?? '';


/* =====================================================
   PROSES SIMPAN
===================================================== */

if (isset($_POST['simpan'])) {

    $siswa_id = intval($_POST['siswa_id'] ?? 0);
    $jenis_id = intval($_POST['jenis_id'] ?? 0);
    $guru_id = intval($_POST['guru_id'] ?? 0);

    $tanggal = trim($_POST['tanggal'] ?? '');
    $keterangan = trim($_POST['keterangan'] ?? '');
    $status = trim($_POST['status'] ?? 'tercatat');

    if ($siswa_id <= 0 || $jenis_id <= 0 || $tanggal == '') {

        $pesan = "Siswa, jenis pelanggaran, dan tanggal harus diisi.";

    } else {

        $keterangan = mysqli_real_escape_string(
            $koneksi,
            $keterangan
        );

        $tanggal = mysqli_real_escape_string(
            $koneksi,
            $tanggal
        );

        $status = mysqli_real_escape_string(
            $koneksi,
            $status
        );

        /*
         * Guru boleh kosong.
         * Kalau guru_id 0 maka disimpan sebagai NULL.
         */

        if ($guru_id > 0) {
            $guru_sql = $guru_id;
        } else {
            $guru_sql = "NULL";
        }

        $sql = "
            INSERT INTO pelanggaran
            (
                siswa_id,
                jenis_id,
                guru_id,
                tanggal,
                keterangan,
                status
            )
            VALUES
            (
                $siswa_id,
                $jenis_id,
                $guru_sql,
                '$tanggal',
                '$keterangan',
                '$status'
            )
        ";

        $simpan = mysqli_query(
            $koneksi,
            $sql
        );

        if ($simpan) {

            header("Location: pelanggaran.php");
            exit;

        } else {

            $pesan = "Gagal menyimpan data: " .
                     mysqli_error($koneksi);
        }
    }
}


/* =====================================================
   PROSES HAPUS
===================================================== */

if (isset($_GET['hapus'])) {

    $id = intval($_GET['hapus']);

    $hapus = mysqli_query(
        $koneksi,
        "DELETE FROM pelanggaran
         WHERE id = $id"
    );

    if ($hapus) {

        header("Location: pelanggaran.php");
        exit;

    } else {

        $pesan = "Gagal menghapus data: " .
                 mysqli_error($koneksi);
    }
}


/* =====================================================
   DATA SISWA
===================================================== */

$query_siswa = mysqli_query(
    $koneksi,
    "
    SELECT
        id,
        nis,
        nama
    FROM siswa
    WHERE aktif = 1
    ORDER BY nama ASC
    "
);


/* =====================================================
   DATA JENIS PELANGGARAN
===================================================== */

$query_jenis = mysqli_query(
    $koneksi,
    "
    SELECT
        j.id,
        j.nama,
        j.poin,
        k.nama AS nama_kategori
    FROM jenis_pelanggaran j
    LEFT JOIN kategori_pelanggaran k
        ON j.kategori_id = k.id
    ORDER BY k.nama ASC, j.nama ASC
    "
);


/* =====================================================
   DATA GURU
===================================================== */

$query_guru = mysqli_query(
    $koneksi,
    "
    SELECT
        id,
        nama
    FROM guru
    ORDER BY nama ASC
    "
);


/* =====================================================
   DATA PELANGGARAN
===================================================== */

$query_pelanggaran = mysqli_query(
    $koneksi,
    "
    SELECT

        p.id,

        s.nis,
        s.nama AS nama_siswa,

        j.nama AS nama_jenis,
        j.poin,

        k.nama AS nama_kategori,

        g.nama AS nama_guru,

        p.tanggal,
        p.keterangan,
        p.status

    FROM pelanggaran p

    LEFT JOIN siswa s
        ON p.siswa_id = s.id

    LEFT JOIN jenis_pelanggaran j
        ON p.jenis_id = j.id

    LEFT JOIN kategori_pelanggaran k
        ON j.kategori_id = k.id

    LEFT JOIN guru g
        ON p.guru_id = g.id

    ORDER BY p.id DESC
    "
);


/* =====================================================
   CEK QUERY
===================================================== */

if (!$query_pelanggaran) {

    die(
        "<h2>ERROR DATABASE</h2>" .
        "<p>" .
        mysqli_error($koneksi) .
        "</p>"
    );
}

?>

<!DOCTYPE html>

<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

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
           PESAN
        ========================= */

        .pesan {

            background: #fff3cd;

            border: 1px solid #ffe69c;

            color: #664d03;

            padding: 12px;

            border-radius: 5px;

            margin-bottom: 20px;
        }


        /* =========================
           FORM
        ========================= */

        .form-grid {

            display: grid;

            grid-template-columns: 1fr 1fr;

            gap: 15px;
        }


        .form-group {

            margin-bottom: 5px;
        }


        .form-group label {

            display: block;

            font-weight: bold;

            margin-bottom: 7px;

            font-size: 13px;
        }


        input,
        select,
        textarea {

            width: 100%;

            padding: 10px;

            border: 1px solid #ccc;

            border-radius: 5px;

            font-size: 13px;

            font-family: Arial;
        }


        textarea {

            resize: vertical;
        }


        .btn {

            display: inline-block;

            margin-top: 15px;

            padding: 10px 16px;

            border: none;

            border-radius: 5px;

            background: #687280;

            color: white;

            text-decoration: none;

            cursor: pointer;

            font-size: 13px;
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

            min-width: 950px;
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

            vertical-align: top;
        }


        table tr:hover {

            background: #f7f7f7;
        }


        /* =========================
           AKSI
        ========================= */

        .btn-edit {

            background: #687280;

            color: white;

            padding: 7px 10px;

            border-radius: 4px;

            text-decoration: none;

            font-size: 12px;

            display: inline-block;

            margin-right: 4px;
        }


        .btn-edit:hover {

            background: #56606d;
        }


        .btn-hapus {

            background: #b42323;

            color: white;

            padding: 7px 10px;

            border-radius: 4px;

            text-decoration: none;

            font-size: 12px;

            display: inline-block;
        }


        .btn-hapus:hover {

            background: #941d1d;
        }


        /* =========================
           STATUS
        ========================= */

        .status {

            display: inline-block;

            padding: 5px 8px;

            border-radius: 4px;

            font-size: 11px;

            font-weight: bold;
        }


        .tercatat {

            background: #fff3cd;

            color: #856404;
        }


        .ditindaklanjuti {

            background: #d1ecf1;

            color: #0c5460;
        }


        .selesai {

            background: #d4edda;

            color: #155724;
        }


        .kosong {

            text-align: center;

            padding: 25px;

            color: #777;
        }


        /* =========================
           RESPONSIVE
        ========================= */

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

        <a href="kelola_pelanggaran_kategori.php">
            ⚠ Kategori Pelanggaran
        </a>

    <?php } ?>


    <a href="pelanggaran.php"
       class="active">

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


    <a href="logout.php"
       class="logout">

        🚪 Logout

    </a>

</div>


<!-- =========================
     CONTENT
========================= -->

<div class="content">


    <div class="top-header">

        <h1>

            Data Pelanggaran

        </h1>


        <p>

            Kelola data pelanggaran siswa.

        </p>

    </div>


    <?php if (isset($pesan)) { ?>

        <div class="pesan">

            <?= htmlspecialchars($pesan); ?>

        </div>

    <?php } ?>


    <!-- =========================
         FORM TAMBAH
    ========================= -->

    <div class="box">

        <h2>

            Tambah Data Pelanggaran

        </h2>


        <form method="POST">


            <div class="form-grid">


                <!-- SISWA -->

                <div class="form-group">

                    <label>
                        Siswa
                    </label>

                    <select
                        name="siswa_id"
                        required
                    >

                        <option value="">
                            -- Pilih Siswa --
                        </option>


                        <?php

                        if ($query_siswa) {

                            while (
                                $siswa =
                                mysqli_fetch_assoc(
                                    $query_siswa
                                )
                            ) {

                        ?>

                            <option
                                value="<?= $siswa['id']; ?>"
                            >

                                <?= htmlspecialchars(
                                    $siswa['nis']
                                ); ?>

                                -
                                <?= htmlspecialchars(
                                    $siswa['nama']
                                ); ?>

                            </option>

                        <?php

                            }

                        }

                        ?>

                    </select>

                </div>


                <!-- JENIS -->

                <div class="form-group">

                    <label>
                        Jenis Pelanggaran
                    </label>

                    <select
                        name="jenis_id"
                        required
                    >

                        <option value="">
                            -- Pilih Jenis Pelanggaran --
                        </option>


                        <?php

                        if ($query_jenis) {

                            while (
                                $jenis =
                                mysqli_fetch_assoc(
                                    $query_jenis
                                )
                            ) {

                        ?>

                            <option
                                value="<?= $jenis['id']; ?>"
                            >

                                <?= htmlspecialchars(
                                    $jenis['nama_kategori'] ?? '-'
                                ); ?>

                                -
                                <?= htmlspecialchars(
                                    $jenis['nama']
                                ); ?>

                                (<?= $jenis['poin']; ?> poin)

                            </option>

                        <?php

                            }

                        }

                        ?>

                    </select>

                </div>


                <!-- GURU -->

                <div class="form-group">

                    <label>
                        Guru
                    </label>

                    <select
                        name="guru_id"
                    >

                        <option value="0">
                            -- Pilih Guru (Opsional) --
                        </option>


                        <?php

                        if ($query_guru) {

                            while (
                                $guru =
                                mysqli_fetch_assoc(
                                    $query_guru
                                )
                            ) {

                        ?>

                            <option
                                value="<?= $guru['id']; ?>"
                            >

                                <?= htmlspecialchars(
                                    $guru['nama']
                                ); ?>

                            </option>

                        <?php

                            }

                        }

                        ?>

                    </select>

                </div>


                <!-- TANGGAL -->

                <div class="form-group">

                    <label>
                        Tanggal
                    </label>

                    <input
                        type="date"
                        name="tanggal"
                        value="<?= date('Y-m-d'); ?>"
                        required
                    >

                </div>


                <!-- STATUS -->

                <div class="form-group">

                    <label>
                        Status
                    </label>

                    <select
                        name="status"
                    >

                        <option value="tercatat">
                            Tercatat
                        </option>

                        <option value="ditindaklanjuti">
                            Ditindaklanjuti
                        </option>

                        <option value="selesai">
                            Selesai
                        </option>

                    </select>

                </div>


                <!-- KETERANGAN -->

                <div class="form-group">

                    <label>
                        Keterangan
                    </label>

                    <textarea
                        name="keterangan"
                        rows="3"
                        placeholder="Keterangan pelanggaran..."
                    ></textarea>

                </div>


            </div>


            <button
                type="submit"
                name="simpan"
                class="btn"
            >

                + Simpan Pelanggaran

            </button>


        </form>

    </div>


    <!-- =========================
         DAFTAR
    ========================= -->

    <div class="box">

        <h2>

            Daftar Pelanggaran

        </h2>


        <div class="table-container">


            <table>


                <thead>

                    <tr>

                        <th>No</th>

                        <th>NIS</th>

                        <th>Nama Siswa</th>

                        <th>Kategori</th>

                        <th>Jenis Pelanggaran</th>

                        <th>Poin</th>

                        <th>Guru</th>

                        <th>Tanggal</th>

                        <th>Keterangan</th>

                        <th>Status</th>

                        <th>Aksi</th>

                    </tr>

                </thead>


                <tbody>


                <?php

                $no = 1;


                if (
                    mysqli_num_rows(
                        $query_pelanggaran
                    ) > 0
                ) {


                    while (
                        $row =
                        mysqli_fetch_assoc(
                            $query_pelanggaran
                        )
                    ) {

                ?>

                    <tr>

                        <td>

                            <?= $no++; ?>

                        </td>


                        <td>

                            <?= htmlspecialchars(
                                $row['nis'] ?? '-'
                            ); ?>

                        </td>


                        <td>

                            <b>

                                <?= htmlspecialchars(
                                    $row['nama_siswa'] ?? '-'
                                ); ?>

                            </b>

                        </td>


                        <td>

                            <?= htmlspecialchars(
                                $row['nama_kategori'] ?? '-'
                            ); ?>

                        </td>


                        <td>

                            <?= htmlspecialchars(
                                $row['nama_jenis'] ?? '-'
                            ); ?>

                        </td>


                        <td>

                            <b>

                                <?= htmlspecialchars(
                                    $row['poin'] ?? '0'
                                ); ?>

                            </b>

                        </td>


                        <td>

                            <?= htmlspecialchars(
                                $row['nama_guru'] ?? '-'
                            ); ?>

                        </td>


                        <td>

                            <?= htmlspecialchars(
                                $row['tanggal']
                            ); ?>

                        </td>


                        <td>

                            <?= htmlspecialchars(
                                $row['keterangan'] ?? '-'
                            ); ?>

                        </td>


                        <td>

                            <?php

                            $status =
                                $row['status'] ?? 'tercatat';

                            ?>

                            <span
                                class="status <?= htmlspecialchars(
                                    $status
                                ); ?>"
                            >

                                <?= htmlspecialchars(
                                    ucfirst(
                                        $status
                                    )
                                ); ?>

                            </span>

                        </td>


                        <td>


                            <a
                                href="edit_pelanggaran.php?id=<?= $row['id']; ?>"
                                class="btn-edit"
                            >

                                Edit

                            </a>


                            <a
                                href="pelanggaran.php?hapus=<?= $row['id']; ?>"
                                class="btn-hapus"
                                onclick="return confirm('Yakin ingin menghapus data pelanggaran ini?')"
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
                            colspan="11"
                            class="kosong"
                        >

                            Belum ada data pelanggaran.

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