<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'peminjam') {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Pilih Jenis Pinjam</title>
  <link rel="stylesheet" href="style_dashboard.css">
  <style>
    .menu-container {
      display: flex;
      flex-direction: column;
      gap: 15px;
      margin-top: 30px;
    }
    .menu-container button {
      padding: 12px;
      border: none;
      border-radius: 8px;
      background-color: #1a3945;
      color: white;
      font-size: 16px;
      cursor: pointer;
      transition: 0.3s;
    }
    .menu-container button:hover {
      background-color: #1a3945;
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>Pilih Jenis Pinjam</h2>
    <p>Silakan pilih jenis berkas yang ingin dipinjam:</p>

    <div class="menu-container">
      <button onclick="window.location.href='pinjam_risalah.php'">Pinjam Risalah Minuta</button>
      <button onclick="window.location.href='pinjam_risalah_batal.php'">Pinjam Berkas Batal</button>
      <button onclick="window.location.href='pinjam_risalah_tap.php'">Pinjam Berkas Tap</button>
    </div>

    <br>
    <a href="peminjam_dashboard.php">⬅ Kembali ke Dashboard</a>
  </div>
</body>
</html>
