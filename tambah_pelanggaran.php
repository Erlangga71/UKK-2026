<?php

require_once "cek_role.php";

wajib_role(['admin', 'guru']);

include "database.php";


/*
|--------------------------------------------------------------------------
| Ambil data siswa
|--------------------------------------------------------------------------
*/

$siswa = mysqli_query($koneksi, "
    SELECT id, nis, nama
    FROM siswa
    WHERE aktif = 1
    ORDER BY nama ASC
");


/*
|--------------------------------------------------------------------------
| Ambil jenis pelanggaran
|--------------------------------------------------------------------------
*/

$jenis = mysqli_query($koneksi, "
    SELECT
        jenis_pelanggaran.id,
        jenis_pelanggaran.nama,
        jenis_pelanggaran.poin
    FROM jenis_pelanggaran
    ORDER BY jenis_pelanggaran.nama ASC
");


/*
|--------------------------------------------------------------------------
| Ambil data guru
|--------------------------------------------------------------------------
*/

$guru = mysqli_query($koneksi, "
    SELECT id, nama
    FROM guru
    ORDER BY nama ASC
");


$error = "";


/*
|--------------------------------------------------------------------------
| SIMPAN
|--------------------------------------------------------------------------
*/

if (isset($_POST['simpan'])) {

    $siswa_id = intval($_POST['siswa_id']);
    $jenis_id = intval($_POST['jenis_id']);
    $guru_id = !empty($_POST['guru_id'])
        ? intval($_POST['guru_id'])
        : "NULL";

    $tanggal = mysqli_real_escape_string(
        $koneksi,
        $_POST['tanggal']
    );

    $keterangan = mysqli_real_escape_string(
        $koneksi,
        $_POST['keterangan']
    );

    $status = mysqli_real_escape_string(
        $koneksi,
        $_POST['status']
    );


    $query = mysqli_query($koneksi, "
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
            $guru_id,
            '$tanggal',
            '$keterangan',
            '$status'
        )
    ");


    if ($query) {

        header("Location: pelanggaran.php");
        exit;

    } else {

        $error = mysqli_error($koneksi);

    }
}

?>

<!DOCTYPE html>
<html>

<head>

    <title>Tambah Pelanggaran</title>

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

        .form-group {
            margin-bottom: 18px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }

        input,
        select,
        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #999;
            border-radius: 4px;
            font-size: 15px;
            box-sizing: border-box;
            background: white;
        }

        textarea {
            min-height: 100px;
        }

        .btn {
            background: #2563eb;
            color: white;
            border: none;
            padding: 10px 18px;
            border-radius: 5px;
            cursor: pointer;
        }

        .batal {
            margin-left: 10px;
            text-decoration: none;
            color: #333;
        }

        .error {
            background: #fee2e2;
            color: #b91c1c;
            padding: 10px;
            margin-bottom: 15px;
        }

    </style>

</head>

<body>

<div class="header">

    <b>SISTEM INFORMASI PELANGGARAN SISWA</b>

    <a href="pelanggaran.php">
        Kembali
    </a>

</div>


<div class="container">

    <div class="box">

        <h2>Tambah Pelanggaran</h2>


        <?php if ($error != "") { ?>

            <div class="error">
                <?= htmlspecialchars($error); ?>
            </div>

        <?php } ?>


        <form method="POST">


            <!-- SISWA -->

            <div class="form-group">

                <label>
                    Siswa
                </label>

                <select name="siswa_id" required>

                    <option value="">
                        -- Pilih Siswa --
                    </option>

                    <?php while ($data = mysqli_fetch_assoc($siswa)) { ?>

                        <option value="<?= $data['id']; ?>">

                            <?= htmlspecialchars($data['nis']); ?>
                            -
                            <?= htmlspecialchars($data['nama']); ?>

                        </option>

                    <?php } ?>

                </select>

            </div>


            <!-- JENIS -->

            <div class="form-group">

                <label>
                    Jenis Pelanggaran
                </label>

                <select name="jenis_id" required>

                    <option value="">
                        -- Pilih Jenis Pelanggaran --
                    </option>

                    <?php while ($data = mysqli_fetch_assoc($jenis)) { ?>

                        <option value="<?= $data['id']; ?>">

                            <?= htmlspecialchars($data['nama']); ?>

                            -
                            <?= $data['poin']; ?> poin

                        </option>

                    <?php } ?>

                </select>

            </div>


            <!-- GURU -->

            <div class="form-group">

                <label>
                    Guru
                </label>

                <select name="guru_id">

                    <option value="">
                        -- Pilih Guru --
                    </option>

                    <?php while ($data = mysqli_fetch_assoc($guru)) { ?>

                        <option value="<?= $data['id']; ?>">

                            <?= htmlspecialchars($data['nama']); ?>

                        </option>

                    <?php } ?>

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


            <!-- KETERANGAN -->

            <div class="form-group">

                <label>
                    Keterangan
                </label>

                <textarea
                    name="keterangan"
                    placeholder="Masukkan keterangan pelanggaran..."
                ></textarea>

            </div>


            <!-- STATUS -->

            <div class="form-group">

                <label>
                    Status
                </label>

                <select name="status" required>

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


            <button
                type="submit"
                name="simpan"
                class="btn"
            >
                Simpan
            </button>


            <a
                href="pelanggaran.php"
                class="batal"
            >
                Batal
            </a>


        </form>

    </div>

</div>

</body>

</html>