```php
<?php

session_start();

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

include "database.php";

$role = $_SESSION['role'] ?? '';

/* =========================
   CEK ADMIN
========================= */

if ($role != 'admin') {
    echo "Akses ditolak.";
    exit;
}


/* =========================
   TOMBOL TAMBAH
========================= */

$tampil_form = isset($_GET['tambah']);


/* =========================
   TAMBAH GURU
========================= */

if (isset($_POST['simpan'])) {

    $nip      = trim($_POST['nip'] ?? '');
    $nama     = trim($_POST['nama'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $no_hp    = trim($_POST['no_hp'] ?? '');

    if ($nama == '' || $email == '' || $password == '') {

        $error = "Nama, Gmail dan password wajib diisi.";
        $tampil_form = true;

    } else {

        $nip_db   = mysqli_real_escape_string($koneksi, $nip);
        $nama_db  = mysqli_real_escape_string($koneksi, $nama);
        $email_db = mysqli_real_escape_string($koneksi, $email);
        $no_hp_db = mysqli_real_escape_string($koneksi, $no_hp);

        /* CEK EMAIL */

        $cek_user = mysqli_query(
            $koneksi,
            "SELECT id
             FROM users
             WHERE username = '$email_db'
             LIMIT 1"
        );

        if (mysqli_num_rows($cek_user) > 0) {

            $error = "Gmail tersebut sudah digunakan.";
            $tampil_form = true;

        } else {

            /* PASSWORD */

            $password_hash = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            $password_db = mysqli_real_escape_string(
                $koneksi,
                $password_hash
            );


            /* BUAT AKUN USER */

            $buat_user = mysqli_query(
                $koneksi,
                "INSERT INTO users
                (
                    nama,
                    username,
                    password,
                    role,
                    aktif
                )
                VALUES
                (
                    '$nama_db',
                    '$email_db',
                    '$password_db',
                    'guru',
                    1
                )"
            );


            if (!$buat_user) {

                $error = "Gagal membuat akun: "
                       . mysqli_error($koneksi);

                $tampil_form = true;

            } else {

                $user_id = mysqli_insert_id($koneksi);


                /* SIMPAN DATA GURU */

                $buat_guru = mysqli_query(
                    $koneksi,
                    "INSERT INTO guru
                    (
                        user_id,
                        nip,
                        nama,
                        email,
                        no_hp
                    )
                    VALUES
                    (
                        $user_id,
                        " .
                        (
                            $nip != ''
                            ? "'$nip_db'"
                            : "NULL"
                        ) . ",
                        '$nama_db',
                        '$email_db',
                        '$no_hp_db'
                    )"
                );


                if ($buat_guru) {

                    header("Location: kelola_guru.php");
                    exit;

                } else {

                    mysqli_query(
                        $koneksi,
                        "DELETE FROM users
                         WHERE id = $user_id"
                    );

                    $error = "Data guru gagal disimpan: "
                           . mysqli_error($koneksi);

                    $tampil_form = true;
                }
            }
        }
    }
}


/* =========================
   HAPUS GURU
========================= */

if (isset($_GET['hapus'])) {

    $id = intval($_GET['hapus']);

    if ($id > 0) {

        /* Ambil user_id */

        $ambil_user = mysqli_query(
            $koneksi,
            "SELECT user_id
             FROM guru
             WHERE id = $id
             LIMIT 1"
        );

        $guru_hapus = mysqli_fetch_assoc($ambil_user);

        $user_id = $guru_hapus['user_id'] ?? 0;


        /* Hapus guru */

        mysqli_query(
            $koneksi,
            "DELETE FROM guru
             WHERE id = $id"
        );


        /* Hapus akun login */

        if ($user_id > 0) {

            mysqli_query(
                $koneksi,
                "DELETE FROM users
                 WHERE id = $user_id"
            );
        }
    }

    header("Location: kelola_guru.php");
    exit;
}


/* =========================
   SEARCH
========================= */

$search = trim($_GET['search'] ?? '');


/* =========================
   PAGINATION
========================= */

$per_halaman = 5;

$halaman = isset($_GET['halaman'])
    ? (int)$_GET['halaman']
    : 1;

if ($halaman < 1) {
    $halaman = 1;
}


/* =========================
   HITUNG DATA
========================= */

if ($search != '') {

    $cari = "%$search%";

    $stmt = $koneksi->prepare(
        "SELECT COUNT(*) AS total
         FROM guru
         WHERE nip LIKE ?
         OR nama LIKE ?
         OR email LIKE ?
         OR no_hp LIKE ?"
    );

    $stmt->bind_param(
        "ssss",
        $cari,
        $cari,
        $cari,
        $cari
    );

    $stmt->execute();

    $hasil = $stmt->get_result()->fetch_assoc();

    $total_data = (int)$hasil['total'];

} else {

    $hasil = mysqli_query(
        $koneksi,
        "SELECT COUNT(*) AS total
         FROM guru"
    );

    $hasil_total = mysqli_fetch_assoc($hasil);

    $total_data = (int)$hasil_total['total'];
}


$total_halaman = max(
    1,
    ceil($total_data / $per_halaman)
);

if ($halaman > $total_halaman) {
    $halaman = $total_halaman;
}

$mulai = ($halaman - 1) * $per_halaman;


/* =========================
   AMBIL DATA GURU
========================= */

if ($search != '') {

    $cari = "%$search%";

    $stmt = $koneksi->prepare(
        "SELECT
            id,
            nip,
            nama,
            email,
            no_hp
         FROM guru
         WHERE nip LIKE ?
         OR nama LIKE ?
         OR email LIKE ?
         OR no_hp LIKE ?
         ORDER BY nama ASC
         LIMIT ?, ?"
    );

    $stmt->bind_param(
        "ssssii",
        $cari,
        $cari,
        $cari,
        $cari,
        $mulai,
        $per_halaman
    );

    $stmt->execute();

    $data = $stmt->get_result();

} else {

    $stmt = $koneksi->prepare(
        "SELECT
            id,
            nip,
            nama,
            email,
            no_hp
         FROM guru
         ORDER BY nama ASC
         LIMIT ?, ?"
    );

    $stmt->bind_param(
        "ii",
        $mulai,
        $per_halaman
    );

    $stmt->execute();

    $data = $stmt->get_result();
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Data Guru</title>

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
   SIDEBAR
========================= */

.sidebar {
    position: fixed;
    left: 0;
    top: 0;
    width: 220px;
    height: 100vh;
    background: #687280;
    padding: 20px 12px;
    overflow-y: auto;
}

.sidebar-title {
    color: white;
    font-size: 19px;
    font-weight: bold;
    margin: 0 8px 25px;
    line-height: 1.4;
}

.sidebar a {
    display: block;
    padding: 11px 13px;
    margin-bottom: 5px;
    color: white;
    text-decoration: none;
    border-radius: 5px;
    font-size: 14px;
}

.sidebar a:hover,
.sidebar a.active {
    background: #4f5965;
}

.logout {
    margin-top: 20px;
    background: #b42323;
}


/* =========================
   CONTENT
========================= */

.content {
    margin-left: 220px;
    padding: 30px;
}

.header,
.box {
    background: white;
    border: 1px solid #ddd;
    border-radius: 7px;
    padding: 22px;
    margin-bottom: 20px;
}

.header h1 {
    margin: 0;
    font-size: 24px;
}

.header p {
    color: #777;
}


/* =========================
   TOMBOL
========================= */

.btn {
    display: inline-block;
    padding: 10px 15px;
    border: none;
    border-radius: 5px;
    text-decoration: none;
    cursor: pointer;
    font-size: 14px;
}

.btn-tambah {
    background: #087f5b;
    color: white;
}

.btn-tambah:hover {
    background: #066b4d;
}

.btn-batal {
    background: #777;
    color: white;
    margin-left: 5px;
}

.btn-batal:hover {
    background: #555;
}

.btn-simpan {
    background: #687280;
    color: white;
}

.btn-simpan:hover {
    background: #56606d;
}


/* =========================
   ATAS DATA
========================= */

.top-data {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 10px;
    margin-bottom: 20px;
}

.search-form {
    display: flex;
    gap: 7px;
}

.search-form input {
    width: 280px;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
}

.btn-cari {
    background: #176b80;
    color: white;
}

.btn-reset {
    background: #777;
    color: white;
}


/* =========================
   FORM
========================= */

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}

.form-group {
    display: flex;
    flex-direction: column;
}

label {
    margin-bottom: 6px;
    font-size: 13px;
    font-weight: bold;
}

input {
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
    font-family: Arial;
}

.form-buttons {
    margin-top: 5px;
}


/* =========================
   ERROR
========================= */

.error {
    background: #fee2e2;
    color: #b91c1c;
    border: 1px solid #fecaca;
    padding: 10px;
    margin-bottom: 15px;
    border-radius: 5px;
}


/* =========================
   TABLE
========================= */

.table-container {
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th {
    background: #687280;
    color: white;
    padding: 11px;
    text-align: left;
    font-size: 13px;
}

td {
    padding: 11px;
    border-bottom: 1px solid #ddd;
    font-size: 13px;
}

tr:hover {
    background: #f7f7f7;
}


/* =========================
   NAMA + NIP
========================= */

.nama-guru {
    font-weight: bold;
}

.nip-guru {
    color: #777;
    font-size: 12px;
    margin-top: 4px;
}


/* =========================
   AKSI
========================= */

.btn-edit,
.btn-hapus {
    color: white;
    padding: 6px 10px;
    text-decoration: none;
    border-radius: 4px;
    font-size: 12px;
}

.btn-edit {
    background: #086177;
}

.btn-edit:hover {
    background: #064d5f;
}

.btn-hapus {
    background: #b42323;
}

.btn-hapus:hover {
    background: #941d1d;
}


/* =========================
   PAGINATION
========================= */

.pagination {
    margin-top: 20px;
    display: flex;
    gap: 5px;
    flex-wrap: wrap;
}

.pagination a {
    padding: 8px 12px;
    border: 1px solid #ccc;
    background: white;
    color: #333;
    text-decoration: none;
    border-radius: 4px;
}

.pagination a:hover,
.pagination a.active {
    background: #176b80;
    color: white;
}

.info {
    margin-top: 15px;
    color: #666;
    font-size: 13px;
}


/* =========================
   MOBILE
========================= */

@media(max-width:700px) {

    .sidebar {
        position: relative;
        width: 100%;
        height: auto;
    }

    .content {
        margin-left: 0;
        padding: 15px;
    }

    .form-grid {
        grid-template-columns: 1fr;
    }

    .top-data {
        display: block;
    }

    .search-form {
        margin-top: 15px;
        flex-wrap: wrap;
    }

    .search-form input {
        width: 100%;
    }
}

</style>

</head>

<body>


<!-- =========================
     SIDEBAR
========================= -->

<div class="sidebar">

    <div class="sidebar-title">
        ⚖ Pelanggaran<br>
        Siswa
    </div>

    <a href="dashboard.php">
        🏠 Dashboard
    </a>

    <a href="kelola_siswa.php">
        Data Siswa
    </a>

    <a href="kelola_guru.php" class="active">
        Data Guru
    </a>

    <a href="kelola_kelas.php">
        Data Kelas
    </a>

    <a href="kelola_tahun_ajaran.php">
        Tahun Ajaran
    </a>

    <a href="kelola_wali_kelas.php">
        Wali Kelas
    </a>

    <a href="penempatan_siswa.php">
        Penempatan Siswa
    </a>

    <a href="kelola_pelanggaran_kategori.php">
        ⚠ Kategori Pelanggaran
    </a>

    <a href="pelanggaran.php">
        Data Pelanggaran
    </a>

    <a href="tindakan.php">
        Data Tindakan
    </a>

    <a href="laporan.php">
        Laporan
    </a>

    <a href="about_me.php">
        ℹ About Me
    </a>

    <a href="logout.php" class="logout">
        🚪 Logout
    </a>

</div>


<!-- =========================
     CONTENT
========================= -->

<div class="content">

    <div class="header">

        <h1>
            Data Guru
        </h1>

        <p>
            Kelola data guru dan akun login guru.
        </p>

    </div>


    <?php if (isset($error)) { ?>

        <div class="error">
            <?= htmlspecialchars($error); ?>
        </div>

    <?php } ?>


    <!-- =========================
         TOMBOL TAMBAH
    ========================= -->

    <div class="box">

        <div class="top-data">

            <strong>
                Daftar Guru
            </strong>

            <a
                href="kelola_guru.php?tambah=1"
                class="btn btn-tambah"
            >
                + Tambah Guru
            </a>

        </div>


        <!-- SEARCH -->

        <form
            method="GET"
            class="search-form"
        >

            <input
                type="text"
                name="search"
                placeholder="Cari NIP, nama, Gmail..."
                value="<?= htmlspecialchars($search); ?>"
            >

            <button
                type="submit"
                class="btn btn-cari"
            >
                Cari
            </button>

            <?php if ($search != '') { ?>

                <a
                    href="kelola_guru.php"
                    class="btn btn-reset"
                >
                    Reset
                </a>

            <?php } ?>

        </form>

    </div>


    <!-- =========================
         FORM TAMBAH
         HANYA MUNCUL JIKA DIKLIK
    ========================= -->

    <?php if ($tampil_form) { ?>

        <div class="box">

            <h2>
                Tambah Data Guru
            </h2>

            <form method="POST">

                <div class="form-grid">


                    <div class="form-group">

                        <label>
                            NIP
                        </label>

                        <input
                            type="text"
                            name="nip"
                            placeholder="Masukkan NIP"
                        >

                    </div>


                    <div class="form-group">

                        <label>
                            Nama Guru
                        </label>

                        <input
                            type="text"
                            name="nama"
                            placeholder="Masukkan nama guru"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label>
                            Gmail
                        </label>

                        <input
                            type="email"
                            name="email"
                            placeholder="contoh@gmail.com"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label>
                            Password Login
                        </label>

                        <input
                            type="password"
                            name="password"
                            placeholder="Masukkan password"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label>
                            No HP
                        </label>

                        <input
                            type="text"
                            name="no_hp"
                            placeholder="Masukkan nomor HP"
                        >

                    </div>

                </div>


                <div class="form-buttons">

                    <button
                        type="submit"
                        name="simpan"
                        class="btn btn-simpan"
                    >
                        Simpan Guru
                    </button>

                    <a
                        href="kelola_guru.php"
                        class="btn btn-batal"
                    >
                        Batal
                    </a>

                </div>

            </form>

        </div>

    <?php } ?>


    <!-- =========================
         DAFTAR GURU
    ========================= -->

    <div class="box">

        <h2>
            Daftar Guru
        </h2>

        <div class="table-container">

            <table>

                <thead>

                    <tr>

                        <th>No</th>
                        <th>Nama Guru / NIP</th>
                        <th>Gmail</th>
                        <th>No HP</th>
                        <th>Aksi</th>

                    </tr>

                </thead>

                <tbody>


                <?php

                if ($data && $data->num_rows > 0) {

                    $no = $mulai + 1;

                    while ($row = $data->fetch_assoc()) {

                ?>

                    <tr>

                        <td>
                            <?= $no++; ?>
                        </td>


                        <td>

                            <div class="nama-guru">

                                <?= htmlspecialchars(
                                    $row['nama']
                                ); ?>

                            </div>

                            <div class="nip-guru">

                                NIP:
                                <?= htmlspecialchars(
                                    $row['nip'] ?? '-'
                                ); ?>

                            </div>

                        </td>


                        <td>

                            <?= htmlspecialchars(
                                $row['email'] ?? '-'
                            ); ?>

                        </td>


                        <td>

                            <?= htmlspecialchars(
                                $row['no_hp'] ?? '-'
                            ); ?>

                        </td>


                        <td>

                            <a
                                class="btn-edit"
                                href="edit_guru.php?id=<?= (int)$row['id']; ?>"
                            >
                                Edit
                            </a>

                            <a
                                class="btn-hapus"
                                href="kelola_guru.php?hapus=<?= (int)$row['id']; ?>"
                                onclick="return confirm('Hapus guru dan akun login guru ini?')"
                            >
                                Hapus
                            </a>

                        </td>

                    </tr>

                <?php

                    }

                } else {

                ?>

                    <tr>

                        <td
                            colspan="5"
                            style="text-align:center;padding:25px;"
                        >
                            Belum ada data guru.
                        </td>

                    </tr>

                <?php } ?>

                </tbody>

            </table>

        </div>


        <!-- INFO -->

        <div class="info">

            Menampilkan

            <?= $total_data > 0 ? $mulai + 1 : 0; ?>

            -

            <?= min(
                $mulai + $per_halaman,
                $total_data
            ); ?>

            dari

            <?= $total_data; ?>

            data guru.

        </div>


        <!-- PAGINATION -->

        <?php if ($total_halaman > 1) { ?>

            <div class="pagination">


                <?php if ($halaman > 1) { ?>

                    <a
                        href="?halaman=<?= $halaman - 1; ?>&search=<?= urlencode($search); ?>"
                    >
                        &laquo;
                    </a>

                <?php } ?>


                <?php for (
                    $i = 1;
                    $i <= $total_halaman;
                    $i++
                ) { ?>

                    <a
                        href="?halaman=<?= $i; ?>&search=<?= urlencode($search); ?>"
                        class="<?= $i == $halaman ? 'active' : ''; ?>"
                    >
                        <?= $i; ?>
                    </a>

                <?php } ?>


                <?php if ($halaman < $total_halaman) { ?>

                    <a
                        href="?halaman=<?= $halaman + 1; ?>&search=<?= urlencode($search); ?>"
                    >
                        &raquo;
                    </a>

                <?php } ?>


            </div>

        <?php } ?>

    </div>


</div>

</body>

</html>
```