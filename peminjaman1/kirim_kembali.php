<?php
include 'db.php';
session_start();

$id = $_POST['id'] ?? '';
if ($id) {
    $result = mysqli_query($conn, "SELECT * FROM risalah_revisi WHERE id = '$id'");
    $data = mysqli_fetch_assoc($result);

    if ($data) {
        // Pindahkan kembali ke risalah_pending
        $insert = "INSERT INTO risalah_pending 
                  (no_risalah, jenis, tgl_risalah, nama_pelelang, pemohon_lelang, box, lemari, keterangan, catatan, status)
                  VALUES 
                  ('{$data['no_risalah']}', '{$data['jenis']}', '{$data['tgl_risalah']}', '{$data['nama_pelelang']}', '{$data['pemohon_lelang']}', '{$data['box']}', '{$data['lemari']}', '{$data['keterangan']}', '{$data['catatan']}', 'belum_validasi')";

        if (mysqli_query($conn, $insert)) {
            mysqli_query($conn, "DELETE FROM risalah_revisi WHERE id = '$id'");
            echo "<script>alert('Data berhasil dikirim kembali untuk validasi.'); window.location='pelelang_dashboard.php';</script>";
        } else {
            echo "Error insert: " . mysqli_error($conn);
        }
    } else {
        echo "Data revisi tidak ditemukan.";
    }
}
?>
