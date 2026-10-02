# Laporan Pengujian — Tugas Rutin 8: CRUD Inventaris (PHP PDO)

**Mata kuliah:** Pemrograman Web  
**Objek uji:** `inventaris/` (PHP native + PDO Singleton + MariaDB)  
**Tanggal:** 2 Oktober 2026

## 1. Ringkasan

- **31 dari 31 skenario fungsional & E2E lulus** pada kode final.
- Pengujian menemukan **3 masalah**; ketiganya sudah diperbaiki (lihat bagian 3).
- Responsivitas diuji pada **3 breakpoint** (375 / 768 / 1280 px) di 3 halaman: tidak ada halaman yang mengalami horizontal overflow.

## 2. Lingkungan Pengujian

| Komponen | Versi |
|---|---|
| PHP (built-in server) | 8.3.6 |
| Database | MariaDB 10.11.14 |
| Browser otomatis | Chromium via Playwright 1.56.0 |
| Data awal | `database.sql` (5 kategori, 5 supplier, 10 produk), di-reset sebelum setiap rangkaian uji |

**Metode:** skenario `F01–F27` dijalankan lewat HTTP request langsung dengan pengecekan isi database (`mysql`) sebagai bukti; skenario `E01–E04` dijalankan di browser sungguhan karena melibatkan dialog `confirm()` JavaScript; uji responsif memakai viewport berbeda dan pengukuran DOM. Semua dijalankan oleh satu skrip (`tests/report_tests.js`) sehingga dapat diulang.

## 3. Masalah yang Ditemukan dan Diperbaiki

