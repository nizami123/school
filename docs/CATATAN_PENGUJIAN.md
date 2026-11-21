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
