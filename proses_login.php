<?php
require_once 'config/database.php';
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';
$role = $_POST['role'] ?? 'siswa';

if($role == 'admin') {
    $stmt = $pdo->prepare("SELECT * FROM admin WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    if($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = 'admin';
        $_SESSION['nama'] = $user['nama_lengkap'];
        header("Location: admin/");
        exit;
    }
} else {
    $stmt = $pdo->prepare("SELECT * FROM siswa WHERE nis = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    if($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = 'siswa';
        $_SESSION['nama'] = $user['nama_lengkap'];
        $_SESSION['nis'] = $user['nis'];
        header("Location: siswa/");
        exit;
    }
}
header("Location: login.php?error=1");
?>