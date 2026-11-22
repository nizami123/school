<div class="list-group shadow-sm">
<?php foreach (array(array('portal/jadwal', 'bi-calendar3', 'Jadwal Pelajaran & Kalender Akademik'), array('portal/pengumuman', 'bi-megaphone', 'Pengumuman Sekolah'), array('portal/ekskul', 'bi-trophy', 'Ekstrakurikuler'), array('portal/profil', 'bi-person-gear', 'Profil & Ganti Password')) as $m): ?>
  <a class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-3" href="<?= site_url($m[0]) ?>"><i class="bi <?= $m[1] ?> fs-4 text-success"></i><span><?= e($m[2]) ?></span><i class="bi bi-chevron-right ms-auto text-muted"></i></a><?php endforeach; ?>
  <a class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-3 text-danger" href="<?= site_url('logout') ?>"><i class="bi bi-box-arrow-right fs-4"></i><span>Keluar</span></a>
</div>
<p class="text-center small text-muted mt-3 mb-0">Notifikasi WhatsApp hanya dikirim sekolah saat absensi anak.</p>
