<?php

$koneksi = mysqli_connect(
    "localhost",
    "root",
    "",
    "ukk-2026"
);

if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

?>