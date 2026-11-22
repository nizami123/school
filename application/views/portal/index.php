<?php $m = $month; ?>
<div class="card mb-3"><div class="card-body">
  <div class="portal-card-title">Kehadiran hari ini · <?= tanggal_id(date('Y-m-d')) ?></div>
  <?php if ($today_status): ?><div class="list-group list-group-flush list-tight">
    <?php foreach ($today_status as $r): ?><div class="list-group-item d-flex justify-content-between align-items-center px-0"><div><strong><?= e($r->subject) ?></strong><br><small class="text-muted"><?= substr($r->start_time, 0, 5) ?>–<?= substr($r->end_time, 0, 5) ?><?= $r->scanned_at ? ' · scan ' . jam_id($r->scanned_at, FALSE) : '' ?></small></div><?= status_badge($r->status) ?></div><?php endforeach; ?></div>
  <?php else: ?><p class="text-muted mb-0 small"><?= $schedule ? 'Belum ada absensi tercatat hari ini.' : 'Tidak ada jadwal pelajaran hari ini.' ?></p><?php endif; ?>
</div></div>

<?php if ($m && (int)$m->total > 0): ?>
<div class="card mb-3"><div class="card-body">
  <div class="d-flex justify-content-between"><div class="portal-card-title">Rekap bulan <?= e(bulan_id(date('Y-m'))) ?></div><a class="small" href="<?= site_url('portal/kehadiran') ?>">Detail</a></div>
  <div class="row g-2 text-center">
    <div class="col-3"><div class="stat-mini"><div class="n"><?= (int)$m->hadir ?></div><div class="l">Hadir</div></div></div>
    <div class="col-3"><div class="stat-mini warn"><div class="n"><?= (int)$m->terlambat ?></div><div class="l">Terlambat</div></div></div>
    <div class="col-3"><div class="stat-mini info"><div class="n"><?= (int)$m->sakit + (int)$m->izin ?></div><div class="l">Sakit/Izin</div></div></div>
    <div class="col-3"><div class="stat-mini bad"><div class="n"><?= (int)$m->alpa + (int)$m->belum ?></div><div class="l">Alpa</div></div></div>
  </div>
  <div class="progress mt-3"><div class="progress-bar" style="width:<?= (float)$m->pct ?>%"></div></div><div class="small text-muted mt-1">Persentase hadir: <strong><?= (float)$m->pct ?>%</strong> dari <?= (int)$m->total ?> sesi</div>
</div></div>
<?php endif; ?>

<?php if ($unpaid->n > 0 || $leave_pending > 0): ?>
<div class="row g-2 mb-3">
  <?php if ($unpaid->n > 0): ?><div class="col-12 col-sm-6"><a class="card text-decoration-none h-100 border-start border-4 border-warning" href="<?= site_url('portal/bayar') ?>"><div class="card-body py-2"><div class="small text-muted">Tagihan belum lunas</div><strong><?= (int)$unpaid->n ?> tagihan · <?= rupiah($unpaid->sisa) ?></strong></div></a></div><?php endif; ?>
  <?php if ($leave_pending > 0): ?><div class="col-12 col-sm-6"><a class="card text-decoration-none h-100 border-start border-4 border-info" href="<?= site_url('portal/izin') ?>"><div class="card-body py-2"><div class="small text-muted">Izin menunggu persetujuan</div><strong><?= (int)$leave_pending ?> pengajuan</strong></div></a></div><?php endif; ?>
</div>
<?php endif; ?>

<div class="card mb-3"><div class="card-body"><div class="portal-card-title">Jadwal hari ini</div>
  <?php if ($schedule): ?><div class="list-group list-group-flush list-tight"><?php foreach ($schedule as $r): ?><div class="list-group-item px-0 d-flex justify-content-between"><div><strong><?= e($r->subject) ?></strong><br><small class="text-muted"><?= e($r->teacher) ?><?= $r->room ? ' · ' . e($r->room) : '' ?></small></div><span class="text-nowrap small"><?= substr($r->start_time, 0, 5) ?>–<?= substr($r->end_time, 0, 5) ?></span></div><?php endforeach; ?></div>
  <?php else: ?><p class="text-muted small mb-0">Tidak ada jadwal.</p><?php endif; ?></div></div>

<div class="card mb-3"><div class="card-body"><div class="d-flex justify-content-between"><div class="portal-card-title">Pengumuman terbaru</div><a class="small" href="<?= site_url('portal/pengumuman') ?>">Semua</a></div>
  <?php foreach ($ann as $a): ?><a class="d-block text-decoration-none text-body py-2 border-top" href="<?= site_url('portal/pengumuman/' . $a->id) ?>"><?= kategori_badge($a->category) ?> <?= $a->is_pinned ? '<i class="bi bi-pin-angle-fill text-danger"></i>' : '' ?><div class="fw-semibold"><?= e($a->title) ?></div><small class="text-muted"><?= tanggal_id($a->publish_date, FALSE) ?></small></a><?php endforeach; if (!$ann): ?><p class="text-muted small mb-0">Belum ada pengumuman.</p><?php endif; ?></div></div>

<div class="card"><div class="card-body"><div class="d-flex justify-content-between"><div class="portal-card-title">Agenda 30 hari ke depan</div><a class="small" href="<?= site_url('portal/jadwal?tab=kalender') ?>">Kalender</a></div>
  <?php foreach ($events as $ev): ?><div class="py-2 border-top d-flex justify-content-between gap-2"><div><span class="d-inline-block rounded-circle dot-<?= e($ev->event_type) ?>" style="width:9px;height:9px"></span> <strong><?= e($ev->title) ?></strong></div><small class="text-muted text-nowrap"><?= tanggal_id($ev->start_date, FALSE) ?><?= $ev->end_date !== $ev->start_date ? ' – ' . tanggal_id($ev->end_date, FALSE) : '' ?></small></div><?php endforeach; if (!$events): ?><p class="text-muted small mb-0">Tidak ada agenda.</p><?php endif; ?></div></div>
