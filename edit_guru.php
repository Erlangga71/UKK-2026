
<?php
session_start();

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

/* Nilai awal form */
$form = $guru;

/* =========================
   PROSES UPDATE
========================= */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nama     = trim($_POST['nama'] ?? '');
    $nip      = trim($_POST['nip'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $no_hp    = trim($_POST['no_hp'] ?? '');
    $password = $_POST['password'] ?? '';
    $aktif    = isset($_POST['aktif']) ? (int)$_POST['aktif'] : 0;

    $form['nama']  = $nama;
    $form['nip']   = $nip;
    $form['email'] = $email;
    $form['no_hp'] = $no_hp;
    $form['aktif'] = $aktif;

    if ($nama == '' || $email == '') {

        $error = "Nama dan Gmail wajib diisi.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "Format Gmail tidak valid.";

    } elseif ($nip != '' && (!ctype_digit($nip) || strlen($nip) > 18)) {

        $error = "NIP hanya boleh angka dan maksimal 18 digit.";

    } elseif (!in_array($aktif, [0, 1], true)) {

        $error = "Status akun tidak valid.";

    } else {

        $user_id = (int)$guru['user_id'];

        /* CEK EMAIL DUPLIKAT DI USERS */
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

        /* CEK EMAIL DUPLIKAT DI GURU */
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

        /* CEK NIP DUPLIKAT */
        $nip_ada = false;

        if ($nip != '') {
            $stmt = mysqli_prepare(
                $koneksi,
                "SELECT id FROM guru
                 WHERE nip = ? AND id != ?
                 LIMIT 1"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "si",
                $nip,
                $id
            );

            mysqli_stmt_execute($stmt);
            $cek_nip = mysqli_stmt_get_result($stmt);
            $nip_ada = mysqli_num_rows($cek_nip) > 0;
            mysqli_stmt_close($stmt);
        }

        if ($email_ada || $email_guru_ada) {

            $error = "Gmail tersebut sudah digunakan.";

        } elseif ($nip_ada) {

            $error = "NIP tersebut sudah digunakan guru lain.";

        } else {

            mysqli_begin_transaction($koneksi);

            try {

                $nip_db = ($nip == '') ? null : $nip;
                $no_hp_db = ($no_hp == '') ? null : $no_hp;

                /* UPDATE DATA GURU */
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

                /* UPDATE AKUN TANPA GANTI PASSWORD */
                if ($password == '') {

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

                    /* PASSWORD BARU DI-HASH */
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
?>

<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Edit Data Guru</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body {
    background: #f4f6f8;
    font-family: Arial, sans-serif;
    font-size: 14px;
    margin: 0;
}

/* SIDEBAR */
.sidebar {
    width: 220px;
    background: #687280;
}

.sidebar .nav-link {
    color: white;
    font-size: 14px;
    padding: 8px 12px;
    border-radius: 4px;
}

.sidebar .nav-link:hover,
.sidebar .nav-link.active {
    background: #4f5965;
    color: white;
}

/* CONTENT */
.content {
    margin-left: 220px;
}

/* CARD */
.card {
    border: 1px solid #d8d8d8;
    border-radius: 6px;
}

.card-body {
    padding: 13px;
}

/* TOMBOL */
.btn-stabilo {
    background: #9dff00;
    color: #1a3300;
    font-weight: bold;
    border: none;
}

.btn-stabilo:hover {
    background: #86e000;
    color: #1a3300;
}

/* RESPONSIVE */
@media (max-width: 700px) {
    .sidebar {
        position: static !important;
        width: 100%;
        height: auto !important;
    }

    .content {
        margin-left: 0;
    }
}

</style>

</head>

<body>

<!-- SIDEBAR -->
<div class="sidebar position-fixed top-0 start-0 vh-100 overflow-auto p-3">

    <h5 class="text-white fw-bold mb-4 px-2">
        ⚖ Pelanggaran<br>Siswa
    </h5>

    <ul class="nav flex-column gap-1">

        <li><a class="nav-link" href="dashboard.php">🏠 Dashboard</a></li>
        <li><a class="nav-link" href="kelola_siswa.php">Data Siswa</a></li>
        <li><a class="nav-link active" href="kelola_guru.php">Data Guru</a></li>
        <li><a class="nav-link" href="kelola_kelas.php">Data Kelas</a></li>
        <li><a class="nav-link" href="kelola_tahun_ajaran.php">Tahun Ajaran</a></li>
        <li><a class="nav-link" href="kelola_wali_kelas.php">Wali Kelas</a></li>
        <li><a class="nav-link" href="penempatan_siswa.php">Penempatan Siswa</a></li>
        <li><a class="nav-link" href="kelola_pelanggaran_kategori.php">⚠ Kategori Pelanggaran</a></li>
        <li><a class="nav-link" href="pelanggaran.php">Data Pelanggaran</a></li>
        <li><a class="nav-link" href="tindakan.php">Data Tindakan</a></li>
        <li><a class="nav-link" href="laporan.php">Laporan</a></li>
        <li><a class="nav-link" href="about_me.php">ℹ About Me</a></li>

    </ul>

    <a href="logout.php" class="btn btn-danger w-100 mt-4 text-start">
        🚪 Logout
    </a>

</div>

<!-- CONTENT -->
<div class="content p-4">

    <!-- HEADER -->
    <div class="card mb-3">
        <div class="card-body">

            <h1 class="h3 mb-1">Data Guru</h1>

            <p class="text-muted mb-0">
                Kelola data guru dan akun login guru.
            </p>

        </div>
    </div>

    <!-- FORM EDIT -->
    <div class="card mb-3">

        <div class="card-body">

            <h2 class="h5 mb-3">Edit Data Guru</h2>

            <?php if ($error != '') { ?>

                <div class="alert alert-danger">
                    <?= htmlspecialchars($error); ?>
                </div>

            <?php } ?>

            <form method="POST">

                <div class="row g-3">

                    <div class="col-md-6">
                        <label class="form-label fw-bold">NIP</label>

                        <input
                            type="text"
                            name="nip"
                            class="form-control"
                            placeholder="Masukkan NIP (maksimal 18 digit)"
                            value="<?= htmlspecialchars($form['nip'] ?? ''); ?>"
                            maxlength="18"
                            inputmode="numeric"
                            pattern="[0-9]{1,18}"
                            title="NIP hanya boleh angka dan maksimal 18 digit."
                            oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 18)"
                        >
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Nama Guru</label>

                        <input
                            type="text"
                            name="nama"
                            class="form-control"
                            placeholder="Masukkan nama guru"
                            value="<?= htmlspecialchars($form['nama'] ?? ''); ?>"
                            required
                        >
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Gmail</label>

                        <input
                            type="email"
                            name="email"
                            class="form-control"
                            placeholder="contoh@gmail.com"
                            value="<?= htmlspecialchars($form['email'] ?? ''); ?>"
                            required
                        >
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Password Baru</label>

                        <input
                            type="password"
                            name="password"
                            class="form-control"
                            placeholder="Kosongkan jika tidak ingin mengganti password"
                        >

                        <small class="text-muted">
                            Password lama tetap digunakan jika dikosongkan.
                        </small>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">No HP</label>

                        <input
                            type="text"
                            name="no_hp"
                            class="form-control"
                            placeholder="Masukkan nomor HP"
                            value="<?= htmlspecialchars($form['no_hp'] ?? ''); ?>"
                        >
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Status Akun</label>

                        <select name="aktif" class="form-select">

                            <option value="1"
                                <?= ((int)$form['aktif'] === 1) ? 'selected' : ''; ?>>
                                Aktif
                            </option>

                            <option value="0"
                                <?= ((int)$form['aktif'] === 0) ? 'selected' : ''; ?>>
                                Tidak Aktif
                            </option>

                        </select>
                    </div>

                </div>

                <div class="mt-3">

                    <button type="submit" class="btn btn-secondary">
                        Simpan Perubahan
                    </button>

                    <a href="kelola_guru.php" class="btn btn-outline-secondary">
                        Batal
                    </a>

                </div>

            </form>

        </div>

    </div>

</div>

</body>
</html>
