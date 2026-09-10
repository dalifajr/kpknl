<?php
include 'db.php';

if (isset($_POST['validasi_kembali'])) {
    $id             = mysqli_real_escape_string($conn, $_POST['id']);
    $no_risalah     = mysqli_real_escape_string($conn, $_POST['no_risalah']);
    $jenis          = mysqli_real_escape_string($conn, $_POST['jenis']);
    $tgl_risalah    = mysqli_real_escape_string($conn, $_POST['tgl_risalah']);
    $nama_pelelang  = mysqli_real_escape_string($conn, $_POST['nama_pelelang']);
    $pemohon_lelang = mysqli_real_escape_string($conn, $_POST['pemohon_lelang']);

    // Insert ke risalah_pending
    $insert = mysqli_query($conn, "
        INSERT INTO risalah_pending
        (no_risalah, jenis, tgl_risalah, nama_pelelang, pemohon_lelang, status)
        VALUES ('$no_risalah', '$jenis', '$tgl_risalah', '$nama_pelelang', '$pemohon_lelang', 'belum_validasi')
    ");

    if ($insert) {
        // Hapus data dari risalah_revisi
        mysqli_query($conn, "DELETE FROM risalah_revisi WHERE id='$id'");

        // Kirim respon HTML+JS
        echo "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        </head>
        <body>
        <script>
            Swal.fire({
                title: 'Berhasil!',
                text: 'Risalah berhasil dikirim kembali ke Admin.',
                icon: 'success',
                timer: 2000,
                showConfirmButton: false
            }).then(() => {
                window.location.href = 'cek_revisi.php';
            });
        </script>
        </body>
        </html>
        ";
        exit;
    } else {
        echo "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        </head>
        <body>
        <script>
            Swal.fire({
                title: 'Gagal!',
                text: 'Data gagal dipindahkan: " . addslashes(mysqli_error($conn)) . "',
                icon: 'error',
                confirmButtonText: 'Coba Lagi'
            }).then(() => {
                window.history.back();
            });
        </script>
        </body>
        </html>
        ";
    }
} else {
    echo "Akses tidak valid.";
}
?>
