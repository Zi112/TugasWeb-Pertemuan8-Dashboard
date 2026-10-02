<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/helpers.php';

$title     = 'Tambah Produk';
$kategori  = get_kategori();
$suppliers = get_supplier();
$data      = [];
$errors    = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    [$data, $errors] = validate_produk($_POST);

    if (!$errors) {
        try {
            $stmt = db()->prepare(
                'INSERT INTO produk (kode, nama, kategori_id, supplier_id, harga, stok)
                 VALUES (:kode, :nama, :kategori_id, :supplier_id, :harga, :stok)'
            );
            $stmt->execute([
                ':kode'        => $data['kode'],
                ':nama'        => $data['nama'],
                ':kategori_id' => $data['kategori_id'],
                ':supplier_id' => $data['supplier_id'],
                ':harga'       => $data['harga'],
                ':stok'        => (int) $data['stok'],
            ]);
            flash_set('success', 'Produk "' . $data['nama'] . '" berhasil ditambahkan.');
            redirect('index.php');
        } catch (PDOException $ex) {
            $errors[] = $ex->getCode() === '23000'
                ? 'Kode produk sudah dipakai, atau kategori/supplier tidak valid.'
                : 'Gagal menyimpan data.';
        }
    }
}

$action = 'create.php';
$label  = 'Simpan';
require __DIR__ . '/includes/header.php';
?>
<h1>Tambah Produk</h1>
<?php require __DIR__ . '/includes/form.php'; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
