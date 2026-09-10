<?php
include 'db.php';

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Ambil data dari form
// Ambil data dari form
$no_risalah = mysqli_real_escape_string($conn, $_POST['no_risalah']);
$jenis = mysqli_real_escape_string($conn, $_POST['jenis']);
$tgl_risalah = mysqli_real_escape_string($conn, $_POST['tgl_risalah']);
$nama_pelelang = mysqli_real_escape_string($conn, $_POST['nama_pelelang']);
$pemohon_lelang = mysqli_real_escape_string($conn, $_POST['pemohon_lelang']);
// ✅ Tambahan

// Validasi input
if (empty($no_risalah) || empty($jenis) || empty($tgl_risalah) || empty($nama_pelelang) || empty($pemohon_lelang)) {
    die("Semua field harus diisi!");
}

// Query Insert
$query = "INSERT INTO risalah_pending 
          (no_risalah, jenis, tgl_risalah, nama_pelelang, pemohon_lelang, status)
          VALUES 
          ('$no_risalah', '$jenis', '$tgl_risalah', '$nama_pelelang', '$pemohon_lelang',  'belum_validasi')";

if (mysqli_query($conn, $query)) {
    // Kembali ke form dengan pesan sukses
    echo "<script>
            alert('✅ Risalah berhasil ditambahkan!');
            window.location.href = 'tambah_risalah.php';
          </script>";
} else {
    echo "Error: " . mysqli_error($conn);
}
?>
