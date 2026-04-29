-- Migration: create audit_trail table
-- Run this SQL in your MySQL client (e.g., phpMyAdmin or mysql CLI)

CREATE TABLE IF NOT EXISTS `audit_trail` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT NULL,
  `username` VARCHAR(150) NOT NULL,
  `action` VARCHAR(255) NOT NULL,
  `entity_type` VARCHAR(100) NULL,
  `entity_id` INT NULL,
  `entity_name` VARCHAR(255) NULL,
  `old_values` LONGTEXT NULL,
  `new_values` LONGTEXT NULL,
  `details` LONGTEXT NULL,
  `ip` VARCHAR(45) NULL,
  `status` VARCHAR(50) NOT NULL DEFAULT 'success',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX (`user_id`),
  INDEX (`username`),
  INDEX (`action`),
  INDEX (`entity_type`),
  INDEX (`entity_id`),
  INDEX (`created_at`)
)
ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
