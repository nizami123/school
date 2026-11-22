# Catatan Pengujian

Kode ditulis tanpa server PHP/MySQL di lingkungan pembuatnya, sehingga **belum dijalankan end-to-end**. Lakukan uji berikut setelah instalasi (±30 menit):

1. Installer berhasil → login admin.
2. Tambah kelas, mapel, orang tua (nomor WA Anda), siswa (QR otomatis dibuat), guru (akun login).
3. Tambah jadwal; coba jadwal bentrok guru/kelas → harus ditolak.
4. Cetak kartu (`Cetak Kartu Pelajar`) → QR tampil.
5. Login guru di HP (HTTPS) → Mulai Absensi → scan kartu: hijau + bunyi; scan ulang: "SISWA SUDAH TERCATAT HADIR"; scan siswa kelas lain: ditolak; QR acak: "TIDAK VALID".
6. Matikan internet saat scan → antrean offline muncul; nyalakan → tersinkron.
7. Akhiri sesi → konfirmasi sakit/izin/alpa → Simpan & kirim notifikasi.
8. Admin: Regenerasi QR → QR lama ditolak saat scan.
9. Rekap, laporan (Cetak, CSV), dashboard terisi data nyata.
10. WhatsApp: isi kredensial → Kirim Pesan Uji → jalankan satu sesi → cek status di menu Notifikasi WhatsApp.

Jika muncul halaman kosong/500: set `CI_ENV=development` sementara (atau lihat `application/logs/`) untuk melihat pesan kesalahan.

## Uji tambahan: portal orang tua & fitur baru
Instalasi lama: jalankan `database/migrasi_fitur_baru.sql` sekali (phpMyAdmin). Instalasi baru sudah termasuk. Lalu impor `database/dummy_data.sql` bila ingin data uji.

1. Admin > Akun Orang Tua > Buat Akun → catat username/password → login di HP → diminta ganti password.
2. Portal: Beranda, Kehadiran (ganti bulan), Jadwal & Kalender, Pengumuman (rapat: konfirmasi hadir), Ekskul (daftar → admin terima).
3. Izin: ortu ajukan → Admin > Izin & Sakit Siswa > Setujui → absensi tanggal tsb tercatat Izin/Sakit (cek Rekap Bulanan). Siswa yang sudah scan hadir tidak ditimpa.
4. Iuran: Admin > Iuran > Buat Tagihan (SPP bulan ini) → ortu unggah bukti → Admin Verifikasi → kwitansi. Coba nominal melebihi sisa (harus ditolak).
5. Keamanan: ortu A tidak boleh membuka `portal/lampiran/ID`, `portal/bukti/ID`, `portal/bayar_form/ID` milik ortu B (harus 404); ortu tidak bisa membuka `master/...`, `scan/...` (403/redirect).
6. WhatsApp: pastikan HANYA absensi yang masuk antrean (`Notifikasi WhatsApp`); izin/iuran/pengumuman tidak membuat antrean.
