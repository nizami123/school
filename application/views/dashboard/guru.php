<div class="mb-3"><h2 class="h5 mb-0">Jadwal Mengajar Hari Ini</h2><small class="text-muted"><?= tanggal_id(date('Y-m-d')) ?></small></div>
<?php if (!$rows): ?><div class="alert alert-light border">Tidak ada jadwal mengajar Anda hari ini.</div><?php endif; ?>
<?php foreach ($rows as $r):
  $now = date('H:i:s'); $live = ($now >= $r->start_time && $now <= $r->end_time); $st = $r->stats; ?>
<div class="card mb-3"><div class="card-body">
  <div class="d-flex justify-content-between align-items-start">
    <div><div class="fw-bold fs-5"><?= e($r->subject) ?> <span class="text-muted">· <?= e($r->class_name) ?></span></div>
      <div class="text-muted"><i class="bi bi-clock"></i> <?= substr($r->start_time, 0, 5) ?>–<?= substr($r->end_time, 0, 5) ?><?= $r->room ? ' · ' . e($r->room) : '' ?></div></div>
    <div class="text-end">
      <?php if ($r->ses_status === 'open'): ?><span class="badge bg-success">Sesi Berjalan</span>
      <?php elseif ($r->ses_status === 'closed'): ?><span class="badge bg-secondary">Selesai</span>
      <?php elseif ($live): ?><span class="badge bg-warning text-dark">Sedang Berlangsung</span>
      <?php else: ?><span class="badge bg-light text-dark border">Belum Mulai</span><?php endif; ?>
    </div>
  </div>
  <div class="mt-2 small">Hadir <strong class="text-success"><?= (int)$st['present'] ?></strong> · Belum hadir <strong class="text-danger"><?= (int)$st['absent'] ?></strong> · Total <?= (int)$st['total'] ?></div>
  <div class="progress my-2"><div class="progress-bar" style="width:<?= (int)$st['pct'] ?>%"></div></div>
  <div class="d-flex gap-2 flex-wrap">
    <?php if ($r->ses_status === 'open'): ?>
      <a class="btn btn-primary" href="<?= site_url('scan/run/' . $r->ses_id) ?>"><i class="bi bi-qr-code-scan"></i> Lanjutkan Absensi</a>
      <?= form_open('scan/finish', array('data-confirm' => 'Akhiri sesi absensi ini? Siswa yang belum scan akan masuk daftar belum dikonfirmasi.')) ?><input type="hidden" name="session_id" value="<?= $r->ses_id ?>"><button class="btn btn-outline-danger"><i class="bi bi-stop-circle"></i> Akhiri Sesi</button><?= form_close() ?>
    <?php elseif ($r->ses_status === 'closed'): ?>
      <a class="btn btn-outline-primary" href="<?= site_url('scan/detail/' . $r->ses_id) ?>"><i class="bi bi-list-check"></i> Lihat Rekap</a>
      <a class="btn btn-outline-secondary" href="<?= site_url('scan/confirm/' . $r->ses_id) ?>">Konfirmasi Status</a>
      <?= form_open('scan/start') ?><input type="hidden" name="schedule_id" value="<?= $r->id ?>"><button class="btn btn-outline-primary">Buka Ulang</button><?= form_close() ?>
    <?php else: ?>
      <?= form_open('scan/start') ?><input type="hidden" name="schedule_id" value="<?= $r->id ?>"><button class="btn btn-primary btn-lg"><i class="bi bi-play-circle"></i> Mulai Absensi</button><?= form_close() ?>
    <?php endif; ?>
  </div>
</div></div>
<?php endforeach; ?>
