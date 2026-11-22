# Panduan Portal Orang Tua & Fitur Baru

## Alur singkat untuk admin
1. **Data Orang Tua & Siswa**: pastikan tiap siswa tertaut ke orang tua (Data Siswa > Orang Tua/Wali). Siswa bersaudara cukup ditautkan ke orang tua yang sama → satu akun melihat semua anak.
2. **Akun Orang Tua** (menu Orang Tua & Keuangan): *Buat Akun Semua yang Belum Punya*. Username `ortu`+NIS anak pertama, password acak ditampilkan **sekali** (cetak/catat). Orang tua wajib ganti password saat login pertama. Tersedia Reset Password dan Nonaktifkan.
3. **Pengumuman / Kalender Akademik / Ekstrakurikuler**: menu Informasi Sekolah. Pengumuman dapat ditujukan ke satu kelas atau semua, disematkan, dan meminta konfirmasi kehadiran (rapat wali murid).
4. **Izin & Sakit Siswa**: tinjau pengajuan orang tua. Disetujui → absensi pada rentang tanggal otomatis Izin/Sakit (tidak menimpa siswa yang sudah scan). Sesi yang dibuka/ditutup setelahnya juga mengikuti izin yang disetujui.
5. **Iuran, SPP & Infaq**: buat *Jenis Iuran* → *Buat Tagihan* (per bulan/semester, semua siswa atau satu kelas; tidak dobel) → orang tua transfer & unggah bukti → *Verifikasi* → kwitansi cetak. Pembayaran tunai dicatat di Detail Tagihan. Isi rekening di Pengaturan > Pembayaran. Jenis "Bebas" dipakai untuk infaq sukarela (orang tua menentukan nominal).
6. **Rekap Bulanan**: menu Kehadiran > Rekap Bulanan (otomatis, bisa CSV/Excel/cetak). Orang tua melihat rekap anaknya di portal.

## Aturan penting
- WhatsApp **hanya** untuk absensi (hadir/terlambat/belum/izin-sakit yang dikonfirmasi guru, rekap harian bila diaktifkan). Izin, iuran, pengumuman, dan portal tidak mengirim WhatsApp.
- Lampiran izin & bukti transfer disimpan di `uploads/private/` (tidak bisa diakses langsung lewat URL; hanya lewat halaman dengan pengecekan hak akses).
- Data dummy: `database/dummy_data.sql` (impor sekali ke database baru) dan `database/hapus_data_dummy.sql`. Login dummy: guru `dummy.andi` dst / `guru12345`; ortu `ortudummy01`–`ortudummy10` / `ortu12345`. Generator: `php database/tools/generate_dummy.php > database/dummy_data.sql`.
- Instalasi lama: jalankan `database/migrasi_fitur_baru.sql` sekali.
