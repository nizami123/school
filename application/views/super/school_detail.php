<?php if (!empty($new)): ?><div class="alert alert-warning"><strong>Password baru untuk <?= e($new['username']) ?>:</strong> <code class="fs-5"><?= e($new['password']) ?></code><br><small>Hanya tampil sekali. Catat dan berikan ke admin sekolah.</small></div><?php endif; ?>
<div class="row g-3 mb-3">
<?php foreach (array(array('Siswa terdaftar', $s->students_total), array('Siswa aktif', $s->students_active), array('Guru', $s->teachers_total), array('Kelas', $s->classes_total), array('Orang tua', $s->parents_total)) as $c): ?>
<div class="col-6 col-md"><div class="card"><div class="card-body"><div class="text-muted small"><?= e($c[0]) ?></div><div class="fs-4 fw-bold"><?= (int)$c[1] ?></div></div></div></div><?php endforeach; ?></div>
<div class="row g-3"><div class="col-lg-5"><div class="card"><div class="card-body">
<h2 class="h6">Langganan <?= $s->is_active ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-secondary">Nonaktif</span>' ?></h2>
<dl class="row mb-2 small"><dt class="col-6">Kuota siswa</dt><dd class="col-6"><?= $s->student_quota > 0 ? (int)$s->student_quota : 'Tanpa batas' ?></dd>
<dt class="col-6">Harga per siswa</dt><dd class="col-6"><?= rupiah($s->price_per_student) ?></dd>
<dt class="col-6">Estimasi tagihan</dt><dd class="col-6"><strong><?= rupiah($s->students_total * $s->price_per_student) ?></strong></dd>
<dt class="col-6">Alamat</dt><dd class="col-6"><?= e($s->address) ?: '-' ?></dd><dt class="col-6">Email / Telp</dt><dd class="col-6"><?= e($s->email) ?: '-' ?> / <?= e($s->phone) ?: '-' ?></dd>
<dt class="col-6">Catatan</dt><dd class="col-6"><?= e($s->notes) ?: '-' ?></dd></dl>
<a class="btn btn-outline-primary btn-sm" href="<?= site_url('super/sekolah_form/' . $s->id) ?>">Ubah</a>
<?= form_open('super/sekolah_toggle/' . $s->id, array('class' => 'd-inline', 'data-confirm' => $s->is_active ? 'Nonaktifkan sekolah ini? Semua akunnya tidak bisa login (data tetap tersimpan).' : 'Aktifkan kembali sekolah ini?')) ?>
<button class="btn btn-sm <?= $s->is_active ? 'btn-outline-danger' : 'btn-outline-success' ?>"><?= $s->is_active ? 'Nonaktifkan sekolah' : 'Aktifkan sekolah' ?></button><?= form_close() ?>
</div></div></div>
<div class="col-lg-7"><div class="card"><div class="card-body"><h2 class="h6">Akun Admin Sekolah</h2>
<table class="table table-sm align-middle"><thead><tr><th>Nama</th><th>Username</th><th>Login terakhir</th><th>Status</th><th></th></tr></thead><tbody>
<?php foreach ($admins as $a): ?><tr><td><?= e($a->name) ?></td><td><?= e($a->username) ?></td><td><?= $a->last_login ? e($a->last_login) : '-' ?></td><td><?= $a->is_active ? 'Aktif' : 'Nonaktif' ?></td>
<td class="text-end text-nowrap"><?= form_open('super/admin_reset/' . $a->id, array('class' => 'd-inline', 'data-confirm' => 'Reset password admin ini?')) ?><button class="btn btn-sm btn-outline-warning">Reset</button><?= form_close() ?>
<?= form_open('super/admin_toggle/' . $a->id, array('class' => 'd-inline', 'data-confirm' => 'Ubah status akun?')) ?><button class="btn btn-sm btn-outline-secondary">Aktif/Nonaktif</button><?= form_close() ?></td></tr><?php endforeach; ?></tbody></table>
<hr><strong class="small">Tambah admin</strong>
<?= form_open('super/admin_tambah/' . $s->id, array('class' => 'row g-2 mt-1')) ?>
<div class="col-md-4"><input name="name" class="form-control form-control-sm" placeholder="Nama" required></div><div class="col-md-3"><input name="username" class="form-control form-control-sm" placeholder="Username" autocomplete="off" required></div>
<div class="col-md-3"><input type="password" name="password" class="form-control form-control-sm" placeholder="Password (min. 8)" autocomplete="new-password" required></div><div class="col-md-2"><button class="btn btn-sm btn-primary w-100">Tambah</button></div><?= form_close() ?>
</div></div></div></div>