| # | Masalah | Dampak | Penyebab | Perbaikan | Status |
|---|---|---|---|---|---|
| 1 | Pencarian error HTTP 500 (`SQLSTATE[HY093]: Invalid parameter number`) | Fitur search tidak bisa dipakai sama sekali | Placeholder `:q` dipakai 4× dalam satu query, sedangkan `ATTR_EMULATE_PREPARES => false` melarang nama placeholder berulang | Empat placeholder unik `:q1`–`:q4` di `index.php` | ✅ Diperbaiki |
| 2 | Kolom Kode dan Harga terpotong jadi dua baris; garis baris kolom Aksi tidak sejajar | Tampilan tabel berantakan di desktop | `display:flex` dipasang langsung pada `<td class="aksi">` sehingga bukan lagi sel tabel; tidak ada `white-space:nowrap` pada kode/harga | `style.css`: `nowrap` pada kode dan harga, tombol Aksi dibuat `inline-block` | ✅ Diperbaiki |
| 3 | Di tablet (768 px) kolom Aksi (tombol Edit/Hapus) terpotong di luar kartu tanpa petunjuk | Tombol utama tidak terlihat tanpa menggulir tabel | Tabel selebar 805 px dalam kartu 726 px (sudah ada pada CSS asli; sempat bertambah jadi 854 px setelah perbaikan #2) | Media query ≤900 px: padding dan font dirapatkan, badge boleh wrap, kolom Aksi `position:sticky` di kanan | ✅ Diperbaiki |

Hasil ukur lebar tabel di 768 px: asli 805 px → setelah perbaikan #2 saja 854 px → final **726 px (pas, tanpa scroll)**.

## 4. Hasil Uji Fungsional

| ID | Area | Skenario | Hasil yang diharapkan | Hasil aktual | Status |
|---|---|---|---|---|---|
| F01 | Read | Buka daftar produk (JOIN kategori+supplier) | HTTP 200, 5 baris, total 10 | HTTP 200, 5 baris | ✅ Lulus |
| F02 | Search | Cari "mouse" | HTTP 200, 1 hasil | HTTP 200, 1 hasil | ✅ Lulus |
| F03 | Search | Cari "medan" | HTTP 200, 2 hasil | HTTP 200, 2 hasil | ✅ Lulus |
| F04 | Search | Cari "ELK-001" | HTTP 200, 1 hasil | HTTP 200, 1 hasil | ✅ Lulus |
| F05 | Search | Cari "zzzz" | HTTP 200, 0 hasil | HTTP 200, 0 hasil | ✅ Lulus |
| F06 | Search | Cari "' OR 1=1 --" | HTTP 200, 0 hasil | HTTP 200, 0 hasil | ✅ Lulus |
| F07 | Pagination | page=2 | HTTP 200, jatuh ke halaman 2 | HTTP 200, halaman 2 / 2 | ✅ Lulus |
| F08 | Pagination | page=999 | HTTP 200, jatuh ke halaman 2 | HTTP 200, halaman 2 / 2 | ✅ Lulus |
| F09 | Pagination | page=0 | HTTP 200, jatuh ke halaman 1 | HTTP 200, halaman 1 / 2 | ✅ Lulus |
| F10 | Pagination | page=abc | HTTP 200, jatuh ke halaman 1 | HTTP 200, halaman 1 / 2 | ✅ Lulus |
| F11 | Create | Dropdown kategori & supplier terisi dari DB | 12 <option> (2 placeholder + 5 + 5) | 12 option | ✅ Lulus |
| F12 | Create | Tambah produk valid | HTTP 302 ke index, flash sukses, data tersimpan | HTTP 302, flash=success | ✅ Lulus |
| F13 | Flash | Flash tampil sekali (refresh tidak muncul lagi) | Flash kedua = kosong | hilang | ✅ Lulus |
| F14 | Keamanan | Nama berisi <b> di-escape (XSS) | Tampil sebagai &lt;b&gt; | ter-escape | ✅ Lulus |
| F15 | Validasi | Kode duplikat | Pesan error, tidak tersimpan ganda | HTTP 200 | ✅ Lulus |
| F16 | Validasi | Semua field kosong/tidak valid | 6 pesan error | 6 pesan | ✅ Lulus |
| F17 | Validasi | Harga negatif & stok desimal | 2 pesan error | 2 pesan | ✅ Lulus |
| F18 | Validasi | FK tidak valid (kategori_id=999) | Ditolak DB, pesan error, tidak crash | HTTP 200 | ✅ Lulus |
| F19 | Update | Form edit ter-isi data lama | value kode & harga terisi | pre-filled | ✅ Lulus |
| F20 | Update | Simpan perubahan | HTTP 302, flash sukses, DB berubah | HTTP 302 | ✅ Lulus |
| F21 | Update | Ubah kode jadi milik produk lain | Ditolak, data lama utuh | ditolak | ✅ Lulus |
| F22 | Update | Edit id tidak ada | Redirect + flash error | HTTP 302 | ✅ Lulus |
| F23 | Delete | Delete lewat GET | Ditolak, data tetap ada | data tetap | ✅ Lulus |
| F24 | Delete | Delete lewat POST | Terhapus + 1 baris log DELETE | terhapus + log | ✅ Lulus |
| F25 | Delete | Delete id tidak ada | Flash error, tidak ada log palsu | tidak ada log palsu | ✅ Lulus |
| F26 | Transaction | Delete saat INSERT log gagal (tabel log dirusak sementara) | ROLLBACK: produk tetap ada, flash error | produk ada=1 | ✅ Lulus |
| F27 | Export | Export CSV | Content-Type csv, BOM UTF-8, 11 baris | 11 baris, BOM=true | ✅ Lulus |

## 5. Hasil Uji End-to-End di Browser

| ID | Skenario | Status |
|---|---|---|
| E01 | Nama berisi tanda kutip, & dan apostrof tampil utuh | ✅ Lulus |
| E02 | Dialog confirm() muncul memuat nama produk lengkap | ✅ Lulus |
| E03 | Klik Batal pada confirm: produk tidak terhapus | ✅ Lulus |
| E04 | Klik OK pada confirm: produk terhapus + flash sukses | ✅ Lulus |

## 6. Hasil Uji Responsif (3 Breakpoint)

| Breakpoint | Lebar | Halaman | Halaman overflow horizontal? | Kolom form | Tabel perlu scroll? | Status |
|---|---|---|---|---|---|---|
| Mobile | 375 px | index | Tidak | - | Ya (Aksi tetap terlihat, sticky) | ✅ Lulus |
| Mobile | 375 px | create | Tidak | 1 | - | ✅ Lulus |
| Mobile | 375 px | edit | Tidak | 1 | - | ✅ Lulus |
| Tablet | 768 px | index | Tidak | - | Tidak | ✅ Lulus |
| Tablet | 768 px | create | Tidak | 2 | - | ✅ Lulus |
| Tablet | 768 px | edit | Tidak | 2 | - | ✅ Lulus |
| Desktop | 1280 px | index | Tidak | - | Tidak | ✅ Lulus |
| Desktop | 1280 px | create | Tidak | 2 | - | ✅ Lulus |
| Desktop | 1280 px | edit | Tidak | 2 | - | ✅ Lulus |

**Perilaku yang diharapkan:** form 1 kolom di mobile, 2 kolom di tablet dan desktop; tabel pas tanpa scroll mulai 768 px; di mobile tabel boleh digulir ke samping tetapi tombol Aksi menempel di kanan.

### Tangkapan layar — Daftar Produk

| Mobile (375 px) | Tablet (768 px) | Desktop (1280 px) |
|---|---|---|
| ![](shots/index_375.png) | ![](shots/index_768.png) | ![](shots/index_1280.png) |

### Tangkapan layar — Form Tambah Produk

| Mobile (375 px) | Tablet (768 px) | Desktop (1280 px) |
|---|---|---|
| ![](shots/create_375.png) | ![](shots/create_768.png) | ![](shots/create_1280.png) |

## 7. Keterbatasan dan Catatan

- Skenario `F01–F27` memakai HTTP request, bukan klik UI. Interaksi UI hanya dicakup `E01–E04` (dialog konfirmasi, batal/OK).
- Kondisi balapan (dua pengguna menghapus produk yang sama bersamaan) tidak disimulasikan. Kode memakai `SELECT … FOR UPDATE` di dalam transaksi, sehingga secara desain aman, tetapi belum dibuktikan lewat uji.
- Di mobile, kolom Kategori, Supplier, Harga, dan Stok baru terlihat setelah tabel digulir ke samping; tidak ada indikator visual bahwa tabel bisa digulir.
- Navigasi atas di mobile membungkus ke dua baris (logo dan menu) — fungsional, tetapi belum dirapikan.
- Belum ditangani (bukan bug fungsional): wildcard `%`/`_` pada pencarian tidak di-escape; export CSV rentan formula injection pada sel yang diawali `=`/`+`/`-`/`@`; tidak ada token CSRF pada form hapus.
- Pengujian dilakukan di lingkungan sandbox (PHP built-in server), bukan XAMPP/Laragon; perilaku yang bergantung konfigurasi server tidak tercakup.

## 8. Cara Menjalankan Ulang

```bash
# 1. Jalankan MariaDB dan server PHP
cd inventaris && php -S 127.0.0.1:8000
# 2. Instal Playwright (sekali saja), lalu jalankan suite uji
npm i playwright && npx playwright install chromium
node tests/report_tests.js
```

Catatan: skrip memakai `mysql -uroot` tanpa password dan path `/home/claude/work/...` untuk `database.sql` serta folder `shots`; sesuaikan dua hal itu dengan lingkunganmu sebelum menjalankannya.