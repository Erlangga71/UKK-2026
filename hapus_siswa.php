<?php

require_once "cek_role.php";
wajib_role(['admin']);

include "database.php";

if (!isset($_GET['id'])) {
    header("Location: siswa.php");
    exit;
}

$id = intval($_GET['id']);




mysqli_query(
    $koneksi,
    "DELETE FROM penempatan_siswa WHERE siswa_id = $id"
);



mysqli_query(
    $koneksi,
    "DELETE FROM pelanggaran WHERE siswa_id = $id"
);



$query = mysqli_query(
    $koneksi,
    "DELETE FROM siswa WHERE id = $id"
);

if ($query) {

    header("Location: siswa.php");
    exit;

} else {

    die("Gagal menghapus siswa: " . mysqli_error($koneksi));

}

?>