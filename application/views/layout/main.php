<?php
$uri = uri_string();
$act = function ($p) use ($uri) { return ($p === 'dashboard' ? ($uri === '' || strpos($uri, 'dashboard') === 0) : strpos($uri, $p) === 0) ? 'active' : ''; };
$role = $this->session->userdata('role');
$flash = $this->session->flashdata('flash');
$school = setting('school_name', 'QR School Attendance');
$logo = setting('school_logo', '');
$admin_menu = array(
    array('dashboard', 'bi-speedometer2', 'Dashboard'), array('monitor', 'bi-broadcast', 'Monitoring Absensi'),
    array('--', '', 'Data Master'),
    array('master/students', 'bi-people', 'Data Siswa'), array('master/teachers', 'bi-person-workspace', 'Data Guru'), array('master/classes', 'bi-door-open', 'Data Kelas'),
    array('master/subjects', 'bi-book', 'Mata Pelajaran'), array('master/parents', 'bi-person-hearts', 'Orang Tua/Wali'),
    array('master/academic_years', 'bi-calendar-range', 'Tahun Ajaran'), array('master/semesters', 'bi-calendar2-week', 'Semester'),
    array('--', '', 'Jadwal & Kartu'),
    array('master/schedules', 'bi-calendar3', 'Jadwal Pelajaran'), array('qr', 'bi-qr-code', 'Generate QR Code'), array('qr/cards', 'bi-person-vcard', 'Cetak Kartu Pelajar'),
    array('--', '', 'Kehadiran'),
    array('rekap', 'bi-table', 'Rekap Kehadiran'), array('report', 'bi-file-earmark-bar-graph', 'Laporan'), array('scan/history', 'bi-clock-history', 'Riwayat Sesi'),
    array('--', '', 'Sistem'),
    array('wa', 'bi-whatsapp', 'Notifikasi WhatsApp'), array('master/users', 'bi-shield-lock', 'Akun Admin'), array('settings/audit', 'bi-journal-text', 'Audit Log'), array('settings', 'bi-gear', 'Pengaturan'),
);
$guru_menu = array(array('dashboard', 'bi-house', 'Beranda'), array('scan/history', 'bi-clock-history', 'Riwayat'), array('scan/notifications', 'bi-whatsapp', 'WhatsApp'), array('logout', 'bi-box-arrow-right', 'Keluar'));
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title><?= e($title) ?> - <?= e($school) ?></title>
<meta name="theme-color" content="#14532d">
<meta name="csrf-name" content="<?= $this->security->get_csrf_token_name() ?>">
<meta name="csrf-hash" content="<?= $this->security->get_csrf_hash() ?>">
<meta name="apple-mobile-web-app-capable" content="yes"><meta name="mobile-web-app-capable" content="yes">
<link rel="manifest" href="<?= base_url('manifest.webmanifest') ?>">
<link rel="apple-touch-icon" href="<?= base_url('assets/icons/icon-192.png') ?>">
<link rel="icon" href="<?= base_url('assets/icons/icon-192.png') ?>">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/datatables.net-bs5@1.13.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<link href="<?= base_url('assets/css/app.css') ?>" rel="stylesheet">
</head>
<body class="role-<?= e($role) ?>">
<?php if ($role === 'admin'): ?>
<div class="d-flex app-shell">
  <nav class="sidebar offcanvas-lg offcanvas-start" tabindex="-1" id="sidebar">
    <div class="sidebar-brand">
      <?php if ($logo): ?><img src="<?= upload_url('logo/' . $logo) ?>" alt="" class="brand-logo"><?php else: ?><i class="bi bi-qr-code-scan fs-3"></i><?php endif; ?>
      <div><strong>QR School</strong><br><small><?= e($school) ?></small></div>
    </div>
    <div class="sidebar-scroll">
    <?php foreach ($admin_menu as $m): if ($m[0] === '--'): ?><div class="menu-title"><?= e($m[2]) ?></div><?php else: ?>
      <a class="menu-link <?= $act($m[0]) ?>" href="<?= site_url($m[0]) ?>"><i class="bi <?= $m[1] ?>"></i><span><?= e($m[2]) ?></span></a>
    <?php endif; endforeach; ?>
    </div>
    <div class="sidebar-foot"><i class="bi bi-person-circle"></i> <?= e($this->session->userdata('name')) ?><a href="<?= site_url('logout') ?>" class="float-end text-white" title="Keluar"><i class="bi bi-box-arrow-right"></i></a></div>
  </nav>
  <main class="flex-grow-1 main-area">
    <header class="topbar d-lg-none"><button class="btn btn-sm btn-light" data-bs-toggle="offcanvas" data-bs-target="#sidebar"><i class="bi bi-list fs-5"></i></button><span class="fw-semibold"><?= e($title) ?></span><span></span></header>
    <div class="container-fluid p-3 p-lg-4">
      <h1 class="h4 mb-3 d-none d-lg-block"><?= e($title) ?></h1>
      <?= $content ?>
    </div>
  </main>
</div>
<?php else: ?>
<header class="topbar guru-top"><span class="fw-semibold"><i class="bi bi-qr-code-scan"></i> <?= e($school) ?></span><small><?= e($this->session->userdata('name')) ?></small></header>
<main class="container py-3 guru-main"><?= $content ?></main>
<nav class="bottom-nav">
  <?php foreach ($guru_menu as $m): ?><a href="<?= site_url($m[0]) ?>" class="<?= $act($m[0]) ?>"><i class="bi <?= $m[1] ?>"></i><span><?= e($m[2]) ?></span></a><?php endforeach; ?>
</nav>
<?php endif; ?>

<div class="toast-container position-fixed top-0 end-0 p-3" id="toasts" style="z-index:2000"></div>
<div class="modal fade" id="confirmModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content">
  <div class="modal-header"><h5 class="modal-title">Konfirmasi</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
  <div class="modal-body" id="confirmText">Yakin?</div>
  <div class="modal-footer"><button class="btn btn-light" data-bs-dismiss="modal">Batal</button><button class="btn btn-danger" id="confirmOk">Ya, lanjutkan</button></div>
</div></div></div>
<div id="loading" class="loading d-none"><div class="spinner-border text-success"></div></div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/datatables.net@1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/datatables.net-bs5@1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script>window.BASE = <?= json_encode(site_url('')) ?>;</script>
<script src="<?= base_url('assets/js/app.js') ?>"></script>
<?php if ($flash): ?><script>document.addEventListener('DOMContentLoaded',function(){toast(<?= json_encode($flash[1]) ?>,<?= json_encode($flash[0]) ?>,true);});</script><?php endif; ?>
<?php if (isset($scripts)) echo $scripts; ?>
</body>
</html>
