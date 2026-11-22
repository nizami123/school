<!doctype html><html lang="id"><head><meta charset="utf-8"><title>Kwitansi <?= e($p->receipt_no) ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>body{font-family:system-ui,Arial,sans-serif;max-width:640px;margin:24px auto;padding:0 16px;color:#111}h1{font-size:1.3rem;margin:0}.box{border:2px solid #166534;border-radius:10px;padding:20px}
table{width:100%;border-collapse:collapse;margin:14px 0}td{padding:6px 0;vertical-align:top}td:first-child{width:32%;color:#555}.amt{font-size:1.4rem;font-weight:700;border:1px dashed #166534;padding:8px 12px;display:inline-block;margin-top:6px}
.sign{margin-top:36px;text-align:right}@media print{.np{display:none}}</style></head><body>
<div class="box"><h1>KWITANSI PEMBAYARAN</h1><div style="color:#555"><?= e(setting('school_name', 'Sekolah')) ?></div>
<table>
<tr><td>No. Kwitansi</td><td><?= e($p->receipt_no) ?></td></tr>
<tr><td>Telah diterima dari</td><td>Orang tua/wali <?= e($p->student_name) ?> (<?= e($p->class_name) ?>, NIS <?= e($p->nis) ?>)</td></tr>
<tr><td>Untuk pembayaran</td><td><?= e($p->title) ?></td></tr>
<tr><td>Tanggal</td><td><?= tanggal_id($p->paid_at, FALSE) ?> · cara: <?= e($p->method) ?></td></tr>
<tr><td>Jumlah</td><td><span class="amt"><?= rupiah($p->amount) ?></span></td></tr></table>
<div class="sign">Petugas sekolah<br><br><br>( ______________________ )</div></div>
<p class="np"><button onclick="window.print()">Cetak</button></p></body></html>
