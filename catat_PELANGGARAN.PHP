
<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "database.php";
require_once "auth.php";

/*
|--------------------------------------------------------------------------
| AKSES HALAMAN
|--------------------------------------------------------------------------
| Admin, Guru, dan Wali Kelas boleh mencatat pelanggaran.
*/

hanyaRole(['admin', 'guru', 'wali_kelas']);


/*
|--------------------------------------------------------------------------
| CSRF
|--------------------------------------------------------------------------
*/

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

$pesan = '';


/*
|--------------------------------------------------------------------------
| SIMPAN PELANGGARAN
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $token = $_POST['csrf'] ?? '';

    if (
        !isset($_SESSION['csrf']) ||
        !hash_equals($_SESSION['csrf'], $token)
    ) {
        die("Token formulir tidak valid. Silakan muat ulang halaman.");
    }


    $siswa_id = filter_input(
        INPUT_POST,
        'siswa_id',
        FILTER_VALIDATE_INT
    );

    $jenis_id = filter_input(
        INPUT_POST,
        'jenis_id',
        FILTER_VALIDATE_INT
    );

    $tanggal = $_POST['tanggal'] ?? '';

    $keterangan = trim(
        $_POST['keterangan'] ?? ''
    );


    /*
    |--------------------------------------------------------------------------
    | VALIDASI
    |--------------------------------------------------------------------------
    */

    $tanggal_obj = DateTime::createFromFormat(
        '!Y-m-d',
        $tanggal
    );


    if (
        !$siswa_id ||
        !$jenis_id ||
        !$tanggal_obj ||
        $tanggal_obj->format('Y-m-d') !== $tanggal
    ) {

        $pesan = "Periksa kembali data yang dimasukkan.";

    } else {


        /*
        |--------------------------------------------------------------------------
        | CEK SISWA
        |--------------------------------------------------------------------------
        */

        $stmt = $koneksi->prepare(
            "SELECT id
             FROM siswa
             WHERE id = ?
             AND aktif = 1"
        );

        $stmt->bind_param(
            "i",
            $siswa_id
        );

        $stmt->execute();

        $siswa_valid =
            $stmt->get_result()->num_rows > 0;


        /*
        |--------------------------------------------------------------------------
        | CEK JENIS PELANGGARAN
        |--------------------------------------------------------------------------
        */

        $stmt = $koneksi->prepare(
            "SELECT id
             FROM jenis_pelanggaran
             WHERE id = ?"
        );

        $stmt->bind_param(
            "i",
            $jenis_id
        );

        $stmt->execute();

        $jenis_valid =
            $stmt->get_result()->num_rows > 0;


        if (
            !$siswa_valid ||
            !$jenis_valid
        ) {

            $pesan =
                "Siswa atau jenis pelanggaran tidak valid.";

        } else {


            /*
            |--------------------------------------------------------------------------
            | CARI ID USER YANG LOGIN
            |--------------------------------------------------------------------------
            */

            $uid = (int)(
                $_SESSION['user_id'] ?? 0
            );

            $guru_id = null;


            /*
            |--------------------------------------------------------------------------
            | JIKA USER ADALAH GURU
            |--------------------------------------------------------------------------
            */

            if ($uid > 0) {

                $stmt = $koneksi->prepare(
                    "SELECT id
                     FROM guru
                     WHERE user_id = ?"
                );

                $stmt->bind_param(
                    "i",
                    $uid
                );

                $stmt->execute();

                $guru =
                    $stmt->get_result()->fetch_assoc();


                if ($guru) {

                    $guru_id =
                        (int)$guru['id'];
                }
            }


            /*
            |--------------------------------------------------------------------------
            | SIMPAN DATA
            |--------------------------------------------------------------------------
            */

            $stmt = $koneksi->prepare(
                "INSERT INTO pelanggaran
                (
                    siswa_id,
                    jenis_id,
                    guru_id,
                    tanggal,
                    keterangan
                )
                VALUES (?, ?, ?, ?, ?)"
            );


            $stmt->bind_param(
                "iiiss",
                $siswa_id,
                $jenis_id,
                $guru_id,
                $tanggal,
                $keterangan
            );


            if ($stmt->execute()) {

                header(
                    "Location: pelanggaran.php"
                );

                exit;

            } else {

                $pesan =
                    "Gagal menyimpan pelanggaran: " .
                    $stmt->error;
            }
        }
    }
}


/*
|--------------------------------------------------------------------------
| DATA SISWA
|--------------------------------------------------------------------------
*/

$siswa = mysqli_query(
    $koneksi,
    "SELECT id, nis, nama
     FROM siswa
     WHERE aktif = 1
     ORDER BY nama ASC"
);


