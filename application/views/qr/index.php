<div class="card"><div class="card-body">
<form class="row g-2 mb-3" method="get" data-noload>
  <div class="col-md-4"><select name="class_id" class="form-select" onchange="this.form.submit()"><option value="">Semua kelas</option><?php foreach ($classes as $c): ?><option value="<?= $c->id ?>" <?= $c->id == $cid ? 'selected' : '' ?>><?= e($c->name) ?></option><?php endforeach; ?></select></div>
  <div class="col-md-8 d-flex gap-2 flex-wrap">
    <a class="btn btn-primary" href="<?= site_url('qr/cards?scope=all') ?>"><i class="bi bi-printer"></i> Cetak Semua Kartu</a>
    <?php if ($cid): ?><a class="btn btn-outline-primary" href="<?= site_url('qr/cards?scope=class&id=' . $cid) ?>">Cetak Kartu Kelas Ini</a><?php endif; ?>
  </div>
</form>
<p class="small text-muted">Setiap siswa aktif otomatis memiliki 1 QR unik (token acak, tidak memuat NIS). Jika kartu hilang, buat QR baru — QR lama langsung tidak berlaku.</p>
<div class="table-responsive"><table class="table align-middle dt"><thead><tr><th>Foto</th><th>NIS</th><th>Nama</th><th>Kelas</th><th>QR Dibuat</th><th>Riwayat</th><th class="text-end">Aksi</th></tr></thead><tbody>
<?php foreach ($rows as $r): ?><tr><td><img class="avatar" src="<?= student_photo($r->photo) ?>"></td><td><?= e($r->nis) ?></td><td><?= e($r->name) ?></td><td><?= e($r->class_name) ?></td><td><?= $r->qr_at ? date('d/m/Y', strtotime($r->qr_at)) : '-' ?></td>
<td><a href="<?= site_url('qr/history/' . $r->id) ?>"><?= (int)$r->qr_count ?> QR</a></td>
<td class="text-end text-nowrap"><a class="btn btn-sm btn-outline-success" href="<?= site_url('qr/cards?scope=student&id=' . $r->id) ?>"><i class="bi bi-person-vcard"></i> Kartu</a>
<button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#regen" data-id="<?= $r->id ?>" data-name="<?= e($r->name) ?>"><i class="bi bi-arrow-repeat"></i> QR Pengganti</button></td></tr><?php endforeach; ?>
</tbody></table></div></div></div>
<div class="modal fade" id="regen" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content">
<?= form_open('qr/regenerate') ?><div class="modal-header"><h5 class="modal-title">QR Pengganti</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<div class="modal-body"><input type="hidden" name="student_id" id="rid"><p>Buat QR baru untuk <strong id="rname"></strong>? <span class="text-danger">QR lama akan langsung tidak berlaku.</span></p>
<label class="form-label">Alasan</label><select name="reason" class="form-select"><option>Kartu hilang</option><option>Kartu rusak</option><option>Kartu dicuri/disalahgunakan</option></select></div>
<div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button><button class="btn btn-danger">Buat QR Baru</button></div><?= form_close() ?></div></div></div>
<script>document.getElementById('regen').addEventListener('show.bs.modal',function(e){var b=e.relatedTarget;document.getElementById('rid').value=b.dataset.id;document.getElementById('rname').textContent=b.dataset.name;});</script>
