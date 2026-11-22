<?php $u = uri_string(); $pend = isset($pending) ? (int)$pending : 0; ?>
<ul class="nav nav-pills mb-3">
  <li class="nav-item"><a class="nav-link <?= $u === 'iuran' || strpos($u, 'iuran/detail') === 0 ? 'active' : '' ?>" href="<?= site_url('iuran') ?>">Tagihan</a></li>
  <li class="nav-item"><a class="nav-link <?= strpos($u, 'iuran/generate') === 0 ? 'active' : '' ?>" href="<?= site_url('iuran/generate') ?>">Buat Tagihan</a></li>
  <li class="nav-item"><a class="nav-link <?= strpos($u, 'iuran/verifikasi') === 0 ? 'active' : '' ?>" href="<?= site_url('iuran/verifikasi') ?>">Verifikasi<?= $pend ? ' <span class="badge bg-danger">' . $pend . '</span>' : '' ?></a></li>
  <li class="nav-item"><a class="nav-link" href="<?= site_url('master/fee_types') ?>">Jenis Iuran</a></li>
  <li class="nav-item"><a class="nav-link" href="<?= site_url('settings#pay') ?>">Rekening Pembayaran</a></li>
</ul>
