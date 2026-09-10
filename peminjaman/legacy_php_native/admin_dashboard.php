<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: index.html");
    exit;
}
include 'db.php';
 // ANAK MAGANG UIN RADEN FATAH PALEMBANG 
//AWANG, AULIA, DINDA, MESYA, YONIZA
$pendingCountQuery = mysqli_query($conn, "SELECT COUNT(*) as total FROM risalah_pending");
$pendingCount = mysqli_fetch_assoc($pendingCountQuery)['total'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Dashboard Admin</title>
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
    <header>
      <img src="logo.png" alt="Logo" class="logo">
      <h2>USER ADMIN</h2>
      <div class="logout">
        <a href="logout.php">Log Out</a>
      </div>
    </header>

    <main>
      <div class="menu">
        <div style="position:relative;">
          <button class="btn-notif" onclick="location.href='validasi.php'">Validasi Risalah Lelang Baru</button>
          <?php if ($pendingCount > 0): ?>
            <span class="badge"><?php echo $pendingCount; ?></span>
          <?php endif; ?>
        </div>

        <button onclick="location.href='menu_risalah.php'">Cek Data Non Minuta</button>
        <button onclick="location.href='cek_data_risalah.php'">Cek Data Risalah</button>
        <button onclick="location.href='peminjam.php'">Cek Data Peminjam</button>
        <button onclick="location.href='cek_grafik.php'">Cek Grafik</button>
      </div>
    </main>
  </div>
</body>
</html>
