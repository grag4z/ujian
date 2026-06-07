<?php
require_once '../config/database.php';
if($_SESSION['role'] != 'admin') header("Location: ../login.php");

// Tambah/edit siswa
if(isset($_POST['simpan_siswa'])) {
    $id = $_POST['id'] ?? 0;
    $nis = $_POST['nis'];
    $nama = $_POST['nama_lengkap'];
    $kelas = $_POST['kelas'];
    $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;
    if($id) {
        if($password) {
            $sql = "UPDATE siswa SET nis=?, nama_lengkap=?, kelas=?, password=? WHERE id=?";
            $pdo->prepare($sql)->execute([$nis, $nama, $kelas, $password, $id]);
        } else {
            $sql = "UPDATE siswa SET nis=?, nama_lengkap=?, kelas=? WHERE id=?";
            $pdo->prepare($sql)->execute([$nis, $nama, $kelas, $id]);
        }
    } else {
        $hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $sql = "INSERT INTO siswa (nis, nama_lengkap, kelas, password) VALUES (?,?,?,?)";
        $pdo->prepare($sql)->execute([$nis, $nama, $kelas, $hash]);
    }
    header("Location: siswa.php");
    exit;
}

if(isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $pdo->prepare("DELETE FROM siswa WHERE id=?")->execute([$id]);
    header("Location: siswa.php");
    exit;
}

// Import CSV
if(isset($_POST['import_csv']) && $_FILES['file_csv']['error']==0) {
    $file = $_FILES['file_csv']['tmp_name'];
    $handle = fopen($file, "r");
    fgetcsv($handle); // skip header
    while(($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        $nis = $data[0];
        $nama = $data[1];
        $kelas = $data[2];
        $pass_default = password_hash('123456', PASSWORD_DEFAULT);
        $cek = $pdo->prepare("SELECT id FROM siswa WHERE nis=?");
        $cek->execute([$nis]);
        if(!$cek->fetch()) {
            $ins = $pdo->prepare("INSERT INTO siswa (nis, nama_lengkap, kelas, password) VALUES (?,?,?,?)");
            $ins->execute([$nis, $nama, $kelas, $pass_default]);
        }
    }
    fclose($handle);
    header("Location: siswa.php?import=ok");
    exit;
}

$siswa_list = $pdo->query("SELECT * FROM siswa ORDER BY kelas, nama_lengkap")->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Kelola Siswa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-3">
    <h2>Manajemen Akun Siswa</h2>
    <a href="index.php" class="btn btn-secondary">Kembali</a>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalSiswa">Tambah Siswa</button>
    <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#modalImport">Import CSV</button>

    <table id="tabelSiswa" class="table table-bordered mt-3">
        <thead><tr><th>NIS</th><th>Nama Lengkap</th><th>Kelas</th><th>Aksi</th></tr></thead>
        <tbody>
            <?php foreach($siswa_list as $s): ?>
            <tr>
                <td><?= htmlspecialchars($s['nis']) ?></td>
                <td><?= htmlspecialchars($s['nama_lengkap']) ?></td>
                <td><?= htmlspecialchars($s['kelas']) ?></td>
                <td>
                    <button class="btn btn-sm btn-warning editSiswa" data-id="<?= $s['id'] ?>" data-nis="<?= $s['nis'] ?>" data-nama="<?= htmlspecialchars($s['nama_lengkap']) ?>" data-kelas="<?= $s['kelas'] ?>">Edit</button>
                    <a href="?hapus=<?= $s['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus?')">Hapus</a>
                    <button class="btn btn-sm btn-secondary resetPass" data-id="<?= $s['id'] ?>">Reset Password</button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Modal Siswa -->
<div class="modal fade" id="modalSiswa" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header"><h5>Form Siswa</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="siswaId">
                    <div class="mb-2"><label>NIS</label><input type="text" name="nis" id="nis" class="form-control" required></div>
                    <div class="mb-2"><label>Nama Lengkap</label><input type="text" name="nama_lengkap" id="nama_lengkap" class="form-control" required></div>
                    <div class="mb-2"><label>Kelas</label><input type="text" name="kelas" id="kelas" class="form-control" required></div>
                    <div class="mb-2"><label>Password (kosongkan jika tidak diubah)</label><input type="password" name="password" class="form-control"></div>
                </div>
                <div class="modal-footer"><button type="submit" name="simpan_siswa" class="btn btn-primary">Simpan</button></div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Import CSV -->
<div class="modal fade" id="modalImport" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-header"><h5>Import CSV Siswa</h5></div>
                <div class="modal-body">
                    <p>Format CSV: nis,nama_lengkap,kelas (tanpa spasi, baris pertama header). Password default: 123456</p>
                    <input type="file" name="file_csv" accept=".csv" class="form-control" required>
                </div>
                <div class="modal-footer"><button type="submit" name="import_csv" class="btn btn-success">Upload</button></div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script>
$(document).ready(function() {
    $('#tabelSiswa').DataTable();
    $('.editSiswa').click(function() {
        $('#siswaId').val($(this).data('id'));
        $('#nis').val($(this).data('nis'));
        $('#nama_lengkap').val($(this).data('nama'));
        $('#kelas').val($(this).data('kelas'));
        $('#modalSiswa').modal('show');
    });
    $('.resetPass').click(function() {
        let id = $(this).data('id');
        if(confirm('Reset password menjadi 123456?')) {
            $.post('reset_password.php', {id:id}, function(res) {
                alert(res);
                location.reload();
            });
        }
    });
});
</script>
</body>
</html>