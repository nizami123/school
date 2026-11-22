<?php $this->load->view('iuran/_nav', array('pending' => $pending)); ?>
<div class="row g-3 mb-3">
  <div class="col-6 col-md-3"><div class="card stat-card"><div class="card-body"><div class="num"><?= (int)$sum->n ?></div><div class="lbl">Tagihan (sesuai filter)</div></div></div></div>
  <div class="col-6 col-md-3"><div class="card stat-card"><div class="card-body"><div class="num fs-5"><?= rupiah($sum->total) ?></div><div class="lbl">Total tagihan</div></div></div></div>
  <div class="col-6 col-md-3"><div class="card stat-card"><div class="card-body"><div class="num fs-5"><?= rupiah($sum->paid) ?></div><div class="lbl">Terbayar</div></div></div></div>
  <div class="col-6 col-md-3"><div class="card stat-card"><div class="card-body"><div class="num fs-5 text-danger"><?= rupiah($sum->total - $sum->paid) ?></div><div class="lbl">Belum terbayar</div></div></div></div>
</div>
<div class="card mb-3"><div class="card-body"><form class="row g-2" method="get" data-noload>
  <div class="col-6 col-md-2"><select name="status" class="form-select"><?php foreach (array('tunggakan' => 'Belum lunas', 'belum' => 'Belum bayar', 'sebagian' => 'Sebagian', 'lunas' => 'Lunas', 'semua' => 'Semua') as $k => $l): ?><option value="<?= $k ?>" <?= $f['status'] === $k ? 'selected' : '' ?>><?= $l ?></option><?php endforeach; ?></select></div>
  <div class="col-6 col-md-2"><select name="class_id" class="form-select"><option value="">Semua kelas</option><?php foreach ($classes as $c): ?><option value="<?= $c->id ?>" <?= (int)$f['class_id'] === (int)$c->id ? 'selected' : '' ?>><?= e($c->name) ?></option><?php endforeach; ?></select></div>
  <div class="col-6 col-md-3"><select name="fee_type_id" class="form-select"><option value="">Semua jenis</option><?php foreach ($fees as $t): ?><option value="<?= $t->id ?>" <?= (int)$f['fee_type_id'] === (int)$t->id ? 'selected' : '' ?>><?= e($t->name) ?></option><?php endforeach; ?></select></div>
  <div class="col-6 col-md-2"><input name="period" class="form-control" placeholder="Periode (2026-11)" value="<?= e($f['period']) ?>"></div>
  <div class="col-8 col-md-2"><input name="q" class="form-control" placeholder="Nama/NIS" value="<?= e($f['q']) ?>"></div>
  <div class="col-4 col-md-1"><button class="btn btn-primary w-100">Cari</button></div>
</form></div></div>
<div class="card"><div class="card-body"><div class="table-responsive"><table class="table table-hover align-middle table-sm">
<thead><tr><th>Siswa</th><th>Tagihan</th><th class="text-end">Nominal</th><th class="text-end">Terbayar</th><th>Jatuh Tempo</th><th>Status</th><th></th></tr></thead><tbody>
<?php foreach ($rows as $r): $late = $r->due_date && $r->due_date < date('Y-m-d') && in_array($r->status, array('belum', 'sebagian'), TRUE); ?><tr>
  <td><?= e($r->student_name) ?><br><small class="text-muted"><?= e($r->class_name) ?> · <?= e($r->nis) ?></small></td>
  <td><?= e($r->title) ?></td><td class="text-end"><?= rupiah($r->amount) ?></td><td class="text-end"><?= rupiah($r->paid_total) ?></td>
  <td class="small <?= $late ? 'text-danger fw-semibold' : '' ?>"><?= $r->due_date ? tanggal_id($r->due_date, FALSE) : '-' ?><?= $late ? ' (lewat)' : '' ?></td>
  <td><?= status_badge($r->status) ?></td>
  <td class="text-end"><a class="btn btn-sm btn-outline-primary" href="<?= site_url('iuran/detail/' . $r->id) ?>">Kelola</a></td></tr>
<?php endforeach; if (!$rows): ?><tr><td colspan="7" class="text-muted">Tidak ada tagihan.</td></tr><?php endif; ?></tbody></table></div></div></div>
