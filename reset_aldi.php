
<?php
require_once "database.php";

$email = "aldiganteng@gmail.com";
$password_baru = "admin123";

$hash = password_hash($password_baru, PASSWORD_DEFAULT);

$stmt = mysqli_prepare(
    $koneksi,
    "UPDATE users SET password = ? WHERE email = ? AND role = 'guru'"
);

mysqli_stmt_bind_param($stmt, "ss", $hash, $email);

if (mysqli_stmt_execute($stmt)) {
    if (mysqli_stmt_affected_rows($stmt) > 0) {
        echo "Password Aldi berhasil direset menjadi admin123.";
    } else {
        echo "Password tidak berubah atau akun tidak ditemukan.";
    }
} else {
    echo "Gagal reset password: " . mysqli_error($koneksi);
}

mysqli_stmt_close($stmt);
?>
