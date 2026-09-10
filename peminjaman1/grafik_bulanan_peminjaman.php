<?php
// Koneksi Database
$host = "localhost";
$user = "root";
$pass = "";
$db   = "kpknl"; // pastikan sesuai

$conn = mysqli_connect($host, $user, $pass, $db);
if(!$conn){
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Ambil data jumlah per bulan (tanpa nama peminjam)
$query = mysqli_query($conn, "
    SELECT YEAR(tgl_peminjaman) AS tahun, MONTH(tgl_peminjaman) AS bulan, COUNT(*) AS total
    FROM peminjaman
    GROUP BY YEAR(tgl_peminjaman), MONTH(tgl_peminjaman)
    ORDER BY tahun, bulan
");

$dataBulanan = [];
while($row = mysqli_fetch_assoc($query)){
    $tahun  = $row['tahun'];
    $bulan  = $row['bulan'];
    $total  = $row['total'];

    $dataBulanan[$tahun][$bulan] = $total;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Grafik Bulanan Peminjaman (Total)</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            background:#f4f8fb; 
            margin: 0; 
            padding: 0;
            position: relative;
        }
        .container { 
            width:80%; 
            margin:30px auto; 
            background:#fff; 
            padding:20px; 
            border-radius:10px; 
            box-shadow:0 0 10px rgba(0,0,0,0.1); 
            position: relative;
        }
        .btn-container { 
            display:flex; 
            justify-content:center; 
            gap:10px; 
            margin-bottom:20px; 
            flex-wrap: wrap; 
        }
        .btn-tahun { 
            padding:8px 15px; 
            background:#007bff; 
            color:white; 
            border:none; 
            border-radius:5px; 
            cursor:pointer; 
            transition:0.3s; 
        }
        .btn-tahun:hover { background:#0056b3; }
        .btn-active { background:#28a745 !important; }
        h2 { text-align:center; }

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
        }
        .back-button:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
<div class="container">
    <!-- Tombol Kembali -->
    <button class="back-button" onclick="goBack()">Kembali</button>

    <h2>GRAFIK BULANAN PEMINJAMAN (TOTAL SEMUA PEMINJAM)</h2>

    <?php if(!empty($dataBulanan)): ?>
        <div class="btn-container">
            <?php foreach($dataBulanan as $tahun => $bulanData): ?>
                <button class="btn-tahun" onclick="showChart(<?= $tahun; ?>, this)"><?= $tahun; ?></button>
            <?php endforeach; ?>
        </div>

        <canvas id="chartBulanan" width="900" height="450"></canvas>

        <script>
            const allCharts = <?= json_encode($dataBulanan); ?>;
            const ctx = document.getElementById('chartBulanan').getContext('2d');
            let chartInstance;

            function showChart(tahun, el){
                const bulanLabels = ['JAN','FEB','MAR','APR','MEI','JUN','JUL','AGS','SEP','OKT','NOV','DES'];
                const tahunData = allCharts[tahun];

                const dataBulan = Array(12).fill(0);
                for (let bulan in tahunData){
                    dataBulan[bulan-1] = tahunData[bulan];
                }

                if(chartInstance){ chartInstance.destroy(); }

                chartInstance = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: bulanLabels,
                        datasets: [{
                            label: 'Total Peminjaman',
                            data: dataBulan,
                            backgroundColor: 'rgba(54, 162, 235, 0.7)',
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 1
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

                // ubah tombol aktif
                const buttons = document.querySelectorAll('.btn-tahun');
                buttons.forEach(btn => btn.classList.remove('btn-active'));
                el.classList.add('btn-active');
            }

            // tampilkan tahun pertama default
            const firstYear = Object.keys(allCharts)[0];
            const firstBtn = document.querySelectorAll('.btn-tahun')[0];
            showChart(firstYear, firstBtn);
            firstBtn.classList.add('btn-active');

            function goBack(){
                window.history.back();
            }
        </script>
    <?php else: ?>
        <p style="text-align:center;">Belum ada data peminjaman.</p>
    <?php endif; ?>
</div>
</body>
</html>