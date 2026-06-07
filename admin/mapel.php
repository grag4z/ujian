<?php
require_once '../config/database.php';
if($_SESSION['role'] != 'admin') header("Location: ../login.php");

// Proses tambah/edit/hapus mapel
if(isset($_POST['simpan'])) {
    $id = $_POST['id'] ?? 0;
    $nama = $_POST['nama_mapel'];
    $deskripsi = $_POST['deskripsi'];
    $durasi = $_POST['durasi_menit'];
    $tgl_mulai = $_POST['tgl_mulai'];
    $tgl_selesai = $_POST['tgl_selesai'];
    $passing = $_POST['passing_grade'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    if($id) {
        $sql = "UPDATE mata_pelajaran SET nama_mapel=?, deskripsi=?, durasi_menit=?, tgl_mulai=?, tgl_selesai=?, passing_grade=?, is_active=? WHERE id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nama, $deskripsi, $durasi, $tgl_mulai, $tgl_selesai, $passing, $is_active, $id]);
    } else {
        $sql = "INSERT INTO mata_pelajaran (nama_mapel, deskripsi, durasi_menit, tgl_mulai, tgl_selesai, passing_grade, is_active) VALUES (?,?,?,?,?,?,?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nama, $deskripsi, $durasi, $tgl_mulai, $tgl_selesai, $passing, $is_active]);
    }
    header("Location: mapel.php");
    exit;
}

if(isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $pdo->prepare("DELETE FROM mata_pelajaran WHERE id=?")->execute([$id]);
    header("Location: mapel.php");
    exit;
}

$mapel_list = $pdo->query("SELECT * FROM mata_pelajaran ORDER BY tgl_mulai DESC")->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Kelola Mata Pelajaran</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-3">
    <h2>Manajemen Mata Pelajaran & Jadwal</h2>
    <a href="index.php" class="btn btn-secondary mb-3">Kembali ke Dashboard</a>
    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#modalMapel">Tambah Mapel</button>

    <table id="tabelMapel" class="table table-bordered">
        <thead>
            <tr><th>ID</th><th>Nama Mapel</th><th>Durasi (menit)</th><th>Jadwal Mulai</th><th>Jadwal Selesai</th><th>Passing Grade</th><th>Aktif</th><th>Aksi</th></tr>
        </thead>
        <tbody>
            <?php foreach($mapel_list as $m): ?>
            <tr>
                <td><?= $m['id'] ?></td>
                <td><?= htmlspecialchars($m['nama_mapel']) ?></td>
                <td><?= $m['durasi_menit'] ?></td>
                <td><?= $m['tgl_mulai'] ?></td>
                <td><?= $m['tgl_selesai'] ?></td>
                <td><?= $m['passing_grade'] ?></td>
                <td><?= $m['is_active'] ? 'Ya' : 'Tidak' ?></td>
                <td>
                    <button class="btn btn-sm btn-warning editBtn" data-id="<?= $m['id'] ?>" data-nama="<?= htmlspecialchars($m['nama_mapel']) ?>" data-deskripsi="<?= htmlspecialchars($m['deskripsi']) ?>" data-durasi="<?= $m['durasi_menit'] ?>" data-mulai="<?= $m['tgl_mulai'] ?>" data-selesai="<?= $m['tgl_selesai'] ?>" data-passing="<?= $m['passing_grade'] ?>" data-active="<?= $m['is_active'] ?>">Edit</button>
                    <a href="?hapus=<?= $m['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus mapel? Semua soal dan ujian terkait akan hilang.')">Hapus</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Modal Tambah/Edit -->
<div class="modal fade" id="modalMapel" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header"><h5 class="modal-title">Form Mapel</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="mapelId">
                    <div class="mb-2"><label>Nama Mapel</label><input type="text" name="nama_mapel" id="nama_mapel" class="form-control" required></div>
                    <div class="mb-2"><label>Deskripsi</label><textarea name="deskripsi" id="deskripsi" class="form-control"></textarea></div>
                    <div class="mb-2"><label>Durasi (menit)</label><input type="number" name="durasi_menit" id="durasi_menit" class="form-control" required></div>
                    <div class="mb-2"><label>Tanggal Mulai Ujian</label><input type="datetime-local" name="tgl_mulai" id="tgl_mulai" class="form-control" required></div>
                    <div class="mb-2"><label>Tanggal Selesai Ujian</label><input type="datetime-local" name="tgl_selesai" id="tgl_selesai" class="form-control" required></div>
                    <div class="mb-2"><label>Passing Grade (0-100)</label><input type="number" name="passing_grade" id="passing_grade" class="form-control" required></div>
                    <div class="mb-2 form-check"><input type="checkbox" name="is_active" id="is_active" class="form-check-input" value="1"> <label class="form-check-label">Aktif</label></div>
                </div>
                <div class="modal-footer"><button type="submit" name="simpan" class="btn btn-primary">Simpan</button></div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script>
$(document).ready(function() {
    $('#tabelMapel').DataTable();
    $('.editBtn').click(function() {
        $('#mapelId').val($(this).data('id'));
        $('#nama_mapel').val($(this).data('nama'));
        $('#deskripsi').val($(this).data('deskripsi'));
        $('#durasi_menit').val($(this).data('durasi'));
        $('#tgl_mulai').val($(this).data('mulai'));
        $('#tgl_selesai').val($(this).data('selesai'));
        $('#passing_grade').val($(this).data('passing'));
        $('#is_active').prop('checked', $(this).data('active') == 1);
        $('#modalMapel').modal('show');
    });
});
</script>
</body>
</html>