<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/helpers.php';

$title = 'Edit Produk';
$id    = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);

$stmt = db()->prepare('SELECT * FROM produk WHERE id = :id');
$stmt->execute([':id' => $id]);
$produk = $stmt->fetch();

if (!$produk) {
    flash_set('error', 'Produk tidak ditemukan.');
    redirect('index.php');
}

$kategori  = get_kategori();
$suppliers = get_supplier();
$data      = $produk;   // form pre-filled
$errors    = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    [$data, $errors] = validate_produk($_POST);

    if (!$errors) {
        try {
            $stmt = db()->prepare(
                'UPDATE produk
                 SET kode = :kode, nama = :nama, kategori_id = :kategori_id,
                     supplier_id = :supplier_id, harga = :harga, stok = :stok
                 WHERE id = :id'
            );
            $stmt->execute([
                ':kode'        => $data['kode'],
                ':nama'        => $data['nama'],
                ':kategori_id' => $data['kategori_id'],
                ':supplier_id' => $data['supplier_id'],
                ':harga'       => $data['harga'],
                ':stok'        => (int) $data['stok'],
                ':id'          => $id,
            ]);
            flash_set('success', 'Produk "' . $data['nama'] . '" berhasil diperbarui.');
            redirect('index.php');
        } catch (PDOException $ex) {
            $errors[] = $ex->getCode() === '23000'
                ? 'Kode produk sudah dipakai, atau kategori/supplier tidak valid.'
                : 'Gagal memperbarui data.';
        }
    }
}

$action = 'edit.php?id=' . $id;
$label  = 'Perbarui';
require __DIR__ . '/includes/header.php';
?>
<h1>Edit Produk</h1>
<?php require __DIR__ . '/includes/form.php'; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
