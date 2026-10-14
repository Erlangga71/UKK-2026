<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$koneksi = mysqli_connect(
    "localhost",
    "root",
    "",
    "ukk_2026"
);

if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

function e($text)
{
    return htmlspecialchars($text ?? '', ENT_QUOTES, 'UTF-8');
}

function wajib_login()
{
    if (empty($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }
}
?>