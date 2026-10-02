<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

require '../database.php';

$sql = "SELECT * FROM siswa ORDER BY id DESC";
$stmt = $pdo->query($sql);

$siswa = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <title>Data Siswa</title>

    <style>

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f5f5f5;
        }

        .header {
            background: white;
            border-bottom: 1px solid #ccc;
            padding: 15px 30px;

            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h2 {
            margin: 0;
        }

        .content {
            padding: 30px;
        }

        .button {
            display: inline-block;
            padding: 9px 15px;

            background: #333;
            color: white;

            text-decoration: none;

            border-radius: 3px;

            margin-bottom: 20px;
        }

        .back {
            background: #777;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: left;
        }

        th {
            background: #eee;
        }

        .edit {
            color: #0066cc;
            text-decoration: none;
        }

        .hapus {
            color: #cc0000;
            text-decoration: none;
        }

    </style>

</head>

<body>


<div class="header">

    <h2>
        Data Siswa
    </h2>

    <div>
        <?= htmlspecialchars($_SESSION['nama']) ?>
    </div>

</div>


<div class="content">

    <a
        href="tambah.php"
        class="button"
    >
        + Tambah Siswa
    </a>

    <a
        href="../dashboard.php"
        class="button back"
    >
        Kembali
    </a>


    <table>

        <thead>

            <tr>

                <th>No</th>
                <th>NIS</th>
                <th>Nama</th>
                <th>Jenis Kelamin</th>
                <th>Kelas</th>
                <th>Alamat</th>
                <th>No HP</th>
                <th>Aksi</th>

            </tr>

        </thead>


        <tbody>

            <?php if (count($siswa) > 0): ?>

                <?php $no = 1; ?>

                <?php foreach ($siswa as $row): ?>

                    <tr>

                        <td>
                            <?= $no++ ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($row['nis']) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($row['nama']) ?>
                        </td>

                        <td>
                            <?= $row['jenis_kelamin'] == 'L'
                                ? 'Laki-laki'
                                : 'Perempuan'
                            ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($row['kelas']) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($row['alamat']) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($row['no_hp']) ?>
                        </td>

                        <td>

                            <a
                                href="edit.php?id=<?= $row['id'] ?>"
                                class="edit"
                            >
                                Edit
                            </a>

                            |

                            <a
                                href="hapus.php?id=<?= $row['id'] ?>"
                                class="hapus"
                                onclick="return confirm('Yakin ingin menghapus data ini?')"
                            >
                                Hapus
                            </a>

                        </td>

                    </tr>

                <?php endforeach; ?>

            <?php else: ?>

                <tr>

                    <td colspan="8" style="text-align:center;">
                        Belum ada data siswa
                    </td>

                </tr>

            <?php endif; ?>

        </tbody>

    </table>

</div>


</body>

</html>
