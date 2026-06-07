<?php
require_once '../config/database.php';
if($_SESSION['role'] != 'admin') header("Location: ../login.php");

// Pilih mapel untuk filter
$mapel_list = $pdo->query("SELECT id, nama_mapel FROM mata_pelajaran ORDER BY nama_mapel")->fetchAll();
$mapel_id = $_GET['mapel_id'] ?? ($mapel_list[0]['id'] ?? 0);

// Proses tambah/edit soal
if(isset($_POST['simpan_soal'])) {
    $id = $_POST['id'] ?? 0;
    $mapel_id = $_POST['mapel_id'];
    $teks = $_POST['teks_soal'];
    $a = $_POST['pilihan_a'];
    $b = $_POST['pilihan_b'];
    $c = $_POST['pilihan_c'];
    $d = $_POST['pilihan_d'];
    $jawaban = $_POST['jawaban_benar'];
    $poin = $_POST['poin'];
    if($id) {
        $sql = "UPDATE soal SET mapel_id=?, teks_soal=?, pilihan_a=?, pilihan_b=?, pilihan_c=?, pilihan_d=?, jawaban_benar=?, poin=? WHERE id=?";
        $pdo->prepare($sql)->execute([$mapel_id, $teks, $a, $b, $c, $d, $jawaban, $poin, $id]);
    } else {
        $sql = "INSERT INTO soal (mapel_id, teks_soal, pilihan_a, pilihan_b, pilihan_c, pilihan_d, jawaban_benar, poin) VALUES (?,?,?,?,?,?,?,?)";
        $pdo->prepare($sql)->execute([$mapel_id, $teks, $a, $b, $c, $d, $jawaban, $poin]);
    }
    header("Location: soal.php?mapel_id=$mapel_id");
    exit;
}

if(isset($_GET['hapus'])) {
    $id_soal = $_GET['hapus'];
    $pdo->prepare("DELETE FROM soal WHERE id=?")->execute([$id_soal]);
    header("Location: soal.php?mapel_id=$mapel_id");
    exit;
}

// Import CSV
if(isset($_POST['import_csv_soal']) && $_FILES['file_csv_soal']['error']==0) {
    $file = $_FILES['file_csv_soal']['tmp_name'];
    $handle = fopen($file, "r");
    fgetcsv($handle);
    while(($data = fgetcsv($handle, 5000, ",")) !== FALSE) {
        $teks = $data[0];
        $a = $data[1];
        $b = $data[2];
        $c = $data[3];
        $d = $data[4];
        $jawaban = strtoupper($data[5]);
        $poin = $data[6] ?? 1;
        $ins = $pdo->prepare("INSERT INTO soal (mapel_id, teks_soal, pilihan_a, pilihan_b, pilihan_c, pilihan_d, jawaban_benar, poin) VALUES (?,?,?,?,?,?,?,?)");
        $ins->execute([$mapel_id, $teks, $a, $b, $c, $d, $jawaban, $poin]);
    }
    fclose($handle);
    header("Location: soal.php?mapel_id=$mapel_id");
    exit;
}

