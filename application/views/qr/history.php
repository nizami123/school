<div class="card"><div class="card-body"><a href="<?= site_url('qr') ?>" class="btn btn-sm btn-light mb-3 no-print">&laquo; Kembali</a>
<h2 class="h6"><?= e($st->name) ?> (<?= e($st->nis) ?>)</h2>
<table class="table table-sm"><thead><tr><th>Dibuat</th><th>Oleh</th><th>Alasan</th><th>Status</th><th>Dinonaktifkan</th></tr></thead><tbody>
<?php foreach ($rows as $r): ?><tr><td><?= $r->created_at ?></td><td><?= e($r->by_name) ?></td><td><?= e($r->reason) ?></td><td><?= $r->is_active ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-secondary">Tidak berlaku</span>' ?></td><td><?= $r->deactivated_at ?: '-' ?></td></tr><?php endforeach; ?></tbody></table></div></div>
