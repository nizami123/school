<?php $school = setting('school_name', 'Sekolah'); $logo = setting('school_logo', ''); $addr = setting('school_address', ''); ?>
<div class="card mb-3 no-print"><div class="card-body d-flex flex-wrap gap-2 align-items-center">
  <button class="btn btn-primary" onclick="window.print()"><i class="bi bi-printer"></i> Cetak / Simpan PDF</button>
  <button class="btn btn-outline-primary" id="dlAll"><i class="bi bi-image"></i> Unduh Gambar Kartu (PNG)</button>
  <select class="form-select w-auto" onchange="if(this.value)location.href=this.value"><option value="">Cetak per kelas…</option>
  <?php foreach ($classes as $c): ?><option value="<?= site_url('qr/cards?scope=class&id=' . $c->id) ?>"><?= e($c->name) ?></option><?php endforeach; ?></select>
  <a class="btn btn-light" href="<?= site_url('qr/cards?scope=all') ?>">Semua siswa</a>
  <span class="text-muted small"><?= count($rows) ?> kartu · ukuran CR80 (85,6×54 mm), 2 kolom per halaman A4. Di dialog cetak pilih "Simpan sebagai PDF" untuk PDF; skala 100%.</span>
</div></div>
<div class="cards-sheet">
<?php foreach ($rows as $r): ?>
<div class="idcard" data-nis="<?= e($r->nis) ?>">
  <div class="hd"><?php if ($logo): ?><img src="<?= upload_url('logo/' . $logo) ?>" alt=""><?php endif; ?><div><b><?= e($school) ?></b><small>KARTU PELAJAR<?= $addr ? ' · ' . e(mb_substr($addr, 0, 40)) : '' ?></small></div></div>
  <div class="bd">
    <img class="ph" src="<?= student_photo($r->photo) ?>" alt="">
    <div class="inf"><div class="nm"><?= e($r->name) ?></div><div>NIS : <?= e($r->nis) ?></div><div>NISN : <?= e($r->nisn ?: '-') ?></div><div>Kelas : <?= e($r->class_name) ?></div></div>
    <div class="qr"><div class="qrbox" data-token="<?= e($r->token) ?>"></div></div>
  </div>
  <div class="ft"><?= e(setting('card_note', '')) ?></div>
</div>
<?php endforeach; ?>
</div>
<?php if (!$rows): ?><div class="alert alert-light border">Tidak ada siswa untuk dicetak.</div><?php endif; ?>
<?php $scripts = '<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script><script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script><script src="' . base_url('assets/js/cards.js') . '"></script>'; $this->load->vars(array('scripts' => $scripts)); ?>
