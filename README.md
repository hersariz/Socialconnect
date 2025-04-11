# SocialConnect - Platform Media Sosial Modern

SocialConnect adalah aplikasi media sosial sederhana dengan UI/UX modern yang memungkinkan pengguna untuk berbagi momen, berinteraksi, dan terhubung dengan teman-teman.

## Fitur Utama

- Registrasi dan login pengguna
- Membuat, menyukai, dan mengomentari post
- Profil pengguna dengan foto, bio, dan statistik
- Mengikuti/berhenti mengikuti pengguna lain
- Feed beranda dengan postingan terbaru
- Notifikasi aktivitas
- Pesan langsung antar pengguna
- Desain responsif untuk berbagai perangkat

## Teknologi yang Digunakan

- **Frontend**: HTML, CSS, JavaScript, Bootstrap 5, Font Awesome
- **Backend**: PHP
- **Database**: MySQL
- **Library**: jQuery

## Instalasi

### Prasyarat
- PHP 7.4 atau lebih baru
- MySQL 5.7 atau lebih baru
- Server web (seperti Apache)
- XAMPP (direkomendasikan untuk pengembangan lokal)

### Langkah Instalasi

1. Clone repository ini ke direktori htdocs XAMPP Anda:
   ```
   ```
   atau download dan ekstrak file ZIP ke direktori `htdocs`.

2. Buat database MySQL baru bernama `social_media_db`:
   ```
   CREATE DATABASE social_media_db;
   ```

3. Import file `database.sql` untuk membuat struktur database dan data contoh:
   ```
   mysql -u root -p social_media_db < database.sql
   ```
   atau gunakan phpMyAdmin untuk import file SQL.

4. Konfigurasikan koneksi database di `includes/config.php` jika diperlukan:
   ```php
   $db_host = "localhost";
   $db_user = "root";
   $db_pass = ""; // Sesuaikan dengan password MySQL Anda
   $db_name = "social_media_db";
   ```

5. Buat folder `assets/uploads` jika belum ada dan pastikan memiliki izin tulis:
   ```
   mkdir -p assets/uploads
   chmod 777 assets/uploads
   ```

6. Akses aplikasi melalui browser:
   ```
   http://localhost/social_media
   ```

## Penggunaan

### Login
- Gunakan akun demo:
  - Username: johndoe
  - Password: password123

### Registrasi
- Klik tombol "Daftar" di halaman login atau beranda
- Isi formulir registrasi dengan username, email, dan password

### Membuat Post
- Setelah login, Anda akan melihat form "Buat Post Baru" di beranda
- Tulis konten post dan tambahkan gambar (opsional)
- Klik tombol "Posting"

### Interaksi dengan Post
- Klik ikon hati untuk menyukai post
- Klik ikon komentar untuk melihat dan menambahkan komentar
- Klik ikon bagikan untuk membagikan post

### Profil Pengguna
- Klik pada username atau foto pengguna untuk melihat profilnya
- Dari halaman profil, Anda dapat melihat post, media, suka, dan following pengguna
- Jika melihat profil Anda sendiri, Anda dapat mengedit profil

## Pengembangan Lebih Lanjut

Beberapa fitur yang bisa ditambahkan untuk pengembangan lebih lanjut:

- Pencarian pengguna dan konten
- Sistem hashtag dan trending topics
- Upload video
- Fitur story/status
- Grup dan komunitas
- Verifikasi email
- Autentikasi dua faktor
- API untuk aplikasi mobile

## Lisensi

Hak Cipta © 2025 SocialConnect. Semua hak dilindungi.

## Kontak

Untuk pertanyaan atau saran, silakan hubungi kami di rizkymvp123@gmail.com