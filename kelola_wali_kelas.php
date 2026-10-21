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
   TAMBAH WALI KELAS
========================= */

if (isset($_POST['simpan'])) {

    $guru_id = intval($_POST['guru_id'] ?? 0);
    $kelas_id = intval($_POST['kelas_id'] ?? 0);
    $tahun_ajaran_id = intval($_POST['tahun_ajaran_id'] ?? 0);

    if (
        $guru_id <= 0 ||
        $kelas_id <= 0 ||
        $tahun_ajaran_id <= 0
    ) {

        $error = "Guru, kelas, dan tahun ajaran wajib dipilih.";

    } else {

        /* =========================
           CEK DATA GURU
        ========================= */

        $cek_guru = mysqli_query(
            $koneksi,
            "SELECT id
             FROM guru
             WHERE id = $guru_id
             LIMIT 1"
        );


        /* =========================
           CEK DATA KELAS
        ========================= */

        $cek_kelas = mysqli_query(
            $koneksi,
            "SELECT id
             FROM kelas
             WHERE id = $kelas_id
             LIMIT 1"
        );


        /* =========================
           CEK TAHUN AJARAN
        ========================= */

        $cek_tahun = mysqli_query(
            $koneksi,
            "SELECT id
             FROM tahun_ajaran
             WHERE id = $tahun_ajaran_id
             LIMIT 1"
        );


        if (
            mysqli_num_rows($cek_guru) == 0 ||
            mysqli_num_rows($cek_kelas) == 0 ||
            mysqli_num_rows($cek_tahun) == 0
        ) {

            $error = "Data guru, kelas, atau tahun ajaran tidak valid.";

        } else {

            /* =========================
               CEK WALI KELAS SUDAH ADA
            ========================= */

            $cek = mysqli_query(
                $koneksi,
                "SELECT id
                 FROM wali_kelas
                 WHERE kelas_id = $kelas_id
                 AND tahun_ajaran_id = $tahun_ajaran_id
                 LIMIT 1"
            );


            if (mysqli_num_rows($cek) > 0) {

                $error =
                    "Kelas tersebut sudah memiliki wali kelas pada tahun ajaran ini.";

            } else {

                /* =========================
                   SIMPAN
                ========================= */

                $query = mysqli_query(
                    $koneksi,
                    "INSERT INTO wali_kelas
                    (
                        guru_id,
                        kelas_id,
                        tahun_ajaran_id
                    )
                    VALUES
                    (
                        $guru_id,
                        $kelas_id,
                        $tahun_ajaran_id
                    )"
                );


                if ($query) {

                    header(
                        "Location: kelola_wali_kelas.php"
                    );

                    exit;

                } else {

                    $error =
                        "Gagal menambahkan wali kelas: "
                        . mysqli_error($koneksi);
                }
            }
        }
    }
}


/* =========================
   HAPUS WALI KELAS
========================= */

if (isset($_GET['hapus'])) {

    $id = intval($_GET['hapus']);

    if ($id > 0) {

        mysqli_query(
            $koneksi,
            "DELETE FROM wali_kelas
             WHERE id = $id"
        );
    }

    header(
        "Location: kelola_wali_kelas.php"
    );

    exit;
}


/* =========================
   DATA GURU
========================= */

$guru_data = mysqli_query(
    $koneksi,
    "SELECT
        id,
        nip,
        nama
     FROM guru
     ORDER BY nama ASC"
);


/* =========================
   DATA KELAS
========================= */

