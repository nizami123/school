<?php $s = function ($k, $d = '') { return e(setting($k, $d)); }; $chk = function ($k, $d = '0') { return setting($k, $d) === '1' ? 'checked' : ''; };
$eff = explode(',', setting('effective_days', '1,2,3,4,5')); ?>
<?= form_open_multipart('settings/save') ?>
<ul class="nav nav-tabs mb-3"><li class="nav-item"><button type="button" class="nav-link active" data-bs-toggle="tab" data-bs-target="#t1">Sekolah</button></li><li class="nav-item"><button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#t2">Jam &amp; Absensi</button></li><li class="nav-item"><button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#t3">WhatsApp</button></li><li class="nav-item"><button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#t4">Template Pesan</button></li></ul>
<div class="tab-content card card-body" style="max-width:900px">
<div class="tab-pane fade show active" id="t1"><div class="row g-3">
  <div class="col-12"><label class="form-label">Nama Sekolah</label><input name="school_name" class="form-control" value="<?= $s('school_name') ?>" required></div>
  <div class="col-12"><label class="form-label">Alamat</label><textarea name="school_address" class="form-control" rows="2"><?= $s('school_address') ?></textarea></div>
  <div class="col-md-6"><label class="form-label">Telepon</label><input name="school_phone" class="form-control" value="<?= $s('school_phone') ?>"></div>
  <div class="col-md-6"><label class="form-label">Email</label><input name="school_email" type="email" class="form-control" value="<?= $s('school_email') ?>"></div>
  <div class="col-md-6"><label class="form-label">Logo (JPG/PNG, maks 1 MB)</label><input type="file" name="logo" class="form-control" accept="image/png,image/jpeg"><?php if (setting('school_logo')): ?><img src="<?= upload_url('logo/' . setting('school_logo')) ?>" style="height:48px" class="mt-2"><?php endif; ?></div>
  <div class="col-12"><label class="form-label">Teks kaki kartu pelajar</label><input name="card_note" class="form-control" value="<?= $s('card_note') ?>" maxlength="120"></div>
