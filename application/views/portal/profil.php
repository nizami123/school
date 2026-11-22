<?php if ($must): ?><div class="alert alert-warning">Ini login pertama Anda. Silakan ganti password awal sebelum melanjutkan.</div><?php endif; ?>
<div class="card mb-3"><div class="card-body"><div class="portal-card-title">Akun</div>
  <dl class="row small mb-0"><dt class="col-5">Nama</dt><dd class="col-7"><?= e($p->name) ?></dd><dt class="col-5">Username</dt><dd class="col-7"><code><?= e($u->username) ?></code></dd><dt class="col-5">WhatsApp absensi</dt><dd class="col-7"><?= e($p->whatsapp ?: '-') ?></dd>
  <dt class="col-5">Anak</dt><dd class="col-7"><?php foreach ($children as $c): ?><?= e($c->name) ?> (<?= e($c->class_name) ?>)<br><?php endforeach; ?></dd></dl>
  <div class="form-text mt-2">Untuk mengubah nomor WhatsApp atau data anak, hubungi admin sekolah.</div></div></div>
<div class="card"><div class="card-body"><div class="portal-card-title">Ganti password</div>
  <?= form_open('portal/profil_simpan') ?>
  <div class="mb-2"><label class="form-label small mb-0">Password lama</label><input type="password" name="old_password" class="form-control" autocomplete="current-password" required></div>
  <div class="mb-2"><label class="form-label small mb-0">Password baru (min. 8 karakter)</label><input type="password" name="new_password" class="form-control" autocomplete="new-password" minlength="8" required></div>
  <div class="mb-3"><label class="form-label small mb-0">Ulangi password baru</label><input type="password" name="new_password2" class="form-control" autocomplete="new-password" minlength="8" required></div>
  <button class="btn btn-primary w-100">Simpan Password</button><?= form_close() ?></div></div>
