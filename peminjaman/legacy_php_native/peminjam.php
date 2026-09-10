<?php
include 'db.php';

// Ambil data peminjaman
$query = "SELECT * FROM peminjaman ORDER BY id DESC";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Peminjaman Risalah</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #d9edf2;
            margin: 0;
            padding: 20px;
        }

        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
            font-size: 22px;
        }

        .table-container {
            width: 100%;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            min-width: 1100px;
        }

        th {
            background-color: #0a3d62;
            color: #fff;
            padding: 12px 15px;
            text-align: center;
            font-size: 14px;
        }

        td {
            padding: 10px 12px;
            border-bottom: 1px solid #ddd;
            font-size: 14px;
            text-align: center;
            color: #333;
        }

        tr:hover {
            background-color: #f1f7fc;
        }

        /* Tombol */
        .btn {
            display: inline-block;
            padding: 6px 12px;
            text-decoration: none;
            border-radius: 6px;
            font-size: 13px;
            transition: background 0.3s ease;
            color: #fff;
        }

        .btn-validasi {
            background-color: #28a745;
        }

        .btn-validasi:hover {
            background-color: #218838;
        }

        .btn-disabled {
            background-color: #6c757d;
            cursor: not-allowed;
        }

        /* Lampu indikator */
        .lampu {
            padding: 4px 10px;
            border-radius: 6px;
            color: #fff;
            display: inline-block;
            min-width: 36px;
        }
        .hijau { background: green; }
        .kuning { background: orange; }
        .merah { background: red; }
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

<h1>Data Peminjaman Risalah</h1>

<div class="table-container">
<table>
    <tr>
        <th>No</th>
        <th>Nama Peminjam</th>
        <th>No Risalah / No. Register</th>
        <th>Tanggal Risalah</th>
        <th>Nama Pelelang</th>
        <th>Pemohon Lelang</th>
        <th>Box</th>
        <th>Lemari</th>
        <th>Tgl Peminjaman</th>
        <th>Tgl Pengembalian</th>
        <th>Status</th>
        <th>Lama Pinjam</th>
        <th>Terlambat</th>
        <th>Alasan Peminjaman</th>
        <th>Aksi</th>
    </tr>

    <?php
    $no = 1;
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $id     = $row['id'];
            $status = $row['status'];

            // --- Hitung Lama Pinjam (saat sedang dipinjam) ---
            $lamaPinjam = "-";
            $warna = "";
            if ($status === "Sedang Dipinjam" && !empty($row['tgl_peminjaman'])) {
                $tglPinjam = new DateTime($row['tgl_peminjaman']);
                $hariIni   = new DateTime();
                $diff      = (int)$hariIni->diff($tglPinjam)->days;

                if ($diff < 3) {
                    $lamaPinjam = "-" . (3 - $diff);
                    $warna = "hijau";
                } elseif ($diff === 3) {
                    $lamaPinjam = "0";
                    $warna = "kuning";
                } elseif ($diff > 3 && $diff < 4) {
                    $lamaPinjam = "+" . ($diff - 3);
                    $warna = "kuning";
                } else {
                    $lamaPinjam = "+" . ($diff - 3);
                    $warna = "merah";
                }
            }

            // --- Hitung Terlambat (hanya jika sudah dikembalikan) ---
            $terlambat = "-";
            if ($status === "Sudah Dikembalikan" && !empty($row['tgl_pengembalian']) && !empty($row['tgl_peminjaman'])) {
                $tglPinjam  = new DateTime($row['tgl_peminjaman']);
                $tglKembali = new DateTime($row['tgl_pengembalian']);
                $selisih    = (int)$tglKembali->diff($tglPinjam)->days;

                if ($selisih > 3) {
                    $terlambat = $selisih - 3;
                } else {
                    $terlambat = 0;
                }
            }

            echo "<tr>
                <td>$no</td>
                <td>{$row['nama_peminjam']}</td>
                <td>{$row['no_risalah']}</td>
                <td>{$row['tgl_risalah']}</td>
                <td>{$row['nama_pelelang']}</td>
                <td>{$row['pemohon_lelang']}</td>
                <td>{$row['box']}</td>
                <td>{$row['lemari']}</td>
                <td>" . ($row['tgl_peminjaman'] ?? '-') . "</td>
                <td>" . ($row['tgl_pengembalian'] ?? '-') . "</td>
                <td>$status</td>
                <td><span class='lampu $warna'>$lamaPinjam</span></td>
                <td>$terlambat hari</td>
                <td>{$row['alasan_peminjaman']}</td>
                <td>";

            // Tombol validasi sesuai status
            if ($status == 'Proses Peminjaman') {
                echo "<a class='btn btn-validasi' href='proses_validasi_pinjam.php?id=$id' onclick='return confirm(\"Validasi peminjaman ini?\")'>Validasi Peminjaman</a>";
            } elseif ($status == 'Proses Pengembalian') {
                echo "<a class='btn btn-validasi' href='admin_kembalikan.php?id=$id' onclick='return confirm(\"Validasi pengembalian ini?\")'>Validasi Pengembalian</a>";
            } else {
                echo "<span class='btn btn-disabled'>Selesai</span>";
            }

            echo "</td>
            </tr>";
            $no++;
        }
    } else {
        echo "<tr><td colspan='14'>Belum ada data peminjaman.</td></tr>";
    }
    ?>
</table>
</div>

</body>
</html>