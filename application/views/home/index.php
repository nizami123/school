<?php
$wa = 'https://wa.me/' . preg_replace('/\D/', '', $v['wa_number']) . '?text=' . rawurlencode($v['wa_text']);
$features = array(
    array('bi-qr-code-scan', 'Absensi QR per mata pelajaran', 'Setiap siswa punya QR acak. Guru membuka sesi dari jadwal, scan lewat kamera tanpa reload, dengan suara, validasi server, dan anti-ganda.'),
    array('bi-wifi-off', 'Tetap jalan saat sinyal lemah', 'Hasil scan masuk antrean lokal dan disinkronkan otomatis ketika internet kembali.'),
    array('bi-whatsapp', 'Notifikasi WhatsApp ke orang tua', 'Hadir, terlambat, tidak hadir, izin, dan sakit terkirim lewat Fonnte dengan antrean, retry, dan template pesan yang bisa diubah.'),
    array('bi-person-hearts', 'Portal orang tua', 'Satu akun memantau beberapa anak: kehadiran, rekap bulanan, jadwal, pengumuman, dan kalender akademik.'),
    array('bi-calendar3', 'Jadwal pintar', 'Cek bentrok guru, kelas, dan jam istirahat, hari efektif, tampilan mingguan, duplikasi, serta impor jadwal.'),
    array('bi-envelope-paper', 'Izin & sakit online', 'Orang tua mengajukan, admin menyetujui, dan absensi pada tanggal tersebut otomatis tercatat.'),
    array('bi-cash-coin', 'Iuran, SPP & infaq', 'Tagihan massal, bukti transfer, verifikasi admin, dan kwitansi. Pembayaran bersifat manual (belum payment gateway).'),
    array('bi-megaphone', 'Pengumuman & rapat wali murid', 'Pengumuman per kelas atau seluruh sekolah, sematkan, dan konfirmasi kehadiran orang tua.'),
    array('bi-trophy', 'Ekstrakurikuler', 'Informasi kegiatan, kuota, pendaftaran oleh orang tua, dan persetujuan pembina.'),
    array('bi-person-vcard', 'Kartu pelajar', 'Cetak kartu ukuran CR80 per siswa atau per kelas; QR lama otomatis tidak berlaku saat dibuat ulang.'),
    array('bi-file-earmark-bar-graph', 'Rekap & laporan', 'Dashboard dan grafik, laporan per siswa, kelas, mapel, guru, dan semester dalam bentuk cetak, PDF, Excel, atau CSV.'),
    array('bi-file-earmark-spreadsheet', 'Impor & ekspor data', 'Data siswa, guru, orang tua, dan jadwal dapat diimpor dari Excel/CSV dan diekspor kembali.'),
);
?><!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Velano School - Absensi QR & Administrasi Sekolah</title>
<meta name="description" content="Velano School: absensi QR per mata pelajaran, notifikasi WhatsApp, portal orang tua, iuran, dan laporan untuk banyak sekolah dalam satu platform.">
<meta name="theme-color" content="#14532d"><link rel="icon" href="<?= base_url('assets/icons/favicon.png') ?>"><link rel="manifest" href="<?= site_url('manifest.webmanifest') ?>">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<style>
:root{--g9:#14532d;--g7:#15803d;--g5:#22c55e;--ink:#0f1a14}
body{color:var(--ink);scroll-behavior:smooth}
html{scroll-behavior:smooth}
.navbar-brand img{height:34px}
.hero{background:radial-gradient(1000px 500px at 85% -10%,#bbf7d0 0,transparent 60%),linear-gradient(180deg,#f0fdf4,#fff);padding:4.5rem 0 3.5rem}
.hero h1{font-weight:800;letter-spacing:-.02em}
.hero h1 span{color:var(--g7)}
.btn-vel{background:var(--g7);border-color:var(--g7);color:#fff}.btn-vel:hover{background:var(--g9);border-color:var(--g9);color:#fff}
.mock{background:#fff;border:1px solid #e2e8f0;border-radius:1rem;box-shadow:0 20px 50px rgba(20,83,45,.15);overflow:hidden}
.mock .bar{background:var(--g9);color:#fff;padding:.6rem 1rem;font-size:.85rem}
.mock .row-i{display:flex;justify-content:space-between;align-items:center;padding:.6rem 1rem;border-bottom:1px solid #f1f5f9;font-size:.9rem}
.fcard{border:1px solid #e5efe8;border-radius:1rem;padding:1.25rem;height:100%;background:#fff;transition:.15s}
.fcard:hover{transform:translateY(-3px);box-shadow:0 10px 25px rgba(20,83,45,.1)}
.ficon{width:46px;height:46px;border-radius:.8rem;background:#dcfce7;color:var(--g9);display:flex;align-items:center;justify-content:center;font-size:1.4rem;margin-bottom:.8rem}
.band{background:var(--g9);color:#fff}
.step{width:42px;height:42px;border-radius:50%;background:var(--g5);color:#052e16;font-weight:800;display:flex;align-items:center;justify-content:center;flex:none}
footer{background:#0b2517;color:#a7c7b2}
</style></head><body>
<nav class="navbar navbar-expand-lg bg-white sticky-top border-bottom"><div class="container">
  <a class="navbar-brand" href="<?= site_url('/') ?>"><img src="<?= base_url('assets/img/velano-logo.png') ?>" alt="Velano School"></a>
  <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#nv"><span class="navbar-toggler-icon"></span></button>
  <div class="collapse navbar-collapse" id="nv"><ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
    <li class="nav-item"><a class="nav-link" href="#fitur">Fitur</a></li><li class="nav-item"><a class="nav-link" href="#cara">Cara kerja</a></li>
    <li class="nav-item"><a class="nav-link" href="#paket">Paket</a></li><li class="nav-item"><a class="nav-link" href="#faq">FAQ</a></li>
    <li class="nav-item"><a class="btn btn-vel ms-lg-2" href="<?= $logged ? site_url($is_super ? 'super' : 'dashboard') : site_url('login') ?>"><i class="bi bi-box-arrow-in-right"></i> <?= $logged ? 'Buka aplikasi' : 'Masuk' ?></a></li>
  </ul></div></div></nav>

<header class="hero"><div class="container"><div class="row align-items-center g-5">
  <div class="col-lg-6">
    <span class="badge rounded-pill text-bg-success mb-3">Satu platform untuk banyak sekolah</span>
    <h1 class="display-5">Absensi QR dan administrasi sekolah, <span>rapi dan terhubung</span> ke orang tua.</h1>
    <p class="lead text-secondary mt-3">Dari scan kehadiran per mata pelajaran, kabar otomatis lewat WhatsApp, sampai iuran dan laporan. Data tiap sekolah terpisah dan hanya bisa diakses admin sekolah tersebut.</p>
    <div class="d-flex flex-wrap gap-2 mt-4">
      <a href="<?= e($wa) ?>" class="btn btn-vel btn-lg" target="_blank" rel="noopener"><i class="bi bi-whatsapp"></i> Tanya paket &amp; demo</a>
      <a href="#fitur" class="btn btn-outline-success btn-lg">Lihat fitur</a>
    </div>
  </div>
  <div class="col-lg-6"><div class="mock">
    <div class="bar"><i class="bi bi-qr-code-scan"></i> Scan Absensi &middot; Matematika &middot; VII A</div>
    <div class="row-i"><span><strong>Ahmad Fauzi</strong><br><small class="text-muted">07.02</small></span><span class="badge text-bg-success">Hadir</span></div>
    <div class="row-i"><span><strong>Siti Aminah</strong><br><small class="text-muted">07.19</small></span><span class="badge text-bg-warning">Terlambat</span></div>
    <div class="row-i"><span><strong>Rizky Pratama</strong><br><small class="text-muted">belum scan</small></span><span class="badge text-bg-secondary">Belum</span></div>
    <div class="row-i bg-light"><span class="small text-muted"><i class="bi bi-whatsapp text-success"></i> Notifikasi orang tua terkirim otomatis</span><strong>2/3</strong></div>
  </div></div>
</div></div></header>

<section id="fitur" class="py-5"><div class="container">
  <div class="text-center mb-5"><h2 class="fw-bold">Fitur yang sudah tersedia</h2><p class="text-secondary">Semua fitur berikut sudah berjalan di aplikasi.</p></div>
  <div class="row g-3"><?php foreach ($features as $f): ?>
    <div class="col-md-6 col-lg-4"><div class="fcard"><div class="ficon"><i class="bi <?= $f[0] ?>"></i></div><h3 class="h6 fw-bold"><?= e($f[1]) ?></h3><p class="small text-secondary mb-0"><?= e($f[2]) ?></p></div></div>
  <?php endforeach; ?></div>
</div></section>

<section class="band py-5"><div class="container"><div class="row g-4 align-items-center">
  <div class="col-lg-5"><h2 class="fw-bold">Multi sekolah, data terpisah</h2><p class="text-white-50 mb-0">Setiap sekolah punya akun admin, guru, orang tua, dan data sendiri. Sekolah tidak dapat melihat data sekolah lain.</p></div>
  <div class="col-lg-7"><div class="row g-3 text-center">
    <div class="col-4"><div class="fs-2"><i class="bi bi-building-lock"></i></div><small>Data terisolasi per sekolah</small></div>
    <div class="col-4"><div class="fs-2"><i class="bi bi-people"></i></div><small>Langganan sesuai jumlah siswa</small></div>
    <div class="col-4"><div class="fs-2"><i class="bi bi-phone"></i></div><small>Bisa dipasang seperti aplikasi (PWA)</small></div>
  </div></div>
</div></div></section>

<section id="cara" class="py-5"><div class="container"><div class="text-center mb-5"><h2 class="fw-bold">Mulai dalam 4 langkah</h2></div>
  <div class="row g-4"><?php foreach (array('Sekolah mendaftar, Velano membuat akun admin sekolah.', 'Admin mengimpor data siswa, guru, orang tua, dan jadwal dari Excel.', 'Cetak kartu QR siswa, guru mulai sesi absensi dari ponsel.', 'Orang tua menerima kabar dan memantau lewat portal.') as $i => $t): ?>
    <div class="col-md-6 col-lg-3"><div class="d-flex gap-3"><div class="step"><?= $i + 1 ?></div><p class="mb-0"><?= e($t) ?></p></div></div>
  <?php endforeach; ?></div>
</div></section>

<section id="paket" class="py-5 bg-light"><div class="container"><div class="row justify-content-center"><div class="col-lg-8 text-center">
  <h2 class="fw-bold">Berlangganan sesuai jumlah siswa</h2>
  <p class="text-secondary">Biaya mengikuti jumlah siswa terdaftar di sekolah Anda, jadi sekolah kecil maupun besar membayar sewajarnya. Hubungi kami untuk penawaran dan demo.</p>
  <a href="<?= e($wa) ?>" class="btn btn-vel btn-lg" target="_blank" rel="noopener"><i class="bi bi-whatsapp"></i> Hubungi via WhatsApp</a>
  <a href="mailto:<?= e($v['email']) ?>" class="btn btn-outline-success btn-lg"><i class="bi bi-envelope"></i> <?= e($v['email']) ?></a>
</div></div></div></section>

<section id="faq" class="py-5"><div class="container" style="max-width:820px"><h2 class="fw-bold text-center mb-4">Pertanyaan umum</h2>
<div class="accordion" id="acc">
<?php foreach (array(
  array('Apakah data sekolah kami aman dari sekolah lain?', 'Ya. Setiap data tersimpan dengan penanda sekolah dan seluruh halaman hanya menampilkan data sekolah akun yang login.'),
  array('Apakah WhatsApp gratis?', 'Notifikasi memakai layanan Fonnte milik sekolah (token Anda sendiri). Biaya layanan WhatsApp mengikuti paket Fonnte.'),
  array('Apakah bisa bayar SPP online lewat bank atau e-wallet?', 'Saat ini pembayaran bersifat manual: orang tua mengunggah bukti transfer lalu admin memverifikasi. Integrasi payment gateway belum tersedia.'),
  array('Perlu perangkat khusus?', 'Tidak. Cukup ponsel atau laptop dengan kamera dan koneksi internet. Aplikasi dapat dipasang di layar utama.'),
  array('Bagaimana memindahkan data siswa yang sudah ada?', 'Gunakan fitur impor Excel/CSV untuk siswa, guru, orang tua, dan jadwal; template tersedia di aplikasi.'),
) as $i => $q): ?>
<div class="accordion-item"><h3 class="accordion-header"><button class="accordion-button <?= $i ? 'collapsed' : '' ?>" data-bs-toggle="collapse" data-bs-target="#q<?= $i ?>"><?= e($q[0]) ?></button></h3>
<div id="q<?= $i ?>" class="accordion-collapse collapse <?= $i ? '' : 'show' ?>" data-bs-parent="#acc"><div class="accordion-body text-secondary"><?= e($q[1]) ?></div></div></div>
<?php endforeach; ?></div></div></section>

<footer class="py-4"><div class="container d-flex flex-wrap justify-content-between align-items-center gap-2">
  <span>&copy; <?= date('Y') ?> Velano School</span><a class="text-decoration-none text-white-50" href="<?= site_url('login') ?>">Masuk</a></div></footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script></body></html>
