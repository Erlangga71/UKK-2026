<?php
require_once "database.php";

$email = "arisandriana@gmail.com";
$password_baru = "password";

$hash = password_hash($password_baru, PASSWORD_DEFAULT);

$query = "UPDATE users
          SET password = ?
          WHERE email = ?
          AND role = 'guru'";

$stmt = mysqli_prepare($koneksi, $query);

if (!$stmt) {
    die("Gagal menyiapkan query: " . mysqli_error($koneksi));
}

mysqli_stmt_bind_param($stmt, "ss", $hash, $email);

if (mysqli_stmt_execute($stmt)) {
    if (mysqli_stmt_affected_rows($stmt) > 0) {
        echo "Password guru berhasil diperbarui.";
    } else {
        echo "Tidak ada data yang diperbarui. Periksa email guru di database.";
    }
} else {
    echo "Gagal memperbarui password: " . mysqli_stmt_error($stmt);
}

mysqli_stmt_close($stmt);
?>