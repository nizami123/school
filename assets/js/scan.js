(function () {
  var C = window.SCAN, QKEY = 'qrsa_queue_' + C.sid;
  var scanner = null, running = false, recent = {}, online = true;
  var $ = function (id) { return document.getElementById(id); };

  function uuid() { try { return crypto.randomUUID(); } catch (e) { return 'u' + Date.now().toString(36) + Math.random().toString(36).slice(2, 10); } }
  function esc(s) { var d = document.createElement('div'); d.textContent = s == null ? '' : s; return d.innerHTML; }
  function loadQ() { try { return JSON.parse(localStorage.getItem(QKEY) || '[]'); } catch (e) { return []; } }
  function saveQ(q) { try { localStorage.setItem(QKEY, JSON.stringify(q)); } catch (e) {} renderQ(q); }
  function setNet(on) { online = on; $('net').innerHTML = on ? '<span class="badge bg-success">Online</span>' : '<span class="badge bg-danger">Offline - scan disimpan sementara</span>'; }

  function setStats(s) {
    if (!s) return;
    $('sTotal').textContent = s.total; $('sPresent').textContent = s.present; $('sAbsent').textContent = s.absent;
    $('sPct').textContent = s.pct + '%'; $('sBar').style.width = s.pct + '%';
  }
  function showResult(r) {
    var el = $('result');
    el.className = 'scan-result ' + (r.ok ? (r.type === 'terlambat' ? 'late' : 'ok') : 'bad');
    $('rMsg').textContent = r.message;
    $('rSub').textContent = r.ok ? ((r.student.class || '') + ' · ' + (r.subject || '') + ' · ' + r.time + (r.type === 'terlambat' ? ' (terlambat)' : '')) : (r.detail || (r.student ? r.student.name : ''));
    $('rPhoto').src = r.student ? r.student.photo : window.BASE + 'assets/icons/avatar.svg';
    beep(r.ok ? (r.type === 'terlambat' ? 'late' : 'ok') : 'bad');
    if (navigator.vibrate) navigator.vibrate(r.ok ? 60 : [80, 60, 80]);
  }
  function kick() { postJSON(C.kick, {}, { timeout: 20000 }).catch(function () {}); } // latar belakang, tidak ditunggu

  function send(code, id, extra) {
    id = id || uuid();
    var data = { session_id: C.sid, code: code, client_uuid: id };
    if (extra) Object.keys(extra).forEach(function (k) { data[k] = extra[k]; });
    return postJSON(C.scan, data).then(function (r) { setNet(true); return r; });
  }

  function onScan(text) {
    var now = Date.now();
    if (recent[text] && now - recent[text] < 3000) return; // cegah pembacaan beruntun kode yang sama
    recent[text] = now;
    var id = uuid();
    send(text, id).then(function (r) {
      showResult(r); setStats(r.stats); if (r.ok) { kick(); refresh(); }
    }).catch(function (e) {
      if (e && e.message === 'auth') return;
      if (e && e.status === 403) { toast('Sesi keamanan kedaluwarsa. Muat ulang halaman.', 'danger'); return; }
      setNet(false);
      var q = loadQ(); q.push({ id: id, code: text, ms: Date.now() }); saveQ(q);
      var el = $('result'); el.className = 'scan-result late'; $('rMsg').textContent = 'Koneksi terputus: scan disimpan sementara (belum resmi).'; $('rSub').textContent = 'Akan dikirim otomatis saat online.'; beep('late');
    });
  }

  function renderQ(q) {
    q = q || loadQ();
    $('pendingBox').classList.toggle('d-none', q.length === 0);
    $('pendingList').innerHTML = q.map(function (x) { return '<div>' + new Date(x.ms).toLocaleTimeString('id-ID') + ' · kode …' + esc(x.code.slice(-6)) + '</div>'; }).join('');
  }
  var syncing = false;
  function sync() {
    var q = loadQ(); if (!q.length || syncing || !navigator.onLine) return;
    syncing = true;
    (function next() {
      q = loadQ();
      if (!q.length) { syncing = false; refresh(); kick(); return; }
      var it = q[0];
      send(it.code, it.id, { offline: 1, client_ms: it.ms }).then(function (r) {
        q = loadQ().filter(function (x) { return x.id !== it.id; }); saveQ(q); setStats(r.stats);
        toast((r.ok ? '✔ ' : '✖ ') + r.message, r.ok ? 'success' : 'warning'); next();
      }).catch(function (e) { syncing = false; if (e && e.message !== 'auth') setNet(false); });
    })();
  }

  function refresh() {
    fetch(C.status, { credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } }).then(function (r) {
      if (r.status === 401) { location.href = window.BASE + 'login'; return null; } return r.json();
    }).then(function (d) {
      if (!d) return; setNet(true); setStats(d.stats);
      if (d.status !== 'open') { location.href = window.BASE + 'scan/detail/' + C.sid; return; }
      $('tPresent').innerHTML = d.present.length ? d.present.map(function (p) { return '<div class="d-flex justify-content-between border-bottom py-1"><span>' + esc(p.name) + '</span><span>' + p.time + ' ' + (p.status === 'terlambat' ? '<span class="badge bg-warning text-dark">Terlambat</span>' : '<span class="badge bg-success">Hadir</span>') + '</span></div>'; }).join('') : '<span class="text-muted">Belum ada.</span>';
      $('tMissing').innerHTML = d.missing.length ? d.missing.map(function (n) { return '<div class="border-bottom py-1">' + esc(n) + '</div>'; }).join('') : '<span class="text-success">Semua siswa sudah hadir.</span>';
      $('tRecent').innerHTML = d.recent.map(function (x) { return '<div class="border-bottom py-1 small"><span class="text-muted">' + x.time + '</span> ' + (x.result === 'hadir' || x.result === 'terlambat' ? '✔' : '✖') + ' ' + esc(x.message) + '</div>'; }).join('');
    }).catch(function () { setNet(false); });
  }

  function startCam() {
    if (!window.Html5Qrcode) { camErr('Pustaka scanner gagal dimuat. Periksa koneksi internet.'); return; }
    if (!window.isSecureContext) { camErr('Kamera memerlukan HTTPS. Buka aplikasi lewat alamat https://.'); return; }
    scanner = scanner || new Html5Qrcode('reader', { formatsToSupport: [Html5QrcodeSupportedFormats.QR_CODE], verbose: false });
    scanner.start({ facingMode: 'environment' }, { fps: 10, qrbox: function (w, h) { var m = Math.floor(Math.min(w, h) * 0.7); return { width: m, height: m }; }, aspectRatio: 1.0 }, onScan, function () {})
      .then(function () { running = true; $('btnToggle').innerHTML = '<i class="bi bi-pause-circle"></i> Hentikan Scan'; $('camErr').classList.add('d-none'); })
      .catch(function (e) { camErr('Kamera tidak dapat diakses: ' + (e && e.message ? e.message : e) + '. Izinkan akses kamera di pengaturan browser, lalu muat ulang.'); });
  }
  function camErr(m) { var el = $('camErr'); el.textContent = m; el.classList.remove('d-none'); }
  function stopCam() { if (scanner && running) { scanner.stop().then(function () { running = false; $('btnToggle').innerHTML = '<i class="bi bi-play-circle"></i> Lanjutkan Scan'; }); } }

  document.addEventListener('DOMContentLoaded', function () {
    $('btnToggle').addEventListener('click', function () { running ? stopCam() : startCam(); });
    window.addEventListener('online', function () { setNet(true); sync(); });
    window.addEventListener('offline', function () { setNet(false); });
    document.addEventListener('visibilitychange', function () { if (!document.hidden) refresh(); });
    renderQ(); startCam(); refresh(); sync();
    setInterval(refresh, 6000); setInterval(sync, 10000); setInterval(kick, 20000);
  });
})();
