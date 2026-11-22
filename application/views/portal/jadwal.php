<ul class="nav nav-pills nav-fill mb-3">
  <li class="nav-item"><a class="nav-link <?= $tab === 'jadwal' ? 'active' : '' ?>" href="<?= site_url('portal/jadwal') ?>">Jadwal Pelajaran</a></li>
  <li class="nav-item"><a class="nav-link <?= $tab === 'kalender' ? 'active' : '' ?>" href="<?= site_url('portal/jadwal?tab=kalender&bulan=' . $ym) ?>">Kalender Akademik</a></li>
</ul>
<?php if ($tab === 'jadwal'):
  $by = array(); foreach ($rows as $r) { $by[$r->day][] = $r; } ?>
  <?php foreach ($by as $day => $list): ?>
  <div class="card mb-3"><div class="card-body"><div class="portal-card-title <?= (int)$day === (int)date('N') ? 'text-success fw-bold' : '' ?>"><?= hari_id($day) ?><?= (int)$day === (int)date('N') ? ' (hari ini)' : '' ?></div>
    <?php foreach ($list as $r): ?><div class="d-flex justify-content-between py-2 border-top"><div><strong><?= e($r->subject) ?></strong><br><small class="text-muted"><?= e($r->teacher) ?><?= $r->room ? ' · ' . e($r->room) : '' ?></small></div><span class="small text-nowrap"><?= substr($r->start_time, 0, 5) ?>–<?= substr($r->end_time, 0, 5) ?></span></div><?php endforeach; ?></div></div>
  <?php endforeach; if (!$by): ?><div class="card"><div class="card-body text-muted">Jadwal pelajaran belum tersedia.</div></div><?php endif; ?>
<?php else:
  $first = $ym . '-01'; $dim = (int)date('t', strtotime($first)); $dow = (int)date('N', strtotime($first)); // 1=Senin
  $map = array();
  foreach ($events as $ev) { for ($t = max(strtotime($ev->start_date), strtotime($first)); $t <= min(strtotime($ev->end_date), strtotime($ym . '-' . $dim)); $t += 86400) { $map[(int)date('j', $t)][] = $ev; } }
?>
  <div class="d-flex justify-content-between align-items-center mb-2">
    <a class="btn btn-sm btn-outline-secondary" href="<?= site_url('portal/jadwal?tab=kalender&bulan=' . $prev) ?>"><i class="bi bi-chevron-left"></i></a><strong><?= e(bulan_id($ym)) ?></strong>
    <a class="btn btn-sm btn-outline-secondary" href="<?= site_url('portal/jadwal?tab=kalender&bulan=' . $next) ?>"><i class="bi bi-chevron-right"></i></a></div>
  <div class="card mb-3"><div class="card-body"><div class="cal-grid">
    <?php foreach (array('Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min') as $h): ?><div class="h"><?= $h ?></div><?php endforeach; ?>
    <?php for ($i = 1; $i < $dow; $i++): ?><div></div><?php endfor; ?>
    <?php for ($d = 1; $d <= $dim; $d++): $ts = strtotime($ym . '-' . sprintf('%02d', $d)); $wk = (int)date('N', $ts) >= 6; ?>
      <div class="d <?= $wk ? 'off' : '' ?> <?= date('Y-m-d', $ts) === date('Y-m-d') ? 'today' : '' ?>"><?= $d ?>
        <?php if (isset($map[$d])): foreach (array_slice($map[$d], 0, 2) as $ev): ?><span class="dot dot-<?= e($ev->event_type) ?>"></span><?php endforeach; endif; ?></div>
    <?php endfor; ?></div>
    <div class="small mt-3 d-flex flex-wrap gap-3"><span><i class="dot-libur d-inline-block rounded-circle" style="width:9px;height:9px"></i> Libur</span><span><i class="dot-ujian d-inline-block rounded-circle" style="width:9px;height:9px"></i> Ujian</span><span><i class="dot-kegiatan d-inline-block rounded-circle" style="width:9px;height:9px"></i> Kegiatan</span><span><i class="dot-rapat d-inline-block rounded-circle" style="width:9px;height:9px"></i> Rapat</span></div></div></div>
  <div class="card"><div class="card-body"><div class="portal-card-title">Agenda bulan ini</div>
    <?php foreach ($events as $ev): ?><div class="py-2 border-top"><?= kategori_badge($ev->event_type) ?> <strong><?= e($ev->title) ?></strong><br><small class="text-muted"><?= tanggal_id($ev->start_date, FALSE) ?><?= $ev->end_date !== $ev->start_date ? ' – ' . tanggal_id($ev->end_date, FALSE) : '' ?><?= $ev->description ? ' · ' . e($ev->description) : '' ?></small></div><?php endforeach; if (!$events): ?><p class="text-muted small mb-0">Tidak ada agenda.</p><?php endif; ?></div></div>
<?php endif; ?>
