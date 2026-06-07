<?php
require_once '../config/database.php';
if($_SESSION['role'] != 'siswa') header("Location: ../login.php");
$siswa_id = $_SESSION['user_id'];
$mapel_id = $_GET['mapel_id'] ?? 0;

// Cek apakah ujian sudah pernah
$stmt = $pdo->prepare("SELECT id, is_finished FROM ujian_siswa WHERE siswa_id=? AND mapel_id=?");
$stmt->execute([$siswa_id, $mapel_id]);
$ujian = $stmt->fetch();
if($ujian && $ujian['is_finished']) {
    die("<script>alert('Anda sudah mengerjakan ujian ini!'); location.href='index.php';</script>");
}

// Ambil data mapel & soal
$mapel = $pdo->prepare("SELECT * FROM mata_pelajaran WHERE id=? AND is_active=1 AND NOW() BETWEEN tgl_mulai AND tgl_selesai");
$mapel->execute([$mapel_id]);
$mp = $mapel->fetch();
if(!$mp) die("Ujian tidak tersedia.");

$soal = $pdo->prepare("SELECT * FROM soal WHERE mapel_id=? ORDER BY RAND()");
$soal->execute([$mapel_id]);
$soal_list = $soal->fetchAll();

// Jika belum ada record ujian, buat baru
if(!$ujian) {
    $insert = $pdo->prepare("INSERT INTO ujian_siswa (siswa_id, mapel_id, tanggal_mulai) VALUES (?,?, NOW())");
    $insert->execute([$siswa_id, $mapel_id]);
    $ujian_id = $pdo->lastInsertId();
} else {
    $ujian_id = $ujian['id'];
}

// Proses submit jawaban
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['selesai'])) {
    $jawaban = $_POST['jawaban'] ?? [];
    $benar = 0;
    foreach($soal_list as $s) {
        $jawab = $jawaban[$s['id']] ?? NULL;
        $is_benar = ($jawab == $s['jawaban_benar']) ? 1 : 0;
        if($is_benar) $benar++;
        // simpan atau update jawaban
        $cek = $pdo->prepare("SELECT id FROM jawaban_siswa WHERE ujian_id=? AND soal_id=?");
        $cek->execute([$ujian_id, $s['id']]);
        if($cek->fetch()) {
            $upd = $pdo->prepare("UPDATE jawaban_siswa SET jawaban_siswa=?, is_benar=? WHERE ujian_id=? AND soal_id=?");
            $upd->execute([$jawab, $is_benar, $ujian_id, $s['id']]);
        } else {
            $ins = $pdo->prepare("INSERT INTO jawaban_siswa (ujian_id, soal_id, jawaban_siswa, is_benar) VALUES (?,?,?,?)");
            $ins->execute([$ujian_id, $s['id'], $jawab, $is_benar]);
        }
    }
    $total_soal = count($soal_list);
    $nilai = round(($benar / $total_soal) * 100);
    $update_ujian = $pdo->prepare("UPDATE ujian_siswa SET tanggal_selesai=NOW(), nilai_total=?, is_finished=1 WHERE id=?");
    $update_ujian->execute([$nilai, $ujian_id]);
    header("Location: hasil.php?ujian_id=$ujian_id");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Ujian - <?= htmlspecialchars($mp['nama_mapel']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: Arial; }
        .timer { font-size: 24px; font-weight: bold; color: red; position: sticky; top: 0; background: white; padding: 10px; }
    </style>
</head>
<body>
<div class="container mt-3">
    <div class="timer" id="timer">00:00</div>
    <form method="POST" id="formUjian">
        <?php $no=1; foreach($soal_list as $s): ?>
        <div class="card mb-3">
            <div class="card-body">
                <h5>Soal <?= $no++ ?>. <?= htmlspecialchars($s['teks_soal']) ?></h5>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="jawaban[<?= $s['id'] ?>]" value="A" id="soal<?= $s['id'] ?>A">
                    <label class="form-check-label" for="soal<?= $s['id'] ?>A">A. <?= htmlspecialchars($s['pilihan_a']) ?></label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="jawaban[<?= $s['id'] ?>]" value="B" id="soal<?= $s['id'] ?>B">
                    <label class="form-check-label" for="soal<?= $s['id'] ?>B">B. <?= htmlspecialchars($s['pilihan_b']) ?></label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="jawaban[<?= $s['id'] ?>]" value="C" id="soal<?= $s['id'] ?>C">
                    <label class="form-check-label" for="soal<?= $s['id'] ?>C">C. <?= htmlspecialchars($s['pilihan_c']) ?></label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="jawaban[<?= $s['id'] ?>]" value="D" id="soal<?= $s['id'] ?>D">
                    <label class="form-check-label" for="soal<?= $s['id'] ?>D">D. <?= htmlspecialchars($s['pilihan_d']) ?></label>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        <button type="submit" name="selesai" class="btn btn-success w-100 mb-5" onclick="return confirm('Yakin selesai?')">Selesai & Lihat Nilai</button>
    </form>
</div>
<script>
    let durasiMenit = <?= $mp['durasi_menit'] ?>;
    let timerDisplay = document.getElementById('timer');
    let waktu = durasiMenit * 60;
    function updateTimer() {
        let menit = Math.floor(waktu / 60);
        let detik = waktu % 60;
        timerDisplay.innerText = `${menit.toString().padStart(2,'0')}:${detik.toString().padStart(2,'0')}`;
        if(waktu <= 0) {
            alert('Waktu habis! Ujian akan dikirim.');
            document.getElementById('formUjian').submit();
        }
        waktu--;
    }
    setInterval(updateTimer, 1000);
</script>
</body>
</html>