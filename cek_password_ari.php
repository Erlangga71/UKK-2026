
<?php
require_once "database.php";

$email = "arisandriana@gmail.com";
$password = "guru123";

$stmt = mysqli_prepare(
    $koneksi,
    "SELECT nama, email, password, role, aktif
     FROM users
     WHERE email = ?
     LIMIT 1"
);

mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

if (!$user) {
    exit("Akun Ari tidak ditemukan di database ini.");
}

echo "Nama: " . htmlspecialchars($user['nama']) . "<br>";
echo "Email: " . htmlspecialchars($user['email']) . "<br>";
echo "Role: " . htmlspecialchars($user['role']) . "<br>";
echo "Aktif: " . $user['aktif'] . "<br>";

if (password_verify($password, $user['password'])) {
    echo "<h3 style='color:green'>Password guru123 COCOK.</h3>";
} else {
    echo "<h3 style='color:red'>Password guru123 TIDAK COCOK.</h3>";
}

mysqli_stmt_close($stmt);
?>
