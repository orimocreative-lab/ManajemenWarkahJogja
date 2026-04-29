-- SQL: Create table for Warkah Berkas (Files/Documents)
CREATE TABLE IF NOT EXISTS `warkah_berkas` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `warkah_id` INT UNSIGNED NOT NULL,
  `nama_berkas` VARCHAR(255) NOT NULL,
  `deskripsi` VARCHAR(255) DEFAULT NULL,
  `file_path` VARCHAR(500) NOT NULL,
  `tipe_berkas` VARCHAR(100) NOT NULL,
  `ukuran_file` INT DEFAULT NULL,
  `uploaded_by` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`warkah_id`) REFERENCES `warkah` (`id`) ON DELETE CASCADE,
  KEY `idx_warkah_id` (`warkah_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
