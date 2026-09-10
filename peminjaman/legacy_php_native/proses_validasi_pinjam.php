<?php
include "db.php";

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];

    // Admin validasi → ubah status jadi menunggu konfirmasi peminjam
    $query = "UPDATE peminjaman 
              SET status = 'Menunggu Konfirmasi Peminjam' 
              WHERE id = $id";
    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Peminjaman divalidasi, menunggu konfirmasi peminjam.'); window.location='peminjam.php';</script>";
    } else {
        echo "<script>alert('Gagal memvalidasi peminjaman.'); window.location='peminjam.php';</script>";
    }
}
?>
