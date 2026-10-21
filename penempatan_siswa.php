<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

require_once "database.php";


/* =========================
   CEK LOGIN
========================= */

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}


/* =========================
   CEK ROLE
========================= */

$role = $_SESSION['role'] ?? '';

if ($role != 'admin') {
    die("Akses ditolak.");
}


/* =========================
   TAMBAH PENEMPATAN
========================= */

if (isset($_POST['simpan'])) {

    $siswa_id = (int)($_POST['siswa_id'] ?? 0);
    $kelas_id = (int)($_POST['kelas_id'] ?? 0);
    $tahun_ajaran_id = (int)($_POST['tahun_ajaran_id'] ?? 0);

    if (
        $siswa_id <= 0 ||
        $kelas_id <= 0 ||
        $tahun_ajaran_id <= 0
    ) {

        $error = "Semua data wajib dipilih.";

    } else {

        /* CEK DUPLIKAT */

        $cek = mysqli_query(
            $koneksi,
            "SELECT id
             FROM penempatan_siswa
             WHERE siswa_id = $siswa_id
             AND kelas_id = $kelas_id
             AND tahun_ajaran_id = $tahun_ajaran_id
             LIMIT 1"
        );

        if (mysqli_num_rows($cek) > 0) {

            $error = "Penempatan siswa tersebut sudah ada.";

        } else {

            /* SIMPAN */

            $query = mysqli_query(
                $koneksi,
                "INSERT INTO penempatan_siswa
                (
                    siswa_id,
                    kelas_id,
                    tahun_ajaran_id
                )
                VALUES
                (
                    $siswa_id,
                    $kelas_id,
                    $tahun_ajaran_id
                )"
            );

            if ($query) {

                header("Location: penempatan_siswa.php");
                exit;

            } else {

                $error =
                    "Gagal menyimpan penempatan: " .
                    mysqli_error($koneksi);
            }
        }
    }
}


/* =========================
   HAPUS PENEMPATAN
========================= */

if (isset($_GET['hapus'])) {

    $id = (int)$_GET['hapus'];

    if ($id > 0) {

        mysqli_query(
            $koneksi,
            "DELETE FROM penempatan_siswa
             WHERE id = $id"
        );
    }

    header("Location: penempatan_siswa.php");
    exit;
}


/* =========================
   DATA SISWA
========================= */

$siswa = mysqli_query(
    $koneksi,
    "SELECT
        id,
        nis,
        nama
     FROM siswa
     WHERE aktif = 1
     ORDER BY nama ASC"
);


/* =========================
   DATA KELAS
========================= */

$kelas = mysqli_query(
    $koneksi,
    "SELECT
        id,
        nama_kelas
     FROM kelas
     ORDER BY nama_kelas ASC"
);


/* =========================
   DATA TAHUN AJARAN
========================= */

$tahun = mysqli_query(
    $koneksi,
    "SELECT
        id,
        nama,
        tanggal_mulai,
        tanggal_selesai,
        aktif
     FROM tahun_ajaran
     ORDER BY tanggal_mulai DESC"
);


/* =========================
   DATA PENEMPATAN
========================= */

