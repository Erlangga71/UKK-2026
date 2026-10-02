<?php

session_start();

require 'database.php';

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

if ($username == '' || $password == '') {

    header(
        "Location: login.php?error=Username dan password wajib diisi"
    );

    exit;
}


$sql = "SELECT * FROM users WHERE username = ? LIMIT 1";

$stmt = $pdo->prepare($sql);

$stmt->execute([$username]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);


if ($user && password_verify($password, $user['password'])) {

    session_regenerate_id(true);

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['nama'] = $user['nama'];
    $_SESSION['role'] = $user['role'];

    header("Location: dashboard.php");

    exit;
}


header(
    "Location: login.php?error=Username atau password salah"
);

exit;
