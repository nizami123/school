<div class="row g-3"><div class="col-lg-8"><div class="card"><div class="card-body"><div class="d-flex justify-content-between"><h2 class="h6">Sesi Hari Ini</h2><small class="text-muted">Diperbarui <span id="mTime">-</span> · otomatis tiap 5 dtk</small></div>
<div class="table-responsive"><table class="table table-sm align-middle"><thead><tr><th>Jam</th><th>Kelas</th><th>Mapel</th><th>Guru</th><th>Hadir</th><th>Scan Terakhir</th><th>Status</th><th></th></tr></thead><tbody id="mSessions"><tr><td colspan="8" class="text-muted">Memuat…</td></tr></tbody></table></div></div></div></div>
<div class="col-lg-4"><div class="card"><div class="card-body"><h2 class="h6">Aktivitas Scan Terbaru</h2><div id="mScans" class="small list-scroll" style="max-height:420px"></div></div></div></div></div>
<script>
(function(){var u=<?= json_encode(site_url('monitor/live')) ?>,B=<?= json_encode(site_url('scan/detail/')) ?>;
function esc(s){var d=document.createElement('div');d.textContent=s==null?'':s;return d.innerHTML;}
function load(){fetch(u,{credentials:'same-origin',headers:{'X-Requested-With':'XMLHttpRequest'}}).then(function(r){return r.json();}).then(function(d){
document.getElementById('mTime').textContent=d.time;
document.getElementById('mSessions').innerHTML=d.sessions.length?d.sessions.map(function(s){var p=s.total>0?Math.round(s.present*100/s.total):0;return '<tr><td>'+s.start_time.slice(0,5)+'–'+s.end_time.slice(0,5)+'</td><td>'+esc(s.class_name)+'</td><td>'+esc(s.subject)+'</td><td>'+esc(s.teacher)+'</td><td>'+s.present+'/'+s.total+' <span class="text-muted">('+p+'%)</span></td><td>'+(s.last_scan?s.last_scan.slice(11,16):'-')+'</td><td>'+(s.status==='open'?'<span class="badge bg-success">Berjalan</span>':'<span class="badge bg-secondary">Selesai</span>')+'</td><td><a class="btn btn-sm btn-outline-primary" href="'+B+s.id+'">Detail</a></td></tr>';}).join(''):'<tr><td colspan="8" class="text-muted">Belum ada sesi hari ini.</td></tr>';
document.getElementById('mScans').innerHTML=d.scans.map(function(x){var ok=(x.result==='hadir'||x.result==='terlambat');return '<div class="border-bottom py-1">'+(ok?'✔':'✖')+' <span class="text-muted">'+x.created_at.slice(11,19)+'</span> '+esc(x.message)+'<br><span class="text-muted">oleh '+esc(x.user_name||'-')+'</span></div>';}).join('')||'<span class="text-muted">Belum ada.</span>';
}).catch(function(){});}
load();setInterval(load,5000);})();
</script>
