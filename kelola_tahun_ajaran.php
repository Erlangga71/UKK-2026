<?php

session_start();

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

include "database.php";

$role = $_SESSION['role'] ?? '';

/* =========================
   CEK ADMIN
========================= */

if ($role != 'admin') {
    echo "Akses ditolak.";
    exit;
}


/* =========================
   TAMBAH TAHUN AJARAN
========================= */

if (isset($_POST['simpan'])) {

    $nama = trim($_POST['nama'] ?? '');
    $tanggal_mulai = $_POST['tanggal_mulai'] ?? '';
    $tanggal_selesai = $_POST['tanggal_selesai'] ?? '';
    $aktif = isset($_POST['aktif']) ? 1 : 0;

    if (
        $nama == '' ||
        $tanggal_mulai == '' ||
        $tanggal_selesai == ''
    ) {

        $error = "Semua data wajib diisi.";

    } else {

        $nama_db = mysqli_real_escape_string(
            $koneksi,
            $nama
        );

        $mulai_db = mysqli_real_escape_string(
            $koneksi,
            $tanggal_mulai
        );

        $selesai_db = mysqli_real_escape_string(
            $koneksi,
            $tanggal_selesai
        );


        /* Jika aktif, nonaktifkan yang lain */

        if ($aktif == 1) {

            mysqli_query(
                $koneksi,
                "UPDATE tahun_ajaran
                 SET aktif = 0"
            );
        }


        $query = mysqli_query(
            $koneksi,
            "INSERT INTO tahun_ajaran
            (
                nama,
                tanggal_mulai,
                tanggal_selesai,
                aktif
            )
            VALUES
            (
                '$nama_db',
                '$mulai_db',
                '$selesai_db',
                $aktif
            )"
        );


        if ($query) {

            header(
                "Location: kelola_tahun_ajaran.php"
            );

            exit;

        } else {

            $error =
                "Gagal menambahkan tahun ajaran: "
                . mysqli_error($koneksi);
        }
    }
}


/* =========================
   HAPUS TAHUN AJARAN
========================= */

if (isset($_GET['hapus'])) {

    $id = intval($_GET['hapus']);

    if ($id > 0) {

        mysqli_query(
            $koneksi,
            "DELETE FROM tahun_ajaran
             WHERE id = $id"
        );
    }

    header(
        "Location: kelola_tahun_ajaran.php"
    );

    exit;
}


/* =========================
   AMBIL DATA
========================= */

$data = mysqli_query(
    $koneksi,
    "SELECT
        id,
        nama,
        tanggal_mulai,
        tanggal_selesai,
        aktif
     FROM tahun_ajaran
     ORDER BY id DESC"
);

?>

<!DOCTYPE html>

<html lang="id">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Tahun Ajaran</title>


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

