<?php
require 'database.php';

if (!empty($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}
?>