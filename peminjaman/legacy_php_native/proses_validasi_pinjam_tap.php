<?php
include 'db.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $tgl_peminjaman = date('Y-m-d');

    $update = "UPDATE peminjaman_tap 
               SET status = 'Sedang Dipinjam', tgl_peminjaman = '$tgl_peminjaman'
               WHERE id = $id";

    if ($conn->query($update) === TRUE) {
        header("Location: admin_tap.php?success=1");
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
}
$conn->close();
?>
