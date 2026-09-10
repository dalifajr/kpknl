<?php
// Koneksi Database
$host = "localhost";
$user = "root";
$pass = "";
$db   = "kpknl"; // sesuaikan nama database

$conn = mysqli_connect($host, $user, $pass, $db);
if(!$conn){
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Ambil data jumlah per tahun
$query = mysqli_query($conn, "
    SELECT YEAR(tgl_peminjaman) AS tahun, COUNT(*) AS total
    FROM peminjaman
    GROUP BY YEAR(tgl_peminjaman)
    ORDER BY tahun ASC
");

$tahun = [];
$total = [];

while($row = mysqli_fetch_assoc($query)){
    $tahun[] = $row['tahun'];
    $total[] = $row['total'];
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Grafik Tahunan Peminjaman</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background:#f4f8fb;
            position: relative;
            padding: 20px;
        }
        .container {
            width:80%;
            margin:30px auto;
            background:#fff;
            padding:20px;
            border-radius:10px;
            box-shadow:0 0 10px rgba(0,0,0,0.1);
            text-align:center;
        }
        h2 { color:#264653; }
        .btn-kembali {
            position: absolute;
            top: 20px;
            right: 30px;
            background-color: #28a745;
            color: #fff;
            border: none;
            padding: 10px 18px;
            font-size: 14px;
            border-radius: 5px;
            cursor: pointer;
        }
        .btn-kembali:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
    <button class="btn-kembali" onclick="window.location.href='cek_grafik.php'">Kembali</button>
    <div class="container">
        <h2>GRAFIK TAHUNAN PEMINJAMAN</h2>
        <canvas id="chartTahunan" width="900" height="450"></canvas>

        <script>
            const ctx = document.getElementById('chartTahunan').getContext('2d');
            const chartTahunan = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: <?= json_encode($tahun); ?>,
                    datasets: [{
                        label: 'Total Peminjaman',
                        data: <?= json_encode($total); ?>,
                        borderColor: '#007bff',
                        backgroundColor: 'rgba(0,123,255,0.2)',
                        fill: true,
                        tension: 0.3,
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: '#007bff',
                        pointRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });
        </script>
    </div>
</body>
</html>