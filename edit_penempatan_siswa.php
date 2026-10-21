<?php

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
    $kelas_id = intval($_POST['kelas_id'] ?? 0);
    $tahun_ajaran_id = intval($_POST['tahun_ajaran_id'] ?? 0);

    if ($siswa_id <= 0 || $kelas_id <= 0 || $tahun_ajaran_id <= 0) {

        $pesan = "Semua data harus diisi.";

    } else {

        $cek = mysqli_query(
            $koneksi,
            "SELECT id
             FROM penempatan_siswa
             WHERE siswa_id = $siswa_id
             AND tahun_ajaran_id = $tahun_ajaran_id"
        );

        if (mysqli_num_rows($cek) > 0) {

            $pesan = "Siswa tersebut sudah memiliki penempatan pada tahun ajaran ini.";

        } else {

            $simpan = mysqli_query(
                $koneksi,
                "INSERT INTO penempatan_siswa
                (siswa_id, kelas_id, tahun_ajaran_id)
                VALUES
                ($siswa_id, $kelas_id, $tahun_ajaran_id)"
            );

            if ($simpan) {

                header("Location: penempatan_siswa.php");
                exit;

            } else {

                $pesan = "Data gagal disimpan: " . mysqli_error($koneksi);
            }
        }
    }
}


/* =====================================================
   PROSES HAPUS
===================================================== */

if (isset($_GET['hapus'])) {

    if ($role != 'admin') {
        die("Akses ditolak.");
    }

    $id = intval($_GET['hapus']);

    mysqli_query(
        $koneksi,
        "DELETE FROM penempatan_siswa
         WHERE id = $id"
    );

    header("Location: penempatan_siswa.php");
    exit;
}


/* =====================================================
   DATA SISWA
===================================================== */

$query_siswa = mysqli_query(
    $koneksi,
    "SELECT
        id,
        nis,
        nama
     FROM siswa
     WHERE aktif = 1
     ORDER BY nama ASC"
);


/* =====================================================
   DATA KELAS
===================================================== */

$query_kelas = mysqli_query(
    $koneksi,
    "SELECT
        id,
        nama_kelas
     FROM kelas
     ORDER BY nama_kelas ASC"
);


/* =====================================================
   DATA TAHUN AJARAN
===================================================== */

$query_tahun = mysqli_query(
    $koneksi,
    "SELECT
        id,
        nama,
        tanggal_mulai,
        tanggal_selesai
     FROM tahun_ajaran
     WHERE aktif = 1
     ORDER BY id DESC"
);


/* =====================================================
   DATA PENEMPATAN
===================================================== */

$query_penempatan = mysqli_query(
    $koneksi,
    "SELECT
        p.id,
        s.nis,
        s.nama AS nama_siswa,
        k.nama_kelas,
        ta.nama AS nama_tahun,
        ta.tanggal_mulai,
        ta.tanggal_selesai

     FROM penempatan_siswa p

     LEFT JOIN siswa s
        ON p.siswa_id = s.id

     LEFT JOIN kelas k
        ON p.kelas_id = k.id

     LEFT JOIN tahun_ajaran ta
        ON p.tahun_ajaran_id = ta.id

     ORDER BY p.id DESC"
);

?>

<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Penempatan Siswa</title>

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

.header {
    background: white;
    border: 1px solid #ddd;
    border-radius: 7px;
    padding: 20px;
    margin-bottom: 20px;
}

.header h1 {
    margin: 0;
    font-size: 24px;
}

.header p {
    margin: 7px 0 0;
    color: #777;
    font-size: 14px;
}


/* BOX */

.box {
    background: white;
    border: 1px solid #ddd;
    border-radius: 7px;
    padding: 20px;
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
    font-size: 13px;
    font-weight: bold;
    margin-bottom: 6px;
}

.form-group select {
    width: 100%;
    padding: 9px;
    border: 1px solid #ccc;
    border-radius: 4px;
    background: white;
}

.btn {
    background: #687280;
    color: white;
    border: none;
    padding: 9px 15px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 13px;
}

.btn:hover {
    background: #56606d;
}


/* PESAN */

.pesan {
    background: #fff3cd;
    border: 1px solid #ffe69c;
    color: #664d03;
    padding: 10px;
    border-radius: 4px;
    margin-bottom: 15px;
    font-size: 13px;
}


/* TABLE */

.table-container {
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
    min-width: 850px;
}

table th {
    background: #687280;
    color: white;
    padding: 10px;
    text-align: left;
    font-size: 13px;
}

