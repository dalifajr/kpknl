<?php
$host = "localhost";
$user = "root";
$pass = "";
$db = "kpknl";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Ambil filter jenis risalah
$filter = isset($_GET['jenis']) ? $_GET['jenis'] : 'minuta';
$table = "risalah_minuta"; // default
if ($filter === 'tap') {
    $table = "risalah_tap";
} elseif ($filter === 'batal') {
    $table = "risalah_batal";
}

// Ambil data: kelompokkan berdasarkan tahun
$sql = "SELECT YEAR(tgl_risalah) AS tahun, COUNT(*) AS jumlah 
        FROM $table
        GROUP BY YEAR(tgl_risalah) 
        ORDER BY tahun ASC";
$result = $conn->query($sql);

$tahun = [];
$jumlah = [];

while ($row = $result->fetch_assoc()) {
    $tahun[] = $row['tahun'];
    $jumlah[] = $row['jumlah'];
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Grafik Tahunan Pelelangan</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background-color: #dbeef5;
            font-family: Arial, sans-serif;
            text-align: center;
            padding: 20px;
            margin-top: 50px;
        }
        .chart-container {
            width: 80%;
            margin: auto;
            background: #fff;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        h2 {
            color: #1a3945;
        }
        .filter-container {
            margin-bottom: 20px;
        }
        select {
            padding: 10px;
            font-size: 16px;
            border-radius: 8px;
            border: 1px solid #1a3945;
        }
        /* ✅ Tombol kembali */
        .back-button {
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: #28a745;
            color: white;
            padding: 10px 15px;
            border-radius: 8px;
            font-size: 16px;
            text-decoration: none;
            cursor: pointer;
            z-index: 999;
            transition: 0.3s;
        }
        .back-button:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
    <!-- ✅ Tombol kembali -->
    <a href="cek_grafik.php" class="back-button">Kembali</a>

    <div class="chart-container">
        <h2>GRAFIK TAHUNAN PELELANGAN</h2>

        <!-- ✅ Dropdown filter -->
        <div class="filter-container">
            <form method="GET" id="filterForm">
                <label for="jenis"><b>Pilih Jenis Risalah:</b></label>
                <select name="jenis" id="jenis" onchange="document.getElementById('filterForm').submit()">
                    <option value="minuta" <?= $filter === 'minuta' ? 'selected' : '' ?>>Minuta</option>
                    <option value="tap" <?= $filter === 'tap' ? 'selected' : '' ?>>TAP</option>
                    <option value="batal" <?= $filter === 'batal' ? 'selected' : '' ?>>Batal</option>
                </select>
            </form>
        </div>

        <canvas id="grafik"></canvas>
    </div>

    <script>
        const ctx = document.getElementById('grafik').getContext('2d');
        const myChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($tahun); ?>,
                datasets: [{
                    label: 'Jumlah Pelelangan (<?= ucfirst($filter); ?>)',
                    data: <?php echo json_encode($jumlah); ?>,
                    backgroundColor: 'rgba(26, 57, 69, 0.7)',
                    borderColor: '#1a3945',
                    borderWidth: 2,
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { precision:0 }
                    }
                }
            }
        });
    </script>
</body>
</html>