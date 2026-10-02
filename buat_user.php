<?php

require 'database.php';

$username = "admin";
$password = password_hash("admin123", PASSWORD_DEFAULT);
$nama = "Administrator";
$role = "admin";

$sql = "INSERT INTO users (username, password, nama, role)
        VALUES (?, ?, ?, ?)";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    $username,
    $password,
    $nama,
    $role
]);

echo "User berhasil dibuat.";
