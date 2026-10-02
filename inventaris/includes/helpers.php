<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/Database.php';

function db(): PDO
{
    return Database::getInstance()->getConnection();
}

/** Escape output HTML (wajib untuk semua output). */
function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function rupiah(float|string $n): string
{
    return 'Rp ' . number_format((float) $n, 0, ',', '.');
}

function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

/** Flash message (redirect pattern): simpan di session, tampil sekali. */
function flash_set(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function flash_get(): ?array
{
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

function get_kategori(): array
{
    return db()->query('SELECT id, nama FROM kategori ORDER BY nama')->fetchAll();
}

function get_supplier(): array
{
    return db()->query('SELECT id, nama FROM supplier ORDER BY nama')->fetchAll();
}

/** Validasi input produk. Kembalikan [data, errors]. */
function validate_produk(array $in): array
{
    $d = [
        'kode'        => trim((string) ($in['kode'] ?? '')),
        'nama'        => trim((string) ($in['nama'] ?? '')),
        'kategori_id' => (int) ($in['kategori_id'] ?? 0),
        'supplier_id' => (int) ($in['supplier_id'] ?? 0),
        'harga'       => $in['harga'] ?? '',
        'stok'        => $in['stok'] ?? '',
    ];
    $err = [];

    if ($d['kode'] === '' || strlen($d['kode']) > 20) $err[] = 'Kode wajib diisi (maks. 20 karakter).';
    if ($d['nama'] === '' || strlen($d['nama']) > 150) $err[] = 'Nama wajib diisi (maks. 150 karakter).';
    if ($d['kategori_id'] <= 0) $err[] = 'Pilih kategori.';
    if ($d['supplier_id'] <= 0) $err[] = 'Pilih supplier.';
    if (!is_numeric($d['harga']) || (float) $d['harga'] < 0) $err[] = 'Harga harus angka ≥ 0.';
    if (filter_var($d['stok'], FILTER_VALIDATE_INT) === false || (int) $d['stok'] < 0) $err[] = 'Stok harus bilangan bulat ≥ 0.';

    return [$d, $err];
}
