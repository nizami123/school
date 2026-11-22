<div class="card mb-3"><div class="card-body"><form class="row g-2 align-items-end" method="get" data-noload>
  <div class="col-6 col-md-3"><label class="form-label small mb-0">Bulan</label><input type="month" name="bulan" value="<?= e($ym) ?>" class="form-control"></div>
  <div class="col-6 col-md-3"><label class="form-label small mb-0">Kelas</label><select name="class_id" class="form-select"><option value="">Semua kelas</option><?php foreach ($classes as $c): ?><option value="<?= $c->id ?>" <?= (int)$class_id === (int)$c->id ? 'selected' : '' ?>><?= e($c->name) ?></option><?php endforeach; ?></select></div>
  <div class="col-12 col-md-6 d-flex gap-2 flex-wrap"><button class="btn btn-primary">Tampilkan</button>
    <?php if (class_exists('PhpOffice\\PhpSpreadsheet\\Spreadsheet')): ?><a class="btn btn-outline-success" href="<?= site_url('rekap/bulanan?bulan=' . $ym . '&class_id=' . $class_id . '&format=xlsx') ?>">Excel</a><?php endif; ?>
    <button type="button" class="btn btn-outline-dark" onclick="window.print()"><i class="bi bi-printer"></i></button></div>
</form></div></div>
<div class="card"><div class="card-body"><h2 class="h6">Rekap Kehadiran — <?= e(bulan_id($ym)) ?></h2>
<div class="table-responsive"><table class="table table-sm table-hover align-middle">
<thead><tr><th>#</th><th>NIS</th><th>Nama</th><th>Kelas</th><th class="text-center">Hadir</th><th class="text-center">Terlambat</th><th class="text-center">Sakit</th><th class="text-center">Izin</th><th class="text-center">Alpa</th><th class="text-center">Belum</th><th class="text-center">Sesi</th><th style="min-width:120px">% Hadir</th></tr></thead><tbody>
<?php foreach ($rows as $i => $r): $pct = $r->pct === NULL ? NULL : (float)$r->pct; ?><tr>
  <td><?= $i + 1 ?></td><td><?= e($r->nis) ?></td><td><a href="<?= site_url('rekap/siswa/' . $r->id . '?from=' . $ym . '-01&to=' . date('Y-m-t', strtotime($ym . '-01'))) ?>"><?= e($r->name) ?></a></td><td><?= e($r->class_name) ?></td>
  <td class="text-center"><?= (int)$r->hadir ?></td><td class="text-center"><?= (int)$r->terlambat ?></td><td class="text-center"><?= (int)$r->sakit ?></td><td class="text-center"><?= (int)$r->izin ?></td><td class="text-center"><?= (int)$r->alpa ?></td><td class="text-center"><?= (int)$r->belum ?></td><td class="text-center"><?= (int)$r->total ?></td>
  <td><?php if ($pct === NULL): ?><span class="text-muted">-</span><?php else: ?><div class="d-flex align-items-center gap-2"><div class="progress flex-grow-1"><div class="progress-bar <?= $pct < 75 ? 'bg-danger' : ($pct < 90 ? 'bg-warning' : '') ?>" style="width:<?= $pct ?>%"></div></div><small><?= $pct ?>%</small></div><?php endif; ?></td></tr>
<?php endforeach; if (!$rows): ?><tr><td colspan="12" class="text-muted">Tidak ada siswa.</td></tr><?php endif; ?></tbody></table></div>
<div class="form-text">Dihitung otomatis dari seluruh sesi absensi pada bulan terpilih (satuan: sesi/mata pelajaran). Persentase hadir = (hadir + terlambat) ÷ total sesi.</div></div></div>
