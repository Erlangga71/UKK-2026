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
   AMBIL ID
========================= */

$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    header("Location: kelola_tahun_ajaran.php");
    exit;
}


/* =========================
   SIMPAN PERUBAHAN
========================= */

if (isset($_POST['update'])) {

    $nama = trim($_POST['nama'] ?? '');
    $tanggal_mulai = $_POST['tanggal_mulai'] ?? '';
    $tanggal_selesai = $_POST['tanggal_selesai'] ?? '';

    if (
        $nama == '' ||
        $tanggal_mulai == '' ||
        $tanggal_selesai == ''
    ) {

        $pesan = "Semua data harus diisi.";

    } else {

        $nama_db = mysqli_real_escape_string(
            $koneksi,
            $nama
        );

        $tanggal_mulai_db = mysqli_real_escape_string(
            $koneksi,
            $tanggal_mulai
        );

        $tanggal_selesai_db = mysqli_real_escape_string(
            $koneksi,
            $tanggal_selesai
        );


        /* =========================
           UPDATE DATA
        ========================= */

        $update = mysqli_query(
            $koneksi,
            "UPDATE tahun_ajaran SET
                nama = '$nama_db',
                tanggal_mulai = '$tanggal_mulai_db',
                tanggal_selesai = '$tanggal_selesai_db'
             WHERE id = $id"
        );


        if ($update) {

            header(
                "Location: kelola_tahun_ajaran.php"
            );

            exit;

        } else {

            $pesan = "Gagal menyimpan perubahan: " .
                     mysqli_error($koneksi);
        }
    }
}


/* =========================
   AMBIL DATA
========================= */

$query = mysqli_query(
    $koneksi,
    "SELECT
        id,
        nama,
        tanggal_mulai,
        tanggal_selesai,
        aktif
     FROM tahun_ajaran
     WHERE id = $id
     LIMIT 1"
);


if (!$query) {

    die(
        "Query error: " .
        mysqli_error($koneksi)
    );
}


if (mysqli_num_rows($query) == 0) {

    echo "Data tahun ajaran tidak ditemukan.";
    exit;
}


$data = mysqli_fetch_assoc($query);

?>

<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Edit Tahun Ajaran</title>

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
    margin: 50px auto;
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

p {
    color: #777;
}

.form-group {
    margin-bottom: 17px;
}

label {
    display: block;
    margin-bottom: 7px;
    font-size: 13px;
    font-weight: bold;
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

button,
a {
    display: inline-block;
    padding: 10px 16px;
    border-radius: 5px;
    text-decoration: none;
    font-size: 14px;
    cursor: pointer;
}

button {
    border: none;
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

.kembali:hover {
    background: #ccc;
}

.pesan {
    background: #fee2e2;
    color: #b91c1c;
    border: 1px solid #fecaca;
    padding: 10px;
    margin-bottom: 18px;
    border-radius: 5px;
}

.status {
    margin-bottom: 20px;
    padding: 10px;
    background: #f1f5f9;
    border: 1px solid #ddd;
    border-radius: 5px;
}

.status b {
    color: #333;
}

</style>

</head>

<body>

<div class="content">

    <div class="box">

        <h1>
            Edit Tahun Ajaran
        </h1>

        <p>
            Ubah data tahun ajaran di bawah ini.
        </p>


        <?php if (isset($pesan)) { ?>

            <div class="pesan">

                <?= htmlspecialchars($pesan); ?>

            </div>

        <?php } ?>


        <div class="status">

            Status:
            
            <?php if ($data['aktif'] == 1) { ?>

                <b style="color:green;">
                    Aktif
                </b>

            <?php } else { ?>

                <b style="color:red;">
                    Tidak Aktif
                </b>

            <?php } ?>

        </div>


        <form method="POST">


            <div class="form-group">

                <label>
                    Tahun Ajaran
                </label>

                <input
                    type="text"
                    name="nama"
                    value="<?= htmlspecialchars($data['nama']); ?>"
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
                    value="<?= htmlspecialchars($data['tanggal_mulai']); ?>"
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
                    value="<?= htmlspecialchars($data['tanggal_selesai']); ?>"
                    required
                >

            </div>


            <button
                type="submit"
                name="update"
            >
                Simpan Perubahan
            </button>


            <a
                href="kelola_tahun_ajaran.php"
                class="kembali"
            >
                Kembali
            </a>

        </form>

    </div>

</div>

</body>

</html>