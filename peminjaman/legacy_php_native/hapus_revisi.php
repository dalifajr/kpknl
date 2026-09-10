<?php
session_start();
include 'db.php';

// pastikan user login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'pelelang') {
    header("Location: index.html");
    exit;
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id > 0) {
    $query = "DELETE FROM risalah_revisi WHERE id = $id";
    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Data revisi berhasil dihapus!'); window.location.href='cek_revisi.php';</script>";
    } else {
        echo "<script>alert('Gagal menghapus data: ".mysqli_error($conn)."'); window.location.href='cek_revisi.php';</script>";
    }
} else {
    echo "<script>alert('ID tidak valid!'); window.location.href='cek_revisi.php';</script>";
}
?>
