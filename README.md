# 🎓 Website Ujian Sekolah (Pilihan Ganda)

Sistem ujian online untuk sekolah dengan fitur lengkap: login siswa & admin, pilih mata pelajaran, ujian pilihan ganda, nilai langsung keluar, statistik nilai, upload soal via CSV, manajemen akun, dan pengaturan jadwal ujian.

## ✨ Fitur Utama

### Untuk Siswa
- Login dengan NIS dan password
- Dashboard daftar mata pelajaran yang tersedia (sesuai jadwal)
- Ujian pilihan ganda dengan timer
- Hasil ujian langsung ditampilkan (nilai + status lulus/tidak)
- Setiap siswa hanya bisa mengerjakan satu kali per mata pelajaran

### Untuk Admin
- Dashboard statistik (jumlah siswa, mapel aktif, ujian selesai, rata-rata nilai)
- Manajemen mata pelajaran & jadwal (tanggal mulai, selesai, durasi, passing grade)
- Manajemen soal (tambah, edit, hapus, import via CSV, download template)
- Manajemen akun siswa (tambah, edit, hapus, import CSV, reset password)
- Laporan nilai semua siswa dengan filter mapel & kelas
- Ekspor laporan ke Excel (CSV)
- Grafik peserta ujian per mapel

### Keamanan Dasar
- Password di-hash dengan bcrypt
- Session login
- Pencegahan akses langsung ke halaman tanpa login
- Random urutan soal saat ujian

## 🛠️ Teknologi

- Backend: PHP (native, OOP style)
- Database: MySQL
- Frontend: Bootstrap 5, jQuery, DataTables, Chart.js
- Server: Apache (XAMPP/Laragon) atau hosting PHP+MySQL

## 📦 Instalasi (Localhost)

### Prasyarat
- XAMPP / Laragon / MAMP
- Git (opsional, untuk clone)

### Langkah-langkah

1. **Clone repository ini** (atau download ZIP)
   ```bash
   git clone https://github.com/grag4z/ujian.git
