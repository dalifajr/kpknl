<?php
include 'db.php';

if (!isset($_GET['id'])) {
    echo "<script>alert('ID tidak ditemukan!'); window.location.href='peminjam.php';</script>";
    exit();
}

$id = intval($_GET['id']);

// Ambil data peminjaman
$query = $conn->prepare("SELECT * FROM peminjaman WHERE id = ?");
$query->bind_param("i", $id);
$query->execute();
$result = $query->get_result();

if ($result->num_rows === 0) {
    echo "<script>alert('Data peminjaman tidak ditemukan!'); window.location.href='peminjam.php';</script>";
    exit();
}

$row = $result->fetch_assoc();
$no_risalah = $row['no_risalah'];

// Update status di tabel peminjaman
$upd = $conn->prepare("UPDATE peminjaman 
    SET status='Sudah Dikembalikan', tgl_pengembalian=NOW() 
    WHERE id=?");
$upd->bind_param("i", $id);

if ($upd->execute()) {

     // ANAK MAGANG UIN RADEN FATAH PALEMBANG 
    //AWANG, AULIA, DINDA, MESYA, YONIZA
    $tabel = "";
    $cek1 = $conn->prepare("SELECT id FROM risalah_batal WHERE no_risalah=?");
    $cek1->bind_param("s", $no_risalah);
    $cek1->execute();
    $res1 = $cek1->get_result();
    if ($res1->num_rows > 0) $tabel = "risalah_batal";

    if ($tabel == "") {
        $cek2 = $conn->prepare("SELECT id FROM risalah_minuta WHERE no_risalah=?");
        $cek2->bind_param("s", $no_risalah);
        $cek2->execute();
        $res2 = $cek2->get_result();
        if ($res2->num_rows > 0) $tabel = "risalah_minuta";
    }

    if ($tabel == "") {
        $cek3 = $conn->prepare("SELECT id FROM risalah_tap WHERE no_risalah=?");
        $cek3->bind_param("s", $no_risalah);
        $cek3->execute();
        $res3 = $cek3->get_result();
        if ($res3->num_rows > 0) $tabel = "risalah_tap";
    }

    // Update status risalah asal
    if ($tabel != "") {
        $upd2 = $conn->prepare("UPDATE $tabel SET status='Tersedia' WHERE no_risalah=?");
        $upd2->bind_param("s", $no_risalah);
        $upd2->execute();

        if ($upd2->affected_rows > 0) {
            echo "<script>alert('Pengembalian berhasil divalidasi dan status risalah diperbarui!'); window.location.href='peminjam.php';</script>";
        } else {
            echo "<script>alert('Pengembalian berhasil, tapi status risalah di $tabel tidak berubah (mungkin no_risalah tidak cocok)!'); window.location.href='peminjam.php';</script>";
        }
    } else {
        echo "<script>alert('Pengembalian berhasil, tapi risalah asal tidak ditemukan!'); window.location.href='peminjam.php';</script>";
    }

} else {
    echo "<script>alert('Gagal memvalidasi pengembalian!'); window.location.href='peminjam.php';</script>";
}
