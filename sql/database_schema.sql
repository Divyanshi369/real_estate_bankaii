-- Real Estate Management System - Database Schema
-- Engine: MySQL 8+

SET NAMES utf8mb4;
SET time_zone = '+00:00';

CREATE DATABASE IF NOT EXISTS real_estate_bankaii CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE real_estate_bankaii;

-- Users
CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(160) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('admin','manager','supervisor','worker') NOT NULL DEFAULT 'worker',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  INDEX idx_users_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Projects
CREATE TABLE IF NOT EXISTS projects (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(160) NOT NULL,
  description TEXT NULL,
  status ENUM('planned','active','completed','on_hold','cancelled') NOT NULL DEFAULT 'planned',
  assigned_manager INT UNSIGNED NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  INDEX idx_projects_status (status),
  INDEX idx_projects_assigned_manager (assigned_manager),
  CONSTRAINT fk_projects_manager FOREIGN KEY (assigned_manager) REFERENCES users (id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Stock
CREATE TABLE IF NOT EXISTS stock (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  item_name VARCHAR(160) NOT NULL,
  quantity INT NOT NULL DEFAULT 0,
  used_quantity INT NOT NULL DEFAULT 0,
  updated_by INT UNSIGNED NULL,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  INDEX idx_stock_item_name (item_name),
  CONSTRAINT fk_stock_updated_by FOREIGN KEY (updated_by) REFERENCES users (id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tasks
CREATE TABLE IF NOT EXISTS tasks (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  project_id INT UNSIGNED NOT NULL,
  assigned_to INT UNSIGNED NOT NULL,
  task_name VARCHAR(200) NOT NULL,
  status ENUM('assigned','in_progress','done','blocked') NOT NULL DEFAULT 'assigned',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  INDEX idx_tasks_project_id (project_id),
  INDEX idx_tasks_assigned_to (assigned_to),
  CONSTRAINT fk_tasks_project FOREIGN KEY (project_id) REFERENCES projects (id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_tasks_assigned_to FOREIGN KEY (assigned_to) REFERENCES users (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Attendance
CREATE TABLE IF NOT EXISTS attendance (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  worker_id INT UNSIGNED NOT NULL,
  date DATE NOT NULL,
  status ENUM('present','absent','leave') NOT NULL DEFAULT 'present',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uniq_attendance_worker_date (worker_id, date),
  INDEX idx_attendance_worker_id (worker_id),
  CONSTRAINT fk_attendance_worker FOREIGN KEY (worker_id) REFERENCES users (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Password reset tokens
CREATE TABLE IF NOT EXISTS password_resets (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED NOT NULL,
  token CHAR(64) NOT NULL,
  expires_at DATETIME NOT NULL,
  used TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uniq_password_resets_token (token),
  INDEX idx_password_resets_user_id (user_id),
  CONSTRAINT fk_password_resets_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed an initial admin (change password after first login)
INSERT INTO users (name, email, password_hash, role)
VALUES ('Administrator', 'admin@example.com', CONCAT('$2y$10$', SUBSTRING(MD5(RAND()), 1, 22), SUBSTRING(MD5(RAND()), 1, 31)), 'admin')
ON DUPLICATE KEY UPDATE email = email;


