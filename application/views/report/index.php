<?php $types = Report_model::$types; ?>
<div class="card"><div class="card-body">
<p class="text-muted">Pilih jenis laporan dan filter, lalu cetak, unduh PDF, atau Excel.</p>
<form id="rf" class="row g-2" method="get" action="<?= site_url('report/output') ?>" target="_blank" data-noload>
<div class="col-md-4"><label class="form-label small mb-0">Jenis laporan</label><select name="type" class="form-select"><?php foreach ($types as $k => $l): ?><option value="<?= $k ?>" <?= $k === $type ? 'selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?></select></div>
<div class="col-6 col-md-2"><label class="form-label small mb-0">Dari</label><input type="date" name="from" value="<?= e($f['from']) ?>" class="form-control"></div>
<div class="col-6 col-md-2"><label class="form-label small mb-0">Sampai</label><input type="date" name="to" value="<?= e($f['to']) ?>" class="form-control"></div>
<div class="col-md-2"><label class="form-label small mb-0">Tahun Ajaran</label><select name="year_id" class="form-select"><option value="">Semua</option><?php foreach ($years as $y): ?><option value="<?= $y->id ?>"><?= e($y->name) ?></option><?php endforeach; ?></select></div>
<div class="col-md-2"><label class="form-label small mb-0">Semester</label><select name="semester_id" class="form-select"><option value="">Semua</option><?php foreach ($semesters as $s): ?><option value="<?= $s->id ?>"><?= e($s->label) ?></option><?php endforeach; ?></select></div>
<div class="col-md-3"><label class="form-label small mb-0">Kelas</label><select name="class_id" class="form-select"><option value="">Semua</option><?php foreach ($classes as $c): ?><option value="<?= $c->id ?>"><?= e($c->name) ?></option><?php endforeach; ?></select></div>
<div class="col-md-3"><label class="form-label small mb-0">Guru</label><select name="teacher_id" class="form-select"><option value="">Semua</option><?php foreach ($teachers as $c): ?><option value="<?= $c->id ?>"><?= e($c->name) ?></option><?php endforeach; ?></select></div>
<div class="col-md-3"><label class="form-label small mb-0">Mata pelajaran</label><select name="subject_id" class="form-select"><option value="">Semua</option><?php foreach ($subjects as $c): ?><option value="<?= $c->id ?>"><?= e($c->name) ?></option><?php endforeach; ?></select></div>
<div class="col-md-3"><label class="form-label small mb-0">Status</label><select name="status" class="form-select"><option value="">Semua</option><?php foreach (array('hadir', 'terlambat', 'sakit', 'izin', 'alpa', 'belum') as $k): ?><option value="<?= $k ?>"><?= status_label($k) ?></option><?php endforeach; ?></select></div>
<div class="col-12 d-flex gap-2 flex-wrap">
  <button name="format" value="print" class="btn btn-primary"><i class="bi bi-printer"></i> Cetak</button>
  <button name="format" value="pdf" class="btn btn-outline-danger"><i class="bi bi-file-earmark-pdf"></i> PDF</button>
  <button name="format" value="xlsx" class="btn btn-outline-success"><i class="bi bi-file-earmark-excel"></i> Excel</button>
  <button name="format" value="csv" class="btn btn-outline-secondary">CSV</button>
</div>
<div class="col-12 small text-muted">PDF langsung memerlukan Dompdf (<?= $has_pdf ? 'terpasang ✔' : 'belum terpasang — tombol PDF akan membuka versi cetak; gunakan "Simpan sebagai PDF" di dialog cetak' ?>). Excel .xlsx memerlukan PhpSpreadsheet (<?= $has_xlsx ? 'terpasang ✔' : 'belum terpasang — otomatis diunduh sebagai CSV yang dapat dibuka di Excel' ?>).</div>
</form></div></div>
