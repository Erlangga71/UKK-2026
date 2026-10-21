```php
<?php

session_start();

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

include "database.php";


/* =========================
   AMBIL ID SISWA
========================= */

$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    header("Location: kelola_siswa.php");
    exit;
}


/* =========================
   PROSES UPDATE
========================= */

if (isset($_POST['update'])) {

    $nis = trim($_POST['nis'] ?? '');
    $nama = trim($_POST['nama'] ?? '');
    $jenis_kelamin = $_POST['jenis_kelamin'] ?? '';
    $no_hp_ortu = trim($_POST['no_hp_ortu'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');


    /* VALIDASI */

    if ($nis == '' || $nama == '') {

        $error = "NIS dan nama wajib diisi.";

    } else {

        $nis_db = mysqli_real_escape_string(
            $koneksi,
            $nis
        );

        $nama_db = mysqli_real_escape_string(
            $koneksi,
            $nama
        );

        $jk_db = mysqli_real_escape_string(
            $koneksi,
            $jenis_kelamin
        );

        $no_hp_db = mysqli_real_escape_string(
            $koneksi,
            $no_hp_ortu
        );

        $alamat_db = mysqli_real_escape_string(
            $koneksi,
            $alamat
        );


        /* CEK NIS */

        $cek = mysqli_query(
            $koneksi,
            "SELECT id
             FROM siswa
             WHERE nis = '$nis_db'
             AND id != $id
             LIMIT 1"
        );


        if (mysqli_num_rows($cek) > 0) {

            $error = "NIS tersebut sudah digunakan.";

        } else {


            /* UPDATE DATA */

            $update = mysqli_query(
                $koneksi,
                "UPDATE siswa SET

                    nis = '$nis_db',

                    nama = '$nama_db',

                    jenis_kelamin = '$jk_db',

                    alamat = '$alamat_db',

                    no_hp_ortu = '$no_hp_db'

                 WHERE id = $id"
            );


            if ($update) {

                header(
                    "Location: kelola_siswa.php"
                );

                exit;

            } else {

                $error = mysqli_error($koneksi);
            }
        }
    }
}


/* =========================
   AMBIL DATA SISWA
========================= */

$query = mysqli_query(
    $koneksi,
    "SELECT *
     FROM siswa
     WHERE id = $id
     LIMIT 1"
);


if (!$query || mysqli_num_rows($query) == 0) {

    echo "Data siswa tidak ditemukan.";
    exit;
}


$siswa = mysqli_fetch_assoc($query);

?>

<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Edit Data Siswa</title>

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


.content {

    width: 700px;

    max-width: 90%;

    margin: 40px auto;
}


.box {

    background: white;

    border: 1px solid #ddd;

    border-radius: 7px;

    padding: 25px;
}


h1 {

    margin-top: 0;

    font-size: 24px;
}


.form-group {

    margin-bottom: 15px;
}


label {

    display: block;

    margin-bottom: 6px;

    font-size: 13px;

    font-weight: bold;
}


input,
select,
textarea {

    width: 100%;

    padding: 10px;

    border: 1px solid #ccc;

    border-radius: 5px;

    font-family: Arial;
}


textarea {

    resize: vertical;
}


button,
a {

    display: inline-block;

    padding: 10px 16px;

    border: none;

    border-radius: 5px;

    text-decoration: none;

    cursor: pointer;

    font-size: 14px;
}


button {

    background: #687280;

    color: white;
}


button:hover {

    background: #56606d;
}


.kembali {

    background: #ddd;

    color: #333;

    margin-left: 5px;
}


.error {

    background: #fee2e2;

    color: #b91c1c;

    border: 1px solid #fecaca;

    padding: 10px;

    margin-bottom: 15px;

    border-radius: 5px;
}

</style>

</head>

<body>


<div class="content">

    <div class="box">

        <h1>Edit Data Siswa</h1>


        <?php if (isset($error)) { ?>

            <div class="error">

                <?= htmlspecialchars($error); ?>

            </div>

        <?php } ?>


        <form method="POST">


            <div class="form-group">

                <label>NIS</label>

                <input
                    type="text"
                    name="nis"
                    value="<?= htmlspecialchars($siswa['nis'] ?? ''); ?>"
                    required
                >

            </div>


            <div class="form-group">

                <label>Nama Siswa</label>

                <input
                    type="text"
                    name="nama"
                    value="<?= htmlspecialchars($siswa['nama'] ?? ''); ?>"
                    required
                >

            </div>


            <div class="form-group">

                <label>Jenis Kelamin</label>

                <select name="jenis_kelamin">

                    <option value="">
                        -- Pilih Jenis Kelamin --
                    </option>

                    <option
                        value="L"
                        <?= ($siswa['jenis_kelamin'] ?? '') == 'L' ? 'selected' : ''; ?>
                    >
                        Laki-laki
                    </option>

                    <option
                        value="P"
                        <?= ($siswa['jenis_kelamin'] ?? '') == 'P' ? 'selected' : ''; ?>
                    >
                        Perempuan
                    </option>

                </select>

            </div>


            <div class="form-group">

                <label>No HP Orang Tua</label>

                <input
                    type="text"
                    name="no_hp_ortu"
                    value="<?= htmlspecialchars($siswa['no_hp_ortu'] ?? ''); ?>"
                >

            </div>


            <div class="form-group">

                <label>Alamat</label>

                <textarea
                    name="alamat"
                    rows="4"
                ><?= htmlspecialchars($siswa['alamat'] ?? ''); ?></textarea>

            </div>


            <button
                type="submit"
                name="update"
            >
                Simpan Perubahan
            </button>


            <a
                href="kelola_siswa.php"
                class="kembali"
            >
                Kembali
            </a>


        </form>

    </div>

</div>

</body>

</html>
```