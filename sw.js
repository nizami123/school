/* Service worker: cache aset statis saja. Halaman & API selalu lewat jaringan (data absensi harus dari server). */
var CACHE = 'qrsa-static-v1';
var STATIC = ['assets/css/app.css', 'assets/js/app.js', 'assets/icons/icon-192.png', 'assets/icons/icon-512.png', 'assets/icons/avatar.svg'];
self.addEventListener('install', function (e) { e.waitUntil(caches.open(CACHE).then(function (c) { return c.addAll(STATIC); }).then(function () { return self.skipWaiting(); })); });
self.addEventListener('activate', function (e) {
  e.waitUntil(caches.keys().then(function (ks) { return Promise.all(ks.filter(function (k) { return k !== CACHE; }).map(function (k) { return caches.delete(k); })); }).then(function () { return self.clients.claim(); }));
});
self.addEventListener('fetch', function (e) {
  var req = e.request;
  if (req.method !== 'GET') return;
  var url = new URL(req.url);
  if (url.origin !== location.origin || url.pathname.indexOf('/assets/') === -1) return;
  e.respondWith(caches.match(req).then(function (hit) {
    var net = fetch(req).then(function (res) { var cp = res.clone(); caches.open(CACHE).then(function (c) { c.put(req, cp); }); return res; }).catch(function () { return hit; });
    return hit || net;
  }));
});
