-- Tugas Rutin 8 - CRUD Inventaris
DROP DATABASE IF EXISTS inventaris_db;
CREATE DATABASE inventaris_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE inventaris_db;

CREATE TABLE kategori (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE supplier (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(150) NOT NULL,
  telepon VARCHAR(20) NOT NULL,
  alamat VARCHAR(255) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE produk (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  kode VARCHAR(20) NOT NULL UNIQUE,
  nama VARCHAR(150) NOT NULL,
  kategori_id INT UNSIGNED NOT NULL,
  supplier_id INT UNSIGNED NOT NULL,
  harga DECIMAL(12,2) NOT NULL DEFAULT 0,
  stok INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_produk_kategori FOREIGN KEY (kategori_id)
    REFERENCES kategori(id) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_produk_supplier FOREIGN KEY (supplier_id)
    REFERENCES supplier(id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Bonus: log aktivitas (dipakai pada transaction delete)
CREATE TABLE log_aktivitas (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  aksi VARCHAR(20) NOT NULL,
  produk_id INT UNSIGNED NULL,
  detail VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO kategori (nama) VALUES
('Elektronik'),('Alat Tulis'),('Perabot'),('Makanan & Minuman'),('Peralatan Kebersihan');

INSERT INTO supplier (nama, telepon, alamat) VALUES
('PT Medan Elektronik Jaya','0614567890','Jl. Gatot Subroto No. 12, Medan'),
('CV Sumatera Stationery','0617654321','Jl. Sisingamangaraja No. 45, Medan'),
('UD Furnitur Nusantara','0618123456','Jl. Amplas No. 8, Medan'),
('PT Boga Sejahtera','0619988776','Jl. Setia Budi No. 21, Medan'),
('CV Bersih Selalu','0613344556','Jl. Pancing No. 3, Medan');

INSERT INTO produk (kode, nama, kategori_id, supplier_id, harga, stok) VALUES
('ELK-001','Mouse Wireless',1,1,85000,40),
('ELK-002','Keyboard Mekanik',1,1,450000,15),
('ATK-001','Pulpen Gel 0.5mm (Box)',2,2,36000,120),
('ATK-002','Buku Tulis A5',2,2,8500,300),
('PRB-001','Kursi Kantor Ergonomis',3,3,1250000,8),
('PRB-002','Meja Belajar Lipat',3,3,375000,12),
('MKN-001','Kopi Sachet (Dus)',4,4,52000,60),
('MKN-002','Air Mineral 600ml (Karton)',4,4,45000,85),
('KBS-001','Cairan Pembersih Lantai 1L',5,5,24000,50),
('KBS-002','Tisu Wajah (Pak)',5,5,12000,200);
