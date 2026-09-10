<?php
session_start();

// Cek login
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'peminjam') {
    header("Location: index.html");
    exit();
}

// Pastikan ada risalah yang dipilih
if (!isset($_POST['selected_ids']) || !is_array($_POST['selected_ids'])) {
    echo "<script>alert('Tidak ada risalah yang dipilih!'); window.location.href='pinjam_risalah.php';</script>";
    exit();
}

$selected_ids = $_POST['selected_ids'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Alasan Peminjaman</title>
<style>
    body {
        font-family: Arial, sans-serif;
        background: #eef6f7;
        padding: 20px;
    }
    .container {
        max-width: 600px;
        margin: auto;
        background: white;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    textarea {
        width: 100%;
        height: 120px;
        padding: 10px;
        font-size: 14px;
    }
    button {
        padding: 10px 20px;
        background: #0a3d62;
        border: none;
        color: white;
        font-size: 15px;
        border-radius: 6px;
        cursor: pointer;
    }
    button:hover {
        background: #3c6382;
    }
</style>
</head>
<body>

<div class="container">
    <h2>Masukkan Alasan Peminjaman</h2>

    <form action="proses_pinjam.php" method="POST">

        <!-- Kirim ulang ID risalah yang dipilih -->
        <?php foreach ($selected_ids as $id): ?>
            <input type="hidden" name="selected_ids[]" value="<?= htmlspecialchars($id) ?>">
        <?php endforeach; ?>

        <label>Alasan Peminjaman:</label>
        <textarea name="alasan_peminjaman" placeholder="Tuliskan alasan peminjaman..." required></textarea>

        <br><br>
        <button type="submit">Kirim & Proses Peminjaman</button>
    </form>
</div>

</body>
</html>