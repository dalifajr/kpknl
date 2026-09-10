<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Grafik Bulanan</title>
  <link rel="stylesheet" href="style_dashboard.css">
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #dbeef5;
      margin: 0;
      padding: 0;
      position: relative;
    }
    .container {
      width: 80%;
      margin: auto;
      text-align: center;
      padding: 20px;
    }
    h2 {
      font-size: 28px;
      color: #1a3945;
    }
    .menu {
      display: flex;
      flex-direction: column;
      gap: 15px;
      margin-top: 30px;
    }
    .menu button {
      background-color: #355767;
      color: #fff;
      border: none;
      padding: 15px;
      font-size: 18px;
      border-radius: 5px;
      cursor: pointer;
      transition: background 0.3s;
    }
    .menu button:hover {
      background-color: #1a3945;
    }
    /* Tombol Kembali */
    .back-button {
      position: absolute;
      top: 20px;
      right: 20px;
      background-color: #28a745;
      color: white;
      padding: 10px 15px;
      border: none;
      border-radius: 8px;
      font-size: 16px;
      cursor: pointer;
      text-decoration: none;
      transition: background 0.3s;
    }
    .back-button:hover {
      background-color: #218838;
    }
  </style>
</head>
<body>
  <!-- Tombol Kembali -->
  <button class="back-button" onclick="goBack()">Kembali</button>

  <div class="container">
    <h2>GRAFIK BULANAN</h2>
    <div class="menu">
      <button onclick="window.location.href='grafik_bulanan_pelelangan.php'">Grafik Bulanan Pelelangan</button>
      <button onclick="window.location.href='grafik_bulanan_peminjaman.php'">Grafik Bulanan Peminjaman</button>
    </div>
  </div>

  <script>
    function goBack() {
      window.history.back();
    }
  </script>
</body>
</html>