<?php
// Koneksi Database
$host = "localhost";
$user = "root";
$pass = "";
$db   = "kpknl"; // ganti sesuai nama database

$conn = mysqli_connect($host, $user, $pass, $db);
if(!$conn){
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Ambil nama peminjam untuk dropdown
$peminjamQuery = mysqli_query($conn, "SELECT DISTINCT nama_peminjam FROM peminjaman ORDER BY nama_peminjam ASC");

// Variabel untuk data grafik
$dataBulanan = [];
$dataTahunan = [];
$peminjam = "";
$jenis = "";

if(isset($_GET['peminjam']) && $_GET['peminjam'] != '' && isset($_GET['jenis'])){
    $peminjam = $_GET['peminjam'];
    $jenis = $_GET['jenis'];

    if($jenis == 'bulanan'){
        // Ambil data jumlah per bulan berdasarkan peminjam
        $query = mysqli_query($conn, "
            SELECT YEAR(tgl_peminjaman) AS tahun, MONTH(tgl_peminjaman) AS bulan, COUNT(*) AS total
            FROM peminjaman
            WHERE nama_peminjam = '$peminjam'
            GROUP BY YEAR(tgl_peminjaman), MONTH(tgl_peminjaman)
            ORDER BY tahun, bulan
        ");
        while($row = mysqli_fetch_assoc($query)){
            $dataBulanan[$row['tahun']][$row['bulan']] = $row['total'];
        }
    } elseif($jenis == 'tahunan'){
        // Ambil data jumlah per tahun
        $query = mysqli_query($conn, "
            SELECT YEAR(tgl_peminjaman) AS tahun, COUNT(*) AS total
            FROM peminjaman
            WHERE nama_peminjam = '$peminjam'
            GROUP BY YEAR(tgl_peminjaman)
            ORDER BY tahun
        ");
        while($row = mysqli_fetch_assoc($query)){
            $dataTahunan[] = $row;
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Grafik Peminjam</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: Arial, sans-serif; background:#f4f8fb; }
        .container { width:60%; margin:30px auto; background:#fff; padding:20px; border-radius:10px; box-shadow:0 0 10px rgba(0,0,0,0.1); }
        select, button { padding:10px; margin:10px 0; width:100%; }
        .btn-container {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 20px;
        }
        .btn-tahun {
            padding: 8px 15px;
            background:#007bff;
            color:white;
            border:none;
            border-radius:5px;
            cursor:pointer;
            transition: 0.3s;
        }
        .btn-tahun:hover { background:#0056b3; }
        .btn-active { background:#28a745 !important; }
        h2 { text-align:center; }
        body { 
    font-family: Arial, sans-serif; 
    background: #d9edf2; /* sama dengan halaman lain */
    color: #333;
}

.container { 
    width: 60%; 
    margin: 30px auto; 
    background: #fff; 
    padding: 20px; 
    border-radius: 10px; 
    box-shadow: 0 2px 6px rgba(0,0,0,0.1); 
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

/* Judul */
h2 { 
    text-align: center; 
    color: #0a3d62; 
}
h3 {
    text-align: center;
    color: #3c6382;
}

/* Dropdown & Button */
select, button { 
    padding: 10px; 
    margin: 10px 0; 
    width: 100%; 
    border: 1px solid #ccc;
    border-radius: 6px;
}

.btn-container {
    display: flex;
    justify-content: center;
    gap: 10px;
    margin-bottom: 20px;
}

.btn-tahun {
    padding: 8px 15px;
    background: #0a3d62;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: 0.3s;
}

.btn-tahun:hover { 
    background: #3c6382; 
}

.btn-active { 
    background: #28a745 !important; 
}

/* Chart canvas border biar rapi */
canvas {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 10px;
}

    </style>
</head>
<body>
<div class="container">
    <div class="container">
    <div class="kembali">
    <a href="cek_grafik.php">Kembali</a>
</div>
    <h2>GRAFIK PEMINJAM</h2>
    <form method="GET" action="">
        <select name="peminjam">
            <option value="">Pilih nama peminjam</option>
            <?php while($row = mysqli_fetch_assoc($peminjamQuery)): ?>
                <option value="<?= $row['nama_peminjam']; ?>" <?= ($peminjam == $row['nama_peminjam']) ? 'selected' : ''; ?>>
                    <?= $row['nama_peminjam']; ?>
                </option>
            <?php endwhile; ?>
        </select>
        <label><input type="radio" name="jenis" value="bulanan" <?= ($jenis=='bulanan')?'checked':''; ?>> Grafik Bulanan</label><br>
        <label><input type="radio" name="jenis" value="tahunan" <?= ($jenis=='tahunan')?'checked':''; ?>> Grafik Tahunan</label><br>
        <button type="submit">Tampilkan</button>
    </form>

    <?php if($jenis == 'bulanan' && !empty($dataBulanan)): ?>
        <h3 style="text-align:center;">GRAFIK BULANAN <?= strtoupper($peminjam); ?></h3>
        <div class="btn-container">
            <?php foreach($dataBulanan as $tahun => $bulanan): ?>
                <button class="btn-tahun" onclick="showChart(<?= $tahun; ?>, this)"><?= $tahun; ?></button>
            <?php endforeach; ?>
        </div>
        <canvas id="chartBulanan" width="800" height="400"></canvas>
        <script>
            const allCharts = <?= json_encode($dataBulanan); ?>;
            const ctx = document.getElementById('chartBulanan').getContext('2d');
            let chartInstance;

            function showChart(tahun, el){
                const bulanLabels = ['JAN','FEB','MAR','APR','MEI','JUN','JUL','AGS','SEP','OKT','NOV','DES'];
                const dataBulan = Array(12).fill(0);
                const tahunData = allCharts[tahun];
                for (let i in tahunData) {
                    dataBulan[i-1] = tahunData[i];
                }

                if(chartInstance){ chartInstance.destroy(); }

                chartInstance = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: bulanLabels,
                        datasets: [{
                            label: 'Jumlah Peminjaman ' + tahun,
                            data: dataBulan,
                            backgroundColor: 'rgba(0,123,255,0.6)',
                            borderColor: '#007bff',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: { legend: { display: false } },
                        scales: { y: { beginAtZero: true } }
                    }
                });

                // Ubah warna tombol aktif
                const buttons = document.querySelectorAll('.btn-tahun');
                buttons.forEach(btn => btn.classList.remove('btn-active'));
                el.classList.add('btn-active');
            }

            // Tampilkan grafik tahun pertama secara default
            const firstYear = Object.keys(allCharts)[0];
            const firstBtn = document.querySelectorAll('.btn-tahun')[0];
            showChart(firstYear, firstBtn);
            firstBtn.classList.add('btn-active');
        </script>
    <?php elseif($jenis == 'tahunan' && !empty($dataTahunan)): ?>
        <h3 style="text-align:center;">GRAFIK TAHUNAN <?= strtoupper($peminjam); ?></h3>
        <canvas id="chartTahunan" width="800" height="400"></canvas>
        <script>
            const tahunanLabels = <?= json_encode(array_column($dataTahunan, 'tahun')); ?>;
            const tahunanData = <?= json_encode(array_column($dataTahunan, 'total')); ?>;
            const ctx2 = document.getElementById('chartTahunan').getContext('2d');

            new Chart(ctx2, {
                type: 'line',
                data: {
                    labels: tahunanLabels,
                    datasets: [{
                        label: 'Jumlah Peminjaman per Tahun',
                        data: tahunanData,
                        borderColor: '#28a745',
                        backgroundColor: 'rgba(40,167,69,0.2)',
                        pointBackgroundColor: '#28a745',
                        pointBorderColor: '#fff',
                        pointRadius: 6,
                        fill: false,
                        tension: 0.2
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: true } },
                    scales: { y: { beginAtZero: true } }
                }
            });
        </script>
    <?php endif; ?>
</div>
</body>
</html>