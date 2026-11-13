-- =====================================================================
--  AFRICA'SPY — migration : factures / avoirs PDF et messagerie configurable
--    mysql africaspy < database/migrations/2026_11_15_invoices_mail.sql
--    php database/invoices-backfill.php   (factures des paiements déjà encaissés)
-- =====================================================================
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS invoice_sequences (
  doc_type  ENUM('invoice','credit_note') NOT NULL,
  year      SMALLINT UNSIGNED NOT NULL,
  last_no   INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (doc_type, year)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS invoices (
  id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  doc_type        ENUM('invoice','credit_note') NOT NULL DEFAULT 'invoice',
  invoice_no      VARCHAR(30)  NOT NULL,
  payment_id      INT UNSIGNED NOT NULL,
  related_id      INT UNSIGNED NULL,
  user_id         INT UNSIGNED NULL,
  issued_at       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  seller          TEXT NOT NULL,
  buyer           TEXT NOT NULL,
  label           VARCHAR(255) NOT NULL,
  list_price_xof  INT NOT NULL,
  discount_xof    INT NOT NULL DEFAULT 0,
  amount_ht_xof   INT NOT NULL,
  tax_rate        DECIMAL(5,2) NOT NULL DEFAULT 0,
  tax_label       VARCHAR(60) NOT NULL,
  tax_xof         INT NOT NULL DEFAULT 0,
  amount_ttc_xof  INT NOT NULL,
  file_name       VARCHAR(120) NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_invoice_no (invoice_no),
  UNIQUE KEY uq_invoice_payment_type (payment_id, doc_type),
  KEY idx_invoice_user (user_id),
  KEY idx_invoice_date (issued_at),
  CONSTRAINT fk_inv_payment FOREIGN KEY (payment_id) REFERENCES payments (id) ON DELETE RESTRICT,
  CONSTRAINT fk_inv_related FOREIGN KEY (related_id) REFERENCES invoices (id) ON DELETE RESTRICT,
  CONSTRAINT fk_inv_user    FOREIGN KEY (user_id)    REFERENCES users (id)    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO settings (setting_key, setting_value) VALUES
  ('seller_legal_name', 'AFRICA''SPY — Intelligence Academy'),
  ('seller_address', ''),
  ('seller_tax_id', ''),
  ('seller_registration', ''),
  ('seller_email', ''),
  ('tax_rate', '0'),
  ('tax_label', 'TVA'),
  ('tax_exempt_mention', 'TVA non applicable — à adapter selon votre régime fiscal'),
  ('invoice_footer', 'Facture acquittée. Formation en ligne à usage personnel, non cessible.'),
  ('mail_transport', ''),
  ('mail_from', ''),
  ('mail_from_name', 'AFRICA''SPY Academy'),
  ('mail_reply_to', ''),
  ('smtp_host', ''),
  ('smtp_port', '587'),
  ('smtp_secure', 'tls'),
  ('smtp_user', ''),
  ('smtp_pass_enc', ''),
  ('welcome_mail_enabled', '1');
