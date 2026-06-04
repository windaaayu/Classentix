# Classentix

Classentix adalah website manajemen kelas berbasis PHP dan MySQL. Website ini dibuat untuk membantu pengelolaan data kelas, murid, guru, mata pelajaran, dan jadwal pelajaran.

Sistem ini memiliki beberapa role, yaitu Admin, Guru, dan Pengunjung. Admin dapat mengelola data utama pada sistem. Guru dapat melihat jadwal mengajar dan daftar murid sesuai kelas atau jadwal yang diajar. Pengunjung dapat melihat jadwal pelajaran melalui landing page tanpa harus login.

## Fitur

### Admin

- Login ke dashboard Admin
- Mengelola data kelas
- Mengelola data murid
- Mengelola data guru
- Mengelola data mata pelajaran
- Mengelola jadwal pelajaran
- Mencetak laporan jadwal

### Guru

- Login ke dashboard Guru
- Melihat jadwal mengajar
- Melihat daftar murid sesuai kelas atau jadwal mengajar
- Melihat dan mengubah profil

### Pengunjung

- Mengakses landing page
- Memilih kelas aktif
- Melihat jadwal pelajaran sesuai kelas yang dipilih

## Teknologi yang Digunakan

- PHP
- MySQL
- HTML
- CSS
- JavaScript
- XAMPP

## Cara Menjalankan Project

1. Download atau clone repository ini.
2. Pindahkan folder project ke dalam folder `htdocs` XAMPP.
   Contoh:

   ```text
   C:\xampp\htdocs\classentix
3. Jalankan Apache dan MySQL melalui XAMPP Control Panel.
4. Buka phpMyAdmin melalui browser:
    ```text
    http://localhost/phpmyadmin
6. Buat database baru dengan nama:
   classentix_db
7. Import file database yang ada di folder database.
8. Buka website melalui browser:
     ```text
    http://localhost/classentix/
