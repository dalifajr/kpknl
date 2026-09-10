<?php
session_start();
include "db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)$_POST['id'];

    $sql = "UPDATE peminjaman 
            SET status='Proses Pengembalian', tgl_pengembalian=NOW() 
            WHERE id='$id'";
    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Pengembalian diajukan, menunggu verifikasi admin.');window.location.href='cek_pinjaman.php';</script>";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>
