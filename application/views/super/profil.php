<div class="card" style="max-width:480px"><div class="card-body"><?= form_open('super/profil_simpan') ?>
<div class="mb-2"><label class="form-label">Password lama</label><input type="password" name="old_password" class="form-control" required></div>
<div class="mb-2"><label class="form-label">Password baru (min. 8)</label><input type="password" name="new_password" class="form-control" autocomplete="new-password" required></div>
<div class="mb-3"><label class="form-label">Ulangi password baru</label><input type="password" name="new_password2" class="form-control" autocomplete="new-password" required></div>
<button class="btn btn-primary">Ganti Password</button><?= form_close() ?></div></div>
