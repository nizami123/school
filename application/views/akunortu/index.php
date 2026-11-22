<?php if (!empty($new)): ?>
<div class="card mb-3 border-success"><div class="card-body">
  <div class="d-flex justify-content-between align-items-center mb-2"><h2 class="h6 mb-0"><i class="bi bi-key"></i> Akun baru / password baru (tampil sekali — segera catat atau cetak)</h2><button class="btn btn-sm btn-outline-secondary" onclick="window.print()"><i class="bi bi-printer"></i> Cetak</button></div>
  <div class="table-responsive"><table class="table table-sm mb-0"><thead><tr><th>Nama</th><th>Username</th><th>Password Awal</th></tr></thead><tbody>
  <?php foreach ($new as $n): ?><tr><td><?= e($n['name']) ?></td><td><code><?= e($n['username']) ?></code></td><td><code><?= e($n['password']) ?></code></td></tr><?php endforeach; ?></tbody></table></div>
  <div class="form-text">Orang tua diminta mengganti password saat login pertama. Login lewat halaman yang sama dengan guru/admin: <code><?= e(site_url('login')) ?></code></div>
</div></div>
<?php endif; ?>
<div class="card"><div class="card-body">
  <div class="d-flex flex-wrap gap-2 mb-3 align-items-center">
    <?= form_open('akunortu/create', array('class' => 'd-inline', 'data-confirm' => 'Buat akun untuk SEMUA orang tua yang belum punya akun?')) ?><button class="btn btn-primary"><i class="bi bi-person-plus"></i> Buat Akun Semua yang Belum Punya</button><?= form_close() ?>
    <span class="small text-muted">Username otomatis <code>ortu</code> + NIS anak pertama; password acak 8 karakter. Satu akun dapat memantau semua anak yang tertaut ke orang tua tersebut (atur di Data Siswa).</span>
  </div>
  <div class="table-responsive"><table class="table table-hover align-middle dt">
    <thead><tr><th>Orang Tua/Wali</th><th>Anak</th><th>Username</th><th>Login Terakhir</th><th>Status</th><th class="text-end">Aksi</th></tr></thead><tbody>
    <?php foreach ($rows as $r): ?><tr>
      <td><?= e($r->name) ?><br><small class="text-muted"><?= e($r->whatsapp) ?></small></td>
      <td class="small"><?= $r->children ? e($r->children) : '<span class="text-danger">Belum tertaut ke siswa</span>' ?></td>
      <td><?= $r->username ? '<code>' . e($r->username) . '</code>' : '<span class="text-muted">-</span>' ?></td>
      <td class="small"><?= $r->last_login ? date('d/m/Y H:i', strtotime($r->last_login)) : '-' ?></td>
      <td><?php if (!$r->username): ?><span class="badge bg-secondary">Belum ada akun</span><?php else: ?><?= $r->is_active ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-secondary">Nonaktif</span>' ?><?= $r->must_change_password ? ' <span class="badge bg-warning text-dark">Belum ganti password</span>' : '' ?><?php endif; ?></td>
      <td class="text-end text-nowrap">
        <?php if (!$r->username): ?>
          <?= form_open('akunortu/create/' . $r->id, array('class' => 'd-inline')) ?><button class="btn btn-sm btn-outline-primary" <?= $r->children ? '' : 'disabled' ?>>Buat Akun</button><?= form_close() ?>
        <?php else: ?>
          <?= form_open('akunortu/reset/' . $r->id, array('class' => 'd-inline', 'data-confirm' => 'Reset password orang tua ini? Password lama tidak berlaku lagi.')) ?><button class="btn btn-sm btn-outline-warning">Reset Password</button><?= form_close() ?>
          <?= form_open('akunortu/toggle/' . $r->id, array('class' => 'd-inline', 'data-confirm' => 'Ubah status aktif akun?')) ?><button class="btn btn-sm btn-outline-secondary"><i class="bi bi-toggle-on"></i></button><?= form_close() ?>
        <?php endif; ?></td></tr><?php endforeach; ?></tbody></table></div>
</div></div>
