<?php $f = function ($k, $d = '') use ($row) { return e(isset($row[$k]) ? $row[$k] : $d); }; ?>
<div class="card"><div class="card-body"><?= form_open('super/sekolah_simpan') ?><input type="hidden" name="id" value="<?= (int)$id ?>">
<div class="row g-3">
<div class="col-12"><label class="form-label">Nama Sekolah *</label><input name="name" class="form-control" value="<?= $f('name') ?>" required></div>
<div class="col-md-6"><label class="form-label">Alamat</label><input name="address" class="form-control" value="<?= $f('address') ?>"></div>
<div class="col-md-3"><label class="form-label">Telepon</label><input name="phone" class="form-control" value="<?= $f('phone') ?>"></div>
<div class="col-md-3"><label class="form-label">Email</label><input name="email" type="email" class="form-control" value="<?= $f('email') ?>"></div>
<div class="col-md-4"><label class="form-label">Kuota siswa (0 = tanpa batas)</label><input name="student_quota" inputmode="numeric" class="form-control" value="<?= $f('student_quota', 0) ?>"></div>
<div class="col-md-4"><label class="form-label">Harga per siswa (Rp)</label><input name="price_per_student" inputmode="numeric" class="form-control" value="<?= $f('price_per_student', 0) ?>"></div>
<div class="col-12"><label class="form-label">Catatan internal</label><input name="notes" class="form-control" value="<?= $f('notes') ?>"></div>
<?php if (!$id): ?><div class="col-12"><hr><strong>Akun Admin Sekolah</strong></div>
<div class="col-md-4"><label class="form-label">Nama Admin *</label><input name="admin_name" class="form-control" value="<?= $f('admin_name') ?>" required></div>
<div class="col-md-4"><label class="form-label">Username *</label><input name="admin_username" class="form-control" value="<?= $f('admin_username') ?>" autocomplete="off" required></div>
<div class="col-md-4"><label class="form-label">Password * (min. 8)</label><input type="password" name="admin_password" class="form-control" autocomplete="new-password" required></div><?php endif; ?>
</div><div class="mt-3"><button class="btn btn-primary">Simpan</button> <a class="btn btn-light" href="<?= site_url('super') ?>">Batal</a></div><?= form_close() ?></div></div>
