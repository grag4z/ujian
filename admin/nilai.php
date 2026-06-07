<?php
require_once '../config/database.php';
if($_SESSION['role'] != 'admin') header("Location: ../login.php");

// Ambil data untuk filter
$mapel_list = $pdo->query("SELECT id, nama_mapel FROM mata_pelajaran ORDER BY nama_mapel")->fetchAll();
$kelas_list = $pdo->query("SELECT DISTINCT kelas FROM siswa WHERE kelas IS NOT NULL ORDER BY kelas")->fetchAll();

$filter_mapel = $_GET['mapel_id'] ?? '';
$filter_kelas = $_GET['kelas'] ?? '';

$sql = "SELECT s.nis, s.nama_lengkap, s.kelas, mp.nama_mapel, us.nilai_total, us.tanggal_selesai 
        FROM ujian_siswa us 
        JOIN siswa s ON us.siswa_id = s.id 
        JOIN mata_pelajaran mp ON us.mapel_id = mp.id 
        WHERE us.is_finished=1";
$params = [];
if($filter_mapel) {
    $sql .= " AND us.mapel_id = ?";
    $params[] = $filter_mapel;
}
if($filter_kelas) {
    $sql .= " AND s.kelas = ?";
    $params[] = $filter_kelas;
}
$sql .= " ORDER BY s.kelas, s.nama_lengkap";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$nilai_list = $stmt->fetchAll();

// Statistik
if($filter_mapel) {
    $stat = $pdo->prepare("SELECT AVG(nilai_total) as rata, MAX(nilai_total) as tertinggi, MIN(nilai_total) as terendah FROM ujian_siswa WHERE mapel_id=? AND is_finished=1");
    $stat->execute([$filter_mapel]);
    $stat_data = $stat->fetch();
} else {
    $stat_data = ['rata'=>0, 'tertinggi'=>0, 'terendah'=>0];
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Laporan Nilai</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-3">
    <h2>Laporan & Statistik Nilai Ujian</h2>
    <a href="index.php" class="btn btn-secondary mb-3">Kembali</a>

    <form method="GET" class="row g-3 mb-4">
        <div class="col-md-4">
            <label>Mata Pelajaran</label>
            <select name="mapel_id" class="form-select">
                <option value="">Semua Mapel</option>
                <?php foreach($mapel_list as $m): ?>
                <option value="<?= $m['id'] ?>" <?= $filter_mapel==$m['id'] ? 'selected' : '' ?>><?= htmlspecialchars($m['nama_mapel']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label>Kelas</label>
            <select name="kelas" class="form-select">
                <option value="">Semua Kelas</option>
                <?php foreach($kelas_list as $k): ?>
                <option value="<?= $k['kelas'] ?>" <?= $filter_kelas==$k['kelas'] ? 'selected' : '' ?>><?= $k['kelas'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4 align-self-end">
            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="export_nilai_excel.php?mapel_id=<?= $filter_mapel ?>&kelas=<?= $filter_kelas ?>" class="btn btn-success">Export Excel</a>
        </div>
    </form>

    <?php if($filter_mapel): ?>
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card text-white bg-primary">
                <div class="card-body"><h5>Rata-rata</h5><h3><?= number_format($stat_data['rata'],2) ?></h3></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-success">
                <div class="card-body"><h5>Tertinggi</h5><h3><?= $stat_data['tertinggi'] ?></h3></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-danger">
                <div class="card-body"><h5>Terendah</h5><h3><?= $stat_data['terendah'] ?></h3></div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <table id="tabelNilai" class="table table-bordered">
        <thead>
            <tr><th>NIS</th><th>Nama Siswa</th><th>Kelas</th><th>Mata Pelajaran</th><th>Nilai</th><th>Tanggal Selesai</th></tr>
        </thead>
        <tbody>
            <?php foreach($nilai_list as $n): ?>
            <tr>
                <td><?= htmlspecialchars($n['nis']) ?></td>
                <td><?= htmlspecialchars($n['nama_lengkap']) ?></td>
                <td><?= htmlspecialchars($n['kelas']) ?></td>
                <td><?= htmlspecialchars($n['nama_mapel']) ?></td>
                <td><?= $n['nilai_total'] ?></td>
                <td><?= $n['tanggal_selesai'] ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script>
$(document).ready(function() {
    $('#tabelNilai').DataTable();
});
</script>
</body>
</html>