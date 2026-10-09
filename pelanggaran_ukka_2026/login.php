<?php
session_start();
include 'database.php';

if(isset($_POST['login'])){
    $u = $_POST['username'];
    $p = $_POST['password'];

    if($u=='admin' && $p=='admin'){
        $_SESSION['login']=true;
        $_SESSION['username']='admin';
        header("Location: dashboard.php");
        exit;
    } elseif($u=='guru' && $p=='guru'){
        $_SESSION['login']=true;
        $_SESSION['username']='guru';
        header("Location: dashboard.php");
        exit;
    } else {
        echo "<script>alert('Username / Password SALAH!'); window.location='login.php';</script>";
    }
}
?>
<h2>Login</h2>
<form method="post">
Username: <input type="text" name="username" required><br><br>
Password: <input type="password" name="password" required><br><br>
<button type="submit" name="login">Login</button>
</form>