
<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "database.php";
require_once "auth.php";

hanyaRole(['admin']);

$error = "";

/* =========================
   AMBIL ID GURU
========================= */

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    die("ID guru tidak valid.");
}

/* =========================
   AMBIL DATA GURU DAN AKUN
========================= */

$stmt = mysqli_prepare(
    $koneksi,
    "SELECT
        g.id,
        g.nama,
        g.nip,
        g.email,
        g.no_hp,
        u.id AS user_id,
        u.email AS email_akun,
        u.role,
        u.aktif
     FROM guru g
     LEFT JOIN users u
        ON u.email = g.email AND u.role = 'guru'
     WHERE g.id = ?
     LIMIT 1"
);

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$guru = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

if (!$guru) {
    die("Data guru tidak ditemukan.");
}

if (empty($guru['user_id'])) {
    die("Akun login guru tidak ditemukan. Periksa kecocokan email pada tabel guru dan users.");
}

/* =========================
   PROSES UPDATE
========================= */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nama = trim($_POST['nama'] ?? '');
    $nip = trim($_POST['nip'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $no_hp = trim($_POST['no_hp'] ?? '');
    $password = $_POST['password'] ?? '';
    $aktif = isset($_POST['aktif']) ? (int)$_POST['aktif'] : 0;

    if ($nama == '' || $email == '') {

        $error = "Nama dan email wajib diisi.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "Format email tidak valid.";

    } elseif (!in_array($aktif, [0, 1], true)) {

        $error = "Status akun tidak valid.";

    } else {

        $user_id = (int)$guru['user_id'];
        $email_lama = $guru['email'];

        // CEK EMAIL DUPLIKAT
        $stmt = mysqli_prepare(
            $koneksi,
            "SELECT id FROM users
             WHERE (email = ? OR username = ?)
             AND id != ?
             LIMIT 1"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "ssi",
            $email,
            $email,
            $user_id
        );

        mysqli_stmt_execute($stmt);
        $cek_email = mysqli_stmt_get_result($stmt);
        $email_ada = mysqli_num_rows($cek_email) > 0;
        mysqli_stmt_close($stmt);

        if ($email_ada) {

            $error = "Email tersebut sudah digunakan akun lain.";

        } else {

            // CEK EMAIL DI DATA GURU
            $stmt = mysqli_prepare(
                $koneksi,
                "SELECT id FROM guru
                 WHERE email = ? AND id != ?
                 LIMIT 1"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "si",
                $email,
                $id
            );

            mysqli_stmt_execute($stmt);
            $cek_guru = mysqli_stmt_get_result($stmt);
            $email_guru_ada = mysqli_num_rows($cek_guru) > 0;
            mysqli_stmt_close($stmt);

            if ($email_guru_ada) {

                $error = "Email tersebut sudah digunakan guru lain.";

            } else {

                mysqli_begin_transaction($koneksi);

                try {

                    $nip_db = ($nip === '') ? null : $nip;
                    $no_hp_db = ($no_hp === '') ? null : $no_hp;

                    // UPDATE DATA GURU
                    $stmt = mysqli_prepare(
                        $koneksi,
                        "UPDATE guru
                         SET nama = ?, nip = ?, email = ?, no_hp = ?
                         WHERE id = ?"
                    );

                    mysqli_stmt_bind_param(
                        $stmt,
                        "ssssi",
                        $nama,
                        $nip_db,
                        $email,
                        $no_hp_db,
                        $id
                    );

                    if (!mysqli_stmt_execute($stmt)) {
                        throw new Exception(mysqli_stmt_error($stmt));
                    }

                    mysqli_stmt_close($stmt);

                    // UPDATE AKUN TANPA MENGGANTI PASSWORD
                    if ($password === '') {

                        $stmt = mysqli_prepare(
                            $koneksi,
                            "UPDATE users
                             SET nama = ?, email = ?, username = ?, aktif = ?
                             WHERE id = ? AND role = 'guru'"
                        );

                        mysqli_stmt_bind_param(
                            $stmt,
                            "sssii",
                            $nama,
                            $email,
                            $email,
                            $aktif,
                            $user_id
                        );

                    } else {

                        // UPDATE AKUN SEKALIGUS PASSWORD
                        $password_hash = password_hash(
                            $password,
                            PASSWORD_DEFAULT
                        );

                        $stmt = mysqli_prepare(
                            $koneksi,
                            "UPDATE users
                             SET nama = ?, email = ?, username = ?,
                                 password = ?, aktif = ?
                             WHERE id = ? AND role = 'guru'"
                        );

                        mysqli_stmt_bind_param(
                            $stmt,
                            "ssssii",
                            $nama,
                            $email,
                            $email,
                            $password_hash,
                            $aktif,
                            $user_id
                        );
                    }

                    if (!mysqli_stmt_execute($stmt)) {
                        throw new Exception(mysqli_stmt_error($stmt));
                    }

                    mysqli_stmt_close($stmt);

                    mysqli_commit($koneksi);

                    header("Location: kelola_guru.php");
                    exit;

                } catch (Exception $e) {

                    mysqli_rollback($koneksi);

                    $error = "Gagal memperbarui data: " . $e->getMessage();
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
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Edit Guru</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #f4f6f8;
    color: #333;
}

.header {
    background: #687280;
    color: white;
    padding: 18px 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 10px;
}

.header h2 {
    margin: 0;
    font-size: 20px;
}

.container-edit {
    width: 90%;
    max-width: 750px;
    margin: 30px auto;
}

.box {
    background: white;
    border: 1px solid #ddd;
    border-radius: 7px;
    padding: 25px;
}

.box h1 {
    font-size: 23px;
    margin-bottom: 8px;
}

.keterangan {
    color: #777;
    margin-bottom: 25px;
}

@media(max-width: 600px) {
    .header {
        padding: 15px;
        flex-direction: column;
        align-items: flex-start;
    }

    .container-edit {
        width: 95%;
        margin: 15px auto;
    }

    .box {
        padding: 18px;
    }
}

</style>

</head>

<body>

<div class="header">

    <h2>⚖ SISTEM INFORMASI PELANGGARAN SISWA</h2>

    <a href="kelola_guru.php" class="btn btn-secondary btn-sm">
        ← Kembali
    </a>

</div>


<div class="container-edit">

    <div class="box">

        <h1>Edit Data Guru</h1>

        <p class="keterangan">
            Perbarui informasi guru dan akun login.
        </p>

        <?php if ($error != '') { ?>

            <div class="alert alert-danger">
                <?= htmlspecialchars($error); ?>
            </div>

        <?php } ?>


        <form method="POST">

            <div class="mb-3">

                <label class="form-label">Nama Guru</label>

                <input
                    type="text"
                    name="nama"
                    class="form-control"
                    value="<?= htmlspecialchars($guru['nama']); ?>"
                    required
                >

            </div>


            <div class="mb-3">

                <label class="form-label">NIP</label>

                <input
                    type="text"
                    name="nip"
                    class="form-control"
                    value="<?= htmlspecialchars($guru['nip'] ?? ''); ?>"
                >

            </div>


            <div class="mb-3">

                <label class="form-label">Gmail</label>

                <input
                    type="email"
                    name="email"
                    class="form-control"
                    value="<?= htmlspecialchars($guru['email'] ?? ''); ?>"
                    required
                >

            </div>


            <div class="mb-3">

                <label class="form-label">No HP</label>

                <input
                    type="text"
                    name="no_hp"
                    class="form-control"
                    value="<?= htmlspecialchars($guru['no_hp'] ?? ''); ?>"
                >

            </div>


            <div class="mb-3">

                <label class="form-label">Password Baru</label>

                <input
                    type="password"
                    name="password"
                    class="form-control"
                    placeholder="Kosongkan jika tidak ingin mengganti password"
                >

                <small class="text-muted">
                    Password lama tetap digunakan jika bagian ini dikosongkan.
                </small>

            </div>


            <div class="mb-4">

                <label class="form-label">Status Akun</label>

                <select name="aktif" class="form-select">

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


            <button type="submit" class="btn btn-primary">
                Simpan Perubahan
            </button>

            <a href="kelola_guru.php" class="btn btn-secondary">
                Batal
            </a>

        </form>

    </div>

</div>

</body>
</html>
