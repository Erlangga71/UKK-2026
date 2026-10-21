
<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "database.php";
require_once "auth.php";

/*
|--------------------------------------------------------------------------
| CEK LOGIN & ROLE
|--------------------------------------------------------------------------
*/

hanyaRole(['admin']);


/*
|--------------------------------------------------------------------------
| AMBIL ID GURU
|--------------------------------------------------------------------------
*/

$id = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);

if (!$id) {
    die("ID guru tidak valid.");
}


/*
|--------------------------------------------------------------------------
| AMBIL DATA GURU
|--------------------------------------------------------------------------
*/

$query = mysqli_query(
    $koneksi,
    "
    SELECT
        g.id,
        g.nama,
        g.nip,
        g.user_id,
        u.email,
        u.role,
        u.aktif
    FROM guru g

    LEFT JOIN users u
        ON g.user_id = u.id

    WHERE g.id = $id
    LIMIT 1
    "
);

if (!$query) {
    die(
        "Query guru error: " .
        mysqli_error($koneksi)
    );
}

$guru = mysqli_fetch_assoc($query);

if (!$guru) {
    die("Data guru tidak ditemukan.");
}


/*
|--------------------------------------------------------------------------
| PROSES UPDATE
|--------------------------------------------------------------------------
*/

$pesan = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nama = trim($_POST['nama'] ?? '');
    $nip = trim($_POST['nip'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role = $_POST['role'] ?? 'guru';
    $aktif = isset($_POST['aktif']) ? 1 : 0;
    $password = $_POST['password'] ?? '';


    /*
    |--------------------------------------------------------------------------
    | VALIDASI
    |--------------------------------------------------------------------------
    */

    if (
        empty($nama) ||
        empty($nip) ||
        empty($email)
    ) {

        $pesan = "Nama, NIP, dan email wajib diisi.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $pesan = "Format email tidak valid.";

    } elseif (!in_array($role, ['guru', 'wali_kelas'])) {

        $pesan = "Role tidak valid.";

    } else {

        /*
        |--------------------------------------------------------------------------
        | CEK EMAIL
        |--------------------------------------------------------------------------
        */

        $email_safe = mysqli_real_escape_string(
            $koneksi,
            $email
        );

        $cek_email = mysqli_query(
            $koneksi,
            "
            SELECT id
            FROM users
            WHERE email = '$email_safe'
            AND id != " . (int)$guru['user_id'] . "
            LIMIT 1
            "
        );

        if (!$cek_email) {

            $pesan =
                "Gagal mengecek email: " .
                mysqli_error($koneksi);

        } elseif (mysqli_num_rows($cek_email) > 0) {

            $pesan = "Email tersebut sudah digunakan.";

        } else {

            /*
            |--------------------------------------------------------------------------
            | UPDATE TABEL GURU
            |--------------------------------------------------------------------------
            */

            $stmt = $koneksi->prepare(
                "
                UPDATE guru
                SET
                    nama = ?,
                    nip = ?
                WHERE id = ?
                "
            );

            $stmt->bind_param(
                "ssi",
                $nama,
                $nip,
                $id
            );

            if (!$stmt->execute()) {

                $pesan =
                    "Gagal update data guru: " .
                    $stmt->error;

            } else {

                /*
                |--------------------------------------------------------------------------
                | UPDATE USERS
                |--------------------------------------------------------------------------
                */

                $user_id = (int)$guru['user_id'];

                if (!empty($password)) {

                    $password_hash = password_hash(
                        $password,
                        PASSWORD_DEFAULT
                    );

                    $stmt = $koneksi->prepare(
                        "
                        UPDATE users
                        SET
                            nama = ?,
                            email = ?,
                            password = ?,
                            role = ?,
                            aktif = ?
                        WHERE id = ?
                        "
                    );

                    $stmt->bind_param(
                        "ssssii",
                        $nama,
                        $email,
                        $password_hash,
                        $role,
                        $aktif,
                        $user_id
                    );

                } else {

                    $stmt = $koneksi->prepare(
                        "
                        UPDATE users
                        SET
                            nama = ?,
                            email = ?,
                            role = ?,
                            aktif = ?
                        WHERE id = ?
                        "
                    );

                    $stmt->bind_param(
                        "sssii",
                        $nama,
                        $email,
                        $role,
                        $aktif,
                        $user_id
                    );
                }


                if ($stmt->execute()) {

                    header("Location: guru.php");
                    exit;

                } else {

                    $pesan =
                        "Gagal update akun: " .
                        $stmt->error;
                }
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

<title>Edit Guru</title>

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
    max-width: 700px;
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

.form-group {
    margin-bottom: 18px;
}

label {
    display: block;
    font-weight: bold;
    margin-bottom: 7px;
}

input,
select {
    width: 100%;
    padding: 11px;
    border: 1px solid #ccc;
    border-radius: 5px;
    font-family: Arial, sans-serif;
}

.pesan {
    background: #fff3cd;
    border: 1px solid #ffe69c;
    color: #664d03;
    padding: 12px;
    border-radius: 5px;
    margin-bottom: 20px;
}

.btn {
    background: #687280;
    color: white;
    border: none;
    padding: 11px 18px;
    border-radius: 5px;
    cursor: pointer;
}

</style>

</head>

<body>

<div class="header">

    <h2>
        SISTEM INFORMASI PELANGGARAN SISWA
    </h2>

    <a
        href="guru.php"
        class="kembali"
    >
        ← Kembali
    </a>

</div>


<div class="container">

<div class="box">

<h1>
    Edit Data Guru
</h1>


<?php if ($pesan): ?>

<div class="pesan">
    <?= htmlspecialchars($pesan); ?>
</div>

<?php endif; ?>


<form method="POST">


<div class="form-group">

<label>
    Nama Guru
</label>

<input
    type="text"
    name="nama"
    value="<?= htmlspecialchars($guru['nama']); ?>"
    required
>

</div>


<div class="form-group">

<label>
    NIP
</label>

<input
    type="text"
    name="nip"
    value="<?= htmlspecialchars($guru['nip']); ?>"
    required
>

</div>


<div class="form-group">

<label>
    Email
</label>

<input
    type="email"
    name="email"
    value="<?= htmlspecialchars($guru['email'] ?? ''); ?>"
    required
>

</div>


<div class="form-group">

<label>
    Role
</label>

<select name="role" required>

<option
    value="guru"
    <?= ($guru['role'] === 'guru') ? 'selected' : ''; ?>
>
    Guru
</option>

<option
    value="wali_kelas"
    <?= ($guru['role'] === 'wali_kelas') ? 'selected' : ''; ?>
>
    Wali Kelas
</option>

</select>

</div>


<div class="form-group">

<label>
    Password Baru
</label>

<input
    type="password"
    name="password"
    placeholder="Kosongkan jika tidak ingin mengganti password"
>

</div>


<div class="form-group">

<label>
    Status Akun
</label>

<select name="aktif">

<option
    value="1"
    <?= ((int)$guru['aktif'] === 1) ? 'selected' : ''; ?>
>
    Aktif
</option>

<option
    value="0"
    <?= ((int)$guru['aktif'] === 0) ? 'selected' : ''; ?>
>
    Tidak Aktif
</option>

</select>

</div>


<button
    type="submit"
    class="btn"
>
    Simpan Perubahan
</button>


</form>

</div>

</div>

</body>

</html>
```