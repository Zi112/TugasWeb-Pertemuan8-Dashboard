<?php $flash = flash_get(); ?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($title ?? 'Inventaris') ?> — CRUD Inventaris</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<header class="topbar">
  <div class="container bar">
    <a class="brand" href="index.php">📦 Inventaris</a>
    <nav>
      <a href="index.php">Daftar Produk</a>
      <a href="create.php">+ Tambah</a>
      <a href="export.php">Export CSV</a>
    </nav>
  </div>
</header>
<main class="container">
<?php if ($flash): ?>
  <div class="alert <?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
<?php endif; ?>
