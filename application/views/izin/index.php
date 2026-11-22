<ul class="nav nav-pills mb-3">
<?php foreach (array('menunggu' => 'Menunggu', 'disetujui' => 'Disetujui', 'ditolak' => 'Ditolak', 'dibatalkan' => 'Dibatalkan', 'semua' => 'Semua') as $k => $l): ?>
  <li class="nav-item"><a class="nav-link <?= $st === $k ? 'active' : '' ?>" href="<?= site_url('izin?status=' . $k) ?>"><?= $l ?><?= isset($counts[$k]) ? ' <span class="badge bg-light text-dark">' . $counts[$k] . '</span>' : '' ?></a></li>
<?php endforeach; ?></ul>
<div class="card"><div class="card-body"><div class="table-responsive"><table class="table table-hover align-middle">
<thead><tr><th>Siswa</th><th>Jenis</th><th>Tanggal</th><th>Alasan</th><th>Status</th><th class="text-end">Aksi</th></tr></thead><tbody>
<?php foreach ($rows as $r): ?><tr>
  <td><strong><?= e($r->student_name) ?></strong><br><small class="text-muted"><?= e($r->class_name) ?> · diajukan <?= e($r->parent_name) ?>, <?= date('d/m H:i', strtotime($r->created_at)) ?></small></td>
  <td><?= status_badge($r->leave_type) ?></td>
  <td class="text-nowrap small"><?= tanggal_id($r->date_from, FALSE) ?><?= $r->date_to !== $r->date_from ? '<br>s.d. ' . tanggal_id($r->date_to, FALSE) : '' ?></td>
  <td class="small" style="max-width:320px"><?= nl2br(e($r->reason)) ?><?php if ($r->attachment): ?><br><a href="<?= site_url('izin/lampiran/' . $r->id) ?>" target="_blank"><i class="bi bi-paperclip"></i> Lampiran</a><?php endif; ?>
    <?php if ($r->review_note): ?><br><em class="text-muted">Catatan: <?= e($r->review_note) ?></em><?php endif; ?></td>
  <td><?= status_badge($r->status) ?></td>
  <td class="text-end">
  <?php if ($r->status === 'menunggu'): ?>
    <?= form_open('izin/review/' . $r->id, array('class' => 'd-flex gap-1 justify-content-end flex-wrap')) ?>
      <input name="note" class="form-control form-control-sm" style="max-width:170px" placeholder="Catatan (opsional)" maxlength="250">
      <button name="action" value="setuju" class="btn btn-sm btn-success">Setujui</button><button name="action" value="tolak" class="btn btn-sm btn-outline-danger">Tolak</button>
    <?= form_close() ?>
  <?php endif; ?></td></tr><?php endforeach; if (!$rows): ?><tr><td colspan="6" class="text-muted">Tidak ada pengajuan.</td></tr><?php endif; ?></tbody></table></div>
<div class="form-text">Saat disetujui, absensi siswa pada rentang tanggal tersebut otomatis tercatat Izin/Sakit (tidak menimpa siswa yang sudah scan hadir). Tidak ada notifikasi WhatsApp — WhatsApp hanya untuk absensi.</div></div></div>
