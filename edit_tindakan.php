<?php

require_once "cek_role.php";

wajib_role(['admin', 'guru']);

include "database.php";


// =========================
// CEK ID
// =========================

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {

    header("Location: tindakan.php");
    exit;

}

$id = (int) $_GET['id'];


// =========================
// AMBIL DATA TINDAKAN
// =========================

$query_data = mysqli_query($koneksi, "

    SELECT *
    FROM tindakan
    WHERE id = $id

");

$data = mysqli_fetch_assoc($query_data);


if (!$data) {

    die("Data tindakan tidak ditemukan.");

}


// =========================
// PROSES UPDATE
// =========================

if (isset($_POST['simpan'])) {

    $pelanggaran_id = (int) $_POST['pelanggaran_id'];
    $tanggal        = $_POST['tanggal'];
    $tindakan       = $_POST['tindakan'];
    $petugas_id     = !empty($_POST['petugas_id'])
                        ? (int) $_POST['petugas_id']
                        : NULL;

    $catatan = $_POST['catatan'];


    $stmt = mysqli_prepare($koneksi, "

        UPDATE tindakan

        SET
            pelanggaran_id = ?,
            tanggal = ?,
            tindakan = ?,
            petugas_id = ?,
            catatan = ?

        WHERE id = ?

    ");


    mysqli_stmt_bind_param(
        $stmt,
        "issisi",
        $pelanggaran_id,
        $tanggal,
        $tindakan,
        $petugas_id,
        $catatan,
        $id
    );


    if (mysqli_stmt_execute($stmt)) {

        header("Location: tindakan.php");

        exit;

    } else {

        $error = mysqli_stmt_error($stmt);

    }

}


// =========================
// DATA PELANGGARAN
// =========================

$query_pelanggaran = mysqli_query($koneksi, "

    SELECT
        pelanggaran.id,
        siswa.nama AS nama_siswa,
        jenis_pelanggaran.nama AS jenis_pelanggaran

    FROM pelanggaran

    JOIN siswa
        ON pelanggaran.siswa_id = siswa.id

    JOIN jenis_pelanggaran
        ON pelanggaran.jenis_id = jenis_pelanggaran.id

    ORDER BY pelanggaran.id DESC

");


// =========================
// DATA PETUGAS
// =========================

$query_petugas = mysqli_query($koneksi, "

    SELECT
        id,
        nama,
        username

    FROM users

    WHERE aktif = 1

    ORDER BY nama ASC

");

?>


<!DOCTYPE html>

<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Edit Tindakan</title>


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
           HEADER
        ========================= */

        .header {

            background: #687280;

            color: white;

            padding: 18px 30px;

            display: flex;

            justify-content: space-between;

            align-items: center;

        }


        .header b {

            font-size: 18px;

        }


        .header a {

            color: white;

            text-decoration: none;

            background: #56606d;

            padding: 8px 14px;

            border-radius: 4px;

            font-size: 13px;

        }


        .header a:hover {

            background: #454e59;

        }


        /* =========================
           CONTAINER
        ========================= */

        .container {

            width: 95%;

            max-width: 900px;

            margin: 30px auto;

        }


        /* =========================
           BOX
        ========================= */

        .box {

            background: white;

            border: 1px solid #ddd;

            border-radius: 6px;

            padding: 25px;

        }


        .box h2 {

            margin-top: 0;

            margin-bottom: 25px;

            font-size: 22px;

        }


        /* =========================
           FORM
        ========================= */

        .form-group {

            margin-bottom: 18px;

        }


        .form-group label {

            display: block;

            margin-bottom: 7px;

            font-weight: bold;

            font-size: 14px;

        }


        input,
        select,
        textarea {

            width: 100%;

            padding: 10px;

            border: 1px solid #ccc;

            border-radius: 4px;

            font-family: Arial, sans-serif;

            font-size: 14px;

            background: white;

        }


        input:focus,
        select:focus,
        textarea:focus {

            outline: none;

            border-color: #687280;

        }


        textarea {

            min-height: 100px;

            resize: vertical;

        }


        /* =========================
           BUTTON
        ========================= */

        .form-button {

            margin-top: 10px;

        }


        .btn-simpan {

            border: none;

            background: #687280;

            color: white;

            padding: 10px 18px;

            border-radius: 4px;

            cursor: pointer;

            font-size: 14px;

        }


        .btn-simpan:hover {

            background: #56606d;

        }


        .btn-batal {

            display: inline-block;

            margin-left: 8px;

            background: #ddd;

            color: #333;

            text-decoration: none;

            padding: 9px 16px;

            border-radius: 4px;

            font-size: 14px;

        }


        .btn-batal:hover {

            background: #ccc;

        }


        /* =========================
           ERROR
        ========================= */

        .error {

            background: #f8d7da;

            color: #842029;

            border: 1px solid #f1aeb5;

            padding: 10px;

            margin-bottom: 20px;

            border-radius: 4px;

        }


        /* =========================
           RESPONSIVE
        ========================= */

        @media (max-width: 700px) {

            .header {

                padding: 15px;

            }


            .header b {

                font-size: 14px;

            }


            .container {

                width: 96%;

                margin: 20px auto;

            }


            .box {

                padding: 18px;

            }

        }

    </style>

</head>


<body>


<!-- =========================
     HEADER
========================= -->

<div class="header">

    <b>
        SISTEM INFORMASI PELANGGARAN SISWA
    </b>


    <a href="tindakan.php">
        ← Kembali
    </a>

</div>



<!-- =========================
     CONTENT
========================= -->

<div class="container">


    <div class="box">


        <h2>
            Edit Tindakan
        </h2>


        <?php if (isset($error)) { ?>

            <div class="error">

                Gagal menyimpan perubahan:
                <?= htmlspecialchars($error); ?>

            </div>

        <?php } ?>


        <form method="POST">


            <!-- PELANGGARAN -->

            <div class="form-group">

                <label>
                    Pelanggaran Siswa
                </label>


                <select
                    name="pelanggaran_id"
                    required
                >


                    <?php

                    while (
                        $pel = mysqli_fetch_assoc(
                            $query_pelanggaran
                        )
                    ) {

                    ?>


                        <option
                            value="<?= $pel['id']; ?>"

                            <?= (
                                $pel['id'] == $data['pelanggaran_id']
                            )
                            ? 'selected'
                            : ''
                            ?>
                        >

                            <?= htmlspecialchars(
                                $pel['nama_siswa']
                            ); ?>

                            -

                            <?= htmlspecialchars(
                                $pel['jenis_pelanggaran']
                            ); ?>

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
                    value="<?= htmlspecialchars(
                        $data['tanggal']
                    ); ?>"
                    required
                >

            </div>



            <!-- TINDAKAN -->

            <div class="form-group">

                <label>
                    Tindakan
                </label>


                <input
                    type="text"
                    name="tindakan"
                    value="<?= htmlspecialchars(
                        $data['tindakan']
                    ); ?>"
                    placeholder="Masukkan tindakan"
                    required
                >

            </div>



            <!-- PETUGAS -->

            <div class="form-group">

                <label>
                    Petugas / Guru
                </label>


                <select
                    name="petugas_id"
                >

                    <option value="">
                        -- Pilih Petugas --
                    </option>


                    <?php

                    while (
                        $petugas = mysqli_fetch_assoc(
                            $query_petugas
                        )
                    ) {

                    ?>


                        <option
                            value="<?= $petugas['id']; ?>"

                            <?= (
                                $petugas['id']
                                == $data['petugas_id']
                            )
                            ? 'selected'
                            : ''
                            ?>
                        >

                            <?= htmlspecialchars(
                                $petugas['nama']
                            ); ?>

                            -

                            <?= htmlspecialchars(
                                $petugas['username']
                            ); ?>

                        </option>


                    <?php } ?>


                </select>

            </div>



            <!-- CATATAN -->

            <div class="form-group">

                <label>
                    Catatan
                </label>


                <textarea
                    name="catatan"
                    placeholder="Masukkan catatan jika diperlukan"
                ><?= htmlspecialchars(
                    $data['catatan'] ?? ''
                ); ?></textarea>

            </div>



            <!-- BUTTON -->

            <div class="form-button">

                <button
                    type="submit"
                    name="simpan"
                    class="btn-simpan"
                >
                    Simpan Perubahan
                </button>


                <a
                    href="tindakan.php"
                    class="btn-batal"
                >
                    Batal
                </a>

            </div>


        </form>


    </div>


</div>


</body>

</html>