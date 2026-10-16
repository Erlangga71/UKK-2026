<?php

require_once "cek_role.php";
wajib_role(['admin']);

include "database.php";

$error = "";

if (isset($_POST['simpan'])) {

    $nip = mysqli_real_escape_string($koneksi, $_POST['nip']);
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $email = mysqli_real_escape_string($koneksi, $_POST['email']);
    $no_hp = mysqli_real_escape_string($koneksi, $_POST['no_hp']);

    if ($nama == "") {
        $error = "Nama guru wajib diisi.";
    } else {

        $query = mysqli_query($koneksi, "
            INSERT INTO guru
            (nip, nama, email, no_hp)
            VALUES
            ('$nip', '$nama', '$email', '$no_hp')
        ");

        if ($query) {

            header("Location: guru.php");
            exit;

        } else {

            $error = mysqli_error($koneksi);

        }
    }
}

?>

<!DOCTYPE html>
<html>

<head>

    <title>Tambah Data Guru</title>

    <style>

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f1f5f9;
        }

        .header {
            background: #086177;
            color: white;
            padding: 18px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header a {
            color: white;
            text-decoration: none;
            background: #dc2626;
            padding: 8px 15px;
            border-radius: 5px;
        }

        .container {
            width: 90%;
            max-width: 700px;
            margin: 30px auto;
        }

        .box {
            background: white;
            padding: 25px;
            border: 1px solid #ddd;
        }

        h2 {
            margin-top: 0;
            color: #2563eb;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }

        input {
            display: block;
            width: 100%;
            height: 40px;
            padding: 8px;
            border: 1px solid #999;
            border-radius: 4px;
            font-size: 15px;
            background: white;
            color: black;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .btn {
            display: inline-block;
            padding: 10px 18px;
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .batal {
            margin-left: 10px;
            text-decoration: none;
            color: #333;
        }

        .error {
            color: red;
            background: #fee2e2;
            padding: 10px;
            margin-bottom: 15px;
        }

    </style>

</head>

<body>

<div class="header">

    <b>SISTEM INFORMASI PELANGGARAN SISWA</b>

    <a href="guru.php">Kembali</a>

</div>

<div class="container">

    <div class="box">

        <h2>Tambah Data Guru</h2>

        <?php if ($error != "") { ?>

            <div class="error">
                <?= htmlspecialchars($error); ?>
            </div>

        <?php } ?>

        <form method="POST">

            <div class="form-group">

                <label for="nip">
                    NIP
                </label>

                <input
                    type="text"
                    id="nip"
                    name="nip"
                    placeholder="Masukkan NIP"
                >

            </div>

            <div class="form-group">

                <label for="nama">
                    Nama Guru
                </label>

                <input
                    type="text"
                    id="nama"
                    name="nama"
                    placeholder="Masukkan nama guru"
                    required
                >

            </div>

            <div class="form-group">

                <label for="email">
                    Email
                </label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    placeholder="Masukkan email"
                >

            </div>

            <div class="form-group">

                <label for="no_hp">
                    No. HP
                </label>

                <input
                    type="text"
                    id="no_hp"
                    name="no_hp"
                    placeholder="Masukkan nomor HP"
                >

            </div>

            <button
                type="submit"
                name="simpan"
                class="btn"
            >
                Simpan
            </button>

            <a
                href="guru.php"
                class="batal"
            >
                Batal
            </a>

        </form>

    </div>

</div>

</body>

</html>