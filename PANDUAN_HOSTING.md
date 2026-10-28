# Panduan Upload / Deploy ke Hosting (cPanel / DirectAdmin / VPS)

Proyek ini menggunakan **PHP (versi 8.0+)** dan **MySQL / MariaDB**.

---

## 1. File Database SQL
File export database lengkap (struktur tabel, setting, rundown, dan form pendaftaran) sudah dibuatkan di root proyek:
> **`letris_oddinary.sql`** (Path: `C:\xampp\htdocs\LetsOddinary\letris_oddinary.sql`)

---

## 2. Langkah-Langkah Deploy ke Hosting

### Langkah 1: Buat Database di cPanel
1. Masuk ke cPanel hosting Anda.
2. Buka menu **MySQL® Databases** (atau *MySQL Database Wizard*).
3. Buat database baru, contoh: `u1234567_letris`
4. Buat user database baru dan password yang kuat, contoh: `u1234567_admin` / `Password123!`
5. Hubungkan user ke database tersebut dan centang **ALL PRIVILEGES**.

### Langkah 2: Import File SQL
1. Di cPanel, buka **phpMyAdmin**.
2. Pilih nama database yang baru dibuat di panel sebelah kiri.
3. Klik tab **Import** di bagian atas.
4. Klik **Choose File**, pilih file `letris_oddinary.sql` dari komputer Anda.
5. Klik tombol **Import / Go** di bagian bawah.

### Langkah 3: Upload File Website
1. Di komputer Anda, zip seluruh isi folder `LetsOddinary` (kecuali folder `_old` jika tidak dibutuhkan).
2. Di cPanel, buka **File Manager** -> masuk ke folder `public_html` (atau subdomain Anda).
3. Upload file zip tersebut lalu **Extract**.

### Langkah 4: Sesuaikan File `config.php`
Buka dan edit file `config.php` di hosting Anda melalui File Manager:
```php
const DB_HOST = 'localhost'; // biasanya tetap localhost
const DB_NAME = 'u1234567_letris'; // nama database di hosting
const DB_USER = 'u1234567_admin'; // user database di hosting
const DB_PASS = 'Password123!'; // password database di hosting
```
*(Catatan: konstanta `BASE_URL` sudah diprogram otomatis mendeteksi apakah ditaruh di root domain `https://domainanda.com` atau di subfolder).*

### Langkah 5: Keamanan Sebelum Live
1. Hapus file `install.php` dan `letris_oddinary.sql` dari hosting jika database sudah selesai ter-import.
2. Login ke CMS di `https://domainanda.com/admin/login.php` (User default: `admin` | Password default: `Letris#2026!`).
3. Ganti password admin di menu **Pengaturan** CMS.
