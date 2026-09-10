<?php
include 'db.php';

$id = isset($_GET['id']) ? $_GET['id'] : '';

if ($id == '') {
    die("ID tidak ditemukan di URL.");
}

// Ambil data risalah dari table pending
$query = "SELECT * FROM risalah_pending WHERE id='".mysqli_real_escape_string($conn, $id)."'";
$result = mysqli_query($conn, $query);

if (!$result) {
    die("Query gagal: " . mysqli_error($conn));
}

$data = mysqli_fetch_assoc($result);

if (!$data) {
    die("Data tidak ditemukan untuk ID: $id");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Validasi Risalah</title>
<link rel="stylesheet" href="style_dashboard.css">
<style>
.container {
    width: 550px;
    margin: 30px auto;
    background: #fff;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 0 10px #ccc;
    font-family: Arial, sans-serif;
}
label {
    font-weight: bold;
    display: inline-block;
    margin-top: 10px;
}
.input-wrap {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 6px;
}
input[disabled] {
    background: #f7f7f7;
}
.note {
    display: none;           /* disembunyikan dulu */
    margin-top: 6px;
}
.note textarea {
    width: 100%;
    padding: 8px;
    border: 1px solid #dcdcdc;
    border-radius: 5px;
}
.pen-btn {
    border: none;
    background: #eef6ff;
    color: #0a3d62;
    padding: 6px 8px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
}
.pen-btn:hover {
    background: #d8ebff;
}
button {
    padding: 10px 15px;
    margin-top: 14px;
    border: none;
    cursor: pointer;
    border-radius: 5px;
}
.btn-valid {
    background: #28a745;
    color: #fff;
}
.btn-kembali {
    background: #dc3545;
    color: #fff;
    margin-left: 10px;
}
.helper {
    font-size: 12px; color: #666; margin-top: 4px;
}
.divider {
    border-top: 1px dashed #e0e0e0; margin: 14px 0;
}
</style>
</head>
<body>
<div class="container">
<h2>Validasi Risalah</h2>

<form action="proses_validasi.php" method="POST">
    <input type="hidden" name="id" value="<?php echo htmlspecialchars($data['id']); ?>">
    <input type="hidden" name="status" id="statusField" value="valid">

    <!-- No Risalah -->
    <label>No Risalah:</label>
    <div class="input-wrap">
        <input type="text" value="<?php echo htmlspecialchars($data['no_risalah']); ?>" disabled>
        <button type="button" class="pen-btn" data-target="note_no">✏️ Catatan</button>
    </div>
    <div id="note_no" class="note">
        <textarea name="catatan_no" placeholder="Catatan untuk No Risalah..."></textarea>
        <div class="helper">Catatan ini akan dikirim ke pelelang jika Anda mengembalikan sebagai revisi.</div>
    </div>

    <div class="divider"></div>

    <!-- Jenis -->
    <label>Jenis:</label>
    <div class="input-wrap">
        <input type="text" value="<?php echo htmlspecialchars($data['jenis']); ?>" disabled>
        
    </div>
    <div id="note_jenis" class="note">
        <textarea name="catatan_jenis" placeholder="Catatan untuk Jenis..."></textarea>
    </div>

    <div class="divider"></div>

    <!-- Tanggal Risalah -->
    <label>Tanggal Risalah:</label>
    <div class="input-wrap">
        <input type="text" value="<?php echo htmlspecialchars($data['tgl_risalah']); ?>" disabled>
        <button type="button" class="pen-btn" data-target="note_tgl">✏️ Catatan</button>
    </div>
    <div id="note_tgl" class="note">
        <textarea name="catatan_tgl" placeholder="Catatan untuk Tanggal Risalah..."></textarea>
    </div>

    <div class="divider"></div>

    <!-- Nama Pelelang -->
    <label>Pelelang:</label>
    <div class="input-wrap">
        <input type="text" value="<?php echo htmlspecialchars($data['nama_pelelang']); ?>" disabled>
        
    </div>
    <div id="note_pelelang" class="note">
        <textarea name="catatan_pelelang" placeholder="Catatan untuk Pelelang..."></textarea>
    </div>

    <div class="divider"></div>

    <!-- Pemohon Lelang -->
    <label>Pemohon Lelang:</label>
    <div class="input-wrap">
        <input type="text" value="<?php echo htmlspecialchars($data['pemohon_lelang']); ?>" disabled>
        <button type="button" class="pen-btn" data-target="note_pemohon">✏️ Catatan</button>
    </div>
    <div id="note_pemohon" class="note">
        <textarea name="catatan_pemohon" placeholder="Catatan untuk Pemohon Lelang..."></textarea>
    </div>

    <div class="divider"></div>

    <!-- Tambahan kolom Box & Lemari -->
    <label>Box:</label>
    <input type="text" name="box" placeholder="Isi nomor box..." required>

    <label>Lemari:</label>
    <input type="text" name="lemari" placeholder="Isi nomor lemari..." required>
    
    <label>Link Scan:</label>
    <input type="url" name="link_erisalah" placeholder="Link E-Risalah (https://...)"><br>

    <br>
    <button type="submit" name="aksi" value="validasi" class="btn-valid" onclick="setStatus('valid')">Validasi</button>
    <button type="submit" name="aksi" value="revisi" class="btn-kembali" onclick="setStatus('revisi')">Kembalikan</button>
</form>
</div>

<script>
function setStatus(status){
    document.getElementById('statusField').value = status;
}

// toggle catatan per tombol pena
document.querySelectorAll('.pen-btn').forEach(function(btn){
    btn.addEventListener('click', function(){
        var id = this.getAttribute('data-target');
        var note = document.getElementById(id);
        if (!note) return;
        note.style.display = (note.style.display === 'block') ? 'none' : 'block';
    });
});
</script>
</body>
</html>
