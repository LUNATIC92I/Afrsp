-- =====================================================================
--  AFRICA'SPY : migration double authentification
--  Méthode au choix (application TOTP ou code par e-mail), appareils de
--  confiance, politique d'obligation (aucune, personnel, tous les comptes).
--  Usage : mysql africaspy < database/migrations/2026_11_16_mfa.sql
-- =====================================================================
SET NAMES utf8mb4;

-- totp_enabled = « double authentification activée » (toutes méthodes) ; mfa_method précise laquelle
ALTER TABLE users ADD COLUMN IF NOT EXISTS mfa_method ENUM('totp','email') NULL AFTER totp_enabled;
UPDATE users SET mfa_method = 'totp' WHERE totp_enabled = 1 AND mfa_method IS NULL;

CREATE TABLE IF NOT EXISTS mfa_email_codes (
  user_id     INT UNSIGNED NOT NULL,
  purpose     ENUM('login','setup') NOT NULL,
  code_hash   CHAR(64) NOT NULL,
  tries       TINYINT UNSIGNED NOT NULL DEFAULT 0,
  expires_at  DATETIME NOT NULL,
  created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (user_id, purpose),
  CONSTRAINT fk_mfa_codes_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS mfa_trusted_devices (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id       INT UNSIGNED NOT NULL,
  selector      CHAR(24) NOT NULL,
  token_hash    CHAR(64) NOT NULL,
  label         VARCHAR(190) NULL,
  ip_address    VARCHAR(45) NULL,
  created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  last_used_at  DATETIME NULL,
  expires_at    DATETIME NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_mfa_device_selector (selector),
  KEY idx_mfa_device_user (user_id),
  CONSTRAINT fk_mfa_device_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Politique : optional | staff | all (reprend l'ancien réglage « obligatoire pour le personnel »)
INSERT IGNORE INTO settings (setting_key, setting_value)
  SELECT 'mfa_policy', IF(COALESCE((SELECT setting_value FROM settings WHERE setting_key = 'mfa_required_staff'), '0') = '1', 'staff', 'optional');
INSERT IGNORE INTO settings (setting_key, setting_value) VALUES
  ('mfa_email_enabled', '1'),
  ('mfa_trust_days', '30'),
  ('mfa_notify', '1');
DELETE FROM settings WHERE setting_key = 'mfa_required_staff';
