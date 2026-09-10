<?php
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'peminjam') {
    header("Location: index.html");
    exit();
}

$namaPeminjam = $_SESSION['username'];

$conn = new mysqli("localhost", "root", "", "kpknl");
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

if (!isset($_POST['selected_ids']) || count($_POST['selected_ids']) === 0) {
    echo "<script>alert('Tidak ada risalah yang dipilih!'); window.location.href='pinjam_risalah_batal.php';</script>";
    exit();
}

$ids = array_map('intval', $_POST['selected_ids']);
$idList = implode(',', $ids);

$conn->begin_transaction();

try {
    $sqlSelect = "SELECT * FROM risalah_batal WHERE id IN ($idList) FOR UPDATE";
    $res = $conn->query($sqlSelect);

    $stmtUpd = $conn->prepare("UPDATE risalah_batal SET status='sedang_dipinjam' WHERE id=? AND status!='sedang_dipinjam'");
    $stmtIns = $conn->prepare("
        INSERT INTO peminjaman
        (nama_peminjam, no_risalah, tgl_risalah, nama_pelelang, pemohon_lelang, box, lemari, tgl_peminjaman, tgl_pengembalian, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NULL, 'Proses Peminjaman')
    ");

    $inserted = 0; $skipped = 0;
    while ($row = $res->fetch_assoc()) {
        if ($row['status'] === 'sedang_dipinjam') {
            $skipped++;
            continue;
        }
        $stmtUpd->bind_param("i", $row['id']);
        $stmtUpd->execute();

        if ($stmtUpd->affected_rows > 0) {
            $stmtIns->bind_param("sssssss",
                $namaPeminjam,
                $row['no_risalah'],
                $row['tgl_risalah'],
                $row['nama_pelelang'],
                $row['pemohon_lelang'],
                $row['box'],
                $row['lemari']
            );
            $stmtIns->execute();
            $inserted++;
        }
    }

    $conn->commit();
    $msg = ($inserted > 0) ? "Berhasil pinjam ($inserted). Lewat ($skipped)." : "Semua sudah dipinjam.";
    echo "<script>alert('$msg'); window.location.href='pinjam_risalah_batal.php';</script>";

} catch (Exception $e) {
    $conn->rollback();
    echo "<script>alert('Error: ".$e->getMessage()."'); window.location.href='pinjam_risalah_batal.php';</script>";
}
