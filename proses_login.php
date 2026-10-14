<?php

require 'database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

$stmt = $koneksi->prepare(
    "SELECT id, nama, username, password, role
     FROM users
     WHERE username = ? AND aktif = 1
     LIMIT 1"
);

$stmt->bind_param("s", $username);
$stmt->execute();

$user = $stmt->get_result()->fetch_assoc();

if (!$user || !password_verify($password, $user['password'])) {
    header("Location: login.php?error=1");
    exit;
}

session_regenerate_id(true);

$_SESSION['user_id'] = (int)$user['id'];
$_SESSION['nama'] = $user['nama'];
$_SESSION['username'] = $user['username'];
$_SESSION['role'] = $user['role'];

header("Location: dashboard.php");
exit;