$kelas_data = mysqli_query(
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

$tahun_data = mysqli_query(
    $koneksi,
    "SELECT
        id,
        nama,
        aktif
     FROM tahun_ajaran
     ORDER BY id DESC"
);


/* =========================
   DATA WALI KELAS
========================= */

$data = mysqli_query(
    $koneksi,
    "SELECT
        wk.id,
        wk.guru_id,
        wk.kelas_id,
        wk.tahun_ajaran_id,

        g.nip,
        g.nama AS nama_guru,

        k.nama_kelas,

        t.nama AS nama_tahun,
        t.aktif

     FROM wali_kelas wk

     INNER JOIN guru g
        ON wk.guru_id = g.id

     INNER JOIN kelas k
        ON wk.kelas_id = k.id

     INNER JOIN tahun_ajaran t
        ON wk.tahun_ajaran_id = t.id

     ORDER BY wk.id DESC"
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

<title>Wali Kelas</title>


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
   ATAS
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

select {

    padding: 10px;

    border: 1px solid #ccc;

    border-radius: 5px;

    font-family: Arial;

    background: white;
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
   AKSI
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


    <a href="kelola_tahun_ajaran.php">
        Tahun Ajaran
    </a>


    <a
        href="kelola_wali_kelas.php"
        class="active"
    >
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
            Wali Kelas
        </h1>

        <p>
            Kelola guru yang menjadi wali kelas.
        </p>

    </div>


    <?php if (isset($error)) { ?>

        <div class="error">

            <?= htmlspecialchars($error); ?>

        </div>

    <?php } ?>


    <!-- =========================
         DAFTAR WALI KELAS
    ========================= -->

    <div class="box">


        <div class="atas">

            <h2>
                Daftar Wali Kelas
            </h2>


            <a
                href="#form-tambah"
                class="btn-tambah"
                onclick="tampilkanForm()"
            >
                + Tambah Wali Kelas
            </a>

        </div>


        <div class="table-container">

            <table>

                <thead>

                    <tr>

                        <th>No</th>

                        <th>NIP</th>

                        <th>Nama Guru</th>

                        <th>Kelas</th>

                        <th>Tahun Ajaran</th>

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

                            <?= htmlspecialchars(
                                $row['nip'] ?? '-'
                            ); ?>

                        </td>


                        <td>

                            <b>

                                <?= htmlspecialchars(
                                    $row['nama_guru']
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
                                $row['nama_tahun']
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
                                href="edit_wali_kelas.php?id=<?= $row['id']; ?>"
                                class="btn-edit"
                            >
                                Edit
                            </a>


                            <a
                                href="kelola_wali_kelas.php?hapus=<?= $row['id']; ?>"
                                class="btn-hapus"
                                onclick="return confirm('Yakin ingin menghapus wali kelas ini?')"
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
                            colspan="7"
                            style="text-align:center;padding:25px;"
                        >

                            Belum ada data wali kelas.

                        </td>

                    </tr>

                <?php } ?>


                </tbody>

            </table>

        </div>

    </div>


    <!-- =========================
         FORM TAMBAH
         TERSEMBUNYI
    ========================= -->

    <div
        class="box"
        id="form-tambah"
        style="display:none;"
    >

        <h2>
            Tambah Wali Kelas
        </h2>


        <form method="POST">


            <div class="form-grid">


                <!-- GURU -->

                <div class="form-group">

                    <label>
                        Guru
                    </label>

                    <select
                        name="guru_id"
                        required
                    >

                        <option value="">
                            -- Pilih Guru --
                        </option>


                        <?php

                        if ($guru_data) {

                            while (
                                $g = mysqli_fetch_assoc($guru_data)
                            ) {

                        ?>

                            <option
                                value="<?= $g['id']; ?>"
                            >

                                <?= htmlspecialchars(
                                    $g['nip'] ?? '-'
                                ); ?>

                                -
                                
                                <?= htmlspecialchars(
                                    $g['nama']
                                ); ?>

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

                        if ($kelas_data) {

                            while (
                                $k = mysqli_fetch_assoc($kelas_data)
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

                <div class="form-group full">

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

                        if ($tahun_data) {

                            while (
                                $t = mysqli_fetch_assoc($tahun_data)
                            ) {

                        ?>

                            <option
                                value="<?= $t['id']; ?>"
                            >

                                <?= htmlspecialchars(
                                    $t['nama']
                                ); ?>

                                <?php
                                if ($t['aktif'] == 1) {
                                    echo " - Aktif";
                                }
                                ?>

                            </option>

                        <?php

                            }

                        }

                        ?>

                    </select>

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