// Ambil soal berdasarkan mapel
$soal_list = $pdo->prepare("SELECT * FROM soal WHERE mapel_id=? ORDER BY id");
$soal_list->execute([$mapel_id]);
$soal_list = $soal_list->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Kelola Soal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-3">
    <h2>Manajemen Soal Pilihan Ganda</h2>
    <a href="index.php" class="btn btn-secondary">Kembali</a>
    <form method="GET" class="row g-3 mt-2">
        <div class="col-auto">
            <select name="mapel_id" class="form-select" onchange="this.form.submit()">
                <?php foreach($mapel_list as $mp): ?>
                <option value="<?= $mp['id'] ?>" <?= $mp['id']==$mapel_id ? 'selected' : '' ?>><?= htmlspecialchars($mp['nama_mapel']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-auto">
            <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#modalSoal">Tambah Soal</button>
            <button class="btn btn-info" type="button" data-bs-toggle="modal" data-bs-target="#modalImportSoal">Import CSV</button>
            <a href="template_soal.csv" class="btn btn-secondary">Download Template CSV</a>
        </div>
    </form>

    <table id="tabelSoal" class="table table-bordered mt-3">
        <thead><tr><th>ID</th><th>Soal</th><th>Pilihan A</th><th>B</th><th>C</th><th>D</th><th>Jawaban</th><th>Poin</th><th>Aksi</th></tr></thead>
        <tbody>
            <?php foreach($soal_list as $s): ?>
            <tr>
                <td><?= $s['id'] ?></td>
                <td><?= htmlspecialchars($s['teks_soal']) ?></td>
                <td><?= htmlspecialchars($s['pilihan_a']) ?></td>
                <td><?= htmlspecialchars($s['pilihan_b']) ?></td>
                <td><?= htmlspecialchars($s['pilihan_c']) ?></td>
                <td><?= htmlspecialchars($s['pilihan_d']) ?></td>
                <td><?= $s['jawaban_benar'] ?></td>
                <td><?= $s['poin'] ?></td>
                <td>
                    <button class="btn btn-sm btn-warning editSoal" data-id="<?= $s['id'] ?>" data-teks="<?= htmlspecialchars($s['teks_soal']) ?>" data-a="<?= htmlspecialchars($s['pilihan_a']) ?>" data-b="<?= htmlspecialchars($s['pilihan_b']) ?>" data-c="<?= htmlspecialchars($s['pilihan_c']) ?>" data-d="<?= htmlspecialchars($s['pilihan_d']) ?>" data-jawaban="<?= $s['jawaban_benar'] ?>" data-poin="<?= $s['poin'] ?>">Edit</button>
                    <a href="?hapus=<?= $s['id'] ?>&mapel_id=<?= $mapel_id ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus soal?')">Hapus</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Modal Soal -->
<div class="modal fade" id="modalSoal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header"><h5>Form Soal</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="soalId">
                    <input type="hidden" name="mapel_id" value="<?= $mapel_id ?>">
                    <div class="mb-2"><label>Teks Soal</label><textarea name="teks_soal" id="teks_soal" class="form-control" rows="2" required></textarea></div>
                    <div class="mb-2"><label>Pilihan A</label><input type="text" name="pilihan_a" id="pilihan_a" class="form-control" required></div>
                    <div class="mb-2"><label>Pilihan B</label><input type="text" name="pilihan_b" id="pilihan_b" class="form-control" required></div>
                    <div class="mb-2"><label>Pilihan C</label><input type="text" name="pilihan_c" id="pilihan_c" class="form-control" required></div>
                    <div class="mb-2"><label>Pilihan D</label><input type="text" name="pilihan_d" id="pilihan_d" class="form-control" required></div>
                    <div class="mb-2"><label>Jawaban Benar</label>
                        <select name="jawaban_benar" id="jawaban_benar" class="form-select">
                            <option value="A">A</option><option value="B">B</option><option value="C">C</option><option value="D">D</option>
                        </select>
                    </div>
                    <div class="mb-2"><label>Poin</label><input type="number" name="poin" id="poin" class="form-control" value="1"></div>
                </div>
                <div class="modal-footer"><button type="submit" name="simpan_soal" class="btn btn-primary">Simpan</button></div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Import CSV Soal -->
<div class="modal fade" id="modalImportSoal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-header"><h5>Import CSV Soal</h5></div>
                <div class="modal-body">
                    <p>Format: teks_soal, pilihan_a, pilihan_b, pilihan_c, pilihan_d, jawaban_benar(A/B/C/D), poin (opsional)</p>
                    <input type="file" name="file_csv_soal" accept=".csv" class="form-control" required>
                </div>
                <div class="modal-footer"><button type="submit" name="import_csv_soal" class="btn btn-success">Upload</button></div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script>
$(document).ready(function() {
    $('#tabelSoal').DataTable();
    $('.editSoal').click(function() {
        $('#soalId').val($(this).data('id'));
        $('#teks_soal').val($(this).data('teks'));
        $('#pilihan_a').val($(this).data('a'));
        $('#pilihan_b').val($(this).data('b'));
        $('#pilihan_c').val($(this).data('c'));
        $('#pilihan_d').val($(this).data('d'));
        $('#jawaban_benar').val($(this).data('jawaban'));
        $('#poin').val($(this).data('poin'));
        $('#modalSoal').modal('show');
    });
});
</script>
</body>
</html>