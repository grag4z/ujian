<?php
require_once '../config/database.php';
if($_SESSION['role'] != 'admin') exit;
$filter_mapel = $_GET['mapel_id'] ?? '';
$filter_kelas = $_GET['kelas'] ?? '';
$sql = "SELECT s.nis, s.nama_lengkap, s.kelas, mp.nama_mapel, us.nilai_total, us.tanggal_selesai 
        FROM ujian_siswa us 
        JOIN siswa s ON us.siswa_id = s.id 
        JOIN mata_pelajaran mp ON us.mapel_id = mp.id 
        WHERE us.is_finished=1";
$params = [];
if($filter_mapel) { $sql .= " AND us.mapel_id = ?"; $params[] = $filter_mapel; }
if($filter_kelas) { $sql .= " AND s.kelas = ?"; $params[] = $filter_kelas; }
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$data = $stmt->fetchAll();

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="laporan_nilai.csv"');
$output = fopen('php://output', 'w');
fputcsv($output, ['NIS','Nama Siswa','Kelas','Mata Pelajaran','Nilai','Tanggal Selesai']);
foreach($data as $row) {
    fputcsv($output, $row);
}
fclose($output);
exit;
?>