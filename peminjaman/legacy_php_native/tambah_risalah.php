<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Tambah Risalah</title>
  <link rel="stylesheet" href="style_dashboard.css">
  <script>
    function showForm(formId) {
      document.querySelectorAll('.form-box').forEach(f => f.style.display = 'none');
      document.getElementById(formId).style.display = 'block';
    }
  </script>
</head>
<body>
  
  <div class="container">
    <div class="logout">
    <a href="pelelang_dashboard.php">← Kembali</a>
</div>

    <h2>TAMBAH RISALAH LELANG</h2>
    <img src="logo.jpg" alt="Logo" class="logo">

    <div class="menu">
      <button onclick="showForm('formMinuta')">Minuta</button>
      <button onclick="showForm('formTAP')">TAP</button>
      <button onclick="showForm('formBatal')">Batal</button>
    </div>

    <!-- FORM MINUTA -->
<div id="formMinuta" class="form-box" style="display:none;">
  <h3>Form Minuta</h3>
  <form action="proses_tambah.php" method="POST">
    <input type="hidden" name="jenis" value="minuta">
    <input type="text" name="no_risalah" placeholder="No. Risalah" required><br>
    <input type="date" name="tgl_risalah" required><br>
    <label>Nama Pelelang</label><br>
    <input type="text" name="nama_pelelang" value="<?php echo $_SESSION['username']; ?>" readonly><br><br>
    <input type="text" name="pemohon_lelang" placeholder="Pemohon Lelang" required><br>
    
    <!-- Tambahan -->
 
    
    <button type="submit">Tambah</button>
  </form>
</div>

<!-- FORM TAP -->
<div id="formTAP" class="form-box" style="display:none;">
  <h3>Form TAP</h3>
  <form action="proses_tambah.php" method="POST">
    <input type="hidden" name="jenis" value="TAP">
    <input type="text" name="no_risalah" placeholder="No. Register TAP" required><br>
    <input type="date" name="tgl_risalah" required><br>
    <label>Nama Pelelang</label><br>
    <input type="text" name="nama_pelelang" value="<?php echo $_SESSION['username']; ?>" readonly><br><br>
    <input type="text" name="pemohon_lelang" placeholder="Pemohon Lelang" required><br>
  
    
    <button type="submit">Tambah</button>
  </form>
</div>

<!-- FORM BATAL -->
<div id="formBatal" class="form-box" style="display:none;">
  <h3>Form Batal</h3>
  <form action="proses_tambah.php" method="POST">
    <input type="hidden" name="jenis" value="batal">
    <input type="text" name="no_risalah" placeholder="No. Register Batal" required><br>
    <input type="date" name="tgl_risalah" required><br>
    <label>Nama Pelelang</label><br>
    <input type="text" name="nama_pelelang" value="<?php echo $_SESSION['username']; ?>" readonly><br><br>
    <input type="text" name="pemohon_lelang" placeholder="Pemohon Lelang" required><br>
    
    <!-- Tambahan -->
    
    <button type="submit">Tambah</button>
  </form>
</div>

    </div>
  </div>
</body>
</html>
