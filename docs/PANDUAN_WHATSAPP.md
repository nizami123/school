# Panduan WhatsApp (Fonnte)

Notifikasi ke orang tua dikirim lewat **Fonnte** (`https://api.fonnte.com/send`).
Nomor tujuan = **nomor WhatsApp orang tua** yang tersimpan di Data Siswa / Data Orang Tua / hasil impor siswa (kolom `wa_ortu`).
Format nomor bebas (`0812...`, `62812...`, `+62812...`); otomatis dinormalisasi.

## A. Siapkan Fonnte
1. Daftar di https://fonnte.com, tambahkan **Device** (nomor WhatsApp pengirim) dan scan QR sampai status **Connected**.
2. Salin **Token** device tersebut.

## B. Isi di aplikasi
Admin → Pengaturan → tab **WhatsApp**:
- centang *Aktifkan notifikasi*, tempel **Token Fonnte** (disimpan terenkripsi, tidak ditulis di kode),
- *Jeda antar pesan* (detik, mis. `2` atau `2-5`) untuk mengurangi risiko nomor diblokir,
- pilih jenis notifikasi & kebijakan kirim, lalu **Simpan**.
- Masukkan nomor Anda di *Pesan Uji* → **Kirim Pesan Uji**.

Isi pesan diatur di tab **Template Pesan** (teks biasa, variabel `{nama} {kelas} {mapel} {guru} {hari_tanggal} {waktu} {sekolah} {daftar}`).

## C. Cara kerja
- Scan hanya menulis antrean; pengiriman di latar belakang (`scan/kick`) + cron:
  `* * * * * php /home/USER/public_html/index.php cron wa`
- Status: `menunggu` → `terkirim` / `gagal`. Gagal dicoba ulang sampai 3x, lalu bisa **Kirim Ulang** di menu Notifikasi WhatsApp.
- `terkirim` = permintaan diterima Fonnte. Alasan gagal (mis. device disconnected, token salah) tampil di kolom *Respons API*.
- Pesan ganda dicegah dengan kunci unik per (jenis, catatan kehadiran).
- Orang tua tanpa nomor valid ditandai `gagal` dengan keterangan, tanpa menghentikan proses lain.

## D. Catatan keamanan
Token adalah rahasia. Jika pernah terkirim lewat chat/dokumen, buat ulang token di dashboard Fonnte lalu isi ulang di Pengaturan.