$data = mysqli_query(
    $koneksi,
    "SELECT
        p.id,
        s.nis,
        s.nama AS nama_siswa,
        k.nama_kelas,
        t.nama AS tahun_ajaran
     FROM penempatan_siswa p

     INNER JOIN siswa s
        ON p.siswa_id = s.id

     INNER JOIN kelas k
        ON p.kelas_id = k.id

     INNER JOIN tahun_ajaran t
        ON p.tahun_ajaran_id = t.id

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

.sidebar a:hover,
.sidebar a.active {
    background: #4f5965;
}

.logout {
    margin-top: 20px;
    background: #b42323;
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

.header {
    background: white;
    border: 1px solid #ddd;
    border-radius: 7px;
    padding: 22px;
    margin-bottom: 20px;

    display: flex;
    justify-content: space-between;
    align-items: center;
}

.header h1 {
    margin: 0;
    font-size: 24px;
}

.header p {
    color: #777;
    margin-bottom: 0;
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
}


/* =========================
   TOMBOL
========================= */

.btn {
    display: inline-block;
    padding: 10px 16px;
    border: none;
    border-radius: 5px;
    color: white;
    text-decoration: none;
    cursor: pointer;
    font-size: 14px;
}

.btn-tambah {
    background: #087f5b;
}

.btn-tambah:hover {
    background: #066b4d;
}

.btn-simpan {
    background: #687280;
}

.btn-simpan:hover {
    background: #56606d;
}

.btn-batal {
    background: #777;
}

.btn-hapus {
    background: #b42323;
}

.btn-hapus:hover {
    background: #941d1d;
}


/* =========================
   FORM
========================= */

.form-group {
    margin-bottom: 16px;
}

label {
    display: block;
    margin-bottom: 6px;
    font-size: 13px;
    font-weight: bold;
}

select {
    width: 100%;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
    background: white;
}

.form-actions {
    margin-top: 20px;
}


/* =========================
   ERROR
========================= */

.error {
    background: #fee2e2;
    color: #b91c1c;
    border: 1px solid #fecaca;
    padding: 10px;
    margin-bottom: 15px;
    border-radius: 5px;
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

th {
    background: #687280;
    color: white;
    padding: 11px;
    text-align: left;
    font-size: 13px;
}

td {
    padding: 11px;
    border-bottom: 1px solid #ddd;
    font-size: 13px;
}

tr:hover {
    background: #f7f7f7;
}


/* =========================
   RESPONSIVE
========================= */

@media(max-width:700px) {

    .sidebar {
        position: relative;
        width: 100%;
        height: auto;
    }

    .content {
        margin-left: 0;
        padding: 15px;
    }

    .header {
        display: block;
    }

    .btn-tambah {
        margin-top: 15px;
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

    <a href="logout.php" class="logout">
        🚪 Logout
    </a>

</div>


<!-- =========================
     CONTENT
========================= -->

<div class="content">


    <!-- HEADER -->

    <div class="header">

        <div>

            <h1>
                Penempatan Siswa
            </h1>

            <p>
                Mengatur siswa ke kelas dan tahun ajaran.
            </p>

        </div>


        <a
            href="penempatan_siswa.php?tambah=1"
            class="btn btn-tambah"
        >
            + Tambah Penempatan
        </a>

    </div>


    <!-- ERROR -->

    <?php if (isset($error)) { ?>

        <div class="error">
            <?= htmlspecialchars($error); ?>
        </div>

    <?php } ?>


    <!-- =========================
         FORM TAMBAH
         HANYA MUNCUL SETELAH
         KLIK TAMBAH
    ========================= -->

    <?php if (isset($_GET['tambah']) && $_GET['tambah'] == '1') { ?>

        <div class="box">

            <h2>
                Tambah Penempatan Siswa
            </h2>


            <form method="POST">


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

                        if ($siswa) {

                            while (
                                $s = mysqli_fetch_assoc($siswa)
                            ) {

                        ?>

                            <option
                                value="<?= $s['id']; ?>"
                            >

                                <?= htmlspecialchars($s['nis']); ?>
                                -
                                <?= htmlspecialchars($s['nama']); ?>

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

                    <select
                        name="kelas_id"
                        required
                    >

                        <option value="">
                            -- Pilih Kelas --
                        </option>

                        <?php

                        if ($kelas) {

                            while (
                                $k = mysqli_fetch_assoc($kelas)
                            ) {

                        ?>

                            <option
                                value="<?= $k['id']; ?>"
                            >

                                <?= htmlspecialchars(
                                    $k['nama_kelas']
                                ); ?>

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

                    <select
                        name="tahun_ajaran_id"
                        required
                    >

                        <option value="">
                            -- Pilih Tahun Ajaran --
                        </option>

                        <?php

                        if ($tahun) {

                            while (
                                $t = mysqli_fetch_assoc($tahun)
                            ) {

                        ?>

                            <option
                                value="<?= $t['id']; ?>"
                            >

                                <?= htmlspecialchars(
                                    $t['nama']
                                ); ?>

                                <?php

                                if ((int)$t['aktif'] === 1) {

                                ?>

                                    (Aktif)

                                <?php } ?>

                            </option>

                        <?php

                            }

                        }

                        ?>

                    </select>

                </div>


                <!-- BUTTON -->

                <div class="form-actions">

                    <button
                        type="submit"
                        name="simpan"
                        class="btn btn-simpan"
                    >
                        Simpan Penempatan
                    </button>


                    <a
                        href="penempatan_siswa.php"
                        class="btn btn-batal"
                    >
                        Batal
                    </a>

                </div>


            </form>

        </div>

    <?php } ?>


    <!-- =========================
         DAFTAR PENEMPATAN
    ========================= -->

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
                        <th>Aksi</th>

                    </tr>

                </thead>


                <tbody>

                <?php

                $no = 1;

                if (
                    $data &&
                    mysqli_num_rows($data) > 0
                ) {

                    while (
                        $row = mysqli_fetch_assoc($data)
                    ) {

                ?>

                    <tr>

                        <td>
                            <?= $no++; ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $row['nis']
                            ); ?>
                        </td>

                        <td>

                            <b>
                                <?= htmlspecialchars(
                                    $row['nama_siswa']
                                ); ?>
                            </b>

                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $row['nama_kelas']
                            ); ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $row['tahun_ajaran']
                            ); ?>
                        </td>

                        <td>

                            <a
                                href="penempatan_siswa.php?hapus=<?= $row['id']; ?>"
                                class="btn btn-hapus"
                                onclick="return confirm('Yakin ingin menghapus penempatan ini?')"
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
                            colspan="6"
                            style="text-align:center;padding:25px;"
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