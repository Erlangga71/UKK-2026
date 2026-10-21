<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/*
|--------------------------------------------------------------------------
| WAJIB LOGIN
|--------------------------------------------------------------------------
*/

function wajib_login()
{
    if (!isset($_SESSION['login'])) {
        header("Location: login.php");
        exit;
    }
}

/*
|--------------------------------------------------------------------------
| CEK ROLE
|--------------------------------------------------------------------------
*/

function hanyaRole($roles = [])
{
    wajib_login();

    $role = $_SESSION['role'] ?? '';

    if (!in_array($role, $roles)) {

        echo "
        <!DOCTYPE html>
        <html lang='id'>
        <head>
            <meta charset='UTF-8'>
            <title>Akses Ditolak</title>

            <style>
                body {
                    font-family: Arial, sans-serif;
                    background: #f1f5f9;
                    text-align: center;
                    padding-top: 100px;
                }

                .box {
                    background: white;
                    width: 450px;
                    max-width: 90%;
                    margin: auto;
                    padding: 30px;
                    border-radius: 10px;
                    box-shadow: 0 3px 10px rgba(0,0,0,0.1);
                }

                h2 {
                    color: #dc2626;
                }

                a {
                    display: inline-block;
                    margin-top: 15px;
                    padding: 10px 20px;
                    background: #086177;
                    color: white;
                    text-decoration: none;
                    border-radius: 6px;
                }

                a:hover {
                    background: #064c5d;
                }
            </style>
        </head>

        <body>

            <div class='box'>

                <h2>Akses Ditolak</h2>

                <p>
                    Anda tidak memiliki izin untuk membuka halaman ini.
                </p>

                <a href='dashboard.php'>
                    Kembali ke Dashboard
                </a>

            </div>

        </body>
        </html>
        ";

        exit;
    }
}

?>