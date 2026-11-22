<div class="card mb-3"><div class="card-body">
  <div class="portal-card-title">Ajukan izin / sakit untuk <?= e($child->name) ?></div>
  <?= form_open_multipart('portal/izin_simpan') ?>
  <div class="row g-2">
    <div class="col-12"><div class="btn-group w-100" role="group">
      <input type="radio" class="btn-check" name="leave_type" id="lt1" value="sakit" checked><label class="btn btn-outline-primary" for="lt1">Sakit</label>
      <input type="radio" class="btn-check" name="leave_type" id="lt2" value="izin"><label class="btn btn-outline-primary" for="lt2">Izin</label></div></div>
    <div class="col-6"><label class="form-label small mb-0">Dari tanggal</label><input type="date" name="date_from" class="form-control" value="<?= date('Y-m-d') ?>" min="<?= date('Y-m-d', strtotime('-14 days')) ?>" required></div>
    <div class="col-6"><label class="form-label small mb-0">Sampai tanggal</label><input type="date" name="date_to" class="form-control" value="<?= date('Y-m-d') ?>" required></div>
    <div class="col-12"><label class="form-label small mb-0">Alasan</label><textarea name="reason" class="form-control" rows="3" maxlength="500" required placeholder="Contoh: demam, perlu istirahat di rumah"></textarea></div>
    <div class="col-12"><label class="form-label small mb-0">Lampiran (surat dokter/keterangan, opsional)</label><input type="file" name="attachment" class="form-control" accept=".jpg,.jpeg,.png,.pdf"></div>
    <div class="col-12"><button class="btn btn-primary w-100"><i class="bi bi-send"></i> Kirim Pengajuan</button></div>
  </div><?= form_close() ?>
  <div class="form-text mt-2">Setelah disetujui sekolah, absensi pada tanggal tersebut otomatis tercatat izin/sakit. Anda tidak akan menerima WhatsApp untuk hal ini; cek statusnya di halaman ini.</div>
</div></div>
<div class="card"><div class="card-body"><div class="portal-card-title">Riwayat pengajuan</div>
<?php foreach ($rows as $r): ?><div class="py-2 border-top">
  <div class="d-flex justify-content-between"><div><?= status_badge($r->leave_type) ?> <strong class="small"><?= tanggal_id($r->date_from, FALSE) ?><?= $r->date_to !== $r->date_from ? ' – ' . tanggal_id($r->date_to, FALSE) : '' ?></strong></div><?= status_badge($r->status) ?></div>
  <div class="small mt-1"><?= nl2br(e($r->reason)) ?></div>
  <?php if ($r->review_note): ?><div class="small text-muted fst-italic">Catatan sekolah: <?= e($r->review_note) ?></div><?php endif; ?>
  <div class="mt-1 d-flex gap-2 align-items-center"><?php if ($r->attachment): ?><a class="small" target="_blank" href="<?= site_url('portal/lampiran/' . $r->id) ?>"><i class="bi bi-paperclip"></i> Lampiran</a><?php endif; ?>
  <?php if ($r->status === 'menunggu'): ?><?= form_open('portal/izin_batal/' . $r->id, array('class' => 'd-inline', 'data-confirm' => 'Batalkan pengajuan ini?')) ?><button class="btn btn-sm btn-outline-danger py-0">Batalkan</button><?= form_close() ?><?php endif; ?></div>
</div><?php endforeach; if (!$rows): ?><p class="text-muted small mb-0">Belum ada pengajuan.</p><?php endif; ?></div></div>
