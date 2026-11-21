<div class="card mb-3"><div class="card-body py-2">
  <div class="d-flex justify-content-between flex-wrap">
    <div><div class="fw-bold"><?= e($s->subject_name) ?> · <?= e($s->class_name) ?></div>
    <div class="small text-muted"><?= e($s->teacher_name) ?> · <?= tanggal_id($s->session_date) ?> · <?= substr($s->start_time, 0, 5) ?>–<?= substr($s->end_time, 0, 5) ?> · batas tepat waktu <?= substr($s->late_limit, 0, 5) ?></div></div>
    <div id="net" class="small align-self-center"><span class="badge bg-success">Online</span></div>
  </div>
  <div class="row text-center mt-2 g-2">
    <div class="col-3"><div class="fs-4 fw-bold" id="sTotal"><?= (int)$stats['total'] ?></div><small class="text-muted">Total</small></div>
    <div class="col-3"><div class="fs-4 fw-bold text-success" id="sPresent"><?= (int)$stats['present'] ?></div><small class="text-muted">Hadir</small></div>
    <div class="col-3"><div class="fs-4 fw-bold text-danger" id="sAbsent"><?= (int)$stats['absent'] ?></div><small class="text-muted">Belum</small></div>
    <div class="col-3"><div class="fs-4 fw-bold" id="sPct"><?= (int)$stats['pct'] ?>%</div><small class="text-muted">Hadir</small></div>
  </div>
  <div class="progress mt-2"><div class="progress-bar" id="sBar" style="width:<?= (int)$stats['pct'] ?>%"></div></div>
</div></div>

<div id="reader"></div>
<div id="camErr" class="alert alert-warning mt-2 d-none"></div>
<div class="scan-result idle mt-3" id="result"><img id="rPhoto" src="<?= base_url('assets/icons/avatar.svg') ?>" alt=""><div><div class="msg" id="rMsg">Arahkan kamera ke QR Code kartu siswa</div><div class="small" id="rSub"></div></div></div>

<div class="d-flex gap-2 mt-3">
  <button class="btn btn-outline-secondary flex-fill" id="btnToggle"><i class="bi bi-pause-circle"></i> Hentikan Scan</button>
  <?= form_open('scan/finish', array('class' => 'flex-fill', 'data-confirm' => 'Akhiri sesi absensi? Siswa yang belum scan masuk daftar "belum dikonfirmasi".')) ?>
    <input type="hidden" name="session_id" value="<?= $s->id ?>"><button class="btn btn-danger w-100"><i class="bi bi-stop-circle"></i> Akhiri Sesi</button><?= form_close() ?>
</div>

<div id="pendingBox" class="card mt-3 d-none"><div class="card-body py-2"><strong class="small">Menunggu sinkronisasi <span class="offline-badge">belum resmi, divalidasi server saat online</span></strong><div id="pendingList" class="small mt-1"></div></div></div>

<ul class="nav nav-pills mt-3" id="tabs"><li class="nav-item"><button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tPresent">Hadir</button></li><li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tMissing">Belum Hadir</button></li><li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tRecent">Riwayat Scan</button></li></ul>
<div class="tab-content card card-body mt-2"><div class="tab-pane fade show active list-scroll" id="tPresent"></div><div class="tab-pane fade list-scroll" id="tMissing"></div><div class="tab-pane fade list-scroll" id="tRecent"></div></div>

<?php $scripts = '<script src="https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.8/html5-qrcode.min.js"></script><script>window.SCAN=' . json_encode(array(
  'sid' => (int)$s->id, 'scan' => site_url('scan/ajax_scan'), 'status' => site_url('scan/status/' . $s->id), 'kick' => site_url('scan/kick'))) . ';</script><script src="' . base_url('assets/js/scan.js') . '"></script>';
$this->load->vars(array('scripts' => $scripts)); ?>
