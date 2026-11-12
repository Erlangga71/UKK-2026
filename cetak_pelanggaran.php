<?php
require_once "cek_role.php";
wajib_role(['admin']);
include "database.php";
?>

<h2>Cetak / Export</h2>
<a href="dashboard.php">Kembali</a><br><br>

<button type="button" onclick="window.print()">Cetak Laporan (Print)</button>
<button type="button" onclick="window.location.href='laporan.php'">Export ke Excel (buka laporan, Ctrl+S)</button>
