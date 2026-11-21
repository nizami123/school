# QR School Attendance

Absensi siswa per mata pelajaran berbasis QR Code + notifikasi WhatsApp. CodeIgniter 3 · MySQL · Bootstrap 5 · PWA.

**Mulai di sini:** `docs/PANDUAN_INSTALASI_CPANEL.md` (instalasi), `docs/PANDUAN_WHATSAPP.md`, `docs/STRUKTUR_DATABASE.md`, `docs/CATATAN_PENGUJIAN.md`.

Penting: folder `system/` CodeIgniter 3.1.13 **tidak disertakan** (harus disalin sendiri, lihat panduan langkah 1).
Pustaka front-end (Bootstrap, jQuery, DataTables, Chart.js, html5-qrcode, qrcodejs, html2canvas) dimuat dari CDN (jsdelivr/cdnjs) sehingga perangkat perlu internet.

## Fitur yang ada
Login & RBAC (admin/guru) · CRUD siswa/guru/kelas/mapel/orang tua/tahun ajaran/semester/jadwal (cek bentrok guru, kelas, istirahat, hari efektif; duplikasi; tampilan mingguan; impor CSV) ·
QR acak per siswa + regenerasi (QR lama otomatis tidak berlaku) · kartu pelajar CR80 cetak/PNG · sesi absensi dari jadwal · scanner kamera AJAX tanpa reload, suara, validasi server, anti-ganda, antrean offline lokal ·
konfirmasi sakit/izin/alpa · koreksi dengan alasan · antrean WhatsApp via Fonnte (retry, kirim ulang, dedupe) · dashboard & grafik data asli · rekap/laporan (cetak, PDF via Dompdf, XLSX via PhpSpreadsheet, CSV) · pengaturan, audit log, installer aman, PWA.

## Belum ada / catatan jujur
- Portal orang tua: **belum dibangun** (basis data `parents`/`student_parents` sudah siap).
- Hak khusus guru menggantikan jadwal guru lain: belum ada; admin dapat membuka sesi mana pun.
- Integrasi WhatsApp (Fonnte) belum diuji terhadap akun nyata (butuh token Anda); gunakan Pesan Uji di Pengaturan.
