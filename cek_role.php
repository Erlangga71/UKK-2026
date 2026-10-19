<?php

// Mulai session hanya jika belum aktif
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/*
|--------------------------------------------------------------------------
| CEK LOGIN
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

function wajib_role($roles)
{
    // Pastikan user sudah login
    if (!isset($_SESSION['login'])) {
        header("Location: login.php");
        exit;
    }

    // Ambil role user
    $role = $_SESSION['role'] ?? '';

    // Jika role tidak diizinkan
    if (!in_array($role, $roles)) {
        echo "<!DOCTYPE html>";
        echo "<html lang='id'>";
        echo "<head>";
        echo "<meta charset='UTF-8'>";
        echo "<title>Akses Ditolak</title>";

        echo "<style>";
        echo "
            body {
                font-family: Arial, sans-serif;
                background: #f4f6f8;
                padding: 40px;
            }

            .box {
                max-width: 500px;
                margin: auto;
                background: white;
                border: 1px solid #ddd;
                border-radius: 7px;
                padding: 30px;
                text-align: center;
            }

            h2 {
                color: #b42323;
            }

            a {
                display: inline-block;
                margin-top: 15px;
                padding: 10px 15px;
                background: #687280;
                color: white;
                text-decoration: none;
                border-radius: 4px;
            }

            a:hover {
                background: #56606d;
            }
        ";
        echo "</style>";

        echo "</head>";
        echo "<body>";

        echo "<div class='box'>";

        echo "<h2>Akses Ditolak</h2>";

        echo "<p>";
        echo "Anda tidak memiliki hak akses untuk membuka halaman ini.";
        echo "</p>";

        echo "<a href='dashboard.php'>";
        echo "← Kembali ke Dashboard";
        echo "</a>";

        echo "</div>";

        echo "</body>";
        echo "</html>";

        exit;
    }
}

?>