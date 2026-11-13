-- =====================================================================
--  AFRICA'SPY : migration sécurité : limitation de débit, double authentification
--    mysql africaspy < database/migrations/2026_11_14_security.sql
-- =====================================================================
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS rate_limits (
  bucket        VARCHAR(160) NOT NULL,
  hits          INT UNSIGNED NOT NULL DEFAULT 0,
  window_start  DATETIME NOT NULL,
  PRIMARY KEY (bucket),
  KEY idx_rl_window (window_start)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE users
  ADD COLUMN totp_secret    VARCHAR(255) NULL AFTER password_hash,
  ADD COLUMN totp_enabled   TINYINT(1) NOT NULL DEFAULT 0 AFTER totp_secret,
  ADD COLUMN totp_last_step BIGINT UNSIGNED NULL AFTER totp_enabled,
  ADD COLUMN totp_recovery  TEXT NULL AFTER totp_last_step;

INSERT IGNORE INTO settings (setting_key, setting_value) VALUES ('mfa_required_staff', '0');
