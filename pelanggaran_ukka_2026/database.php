<?php
$koneksi = mysqli_connect("localhost","root","","db_pelanggaran");
if(!$koneksi){
 die("Koneksi gagal bre, nama db salah: ".mysqli_connect_error());
}
?>