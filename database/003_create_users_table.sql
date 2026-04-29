-- Migration: create users table
-- Run this SQL in your MySQL client (e.g., phpMyAdmin or mysql CLI)

CREATE TABLE IF NOT EXISTS `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(150) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `nama_lengkap` VARCHAR(255) NOT NULL,
  `role` ENUM('user', 'admin') NOT NULL DEFAULT 'user',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX (`role`),
  INDEX (`created_at`)
)
ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Optional: Add default admin user (username: admin, password: admin123)
-- Note: Change password immediately after first login!
INSERT IGNORE INTO `users` (username, password, nama_lengkap, role) VALUES
('admin', '$2y$10$YIjlrHyVQH0Xd7.0m.0V0O7S8H.fXbVFXbVFXbVFXbVFXbVFXbVFXb', 'Administrator', 'admin');
