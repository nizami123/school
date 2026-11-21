<?php $cards = array(
  array('Total Siswa', $total_students, 'bi-people'), array('Total Guru', $total_teachers, 'bi-person-workspace'), array('Total Kelas', $total_classes, 'bi-door-open'), array('Mata Pelajaran', $total_subjects, 'bi-book'),
  array('Sesi Hari Ini', $sessions_today, 'bi-calendar-check'), array('Siswa Hadir Hari Ini', $present_today, 'bi-person-check'), array('Siswa Belum Hadir', $absent_today, 'bi-person-x'), array('Persentase Hadir', $pct_today . '%', 'bi-percent'),
  array('WhatsApp Terkirim', $wa_sent, 'bi-whatsapp'), array('WhatsApp Gagal', $wa_failed, 'bi-exclamation-triangle'),
); ?>
<div class="row g-3 mb-3">
<?php foreach ($cards as $c): ?><div class="col-6 col-md-4 col-xl"><div class="card stat-card h-100"><div class="card-body d-flex justify-content-between align-items-center"><div><div class="num"><?= e($c[1]) ?></div><div class="lbl"><?= e($c[0]) ?></div></div><i class="bi <?= $c[2] ?>"></i></div></div></div><?php endforeach; ?>
</div>
<?php if ($wa_pending > 0): ?><div class="alert alert-warning py-2">Ada <?= $wa_pending ?> pesan WhatsApp menunggu di antrean. <a href="<?= site_url('wa') ?>">Kelola antrean</a></div><?php endif; ?>
<div class="row g-3 mb-3">
  <div class="col-lg-6"><div class="card"><div class="card-body"><h2 class="h6">Kehadiran Harian (14 hari)</h2><canvas id="cDaily" height="140"></canvas></div></div></div>
  <div class="col-lg-6"><div class="card"><div class="card-body"><h2 class="h6">Persentase Kehadiran Bulanan</h2><canvas id="cMonthly" height="140"></canvas></div></div></div>
  <div class="col-lg-4"><div class="card"><div class="card-body"><h2 class="h6">% Hadir per Kelas (30 hari)</h2><canvas id="cClass" height="170"></canvas></div></div></div>
  <div class="col-lg-4"><div class="card"><div class="card-body"><h2 class="h6">% Hadir per Mapel (30 hari)</h2><canvas id="cSubject" height="170"></canvas></div></div></div>
  <div class="col-lg-4"><div class="card"><div class="card-body"><h2 class="h6">Tren Keterlambatan (30 hari)</h2><canvas id="cLate" height="170"></canvas></div></div></div>
</div>
<div class="row g-3">
  <div class="col-lg-6"><div class="card"><div class="card-body"><h2 class="h6">Aktivitas Scan Terbaru</h2>
    <table class="table table-sm mb-0"><thead><tr><th>Jam</th><th>Siswa</th><th>Kelas</th><th>Mapel</th><th>Status</th></tr></thead><tbody>
    <?php foreach ($recent_scans as $r): ?><tr><td><?= jam_id($r->scanned_at, FALSE) ?></td><td><?= e($r->name) ?></td><td><?= e($r->class_name) ?></td><td><?= e($r->subject) ?></td><td><?= status_badge($r->status) ?></td></tr><?php endforeach; if (!$recent_scans): ?><tr><td colspan="5" class="text-muted">Belum ada scan hari ini.</td></tr><?php endif; ?></tbody></table></div></div></div>
  <div class="col-lg-6"><div class="card"><div class="card-body"><h2 class="h6">Siswa Belum Hadir (sesi yang sudah ditutup)</h2>
    <table class="table table-sm mb-0"><thead><tr><th>Siswa</th><th>Kelas</th><th>Mapel</th><th>Status</th></tr></thead><tbody>
    <?php foreach ($missing as $r): ?><tr><td><?= e($r->name) ?></td><td><?= e($r->class_name) ?></td><td><?= e($r->subject) ?></td><td><?= status_badge($r->status) ?></td></tr><?php endforeach; if (!$missing): ?><tr><td colspan="4" class="text-muted">Tidak ada.</td></tr><?php endif; ?></tbody></table></div></div></div>
  <div class="col-lg-6"><div class="card"><div class="card-body"><h2 class="h6">Jadwal Pembelajaran Hari Ini</h2><div class="table-responsive list-scroll">
    <table class="table table-sm mb-0"><thead><tr><th>Jam</th><th>Kelas</th><th>Mapel</th><th>Guru</th><th>Sesi</th></tr></thead><tbody>
    <?php foreach ($schedule_today as $r): ?><tr><td><?= substr($r->start_time, 0, 5) ?>–<?= substr($r->end_time, 0, 5) ?></td><td><?= e($r->class_name) ?></td><td><?= e($r->subject) ?></td><td><?= e($r->teacher) ?></td><td><?= $r->ses_status === 'open' ? '<span class="badge bg-success">Berjalan</span>' : ($r->ses_status === 'closed' ? '<span class="badge bg-secondary">Selesai</span>' : '<span class="text-muted">-</span>') ?></td></tr><?php endforeach; if (!$schedule_today): ?><tr><td colspan="5" class="text-muted">Tidak ada jadwal hari ini.</td></tr><?php endif; ?></tbody></table></div></div></div></div>
  <div class="col-lg-6"><div class="card"><div class="card-body"><h2 class="h6">Aktivitas Guru Hari Ini</h2>
    <table class="table table-sm mb-0"><thead><tr><th>Guru</th><th>Sesi</th><th>Hadir</th><th>Scan Terakhir</th></tr></thead><tbody>
    <?php foreach ($teacher_activity as $r): ?><tr><td><?= e($r->name) ?></td><td><?= (int)$r->sessions ?></td><td><?= (int)$r->present ?></td><td><?= $r->last_scan ? jam_id($r->last_scan, FALSE) : '-' ?></td></tr><?php endforeach; if (!$teacher_activity): ?><tr><td colspan="4" class="text-muted">Belum ada aktivitas.</td></tr><?php endif; ?></tbody></table></div></div>
    <div class="card mt-3"><div class="card-body"><h2 class="h6">Status Pengiriman WhatsApp</h2>
    <table class="table table-sm mb-0"><tbody><?php foreach ($wa_latest as $r): ?><tr><td><?= date('d/m H:i', strtotime($r->created_at)) ?></td><td><?= e($r->name) ?></td><td><?= e($r->notif_type) ?></td><td><?= status_badge($r->status) ?></td></tr><?php endforeach; if (!$wa_latest): ?><tr><td class="text-muted">Belum ada notifikasi.</td></tr><?php endif; ?></tbody></table></div></div></div>
</div>
<?php $scripts = '<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script><script src="' . base_url('assets/js/dashboard.js') . '"></script><script>window.CHART_URL=' . json_encode(site_url('dashboard/charts')) . ';</script>'; $this->load->vars(array('scripts' => $scripts)); ?>
