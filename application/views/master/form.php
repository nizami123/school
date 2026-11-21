<div class="card" style="max-width:760px"><div class="card-body">
<?= form_open_multipart('master/' . $key . '/save') ?>
<input type="hidden" name="id" value="<?= (int)$id ?>">
<div class="row g-3">
<?php foreach ($e['fields'] as $f): $n = $f['name']; $val = isset($row[$n]) ? $row[$n] : ''; $req = !empty($f['required']) ? 'required' : ''; ?>
  <div class="col-12 <?= in_array($f['type'], array('select', 'time', 'date', 'number'), TRUE) ? 'col-md-6' : '' ?>">
  <?php if ($f['type'] === 'bool'): ?>
    <div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="<?= $n ?>" value="1" id="f_<?= $n ?>" <?= $val ? 'checked' : '' ?>><label class="form-check-label" for="f_<?= $n ?>"><?= e($f['label']) ?></label></div>
  <?php else: ?>
    <label class="form-label" for="f_<?= $n ?>"><?= e($f['label']) ?><?= $req ? ' <span class="text-danger">*</span>' : '' ?></label>
    <?php if ($f['type'] === 'select'): $o = is_array($f['options']) ? $f['options'] : (isset($opts[$n]) ? $opts[$n] : array()); ?>
      <select class="form-select" name="<?= $n ?>" id="f_<?= $n ?>" <?= $req ?>><option value="">— pilih —</option>
      <?php foreach ($o as $k => $l): ?><option value="<?= e($k) ?>" <?= (string)$val === (string)$k ? 'selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?></select>
    <?php elseif ($f['type'] === 'multiselect'): $sel = is_array($val) ? $val : array(); ?>
      <select class="form-select" name="<?= $n ?>[]" id="f_<?= $n ?>" multiple size="6"><?php foreach ($opts[$n] as $k => $l): ?><option value="<?= e($k) ?>" <?= in_array($k, $sel) ? 'selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?></select>
      <div class="form-text">Tahan Ctrl/Cmd untuk memilih beberapa.</div>
    <?php elseif ($f['type'] === 'file'): ?>
      <?php if (!empty($row['photo'])): ?><div class="mb-2"><img src="<?= student_photo($row['photo']) ?>" class="avatar" style="width:72px;height:72px"></div><?php endif; ?>
      <input type="file" class="form-control" name="<?= $n ?>" id="f_<?= $n ?>" accept="image/jpeg,image/png">
    <?php elseif ($f['type'] === 'password'): ?>
      <input type="password" class="form-control" name="<?= $n ?>" id="f_<?= $n ?>" autocomplete="new-password">
    <?php else: $type = in_array($f['type'], array('time', 'date', 'email', 'number'), TRUE) ? $f['type'] : 'text'; ?>
      <input type="<?= $type ?>" class="form-control" name="<?= $n ?>" id="f_<?= $n ?>" value="<?= e($val) ?>" <?= $req ?>>
    <?php endif; ?>
    <?php if (!empty($f['help'])): ?><div class="form-text"><?= e($f['help']) ?></div><?php endif; ?>
  <?php endif; ?>
  </div>
<?php endforeach; ?>
</div>
<div class="mt-4 d-flex gap-2"><button class="btn btn-primary"><i class="bi bi-save"></i> Simpan</button><a class="btn btn-light" href="<?= site_url('master/' . $key) ?>">Batal</a></div>
<?= form_close() ?></div></div>
