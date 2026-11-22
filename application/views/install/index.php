<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Instalasi Velano School</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="<?= base_url('assets/css/app.css') ?>" rel="stylesheet"></head>
<body><div class="container py-4" style="max-width:720px">
<h1 class="h3 mb-1">Instalasi Velano School</h1><p class="text-muted">Isi data database kosong dan akun Super Admin awal (sekolah dan admin sekolah dibuat kemudian dari panel Super Admin). Installer akan mengimpor skema dan terkunci setelah selesai.</p>
<?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
<?php if (!empty($checks)): ?><div class="card mb-3"><div class="card-body"><strong>Pemeriksaan server</strong><ul class="mb-0 mt-2">
<?php foreach ($checks as $k => $ok): ?><li><?= $ok ? '✅' : '❌' ?> <?= e($k) ?></li><?php endforeach; ?></ul></div></div><?php endif; ?>
<?php $o = isset($old) ? $old : array(); $v = function ($k, $d = '') use ($o) { return e(isset($o[$k]) ? $o[$k] : $d); }; ?>
<?= form_open('install/run') ?>
<div class="card mb-3"><div class="card-body"><strong>Database MySQL</strong>
  <div class="row g-2 mt-1">
    <div class="col-md-6"><label class="form-label">Host *</label><input name="db_host" class="form-control" value="<?= $v('db_host', 'localhost') ?>" required></div>
    <div class="col-md-6"><label class="form-label">Nama Database *</label><input name="db_name" class="form-control" value="<?= $v('db_name') ?>" required></div>
    <div class="col-md-6"><label class="form-label">User Database *</label><input name="db_user" class="form-control" value="<?= $v('db_user') ?>" required></div>
    <div class="col-md-6"><label class="form-label">Password Database</label><input type="password" name="db_pass" class="form-control" autocomplete="off"></div>
  </div></div></div>
<div class="card mb-3"><div class="card-body"><strong>Akun Super Admin</strong>
  <div class="row g-2 mt-1">
    <div class="col-md-6"><label class="form-label">Nama Super Admin *</label><input name="admin_name" class="form-control" value="<?= $v('admin_name') ?>" required></div>
    <div class="col-md-6"><label class="form-label">Username *</label><input name="admin_user" class="form-control" value="<?= $v('admin_user', 'admin') ?>" required></div>
    <div class="col-md-6"><label class="form-label">Password * (min. 8)</label><input type="password" name="admin_pass" class="form-control" autocomplete="new-password" required></div>
    <div class="col-md-6"><label class="form-label">Ulangi Password *</label><input type="password" name="admin_pass2" class="form-control" autocomplete="new-password" required></div>
  </div></div></div>
<button class="btn btn-primary btn-lg w-100">Pasang Aplikasi</button>
<?= form_close() ?></div></body></html>
