<?php
require_once '../config/database.php';
if($_SESSION['role'] != 'admin') exit("Akses ditolak");
$id = $_POST['id'] ?? 0;
$new_pass = password_hash('123456', PASSWORD_DEFAULT);
$pdo->prepare("UPDATE siswa SET password=? WHERE id=?")->execute([$new_pass, $id]);
echo "Password direset menjadi 123456";
?>