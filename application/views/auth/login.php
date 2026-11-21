<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Login - <?= e(setting('school_name', 'QR School Attendance')) ?></title><meta name="theme-color" content="#14532d">
<link rel="manifest" href="<?= base_url('manifest.webmanifest') ?>"><link rel="apple-touch-icon" href="<?= base_url('assets/icons/icon-192.png') ?>">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="<?= base_url('assets/css/app.css') ?>" rel="stylesheet"></head>
<body><div class="login-wrap"><div class="card login-card"><div class="card-body p-4">
  <div class="text-center mb-3">
    <?php $logo = setting('school_logo', ''); if ($logo): ?><img src="<?= upload_url('logo/' . $logo) ?>" style="height:64px" alt=""><?php else: ?><i class="bi bi-qr-code-scan" style="font-size:3rem;color:#166534"></i><?php endif; ?>
    <h1 class="h4 mt-2 mb-0">QR School Attendance</h1><small class="text-muted"><?= e(setting('school_name', '')) ?></small>
  </div>
  <?php if (!empty($installed)): ?><div class="alert alert-success py-2">Instalasi berhasil. Silakan login dengan akun admin yang baru dibuat.</div><?php endif; ?>
  <?php if ($error): ?><div class="alert alert-danger py-2"><?= e($error) ?></div><?php endif; ?>
  <?= form_open('login') ?>
    <div class="mb-3"><label class="form-label">Username</label><input name="username" class="form-control form-control-lg" autocomplete="username" autofocus required></div>
    <div class="mb-3"><label class="form-label">Password</label><input type="password" name="password" class="form-control form-control-lg" autocomplete="current-password" required></div>
    <button class="btn btn-primary btn-lg w-100"><i class="bi bi-box-arrow-in-right"></i> Masuk</button>
  <?= form_close() ?>
</div></div></div></body></html>
