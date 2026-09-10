<?php
$conn = new mysqli('localhost', 'root', '', 'kpknl');

// Jika ambil data chart
if (isset($_GET['action']) && $_GET['action'] === 'get_data') {
    header('Content-Type: application/json');

    $year = intval($_GET['year']);
    $status = isset($_GET['status']) ? strtolower($_GET['status']) : ''; // Pastikan lowercase

    // Tentukan tabel berdasarkan status
    if ($status === 'minuta') {
        $query = "
            SELECT MONTH(tgl_risalah) as bulan, COUNT(*) as total
            FROM risalah_minuta
            WHERE YEAR(tgl_risalah) = $year
            GROUP BY MONTH(tgl_risalah)
        ";
    } elseif ($status === 'tap') {
        $query = "
            SELECT MONTH(tgl_risalah) as bulan, COUNT(*) as total
            FROM risalah_tap
            WHERE YEAR(tgl_risalah) = $year
            GROUP BY MONTH(tgl_risalah)
        ";
    } elseif ($status === 'batal') {
        $query = "
            SELECT MONTH(tgl_risalah) as bulan, COUNT(*) as total
            FROM risalah_batal
            WHERE YEAR(tgl_risalah) = $year
            GROUP BY MONTH(tgl_risalah)
        ";
    } else {
        // Jika Semua → ambil data tiap tabel terpisah
        $labels = ['JAN', 'FEB', 'MAR', 'APR', 'MEI', 'JUN', 'JUL', 'AGS', 'SEP', 'OKT', 'NOV', 'DES'];

        // Fungsi ambil data per tabel
        function getData($conn, $table, $year) {
            $query = "SELECT MONTH(tgl_risalah) as bulan, COUNT(*) as total 
                      FROM $table 
                      WHERE YEAR(tgl_risalah) = $year 
                      GROUP BY MONTH(tgl_risalah)";
            $result = $conn->query($query);
            $values = array_fill(0, 12, 0);
            while ($row = $result->fetch_assoc()) {
                $values[$row['bulan'] - 1] = (int)$row['total'];
            }
            return $values;
        }

        $datasets = [
            [
                'label' => 'Risalah Minuta',
                'data' => getData($conn, "risalah_minuta", $year),
                'borderColor' => 'rgba(54, 162, 235, 1)',
                'backgroundColor' => 'rgba(54, 162, 235, 0.2)',
                'fill' => false
            ],
            [
                'label' => 'Risalah Tap',
                'data' => getData($conn, "risalah_tap", $year),
                'borderColor' => 'rgba(255, 99, 132, 1)',
                'backgroundColor' => 'rgba(255, 99, 132, 0.2)',
                'fill' => false
            ],
            [
                'label' => 'Risalah Batal',
                'data' => getData($conn, "risalah_batal", $year),
                'borderColor' => 'rgba(255, 206, 86, 1)',
                'backgroundColor' => 'rgba(255, 206, 86, 0.2)',
                'fill' => false
            ]
        ];

        echo json_encode([
            'labels' => $labels,
            'datasets' => $datasets
        ]);
        exit;
    }

    $result = $conn->query($query);

    $labels = ['JAN', 'FEB', 'MAR', 'APR', 'MEI', 'JUN', 'JUL', 'AGS', 'SEP', 'OKT', 'NOV', 'DES'];
    $values = array_fill(0, 12, 0);

    while ($row = $result->fetch_assoc()) {
        $values[$row['bulan'] - 1] = (int)$row['total'];
    }

    echo json_encode([
        'labels' => $labels,
        'values' => $values
    ]);
    exit;
}

// Ambil semua tahun unik dari ketiga tabel
$years = [];
$res = $conn->query("
    SELECT DISTINCT YEAR(tgl_risalah) as year FROM (
        SELECT tgl_risalah FROM risalah_minuta
        UNION
        SELECT tgl_risalah FROM risalah_tap
        UNION
        SELECT tgl_risalah FROM risalah_batal
    ) as all_data
    ORDER BY year ASC
");
while ($r = $res->fetch_assoc()) {
    $years[] = $r['year'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Grafik Bulanan Per Tahun Pelelangan</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
body {
    font-family: Arial, sans-serif;
    background-color: #dbeef5;
    text-align: center;
    padding: 20px;
    position: relative;
}
h2 {
    font-size: 28px;
    color: #1a3945;
}
.year-buttons {
    margin: 20px 0;
}
.year-buttons button {
    background-color: #2b5c7c;
    color: #fff;
    padding: 10px 15px;
    margin: 5px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
}
.year-buttons button:hover {
    background-color: #1a3945;
}
canvas {
    background: #fff;
    padding: 20px;
    border-radius: 10px;
}
.filter-status {
    margin-bottom: 15px;
    font-size: 16px;
}
.filter-status select {
    padding: 8px;
    border-radius: 6px;
    border: 1px solid #ccc;
}

/* Tombol Kembali */
.back-button {
    position: absolute;
    top: 0px;
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

<!-- Tombol Kembali -->
<button class="back-button" onclick="goBack()">Kembali</button>

<h2>GRAFIK BULANAN PER TAHUN PELELANGAN</h2>

<div class="year-buttons" id="yearButtons">
    <?php foreach ($years as $year): ?>
        <button onclick="setYear(<?php echo $year; ?>)"><?php echo $year; ?></button>
    <?php endforeach; ?>
</div>

<div class="filter-risalah">
    <label for="risalahSelect">Pilih Risalah: </label>
    <select id="risalahSelect" onchange="updateChart()">
        <option value="">Semua</option>
        <option value="minuta">Minuta</option>
        <option value="tap">Tap</option>
        <option value="batal">Batal</option>
    </select>
</div>

<h3 id="selectedYear">Pilih Tahun</h3>
<canvas id="grafikChart" width="800" height="400"></canvas>

<script>
let chart;
let selectedYear = null;

function setYear(year) {
    selectedYear = year;
    updateChart();
}

function updateChart() {
    if (!selectedYear) {
        alert("Pilih tahun terlebih dahulu!");
        return;
    }
    const status = document.getElementById('risalahSelect').value;

    fetch(`?action=get_data&year=${selectedYear}&status=${status}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('selectedYear').innerText = "Tahun " + selectedYear + (status ? " - " + status : "");

            const labels = data.labels;

            // Jika semua → backend kirim datasets, kalau tidak → values
            let datasets;
            if (data.datasets) {
                datasets = data.datasets;
            } else {
                datasets = [{
                    label: 'Jumlah Risalah',
                    data: data.values,
                    borderColor: '#1a3945',
                    borderWidth: 2,
                    fill: true
                }];
            }

            if (chart) {
                chart.destroy();
            }

            const ctx = document.getElementById('grafikChart').getContext('2d');
            chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: datasets
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { display: true }
                    },
                    scales: {
                        y: {
                            ticks: {
                                precision: 0,
                                stepSize: 1
                            },
                            beginAtZero: true
                        }
                    }
                }
            });
        });
}

function goBack() {
    window.history.back();
}
</script>
</body>
</html>
