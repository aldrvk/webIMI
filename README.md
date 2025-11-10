# Website Ikatan Motor Indonesia Sumatera Utara

## Deskripsi Aplikasi
Aplikasi ini adalah sistem manajemen basis data berbasis web yang dirancang untuk mengatasi tantangan administrasi di Ikatan Motor Indonesia (IMI) Sumatera Utara. Fokus utamanya adalah mendigitalisasi proses pendaftaran dan pengelolaan Kartu Izin Start (KIS) serta menyediakan platform terpusat untuk informasi event balap.

Sistem ini bertujuan untuk menggantikan pencatatan manual yang rentan terhadap kesalahan, mempermudah pembalap (terutama yang berdomisili jauh dari kantor pusat di Medan) untuk mengurus KIS secara online, dan menyediakan data yang akurat secara real-time bagi pengurus IMI untuk meningkatkan efisiensi operasional dan pengambilan keputusan.

## Fitur-Fitur Setiap Peran di Web
### 1. Pembalap
- Melakukan registrasi akun baru dan login/logout.
- Mengelola profil pribadi.
- Mengajukan KIS baru dengan mengunggah dokumen (seperti bukti pembayaran).
- Memantau status pengajuan KIS (Pending, Approved, Rejected).
- Melihat alasan penolakan jika pengajuan ditolak.
- Melihat dan mengunduh KIS Digital jika disetujui.
- Melihat kalender event yang akan datang.
- Mendaftar pada sebuah event.
- Melihat riwayat partisipasi dan prestasi balap (CV Pembalap).
- Melihat Papan Peringkat (Leaderboard) provinsi per kategori.
- Menerima pengumuman dari Pengurus IMI.

### 2. Pengurus IMI
- Login ke dashboard admin.
- Melihat dan mengelola antrean pengajuan KIS.
- Menyetujui (approve) pengajuan KIS.
- Menolak (reject) pengajuan KIS dan wajib memasukkan alasan penolakan.
- Mengelola (Create, Edit, Nonaktifkan) data event di kalender.
- Menginput hasil lomba setelah event selesai (otomatis mengisi CV pembalap dan memperbarui leaderboard).
- Membuat dan mengirimkan pengumuman ke semua pembalap.
- Melihat daftar semua pembalap yang terdaftar di sistem.

### 3. Pimpinan IMI
- Login ke dashboard eksekutif.
- Melihat KPI (Key Performance Indicators) utama (total pembalap, KIS pending, jumlah event).
- Melihat dashboard analitik berupa grafik (pertumbuhan pembalap, sebaran wilayah, kategori terpopuler).
- Melihat papan peringkat (Leaderboard).
- Mendownload laporan agregat (rekapitulasi event dalam PDF/Excel).

### 4. Penyelenggara Event
- Login ke portal verifikasi.
- Mengakses fitur verifikasi KIS (via pencarian NIK/nama atau pemindai QR Code).
- Melihat hasil verifikasi yang jelas (foto, nama, status AKTIF / TIDAK AKTIF).
- Melihat daftar pembalap yang terdaftar khusus untuk event mereka.

## Tech Stack
Backend :
- Bahasa Pemrograman: PHP (versi 8.2)
- Framework: Laravel (versi 12.0)
- Autentikasi: Laravel Breeze (versi 2.3)
- Templating Engine: Blade

Frontend :
- CSS Framework: Tailwind CSS (versi 3.1.0)
- JavaScript Framework: Alpine.js (versi 3.4.2)
- Build Tool: Vite (versi 7.0.7)

Database :
- Sistem Database: MySQL
- ORM (Object-Relational Mapper): Laravel Eloquent
- Manajemen Skema: Laravel Migrations

Tambahan :
- Manajemen Dependency (PHP): Composer
- Manajemen Dependency (JS): NPM
- HTTP Client: Axios (versi 1.11.0)

## Nama Anggota
- Aldrik Noel Sianipar (241402049)
- Leondo Admiral Purba (241402053)
- Rafi Andara Nasution (241402095)
- Wira Hari Pratama (241402111)
- Yosial Marcel Korhesy Simanjuntak (241402114)

## Langkah Setup dan Run Projek
### 1. Clone Repository
```
git clone https://github.com/aldrvk/webIMI.git
cd webIMI
```

### 2. Instalasi Dependencies
```
composer install
npm install
```

### 3. Konfigurasi Environment (.env)
```
copy .env.example .env
```
Buka file .env dan atur koneksi database
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=db_imi_sumut  # Ganti dengan nama database yang sesuai
DB_USERNAME=root          
DB_PASSWORD=              
```

### 4. Setup Aplikasi dan Database
```
php artisan key:generate
php artisan migrate
```

### 5. Jalankan Proyek
```
php artisan serve
```
Di terminal lain, jalankan
```
npm run dev
```
Setelah itu, aplikasi dapat diakses di http://127.0.0.1:8000.
