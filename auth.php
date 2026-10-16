<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/*
|--------------------------------------------------------------------------
| WAJIB LOGIN
|--------------------------------------------------------------------------
*/

function wajibLogin()
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
    wajibLogin();

    if (!in_array($_SESSION['role'], $roles)) {

        echo "<!DOCTYPE html>
        <html>
        <head>
            <title>Akses Ditolak</title>
            <style>
                body {
                    font-family: Arial;
                    background: #f1f5f9;
                    text-align: center;
                    padding-top: 100px;
                }

                .box {
                    background: white;
                    width: 450px;
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
        </html>";

        exit;
    }
}

?>