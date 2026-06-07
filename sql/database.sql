-- database: ujian_sekolah
CREATE DATABASE IF NOT EXISTS ujian_sekolah;
USE ujian_sekolah;

-- tabel admin
CREATE TABLE admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL, -- bcrypt hash
    nama_lengkap VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- tabel mata_pelajaran
CREATE TABLE mata_pelajaran (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_mapel VARCHAR(100) NOT NULL,
    deskripsi TEXT,
    durasi_menit INT DEFAULT 60,
    tgl_mulai DATETIME,
    tgl_selesai DATETIME,
    passing_grade INT DEFAULT 70,
    is_active BOOLEAN DEFAULT TRUE
);

-- tabel siswa
CREATE TABLE siswa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nis VARCHAR(20) UNIQUE NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    kelas VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- tabel soal (pilihan ganda)
CREATE TABLE soal (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mapel_id INT NOT NULL,
    teks_soal TEXT NOT NULL,
    pilihan_a TEXT NOT NULL,
    pilihan_b TEXT NOT NULL,
    pilihan_c TEXT NOT NULL,
    pilihan_d TEXT NOT NULL,
    jawaban_benar CHAR(1) NOT NULL CHECK (jawaban_benar IN ('A','B','C','D')),
    poin INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (mapel_id) REFERENCES mata_pelajaran(id) ON DELETE CASCADE
);

-- tabel ujian_siswa (menyimpan status dan nilai)
CREATE TABLE ujian_siswa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    siswa_id INT NOT NULL,
    mapel_id INT NOT NULL,
    tanggal_mulai DATETIME DEFAULT CURRENT_TIMESTAMP,
    tanggal_selesai DATETIME,
    nilai_total INT,
    is_finished BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (siswa_id) REFERENCES siswa(id) ON DELETE CASCADE,
    FOREIGN KEY (mapel_id) REFERENCES mata_pelajaran(id),
    UNIQUE KEY unik_ujian (siswa_id, mapel_id) -- agar satu siswa hanya sekali ujian per mapel
);

-- tabel jawaban_siswa
CREATE TABLE jawaban_siswa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ujian_id INT NOT NULL,
    soal_id INT NOT NULL,
    jawaban_siswa CHAR(1) CHECK (jawaban_siswa IN ('A','B','C','D', NULL)),
    is_benar BOOLEAN,
    FOREIGN KEY (ujian_id) REFERENCES ujian_siswa(id) ON DELETE CASCADE,
    FOREIGN KEY (soal_id) REFERENCES soal(id)
);

-- tabel log aktivitas (opsional)
CREATE TABLE log_aktivitas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_type ENUM('siswa','admin'),
    user_id INT,
    aksi VARCHAR(255),
    ip_address VARCHAR(45),
    waktu TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default admin (username: admin, password: admin123) -> gunakan bcrypt nanti di PHP
INSERT INTO admin (username, password, nama_lengkap) VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Super Admin');
-- Note: password 'admin123' bcrypt hash di atas hanya contoh. Pastikan Anda buat hash baru.