</div></div>
<div class="tab-pane fade" id="t2"><div class="row g-3">
  <div class="col-12"><label class="form-label d-block">Hari Efektif Sekolah</label><?php foreach (array(1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu') as $k => $l): ?><div class="form-check form-check-inline"><input class="form-check-input" type="checkbox" name="effective_days[]" value="<?= $k ?>" id="d<?= $k ?>" <?= in_array((string)$k, $eff, TRUE) ? 'checked' : '' ?>><label class="form-check-label" for="d<?= $k ?>"><?= $l ?></label></div><?php endforeach; ?></div>
  <div class="col-md-4"><label class="form-label">Jam Masuk</label><input type="time" name="start_time" class="form-control" value="<?= $s('start_time', '07:00') ?>"></div>
  <div class="col-md-4"><label class="form-label">Istirahat Mulai</label><input type="time" name="break_start" class="form-control" value="<?= $s('break_start') ?>"></div>
  <div class="col-md-4"><label class="form-label">Istirahat Selesai</label><input type="time" name="break_end" class="form-control" value="<?= $s('break_end') ?>"></div>
  <div class="col-md-6"><label class="form-label">Toleransi Keterlambatan (menit setelah jam mulai pelajaran)</label><input type="number" min="0" max="120" name="late_minutes" class="form-control" value="<?= $s('late_minutes', '15') ?>"></div>
  <div class="col-12 small text-muted">Jam istirahat dipakai untuk mencegah jadwal bertabrakan dengan istirahat. Toleransi berlaku untuk sesi yang dibuka setelah disimpan.</div>
</div></div>
<div class="tab-pane fade" id="t3"><div class="row g-3">
  <div class="col-12"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="wa_enabled" value="1" id="wae" <?= $chk('wa_enabled') ?>><label class="form-check-label" for="wae">Aktifkan notifikasi WhatsApp ke orang tua (pesan masuk antrean)</label></div>
  <div class="form-text">Pengiriman memakai <strong>Fonnte</strong> (api.fonnte.com). Nomor tujuan otomatis diambil dari <strong>Nomor WhatsApp orang tua</strong> di Data Siswa / Data Orang Tua.</div></div>
  <div class="col-md-8"><label class="form-label">Token Fonnte <?= $has_token ? '<span class="badge bg-success">tersimpan terenkripsi</span>' : '' ?></label><input type="password" name="wa_token" class="form-control" autocomplete="off" placeholder="<?= $has_token ? 'kosongkan jika tidak diganti' : 'tempel token perangkat dari dashboard Fonnte' ?>"><div class="form-text">Token ada di dashboard Fonnte &rarr; Device. Perangkat harus berstatus <em>Connected</em>.</div></div>
  <div class="col-md-4"><label class="form-label">Jeda antar pesan (detik)</label><input name="wa_delay" class="form-control" value="<?= $s('wa_delay', '2') ?>" placeholder="2 atau 2-5"><div class="form-text">Jeda acak mis. <code>2-5</code> mengurangi risiko nomor diblokir.</div></div>
  <div class="col-md-6"><label class="form-label">Kapan pesan hadir dikirim</label><select name="wa_policy" class="form-select"><option value="each" <?= setting('wa_policy', 'each') === 'each' ? 'selected' : '' ?>>Setiap mata pelajaran (bawaan)</option><option value="first" <?= setting('wa_policy') === 'first' ? 'selected' : '' ?>>Hanya absensi pertama setiap hari</option><option value="daily" <?= setting('wa_policy') === 'daily' ? 'selected' : '' ?>>Rekap akhir hari saja</option></select></div>
  <div class="col-12"><label class="form-label d-block">Jenis notifikasi aktif</label>
    <?php foreach (array('wa_notify_hadir' => 'Hadir', 'wa_notify_terlambat' => 'Terlambat', 'wa_notify_absent' => 'Tidak hadir / belum scan', 'wa_notify_izin' => 'Izin', 'wa_notify_sakit' => 'Sakit', 'wa_notify_daily' => 'Rekap harian') as $k => $l): ?><div class="form-check form-check-inline"><input class="form-check-input" type="checkbox" name="<?= $k ?>" value="1" id="<?= $k ?>" <?= $chk($k, $k === 'wa_notify_daily' ? '0' : '1') ?>><label class="form-check-label" for="<?= $k ?>"><?= $l ?></label></div><?php endforeach; ?></div>
  <div class="col-12"><div class="alert alert-light border small mb-0"><strong>Cron cPanel (antrean &amp; rekap):</strong><br><code>* * * * * php <?= e(FCPATH) ?>index.php cron wa</code><br><code>0 16 * * 1-6 php <?= e(FCPATH) ?>index.php cron daily</code><br>Atau via URL: <code><?= e(site_url('cron/wa/' . $cron_key)) ?></code><br>Status "terkirim" berarti permintaan diterima Fonnte.</div></div>
</div>
<hr><h2 class="h6">Pesan Uji</h2>
<div class="row g-2"><div class="col-md-6"><input form="waTest" name="test_phone" class="form-control" placeholder="Nomor tujuan, mis. 0812xxxx"></div><div class="col-md-3"><button form="waTest" class="btn btn-outline-success">Kirim Pesan Uji</button></div></div>
<div class="form-text">Simpan pengaturan lebih dulu (token &amp; aktifkan) sebelum menguji.</div></div>
<div class="tab-pane fade" id="t4"><p class="small text-muted">Variabel: <code>{nama} {kelas} {mapel} {guru} {hari_tanggal} {waktu} {sekolah} {daftar}</code>. Teks dikirim apa adanya lewat Fonnte (baris baru dan <code>*tebal*</code> didukung WhatsApp).</p>
<?php foreach (array('hadir' => 'Siswa Hadir', 'terlambat' => 'Siswa Terlambat', 'belum' => 'Belum Absen / Tidak Hadir', 'izin' => 'Izin', 'sakit' => 'Sakit', 'rekap' => 'Rekap Harian') as $k => $l): ?>
<div class="mb-3"><label class="form-label fw-semibold"><?= $l ?></label>
<textarea name="tpl_<?= $k ?>" class="form-control" rows="6"><?= e(setting('tpl_' . $k, $defaults['tpl_' . $k])) ?></textarea></div><?php endforeach; ?></div>
</div>
<div class="mt-3"><button class="btn btn-primary btn-lg"><i class="bi bi-save"></i> Simpan Pengaturan</button></div>
<?= form_close() ?>
<?= form_open('settings/wa_test', array('id' => 'waTest')) ?><?= form_close() ?>
