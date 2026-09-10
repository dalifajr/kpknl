<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Informasi Grafik</title>
<style>
    body {
        margin: 0;
        font-family: Arial, sans-serif;
        background-color: #e6f1f6;/*ANAK MAGANG UIN RADEN FATAH PALEMBANG*/
    /*AWANG, AULIA, DINDA, MESYA, YONIZA*/
    }
    .container {
        width: 80%;
        margin: 30px auto;
        background-color: #e6f1f6;
        padding: 20px;
        border-radius: 10px;
    }
    .header {
        background-color: #1a3945;
        color: white;
        text-align: center;
        padding: 15px;
        font-size: 20px;
        font-weight: bold;
        border-radius: 5px;
        margin-bottom: 20px;
    }
    .menu-list {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }
    .menu-item {
        display: flex;
        align-items: center;
        background-color: #1a3945;
        color: white;
        padding: 15px;
        font-size: 16px;
        font-weight: bold;
        border-radius: 5px;
        cursor: pointer;
        transition: background 0.3s;
    }
    .menu-item:hover {
        background-color: #536b75ff;
    }
    .icon {
        width: 30px;
        height: 30px;
        background-color: #e6f1f6;
        border-radius: 5px;
        margin-right: 15px;
    }
    .kembali {
            position: absolute;
            top: 20px;
            right: 20px;
        }

        .kembali a {
            display: inline-block;
            padding: 8px 14px;
            background: #28a745;
            color: #fff;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            transition: 0.3s;
        }

        .kembali a:hover {
            background: #218838;
        }
</style>
</head>
<body>
<div class="kembali">
    <a href="admin_dashboard.php">Kembali</a>
</div>
<div class="container">
    <div class="header">INFORMASI GRAFIK</div>
    <div class="menu-list">
        <div class="menu-item" onclick="location.href='grafik_bulanan.php'">
            <div class="icon"></div> Grafik Bulanan
        </div>
        <div class="menu-item" onclick="location.href='grafik_tahunan.php'">
            <div class="icon"></div> Grafik Tahunan
        </div>
        <div class="menu-item" onclick="location.href='grafik_pelelang.php'">
            <div class="icon"></div> Grafik Pelelang
        </div>

        <div class="menu-item" onclick="location.href='grafik_peminjam.php'">
            <div class="icon"></div> Grafik Peminjam
        </div>
    </div>
</div>

</body>
</html>