table td {
    padding: 10px;
    border-bottom: 1px solid #ddd;
    font-size: 13px;
}

table tr:hover {
    background: #f7f7f7;
}


/* BUTTON AKSI */

.btn-edit {
    display: inline-block;
    color: white;
    background: #687280;
    padding: 6px 10px;
    text-decoration: none;
    border-radius: 4px;
    font-size: 12px;
    margin-right: 4px;
}

.btn-edit:hover {
    background: #56606d;
}

.btn-hapus {
    display: inline-block;
    color: white;
    background: #b42323;
    padding: 6px 10px;
    text-decoration: none;
    border-radius: 4px;
    font-size: 12px;
}

.btn-hapus:hover {
    background: #941d1d;
}


/* KOSONG */

.kosong {
    text-align: center;
    color: #777;
    padding: 20px;
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


<!-- SIDEBAR -->

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

        <a href="penempatan_siswa.php"
           class="active">
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


<!-- CONTENT -->

<div class="content">


    <div class="header">

        <h1>
            Penempatan Siswa
        </h1>

        <p>
            Kelola penempatan siswa ke dalam kelas dan tahun ajaran.
        </p>

    </div>


    <?php if (isset($pesan)) { ?>

        <div class="pesan">
            <?= htmlspecialchars($pesan); ?>
        </div>

    <?php } ?>


    <!-- FORM -->

    <div class="box">

        <h2>
            Tambah Penempatan Siswa
        </h2>

        <form method="POST">


            <div class="form-group">

                <label>
                    Siswa
                </label>

                <select name="siswa_id" required>

                    <option value="">
                        -- Pilih Siswa --
                    </option>

                    <?php

                    if ($query_siswa) {

                        while (
                            $siswa = mysqli_fetch_assoc($query_siswa)
                        ) {

                    ?>

                        <option value="<?= $siswa['id']; ?>">

                            <?= htmlspecialchars($siswa['nis']); ?>

                            -

                            <?= htmlspecialchars($siswa['nama']); ?>

                        </option>

                    <?php

                        }

                    }

                    ?>

                </select>

            </div>


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

                        while (
                            $kelas = mysqli_fetch_assoc($query_kelas)
                        ) {

                    ?>

                        <option value="<?= $kelas['id']; ?>">

                            <?= htmlspecialchars(
                                $kelas['nama_kelas']
                            ); ?>

                        </option>

                    <?php

                        }

                    }

                    ?>

                </select>

            </div>


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

                        while (
                            $tahun = mysqli_fetch_assoc($query_tahun)
                        ) {

                    ?>

                        <option value="<?= $tahun['id']; ?>">

                            <?= htmlspecialchars(
                                $tahun['nama']
                            ); ?>

                            (
                            <?= htmlspecialchars(
                                $tahun['tanggal_mulai']
                            ); ?>

                            s/d

                            <?= htmlspecialchars(
                                $tahun['tanggal_selesai']
                            ); ?>
                            )

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
                class="btn"
            >
                + Tambah Penempatan
            </button>

        </form>

    </div>


    <!-- DAFTAR -->

    <div class="box">

        <h2>
            Daftar Penempatan Siswa
        </h2>

        <div class="table-container">

            <table>

                <thead>

                    <tr>

                        <th>No</th>
                        <th>NIS</th>
                        <th>Nama Siswa</th>
                        <th>Kelas</th>
                        <th>Tahun Ajaran</th>
                        <th>Tanggal Mulai</th>
                        <th>Tanggal Selesai</th>
                        <th>Aksi</th>

                    </tr>

                </thead>


                <tbody>

                <?php

                $no = 1;

                if (
                    $query_penempatan &&
                    mysqli_num_rows($query_penempatan) > 0
                ) {

                    while (
                        $row = mysqli_fetch_assoc($query_penempatan)
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
                                $row['nama_kelas'] ?? '-'
                            ); ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $row['nama_tahun'] ?? '-'
                            ); ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $row['tanggal_mulai'] ?? '-'
                            ); ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $row['tanggal_selesai'] ?? '-'
                            ); ?>
                        </td>

                        <td>

                            <a
                                href="edit_penempatan_siswa.php?id=<?= $row['id']; ?>"
                                class="btn-edit"
                            >
                                Edit
                            </a>

                            <a
                                href="penempatan_siswa.php?hapus=<?= $row['id']; ?>"
                                class="btn-hapus"
                                onclick="return confirm('Yakin ingin menghapus data penempatan ini?');"
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
                            Belum ada data penempatan siswa.
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