<?php
session_start();
include 'db.php';

// Ambil data dari form login
$username = $_POST['username'];
$password = $_POST['password'];

// Cek user dari database
$sql = "SELECT * FROM users WHERE username='$username' OR email='$username' LIMIT 1";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();

    // Cek password dengan password_verify
    if (password_verify($password, $row['password'])) {
         // ANAK MAGANG UIN RADEN FATAH PALEMBANG 
    //AWANG, AULIA, DINDA, MESYA, YONIZA
        $_SESSION['username'] = $row['username'];
        $_SESSION['role'] = $row['role'];

        // Redirect sesuai role
        if ($row['role'] == 'admin') {
            header("Location: dashboard_admin.php");
        } elseif ($row['role'] == 'pelelang') {
            header("Location: dashboard_pelelang.php");
        } else {
            header("Location: dashboard_peminjam.php");
        }
        exit();
    } else {
        echo "<script>alert('Password salah!'); window.location='login.php';</script>";
    }
} else {
    echo "<script>alert('User tidak ditemukan!'); window.location='login.php';</script>";
}

$conn->close();
?>
