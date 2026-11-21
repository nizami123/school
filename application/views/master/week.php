<div class="card mb-3 no-print"><div class="card-body"><form class="row g-2" method="get" data-noload>
  <div class="col-md-3"><select name="semester_id" class="form-select"><?php foreach ($semesters as $k => $l): ?><option value="<?= $k ?>" <?= $k == $sem ? 'selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?></select></div>
  <div class="col-md-3"><select name="class_id" class="form-select"><option value="">Semua kelas</option><?php foreach ($classes as $k => $l): ?><option value="<?= $k ?>" <?= $k == $cid ? 'selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?></select></div>
  <div class="col-md-3"><select name="teacher_id" class="form-select"><option value="">Semua guru</option><?php foreach ($teachers as $k => $l): ?><option value="<?= $k ?>" <?= $k == $tid ? 'selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?></select></div>
  <div class="col-md-3 d-flex gap-2"><button class="btn btn-primary">Tampilkan</button><button type="button" class="btn btn-outline-secondary" onclick="window.print()"><i class="bi bi-printer"></i> Cetak/PDF</button><a class="btn btn-light" href="<?= site_url('master/schedules') ?>">Daftar</a></div>
</form></div></div>
<div class="d-flex gap-3 overflow-auto">
<?php for ($d = 1; $d <= 6; $d++): ?><div class="week-col flex-fill"><h2 class="h6 text-center py-2 bg-white rounded"><?= hari_id($d) ?></h2>
  <?php if (empty($by[$d])) echo '<div class="text-muted text-center small">—</div>'; else foreach ($by[$d] as $r): ?>
  <div class="week-item"><strong><?= substr($r->start_time, 0, 5) ?>–<?= substr($r->end_time, 0, 5) ?></strong><br><?= e($r->subject_name) ?><br><span class="text-muted"><?= e($r->class_name) ?> · <?= e($r->teacher_name) ?><?= $r->room ? ' · ' . e($r->room) : '' ?></span>
    <div class="no-print"><a href="<?= site_url('master/schedules/form/' . $r->id) ?>" class="small">ubah</a></div></div><?php endforeach; ?></div><?php endfor; ?>
</div>
