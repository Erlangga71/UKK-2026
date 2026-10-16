<?php

session_start();

if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {

    header("Location: login.php");
    exit;

}


function wajib_role($roles)
{

    if (!is_array($roles)) {

        $roles = [$roles];

    }

    $role_user = trim($_SESSION['role'] ?? '');

    if (!in_array($role_user, $roles)) {

        header("Location: dashboard.php");
        exit;

    }

}

?>