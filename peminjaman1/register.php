<?php
session_start();
include 'db.php'; // Pastikan ada koneksi ke database

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $confirm = mysqli_real_escape_string($conn, $_POST['confirm']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);

    // Validasi password
    if ($password !== $confirm) {
        echo "<script>alert('Password dan konfirmasi tidak cocok!'); window.history.back();</script>";
        exit;
    }

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Cek username sudah ada atau belum
    $checkUser = mysqli_query($conn, "SELECT * FROM users WHERE username = '$username' OR email = '$email'");
    if (mysqli_num_rows($checkUser) > 0) {
        echo "<script>alert('Username atau email sudah terdaftar!'); window.history.back();</script>";
        exit;
    }

    // Insert ke database
    $query = "INSERT INTO users (username, email, password, role) VALUES ('$username', '$email', '$hashedPassword', '$role')";
    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Registrasi berhasil! Silakan login.'); window.location.href='index.html';</script>";
    } else {
        echo "<script>alert('Terjadi kesalahan: " . mysqli_error($conn) . "'); window.history.back();</script>";
    }
}
?>
