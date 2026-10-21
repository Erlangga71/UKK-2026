```php
<?php

session_start();

include "database.php";


/* =========================
   AMBIL DATA LOGIN
========================= */

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';


/* =========================
   CEK INPUT
========================= */

if ($username == '' || $password == '') {

    header("Location: login.php?pesan=Gmail dan password wajib diisi");
    exit;
}


/* =========================
   AMBIL USER
========================= */

$username = mysqli_real_escape_string(
    $koneksi,
    $username
);

$query = mysqli_query(
    $koneksi,
    "SELECT
        id,
        nama,
        username,
        password,
        role,
        aktif
     FROM users
     WHERE username = '$username'
     LIMIT 1"
);


if (!$query) {

    die(
        "Query error: " .
        mysqli_error($koneksi)
    );

}


/* =========================
   CEK USER
========================= */

if (mysqli_num_rows($query) == 0) {

    header(
        "Location: login.php?pesan=Gmail tidak ditemukan"
    );

    exit;
}


$user = mysqli_fetch_assoc($query);


/* =========================
   CEK AKUN AKTIF
========================= */

if ((int)$user['aktif'] !== 1) {

    header(
        "Location: login.php?pesan=Akun tidak aktif"
    );

    exit;
}


/* =========================
   CEK PASSWORD
========================= */

if ($password !== $user['password']) {

    header(
        "Location: login.php?pesan=Password salah"
    );

    exit;
}


/* =========================
   LOGIN BERHASIL
========================= */

$_SESSION['login'] = true;

$_SESSION['id'] = $user['id'];

$_SESSION['nama'] = $user['nama'];

$_SESSION['username'] = $user['username'];

$_SESSION['role'] = trim($user['role']);


/* =========================
   KE DASHBOARD
========================= */

header("Location: dashboard.php");

exit;

?>
```