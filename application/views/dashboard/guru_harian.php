<div class="mb-3"><h2 class="h5 mb-0">Absensi Harian (Pagi)</h2><small class="text-muted"><?= tanggal_id(date('Y-m-d')) ?></small></div>
<?php if (!$classes): ?><div class="alert alert-light border">Belum ada kelas. Hubungi admin sekolah.</div><?php endif; ?>
<?php foreach ($classes as $c): $st = $c->stats; ?>
<div class="card mb-3"><div class="card-body">
  <div class="d-flex justify-content-between align-items-start"><div class="fw-bold fs-5">Kelas <?= e($c->name) ?></div>
    <?php if ($c->ses_status === 'open'): ?><span class="badge bg-success">Sesi Berjalan</span><?php elseif ($c->ses_status === 'closed'): ?><span class="badge bg-secondary">Selesai</span><?php else: ?><span class="badge bg-light text-dark border">Belum Mulai</span><?php endif; ?></div>
  <div class="mt-2 small">Hadir <strong class="text-success"><?= (int)$st['present'] ?></strong> · Belum hadir <strong class="text-danger"><?= (int)$st['absent'] ?></strong> · Total <?= (int)$st['total'] ?></div>
  <div class="progress my-2"><div class="progress-bar" style="width:<?= (int)$st['pct'] ?>%"></div></div>
  <div class="d-flex gap-2 flex-wrap">
  <?php if ($c->ses_status === 'open'): ?>
    <a class="btn btn-primary" href="<?= site_url('scan/run/' . $c->ses_id) ?>"><i class="bi bi-qr-code-scan"></i> Lanjutkan Absensi</a>
    <?= form_open('scan/finish', array('data-confirm' => 'Akhiri sesi absensi ini? Siswa yang belum scan akan masuk daftar belum dikonfirmasi.')) ?><input type="hidden" name="session_id" value="<?= $c->ses_id ?>"><button class="btn btn-outline-danger"><i class="bi bi-stop-circle"></i> Akhiri Sesi</button><?= form_close() ?>
  <?php elseif ($c->ses_status === 'closed'): ?>
    <a class="btn btn-outline-primary" href="<?= site_url('scan/detail/' . $c->ses_id) ?>"><i class="bi bi-list-check"></i> Lihat Rekap</a>
    <a class="btn btn-outline-secondary" href="<?= site_url('scan/confirm/' . $c->ses_id) ?>">Konfirmasi Status</a>
    <?= form_open('scan/start_harian') ?><input type="hidden" name="class_id" value="<?= (int)$c->id ?>"><button class="btn btn-outline-primary">Buka Ulang</button><?= form_close() ?>
  <?php else: ?>
    <?= form_open('scan/start_harian') ?><input type="hidden" name="class_id" value="<?= (int)$c->id ?>"><button class="btn btn-primary btn-lg"><i class="bi bi-play-circle"></i> Mulai Absensi Pagi</button><?= form_close() ?>
  <?php endif; ?></div>
</div></div>
<?php endforeach; ?>
