<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/helpers.php';

// Bonus: export laporan CSV
$rows = db()->query(
    'SELECT p.kode, p.nama, k.nama AS kategori, s.nama AS supplier, p.harga, p.stok
     FROM produk p
     JOIN kategori k ON k.id = p.kategori_id
     JOIN supplier s ON s.id = p.supplier_id
     ORDER BY p.kode'
)->fetchAll();

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="laporan_inventaris_' . date('Ymd') . '.csv"');

$out = fopen('php://output', 'w');
fwrite($out, "\xEF\xBB\xBF"); // BOM agar Excel membaca UTF-8
fputcsv($out, ['Kode', 'Nama', 'Kategori', 'Supplier', 'Harga', 'Stok']);
foreach ($rows as $r) {
    fputcsv($out, $r);
}
fclose($out);
