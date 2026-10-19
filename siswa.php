<?php

require_once "cek_role.php";

wajib_role(['admin']);

require_once "database.php";


// HAPUS DATA
if (isset($_GET['hapus'])) {

    $id = (int) $_GET['hapus'];

    mysqli_query(
        $koneksi,
        "DELETE FROM siswa WHERE id = $id"
    );

    header("Location: siswa.php");
    exit;
}


// AMBIL DATA SISWA
$query = mysqli_query(
    $koneksi,
    "SELECT * FROM siswa ORDER BY id DESC"
);

?>

<!DOCTYPE html>
<html>

<head>

    <title>Data Siswa</title>

    <!-- DataTables CSS -->
    <link rel="stylesheet"
          href="https://cdn.datatables.net/2.3.4/css/dataTables.dataTables.min.css">

    <link rel="stylesheet" href="style.css">

</head>

<body>


<!-- HEADER -->

<div class="header">

    <b>SISTEM INFORMASI PELANGGARAN SISWA</b>

    <a href="dashboard.php">
        Kembali
    </a>

</div>


<div class="container">

    <div class="box">

        <h2>Data Siswa</h2>

        <a href="tambah_siswa.php" class="btn-tambah">
            + Tambah Siswa
        </a>


        <div style="overflow-x:auto;">

            <table id="tabelSiswa">

                <thead>

                    <tr>

                        <th>No</th>

                        <th>NIS</th>

                        <th>Nama</th>

                        <th>Jenis Kelamin</th>

                        <th>Alamat</th>

                        <th>No HP Orang Tua</th>

                        <th>Status</th>

                        <th>Aksi</th>

                    </tr>

                </thead>


                <tbody>

                <?php

                $no = 1;

                while ($siswa = mysqli_fetch_assoc($query)) {

                ?>

                    <tr>

                        <td>
                            <?= $no++; ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($siswa['nis']); ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($siswa['nama']); ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($siswa['jenis_kelamin'] ?? '-'); ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($siswa['alamat'] ?? '-'); ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($siswa['no_hp_ortu'] ?? '-'); ?>
                        </td>

                        <td>

                            <?php if ($siswa['aktif'] == 1) { ?>

                                Aktif

                            <?php } else { ?>

                                Tidak Aktif

                            <?php } ?>

                        </td>

                        <td>

                            <a
                                href="edit_siswa.php?id=<?= $siswa['id']; ?>"
                                class="btn-edit"
                            >
                                Edit
                            </a>

                            |

                            <a
                                href="siswa.php?hapus=<?= $siswa['id']; ?>"
                                class="btn-hapus"
                                onclick="return confirm('Yakin ingin menghapus data siswa ini?')"
                            >
                                Hapus
                            </a>

                        </td>

                    </tr>

                <?php } ?>

                </tbody>

            </table>

        </div>

    </div>

</div>


<!-- jQuery -->

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>


<!-- DataTables JS -->

<script src="https://cdn.datatables.net/2.3.4/js/dataTables.min.js"></script>


<script>

document.addEventListener("DOMContentLoaded", function () {

    new DataTable('#tabelSiswa');

});

</script>


</body>

</html>