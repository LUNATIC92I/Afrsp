-- =====================================================================
--  AFRICA'SPY — migration : paiement obligatoire avant l'accès aux formations
--  À exécuter sur une base installée AVANT cette version :
--    mysql africaspy < database/migrations/2026_11_13_payments.sql
--  (les nouvelles installations l'intègrent déjà via database/africaspy.sql)
-- =====================================================================
SET NAMES utf8mb4;

ALTER TABLE courses
  ADD COLUMN price_xof       INT UNSIGNED NOT NULL DEFAULT 900000 AFTER duration_hours,
  ADD COLUMN promo_price_xof INT UNSIGNED NULL DEFAULT 170000   AFTER price_xof;

CREATE TABLE IF NOT EXISTS payments (
  id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  reference       VARCHAR(40)  NOT NULL,
  user_id         INT UNSIGNED NOT NULL,
  course_id       INT UNSIGNED NOT NULL,
  amount_xof      INT UNSIGNED NOT NULL,
  list_price_xof  INT UNSIGNED NOT NULL,
  currency        CHAR(3)      NOT NULL DEFAULT 'XOF',
  provider        ENUM('cinetpay','manual','sandbox') NOT NULL,
  status          ENUM('pending','paid','failed','cancelled','refunded') NOT NULL DEFAULT 'pending',
  provider_txn    VARCHAR(120) NULL,
  payment_method  VARCHAR(60)  NULL,
  payer_phone     VARCHAR(30)  NULL,
  provider_data   TEXT NULL,
  admin_note      VARCHAR(255) NULL,
  validated_by    INT UNSIGNED NULL,
  paid_at         DATETIME NULL,
  created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_pay_ref (reference),
  KEY idx_pay_user_course (user_id, course_id, status),
  KEY idx_pay_status (status, created_at),
  CONSTRAINT fk_pay_user   FOREIGN KEY (user_id)      REFERENCES users (id)   ON DELETE CASCADE,
  CONSTRAINT fk_pay_course FOREIGN KEY (course_id)    REFERENCES courses (id) ON DELETE RESTRICT,
  CONSTRAINT fk_pay_admin  FOREIGN KEY (validated_by) REFERENCES users (id)   ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO permissions (slug, label) VALUES ('payments.manage', 'Gérer les paiements et valider les règlements');
INSERT IGNORE INTO role_permissions (role_id, permission_id)
  SELECT r.id, p.id FROM roles r JOIN permissions p ON p.slug = 'payments.manage' WHERE r.slug = 'admin';
INSERT IGNORE INTO settings (setting_key, setting_value) VALUES
  ('payment_manual_enabled', '1'),
  ('payment_manual_instructions', "Virement bancaire ou dépôt Mobile Money au nom d'AFRICA'SPY.\nIndiquez impérativement la référence de paiement dans le libellé.\nCoordonnées bancaires : à compléter dans Administration › Paramètres."),
  ('promo_label', 'Offre de lancement'),
  ('promo_ends_at', '');
