
<?php
session_start();
include 'database.php';

$error = '';

if (isset($_POST['login'])) {
    $u = trim($_POST['username']);
    $p = $_POST['password'];

    $stmt = mysqli_prepare(
        $koneksi,
        "SELECT id, username, password, role
         FROM tbl_user
         WHERE username = ?"
    );

    mysqli_stmt_bind_param($stmt, "s", $u);
    mysqli_stmt_execute($stmt);

    $hasil = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($hasil);

    if ($user && $p === $user['password']) {
        session_regenerate_id(true);

        $_SESSION['login'] = true;
        $_SESSION['id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Username atau password salah!";
    }

    mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login Sistem Pelanggaran</title>
</head>
<body>

<h2>LOGIN SISTEM PELANGGARAN SISWA</h2>
<hr>

<?php if ($error != ''): ?>
    <p><?php echo htmlspecialchars($error); ?></p>
<?php endif; ?>

<form method="post">
    <table>
        <tr>
            <td>Username</td>
            <td><input type="text" name="username" required></td>
        </tr>
        <tr>
            <td>Password</td>
            <td><input type="password" name="password" required></td>
        </tr>
        <tr>
            <td></td>
            <td><button type="submit" name="login">Login</button></td>
        </tr>
    </table>
</form>

</body>
</html>
