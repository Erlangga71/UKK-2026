
<?php

require_once "cek_role.php";
wajib_role(['admin']);

require_once "database.php";

$error = "";

$nip = "";
$nama = "";
$email = "";
$no_hp = "";

if (isset($_POST['simpan'])) {

    $nip = trim($_POST['nip'] ?? '');
    $nama = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $no_hp = trim($_POST['no_hp'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($nama === "" || $email === "" || $password === "") {

        $error = "Nama, email, dan password wajib diisi.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "Format email tidak valid.";

    } elseif (strlen($password) < 6) {

        $error = "Password minimal 6 karakter.";

    } else {

        try {

            mysqli_begin_transaction($koneksi);

            // CEK EMAIL SUDAH DIGUNAKAN ATAU BELUM
            $cek_email = mysqli_prepare($koneksi, "
                SELECT id FROM guru WHERE email = ?
                UNION
                SELECT id FROM users WHERE email = ?
            ");

            mysqli_stmt_bind_param(
                $cek_email,
                "ss",
                $email,
                $email
            );

            mysqli_stmt_execute($cek_email);
            $hasil_email = mysqli_stmt_get_result($cek_email);

            if (mysqli_num_rows($hasil_email) > 0) {

                throw new Exception("Email sudah digunakan.");

            }

            // CEK NIP JIKA DIISI
            if ($nip !== "") {

                $cek_nip = mysqli_prepare($koneksi, "
                    SELECT id FROM guru WHERE nip = ?
                ");

                mysqli_stmt_bind_param($cek_nip, "s", $nip);
                mysqli_stmt_execute($cek_nip);

                $hasil_nip = mysqli_stmt_get_result($cek_nip);

                if (mysqli_num_rows($hasil_nip) > 0) {
                    throw new Exception("NIP sudah digunakan.");
                }

            }

            // SIMPAN DATA GURU
            $query_guru = mysqli_prepare($koneksi, "
                INSERT INTO guru (nip, nama, email, no_hp)
                VALUES (?, ?, ?, ?)
            ");

            $nip_db = ($nip === "") ? null : $nip;

            mysqli_stmt_bind_param(
                $query_guru,
                "ssss",
                $nip_db,
                $nama,
                $email,
                $no_hp
            );

            if (!mysqli_stmt_execute($query_guru)) {
                throw new Exception("Gagal menyimpan data guru.");
            }

            $guru_id = mysqli_insert_id($koneksi);

            // ENKRIPSI PASSWORD
            $password_hash = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            // BUAT AKUN LOGIN GURU OTOMATIS
            // Username menggunakan email guru.
            $query_user = mysqli_prepare($koneksi, "
                INSERT INTO users
                (guru_id, nama, username, password, role, aktif, email)
                VALUES (?, ?, ?, ?, 'guru', 1, ?)
            ");

            mysqli_stmt_bind_param(
                $query_user,
                "issss",
                $guru_id,
                $nama,
                $email,
                $password_hash,
                $email
            );

            if (!mysqli_stmt_execute($query_user)) {
                throw new Exception("Gagal membuat akun login guru.");
            }

            $user_id = mysqli_insert_id($koneksi);

            // HUBUNGKAN AKUN DENGAN DATA GURU
            $query_update = mysqli_prepare($koneksi, "
                UPDATE guru SET user_id = ? WHERE id = ?
            ");

            mysqli_stmt_bind_param(
                $query_update,
                "ii",
                $user_id,
                $guru_id
            );

            if (!mysqli_stmt_execute($query_update)) {
                throw new Exception("Gagal menghubungkan akun guru.");
            }

            mysqli_commit($koneksi);

            header("Location: guru.php");
            exit;

        } catch (Throwable $e) {

            mysqli_rollback($koneksi);
            $error = $e->getMessage();

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
            box-sizing: border-box;
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

                <label for="nip">NIP</label>

                <input
                    type="text"
                    id="nip"
                    name="nip"
                    placeholder="Masukkan NIP"
                    value="<?= htmlspecialchars($nip); ?>"
                >

            </div>

            <div class="form-group">

                <label for="nama">Nama Guru</label>

                <input
                    type="text"
                    id="nama"
                    name="nama"
                    placeholder="Masukkan nama guru"
                    value="<?= htmlspecialchars($nama); ?>"
                    required
                >

            </div>

            <div class="form-group">

                <label for="email">Email</label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    placeholder="Masukkan email"
                    value="<?= htmlspecialchars($email); ?>"
                    required
                >

            </div>

            <div class="form-group">

                <label for="password">Password Login</label>

                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Buat password untuk guru"
                    minlength="6"
                    required
                >

            </div>

            <div class="form-group">

                <label for="no_hp">No. HP</label>

                <input
                    type="text"
                    id="no_hp"
                    name="no_hp"
                    placeholder="Masukkan nomor HP"
                    value="<?= htmlspecialchars($no_hp); ?>"
                >

            </div>

            <button
                type="submit"
                name="simpan"
                class="btn"
            >
                Simpan
            </button>

            <a href="guru.php" class="batal">
                Batal
            </a>

        </form>

    </div>

</div>

</body>

</html>