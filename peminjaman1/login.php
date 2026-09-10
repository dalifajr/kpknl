<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_input = $_POST['user_input']; // bisa username atau email
 // bisa username atau email
    $password   = $_POST['password'];

    $sql = "SELECT * FROM users WHERE username = '$user_input' OR email = '$user_input' LIMIT 1";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            // Simpan sesi
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role']     = $user['role'];

            // Arahkan berdasarkan role
            if ($user['role'] == 'admin') {
                header("Location: admin_dashboard.php");
            } elseif ($user['role'] == 'pelelang') {
                header("Location: pelelang_dashboard.php");
            } else {
                header("Location: peminjam_dashboard.php");
            }
            exit;
        } else {
            echo "<script>alert('Password salah!'); window.location='index.html';</script>";
        }
    } else {
        echo "<script>alert('Username atau Email tidak ditemukan!'); window.location='index.html';</script>";
    }
}

$conn->close();
?>
