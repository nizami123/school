<?php $bank = setting('pay_bank'); $acc = setting('pay_account'); $holder = setting('pay_holder'); $note = setting('pay_note'); ?>
<?php if ($bank || $acc): ?><div class="card mb-3 border-success"><div class="card-body py-3"><div class="portal-card-title">Transfer ke rekening sekolah</div>
  <div class="fs-5 fw-bold"><?= e($bank) ?> <span class="user-select-all"><?= e($acc) ?></span></div><div class="small">a.n. <?= e($holder) ?></div>
  <?php if ($note): ?><div class="small text-muted mt-1"><?= nl2br(e($note)) ?></div><?php endif; ?></div></div><?php endif; ?>

<div class="card mb-3"><div class="card-body"><div class="portal-card-title">Tagihan <?= e($child->name) ?></div>
<?php foreach ($inv as $i): $late = $i->due_date && $i->due_date < date('Y-m-d') && in_array($i->status, array('belum', 'sebagian'), TRUE); $sisa = $i->amount - $i->paid_total; ?>
  <div class="py-2 border-top"><div class="d-flex justify-content-between gap-2"><div><strong><?= e($i->title) ?></strong><br>
    <small class="<?= $late ? 'text-danger fw-semibold' : 'text-muted' ?>"><?= $i->due_date ? 'Jatuh tempo ' . tanggal_id($i->due_date, FALSE) . ($late ? ' (lewat)' : '') : 'Tanpa jatuh tempo' ?></small></div>
    <div class="text-end"><div class="fw-bold"><?= rupiah($i->status === 'lunas' ? $i->amount : $sisa) ?></div><?= status_badge($i->status) ?></div></div>
    <?php if ($i->pending_amt > 0): ?><div class="small text-info mt-1"><i class="bi bi-hourglass-split"></i> <?= rupiah($i->pending_amt) ?> menunggu verifikasi sekolah</div><?php endif; ?>
    <?php if (in_array($i->status, array('belum', 'sebagian'), TRUE) && $sisa - $i->pending_amt > 0): ?><a href="<?= site_url('portal/bayar_form/' . $i->id) ?>" class="btn btn-sm btn-primary mt-2"><i class="bi bi-upload"></i> Konfirmasi Pembayaran</a><?php endif; ?></div>
<?php endforeach; if (!$inv): ?><p class="text-muted small mb-0">Tidak ada tagihan.</p><?php endif; ?></div></div>

<?php if ($infaq): ?><div class="card mb-3"><div class="card-body"><div class="portal-card-title">Infaq / Sumbangan sukarela</div>
  <?= form_open_multipart('portal/infaq_kirim') ?><div class="row g-2">
    <div class="col-12"><select name="fee_type_id" class="form-select"><?php foreach ($infaq as $t): ?><option value="<?= $t->id ?>"><?= e($t->name) ?></option><?php endforeach; ?></select></div>
    <div class="col-12"><input name="amount" inputmode="numeric" class="form-control" placeholder="Nominal (Rp), minimal 1.000" required></div>
    <div class="col-12"><input name="note" class="form-control" maxlength="250" placeholder="Catatan (opsional)"></div>
    <div class="col-12"><label class="form-label small mb-0">Bukti transfer (JPG/PNG/PDF, maks 2 MB)</label><input type="file" name="proof" class="form-control" accept=".jpg,.jpeg,.png,.pdf" required></div>
    <div class="col-12"><button class="btn btn-outline-success w-100">Kirim Bukti Infaq</button></div></div><?= form_close() ?></div></div><?php endif; ?>

<div class="card"><div class="card-body"><div class="portal-card-title">Riwayat pembayaran</div>
<?php foreach ($pays as $p): ?><div class="py-2 border-top d-flex justify-content-between align-items-center gap-2"><div><strong class="small"><?= e($p->title) ?></strong><br><small class="text-muted"><?= date('d/m/Y', strtotime($p->paid_at)) ?> · <?= rupiah($p->amount) ?></small><?= $p->status === 'ditolak' && $p->note ? '<br><small class="text-danger">' . e($p->note) . '</small>' : '' ?></div>
  <div class="text-end"><?= status_badge($p->status) ?><?php if ($p->status === 'terverifikasi'): ?><br><a class="small" target="_blank" href="<?= site_url('portal/kwitansi/' . $p->id) ?>">Kwitansi</a><?php endif; ?></div></div>
<?php endforeach; if (!$pays): ?><p class="text-muted small mb-0">Belum ada pembayaran.</p><?php endif; ?></div></div>
