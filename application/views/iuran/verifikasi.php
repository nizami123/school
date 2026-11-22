<?php $this->load->view('iuran/_nav', array('pending' => $pending)); ?>
<div class="card"><div class="card-body"><div class="table-responsive"><table class="table align-middle">
<thead><tr><th>Siswa</th><th>Tagihan</th><th>Nominal Dikirim</th><th>Dikirim</th><th>Bukti</th><th class="text-end">Aksi</th></tr></thead><tbody>
<?php foreach ($rows as $r): ?><tr>
  <td><?= e($r->student_name) ?><br><small class="text-muted"><?= e($r->class_name) ?></small></td>
  <td class="small"><?= e($r->title) ?><br>Sisa: <?= rupiah($r->invoice_amount - $r->paid_total) ?></td>
  <td class="fw-semibold"><?= rupiah($r->amount) ?><br><small class="text-muted fw-normal"><?= e($r->note) ?></small></td>
  <td class="small"><?= date('d/m/Y H:i', strtotime($r->paid_at)) ?></td>
  <td><?php if ($r->proof): ?><a href="<?= site_url('iuran/bukti/' . $r->id) ?>" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="bi bi-image"></i> Lihat</a><?php else: ?><span class="text-muted small">Tanpa bukti</span><?php endif; ?></td>
  <td class="text-end"><?= form_open('iuran/proses/' . $r->id, array('class' => 'd-flex gap-1 justify-content-end flex-wrap')) ?>
    <input name="note" class="form-control form-control-sm" style="max-width:150px" placeholder="Catatan" maxlength="250">
    <button name="action" value="terima" class="btn btn-sm btn-success">Terima</button><button name="action" value="tolak" class="btn btn-sm btn-outline-danger">Tolak</button><?= form_close() ?></td></tr>
<?php endforeach; if (!$rows): ?><tr><td colspan="6" class="text-muted">Tidak ada pembayaran yang menunggu verifikasi.</td></tr><?php endif; ?></tbody></table></div>
<div class="form-text">Cocokkan nominal &amp; bukti dengan mutasi rekening sekolah sebelum menerima. Aplikasi ini tidak terhubung ke bank/payment gateway.</div></div></div>
