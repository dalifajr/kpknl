<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Menu Risalah</title>
<style>
    body {
        font-family: Arial, sans-serif;
        background: #DBEEF5;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        margin: 0;
    }
    .menu-container {
        background: #fff;
        padding: 40px;
        border-radius: 16px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        text-align: center;
        width: 400px;
    }
    h2 {
        margin-bottom: 20px;
        color: #1a3945;
    }
    .menu-btn {
        display: block;
        margin: 15px auto;
        padding: 14px;
        width: 100%;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        font-weight: bold;
        cursor: pointer;
        transition: 0.3s;
    }
    .btn-minuta { background: #1a3945; color: #fff; }
    .btn-minuta:hover { background: #244d5c; }

    .btn-tap { background: #1a3945; color: #fff; }
    .btn-tap:hover { background: #1a3945; }

    .btn-batal { background: #1a3945; color: #fff; }
    .btn-batal:hover { background: #1a3945; }

    .logout {
        margin-top: 20px;
        display: inline-block;
        text-decoration: none;
        color: #a32f2f;
        font-weight: bold;
    }
    .logout:hover { text-decoration: underline; }

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
<div class="menu-container">
    <h2>Menu Data Risalah</h2>
    <form action="cek_data_tap.php" method="get">
        <button type="submit" class="menu-btn btn-tap">✅ Cek Risalah TAP</button>
    </form>
    <form action="cek_data_batal.php" method="get">
        <button type="submit" class="menu-btn btn-batal">❌ Cek Risalah BATAL</button>
    </form>
</div>
</body>
</html>
