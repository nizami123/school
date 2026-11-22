<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Buat Super Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><link href="<?= base_url('assets/css/app.css') ?>" rel="stylesheet"></head>
<body><div class="container py-4" style="max-width:520px">
<img src="<?= base_url('assets/img/velano-logo.png') ?>" alt="Velano School" style="max-width:200px" class="mb-3">
<h1 class="h4">Buat Super Admin pertama</h1><p class="text-muted small">Untuk keamanan, masukkan password database (yang ada di application/config/database.php atau database_local.php).</p>
<?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
<?= form_open('setup-super') ?>
<div class="mb-2"><label class="form-label">Password Database *</label><input type="password" name="db_pass" class="form-control" autocomplete="off" required></div>
<div class="mb-2"><label class="form-label">Nama *</label><input name="name" class="form-control" required></div>
<div class="mb-2"><label class="form-label">Username *</label><input name="username" class="form-control" required></div>
<div class="mb-2"><label class="form-label">Password * (min. 8)</label><input type="password" name="password" class="form-control" autocomplete="new-password" required></div>
<div class="mb-3"><label class="form-label">Ulangi Password *</label><input type="password" name="password2" class="form-control" autocomplete="new-password" required></div>
<button class="btn btn-primary w-100">Buat Super Admin</button><?= form_close() ?></div></body></html>
