<?php
require_once '../config/database.php';
if($_SESSION['role'] != 'siswa') header("Location: ../login.php");
$siswa_id = $_SESSION['user_id'];

// Ambil mapel yang aktif dan dalam jadwal (tgl_mulai <= now <= tgl_selesai) dan belum diujikan
$sql = "SELECT mp.*, 
        (SELECT COUNT(*) FROM soal WHERE mapel_id=mp.id) as total_soal,
        (SELECT id FROM ujian_siswa WHERE siswa_id=? AND mapel_id=mp.id AND is_finished=1) as sudah_ujian
        FROM mata_pelajaran mp 
        WHERE is_active=1 AND NOW() BETWEEN tgl_mulai AND tgl_selesai
        ORDER BY mp.tgl_mulai";
$stmt = $pdo->prepare($sql);
$stmt->execute([$siswa_id]);
$mapel = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard Siswa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <div class="d-flex justify-content-between">
        <h2>Halo, <?= htmlspecialchars($_SESSION['nama']) ?></h2>
        <a href="../logout.php" class="btn btn-danger">Logout</a>
    </div>
    <hr>
    <h4>Pilih Mata Pelajaran</h4>
    <div class="row">
        <?php foreach($mapel as $m): ?>
        <div class="col-md-4 mb-3">
            <div class="card">
                <div class="card-body">
                    <h5><?= htmlspecialchars($m['nama_mapel']) ?></h5>
                    <p>Durasi: <?= $m['durasi_menit'] ?> menit | Soal: <?= $m['total_soal'] ?></p>
                    <p>Jadwal: <?= date('d/m/Y H:i', strtotime($m['tgl_mulai'])) ?> s.d <?= date('d/m/Y H:i', strtotime($m['tgl_selesai'])) ?></p>
                    <?php if($m['sudah_ujian']): ?>
                        <button class="btn btn-secondary" disabled>Sudah Ujian</button>
                    <?php else: ?>
                        <a href="ujian.php?mapel_id=<?= $m['id'] ?>" class="btn btn-primary">Mulai Ujian</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
</body>
</html>