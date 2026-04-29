-- SQL: Create table for Bon Warkah (Peminjaman Warkah)
CREATE TABLE IF NOT EXISTS `bon_warkah` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nomor_bon` VARCHAR(100) NOT NULL UNIQUE,
  `tanggal_bon` DATE NOT NULL,
  `nomor_hak` VARCHAR(100) DEFAULT NULL,
  `kelurahan` VARCHAR(255) DEFAULT NULL,
  `kecamatan` VARCHAR(255) DEFAULT NULL,
  `peminjam` VARCHAR(255) NOT NULL,
  `unit_kerja` VARCHAR(255) NOT NULL,
  `nomor_warkah` VARCHAR(100) NOT NULL,
  `jenis_warkah` VARCHAR(255) NOT NULL,
  `lokasi_warkah` VARCHAR(255) NOT NULL,
  `tanggal_pinjam` DATE NOT NULL,
  `tanggal_kembali` DATE DEFAULT NULL,
  `status` ENUM('dipinjam', 'dikembalikan') NOT NULL DEFAULT 'dipinjam',
  `status_terakhir` VARCHAR(255) DEFAULT NULL,
  `keterangan` TEXT DEFAULT NULL,
  `created_by` INT UNSIGNED NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_nomor_bon` (`nomor_bon`),
  KEY `idx_status` (`status`),
  KEY `idx_tanggal_pinjam` (`tanggal_pinjam`),
  KEY `idx_kelurahan` (`kelurahan`),
  KEY `idx_kecamatan` (`kecamatan`),
  KEY `idx_created_by` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- Catatan: Pastikan table 'users' sudah ada sebelum menjalankan SQL ini
-- Jika diperlukan, tambahkan FOREIGN KEY untuk created_by:
-- ALTER TABLE bon_warkah ADD CONSTRAINT fk_bon_warkah_users 
-- FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT;
