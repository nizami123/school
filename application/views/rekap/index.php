<?php $types = array(); foreach (Report_model::$types as $k => $l) { if ($k !== 'whatsapp') $types[$k] = $l; } ?>
<div class="card mb-3"><div class="card-body"><?php
  $this->load->view('rekap/filters', array('f' => $f, 'classes' => $classes, 'teachers' => $teachers, 'subjects' => $subjects, 'semesters' => $semesters, 'years' => $years,
    'action' => site_url('rekap'), 'type_select' => $types, 'type' => $type,
    'extra_buttons' => '<a class="btn btn-outline-secondary" href="' . site_url('report/output?' . http_build_query(array_merge($f, array('type' => $type, 'format' => 'print')))) . '" target="_blank"><i class="bi bi-printer"></i> Versi Cetak</a>'));
?></div></div>
<div class="card"><div class="card-body"><h2 class="h6"><?= e($rtitle) ?> <small class="text-muted">· <?= tanggal_id($f['from'], FALSE) ?> s/d <?= tanggal_id($f['to'], FALSE) ?></small></h2>
<div class="table-responsive"><table class="table table-sm table-hover dt"><thead><tr><?php foreach ($headers as $h): ?><th><?= e($h) ?></th><?php endforeach; ?></tr></thead><tbody>
<?php foreach ($rows as $r): ?><tr><?php foreach ($r as $i => $c): ?><td><?php if ($type === 'siswa' && $i === 1 && isset($r[0])): ?><?= e($c) ?><?php else: ?><?= e($c) ?><?php endif; ?></td><?php endforeach; ?></tr><?php endforeach; ?></tbody></table></div>
<p class="small text-muted mb-0">% Hadir = (Hadir + Terlambat) ÷ total catatan. Siswa yang belum dikonfirmasi guru tidak dihitung alpa. Untuk rekap per siswa per mapel, buka <em>Data Siswa → ikon tabel</em>.</p></div></div>
