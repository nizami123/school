<?php $this->load->view('iuran/_nav', array('pending' => 0)); ?>
<div class="row g-3">
<div class="col-lg-5"><div class="card"><div class="card-body">
  <h2 class="h6"><?= e($inv->title) ?></h2>
  <dl class="row mb-0 small">
    <dt class="col-5">Siswa</dt><dd class="col-7"><?= e($inv->student_name) ?> (<?= e($inv->class_name) ?>)</dd>
    <dt class="col-5">Nominal</dt><dd class="col-7"><?= rupiah($inv->amount) ?></dd>
    <dt class="col-5">Terbayar</dt><dd class="col-7"><?= rupiah($inv->paid_total) ?></dd>
    <dt class="col-5">Sisa</dt><dd class="col-7 fw-bold"><?= rupiah($remaining) ?></dd>
    <dt class="col-5">Jatuh tempo</dt><dd class="col-7"><?= $inv->due_date ? tanggal_id($inv->due_date, FALSE) : '-' ?></dd>
    <dt class="col-5">Status</dt><dd class="col-7"><?= status_badge($inv->status) ?></dd>
  </dl>
  <?php if ($inv->status !== 'batal' && $remaining > 0): ?><hr>
  <?= form_open('iuran/bayar/' . $inv->id) ?>
    <h3 class="h6">Catat Pembayaran</h3>
    <div class="row g-2"><div class="col-6"><input type="number" name="amount" min="1" max="<?= (int)$remaining ?>" class="form-control" value="<?= (int)$remaining ?>" required></div>
    <div class="col-6"><select name="method" class="form-select"><option value="tunai">Tunai</option><option value="transfer">Transfer</option><option value="lainnya">Lainnya</option></select></div>
    <div class="col-12"><input name="note" class="form-control" placeholder="Catatan (opsional)" maxlength="250"></div>
    <div class="col-12"><button class="btn btn-success">Simpan Pembayaran</button></div></div>
  <?= form_close() ?><?php endif; ?>
  <?php if ($inv->status === 'belum'): ?><hr><?= form_open('iuran/batal/' . $inv->id, array('data-confirm' => 'Batalkan tagihan ini?')) ?><button class="btn btn-sm btn-outline-danger">Batalkan Tagihan</button><?= form_close() ?><?php endif; ?>
</div></div></div>
<div class="col-lg-7"><div class="card"><div class="card-body"><h2 class="h6">Riwayat Pembayaran</h2>
<table class="table table-sm align-middle"><thead><tr><th>Tanggal</th><th>Nominal</th><th>Cara</th><th>Status</th><th></th></tr></thead><tbody>
<?php foreach ($pays as $p): ?><tr><td class="small"><?= date('d/m/Y H:i', strtotime($p->paid_at)) ?></td><td><?= rupiah($p->amount) ?></td><td><?= e(ucfirst($p->method)) ?><?= $p->submitted_by_parent ? ' <small class="text-muted">(ortu)</small>' : '' ?></td><td><?= status_badge($p->status) ?></td>
  <td class="text-end text-nowrap"><?php if ($p->proof): ?><a href="<?= site_url('iuran/bukti/' . $p->id) ?>" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="bi bi-image"></i></a> <?php endif; ?><?php if ($p->status === 'terverifikasi'): ?><a href="<?= site_url('iuran/kwitansi/' . $p->id) ?>" target="_blank" class="btn btn-sm btn-outline-primary"><i class="bi bi-printer"></i> Kwitansi</a><?php elseif ($p->status === 'menunggu'): ?><a href="<?= site_url('iuran/verifikasi') ?>" class="btn btn-sm btn-warning">Verifikasi</a><?php endif; ?></td></tr>
<?php endforeach; if (!$pays): ?><tr><td colspan="5" class="text-muted">Belum ada pembayaran.</td></tr><?php endif; ?></tbody></table></div></div></div>
</div>
