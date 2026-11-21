<div class="card" style="max-width:760px"><div class="card-body">
<p class="text-muted">Mengisi data contoh untuk uji coba: 6 guru, 3 kelas (<code>DM VII A</code>, <code>DM VII B</code>, <code>DM VIII A</code>), 30 siswa beserta orang tua dan QR, jadwal Senin–Jumat, dan (opsional) riwayat absensi 14 hari terakhir agar dashboard &amp; laporan terisi. Semua data bertanda <strong>DM</strong> dan bisa dihapus bersih tanpa menyentuh data asli.</p>
<?php if (!$sem): ?><div class="alert alert-danger">Belum ada semester aktif. Aktifkan semester di menu Semester.</div><?php endif; ?>
<?php if ($has): ?>
  <div class="alert alert-success">Data dummy sudah ada:
    <?php foreach ($counts as $k => $v): ?><span class="badge bg-secondary me-1"><?= e($k) ?>: <?= (int)$v ?></span><?php endforeach; ?></div>
  <p class="small">Login guru dummy: <code>dummy.andi</code>, <code>dummy.siti</code>, <code>dummy.budi</code>, <code>dummy.rina</code>, <code>dummy.agus</code>, <code>dummy.dewi</code> (password sesuai yang Anda isi saat membuat). NIS siswa: <code>DM0001</code>–<code>DM0030</code>.</p>
  <?= form_open('seed/clear', array('data-confirm' => 'Hapus semua data dummy beserta riwayat absensinya?')) ?><button class="btn btn-outline-danger"><i class="bi bi-trash"></i> Hapus Data Dummy</button><?= form_close() ?>
<?php elseif ($sem): ?>
  <?= form_open('seed/run', array('class' => 'row g-3')) ?>
    <div class="col-md-6"><label class="form-label">Password akun guru dummy</label><input name="password" class="form-control" value="guru12345" minlength="6"></div>
    <div class="col-md-6"><label class="form-label">Nomor WhatsApp uji (opsional)</label><input name="test_wa" class="form-control" placeholder="mis. 0812xxxxxxx" inputmode="tel"><div class="form-text">Diisi ke <strong>semua</strong> orang tua dummy agar notifikasi masuk ke HP Anda. Kosong = nomor palsu (+62812000000x) sehingga pesan WA gagal terkirim.</div></div>
    <div class="col-12"><div class="form-check"><input class="form-check-input" type="checkbox" name="history" value="1" id="h" checked><label class="form-check-label" for="h">Sertakan riwayat absensi 14 hari terakhir</label></div></div>
    <div class="col-12"><button class="btn btn-primary"><i class="bi bi-database-add"></i> Buat Data Dummy</button></div>
  <?= form_close() ?>
<?php endif; ?>
<hr><p class="small text-muted mb-0">Fitur ini untuk masa uji. Hapus data dummy dan file <code>application/controllers/Seed.php</code> sebelum dipakai sungguhan.</p>
</div></div>
