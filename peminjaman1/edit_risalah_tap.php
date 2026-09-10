<?php
include 'db.php';

// Ambil ID dari URL
$id = isset($_GET['id']) ? $_GET['id'] : '';

if ($id == '') {
    echo "ID tidak ditemukan!";
    exit;
}

// Ambil data dari database
$query = mysqli_query($conn, "SELECT * FROM risalah_tap WHERE id = '$id'");
$data = mysqli_fetch_assoc($query);

if (!$data) {
    echo "Data tidak ditemukan!";
    exit;
}

// Proses update jika form disubmit
if (isset($_POST['update'])) {
    $box = $_POST['box'];
    $lemari = $_POST['lemari'];

    $update = mysqli_query($conn, "UPDATE risalah_tap SET 
        box = '$box',
        lemari = '$lemari'
        WHERE id = '$id'
    ");

    if ($update) {
        echo "<script>alert('Data berhasil diupdate!'); window.location='cek_data_tap.php';</script>";
    } else {
        echo "<script>alert('Gagal update data!');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Data Risalah TAP</title>
<style>
    body {font-family: Arial, sans-serif; background-color: #DBEEF5; padding: 20px;}
    .container {width: 60%; margin: auto; background: white; padding: 20px; border-radius: 10px;}
    h2 {text-align: center; margin-bottom: 20px;}
    form input {width: 100%; padding: 10px; margin: 8px 0; border: 1px solid #ccc; border-radius: 6px;}
    form input[readonly] {background-color: #f5f5f5; color: #666;}
    button {padding: 10px 20px; background-color: #1a3945; color: white; border: none; border-radius: 8px; cursor: pointer;}
    button:hover {background-color: #2c5a6e;}
</style>
</head>
<body>
<div class="container">
    <h2>Edit Data Risalah TAP (Hanya Box & Lemari)</h2>
    <form method="POST">
        <label>No Risalah:</label>
        <input type="text" value="<?= $data['no_risalah'] ?>" readonly>

        <label>Tanggal Risalah:</label>
        <input type="text" value="<?= $data['tgl_risalah'] ?>" readonly>

        <label>Tanggal Validasi:</label>
        <input type="text" value="<?= $data['tgl_validasi'] ?>" readonly>

        <label>Nama Pelelang:</label>
        <input type="text" value="<?= $data['nama_pelelang'] ?>" readonly>

        <label>Pemohon Lelang:</label>
        <input type="text" value="<?= $data['pemohon_lelang'] ?>" readonly>

        <label>Status:</label>
        <input type="text" value="<?= $data['status'] ?>" readonly>

        <label>Box:</label>
        <input type="text" name="box" value="<?= $data['box'] ?>" required>

        <label>Lemari:</label>
        <input type="text" name="lemari" value="<?= $data['lemari'] ?>" required>

        <button type="submit" name="update">Update</button>
    </form>
</div>
</body>
</html>
