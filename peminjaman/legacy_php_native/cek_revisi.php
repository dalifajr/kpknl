<?php
session_start();
include 'db.php';

// Pastikan user login dan role adalah pelelang
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'pelelang') {
    header("Location: index.html");
    exit;
}

$username = $_SESSION['username']; // Ambil username dari session
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Revisi Risalah</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            background: #d9edf2; /* konsisten */
            margin: 0; 
            padding: 20px;
        }

        h2 { 
            text-align: center; 
            margin: 20px 0; 
            color: #0a3d62;
        }

        table {
            width: 95%;
            margin: 20px auto;
            border-collapse: collapse;
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }

        th {
            background: #0a3d62;
            color: white;
            padding: 12px;
            font-size: 14px;
        }

        td {
            padding: 10px;
            text-align: center;
            border-bottom: 1px solid #ddd;
            font-size: 14px;
            color: #333;
        }

        tr:hover {
            background-color: #f1f7fc;
        }

        /* Tombol kembali */
        .back-btn {
            display: block;
            width: 200px;
            margin: 0 auto 20px;
            padding: 10px;
            background: #28a745;
            color: white;
            text-align: center;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            transition: 0.3s;
        }

        .back-btn:hover {
            background: #218838;
        }

        /* Tombol validasi kembali */
        .btn-validasi {
            display: inline-block;
            padding: 6px 12px;
            background: #ffc107;
            color: #000;
            border: none;
            cursor: pointer;
            border-radius: 6px;
            font-size: 13px;
            text-decoration: none;
            transition: 0.3s;
        }

        .btn-validasi:hover {
            background: #e0a800;
        }

        /* Tombol hapus */
        .btn-hapus {
            display: inline-block;
            padding: 6px 12px;
            background: #dc3545;
            color: #fff;
            border: none;
            cursor: pointer;
            border-radius: 6px;
            font-size: 13px;
            text-decoration: none;
            transition: 0.3s;
            margin-left: 5px;
        }

        .btn-hapus:hover {
            background: #c82333;
        }

        p {
            text-align: center;
            color: #555;
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
    <h2>Data Risalah yang Perlu Direvisi</h2>
    <div class="kembali">
        <a href="pelelang_dashboard.php">← Kembali</a>
    </div>

<?php
$query = "SELECT * FROM risalah_revisi WHERE nama_pelelang = '$username' ORDER BY tgl_revisi DESC";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) > 0) {
    echo "<table>
            <tr>
                <th>No Risalah</th>
                <th>Jenis</th>
                <th>Tanggal Risalah</th>
                <th>Tanggal Revisi</th>
                <th>Pemohon Lelang</th>
                <th>Aksi</th>
            </tr>";

    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>
                <td>{$row['no_risalah']}</td>
                <td>{$row['jenis']}</td>
                <td>{$row['tgl_risalah']}</td>
                <td>{$row['tgl_revisi']}</td>
                <td>{$row['pemohon_lelang']}</td>
                <td>
                    <a href='validasi_kembali.php?id={$row['id']}' class='btn-validasi'>Validasi Kembali</a>
                    <a href='hapus_revisi.php?id={$row['id']}' class='btn-hapus' onclick=\"return confirm('Yakin ingin menghapus data ini?');\">Hapus</a>
                </td>
            </tr>";
    }
    echo "</table>";
} else {
    echo "<p>Tidak ada data revisi untuk Anda.</p>";
}
?>
</body>
</html>
