
<?php
include "database.php";

$daftar_guru = [
    [
        "nama" => "Arif Kusoy",
        "email" => "kusoycucuk4@gmail.com",
        "password" => "arifganteng"
    ],
    [
        "nama" => "Budi idoy",
        "email" => "idoyganteng2@gmail.com",
        "password" => "budiganteng"
    ]
];

foreach ($daftar_guru as $guru) {

    $email = $guru["email"];

    // Cek apakah akun sudah ada
    $cek = mysqli_prepare(
        $koneksi,
        "SELECT id FROM users WHERE email = ? OR username = ?"
    );

    mysqli_stmt_bind_param($cek, "ss", $email, $email);
    mysqli_stmt_execute($cek);
    $hasil = mysqli_stmt_get_result($cek);

    if (mysqli_num_rows($hasil) > 0) {
        echo "Akun " . htmlspecialchars($email) . " sudah ada.<br>";
        continue;
    }

    // Hash password
    $password_hash = password_hash(
        $guru["password"],
        PASSWORD_DEFAULT
    );

    // Simpan akun
    $username = $email;
    $nama = $guru["nama"];

    $simpan = mysqli_prepare(
        $koneksi,
        "INSERT INTO users (nama, username, email, password, role, aktif)
         VALUES (?, ?, ?, ?, 'guru', 1)"
    );

    mysqli_stmt_bind_param(
        $simpan,
        "ssss",
        $nama,
        $username,
        $email,
        $password_hash
    );

    if (mysqli_stmt_execute($simpan)) {
        echo "Akun " . htmlspecialchars($nama) . " berhasil dibuat.<br>";
    } else {
        echo "Gagal membuat akun " . htmlspecialchars($nama) .
             ": " . htmlspecialchars(mysqli_error($koneksi)) . "<br>";
    }
}
?>
