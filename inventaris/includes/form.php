<?php /** Form bersama create & edit. Variabel: $data, $errors, $kategori, $suppliers, $action, $label */ ?>
<?php if (!empty($errors)): ?>
  <div class="alert error"><ul>
    <?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?>
  </ul></div>
<?php endif; ?>

<form method="post" action="<?= e($action) ?>" class="card form">
  <div class="grid">
    <label>Kode
      <input type="text" name="kode" maxlength="20" value="<?= e($data['kode'] ?? '') ?>" required>
    </label>
    <label>Nama Produk
      <input type="text" name="nama" maxlength="150" value="<?= e($data['nama'] ?? '') ?>" required>
    </label>
    <label>Kategori
      <select name="kategori_id" required>
        <option value="">-- Pilih kategori --</option>
        <?php foreach ($kategori as $k): ?>
          <option value="<?= e($k['id']) ?>" <?= (int) ($data['kategori_id'] ?? 0) === (int) $k['id'] ? 'selected' : '' ?>>
            <?= e($k['nama']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </label>
    <label>Supplier
      <select name="supplier_id" required>
        <option value="">-- Pilih supplier --</option>
        <?php foreach ($suppliers as $s): ?>
          <option value="<?= e($s['id']) ?>" <?= (int) ($data['supplier_id'] ?? 0) === (int) $s['id'] ? 'selected' : '' ?>>
            <?= e($s['nama']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </label>
    <label>Harga (Rp)
      <input type="number" name="harga" min="0" step="0.01" value="<?= e($data['harga'] ?? '') ?>" required>
    </label>
    <label>Stok
      <input type="number" name="stok" min="0" step="1" value="<?= e($data['stok'] ?? '') ?>" required>
    </label>
  </div>
  <div class="actions">
    <button type="submit" class="btn primary"><?= e($label) ?></button>
    <a href="index.php" class="btn">Batal</a>
  </div>
</form>
