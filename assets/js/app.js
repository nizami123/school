(function () {
  var meta = function (n) { var m = document.querySelector('meta[name="' + n + '"]'); return m ? m.content : ''; };
  window.CSRF = { name: meta('csrf-name'), hash: meta('csrf-hash') };

  window.toast = function (msg, type, html) {
    var box = document.getElementById('toasts'); if (!box) return;
    var el = document.createElement('div');
    el.className = 'toast align-items-center text-bg-' + (type === 'danger' ? 'danger' : type === 'warning' ? 'warning' : 'success') + ' border-0';
    el.innerHTML = '<div class="d-flex"><div class="toast-body"></div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div>';
    var b = el.querySelector('.toast-body'); if (html) b.innerHTML = msg; else b.textContent = msg;
    box.appendChild(el); var t = new bootstrap.Toast(el, { delay: 6000 }); t.show();
    el.addEventListener('hidden.bs.toast', function () { el.remove(); });
  };

  window.postJSON = function (url, data, opts) {
    var body = new URLSearchParams(); data = data || {};
    Object.keys(data).forEach(function (k) { body.append(k, data[k]); });
    body.append(CSRF.name, CSRF.hash);
    var ctl = new AbortController(); var to = setTimeout(function () { ctl.abort(); }, (opts && opts.timeout) || 12000);
    return fetch(url, { method: 'POST', body: body, credentials: 'same-origin', signal: ctl.signal,
      headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
      .then(function (r) {
        clearTimeout(to);
        if (r.status === 401) { location.href = window.BASE + 'login'; throw new Error('auth'); }
        if (!r.ok && r.status !== 200) { var e = new Error('http'); e.status = r.status; throw e; }
        return r.json();
      });
  };

  // Konfirmasi modal untuk form [data-confirm]
  var pending = null;
  document.addEventListener('submit', function (ev) {
    var f = ev.target; if (!f.matches || !f.matches('form[data-confirm]')) return;
    if (f.dataset.ok === '1') return;
    ev.preventDefault(); pending = f;
    document.getElementById('confirmText').textContent = f.dataset.confirm;
    bootstrap.Modal.getOrCreateInstance(document.getElementById('confirmModal')).show();
  });
  document.addEventListener('DOMContentLoaded', function () {
    var ok = document.getElementById('confirmOk');
    if (ok) ok.addEventListener('click', function () { if (pending) { pending.dataset.ok = '1'; pending.submit(); } });
    document.querySelectorAll('form:not([data-noload])').forEach(function (f) {
      f.addEventListener('submit', function () { if (!f.dataset.confirm || f.dataset.ok === '1') { var l = document.getElementById('loading'); if (l) setTimeout(function () { l.classList.remove('d-none'); }, 300); } });
    });
    if (window.jQuery && jQuery.fn.DataTable) {
      jQuery('table.dt').each(function () {
        jQuery(this).DataTable({ pageLength: 25, order: [], language: {
          search: 'Cari:', lengthMenu: 'Tampilkan _MENU_ data', info: 'Menampilkan _START_-_END_ dari _TOTAL_ data', infoEmpty: 'Tidak ada data',
          zeroRecords: 'Data tidak ditemukan', emptyTable: 'Belum ada data', paginate: { previous: '‹', next: '›' } } });
      });
    }
    if ('serviceWorker' in navigator) { navigator.serviceWorker.register(window.BASE.replace(/index\.php\/?$/, '') + 'sw.js').catch(function () {}); }
  });

  // Bunyi (WebAudio, tanpa file)
  var ac = null;
  window.beep = function (kind) {
    try {
      ac = ac || new (window.AudioContext || window.webkitAudioContext)();
      var seq = kind === 'ok' ? [[880, 0.12]] : kind === 'late' ? [[660, 0.12], [880, 0.12]] : [[220, 0.18], [180, 0.25]];
      var t = ac.currentTime;
      seq.forEach(function (s) {
        var o = ac.createOscillator(), g = ac.createGain(); o.frequency.value = s[0]; o.type = 'sine';
        g.gain.setValueAtTime(0.25, t); g.gain.exponentialRampToValueAtTime(0.001, t + s[1]);
        o.connect(g); g.connect(ac.destination); o.start(t); o.stop(t + s[1]); t += s[1] + 0.04;
      });
    } catch (e) {}
  };
})();
