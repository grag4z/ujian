<?php
require_once '../config/database.php';
if($_SESSION['role'] != 'admin') header("Location: ../login.php");

// Ambil jumlah total siswa
$total_siswa = $pdo->query("SELECT COUNT(*) FROM siswa")->fetchColumn();

// Ambil jumlah total mapel aktif
$total_mapel = $pdo->query("SELECT COUNT(*) FROM mata_pelajaran WHERE is_active=1")->fetchColumn();

// Ambil jumlah total ujian yang sudah selesai
$total_ujian_selesai = $pdo->query("SELECT COUNT(*) FROM ujian_siswa WHERE is_finished=1")->fetchColumn();

// Rata-rata nilai seluruh ujian
$avg_nilai = $pdo->query("SELECT AVG(nilai_total) FROM ujian_siswa WHERE is_finished=1")->fetchColumn();

// Data untuk grafik batang: 5 mapel dengan jumlah peserta ujian
$grafik_mapel = $pdo->query("SELECT mp.nama_mapel, COUNT(us.id) as jumlah 
    FROM mata_pelajaran mp 
    LEFT JOIN ujian_siswa us ON mp.id=us.mapel_id AND us.is_finished=1 
    GROUP BY mp.id LIMIT 5")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Ujian Sekolah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .card-stats { border-left: 5px solid #0d6efd; }
    </style>
</head>
<body>
<nav class="navbar navbar-dark bg-dark">
    <div class="container-fluid">
        <span class="navbar-brand">Admin Panel Ujian Sekolah</span>
        <div class="d-flex">
            <span class="navbar-text me-3">Halo, <?= htmlspecialchars($_SESSION['nama']) ?></span>
            <a href="../logout.php" class="btn btn-outline-light btn-sm">Logout</a>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-3 mb-3">
            <div class="card card-stats">
                <div class="card-body">
                    <h5 class="card-title">Total Siswa</h5>
                    <h2><?= $total_siswa ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card card-stats">
                <div class="card-body">
                    <h5 class="card-title">Mapel Aktif</h5>
                    <h2><?= $total_mapel ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card card-stats">
                <div class="card-body">
                    <h5 class="card-title">Ujian Selesai</h5>
                    <h2><?= $total_ujian_selesai ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card card-stats">
                <div class="card-body">
                    <h5 class="card-title">Rata-rata Nilai</h5>
                    <h2><?= number_format($avg_nilai, 2) ?></h2>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Grafik Peserta Ujian per Mapel (Top 5)</div>
                <div class="card-body">
                    <canvas id="mapelChart" width="400" height="250"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Menu Cepat</div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="siswa.php" class="btn btn-primary">Kelola Siswa</a>
                        <a href="mapel.php" class="btn btn-success">Kelola Mata Pelajaran & Jadwal</a>
                        <a href="soal.php" class="btn btn-warning">Kelola Soal (Upload/Edit)</a>
                        <a href="nilai.php" class="btn btn-info">Lihat Nilai & Statistik</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const ctx = document.getElementById('mapelChart').getContext('2d');
    const labels = <?= json_encode(array_column($grafik_mapel, 'nama_mapel')) ?>;
    const data = <?= json_encode(array_column($grafik_mapel, 'jumlah')) ?>;
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Jumlah Peserta Ujian',
                data: data,
                backgroundColor: '#0d6efd'
            }]
        }
    });
</script>
</body>
</html>