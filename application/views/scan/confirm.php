<div class="card mb-3"><div class="card-body">
  <div class="fw-bold"><?= e($s->subject_name) ?> · <?= e($s->class_name) ?></div><div class="small text-muted"><?= tanggal_id($s->session_date) ?> · sesi ditutup <?= $s->ended_at ? jam_id($s->ended_at) : '-' ?></div>
  <div class="mt-2">Hadir <strong class="text-success"><?= (int)$stats['present'] ?></strong> dari <?= (int)$stats['total'] ?> siswa (<?= (int)$stats['pct'] ?>%)</div>
  <div class="alert alert-info py-2 mt-2 mb-0 small">Siswa yang tidak scan <strong>tidak otomatis dianggap alpa</strong>. Tentukan statusnya: Sakit, Izin, Alpa, atau biarkan "Belum Dikonfirmasi".</div>
</div></div>
<?= form_open('scan/save_confirm') ?><input type="hidden" name="session_id" value="<?= $s->id ?>">
<div class="card"><div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th>Siswa</th><th style="width:170px">Status</th><th>Catatan</th></tr></thead><tbody>
<?php foreach ($recs as $r): $present = in_array($r->status, array('hadir', 'terlambat'), TRUE); ?>
<tr><td><?= e($r->name) ?><div class="small text-muted"><?= e($r->nis) ?></div></td>
<?php if ($present): ?><td><?= status_badge($r->status) ?> <small class="text-muted"><?= jam_id($r->scanned_at, FALSE) ?></small></td><td></td>
<?php else: ?><td><select class="form-select form-select-sm" name="status[<?= $r->id ?>]"><?php foreach (array('belum' => 'Belum Dikonfirmasi', 'sakit' => 'Sakit', 'izin' => 'Izin', 'alpa' => 'Alpa') as $k => $l): ?><option value="<?= $k ?>" <?= $r->status === $k ? 'selected' : '' ?>><?= $l ?></option><?php endforeach; ?></select></td>
<td><input class="form-control form-control-sm" name="note[<?= $r->id ?>]" value="<?= e($r->note) ?>" maxlength="255" placeholder="opsional"></td><?php endif; ?></tr>
<?php endforeach; ?></tbody></table></div></div>
<div class="d-flex flex-wrap gap-2 mt-3">
  <button class="btn btn-primary"><i class="bi bi-save"></i> Simpan Status</button>
  <button class="btn btn-outline-success" name="send_wa" value="1"><i class="bi bi-whatsapp"></i> Simpan &amp; Kirim Notifikasi Ketidakhadiran</button>
  <a class="btn btn-light" href="<?= site_url('dashboard') ?>">Selesai</a>
</div><?= form_close() ?>
