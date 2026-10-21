
<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "database.php";
require_once "auth.php";

/*
|--------------------------------------------------------------------------
| CEK AKSES
|--------------------------------------------------------------------------
| Admin, Guru, dan Wali Kelas boleh menambah tindakan.
*/

hanyaRole(['admin', 'guru', 'wali_kelas']);


/*
|--------------------------------------------------------------------------
| PROSES SIMPAN
|--------------------------------------------------------------------------
*/

$pesan = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $pelanggaran_id = filter_input(
        INPUT_POST,
        'pelanggaran_id',
        FILTER_VALIDATE_INT
    );

    $tanggal = $_POST['tanggal'] ?? '';
    $tindakan = trim($_POST['tindakan'] ?? '');
    $petugas_id = filter_input(
        INPUT_POST,
        'petugas_id',
        FILTER_VALIDATE_INT
    );

    $catatan = trim($_POST['catatan'] ?? '');


    /*
    |--------------------------------------------------------------------------
    | VALIDASI
    |--------------------------------------------------------------------------
    */

    if (
        !$pelanggaran_id ||
        empty($tanggal) ||
        empty($tindakan)
    ) {

        $pesan = "Data wajib diisi dengan lengkap.";

    } else {

        /*
        |--------------------------------------------------------------------------
        | CEK PELANGGARAN
        |--------------------------------------------------------------------------
        */

        $stmt = $koneksi->prepare(
            "SELECT id
             FROM pelanggaran
             WHERE id = ?"
        );

        $stmt->bind_param(
            "i",
            $pelanggaran_id
        );

        $stmt->execute();

        $cek = $stmt->get_result();

        if ($cek->num_rows == 0) {

            $pesan = "Data pelanggaran tidak ditemukan.";

        } else {

            /*
            |--------------------------------------------------------------------------
            | PETUGAS
            |--------------------------------------------------------------------------
            */

            if (!$petugas_id) {
                $petugas_id = null;
            }

            /*
            |--------------------------------------------------------------------------
            | SIMPAN TINDAKAN
            |--------------------------------------------------------------------------
            */

            $stmt = $koneksi->prepare(
                "INSERT INTO tindakan
                (
                    pelanggaran_id,
                    tanggal,
                    tindakan,
                    petugas_id,
                    catatan
                )
                VALUES (?, ?, ?, ?, ?)"
            );

            $stmt->bind_param(
                "issis",
                $pelanggaran_id,
                $tanggal,
                $tindakan,
                $petugas_id,
                $catatan
            );

            if ($stmt->execute()) {

                header("Location: tindakan.php");
                exit;

            } else {

                $pesan = "Gagal menyimpan tindakan: " . $stmt->error;
            }
        }
    }
}


/*
|--------------------------------------------------------------------------
| DATA PELANGGARAN
|--------------------------------------------------------------------------
*/

$pelanggaran = mysqli_query(
    $koneksi,
    "
    SELECT
        p.id,
        p.tanggal,
        s.nama AS nama_siswa,
        s.nis,
        jp.nama AS nama_pelanggaran
    FROM pelanggaran p

    INNER JOIN siswa s
        ON p.siswa_id = s.id

    INNER JOIN jenis_pelanggaran jp
        ON p.jenis_id = jp.id

    ORDER BY p.tanggal DESC
    "
);

if (!$pelanggaran) {

    die(
        "Query pelanggaran error: " .
        mysqli_error($koneksi)
    );
}


/*
|--------------------------------------------------------------------------
| DATA PETUGAS
|--------------------------------------------------------------------------
| Pakai email, bukan username.
|--------------------------------------------------------------------------
*/

$petugas = mysqli_query(
    $koneksi,
    "
    SELECT
        id,
        nama,
        email,
        role
    FROM users
    WHERE aktif = 1
    ORDER BY nama ASC
    "
);

if (!$petugas) {

    die(
        "Query petugas error: " .
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

<title>Tambah Tindakan</title>

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
        href="tindakan.php"
        class="kembali"
    >
        ← Kembali
    </a>

</div>


<div class="container">

<div class="box">

<h1>
    Tambah Tindakan
</h1>

<?php if ($pesan != ''): ?>

    <div class="pesan">
        <?= htmlspecialchars($pesan); ?>
    </div>

<?php endif; ?>


<form method="POST">


<div class="form-group">

<label>
    Pelanggaran
</label>

<select
    name="pelanggaran_id"
    required
>

<option value="">
    -- Pilih Pelanggaran --
</option>

<?php while ($p = mysqli_fetch_assoc($pelanggaran)): ?>

<option value="<?= (int)$p['id']; ?>">

<?= htmlspecialchars($p['nis']); ?>
 -
<?= htmlspecialchars($p['nama_siswa']); ?>

 -
<?= htmlspecialchars($p['nama_pelanggaran']); ?>

 -
<?= htmlspecialchars($p['tanggal']); ?>

</option>

<?php endwhile; ?>

</select>

</div>


<div class="form-group">

<label>
    Tanggal Tindakan
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
    Tindakan
</label>

<textarea
    name="tindakan"
    rows="4"
    placeholder="Contoh: Teguran, pembinaan, pemanggilan orang tua, dll."
    required
></textarea>

</div>


<div class="form-group">

<label>
    Petugas
</label>

<select
    name="petugas_id"
>

<option value="">
    -- Pilih Petugas --
</option>

<?php while ($u = mysqli_fetch_assoc($petugas)): ?>

<option value="<?= (int)$u['id']; ?>">

<?= htmlspecialchars($u['nama']); ?>
 -
<?= htmlspecialchars($u['email']); ?>
 -
<?= htmlspecialchars($u['role']); ?>

</option>

<?php endwhile; ?>

</select>

</div>


<div class="form-group">

<label>
    Catatan
</label>

<textarea
    name="catatan"
    rows="4"
    placeholder="Catatan tambahan"
></textarea>

</div>


<button
    type="submit"
    class="btn"
>
    Simpan Tindakan
</button>


</form>

</div>

</div>

</body>

</html>
```