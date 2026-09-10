<?php  
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'peminjam') {
    header("Location: index.html");
    exit;
}
include "db.php"; 

$username = $_SESSION['username'];

// Filter status
$statusFilter = isset($_GET['status']) && $_GET['status'] != '' ? $_GET['status'] : '';

$sql = "SELECT * FROM peminjaman WHERE nama_peminjam = '$username'";
if ($statusFilter != '') {
    $sql .= " AND status = '" . mysqli_real_escape_string($conn, $statusFilter) . "'";
}
$sql .= " ORDER BY id DESC";
$query = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Cek Data Pinjaman</title>
  <style>
    body { font-family: Arial, sans-serif; background:#d9edf2; margin:0; padding:20px; }
    h2 { text-align:center; color:#333; margin-bottom:20px; }
    .table-container { width:100%; overflow-x:auto; }
    table { width:100%; border-collapse:collapse; background:#fff; border-radius:8px; 
            box-shadow:0 2px 6px rgba(0,0,0,0.1); min-width:950px; }
    th { background:#0a3d62; color:#fff; padding:12px 14px; font-size:14px; text-align:center; }
    td { padding:10px 12px; border-bottom:1px solid #ddd; font-size:14px; text-align:center; color:#333; }
    tr:hover { background:#f1f7fc; }
    .btn { padding:6px 12px; border-radius:6px; border:none; cursor:pointer; font-size:13px; color:#fff; transition:0.3s; }
    .btn-kembali { background:#0a3d62; } .btn-kembali:hover { background:#3c6382; }
    .btn-diterima { background:#28a745; } .btn-diterima:hover { background:#218838; }
    .btn-verifikasi { background:#e67e22; } .btn-verifikasi:hover { background:#cf711c; }
    .btn-selesai { background:#007bff; } .btn-selesai:hover { background:#0069d9; }
    .btn-reset { background:#6c757d; } .btn-reset:hover { background:#5a6268; }
    .lampu { padding:4px 10px; border-radius:6px; color:#fff; display:inline-block; min-width:36px; font-weight:bold; }
    .hijau { background:#28a745; } .kuning { background:#f39c12; } .merah { background:#e74c3c; }
    .kembali { position:absolute; top:20px; right:20px; }
    .kembali a { display:inline-block; padding:8px 14px; background:#28a745; color:#fff; text-decoration:none; border-radius:6px; font-weight:bold; transition:0.3s; }
    .kembali a:hover { background:#218838; }
    .filter-form { text-align:center; margin-bottom:20px; }
    .filter-form select, .filter-form button { padding:8px 12px; font-size:14px; border-radius:6px; border:1px solid #ccc; }
    .filter-form button { margin-left:5px; }
  </style>
</head>
<body>
  <div class="kembali"><a href="peminjam_dashboard.php">Kembali</a></div>
  <h2>Cek Data Pinjaman Anda</h2>

  <!--uin-->
  <form method="GET" class="filter-form">
    <label for="status">Filter Status:</label>
    <select name="status" id="status">
      <option value="">Semua</option>
      <option value="Menunggu Konfirmasi Peminjam" <?= $statusFilter == 'Menunggu Konfirmasi Peminjam' ? 'selected' : ''; ?>>Menunggu Konfirmasi Peminjam</option>
      <option value="Sedang Dipinjam" <?= $statusFilter == 'Sedang Dipinjam' ? 'selected' : ''; ?>>Sedang Dipinjam</option>
      <option value="Proses Pengembalian" <?= $statusFilter == 'Proses Pengembalian' ? 'selected' : ''; ?>>Proses Pengembalian</option>
      <option value="Sudah Dikembalikan" <?= $statusFilter == 'Sudah Dikembalikan' ? 'selected' : ''; ?>>Sudah Dikembalikan</option>
    </select>
    <button type="submit" class="btn btn-diterima">Terapkan</button>
    <a href="cek_pinjaman.php" class="btn btn-reset">Reset</a>
  </form>

  <div class="table-container">
  <table>
    <thead>
      <tr>
        <th>No</th>
        <th>No. Risalah / No. Register</th>
        <th>Tanggal Risalah / Tanggal Register</th>
        <th>Nama Pelelang</th>
        <th>Pemohon Lelang</th>
        <th>Box</th>
        <th>Lemari</th>
        <th>Tanggal Peminjaman</th>
        <th>Tanggal Pengembalian</th>
        <th>Status</th>
        <th>Lama Pinjam</th>
        <th>Aktivitas</th>
      </tr>
    </thead>
    <tbody>
      <?php
      $no = 1;
      if (mysqli_num_rows($query) > 0) {
        while ($row = mysqli_fetch_assoc($query)) {
          $lamaPinjam = "-";
          $warna = "";
          if ($row['status'] === "Sedang Dipinjam" && !empty($row['tgl_peminjaman'])) {
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
          ?>
          <tr>
            <td><?= $no++; ?></td>
            <td><?= htmlspecialchars($row['no_risalah']); ?></td>
            <td><?= htmlspecialchars($row['tgl_risalah']); ?></td>
            <td><?= htmlspecialchars($row['nama_pelelang']); ?></td>
            <td><?= htmlspecialchars($row['pemohon_lelang']); ?></td>
            <td><?= htmlspecialchars($row['box']); ?></td>
            <td><?= htmlspecialchars($row['lemari']); ?></td>
            <td><?= htmlspecialchars($row['tgl_peminjaman']); ?></td>
            <td><?= htmlspecialchars($row['tgl_pengembalian']); ?></td>
            <td><?= htmlspecialchars($row['status']); ?></td>
            <td><span class="lampu <?= $warna; ?>"><?= $lamaPinjam; ?></span></td>
            <td>
              <?php if ($row['status'] === "Sedang Dipinjam") : ?>
                <form method="POST" action="proses_kembalikan.php" style="display:inline;">
                  <input type="hidden" name="id" value="<?= (int)$row['id']; ?>">
                  <button type="submit" class="btn btn-kembali"
                          onclick="return confirm('Ajukan pengembalian untuk risalah ini?')">
                    Kembalikan
                  </button>
                </form>
              <?php elseif ($row['status'] === "Proses Pengembalian") : ?>
                <span class="btn btn-verifikasi">Menunggu Verifikasi Admin</span>
              <?php elseif ($row['status'] === "Sudah Dikembalikan") : ?>
                <span class="btn btn-selesai">Selesai</span>
              <?php elseif ($row['status'] === "Menunggu Konfirmasi Peminjam") : ?>
                <form method="POST" action="proses_terima.php" style="display:inline;">
                  <input type="hidden" name="id" value="<?= (int)$row['id']; ?>">
                  <button type="submit" class="btn btn-diterima"
                          onclick="return confirm('Terima risalah ini dan mulai hitung lama pinjam?')">
                    Terima
                  </button>
                </form>
              <?php else : ?>
                -
              <?php endif; ?>
            </td>
          </tr>
          <?php
        }
      } else {
        echo "<tr><td colspan='12'>Belum ada data pinjaman untuk akun ini.</td></tr>";
      }
      ?>
    </tbody>
  </table>
  </div>
</body>
</html>
