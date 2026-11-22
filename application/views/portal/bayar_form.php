<a href="<?= site_url('portal/bayar') ?>" class="btn btn-light btn-sm mb-2"><i class="bi bi-arrow-left"></i> Kembali</a>
<div class="card"><div class="card-body">
  <h1 class="h6"><?= e($inv->title) ?></h1>
  <p class="small text-muted mb-3">Sisa tagihan <?= rupiah($inv->amount - $inv->paid_total) ?><?= $pending ? ' · ' . rupiah($pending) . ' sedang menunggu verifikasi' : '' ?>. Setelah transfer ke rekening sekolah, unggah bukti di sini.</p>
  <?php if ($max < 1): ?><div class="alert alert-info mb-0">Tidak ada sisa yang dapat dikonfirmasi saat ini.</div><?php else: ?>
  <?= form_open_multipart('portal/bayar_kirim/' . $inv->id) ?>
  <div class="mb-2"><label class="form-label small mb-0">Nominal yang ditransfer (Rp)</label><input name="amount" inputmode="numeric" class="form-control" value="<?= (int)$max ?>" required><div class="form-text">Maksimal <?= rupiah($max) ?>. Boleh dicicil jika sekolah mengizinkan.</div></div>
  <div class="mb-2"><label class="form-label small mb-0">Tanggal transfer</label><input type="date" name="paid_date" class="form-control" value="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d') ?>" required></div>
  <div class="mb-2"><label class="form-label small mb-0">Bukti transfer (JPG/PNG/PDF, maks 2 MB)</label><input type="file" name="proof" class="form-control" accept=".jpg,.jpeg,.png,.pdf" required></div>
  <div class="mb-3"><label class="form-label small mb-0">Catatan (opsional)</label><input name="note" class="form-control" maxlength="250"></div>
  <button class="btn btn-primary w-100">Kirim Bukti Pembayaran</button><?= form_close() ?><?php endif; ?>
</div></div>