if (!$siswa) {

    die(
        "Query siswa error: " .
        mysqli_error($koneksi)
    );
}


/*
|--------------------------------------------------------------------------
| DATA JENIS PELANGGARAN
|--------------------------------------------------------------------------
*/

$jenis = mysqli_query(
    $koneksi,
    "SELECT id, nama, poin
     FROM jenis_pelanggaran
     ORDER BY nama ASC"
);


if (!$jenis) {

    die(
        "Query jenis pelanggaran error: " .
        mysqli_error($koneksi)
    );
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

    <title>Catat Pelanggaran</title>


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
            background: #687280;
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
            background: #56606d;
            color: white;
            text-decoration: none;

            padding: 9px 14px;

            border-radius: 5px;

            font-size: 14px;
        }


        .kembali:hover {
            background: #454e59;
        }


        .container {
            width: 90%;
            max-width: 800px;

            margin: 30px auto;
        }


        .box {
            background: white;

            border: 1px solid #ddd;

            border-radius: 7px;

            padding: 25px;
        }


        .box h1 {
            margin-top: 0;

            font-size: 23px;
        }


        .deskripsi {
            color: #777;

            margin-bottom: 25px;
        }


        .pesan {
            background: #fff3cd;

            border: 1px solid #ffe69c;

            color: #664d03;

            padding: 12px;

            border-radius: 5px;

            margin-bottom: 20px;
        }


        .form-group {
            margin-bottom: 18px;
        }


        label {
            display: block;

            font-weight: bold;

            margin-bottom: 7px;
        }


        select,
        input,
        textarea {

            width: 100%;

            padding: 11px;

            border: 1px solid #ccc;

            border-radius: 5px;

            font-family: Arial, sans-serif;

            font-size: 14px;
        }


        textarea {
            resize: vertical;
        }


        .btn {

            background: #687280;

            color: white;

            border: none;

            padding: 11px 18px;

            border-radius: 5px;

            cursor: pointer;

            font-size: 14px;
        }


        .btn:hover {
            background: #56606d;
        }

    </style>

</head>


<body>


    <div class="header">

        <h2>
            SISTEM INFORMASI PELANGGARAN SISWA
        </h2>


        <a
            href="dashboard.php"
            class="kembali"
        >
            ← Kembali
        </a>

    </div>


    <div class="container">

        <div class="box">

            <h1>
                Form Pencatatan Pelanggaran
            </h1>


            <p class="deskripsi">
                Silakan isi data pelanggaran siswa.
            </p>


            <?php if ($pesan != '') { ?>

                <div class="pesan">

                    <?= htmlspecialchars($pesan); ?>

                </div>

            <?php } ?>


            <form method="POST">


                <input
                    type="hidden"
                    name="csrf"
                    value="<?= htmlspecialchars($_SESSION['csrf']); ?>"
                >


                <div class="form-group">

                    <label>
                        Nama Siswa
                    </label>


                    <select
                        name="siswa_id"
                        required
                    >

                        <option value="">
                            -- Pilih Siswa --
                        </option>


                        <?php while (
                            $s = mysqli_fetch_assoc($siswa)
                        ) { ?>

                            <option
                                value="<?= (int)$s['id']; ?>"
                            >

                                <?= htmlspecialchars(
                                    $s['nis']
                                ); ?>

                                -

                                <?= htmlspecialchars(
                                    $s['nama']
                                ); ?>

                            </option>

                        <?php } ?>

                    </select>

                </div>


                <div class="form-group">

                    <label>
                        Jenis Pelanggaran
                    </label>


                    <select
                        name="jenis_id"
                        required
                    >

                        <option value="">
                            -- Pilih Pelanggaran --
                        </option>


                        <?php while (
                            $j = mysqli_fetch_assoc($jenis)
                        ) { ?>

                            <option
                                value="<?= (int)$j['id']; ?>"
                            >

                                <?= htmlspecialchars(
                                    $j['nama']
                                ); ?>

                                -

                                <?= (int)$j['poin']; ?>

                                poin

                            </option>

                        <?php } ?>

                    </select>

                </div>


                <div class="form-group">

                    <label>
                        Tanggal Kejadian
                    </label>


                    <input
                        type="date"
                        name="tanggal"
                        value="<?= date('Y-m-d'); ?>"
                        required
                    >

                </div>


                <div class="form-group">

                    <label>
                        Keterangan
                    </label>


                    <textarea
                        name="keterangan"
                        rows="4"
                        placeholder="Jelaskan kejadian pelanggaran"
                    ></textarea>

                </div>


                <button
                    type="submit"
                    class="btn"
                >

                    Simpan Pelanggaran

                </button>


            </form>

        </div>

    </div>


</body>

</html>
```