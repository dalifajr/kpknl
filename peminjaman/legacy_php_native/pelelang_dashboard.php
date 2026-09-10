<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'pelelang') {
    header("Location: index.html");
    exit;
}
include 'db.php';

// Ambil username pelelang dari session
$username = $_SESSION['username'];

// Hitung jumlah revisi untuk pelelang ini (status apapun)
$revisiCountQuery = mysqli_query($conn, 
    "SELECT COUNT(*) as total 
     FROM risalah_revisi 
     WHERE nama_pelelang = '$username'"
);
$revisiCount = mysqli_fetch_assoc($revisiCountQuery)['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Dashboard Pelelang</title>
  <link rel="stylesheet" href="style_dashboard.css">
  <style>
    .menu {
      display: flex;
      flex-direction: column;
      gap: 15px;
      position: relative;
    }
    .btn-notif {
      position: relative;
      padding: 10px 14px;
      background: #1a3945;
      color: #fff;
      border-radius: 6px;
      border: none;
      cursor: pointer;
      font-weight: bold;
    }
    .btn-notif:hover {
      background: #07446c;
    }
    .badge {
      position: absolute;
      top: -8px;
      right: -8px;
      background-color: red;
      color: white;
      border-radius: 50%;
      padding: 5px 10px;
      font-size: 14px;
      font-weight: bold;
    }
  </style>
</head>
<body>
  <div class="container">
    <img src="logo.png" alt="Logo" class="logo">
    <h2>USER PELELANG</h2>
    <div class="logout"><a href="logout.php">Log Out</a></div>
    

    <div class="menu">
      <button onclick="window.location.href='tambah_risalah.php'">Tambah Risalah</button>

      <div style="position:relative;">
        <button class="btn-notif" onclick="window.location.href='cek_revisi.php'">
          Cek Data yang Perlu Di Revisi
        </button> 
        <?php if ($revisiCount > 0): ?>
          <span class="badge"><?php echo $revisiCount; ?></span>
        <?php endif; ?>
      </div>
    <div style="position:relative;">
        <button class="btn-notif" onclick="window.location.href='pelelang_pending.php'">
            Risalah Pending (Menunggu Validasi)
        </button>
    </div>
    <button onclick="window.location.href='riwayat_risalah.php'">Riwayat Risalah</button>
    </div>
  </div>
</body>
</html>