<?php
include 'db.php';
$id = $_GET['id'];
$data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM risalah_pending WHERE id=$id"));
?>

<h2>Revisi Risalah (<?= $data['jenis'] ?>)</h2>
<form method="POST" action="proses_revisi.php">
    <input type="hidden" name="id" value="<?= $id ?>">
    <p>No Risalah: <?= $data['no_risalah'] ?></p>
    <p>Catatan Revisi:</p>
    <textarea name="catatan" required></textarea>
    <button type="submit">Kembalikan</button>
</form>
