<?php
require_once '../config/database.php';
if($_SESSION['role'] != 'siswa') header("Location: ../login.php");
$ujian_id = $_GET['ujian_id'] ?? 0;
$stmt = $pdo->prepare("SELECT u.*, mp.nama_mapel FROM ujian_siswa u JOIN mata_pelajaran mp ON u.mapel_id=mp.id WHERE u.id=? AND u.siswa_id=?");
$stmt->execute([$ujian_id, $_SESSION['user_id']]);
$data = $stmt->fetch();
if(!$data) die("Data tidak ditemukan");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Hasil Ujian</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <div class="card text-center">
        <div class="card-header bg-primary text-white">Hasil Ujian <?= $data['nama_mapel'] ?></div>
        <div class="card-body">
            <h3>Nilai Anda: <strong><?= $data['nilai_total'] ?></strong></h3>
            <p>Status: <?= $data['nilai_total'] >= 70 ? 'LULUS' : 'TIDAK LULUS' ?></p>
            <a href="index.php" class="btn btn-primary">Kembali ke Dashboard</a>
        </div>
    </div>
</div>
</body>
</html>