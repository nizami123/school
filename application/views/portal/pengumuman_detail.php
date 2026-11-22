<a href="<?= site_url('portal/pengumuman') ?>" class="btn btn-light btn-sm mb-2"><i class="bi bi-arrow-left"></i> Kembali</a>
<div class="card mb-3"><div class="card-body">
  <?= kategori_badge($a->category) ?> <small class="text-muted ms-1"><?= tanggal_id($a->publish_date) ?></small>
  <h1 class="h5 mt-2"><?= e($a->title) ?></h1>
  <?php if ($a->event_date || $a->location): ?><div class="alert alert-light border small py-2">
    <?php if ($a->event_date): ?><div><i class="bi bi-calendar-event"></i> <?= tanggal_id($a->event_date) ?><?= $a->event_time ? ' · ' . e($a->event_time) : '' ?></div><?php endif; ?>
    <?php if ($a->location): ?><div><i class="bi bi-geo-alt"></i> <?= e($a->location) ?></div><?php endif; ?></div><?php endif; ?>
  <div><?= nl2br(e($a->body)) ?></div>
</div></div>
<?php if ($a->need_rsvp): ?>
<div class="card"><div class="card-body"><div class="portal-card-title">Konfirmasi kehadiran</div>
  <?php if ($rsvp): ?><p class="mb-2">Jawaban Anda: <?= $rsvp->response === 'hadir' ? '<span class="badge bg-success">Akan hadir</span>' : '<span class="badge bg-secondary">Tidak hadir</span>' ?><?= $rsvp->note ? ' <small class="text-muted">— ' . e($rsvp->note) . '</small>' : '' ?><br><small class="text-muted">Diperbarui <?= date('d/m/Y H:i', strtotime($rsvp->updated_at)) ?>. Anda dapat mengubahnya.</small></p><?php endif; ?>
  <?= form_open('portal/rsvp/' . $a->id) ?>
    <input name="note" class="form-control mb-2" maxlength="250" placeholder="Catatan (opsional)" value="<?= $rsvp ? e($rsvp->note) : '' ?>">
    <div class="d-flex gap-2"><button name="response" value="hadir" class="btn btn-success flex-fill">Saya akan hadir</button><button name="response" value="tidak" class="btn btn-outline-secondary flex-fill">Tidak bisa hadir</button></div>
  <?= form_close() ?></div></div>
<?php endif; ?>
