<?php

session_start();

include "database.php";

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

$username = mysqli_real_escape_string($koneksi, $username);

$query = mysqli_query(
    $koneksi,
    "SELECT id, nama, username, password, role, aktif
     FROM users
     WHERE username = '$username'
     LIMIT 1"
);

if (!$query) {
    die("Query error: " . mysqli_error($koneksi));
}

if (mysqli_num_rows($query) == 0) {

    header("Location: login.php?pesan=Username tidak ditemukan");
    exit;

}

$user = mysqli_fetch_assoc($query);


/* CEK PASSWORD */

if ($password != $user['password']) {

    header("Location: login.php?pesan=Password salah");
    exit;

}


/* CEK AKUN */

if ($user['aktif'] == 0) {

    header("Location: login.php?pesan=Akun tidak aktif");
    exit;

}


/* SIMPAN SESSION */

$_SESSION['login'] = true;

$_SESSION['id'] = $user['id'];

$_SESSION['nama'] = $user['nama'];

$_SESSION['username'] = $user['username'];

$_SESSION['role'] = trim($user['role']);


/* PINDAH KE DASHBOARD */

header("Location: dashboard.php");

exit;

?>