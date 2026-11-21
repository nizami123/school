# Panduan Instalasi di cPanel — QR School Attendance

Target: PHP 7.3+ (juga jalan di 7.4/8.0 dengan CodeIgniter 3.1.13), MySQL 5.7+/MariaDB 10.3+, HTTPS aktif.

## 1. Siapkan file
1. Unduh **CodeIgniter 3.1.13** dari GitHub (bcit-ci/CodeIgniter), salin folder **`system/`** ke folder proyek ini (di sebelah `index.php`).
   Aplikasi ini sudah menyertakan seluruh isi `application/`, jadi **jangan** menimpa `application/`.
2. (Opsional, untuk PDF langsung & Excel .xlsx) jalankan `composer install` di folder proyek (lewat Terminal cPanel / SSH),
   atau unggah folder `vendor/` hasil composer dari komputer Anda. Tanpa ini, laporan tetap bisa dicetak / disimpan PDF lewat
   dialog cetak browser dan diekspor sebagai CSV yang terbuka di Excel.

## 2. Unggah
- Unggah semua isi proyek ke `public_html/` (atau subfolder/subdomain, mis. `absensi.sekolahanda.sch.id`).
- Pastikan file `.htaccess` ikut terunggah (aktifkan "Show Hidden Files" di File Manager).

## 3. Database
- cPanel → **MySQL Databases**: buat database + user, beri **ALL PRIVILEGES**. Catat nama database, user, password.
  (Tidak perlu impor manual: installer mengimpor `database/schema.sql`. Anda juga boleh mengimpornya sendiri lewat phpMyAdmin.)

## 4. Izin folder (chmod 755; bila gagal tulis gunakan 775)
`application/config`, `application/cache/sessions`, `application/logs`, `uploads/students`, `uploads/logo`.

## 5. Pilih versi PHP
cPanel → **MultiPHP Manager** → PHP 7.3 atau lebih baru. Di **MultiPHP INI Editor** pastikan ekstensi aktif: `mysqli`, `curl`, `openssl`, `mbstring`, `gd` (opsional), `zip` (untuk xlsx).

## 6. Aktifkan HTTPS (WAJIB)
cPanel → **SSL/TLS Status** → jalankan AutoSSL. Kamera browser (scanner) hanya berfungsi di HTTPS. Setelah HTTPS aktif, tambahkan redirect HTTP→HTTPS bila perlu.

## 7. Jalankan installer
Buka `https://domain-anda/install`, isi data database, nama sekolah, dan akun admin awal (password ≥ 8 karakter).
Installer: mengimpor skema, membuat admin, tahun ajaran & semester awal, mengisi template pesan, menulis `application/config/database_local.php`
dan `secret.php` (kunci enkripsi acak), lalu membuat `installed.lock` sehingga installer terkunci.

## 8. Cron (antrean WhatsApp & rekap)
cPanel → **Cron Jobs**:
```
* * * * * /usr/local/bin/php /home/USER/public_html/index.php cron wa
0 16 * * 1-6 /usr/local/bin/php /home/USER/public_html/index.php cron daily
```
(Sesuaikan path PHP & folder.) Alternatif tanpa CLI: panggil URL `https://domain/cron/wa/KUNCI` (KUNCI ada di Pengaturan → WhatsApp).
Cron tidak wajib agar scan berjalan; scanner juga memicu pengiriman antrean di latar belakang.

## 9. Alur awal pemakaian
1. Pengaturan: nama/logo sekolah, hari efektif, jam istirahat, toleransi terlambat.
2. Data Kelas → Mata Pelajaran → Orang Tua → Siswa (atau Impor CSV) → Guru (membuat akun login guru).
3. Jadwal Pelajaran (sistem menolak jadwal bentrok guru/kelas/istirahat).
4. Cetak Kartu Pelajar (menu Generate QR / Cetak Kartu).
5. Guru login di HP → "Mulai Absensi" → scan.

## 10. Backup
cPanel → **Backup** / phpMyAdmin → Export database secara berkala (mis. mingguan) + salin folder `uploads/`. Jadwalkan juga lewat cron `mysqldump` bila hosting mengizinkan.

## 11. Keamanan produksi
- Jangan hapus `installed.lock`. Pastikan `index.php` memakai `ENVIRONMENT` production (bawaan).
- `application/` dan `database/` dilindungi `.htaccess` (Require all denied). Di server non-Apache (Nginx) blokir akses folder tersebut di konfigurasi server.
- Ganti password admin berkala; akun terkunci 5 menit setelah 5x salah password.
