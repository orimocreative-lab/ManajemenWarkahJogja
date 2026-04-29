-- Migration: Create edit_requests table for user edit requests
-- This table stores edit requests from regular users that need admin approval

CREATE TABLE IF NOT EXISTS `edit_requests` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `bon_id` INT UNSIGNED NOT NULL,
  `requested_by` INT UNSIGNED NOT NULL,
  `requested_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` ENUM('pending', 'approved', 'rejected', 'completed') NOT NULL DEFAULT 'pending',
  `approved_by` INT UNSIGNED DEFAULT NULL,
  `approved_at` DATETIME DEFAULT NULL,
  `rejection_reason` TEXT DEFAULT NULL,
  `proposed_changes` LONGTEXT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_bon_id` (`bon_id`),
  KEY `idx_requested_by` (`requested_by`),
  KEY `idx_status` (`status`),
  KEY `idx_requested_at` (`requested_at`),
  CONSTRAINT `fk_edit_requests_bon` FOREIGN KEY (`bon_id`) REFERENCES `bon_warkah` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_edit_requests_user` FOREIGN KEY (`requested_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_edit_requests_approver` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Catatan migrasi tambahan jika menambah kolom status baru pada tabel yang sudah ada:
-- ALTER TABLE edit_requests MODIFY status ENUM('pending','approved','rejected','completed') NOT NULL DEFAULT 'pending';

