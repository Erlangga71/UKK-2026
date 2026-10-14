<?php

require 'database.php';

$password = password_hash('admin', PASSWORD_DEFAULT);

$sql = "UPDATE users 
        SET password = '$password'
        WHERE username = 'admin'";

if ($koneksi->query($sql)) {
    echo "Password admin berhasil dibuat.<br>";
    echo "Username: admin<br>";
    echo "Password: admin<br>";
} else {
    echo "Gagal: " . $koneksi->error;
}
?>