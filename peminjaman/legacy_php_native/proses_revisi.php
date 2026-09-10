<?php
include 'db.php';
$id = $_POST['id'];
$catatan = $_POST['catatan'];

mysqli_query($conn, "UPDATE risalah_pending SET status='dikembalikan', catatan='$catatan' WHERE id=$id");

header("Location: validasi.php");
