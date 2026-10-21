```php
<?php

session_start();

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

include "database.php";

$role = $_SESSION['role'] ?? '';

if ($role != 'admin') {
    echo "Akses ditolak.";
    exit;
}


/* =========================
   AMBIL ID
========================= */

$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    header("Location: kelola_wali_kelas.php");
    exit;
}


/* =========================
   UPDATE
========================= */

if (isset($_POST['update'])) {

    $guru_id = intval($_POST['guru_id'] ?? 0);
    $kelas_id = intval($_POST['kelas_id'] ?? 0);
    $tahun_ajaran_id = intval(
        $_POST['tahun_ajaran_id'] ?? 0
    );


    if (
        $guru_id <= 0 ||
        $kelas_id <= 0 ||
        $tahun_ajaran_id <= 0
    ) {

        $error = "Semua data harus dipilih.";

    } else {


        /* CEK DUPLIKAT */

        $cek = mysqli_query(
            $koneksi,
            "SELECT id
             FROM wali_kelas
             WHERE guru_id = $guru_id
             AND kelas_id = $kelas_id
             AND tahun_ajaran_id = $tahun_ajaran_id
             AND id != $id
             LIMIT 1"
        );


        if (mysqli_num_rows($cek) > 0) {

            $error = "Kombinasi guru, kelas dan tahun ajaran tersebut sudah ada.";

        } else {


            /* UPDATE */

            $update = mysqli_query(
                $koneksi,
                "UPDATE wali_kelas SET
                    guru_id = $guru_id,
                    kelas_id = $kelas_id,
                    tahun_ajaran_id = $tahun_ajaran_id
                 WHERE id = $id"
            );


            if ($update) {

                header(
                    "Location: kelola_wali_kelas.php"
                );

                exit;

            } else {

                $error = "Gagal mengubah data: "
                       . mysqli_error($koneksi);
            }
        }
    }
}


/* =========================
   AMBIL DATA WALI KELAS
========================= */

$query = mysqli_query(
    $koneksi,
    "SELECT *
     FROM wali_kelas
     WHERE id = $id
     LIMIT 1"
);


if (
    !$query ||
    mysqli_num_rows($query) == 0
) {

    echo "Data wali kelas tidak ditemukan.";
    exit;
}


$wali = mysqli_fetch_assoc($query);


/* =========================
   DATA GURU
========================= */

$query_guru = mysqli_query(
    $koneksi,
    "SELECT
        id,
        nama
     FROM guru
     ORDER BY nama ASC"
);


/* =========================
   DATA KELAS
========================= */

$query_kelas = mysqli_query(
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

$query_tahun = mysqli_query(
    $koneksi,
    "SELECT
        id,
        nama
     FROM tahun_ajaran
     WHERE aktif = 1
     ORDER BY id DESC"
);

?>

<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Edit Wali Kelas</title>

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

.keterangan {
    color: #777;
    margin-bottom: 25px;
}

.form-group {
    margin-bottom: 18px;
}

label {
    display: block;
    margin-bottom: 7px;
    font-size: 14px;
    font-weight: bold;
}

select {
    width: 100%;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
    background: white;
}

button,
.kembali {
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
    border-radius: 5px;
    margin-bottom: 18px;
}

</style>

</head>

<body>

<div class="content">

    <div class="box">

        <h1>
            Edit Data Wali Kelas
        </h1>

        <p class="keterangan">
            Ubah guru, kelas, atau tahun ajaran.
        </p>


        <?php if (isset($error)) { ?>

            <div class="error">
                <?= htmlspecialchars($error); ?>
            </div>

        <?php } ?>


        <form method="POST">


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

                    while (
                        $guru = mysqli_fetch_assoc(
                            $query_guru
                        )
                    ) {

                    ?>

                        <option
                            value="<?= $guru['id']; ?>"
                            <?= (
                                $guru['id']
                                == $wali['guru_id']
                            )
                                ? 'selected'
                                : ''; ?>
                        >

                            <?= htmlspecialchars(
                                $guru['nama']
                            ); ?>

                        </option>

                    <?php } ?>

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

                    while (
                        $kelas = mysqli_fetch_assoc(
                            $query_kelas
                        )
                    ) {

                    ?>

                        <option
                            value="<?= $kelas['id']; ?>"
                            <?= (
                                $kelas['id']
                                == $wali['kelas_id']
                            )
                                ? 'selected'
                                : ''; ?>
                        >

                            <?= htmlspecialchars(
                                $kelas['nama_kelas']
                            ); ?>

                        </option>

                    <?php } ?>

                </select>

            </div>


            <!-- TAHUN AJARAN -->

            <div class="form-group">

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

                    while (
                        $tahun = mysqli_fetch_assoc(
                            $query_tahun
                        )
                    ) {

                    ?>

                        <option
                            value="<?= $tahun['id']; ?>"
                            <?= (
                                $tahun['id']
                                == $wali['tahun_ajaran_id']
                            )
                                ? 'selected'
                                : ''; ?>
                        >

                            <?= htmlspecialchars(
                                $tahun['nama']
                            ); ?>

                        </option>

                    <?php } ?>

                </select>

            </div>


            <button
                type="submit"
                name="update"
            >
                Simpan Perubahan
            </button>


            <a
                href="kelola_wali_kelas.php"
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