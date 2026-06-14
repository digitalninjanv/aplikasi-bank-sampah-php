-- Migration 2026: Feature enhancements for Bank Sampah Digital
-- Run: sudo mysql db_banksampah < migration_2026.sql

-- 1. Soft delete / status untuk pengguna
ALTER TABLE pengguna
  ADD COLUMN status ENUM('aktif','nonaktif') NOT NULL DEFAULT 'aktif' AFTER level,
  ADD COLUMN foto VARCHAR(255) DEFAULT NULL AFTER alamat,
  ADD COLUMN last_login TIMESTAMP NULL DEFAULT NULL AFTER saldo,
  ADD COLUMN login_attempts TINYINT NOT NULL DEFAULT 0 AFTER last_login,
  ADD COLUMN locked_until TIMESTAMP NULL DEFAULT NULL AFTER login_attempts,
  ADD COLUMN reset_token VARCHAR(64) DEFAULT NULL AFTER locked_until,
  ADD COLUMN reset_expires TIMESTAMP NULL DEFAULT NULL AFTER reset_token,
  ADD COLUMN created_by INT DEFAULT NULL AFTER reset_expires,
  ADD INDEX idx_status (status);

-- 2. Tabel log aktivitas
CREATE TABLE IF NOT EXISTS log_aktivitas (
  id_log INT AUTO_INCREMENT PRIMARY KEY,
  id_pengguna INT DEFAULT NULL,
  username VARCHAR(50) DEFAULT NULL,
  aksi VARCHAR(50) NOT NULL,
  tabel VARCHAR(50) DEFAULT NULL,
  id_record INT DEFAULT NULL,
  detail TEXT DEFAULT NULL,
  ip_address VARCHAR(45) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_aksi (aksi),
  INDEX idx_created (created_at),
  INDEX idx_pengguna (id_pengguna)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 3. Tabel riwayat harga sampah
CREATE TABLE IF NOT EXISTS harga_history (
  id_history INT AUTO_INCREMENT PRIMARY KEY,
  id_jenis_sampah INT NOT NULL,
  harga_lama DECIMAL(10,2) NOT NULL,
  harga_baru DECIMAL(10,2) NOT NULL,
  id_petugas INT DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_jenis (id_jenis_sampah),
  INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 4. Soft delete / status untuk jenis_sampah
ALTER TABLE jenis_sampah
  ADD COLUMN status ENUM('aktif','nonaktif') NOT NULL DEFAULT 'aktif' AFTER satuan,
  ADD INDEX idx_jenis_status (status);

-- 5. Update data seed: ubah password warga agar bisa login (password: warga123)
UPDATE pengguna SET password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' WHERE level = 'warga' AND id_pengguna IN (3,4);

-- 6. Tabel pengaturan aplikasi
CREATE TABLE IF NOT EXISTS app_settings (
  setting_key VARCHAR(50) PRIMARY KEY,
  setting_value TEXT DEFAULT NULL,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT IGNORE INTO app_settings (setting_key, setting_value) VALUES
  ('app_name', 'Bank Sampah Digital'),
  ('app_address', ''),
  ('app_phone', '');
