<div class="d-flex gap-2 overflow-auto pb-2 mb-2">
  <?php foreach (array('' => 'Semua', 'libur' => 'Libur', 'jadwal' => 'Jadwal', 'kegiatan' => 'Kegiatan', 'ujian' => 'Ujian', 'rapat' => 'Rapat', 'umum' => 'Umum') as $k => $l): ?>
  <a class="btn btn-sm <?= $cat === $k ? 'btn-primary' : 'btn-outline-secondary' ?> text-nowrap" href="<?= site_url('portal/pengumuman' . ($k ? '?kategori=' . $k : '')) ?>"><?= $l ?></a><?php endforeach; ?></div>
<?php foreach ($rows as $a): ?>
<a class="card mb-2 text-decoration-none text-body" href="<?= site_url('portal/pengumuman/' . $a->id) ?>"><div class="card-body py-3">
  <div class="d-flex justify-content-between"><div><?= kategori_badge($a->category) ?> <?= $a->is_pinned ? '<i class="bi bi-pin-angle-fill text-danger"></i>' : '' ?><?= $a->need_rsvp ? ' <span class="badge bg-light text-dark border">Perlu konfirmasi</span>' : '' ?></div><small class="text-muted"><?= tanggal_id($a->publish_date, FALSE) ?></small></div>
  <div class="fw-semibold mt-1"><?= e($a->title) ?></div>
  <?php if ($a->event_date): ?><small class="text-muted"><i class="bi bi-calendar-event"></i> <?= tanggal_id($a->event_date) ?><?= $a->event_time ? ', ' . e($a->event_time) : '' ?></small><?php endif; ?>
</div></a>
<?php endforeach; if (!$rows): ?><div class="card"><div class="card-body text-muted">Belum ada pengumuman.</div></div><?php endif; ?>
