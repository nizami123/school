<div class="mb-3"><a href="<?= site_url('master/extracurriculars') ?>" class="btn btn-light btn-sm"><i class="bi bi-arrow-left"></i> Kembali</a>
  <span class="ms-2 text-muted small"><?= $x->day ? hari_id($x->day) . ' ' : '' ?><?= $x->start_time ? substr($x->start_time, 0, 5) . '–' . substr($x->end_time, 0, 5) : '' ?> · Pembina: <?= e($x->coach ?: '-') ?> · Anggota aktif: <?= (int)$active ?><?= $x->quota ? ' / ' . (int)$x->quota : '' ?></span></div>
<div class="row g-3"><div class="col-lg-4"><div class="card"><div class="card-body"><h2 class="h6">Tambah Anggota</h2>
  <?= form_open('ekskul/tambah/' . $x->id) ?><select name="student_id" class="form-select mb-2" required><option value="">— pilih siswa —</option><?php foreach ($students as $s): ?><option value="<?= $s->id ?>"><?= e($s->label) ?></option><?php endforeach; ?></select>
  <button class="btn btn-primary w-100">Tambahkan</button><?= form_close() ?></div></div></div>
<div class="col-lg-8"><div class="card"><div class="card-body"><div class="table-responsive"><table class="table table-sm align-middle"><thead><tr><th>Siswa</th><th>Kelas</th><th>Status</th><th class="text-end">Aksi</th></tr></thead><tbody>
<?php foreach ($members as $m): ?><tr><td><?= e($m->name) ?><?= $m->requested_by_parent ? ' <small class="text-muted">(daftar via ortu)</small>' : '' ?></td><td><?= e($m->class_name) ?></td><td><?= status_badge($m->status) ?></td>
<td class="text-end"><?= form_open('ekskul/status/' . $m->id, array('class' => 'd-inline-flex gap-1')) ?>
  <?php if ($m->status === 'menunggu'): ?><button name="action" value="setuju" class="btn btn-sm btn-success">Terima</button><button name="action" value="tolak" class="btn btn-sm btn-outline-danger">Tolak</button>
  <?php elseif ($m->status === 'aktif'): ?><button name="action" value="keluar" class="btn btn-sm btn-outline-warning">Keluarkan</button><?php endif; ?>
  <button name="action" value="hapus" class="btn btn-sm btn-outline-secondary" title="Hapus catatan"><i class="bi bi-trash"></i></button><?= form_close() ?></td></tr>
<?php endforeach; if (!$members): ?><tr><td colspan="4" class="text-muted">Belum ada anggota.</td></tr><?php endif; ?></tbody></table></div></div></div></div></div>
