<?php $this->load->view('iuran/_nav', array('pending' => $pending)); ?>
<div class="card" style="max-width:640px"><div class="card-body">
<?php if (!$fees): ?><div class="alert alert-warning mb-0">Belum ada jenis iuran aktif (selain jenis "Bebas"). <a href="<?= site_url('master/fee_types/form') ?>">Tambah jenis iuran</a> dulu.</div><?php else: ?>
<?= form_open('iuran/generate') ?>
<div class="mb-3"><label class="form-label">Jenis Iuran</label><select name="fee_type_id" id="ft" class="form-select" required>
  <?php foreach ($fees as $t): ?><option value="<?= $t->id ?>" data-freq="<?= e($t->frequency) ?>" data-amt="<?= (int)$t->amount ?>"><?= e($t->name) ?> — <?= rupiah($t->amount) ?> (<?= e($t->frequency) ?>)</option><?php endforeach; ?></select></div>
<div class="mb-3" id="pm"><label class="form-label">Bulan</label><input type="month" name="period" id="pmi" class="form-control" value="<?= date('Y-m') ?>"></div>
<div class="mb-3 d-none" id="ps"><label class="form-label">Label Periode</label><input type="text" id="psi" maxlength="30" class="form-control" placeholder="mis. Semester Ganjil 2026"></div>
<div class="row g-3 mb-3"><div class="col-md-6"><label class="form-label">Kelas</label><select name="class_id" class="form-select"><option value="0">Semua siswa aktif</option><?php foreach ($classes as $c): ?><option value="<?= $c->id ?>"><?= e($c->name) ?></option><?php endforeach; ?></select></div>
  <div class="col-md-6"><label class="form-label">Jatuh Tempo (opsional)</label><input type="date" name="due_date" class="form-control"></div></div>
<div class="mb-3"><label class="form-label">Nominal (Rp)</label><input type="number" min="0" name="amount" id="amt" class="form-control"><div class="form-text">Kosongkan untuk memakai nominal bawaan jenis iuran.</div></div>
<button class="btn btn-primary"><i class="bi bi-receipt"></i> Buat Tagihan</button>
<div class="form-text mt-2">Tagihan yang sudah ada untuk siswa + jenis + periode yang sama dilewati (tidak dobel).</div>
<?= form_close() ?>
<script>
(function(){var s=document.getElementById('ft'),pm=document.getElementById('pm'),ps=document.getElementById('ps'),pmi=document.getElementById('pmi'),psi=document.getElementById('psi'),a=document.getElementById('amt');
function u(){var o=s.options[s.selectedIndex],m=o.dataset.freq==='bulanan';pm.classList.toggle('d-none',!m);ps.classList.toggle('d-none',m);
 if(m){pmi.name='period';psi.removeAttribute('name');}else{psi.name='period';pmi.removeAttribute('name');}a.placeholder=o.dataset.amt;}
s.addEventListener('change',u);u();})();
</script>
<?php endif; ?></div></div>
