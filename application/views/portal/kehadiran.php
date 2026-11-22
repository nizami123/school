<?php $m = $month; ?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <a class="btn btn-sm btn-outline-secondary" href="<?= site_url('portal/kehadiran?bulan=' . $prev) ?>"><i class="bi bi-chevron-left"></i></a>
  <strong><?= e(bulan_id($ym)) ?></strong>
  <a class="btn btn-sm btn-outline-secondary <?= $next > date('Y-m') ? 'disabled' : '' ?>" href="<?= site_url('portal/kehadiran?bulan=' . $next) ?>"><i class="bi bi-chevron-right"></i></a>
</div>
<div class="card mb-3"><div class="card-body">
  <div class="portal-card-title">Rekap bulanan (otomatis)</div>
  <?php if ($m && (int)$m->total > 0): ?>
  <div class="row g-2 text-center">
    <div class="col-4 col-sm-2"><div class="stat-mini"><div class="n"><?= (int)$m->hadir ?></div><div class="l">Hadir</div></div></div>
    <div class="col-4 col-sm-2"><div class="stat-mini warn"><div class="n"><?= (int)$m->terlambat ?></div><div class="l">Terlambat</div></div></div>
    <div class="col-4 col-sm-2"><div class="stat-mini info"><div class="n"><?= (int)$m->sakit ?></div><div class="l">Sakit</div></div></div>
    <div class="col-4 col-sm-2"><div class="stat-mini info"><div class="n"><?= (int)$m->izin ?></div><div class="l">Izin</div></div></div>
    <div class="col-4 col-sm-2"><div class="stat-mini bad"><div class="n"><?= (int)$m->alpa ?></div><div class="l">Alpa</div></div></div>
    <div class="col-4 col-sm-2"><div class="stat-mini"><div class="n"><?= (float)$m->pct ?>%</div><div class="l">Kehadiran</div></div></div>
  </div><?php if ((int)$m->belum > 0): ?><div class="small text-muted mt-2"><?= (int)$m->belum ?> sesi belum dikonfirmasi guru.</div><?php endif; ?>
  <?php else: ?><p class="text-muted small mb-0">Belum ada data absensi pada bulan ini.</p><?php endif; ?>
</div></div>

<div class="card mb-3"><div class="card-body"><div class="portal-card-title">Riwayat kehadiran</div>
<?php $cur = ''; foreach ($days as $r): if ($r->session_date !== $cur): $cur = $r->session_date; ?><div class="fw-semibold small text-success border-bottom pb-1 mt-3 mb-1"><?= tanggal_id($cur) ?></div><?php endif; ?>
  <div class="d-flex justify-content-between align-items-center py-1"><div><?= e($r->subject) ?><br><small class="text-muted"><?= substr($r->start_time, 0, 5) ?>–<?= substr($r->end_time, 0, 5) ?><?= $r->scanned_at ? ' · scan ' . jam_id($r->scanned_at, FALSE) : '' ?><?= $r->note ? ' · ' . e($r->note) : '' ?></small></div><?= status_badge($r->status) ?></div>
<?php endforeach; if (!$days): ?><p class="text-muted small mb-0">Tidak ada data.</p><?php endif; ?></div></div>

<div class="card"><div class="card-body"><div class="portal-card-title">Ringkasan 6 bulan terakhir</div>
<table class="table table-sm mb-0"><thead><tr><th>Bulan</th><th class="text-center">Hadir</th><th class="text-center">Sakit/Izin</th><th class="text-center">Alpa</th><th class="text-center">%</th></tr></thead><tbody>
<?php foreach ($trend as $t): ?><tr><td><a href="<?= site_url('portal/kehadiran?bulan=' . $t->ym) ?>"><?= e(bulan_id($t->ym)) ?></a></td><td class="text-center"><?= (int)$t->present ?></td><td class="text-center"><?= (int)$t->excused ?></td><td class="text-center"><?= (int)$t->absent ?></td><td class="text-center"><?= $t->total ? round($t->present * 100 / $t->total, 1) : 0 ?>%</td></tr><?php endforeach; if (!$trend): ?><tr><td colspan="5" class="text-muted">Belum ada data.</td></tr><?php endif; ?></tbody></table></div></div>