.logout:hover {
    background: #941d1d !important;
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


/* =========================
   TOMBOL ATAS
========================= */

.atas {
    display: flex;

    justify-content: space-between;

    align-items: center;

    margin-bottom: 20px;
}

.atas h2 {
    margin: 0;
}

.btn-tambah {
    display: inline-block;

    background: #087f5b;

    color: white;

    text-decoration: none;

    padding: 10px 15px;

    border-radius: 5px;
}

.btn-tambah:hover {
    background: #066b4d;
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
    display: flex;

    flex-direction: column;
}

.form-group.full {
    grid-column: 1 / -1;
}

label {
    margin-bottom: 6px;

    font-size: 13px;

    font-weight: bold;
}

input {
    padding: 10px;

    border: 1px solid #ccc;

    border-radius: 5px;

    font-family: Arial;
}

.checkbox {
    display: flex;

    align-items: center;

    gap: 8px;

    margin-top: 5px;
}

.checkbox input {
    width: auto;
}

.btn-simpan {
    margin-top: 15px;

    padding: 10px 18px;

    border: none;

    border-radius: 5px;

    background: #687280;

    color: white;

    cursor: pointer;
}

.btn-simpan:hover {
    background: #56606d;
}

.btn-batal {
    display: inline-block;

    margin-left: 8px;

    padding: 9px 16px;

    background: #777;

    color: white;

    text-decoration: none;

    border-radius: 5px;
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
   STATUS
========================= */

.status-aktif {
    color: #087f5b;

    font-weight: bold;
}

.status-nonaktif {
    color: #b42323;

    font-weight: bold;
}


/* =========================
   TOMBOL AKSI
========================= */

.btn-edit,
.btn-hapus {
    display: inline-block;

    color: white;

    padding: 6px 10px;

    text-decoration: none;

    border-radius: 4px;

    font-size: 12px;
}

.btn-edit {
    background: #086177;
}

.btn-edit:hover {
    background: #064d5f;
}

.btn-hapus {
    background: #b42323;
}

.btn-hapus:hover {
    background: #941d1d;
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

    .form-grid {
        grid-template-columns: 1fr;
    }

    .atas {
        display: block;
    }

    .btn-tambah {
        display: inline-block;

        margin-top: 10px;
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


    <a
        href="kelola_tahun_ajaran.php"
        class="active"
    >
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


    <a
        href="logout.php"
        class="logout"
    >
        🚪 Logout
    </a>

</div>


<!-- =========================
     CONTENT
========================= -->

<div class="content">


    <div class="header">

        <h1>
            Tahun Ajaran
        </h1>

        <p>
            Kelola data tahun ajaran sekolah.
        </p>

    </div>


    <?php if (isset($error)) { ?>

        <div class="error">

            <?= htmlspecialchars($error); ?>

        </div>

    <?php } ?>


    <!-- =========================
         DAFTAR TAHUN AJARAN
    ========================= -->

    <div class="box">


        <div class="atas">

            <h2>
                Daftar Tahun Ajaran
            </h2>


            <a
                href="#form-tambah"
                class="btn-tambah"
                onclick="tampilkanForm()"
            >
                + Tambah Tahun Ajaran
            </a>

        </div>


        <div class="table-container">

            <table>

                <thead>

                    <tr>

                        <th>No</th>

                        <th>Nama</th>

                        <th>Tanggal Mulai</th>

                        <th>Tanggal Selesai</th>

                        <th>Status</th>

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

                            <b>
                                <?= htmlspecialchars(
                                    $row['nama']
                                ); ?>
                            </b>

                        </td>


                        <td>

                            <?= htmlspecialchars(
                                $row['tanggal_mulai']
                            ); ?>

                        </td>


                        <td>

                            <?= htmlspecialchars(
                                $row['tanggal_selesai']
                            ); ?>

                        </td>


                        <td>

                            <?php if ($row['aktif'] == 1) { ?>

                                <span class="status-aktif">
                                    Aktif
                                </span>

                            <?php } else { ?>

                                <span class="status-nonaktif">
                                    Tidak Aktif
                                </span>

                            <?php } ?>

                        </td>


                        <td>

                            <a
                                href="edit_tahun_ajaran.php?id=<?= $row['id']; ?>"
                                class="btn-edit"
                            >
                                Edit
                            </a>


                            <a
                                href="kelola_tahun_ajaran.php?hapus=<?= $row['id']; ?>"
                                class="btn-hapus"
                                onclick="return confirm('Yakin ingin menghapus tahun ajaran ini?')"
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

                            Belum ada data tahun ajaran.

                        </td>

                    </tr>

                <?php } ?>

                </tbody>

            </table>

        </div>

    </div>


    <!-- =========================
         FORM TAMBAH
         AWALNYA DISEMBUNYIKAN
    ========================= -->

    <div
        class="box"
        id="form-tambah"
        style="display:none;"
    >

        <h2>
            Tambah Tahun Ajaran
        </h2>


        <form method="POST">


            <div class="form-grid">


                <div class="form-group full">

                    <label>
                        Nama Tahun Ajaran
                    </label>

                    <input
                        type="text"
                        name="nama"
                        placeholder="Contoh: 2026/2027"
                        required
                    >

                </div>


                <div class="form-group">

                    <label>
                        Tanggal Mulai
                    </label>

                    <input
                        type="date"
                        name="tanggal_mulai"
                        required
                    >

                </div>


                <div class="form-group">

                    <label>
                        Tanggal Selesai
                    </label>

                    <input
                        type="date"
                        name="tanggal_selesai"
                        required
                    >

                </div>


                <div class="form-group full">

                    <label>
                        Status
                    </label>

                    <div class="checkbox">

                        <input
                            type="checkbox"
                            name="aktif"
                            value="1"
                        >

                        <span>
                            Jadikan tahun ajaran aktif
                        </span>

                    </div>

                </div>


            </div>


            <button
                type="submit"
                name="simpan"
                class="btn-simpan"
            >
                Simpan
            </button>


            <a
                href="#"
                class="btn-batal"
                onclick="sembunyikanForm()"
            >
                Batal
            </a>


        </form>

    </div>


</div>


<script>

/* =========================
   TAMPILKAN FORM
========================= */

function tampilkanForm() {

    document.getElementById(
        "form-tambah"
    ).style.display = "block";

    document.getElementById(
        "form-tambah"
    ).scrollIntoView({
        behavior: "smooth"
    });
}


/* =========================
   SEMBUNYIKAN FORM
========================= */

function sembunyikanForm() {

    document.getElementById(
        "form-tambah"
    ).style.display = "none";

}

</script>


</body>

</html>