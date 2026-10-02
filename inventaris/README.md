# CRUD Inventaris — Tugas Rutin 8

## Cara menjalankan
1. Jalankan MySQL/MariaDB (XAMPP/Laragon), lalu import `database.sql`
   (phpMyAdmin → Import, atau `mysql -u root < database.sql`).
2. Sesuaikan kredensial di `includes/config.php` bila perlu.
3. Letakkan folder ini di `htdocs` / `www`, atau jalankan:
   `php -S localhost:8000` di dalam folder `inventaris`.
4. Buka http://localhost:8000

## Checklist requirement
| # | Requirement | Lokasi |
|---|-------------|--------|
| 1 | DB `inventaris_db`, 3 tabel + FK | `database.sql` (kategori, supplier, produk) |
| 2 | Minimal 5 seed per tabel | `database.sql` (5 / 5 / 10) |
| 3 | PDO + Singleton | `includes/Database.php` |
| 4 | List produk JOIN 2 tabel | `index.php` |
| 5 | Form create dropdown kategori & supplier | `create.php`, `includes/form.php` |
| 6 | Update (pre-filled) & delete (konfirmasi) | `edit.php`, `delete.php` + `confirm()` |
| 7 | Semua query prepared statement | seluruh file (`prepare` + bind/execute) |
| 8 | Output `htmlspecialchars()` | helper `e()` di `includes/helpers.php` |
| 9 | Flash message (redirect pattern) | `flash_set()` / `flash_get()` |
| 10 | UI rapi | `style.css` |

Bonus: transaction + log pada delete, pencarian, pagination, export CSV.
