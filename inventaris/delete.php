<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}

$id  = (int) ($_POST['id'] ?? 0);
$pdo = db();

try {
    // Bonus: transaction — hapus produk + catat log aktivitas secara atomik
    $pdo->beginTransaction();

    $stmt = $pdo->prepare('SELECT kode, nama FROM produk WHERE id = :id FOR UPDATE');
    $stmt->execute([':id' => $id]);
    $p = $stmt->fetch();

    if (!$p) {
        $pdo->rollBack();
        flash_set('error', 'Produk tidak ditemukan.');
        redirect('index.php');
    }

    $stmt = $pdo->prepare('DELETE FROM produk WHERE id = :id');
    $stmt->execute([':id' => $id]);

    $stmt = $pdo->prepare(
        'INSERT INTO log_aktivitas (aksi, produk_id, detail) VALUES (:aksi, :pid, :detail)'
    );
    $stmt->execute([
        ':aksi'   => 'DELETE',
        ':pid'    => $id,
        ':detail' => 'Menghapus produk ' . $p['kode'] . ' - ' . $p['nama'],
    ]);

    $pdo->commit();
    flash_set('success', 'Produk "' . $p['nama'] . '" berhasil dihapus.');
} catch (PDOException $ex) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    flash_set('error', 'Gagal menghapus produk.');
}

redirect('index.php');
