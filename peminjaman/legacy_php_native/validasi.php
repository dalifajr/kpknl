<?php
include 'db.php';
$query = mysqli_query($conn, "SELECT * FROM risalah_pending WHERE status='belum_validasi'");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Risalah Pending</title>
    <style>
        /* Style umum */
        body {
            font-family: Arial, sans-serif;
            background-color: #d9edf2;
            padding: 20px;
            margin: 0;
        }

        /* Judul */
        h2 {
            margin: 20px 0;
            font-size: 20px;
            color: #333;
            text-align: center;
        }

        /* Wrapper agar tabel bisa scroll di HP */
        .table-container {
            width: 100%;
            overflow-x: auto;
        }

        /* Tabel */
        table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            min-width: 800px;
        }

        /* Header tabel */
        table th {
            background: #0a3d62;
            color: #fff;
            text-align: left;
            padding: 12px;
            font-size: 14px;
        }

        /* Isi tabel */
        table td {
            padding: 10px 12px;
            border-bottom: 1px solid #ddd;
            font-size: 14px;
            color: #333;
        }

        /* Hover effect */
        table tr:hover {
            background: #f1f7fc;
        }

        /* Tombol aksi tabel */
        table a {
            display: inline-block;
            padding: 6px 12px;
            background: #0a3d62;
            color: #fff;
            text-decoration: none;
            border-radius: 6px;
            font-size: 13px;
            transition: background 0.3s ease;
        }

        table a:hover {
            background: #3c6382;
        }

        /* Tombol kembali pojok kanan atas */
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

<h2>Daftar Risalah Pending</h2>

<div class="table-container">
<table border="1" cellpadding="10">
    <tr>
        <th>No</th>
        <th>No Risalah / No. Register</th>
        <th>Pelelang</th>
        <th>Pemohon</th>
        <th>Jenis</th>
        <th>Tanggal</th>
        <th>Aksi</th>
    </tr>
<?php $no=1; while($row = mysqli_fetch_assoc($query)) { ?>
    <tr>
        <td><?= $no++ ?></td>
        <td><?= $row['no_risalah'] ?></td>
        <td><?= $row['nama_pelelang'] ?></td>
        <td><?= $row['pemohon_lelang'] ?></td>
        <td><?= $row['jenis'] ?></td>
        <td><?= $row['tgl_risalah'] ?></td>
        <td>
            <a href="form_validasi.php?id=<?= $row['id'] ?>">Validasi</a>
        </td>
    </tr>
<?php } ?>
</table>
</div>

</body>
</html>
