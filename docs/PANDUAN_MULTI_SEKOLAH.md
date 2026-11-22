# Panduan Multi Sekolah (Velano School)

## Peran
| Peran | Tabel | Akses |
|---|---|---|
| Super Admin | `super_admins` | Panel `/super`: tambah/ubah sekolah, buat akun admin sekolah, kuota & harga per siswa, aktif/nonaktifkan sekolah, lihat jumlah siswa/guru/kelas/orang tua. **Tidak** melihat isi data sekolah. |
| Admin Sekolah, Guru, Orang Tua | `users` (+ `school_id`) | Hanya data sekolahnya sendiri. |

Satu form login untuk semua. Username unik secara global (pesan "sudah digunakan" hanya muncul saat membuat akun).

## Instalasi baru
Buka `/install`: isi database + akun Super Admin. Lalu login, tambah sekolah dari `/super`.

## Migrasi dari versi 1 sekolah (database sudah berisi data)
1. **Backup database.**
2. Jalankan `database/migrate_multi_sekolah.sql` SEKALI (phpMyAdmin > Import). Semua data lama menjadi milik sekolah #1; nama/alamat/logo diambil dari pengaturan lama.
3. Salin kode baru ke server (timpa file lama). Hapus `manifest.webmanifest` lama bila masih ada.
4. Buka `https://DOMAIN/setup-super`, masukkan password database lalu buat Super Admin pertama. Halaman ini terkunci setelah ada Super Admin.
5. Setel cron di panel Super Admin (satu cron untuk semua sekolah).

## Keamanan antar sekolah
- Semua tabel utama punya `school_id`; setiap query memfilter sekolah dari sesi login.
- ID dari form/URL (kelas, guru, orang tua, tagihan, bukti bayar, dsb.) diperiksa kepemilikannya.
- Tiap request memeriksa akun & sekolah masih aktif: menonaktifkan sekolah langsung mengeluarkan semua penggunanya.
- QR milik sekolah lain dianggap tidak terdaftar saat discan.
- Modul data dummy (`Seed`) dihapus karena tidak aman untuk multi sekolah.

## Kuota langganan
Kuota (0 = tanpa batas) membatasi jumlah siswa terdaftar (belum dihapus). Berlaku di form tambah siswa dan impor. Estimasi tagihan = siswa terdaftar x harga per siswa.

## Impor / ekspor
Data Siswa, Guru, Orang Tua, dan Jadwal: tombol Ekspor (Excel/CSV), Template, dan Impor. Hasil ekspor bisa diimpor kembali.
- Guru: `nama, nip, username, password, no_hp, email, mapel, status` (mapel = kode atau nama, pisahkan dengan `;`). Password wajib untuk guru baru; kosong pada guru lama = tidak diubah.
- Orang tua: `nama, ayah, ibu, wali, hubungan, whatsapp` (dicocokkan dengan nomor WhatsApp).
- Siswa: `nis, nisn, nama, jk, kelas, nama_ortu, wa_ortu`.

## Kontak halaman promosi
Ubah nomor WhatsApp & email di `application/config/velano.php`.
