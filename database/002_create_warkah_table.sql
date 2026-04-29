-- SQL: Create table for Warkah records
CREATE TABLE IF NOT EXISTS `warkah` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nomor_warkah` VARCHAR(100) NOT NULL,
  `kecamatan` VARCHAR(100) NOT NULL,
  `kelurahan` VARCHAR(100) NOT NULL,
  `nomor_hak` VARCHAR(100) NOT NULL,
  `peminjam` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('Tersedia','Dipinjam') NOT NULL DEFAULT 'Tersedia',
  `tanggal_dipinjam` DATE DEFAULT NULL,
  `file_ktp` VARCHAR(255) DEFAULT NULL,
  `file_surat_permohonan` VARCHAR(255) DEFAULT NULL,
  `file_alas_hak` VARCHAR(255) DEFAULT NULL,
  `file_bukti_penguasaan` VARCHAR(255) DEFAULT NULL,
  `file_sk_pemberian_hak` VARCHAR(255) DEFAULT NULL,
  `file_peta_bidang` VARCHAR(255) DEFAULT NULL,
  `file_berita_acara` VARCHAR(255) DEFAULT NULL,
  `file_bukti_pajak` VARCHAR(255) DEFAULT NULL,
  `file_akta_ppat` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY (`nomor_warkah`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
