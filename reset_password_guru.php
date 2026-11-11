<?php
require_once "database.php";

$password = password_hash("guru123", PASSWORD_DEFAULT);

$emails = [
    "arisandriana@gmail.com",
    "aldiganteng@gmail.com"
];

$stmt = mysqli_prepare(
    $koneksi,
    "UPDATE users SET password = ? WHERE email = ?"
);

foreach ($emails as $email) {
    mysqli_stmt_bind_param($stmt, "ss", $password, $email);
    mysqli_stmt_execute($stmt);

    echo "$email berhasil diproses<br>";
}

mysqli_stmt_close($stmt);
?>