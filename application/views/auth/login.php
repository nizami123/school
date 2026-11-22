<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Masuk - Velano School</title><meta name="theme-color" content="#14532d">
<link rel="manifest" href="<?= site_url('manifest.webmanifest') ?>"><link rel="apple-touch-icon" href="<?= base_url('assets/icons/icon-192.png') ?>"><link rel="icon" href="<?= base_url('assets/icons/favicon.png') ?>">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="<?= base_url('assets/css/app.css') ?>" rel="stylesheet"></head>
<body><div class="login-wrap"><div class="card login-card"><div class="card-body p-4">
  <div class="text-center mb-4">
    <img src="<?= base_url('assets/img/velano-logo.png') ?>" alt="Velano School" style="max-width:220px;width:100%;height:auto">
    <div class="text-muted small mt-2">Masuk ke akun Anda</div>
  </div>
  <?php if (!empty($installed)): ?><div class="alert alert-success py-2">Instalasi berhasil. Silakan login dengan akun Super Admin yang baru dibuat.</div><?php endif; ?>
  <?php if ($error): ?><div class="alert alert-danger py-2"><?= e($error) ?></div><?php endif; ?>
  <?= form_open('login') ?>
    <div class="mb-3"><label class="form-label">Username</label><input name="username" class="form-control form-control-lg" autocomplete="username" autofocus required></div>
    <div class="mb-3"><label class="form-label">Password</label><input type="password" name="password" class="form-control form-control-lg" autocomplete="current-password" required></div>
    <button class="btn btn-primary btn-lg w-100"><i class="bi bi-box-arrow-in-right"></i> Masuk</button>
  <?= form_close() ?>
  <div class="text-center mt-3"><a href="<?= site_url('/') ?>" class="small text-muted text-decoration-none"><i class="bi bi-arrow-left"></i> Kembali ke beranda</a></div>
</div></div></div></body></html>
