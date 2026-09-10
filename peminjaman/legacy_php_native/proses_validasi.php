<?php
include 'db.php';

if (!isset($_POST['aksi'])) {
    echo "Akses tidak valid.";
    exit;
}

$aksi = $_POST['aksi']; // 'validasi' atau 'revisi'
$id   = mysqli_real_escape_string($conn, $_POST['id'] ?? '');

// Wajib ada ID
if ($id === '') {
    die("ID tidak ditemukan.");
}

// Ambil data asli dari risalah_pending by ID
$get = mysqli_query($conn, "SELECT * FROM risalah_pending WHERE id='$id'");
if (!$get) {
    die("Gagal mengambil data pending: " . mysqli_error($conn));
}
$data = mysqli_fetch_assoc($get);
if (!$data) {
    die("Data risalah_pending tidak ditemukan untuk ID: $id");
}

// Ambil nilai dari DB
$no_risalah     = mysqli_real_escape_string($conn, $data['no_risalah'] ?? '');
$jenis_db       = strtolower(trim($data['jenis'] ?? '')); 
$tgl_risalah    = mysqli_real_escape_string($conn, $data['tgl_risalah'] ?? '');
$nama_pelelang  = mysqli_real_escape_string($conn, $data['nama_pelelang'] ?? '');
$pemohon_lelang = mysqli_real_escape_string($conn, $data['pemohon_lelang'] ?? '');
// Ambil dari form validasi (prioritas) atau fallback dari pending
$link_erisalah_input = mysqli_real_escape_string($conn, $_POST['link_erisalah'] ?? '');
$link_erisalah = !empty($link_erisalah_input) ? $link_erisalah_input : mysqli_real_escape_string($conn, $data['link_erisalah'] ?? '');


// Ambil input tambahan dari form (box/lemari + catatan)
$box            = mysqli_real_escape_string($conn, $_POST['box'] ?? '');
$lemari         = mysqli_real_escape_string($conn, $_POST['lemari'] ?? '');
$cat_no         = mysqli_real_escape_string($conn, $_POST['catatan_no'] ?? '');
$cat_jenis      = mysqli_real_escape_string($conn, $_POST['catatan_jenis'] ?? '');
$cat_tgl        = mysqli_real_escape_string($conn, $_POST['catatan_tgl'] ?? '');
$cat_pelelang   = mysqli_real_escape_string($conn, $_POST['catatan_pelelang'] ?? '');
$cat_pemohon    = mysqli_real_escape_string($conn, $_POST['catatan_pemohon'] ?? '');

$msg = "";

// ====== PROSES ======
if ($aksi === 'validasi') {
    if ($jenis_db === '') {
        die("Jenis risalah kosong, silakan cek data di risalah_pending!");
    }

    if ($jenis_db === 'minuta') {
        if ($box === '' || $lemari === '') {
            echo "<script>alert('Box dan Lemari harus diisi untuk validasi MINUTA!'); history.back();</script>";
            exit;
        }
        $insert = "
            INSERT INTO risalah_minuta
                (no_risalah, tgl_risalah, nama_pelelang, pemohon_lelang, link_erisalah, box, lemari, tgl_validasi)
            VALUES
                ('$no_risalah', '$tgl_risalah', '$nama_pelelang', '$pemohon_lelang', '$link_erisalah', '$box', '$lemari', NOW())
        ";
    } elseif ($jenis_db === 'tap') {
        if ($box === '' || $lemari === '') {
            echo "<script>alert('Box dan Lemari harus diisi untuk validasi TAP!'); history.back();</script>";
            exit;
        }
        $insert = "
            INSERT INTO risalah_tap
                (no_risalah, tgl_risalah, nama_pelelang, pemohon_lelang, link_erisalah, box, lemari, tgl_validasi)
            VALUES
                ('$no_risalah', '$tgl_risalah', '$nama_pelelang', '$pemohon_lelang', '$link_erisalah', '$box', '$lemari', NOW())
        ";
    } elseif ($jenis_db === 'batal') {
        $insert = "
            INSERT INTO risalah_batal
                (no_risalah, tgl_risalah, nama_pelelang, pemohon_lelang, link_erisalah, tgl_validasi)
            VALUES
                ('$no_risalah', '$tgl_risalah', '$nama_pelelang', '$pemohon_lelang', '$link_erisalah', NOW())
        ";
    } else {
        die("Jenis risalah tidak dikenali! (dapat: {$jenis_db})");
    }

    if (mysqli_query($conn, $insert)) {
        mysqli_query($conn, "DELETE FROM risalah_pending WHERE id='$id'");
        $msg = "✅ Risalah berhasil divalidasi ke tabel {$jenis_db}.";
    } else {
        $msg = "❌ Error Validasi: " . addslashes(mysqli_error($conn));
    }

} else { // aksi === 'revisi' (dikembalikan)
    $insert = mysqli_query($conn, "
        INSERT INTO risalah_revisi
            (no_risalah, jenis, tgl_risalah, nama_pelelang, pemohon_lelang,
             catatan_no, catatan_jenis, catatan_tgl, catatan_pelelang, catatan_pemohon,
             status, tgl_revisi)
        VALUES
            ('$no_risalah', '$jenis_db', '$tgl_risalah', '$nama_pelelang', '$pemohon_lelang',
             '$cat_no', '$cat_jenis', '$cat_tgl', '$cat_pelelang', '$cat_pemohon',
             'dikembalikan', NOW())
    ");

    if ($insert) {
        mysqli_query($conn, "DELETE FROM risalah_pending WHERE id='$id'");
        $msg = "📌 Risalah berhasil dikembalikan untuk revisi.";
    } else {
        $msg = "❌ Error Revisi: " . addslashes(mysqli_error($conn));
    }
}

// ====== NOTIFIKASI ======
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
        title: 'Informasi',
        text: '$msg',
        icon: '".(strpos($msg, '❌') !== false ? "error" : "success")."',
        timer: 2200,
        showConfirmButton: false
    }).then(() => {
        window.location.href = 'validasi.php';
    });
</script>
</body>
</html>";
