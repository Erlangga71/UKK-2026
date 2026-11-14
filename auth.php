
<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/*
|--------------------------------------------------------------------------
| ROLE YANG DIIZINKAN
|--------------------------------------------------------------------------
*/

$role_diizinkan = ['admin', 'guru'];

/*
|--------------------------------------------------------------------------
| WAJIB LOGIN
|--------------------------------------------------------------------------
*/

function wajib_login()
{
    global $role_diizinkan;

    if (
        !isset($_SESSION['login']) ||
        $_SESSION['login'] !== true
    ) {
        header("Location: login.php");
        exit;
    }

    // Pastikan hanya admin dan guru yang bisa masuk
    $role = $_SESSION['role'] ?? '';

    if (!in_array($role, $role_diizinkan, true)) {
        session_unset();
        session_destroy();

        header("Location: login.php");
        exit;
    }
}

/*
|--------------------------------------------------------------------------
| ALIAS UNTUK KOMPATIBILITAS KODE LAMA
|--------------------------------------------------------------------------
*/

function wajibLogin()
{
    wajib_login();
}

/*
|--------------------------------------------------------------------------
| CEK HAK AKSES ROLE
|--------------------------------------------------------------------------
*/

function hanyaRole($roles = [])
{
    global $role_diizinkan;

    wajib_login();

    $role = $_SESSION['role'] ?? '';

    // Batasi semua pemeriksaan role ke admin dan guru
    $roles_valid = array_intersect(
        $roles,
        $role_diizinkan
    );

    if (!in_array($role, $roles_valid, true)) {

        http_response_code(403);

        echo "
        <!DOCTYPE html>
        <html lang='id'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Akses Ditolak</title>

            <style>
                body {
                    font-family: Arial, sans-serif;
                    background: #f4f6f8;
                    text-align: center;
                    padding-top: 100px;
                    margin: 0;
                }

                .box {
                    background: white;
                    width: 450px;
                    max-width: 90%;
                    margin: auto;
                    padding: 30px;
                    border-radius: 10px;
                    border: 1px solid #ddd;
                }

                h2 {
                    color: #dc2626;
                }

                a {
                    display: inline-block;
                    margin-top: 15px;
                    padding: 10px 20px;
                    background: #687280;
                    color: white;
                    text-decoration: none;
                    border-radius: 6px;
                }

                a:hover {
                    background: #4f5965;
                }
            </style>
        </head>

        <body>
            <div class='box'>
                <h2>Akses Ditolak</h2>

                <p>
                    Anda tidak memiliki izin untuk membuka halaman ini.
                </p>

                <a href='dashboard.php'>Kembali ke Dashboard</a>
            </div>
        </body>
        </html>
        ";

        exit;
    }
}

?>
