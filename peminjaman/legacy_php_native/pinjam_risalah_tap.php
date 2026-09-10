<?php
// Koneksi ke database
$host = "localhost";
$user = "root";
$pass = "";
$db   = "kpknl"; 

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Ambil input pencarian & filter
$no_risalah     = isset($_GET['no_risalah']) ? trim($_GET['no_risalah']) : '';
$nama_pelelang  = isset($_GET['nama_pelelang']) ? trim($_GET['nama_pelelang']) : '';
$pemohon_lelang = isset($_GET['pemohon_lelang']) ? trim($_GET['pemohon_lelang']) : '';
$status_filter  = isset($_GET['status']) ? trim($_GET['status']) : '';

// Query gabungan risalah_tap + peminjaman
$sql = "
    SELECT r.id, r.no_risalah, r.tgl_risalah, r.nama_pelelang, r.pemohon_lelang, 
           r.box, r.lemari, r.status, p.nama_peminjam
    FROM risalah_tap r
    LEFT JOIN peminjaman p 
        ON r.no_risalah = p.no_risalah 
        AND r.status = 'sedang_dipinjam'
    WHERE 1=1
";

// Tambah kondisi pencarian
if ($no_risalah !== '') {
    $sql .= " AND r.no_risalah LIKE '%" . $conn->real_escape_string($no_risalah) . "%'";
}
if ($nama_pelelang !== '') {
    $sql .= " AND r.nama_pelelang LIKE '%" . $conn->real_escape_string($nama_pelelang) . "%'";
}
if ($pemohon_lelang !== '') {
    $sql .= " AND r.pemohon_lelang LIKE '%" . $conn->real_escape_string($pemohon_lelang) . "%'";
}

// Tambah kondisi filter status
if ($status_filter === 'sedang_dipinjam') {
    $sql .= " AND r.status = 'sedang_dipinjam'";
} elseif ($status_filter === 'tersedia') {
    $sql .= " AND (r.status IS NULL OR r.status = 'tersedia')";
}
$query = "
    SELECT * 
    FROM risalah_tap
    ORDER BY tgl_risalah DESC, CAST(no_risalah AS UNSIGNED) DESC
";
$result = $conn->query($query);

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pinjam Risalah TAP</title>
    <link rel="stylesheet" href="style_pinjam.css">
    <style>
        .btn-submit {
            text-align: right;
            margin: 15px 0;
        }
        .btn-submit button {
            background: #007bff;
            color: #fff;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
        }
        .btn-submit button:hover {
            background: #0056b3;
        }
        /* ✅ Scroll hanya tbody */
        /* Tambahan untuk scroll tabel */
.table-container {
    max-height: 550px; /* Atur tinggi area scroll sesuai kebutuhan */
    overflow-y: auto;
    border: 1px solid #ccc;
    margin-top: 10px;
}
.table-container table {
    width: 100%;
    border-collapse: collapse;
}
.table-container thead th {
    position: sticky;
    top: 0;
    background: #007bff;
    color: white;
    z-index: 2;
}

    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <img src="logo.png" alt="Icon" class="icon">
        <form method="GET" class="search-form">
            <input type="text" name="no_risalah" placeholder="Masukkan No. Risalah" value="<?= htmlspecialchars($no_risalah) ?>">
            <input type="text" name="nama_pelelang" placeholder="Masukkan Nama Pelelang" value="<?= htmlspecialchars($nama_pelelang) ?>">
            <input type="text" name="pemohon_lelang" placeholder="Masukkan Pemohon Lelang" value="<?= htmlspecialchars($pemohon_lelang) ?>">
            
            <!-- Dropdown Filter Status -->
            <select name="status">
                <option value="">Semua Status</option>
                <option value="tersedia" <?= $status_filter=='tersedia' ? 'selected' : '' ?>>Tersedia</option>
                <option value="sedang_dipinjam" <?= $status_filter=='sedang_dipinjam' ? 'selected' : '' ?>>Sedang Dipinjam</option>
            </select>

            <button type="submit" class="search-btn">Cari</button>
            <a href="pinjam_risalah_tap.php" class="reset-btn">Reset</a>
        </form>
        <div class="kembali">
            <a href="pilih_pinjam.php">Kembali</a>
        </div>
    </div>

    <form method="POST" action="proses_pinjam_tap.php">
        <div class="btn-submit">
            <button type="submit">Pinjam Yang Dicentang</button>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Peminjam</th>
                        <th>No. Risalah</th>
                        <th>Tanggal Risalah</th>
                        <th>Nama Pelelang</th>
                        <th>Pemohon Lelang</th>
                        <th>Box</th>
                        <th>Lemari</th>
                        <th>Status</th>
                        <th>Pilih</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                if ($result && $result->num_rows > 0) {
                    $no = 1;
                    while ($row = $result->fetch_assoc()) {
                        $status = $row['status'] ? strtolower($row['status']) : 'tersedia';
                        $disabled = ($status == 'sedang_dipinjam') ? 'disabled' : '';
                        $statusClass = ($status == 'sedang_dipinjam') ? 'status-disabled' : 'status-available';
                        $namaPeminjam = ($status == 'sedang_dipinjam') ? htmlspecialchars($row['nama_peminjam']) : '-';

                        echo "<tr>
                            <td>".$no++."</td>
                            <td>".$namaPeminjam."</td>
                            <td>".$row['no_risalah']."</td>
                            <td>".date('d - m - Y', strtotime($row['tgl_risalah']))."</td>
                            <td>".$row['nama_pelelang']."</td>
                            <td>".$row['pemohon_lelang']."</td>
                            <td>".$row['box']."</td>
                            <td>".$row['lemari']."</td>
                            <td class='$statusClass'>".ucwords(str_replace('_',' ',$status))."</td>
                            <td>
                                <input type='checkbox' name='selected_ids[]' value='".$row['id']."' $disabled>
                            </td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='10'>Tidak ada data ditemukan</td></tr>";
                }
                ?>
                </tbody>
            </table>
        </div>
    </form>
</div>
</body>
</html>

<?php $conn->close(); ?>