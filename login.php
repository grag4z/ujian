<?php
session_start();
if(isset($_SESSION['user_id'])) {
    header("Location: ". ($_SESSION['role'] == 'admin' ? 'admin/' : 'siswa/'));
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login Ujian Sekolah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5" style="max-width: 500px;">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">Login Sistem Ujian</div>
        <div class="card-body">
            <form action="proses_login.php" method="POST">
                <div class="mb-3">
                    <label>Username / NIS</label>
                    <input type="text" name="username" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Role</label>
                    <select name="role" class="form-select">
                        <option value="siswa">Siswa</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary w-100">Login</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>