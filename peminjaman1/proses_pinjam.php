<?php
session_start();

// Pastikan user login sebagai peminjam
if (!isset($_SESSION['username']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'peminjam') {
    header("Location: index.html");
    exit();
}

$namaPeminjam = $_SESSION['username'];

// Koneksi DB
$host = "localhost";
$user = "root";
$pass = "";
$db   = "kpknl";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Validasi input
if (!isset($_POST['selected_ids']) || !is_array($_POST['selected_ids']) || count($_POST['selected_ids']) === 0) {
    echo "<script>alert('Tidak ada risalah yang dipilih!'); window.location.href='pinjam_risalah.php';</script>";
    exit();
}

// Siapkan ID (integer aman)
$ids = array_map('intval', $_POST['selected_ids']);
$ids = array_unique($ids);
$idList = implode(',', $ids);

// Ambil alasan peminjaman dari form (boleh kosong jika frontend tidak mengirim)
$alasan = isset($_POST['alasan_peminjaman']) ? trim($_POST['alasan_peminjaman']) : '';

// Mulai transaksi
$conn->begin_transaction();

try {
    // Ambil data yang dipilih (lock row)
    $sqlSelect = "
        SELECT id, no_risalah, tgl_risalah, nama_pelelang, pemohon_lelang, box, lemari, 
               COALESCE(status, 'tersedia') AS st
        FROM risalah_minuta
        WHERE id IN ($idList)
        FOR UPDATE
    ";
    $res = $conn->query($sqlSelect);
    if (!$res) {
        throw new Exception("Gagal mengambil data risalah: " . $conn->error);
    }

    // Siapkan statement update & insert
    $stmtUpd = $conn->prepare("UPDATE risalah_minuta SET status='sedang_dipinjam' WHERE id=? AND status!='sedang_dipinjam'");

    // Perubahan: masukkan kolom alasan_peminjaman pada INSERT
    $stmtIns = $conn->prepare("
        INSERT INTO peminjaman 
        (nama_peminjam, no_risalah, tgl_risalah, nama_pelelang, pemohon_lelang, box, lemari, alasan_peminjaman, tgl_peminjaman, tgl_pengembalian, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NULL, 'Proses Peminjaman')
    ");
    if (!$stmtUpd || !$stmtIns) {
        throw new Exception("Gagal menyiapkan statement: " . $conn->error);
    }

    $inserted = 0; 
    $skipped  = 0;

    while ($row = $res->fetch_assoc()) {
        $id = (int)$row['id'];

        // Skip kalau sudah dipinjam
        if ($row['st'] === 'sedang_dipinjam') {
            $skipped++;
            continue;
        }

        // Update status risalah_minuta
        $stmtUpd->bind_param("i", $id);
        $stmtUpd->execute();

        if ($stmtUpd->affected_rows > 0) {
            // Insert ke peminjaman dengan alasan
            $stmtIns->bind_param(
                "ssssssss",
                $namaPeminjam,
                $row['no_risalah'],
                $row['tgl_risalah'],
                $row['nama_pelelang'],
                $row['pemohon_lelang'],
                $row['box'],
                $row['lemari'],
                $alasan
            );
            $stmtIns->execute();

            if ($stmtIns->affected_rows > 0) {
                $inserted++;
            } else {
                // jika insert gagal, rollback agar konsisten
                throw new Exception("Gagal insert peminjaman: " . $stmtIns->error);
            }
        } else {
            $skipped++;
        }
    }

    $conn->commit();

    // Pesan hasil
    if ($inserted > 0 && $skipped === 0) {
        $msg = "Risalah berhasil dipinjam ($inserted item).";
    } elseif ($inserted > 0 && $skipped > 0) {
        $msg = "Sebagian berhasil ($inserted), sebagian sudah dipinjam ($skipped).";
    } else {
        $msg = "Semua risalah yang dipilih sudah sedang dipinjam.";
    }

    echo "<script>alert('{$msg}'); window.location.href='pinjam_risalah.php';</script>";
} catch (Exception $e) {
    $conn->rollback();
    echo "<script>alert('Terjadi kesalahan: ".addslashes($e->getMessage())."'); window.location.href='pinjam_risalah.php';</script>";
} finally {
    if (isset($stmtUpd) && $stmtUpd) $stmtUpd->close();
    if (isset($stmtIns) && $stmtIns) $stmtIns->close();
    $conn->close();
}
?>