-- Migration: Create berkas (documents) table

CREATE TABLE IF NOT EXISTS `berkas` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `bon_warkah_id` INT UNSIGNED NOT NULL,
  `nama_berkas` VARCHAR(255) NOT NULL,
  `tipe_berkas` VARCHAR(100) NOT NULL COMMENT 'KTP, SURAT_PERMOHONAN, ALAS_HAK, dll',
  `file_path` VARCHAR(500) NOT NULL,
  `file_size` INT NOT NULL COMMENT 'Ukuran dalam bytes',
  `file_hash` VARCHAR(64) DEFAULT NULL COMMENT 'SHA256 hash untuk verifikasi',
  `uploaded_by` INT UNSIGNED NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`bon_warkah_id`) REFERENCES `bon_warkah` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
  INDEX `idx_bon_warkah_id` (`bon_warkah_id`),
  INDEX `idx_tipe_berkas` (`tipe_berkas`),
  INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabel untuk tracking perubahan berkas (audit trail)
CREATE TABLE IF NOT EXISTS `berkas_audit` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `berkas_id` INT UNSIGNED NOT NULL,
  `action` VARCHAR(50) NOT NULL COMMENT 'UPLOAD, UPDATE, DELETE',
  `user_id` INT UNSIGNED NOT NULL,
  `old_values` JSON DEFAULT NULL,
  `new_values` JSON DEFAULT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`berkas_id`) REFERENCES `berkas` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
  INDEX `idx_berkas_id` (`berkas_id`),
  INDEX `idx_action` (`action`),
  INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;