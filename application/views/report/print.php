<?php $logo = setting('school_logo', ''); $logo_data = isset($logo_data) ? $logo_data : ''; ?>
<!doctype html><html lang="id"><head><meta charset="utf-8"><title><?= e($title) ?></title>
<style>body{font-family:Arial,Helvetica,sans-serif;font-size:11px;color:#111;margin:18px}
.kop{display:table;width:100%;border-bottom:3px double #14532d;padding-bottom:8px;margin-bottom:10px}.kop .l{display:table-cell;width:70px;vertical-align:middle}.kop .r{display:table-cell;vertical-align:middle;text-align:center}
.kop img{height:60px}.kop h1{font-size:16px;margin:0;color:#14532d}.kop p{margin:1px 0;font-size:10px}
h2{font-size:14px;text-align:center;margin:8px 0 2px}.per{text-align:center;margin-bottom:8px}
table{width:100%;border-collapse:collapse}th,td{border:1px solid #666;padding:3px 5px;text-align:left}th{background:#dcfce7}thead{display:table-header-group}tr{page-break-inside:avoid}
.ttd{margin-top:28px;width:100%}.ttd td{border:0;text-align:center;width:50%}
@media print{.np{display:none}}</style></head><body>
<?php if (empty($pdf)): ?><div class="np" style="margin-bottom:10px"><button onclick="window.print()">Cetak / Simpan PDF</button></div><?php endif; ?>
<div class="kop"><div class="l"><?php if ($logo && empty($pdf)): ?><img src="<?= upload_url('logo/' . $logo) ?>" alt=""><?php elseif ($logo_data): ?><img src="<?= $logo_data ?>" alt=""><?php endif; ?></div>
<div class="r"><h1><?= e(setting('school_name', 'Sekolah')) ?></h1><p><?= e(setting('school_address', '')) ?></p><p><?= e(setting('school_phone', '')) ?> <?= setting('school_email', '') ? '· ' . e(setting('school_email')) : '' ?></p></div></div>
<h2><?= e(mb_strtoupper($title)) ?></h2><div class="per">Periode: <?= tanggal_id($f['from'], FALSE) ?> s/d <?= tanggal_id($f['to'], FALSE) ?></div>
<table><thead><tr><th>No</th><?php foreach ($headers as $h): ?><th><?= e($h) ?></th><?php endforeach; ?></tr></thead><tbody>
<?php foreach ($rows as $i => $r): ?><tr><td><?= $i + 1 ?></td><?php foreach ($r as $c): ?><td><?= e($c) ?></td><?php endforeach; ?></tr><?php endforeach; if (!$rows): ?><tr><td colspan="<?= count($headers) + 1 ?>">Tidak ada data.</td></tr><?php endif; ?></tbody></table>
<table class="ttd"><tr><td>Mengetahui,<br>Kepala Sekolah<br><br><br><br>(................................)</td><td><?= tanggal_id(date('Y-m-d'), FALSE) ?><br>Admin / Wali Kelas<br><br><br><br>(................................)</td></tr></table>
<p style="font-size:9px;color:#555">Dicetak <?= date('d/m/Y H:i') ?> WIB</p></body></html>
