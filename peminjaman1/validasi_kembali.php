<?php
include 'db.php';

$id = isset($_GET['id']) ? $_GET['id'] : '';

if ($id == '') {
    die("ID tidak ditemukan di URL.");
}

// Ambil data dari tabel risalah_revisi
$query = "SELECT * FROM risalah_revisi WHERE id='$id'";
$result = mysqli_query($conn, $query);

if (!$result) {
    die("Query gagal: " . mysqli_error($conn));
}

$data = mysqli_fetch_assoc($result);

if (!$data) {
    die("Data tidak ditemukan untuk ID: $id");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Validasi Kembali Risalah</title>
<link rel="stylesheet" href="style_dashboard.css">
<style>
.container {
    width: 600px;
    margin: 30px auto;
    background: #fff;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 0 10px #ccc;
    font-family: Arial, sans-serif;
}
label {
    font-weight: bold;
    display: block;
    margin-top: 10px;
}
input, textarea {
    width: 100%;
    padding: 8px;
    margin: 6px 0;
    border: 1px solid #ccc;
    border-radius: 5px;
}
textarea[disabled] {
    background: #f5f5f5;
}
button {
    padding: 10px 15px;
    margin-top: 10px;
    border: none;
    cursor: pointer;
    border-radius: 5px;
}
.btn-valid {
    background: #28a745;
    color: #fff;
}
.btn-cancel {
    background: #dc3545;
    color: #fff;
    margin-left: 10px;
}
</style>
</head>
<body>
<div class="container">
<h2>Validasi Kembali Risalah</h2>
<form action="proses_validasi_kembali.php" method="POST">
    <input type="hidden" name="id" value="<?php echo $data['id']; ?>">

    <!-- No Risalah -->
    <label>No Risalah:</label>
    <input type="text" name="no_risalah" value="<?php echo $data['no_risalah']; ?>" required>
    <textarea disabled><?php echo $data['catatan_no']; ?></textarea>

    <!-- Jenis -->
    <label>Jenis:</label>
    <input type="text" name="jenis" value="<?php echo $data['jenis']; ?>" required>
    <textarea disabled><?php echo $data['catatan_jenis']; ?></textarea>

    <!-- Tanggal Risalah -->
    <label>Tanggal Risalah:</label>
    <input type="date" name="tgl_risalah" value="<?php echo $data['tgl_risalah']; ?>" required>
    <textarea disabled><?php echo $data['catatan_tgl']; ?></textarea>

    <!-- Nama Pelelang -->
    <label>Pelelang:</label>
    <input type="text" name="nama_pelelang" value="<?php echo $data['nama_pelelang']; ?>" required>
    <textarea disabled><?php echo $data['catatan_pelelang']; ?></textarea>

    <!-- Pemohon Lelang -->
    <label>Pemohon Lelang:</label>
    <input type="text" name="pemohon_lelang" value="<?php echo $data['pemohon_lelang']; ?>" required>
    <textarea disabled><?php echo $data['catatan_pemohon']; ?></textarea>

    <br>
    <button type="submit" class="btn-valid" name="validasi_kembali">Validasi Kembali</button>
    <a href="cek_revisi.php" class="btn-cancel" style="text-decoration:none; display:inline-block; text-align:center;">Batal</a>
</form>
</div>
</body>
</html>
