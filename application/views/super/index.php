<div class="row g-3 mb-3">
<?php foreach (array(array('Sekolah', $t['schools'], 'bi-building'), array('Sekolah aktif', $t['active'], 'bi-check-circle'), array('Total siswa terdaftar', number_format($t['students'], 0, ',', '.'), 'bi-people'), array('Estimasi tagihan / periode', rupiah($t['bill']), 'bi-cash-coin')) as $c): ?>
<div class="col-6 col-lg-3"><div class="card"><div class="card-body"><div class="text-muted small"><i class="bi <?= $c[2] ?>"></i> <?= e($c[0]) ?></div><div class="fs-4 fw-bold"><?= e($c[1]) ?></div></div></div></div>
<?php endforeach; ?></div>
<div class="card"><div class="card-body">
<a class="btn btn-primary mb-3" href="<?= site_url('super/sekolah_form') ?>"><i class="bi bi-plus-lg"></i> Tambah Sekolah</a>
<div class="table-responsive"><table class="table table-hover align-middle dt">
<thead><tr><th>Sekolah</th><th class="text-end">Siswa terdaftar</th><th class="text-end">Aktif</th><th class="text-end">Kuota</th><th class="text-end">Harga/siswa</th><th class="text-end">Estimasi</th><th>Status</th><th></th></tr></thead><tbody>
<?php foreach ($rows as $r): $over = $r->student_quota > 0 && $r->students_total >= $r->student_quota; ?>
<tr><td><strong><?= e($r->name) ?></strong><br><small class="text-muted"><?= e($r->phone) ?></small></td>
<td class="text-end"><?= (int)$r->students_total ?></td><td class="text-end"><?= (int)$r->students_active ?></td>
<td class="text-end"><?= $r->student_quota > 0 ? (int)$r->student_quota . ($over ? ' <span class="badge bg-warning text-dark">penuh</span>' : '') : '<span class="text-muted">tanpa batas</span>' ?></td>
<td class="text-end"><?= rupiah($r->price_per_student) ?></td><td class="text-end"><?= rupiah($r->students_total * $r->price_per_student) ?></td>
<td><?= $r->is_active ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-secondary">Nonaktif</span>' ?></td>
<td class="text-end"><a class="btn btn-sm btn-outline-primary" href="<?= site_url('super/sekolah_detail/' . $r->id) ?>">Detail</a></td></tr>
<?php endforeach; ?></tbody></table></div></div></div>
<div class="card mt-3"><div class="card-body small"><strong>Cron cPanel (untuk semua sekolah)</strong><br>
<code>* * * * * php <?= e(FCPATH) ?>index.php cron wa</code><br><code>0 16 * * 1-6 php <?= e(FCPATH) ?>index.php cron daily</code><br>URL: <code><?= e(site_url('cron/wa/' . $cron_key)) ?></code></div></div>
