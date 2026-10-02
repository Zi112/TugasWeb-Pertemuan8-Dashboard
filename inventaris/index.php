<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/helpers.php';

$title   = 'Daftar Produk';
$q       = trim((string) ($_GET['q'] ?? ''));
$perPage = 5;
$page    = max(1, (int) ($_GET['page'] ?? 1));

$where  = '';
$params = [];
if ($q !== '') {
    // Catatan: dengan EMULATE_PREPARES=false, satu nama placeholder tidak boleh
    // dipakai berulang, jadi tiap kolom memakai placeholder sendiri (:q1..:q4).
    $where = 'WHERE p.nama LIKE :q1 OR p.kode LIKE :q2 OR k.nama LIKE :q3 OR s.nama LIKE :q4';
    $like  = '%' . $q . '%';
    $params = [':q1' => $like, ':q2' => $like, ':q3' => $like, ':q4' => $like];
}

$from = 'FROM produk p
         JOIN kategori k ON k.id = p.kategori_id
         JOIN supplier s ON s.id = p.supplier_id';

// Total data (untuk pagination)
$stmt = db()->prepare("SELECT COUNT(*) $from $where");
$stmt->execute($params);
$total      = (int) $stmt->fetchColumn();
$totalPages = max(1, (int) ceil($total / $perPage));
$page       = min($page, $totalPages);
$offset     = ($page - 1) * $perPage;

// List produk: JOIN 2 tabel (kategori & supplier)
$stmt = db()->prepare(
    "SELECT p.id, p.kode, p.nama, p.harga, p.stok,
            k.nama AS kategori, s.nama AS supplier
     $from $where
     ORDER BY p.id DESC
     LIMIT :limit OFFSET :offset"
);
foreach ($params as $key => $val) {
    $stmt->bindValue($key, $val);
}
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$produk = $stmt->fetchAll();

require __DIR__ . '/includes/header.php';
?>
<div class="head-row">
  <h1>Daftar Produk</h1>
  <a href="create.php" class="btn primary">+ Tambah Produk</a>
</div>

<form method="get" class="search">
  <input type="text" name="q" placeholder="Cari kode, nama, kategori, atau supplier…" value="<?= e($q) ?>">
  <button class="btn" type="submit">Cari</button>
  <?php if ($q !== ''): ?><a class="btn" href="index.php">Reset</a><?php endif; ?>
</form>

<div class="card table-wrap">
  <table>
    <thead>
      <tr><th>#</th><th>Kode</th><th>Nama</th><th>Kategori</th><th>Supplier</th><th class="r">Harga</th><th class="r">Stok</th><th>Aksi</th></tr>
    </thead>
    <tbody>
    <?php if (!$produk): ?>
      <tr><td colspan="8" class="empty">Tidak ada data produk.</td></tr>
    <?php endif; ?>
    <?php foreach ($produk as $i => $p): ?>
      <tr>
        <td><?= e($offset + $i + 1) ?></td>
        <td><code><?= e($p['kode']) ?></code></td>
        <td><?= e($p['nama']) ?></td>
        <td><span class="badge"><?= e($p['kategori']) ?></span></td>
        <td><?= e($p['supplier']) ?></td>
        <td class="r"><?= e(rupiah($p['harga'])) ?></td>
        <td class="r"><?= e($p['stok']) ?></td>
        <td class="aksi">
          <a class="btn sm" href="edit.php?id=<?= e($p['id']) ?>">Edit</a>
          <form method="post" action="delete.php"
                onsubmit="return confirm('Yakin hapus produk &quot;<?= e(addslashes($p['nama'])) ?>&quot;?');">
            <input type="hidden" name="id" value="<?= e($p['id']) ?>">
            <button type="submit" class="btn sm danger">Hapus</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>

<div class="pager">
  <span>Total <?= e($total) ?> produk — halaman <?= e($page) ?> / <?= e($totalPages) ?></span>
  <div>
    <?php for ($n = 1; $n <= $totalPages; $n++): ?>
      <a class="btn sm <?= $n === $page ? 'primary' : '' ?>"
         href="?<?= e(http_build_query(['q' => $q, 'page' => $n])) ?>"><?= e($n) ?></a>
    <?php endfor; ?>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
