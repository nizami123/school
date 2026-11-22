<p class="small text-muted">Pilih kegiatan untuk <strong><?= e($child->name) ?></strong>. Pendaftaran perlu disetujui sekolah.</p>
<?php foreach ($rows as $x): $full = $x->quota && $x->filled >= $x->quota; ?>
<div class="card mb-2"><div class="card-body">
  <div class="d-flex justify-content-between gap-2"><h2 class="h6 mb-1"><?= e($x->name) ?></h2><?= $x->my_status ? status_badge($x->my_status) : '' ?></div>
  <div class="small text-muted mb-2"><?php if ($x->day): ?><i class="bi bi-clock"></i> <?= hari_id($x->day) ?><?= $x->start_time ? ', ' . substr($x->start_time, 0, 5) . '–' . substr($x->end_time, 0, 5) : '' ?> &nbsp;<?php endif; ?>
    <?php if ($x->location): ?><i class="bi bi-geo-alt"></i> <?= e($x->location) ?> &nbsp;<?php endif; ?><?php if ($x->coach): ?><i class="bi bi-person"></i> <?= e($x->coach) ?> &nbsp;<?php endif; ?>
    <i class="bi bi-people"></i> <?= (int)$x->filled ?><?= $x->quota ? '/' . (int)$x->quota : '' ?> anggota</div>
  <?php if ($x->description): ?><div class="small mb-2"><?= nl2br(e($x->description)) ?></div><?php endif; ?>
  <?php if (in_array($x->my_status, array('aktif', 'menunggu'), TRUE)): ?>
    <?= form_open('portal/ekskul_keluar/' . $x->id, array('class' => 'd-inline', 'data-confirm' => 'Batalkan keikutsertaan?')) ?><button class="btn btn-sm btn-outline-danger"><?= $x->my_status === 'aktif' ? 'Keluar dari kegiatan' : 'Batalkan pendaftaran' ?></button><?= form_close() ?>
  <?php else: ?>
    <?= form_open('portal/ekskul_daftar/' . $x->id, array('class' => 'd-inline')) ?><button class="btn btn-sm btn-primary" <?= $full ? 'disabled' : '' ?>><?= $full ? 'Kuota penuh' : 'Daftarkan' ?></button><?= form_close() ?>
  <?php endif; ?>
</div></div>
<?php endforeach; if (!$rows): ?><div class="card"><div class="card-body text-muted">Belum ada kegiatan ekstrakurikuler.</div></div><?php endif; ?>
