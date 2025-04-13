# SocialConnect

Aplikasi media sosial sederhana yang dibangun dengan PHP dan MySQL dengan fitur-fitur lengkap.

## Fitur

- **Pengelolaan Akun**
  - Registrasi dan login pengguna
  - Profil pengguna dengan foto profil, bio, dan info dasar
  - Update profil dan pengaturan akun

- **Postingan dan Interaksi**
  - Membuat postingan teks dan gambar
  - Like dan komentar pada postingan
  - Fitur hashtag yang dapat diklik (#hashtag)
  - Berbagi postingan ke timeline pengguna
  - Berbagi ke platform eksternal (WhatsApp, Facebook, Twitter)

- **Komunikasi**
  - Sistem pesan antar pengguna
  - Notifikasi untuk interaksi (like, komentar, follow)
  - Follow/unfollow pengguna lain

- **Pencarian dan Eksplorasi**
  - Pencarian pengguna, postingan, dan hashtag
  - Halaman eksplorasi untuk konten trending
  - Highlight hasil pencarian

## Teknologi

- **Backend**: PHP 8.2
- **Database**: MySQL
- **Frontend**: HTML5, CSS3, JavaScript, Bootstrap 5
- **Library**: jQuery, Font Awesome

## Struktur Database

Database terdiri dari tabel-tabel berikut:
- `users` - Data pengguna
- `posts` - Postingan dari pengguna
- `comments` - Komentar pada postingan
- `likes` - Like pada postingan
- `followers` - Relasi follower/following
- `notifications` - Notifikasi pengguna
- `messages` - Pesan antar pengguna
- `activities` - Aktivitas pengguna
- `trending_topics` - Hashtag trending

## Instalasi

1. **Persiapan server:**
   - Install XAMPP atau server PHP/MySQL lainnya
   - PHP versi 8.0+ direkomendasikan

2. **Setup database:**
   - Buat database baru bernama `social_media`
   - Import file `database.sql` ke database tersebut

3. **Konfigurasi:**
   - Copy file `includes/config.sample.php` ke `includes/config.php`
   - Edit `config.php` dengan kredensial database Anda

4. **Upload kode:**
   - Upload semua file ke direktori htdocs (XAMPP) atau root direktori web Anda
   - Pastikan web server memiliki izin menulis ke folder `assets/uploads/`

5. **Akses aplikasi:**
   - Buka browser dan akses `http://localhost/social_media/`

## Akun Demo

- **Username**: admin
- **Password**: admin123

- **Username**: user1
- **Password**: user123

## Pengembangan Selanjutnya

- Implementasi API untuk aplikasi mobile
- Fitur story seperti Instagram
- Sistem polling dan survey
- Integrasi dengan Google Analytics
- Mode gelap/terang (dark/light mode)

## Lisensi

Proyek ini dilisensikan di bawah [MIT License](LICENSE).