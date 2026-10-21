
<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "database.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* =========================
   CEK LOGIN
========================= */

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

/* =========================
   CEK ROLE
   HANYA ADMIN
========================= */

$role = $_SESSION['role'] ?? '';

if ($role != 'admin') {
    die("Akses ditolak. Halaman ini hanya untuk admin.");
}

/* =========================
   AMBIL ID
========================= */

$id = isset($_GET['id'])
    ? (int) $_GET['id']
    : 0;

if ($id <= 0) {
    die("ID kelas tidak valid.");
}

/* =========================
   AMBIL DATA KELAS
========================= */

$query = mysqli_query(
    $koneksi,
    "SELECT id, nama_kelas
     FROM kelas
     WHERE id = $id
     LIMIT 1"
);

if (!$query) {
    die(
        "Query kelas error: " .
        mysqli_error($koneksi)
    );
}

$data = mysqli_fetch_assoc($query);

if (!$data) {
    die("Data kelas tidak ditemukan.");
}

$pesan = '';

/* =========================
   PROSES UPDATE
========================= */

if (isset($_POST['simpan'])) {

    $nama_kelas = trim(
        $_POST['nama_kelas'] ?? ''
    );

    /* =========================
       VALIDASI
    ========================= */

    if ($nama_kelas == '') {

        $pesan = "Nama kelas wajib diisi.";

    } else {

        $nama_kelas = mysqli_real_escape_string(
            $koneksi,
            $nama_kelas
        );

        /* =========================
           CEK DUPLIKAT
        ========================= */

        $cek = mysqli_query(
            $koneksi,
            "SELECT id
             FROM kelas
             WHERE nama_kelas = '$nama_kelas'
             AND id != $id
             LIMIT 1"
        );

        if (!$cek) {

            $pesan =
                "Query pengecekan error: " .
                mysqli_error($koneksi);

        } elseif (mysqli_num_rows($cek) > 0) {

            $pesan =
                "Nama kelas tersebut sudah digunakan.";

        } else {

            /* =========================
               UPDATE DATA
            ========================= */

            $update = mysqli_query(
                $koneksi,
                "UPDATE kelas
                 SET nama_kelas = '$nama_kelas'
                 WHERE id = $id"
            );

            if ($update) {

                header(
                    "Location: kelas.php"
                );

                exit;

            } else {

                $pesan =
                    "Gagal mengubah data kelas: " .
                    mysqli_error($koneksi);
            }
        }
    }
}

?>

<!DOCTYPE html>

<html lang="id">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Edit Kelas</title>

<style>

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #f4f4f4;
    color: #333;
}

.header {
    background: #004d4d;
    color: white;
    padding: 18px 30px;

    display: flex;
    justify-content: space-between;
    align-items: center;
}

.header h2 {
    margin: 0;
    font-size: 20px;
}

.kembali {
    background: #176b80;
    color: white;
    text-decoration: none;
    padding: 9px 14px;
    border-radius: 5px;
}

.kembali:hover {
    background: #0d5364;
}

.container {
    width: 90%;
    max-width: 650px;
    margin: 35px auto;
}

.box {
    background: white;
    border: 1px solid #ddd;
    border-radius: 7px;
    padding: 25px;
}

.box h1 {
    margin-top: 0;
    font-size: 25px;
}

.deskripsi {
    color: #777;
    margin-bottom: 25px;
}

.form-group {
    margin-bottom: 18px;
}

label {
    display: block;
    font-weight: bold;
    margin-bottom: 7px;
}

input {
    width: 100%;
    padding: 11px;
    border: 1px solid #ccc;
    border-radius: 5px;
    font-size: 14px;
}

.btn {
    display: inline-block;
    padding: 10px 16px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    text-decoration: none;
    font-size: 14px;
}

.btn-simpan {
    background: #087f5b;
    color: white;
}

.btn-simpan:hover {
    background: #066b4d;
}

.btn-batal {
    background: #777;
    color: white;
    margin-left: 5px;
}

.btn-batal:hover {
    background: #555;
}

.pesan {
    background: #fff3cd;
    border: 1px solid #ffe69c;
    color: #664d03;
    padding: 12px;
    border-radius: 5px;
    margin-bottom: 20px;
}

</style>

</head>

<body>

<div class="header">

    <h2>
        SI Pelanggaran
    </h2>

    <a
        href="kelas.php"
        class="kembali"
    >
        ← Kembali
    </a>

</div>


<div class="container">

    <div class="box">

        <h1>
            Edit Data Kelas
        </h1>

        <p class="deskripsi">
            Ubah nama kelas yang terdaftar dalam sistem.
        </p>


        <?php if ($pesan != '') { ?>

            <div class="pesan">
                <?= htmlspecialchars($pesan); ?>
            </div>

        <?php } ?>


        <form method="POST">

            <div class="form-group">

                <label>
                    Nama Kelas
                </label>

                <input
                    type="text"
                    name="nama_kelas"
                    value="<?= htmlspecialchars($data['nama_kelas']); ?>"
                    placeholder="Contoh: XII RPL 1"
                    required
                >

            </div>


            <button
                type="submit"
                name="simpan"
                class="btn btn-simpan"
            >
                Simpan Perubahan
            </button>


            <a
                href="kelas.php"
                class="btn btn-batal"
            >
                Batal
            </a>

        </form>

    </div>

</div>

</body>

</html>
```