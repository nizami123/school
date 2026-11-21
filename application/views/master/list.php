<div class="card"><div class="card-body">
<div class="d-flex flex-wrap gap-2 mb-3">
  <a class="btn btn-primary" href="<?= site_url('master/' . $key . '/form') ?>"><i class="bi bi-plus-lg"></i> Tambah <?= e($e['singular']) ?></a>
  <?php if ($key === 'schedules'): ?><a class="btn btn-outline-primary" href="<?= site_url('master/schedules/week') ?>"><i class="bi bi-calendar-week"></i> Tampilan Mingguan</a><?php endif; ?>
  <?php $hasx = class_exists('PhpOffice\\PhpSpreadsheet\\Spreadsheet'); ?>
  <?php if ($key !== 'users'): ?><div class="btn-group"><?php if ($hasx): ?><a class="btn btn-outline-success" href="<?= site_url('master/' . $key . '/export?format=xlsx') ?>"><i class="bi bi-file-earmark-excel"></i> Ekspor Excel</a><?php endif; ?><a class="btn btn-outline-secondary" href="<?= site_url('master/' . $key . '/export?format=csv') ?>"><?= $hasx ? 'CSV' : '<i class="bi bi-file-earmark-excel"></i> Ekspor CSV' ?></a></div><?php endif; ?>
  <?php if (!empty($e['import'])): ?>
    <button class="btn btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#imp"><i class="bi bi-upload"></i> Impor</button>
    <?php if ($hasx): ?><a class="btn btn-link" href="<?= site_url('master/' . $key . '/template?format=xlsx') ?>">Template Excel</a><?php endif; ?><a class="btn btn-link" href="<?= site_url('master/' . $key . '/template?format=csv') ?>">Template CSV</a>
  <?php endif; ?>
</div>
<?php if (!empty($e['import'])): ?>
<div class="collapse mb-3" id="imp"><?= form_open_multipart('master/' . $key . '/import', array('class' => 'row g-2 align-items-end')) ?>
  <div class="col-md-6"><label class="form-label">File Excel (.xlsx) atau CSV</label><input type="file" name="file" class="form-control" accept=".csv,.xlsx,.xls" required></div>
  <div class="col-md-3"><button class="btn btn-primary">Unggah &amp; Impor</button></div>
  <div class="col-12 small text-muted">Gunakan template (kolom NIS/NISN/WhatsApp berformat Teks agar angka 0 di depan tidak hilang). Hasil ekspor juga bisa diimpor kembali. Baris yang salah dilewati dan dilaporkan beserta nomor barisnya.</div><?= form_close() ?></div>
<?php endif; ?>
<div class="table-responsive"><table class="table table-hover align-middle dt">
<thead><tr><th>#</th><?php foreach ($e['columns'] as $c): ?><th><?= e($c[1]) ?></th><?php endforeach; ?><th class="text-end">Aksi</th></tr></thead><tbody>
<?php foreach ($rows as $i => $r): ?><tr><td><?= $i + 1 ?></td>
<?php foreach ($e['columns'] as $c): $t = isset($c[2]) ? $c[2] : ''; $v = isset($r[$c[0]]) ? $r[$c[0]] : ''; ?><td>
  <?php if ($t === 'photo'): ?><img class="avatar" src="<?= student_photo($v) ?>" alt="">
  <?php elseif ($t === 'active'): ?><?= $v ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-secondary">Nonaktif</span>' ?>
  <?php elseif ($t === 'day'): ?><?= hari_id($v) ?>
  <?php elseif ($t === 'time'): ?><?= substr($v, 0, 5) ?>
  <?php else: ?><?= e($v) ?><?php endif; ?></td><?php endforeach; ?>
<td class="text-end text-nowrap">
  <?php if (!empty($e['qr'])): ?><a class="btn btn-sm btn-outline-success" title="Kartu" href="<?= site_url('qr/cards?scope=student&id=' . $r['id']) ?>"><i class="bi bi-person-vcard"></i></a>
    <a class="btn btn-sm btn-outline-secondary" title="Rekap" href="<?= site_url('rekap/siswa/' . $r['id']) ?>"><i class="bi bi-table"></i></a><?php endif; ?>
  <?php if (!empty($e['duplicate'])): ?><a class="btn btn-sm btn-outline-secondary" title="Duplikasi" href="<?= site_url('master/' . $key . '/form?copy=' . $r['id']) ?>"><i class="bi bi-copy"></i></a><?php endif; ?>
  <a class="btn btn-sm btn-outline-primary" title="Ubah" href="<?= site_url('master/' . $key . '/form/' . $r['id']) ?>"><i class="bi bi-pencil"></i></a>
  <?php if (!empty($e['toggle'])): ?><?= form_open('master/' . $key . '/toggle/' . $r['id'], array('class' => 'd-inline', 'data-confirm' => 'Ubah status aktif/nonaktif?')) ?><button class="btn btn-sm btn-outline-warning" title="Aktif/Nonaktif"><i class="bi bi-toggle-on"></i></button><?= form_close() ?><?php endif; ?>
  <?= form_open('master/' . $key . '/delete/' . $r['id'], array('class' => 'd-inline', 'data-confirm' => 'Hapus data ini? Tindakan tidak dapat dibatalkan.')) ?><button class="btn btn-sm btn-outline-danger" title="Hapus"><i class="bi bi-trash"></i></button><?= form_close() ?>
</td></tr><?php endforeach; ?></tbody></table></div>
</div></div>
