<?php
session_start();
include 'database.php';
$u = $_POST['username'];
$p = $_POST['password'];
$q = mysqli_query($koneksi, "SELECT * FROM tbl_user WHERE username='$u' AND password='$p'");
$d = mysqli_fetch_assoc($q);
if($d){
    $_SESSION['login'] = true;
    $_SESSION['role'] = $d['role'];
    $_SESSION['username'] = $d['username'];
    header("Location: dashboard.php");
    exit;
} else {
    header("Location: login.php?pesan=gagal");
}
?>