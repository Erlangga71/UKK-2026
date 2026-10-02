<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <title>Tambah Siswa</title>

    <style>

        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            margin: 0;
        }

        .container {
            width: 500px;
            margin: 40px auto;

            background: white;

            padding: 25px;

            border: 1px solid #ccc;
        }

        h2 {
            margin-top: 0;
        }

        label {
            display: block;
            margin-bottom: 5px;
        }

        input,
        select,
        textarea {
            width: 100%;
            padding: 9px;

            box-sizing: border-box;

            margin-bottom: 15px;

            border: 1px solid #aaa;
        }

        textarea {
            height: 80px;
        }

        button {
            padding: 9px 20px;

            background: #333;
            color: white;

            border: none;

            cursor: pointer;
        }

        a {
            margin-left: 10px;
            color: #333;
        }

    </style>

</head>

<body>


<div class="container">

    <h2>Tambah Siswa</h2>


    <form action="simpan.php" method="POST">

        <label>NIS</label>

        <input
            type="text"
            name="nis"
            required
        >


        <label>Nama Siswa</label>

        <input
            type="text"
            name="nama"
            required
        >


        <label>Jenis Kelamin</label>

        <select
            name="jenis_kelamin"
            required
        >

            <option value="">
                -- Pilih --
            </option>

            <option value="L">
                Laki-laki
            </option>

            <option value="P">
                Perempuan
            </option>

        </select>


        <label>Kelas</label>

        <input
            type="text"
            name="kelas"
            placeholder="Contoh: X RPL 1"
            required
        >


        <label>Alamat</label>

        <textarea
            name="alamat"
        ></textarea>


        <label>No HP</label>

        <input
            type="text"
            name="no_hp"
        >


        <button type="submit">
            Simpan
        </button>

        <a href="index.php">
            Batal
        </a>

    </form>

</div>


</body>

</html>
