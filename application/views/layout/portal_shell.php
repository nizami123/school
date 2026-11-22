<?php
$uri = uri_string();
$nav = array(
    array('portal', 'bi-house-heart', 'Beranda', array('portal')),
    array('portal/kehadiran', 'bi-calendar-check', 'Kehadiran', array('portal/kehadiran')),
    array('portal/izin', 'bi-envelope-paper', 'Izin', array('portal/izin')),
    array('portal/bayar', 'bi-cash-coin', 'Bayar', array('portal/bayar', 'portal/infaq')),
    array('portal/menu', 'bi-grid', 'Menu', array('portal/menu', 'portal/jadwal', 'portal/pengumuman', 'portal/ekskul', 'portal/profil')),
);
$is_act = function ($prefixes) use ($uri) { foreach ($prefixes as $p) { if ($p === 'portal' ? ($uri === 'portal' || $uri === 'portal/index') : strpos($uri, $p) === 0) return TRUE; } return FALSE; };
?>
<header class="topbar guru-top portal-top"><span class="fw-semibold"><?php if (!empty($logo)): ?><img src="<?= upload_url('logo/' . $logo) ?>" alt="" style="height:22px;vertical-align:-5px"><?php else: ?><i class="bi bi-mortarboard"></i><?php endif; ?> <?= e($school) ?></span>
  <a href="<?= site_url('logout') ?>" class="text-white text-decoration-none small"><i class="bi bi-box-arrow-right"></i> Keluar</a></header>
<?php if (!empty($children)): ?>
<div class="portal-child"><div class="container" style="max-width:760px">
  <?php if (count($children) > 1): ?>
    <form method="get" action="<?= site_url($uri) ?>" class="d-flex align-items-center gap-2" data-noload>
      <i class="bi bi-person-badge fs-5"></i>
      <select name="anak" class="form-select form-select-sm" onchange="this.form.submit()"><?php foreach ($children as $c): ?><option value="<?= (int)$c->id ?>" <?= $child && (int)$child->id === (int)$c->id ? 'selected' : '' ?>><?= e($c->name) ?> — <?= e($c->class_name) ?></option><?php endforeach; ?></select>
    </form>
  <?php elseif ($child): ?>
    <div class="d-flex align-items-center gap-2"><img class="avatar" src="<?= student_photo($child->photo) ?>" alt=""><div class="lh-sm"><strong><?= e($child->name) ?></strong><br><small class="text-muted">Kelas <?= e($child->class_name) ?> · NIS <?= e($child->nis) ?></small></div></div>
  <?php endif; ?>
</div></div>
<?php endif; ?>
<main class="container py-3 portal-main" style="max-width:760px"><?= $content ?></main>
<nav class="bottom-nav">
  <?php foreach ($nav as $m): ?><a href="<?= site_url($m[0]) ?>" class="<?= $is_act($m[3]) ? 'active' : '' ?>"><i class="bi <?= $m[1] ?>"></i><span><?= e($m[2]) ?></span></a><?php endforeach; ?>
</nav>
