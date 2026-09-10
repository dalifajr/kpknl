<?php
include 'db.php'; // koneksi database

// --- Ambil & rapikan input GET ---
$searchNo       = isset($_GET['no_risalah'])      ? trim($_GET['no_risalah'])      : '';
$searchPelelang = isset($_GET['nama_pelelang'])   ? trim($_GET['nama_pelelang'])   : '';
$searchPemohon  = isset($_GET['pemohon_lelang'])  ? trim($_GET['pemohon_lelang'])  : '';
$searchBox      = isset($_GET['box'])             ? trim($_GET['box'])             : '';
$lemari = isset($_GET['lemari']) ? $_GET['lemari'] : "";
$filterTahun    = isset($_GET['tahun'])           ? trim($_GET['tahun'])           : '';
$bulanAwal      = isset($_GET['bulan_awal'])      ? trim($_GET['bulan_awal'])      : '';
$bulanAkhir     = isset($_GET['bulan_akhir'])     ? trim($_GET['bulan_akhir'])     : '';
$filterStatus   = isset($_GET['status'])          ? trim($_GET['status'])          : '';
$filterStatusLc = mb_strtolower($filterStatus, 'UTF-8');

// Sanitasi ringan untuk string
$esc = fn($s) => $s === '' ? '' : $GLOBALS['conn']->real_escape_string($s);

// Validasi numeric
$isValidYear  = $filterTahun !== '' && ctype_digit($filterTahun) && (int)$filterTahun >= 1900 && (int)$filterTahun <= 2100;
$bulanAwal    = ($bulanAwal !== ''   && ctype_digit($bulanAwal)   && (int)$bulanAwal   >= 1 && (int)$bulanAwal   <= 12) ? (int)$bulanAwal   : '';
$bulanAkhir   = ($bulanAkhir !== ''  && ctype_digit($bulanAkhir)  && (int)$bulanAkhir  >= 1 && (int)$bulanAkhir  <= 12) ? (int)$bulanAkhir  : '';

// --- Bangun query ---
$query = "SELECT * FROM risalah_minuta WHERE 1=1";

if ($searchNo       !== '') $query .= " AND no_risalah      LIKE '%" . $esc($searchNo)       . "%'";
if ($searchPelelang !== '') $query .= " AND nama_pelelang   LIKE '%" . $esc($searchPelelang) . "%'";
if ($searchPemohon  !== '') $query .= " AND pemohon_lelang  LIKE '%" . $esc($searchPemohon)  . "%'";
if ($searchBox      !== '') $query .= " AND box = '"              . $esc($searchBox)      . "'";
if (!empty($lemari)) {$query .= " AND lemari LIKE '%" . mysqli_real_escape_string($conn, $lemari) . "%'";
}

if ($isValidYear) {
    $query .= " AND YEAR(tgl_risalah) = " . (int)$filterTahun;
}
if ($bulanAwal !== '' && $bulanAkhir !== '') {
    $start = min($bulanAwal, $bulanAkhir);
    $end   = max($bulanAwal, $bulanAkhir);
    $query .= " AND MONTH(tgl_risalah) BETWEEN $start AND $end";
} elseif ($bulanAwal !== '') {
    $query .= " AND MONTH(tgl_risalah) = $bulanAwal";
}

if ($filterStatusLc !== '') {
    if ($filterStatusLc === 'sedang dipinjam') {
        $query .= " AND LOWER(TRIM(status)) REGEXP '^(sedang[ _-]?dipinjam|dipinjam)$'";
    } elseif ($filterStatusLc === 'tersedia') {
        $query .= " AND LOWER(TRIM(status)) REGEXP '^tersedia$'";
    } else {
        $query .= " AND LOWER(TRIM(status)) LIKE '%" . $esc($filterStatusLc) . "%'";
    }
}

$query .= " ORDER BY tgl_risalah DESC, CAST(no_risalah AS UNSIGNED) DESC";

$result = mysqli_query($conn, $query);
if (!$result) {
    die("Query gagal: " . mysqli_error($conn));
}
 // ANAK MAGANG UIN RADEN FATAH PALEMBANG 
    //AWANG, AULIA, DINDA, MESYA, YONIZA
