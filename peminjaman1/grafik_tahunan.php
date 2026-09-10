<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Grafik Tahunan</title>
  <link rel="stylesheet" href="style_dashboard.css">
  <style>
    .container {
      width: 80%;
      margin: auto;
      text-align: center;
      padding: 20px;
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
    }
    .menu button:hover {
      background-color: #1a3945;
    }

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
  <button class="back-button" onclick="goBack()">Kembali</button>
  <div class="container">
    <h2>GRAFIK TAHUNAN</h2>
    <div class="menu">
      <button onclick="window.location.href='grafik_tahunan_pelelangan.php'">Grafik Tahunan Pelelangan</button>
      <button onclick="window.location.href='grafik_tahunan_peminjaman.php'">Grafik Tahunan Peminjaman</button>
    </div>
  </div>
  <script>
    function goBack() {
      window.history.back();
    }
  </script>
</body>
</html>