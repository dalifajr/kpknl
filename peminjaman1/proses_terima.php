<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'peminjam') {
    header("Location: index.html");
    exit;
}

if (isset($_POST['id'])) {
    $id = (int)$_POST['id'];
    $tglPinjam = date("Y-m-d");

    // Update status jadi Sedang Dipinjam + set tgl_peminjaman
    $query = "UPDATE peminjaman 
              SET status = 'Sedang Dipinjam', tgl_peminjaman = '$tglPinjam' 
              WHERE id = $id";

    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Risalah berhasil diterima. Lama pinjam mulai dihitung.'); window.location='cek_pinjaman.php';</script>";
    } else {
        echo "<script>alert('Gagal menerima risalah.'); window.location='cek_pinjaman.php';</script>";
    }
}
?>