if (isset($_GET['export']) && $_GET['export'] === 'excel') {
    $exportResult = mysqli_query($conn, $query);
    if (!$exportResult) {
        die("Query export gagal: " . mysqli_error($conn));
    }

    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=data_risalah.xls");

    echo "<table border='1'>";
    echo "<tr>
            <th>No</th>
            <th>No. Risalah</th>
            <th>Tanggal Risalah</th>
            <th>Tanggal Validasi</th>
            <th>Nama Pelelang</th>
            <th>Pemohon Lelang</th>
            <th>Box</th>
            <th>Lemari</th>
            <th>Status</th>
          </tr>";
    $no = 1;
    while ($row = mysqli_fetch_assoc($exportResult)) {
        echo "<tr>
                <td>{$no}</td>
                <td>" . htmlspecialchars($row['no_risalah']) . "</td>
                <td>" . ($row['tgl_risalah']  ? date('d-m-Y', strtotime($row['tgl_risalah']))   : '-') . "</td>
                <td>" . ($row['tgl_validasi'] ? date('d-m-Y', strtotime($row['tgl_validasi']))  : '-') . "</td>
                <td>" . htmlspecialchars($row['nama_pelelang'])  . "</td>
                <td>" . htmlspecialchars($row['pemohon_lelang']) . "</td>
                <td>" . htmlspecialchars($row['box'])            . "</td>
                <td>" . htmlspecialchars($row['lemari'])         . "</td>
                <td>" . htmlspecialchars($row['status'])         . "</td>
              </tr>";
        $no++;
    }
    echo "</table>";
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Cek Data Risalah</title>
<style>
    body { font-family: Arial, sans-serif; background-color: #DBEEF5; }
    .container { width: 90%; margin: auto; padding: 20px; }
    .filters { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 20px; }
    .filters input, .filters select, .filters button, .btn-export, .btn-reset {
        padding: 8px; font-size: 14px; border-radius: 8px; border: 1px solid #ccc;
    }
    .btn-export { background-color: #4da6c0; color: #fff; border: none; cursor: pointer; text-decoration: none; display: inline-block; }
    .btn-reset  { background-color: #6c757d; color: #fff; border: none; cursor: pointer; text-decoration: none; display: inline-block; }
    table { width: 100%; border-collapse: collapse; background-color: #fff; }
    th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
    th { background-color: #1a3945; color: #fff; }
    .btn-edit { background-color: #1a3945; color: #fff; border: none; padding: 6px 12px; border-radius: 6px; cursor: pointer; text-decoration: none; }
    .kembali { position: absolute; top: 20px; right: 20px; }
    .kembali a { display: inline-block; padding: 8px 14px; background: #28a745; color: #fff; text-decoration: none; border-radius: 6px; font-weight: bold; transition: 0.3s; }
    .kembali a:hover { background: #218838; }
    .summary { margin: 10px 0 15px; color: #333; font-size: 14px; }

    /* Scroll wrapper */
    .table-container {
        max-height: 450px;
        overflow-y: auto;
        border: 1px solid #ccc;
    }

    /* Sticky header */
    th {
        position: sticky;
        top: 0;
        z-index: 2;
    }
</style>
</head>
<body>
<div class="container">
    <div class="kembali">
        <a href="admin_dashboard.php">← Kembali</a>
    </div>

    <h2>Cek Data Risalah</h2>

    <form method="GET">
        <div class="filters">
            <input type="text" name="no_risalah" placeholder="No. Risalah"       value="<?= htmlspecialchars($searchNo) ?>">
            <input type="text" name="nama_pelelang" placeholder="Nama Pelelang"  value="<?= htmlspecialchars($searchPelelang) ?>">
            <input type="text" name="pemohon_lelang" placeholder="Pemohon Lelang"value="<?= htmlspecialchars($searchPemohon) ?>">
            <input type="text" name="box" placeholder="Box"                      value="<?= htmlspecialchars($searchBox) ?>">
            <input type="text" name="lemari" placeholder="Lemari" value="<?= htmlspecialchars($_GET['lemari'] ?? '') ?>">

            <select name="tahun">
                <option value="">Pilih Tahun</option>
                <?php for ($year = 2019; $year <= 2025; $year++): ?>
                    <option value="<?= $year ?>" <?= ($filterTahun == $year ? 'selected' : '') ?>><?= $year ?></option>
                <?php endfor; ?>
            </select>

            <?php
            $bulanArr = [
                1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',
                7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'
            ];
            ?>
            <select name="bulan_awal">
                <option value="">Bulan Awal</option>
                <?php foreach ($bulanArr as $k=>$v): ?>
                    <option value="<?= $k ?>" <?= ($bulanAwal===$k ? 'selected' : '') ?>><?= $v ?></option>
                <?php endforeach; ?>
            </select>
            <select name="bulan_akhir">
                <option value="">Bulan Akhir</option>
                <?php foreach ($bulanArr as $k=>$v): ?>
                    <option value="<?= $k ?>" <?= ($bulanAkhir===$k ? 'selected' : '') ?>><?= $v ?></option>
                <?php endforeach; ?>
            </select>

            <select name="status" title="Status">
                <option value="">Semua Status</option>
                <option value="Sedang Dipinjam" <?= ($filterStatusLc==='sedang dipinjam' ? 'selected' : '') ?>>Sedang Dipinjam</option>
                <option value="Tersedia"        <?= ($filterStatusLc==='tersedia'        ? 'selected' : '') ?>>Tersedia</option>
            </select>

            <button type="submit">Cari</button>
            <a class="btn-reset" href="<?= strtok($_SERVER['REQUEST_URI'], '?') ?>">Reset</a>
        </div>
    </form>

    <a href="?<?= htmlspecialchars(http_build_query(array_merge($_GET, ['export'=>'excel']))) ?>" class="btn-export">Export Excel</a>

    <div class="summary">
        <?php
        $total = mysqli_num_rows($result);
        $aktif = [];
        if ($searchNo       !== '') $aktif[] = "No: <b>".htmlspecialchars($searchNo)."</b>";
        if ($searchPelelang !== '') $aktif[] = "Pelelang: <b>".htmlspecialchars($searchPelelang)."</b>";
        if ($searchPemohon  !== '') $aktif[] = "Pemohon: <b>".htmlspecialchars($searchPemohon)."</b>";
        if ($searchBox      !== '') $aktif[] = "Box: <b>".htmlspecialchars($searchBox)."</b>";
        if ($isValidYear)           $aktif[] = "Tahun: <b>".(int)$filterTahun."</b>";
        if ($bulanAwal !== '' && $bulanAkhir !== '') $aktif[] = "Bulan: <b>{$bulanArr[min($bulanAwal,$bulanAkhir)]}–{$bulanArr[max($bulanAwal,$bulanAkhir)]}</b>";
        elseif ($bulanAwal !== '')                    $aktif[] = "Bulan: <b>{$bulanArr[$bulanAwal]}</b>";
        if ($filterStatusLc !== '') $aktif[] = "Status: <b>".htmlspecialchars($filterStatus)."</b>";

        echo "Ditemukan <b>{$total}</b> data";
        if (!empty($aktif)) echo " | Filter: " . implode(", ", $aktif);
        ?>
    </div>

    <div class="table-container">
        <table>
            <tr>
                <th>No</th>
                <th>No. Risalah</th>
                <th>Tanggal Risalah</th>
                <th>Tanggal Validasi</th>
                <th>Nama Pelelang</th>
                <th>Pemohon Lelang</th>
                <th>Box</th>
                <th>Lemari</th>
                <th>E Risalah</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
            <?php
            $no = 1;
            if ($total === 0): ?>
                <tr><td colspan="11">Data tidak ditemukan untuk kombinasi filter tersebut.</td></tr>
            <?php
            else:
                while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= htmlspecialchars($row['no_risalah']) ?></td>
                        <td><?= $row['tgl_risalah']  ? date('d-m-Y', strtotime($row['tgl_risalah']))  : '-' ?></td>
                        <td><?= $row['tgl_validasi'] ? date('d-m-Y', strtotime($row['tgl_validasi'])) : '-' ?></td>
                        <td><?= htmlspecialchars($row['nama_pelelang']) ?></td>
                        <td><?= htmlspecialchars($row['pemohon_lelang']) ?></td>
                        <td><?= htmlspecialchars($row['box']) ?></td>
                        <td><?= htmlspecialchars($row['lemari']) ?></td>
                        <td>
                          <?php if (!empty($row['link_erisalah'])): ?>
                              <a href="<?= htmlspecialchars($row['link_erisalah']) ?>" target="_blank">Download</a>
                          <?php else: ?>
                              -
                          <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($row['status']) ?></td>
                        <td><a href="edit_risalah.php?id=<?= (int)$row['id'] ?>" class="btn-edit">Edit</a></td>
                    </tr>
            <?php
                endwhile;
            endif; ?>
        </table>
    </div>
</div>
</body>
</html>