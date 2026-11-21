document.addEventListener('DOMContentLoaded', function () {
  fetch(window.CHART_URL, { credentials: 'same-origin' }).then(function (r) { return r.json(); }).then(function (d) {
    var G = '#16a34a', D = '#14532d', L = '#86efac', Y = '#f59e0b', R = '#ef4444';
    var mk = function (id, cfg) { var el = document.getElementById(id); if (el) new Chart(el, cfg); };
    mk('cDaily', { type: 'bar', data: { labels: d.daily.map(function (x) { return x.d.slice(5); }), datasets: [
      { label: 'Hadir', data: d.daily.map(function (x) { return +x.h; }), backgroundColor: G },
      { label: 'Terlambat', data: d.daily.map(function (x) { return +x.t; }), backgroundColor: Y },
      { label: 'Tidak hadir', data: d.daily.map(function (x) { return +x.a; }), backgroundColor: R }] }, options: { scales: { x: { stacked: true }, y: { stacked: true } } } });
    mk('cMonthly', { type: 'line', data: { labels: d.monthly.map(function (x) { return x.ym; }), datasets: [{ label: '% hadir', data: d.monthly.map(function (x) { return +x.pct; }), borderColor: D, backgroundColor: L, fill: true, tension: .3 }] }, options: { scales: { y: { min: 0, max: 100 } } } });
    mk('cClass', { type: 'bar', data: { labels: d.by_class.map(function (x) { return x.label; }), datasets: [{ label: '% hadir', data: d.by_class.map(function (x) { return +x.pct; }), backgroundColor: G }] }, options: { indexAxis: 'y', scales: { x: { min: 0, max: 100 } } } });
    mk('cSubject', { type: 'bar', data: { labels: d.by_subject.map(function (x) { return x.label; }), datasets: [{ label: '% hadir', data: d.by_subject.map(function (x) { return +x.pct; }), backgroundColor: D }] }, options: { indexAxis: 'y', scales: { x: { min: 0, max: 100 } } } });
    mk('cLate', { type: 'line', data: { labels: d.late.map(function (x) { return x.d.slice(5); }), datasets: [{ label: 'Terlambat', data: d.late.map(function (x) { return +x.t; }), borderColor: Y, backgroundColor: 'rgba(245,158,11,.2)', fill: true, tension: .3 }] } });
  });
});
