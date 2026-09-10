<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'pelelang') {
    header("Location: index.html");
    exit;
}
include "db.php";

$username = $_SESSION['username'];

// Input filter
$jenis      = $_GET['jenis'] ?? "";
$no_risalah = $_GET['no_risalah'] ?? "";
$pemohon    = $_GET['pemohon'] ?? "";
$box        = $_GET['box'] ?? "";
$lemari     = $_GET['lemari'] ?? "";
$tahun      = $_GET['tahun'] ?? "";
$bulan_awal = $_GET['bulan_awal'] ?? "";
$bulan_akhir= $_GET['bulan_akhir'] ?? "";

// Query dasar (pendings milik pelelang ini)
$sql = "
    SELECT jenis, no_risalah, tgl_risalah, nama_pelelang, pemohon_lelang, box, lemari, status
    FROM risalah_pending
    WHERE nama_pelelang = '$username'
";

// Filter
if ($jenis != "") {
    $sql .= " AND jenis = '".mysqli_real_escape_string($conn,$jenis)."'";
}
if ($no_risalah != "") {
    $sql .= " AND no_risalah LIKE '%".mysqli_real_escape_string($conn,$no_risalah)."%'";
}
if ($pemohon != "") {
    $sql .= " AND pemohon_lelang LIKE '%".mysqli_real_escape_string($conn,$pemohon)."%'";
}
if ($box != "") {
    $sql .= " AND box LIKE '%".mysqli_real_escape_string($conn,$box)."%'";
}
if ($lemari != "") {
    $sql .= " AND lemari LIKE '%".mysqli_real_escape_string($conn,$lemari)."%'";
}
if ($tahun != "") {
    $sql .= " AND YEAR(tgl_risalah) = '".intval($tahun)."'";
}
if ($bulan_awal != "" && $bulan_akhir != "") {
    $sql .= " AND MONTH(tgl_risalah) BETWEEN '".intval($bulan_awal)."' AND '".intval($bulan_akhir)."'";
}

$sql .= " ORDER BY tgl_risalah DESC";

$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Risalah Pending</title>
<style>
    body { font-family: Arial, sans-serif; background:#d9edf2; margin:0; padding:20px; }
    h2 { text-align:center; margin-bottom:20px; color:#333; }
    .table-container { width:100%; overflow-x:auto; }
    table { width:100%; border-collapse:collapse; background:#fff; border-radius:8px;
            overflow:hidden; box-shadow:0 2px 6px rgba(0,0,0,0.1); min-width:1000px; }
    th { background:#0a3d62; color:#fff; padding:12px; text-align:center; font-size:14px; }
    td { padding:10px; border-bottom:1px solid #ddd; text-align:center; font-size:14px; color:#333; }
    tr:hover { background:#f1f7fc; }
    .kembali { position:absolute; top:20px; right:20px; }
    .kembali a { display:inline-block; padding:8px 14px; background:#28a745; color:#fff; text-decoration:none; border-radius:6px; font-weight:bold; transition:0.3s; }
    .kembali a:hover { background:#218838; }
    .filter-form { margin-bottom:15px; text-align:center; background:#cde8f2; padding:10px; border-radius:8px; }
    input, select, button { padding:6px 10px; border-radius:6px; border:1px solid #ccc; margin:3px; }
    button { cursor:pointer; }
    .reset { background:#6c757d; color:#fff; padding:6px 12px; border-radius:6px; text-decoration:none; }
    .reset:hover { background:#555; }
    .status-pending { color:orange; font-weight:bold; }
    .status-validated { color:green; font-weight:bold; }
</style>
</head>
<body>

<div class="kembali"><a href="pelelang_dashboard.php">Kembali</a></div>

<h2>Daftar Risalah Pending</h2>

<!-- Form Filter -->
<div class="filter-form">
    <form method="get">
        <input type="text" name="no_risalah" placeholder="No. Risalah" value="<?=htmlspecialchars($no_risalah)?>">
        <input type="text" name="pemohon" placeholder="Pemohon Lelang" value="<?=htmlspecialchars($pemohon)?>">
        <input type="text" name="box" placeholder="Box" value="<?=htmlspecialchars($box)?>">
        <input type="text" name="lemari" placeholder="Lemari" value="<?=htmlspecialchars($lemari)?>">

        <select name="jenis">
            <option value="">Semua Jenis</option>
            <option value="minuta" <?= $jenis=="minuta"?"selected":""; ?>>Minuta</option>
            <option value="tap" <?= $jenis=="tap"?"selected":""; ?>>TAP</option>
            <option value="batal" <?= $jenis=="batal"?"selected":""; ?>>Batal</option>
        </select>

        <select name="tahun">
            <option value="">Pilih Tahun</option>
            <?php
            $thnNow = date("Y");
            for ($t=2020;$t<=$thnNow;$t++) {
                echo "<option value='$t' ".($tahun==$t?"selected":"").">$t</option>";
            }
            ?>
        </select>

        <select name="bulan_awal">
            <option value="">Bulan Awal</option>
            <?php for ($b=1;$b<=12;$b++) {
                echo "<option value='$b' ".($bulan_awal==$b?"selected":"").">".date("F", mktime(0,0,0,$b,1))."</option>";
            }?>
        </select>

        <select name="bulan_akhir">
            <option value="">Bulan Akhir</option>
            <?php for ($b=1;$b<=12;$b++) {
                echo "<option value='$b' ".($bulan_akhir==$b?"selected":"").">".date("F", mktime(0,0,0,$b,1))."</option>";
            }?>
        </select>

        <button type="submit">Cari</button>
        <a href="pelelang_pending.php" class="reset">Reset</a>
    </form>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Jenis Risalah</th>
                <th>No. Risalah</th>
                <th>Tanggal Risalah</th>
                <th>Pemohon Lelang</th>
                <th>Box</th>
                <th>Lemari</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (mysqli_num_rows($result) > 0) {
                $no = 1;
                while ($row = mysqli_fetch_assoc($result)) {
                    $status_label = ($row['status'] == 'belum_validasi')
                        ? "<span class='status-pending'>Pending</span>"
                        : "<span class='status-validated'>Valid</span>";

                    echo "<tr>
                        <td>".$no++."</td>
                        <td>".htmlspecialchars(ucfirst($row['jenis']))."</td>
                        <td>".htmlspecialchars($row['no_risalah'])."</td>
                        <td>".htmlspecialchars($row['tgl_risalah'])."</td>
                        <td>".htmlspecialchars($row['pemohon_lelang'])."</td>
                        <td>".htmlspecialchars($row['box'])."</td>
                        <td>".htmlspecialchars($row['lemari'])."</td>
                        <td>".$status_label."</td>
                    </tr>";
                }
            } else {
                echo "<tr><td colspan='8'>Tidak ada risalah pending untuk sekarang.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

</body>
</html>