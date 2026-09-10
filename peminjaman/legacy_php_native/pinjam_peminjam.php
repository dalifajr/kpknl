<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pinjam Berkas</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #d9f0fa;
            font-family: Arial, sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .header {
            width: 100%;
            display: flex;
            justify-content: flex-end;
            align-items: center;
            padding: 20px 40px;
            font-size: 16px;
        }
        .header a {
            color: #1a3945;
            text-decoration: none;
            font-weight: bold;
        }
        .icon-container {
            margin-top: 50px;
            text-align: center;
        }
        .icon-container img {
            width: 150px;
            height: auto;
        }
        .menu-container {
            display: flex;
            gap: 20px;
            margin-top: 40px;
        }
        .menu-box {
            width: 160px;
            height: 160px;
            background-color: #0369a1;
            color: #fff;
            font-size: 16px;
            font-weight: bold;
            border-radius: 8px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        .menu-box:hover {
            background-color: #355b69;
        }
        .menu-box .plus {
            font-size: 40px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <span style="margin-right: 10px;">&#128100;</span>
        <a href="logout.php">Log Out &#10145;</a>
    </div>
    
    <div class="icon-container">
        <img src="logo.jpg" alt="Folder Icon"> <!-- Ganti dengan icon folder palu -->
    </div>

    <div class="menu-container">
        <div class="menu-box" onclick="window.location.href='pinjam_risalah.php'">
            <div class="plus">+</div>
            Pinjam Risalah Minuta
        </div>
        <div class="menu-box" onclick="window.location.href='pinjam_risalah_tap.php'">
            <div class="plus">+</div>
            Pinjam Berkas TAP
        </div>
        <div class="menu-box" onclick="window.location.href='pinjam_risalah_batal.php'">
            <div class="plus">+</div>
            Pinjam Berkas Batal
        </div>
    </div>
</body>
</html>