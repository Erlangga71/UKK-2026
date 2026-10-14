<?php
include "database.php";
echo "APACHE NYALA<br>";

$koneksi->query("CREATE TABLE IF NOT EXISTS users (id INT AUTO_INCREMENT PRIMARY KEY, username VARCHAR(50), password VARCHAR(50), level VARCHAR(20))");

$koneksi->query("DELETE FROM users WHERE username='admin'");
$koneksi->query("INSERT INTO users (username, password, level) VALUES ('admin', 'admin', 'admin')");

echo "DATABASE NYAMBUNG<br>";
echo "ADMIN JADI username: admin password: admin<br>";
echo "<h1><a href='login.php'>KLIK DISINI BUAT LOGIN</a></h1>";
?>