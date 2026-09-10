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
  <title>Dashboard Peminjam</title>
  <link rel="stylesheet" href="style_dashboard.css">
</head>
<body>
  <div class="container">
    <img src="logo.png" alt="Logo" class="logo">
    <h2>Selamat Datang, <?php echo htmlspecialchars($_SESSION['username']); ?> (USER PEMINJAM)</h2>
    <div class="logout"><a href="logout.php">Log Out</a></div>
    

    <div class="menu">
      <button onclick="window.location.href='pilih_pinjam.php'">Pinjam</button>
      <button onclick="window.location.href='cek_pinjaman.php'">Cek Data Pinjaman</button>
    </div>
  </div>
</body>
</html>
