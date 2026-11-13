-- =====================================================================
--  AFRICA'SPY : migration : dépôt de travaux corrigés et notes personnelles
--    mysql africaspy < database/migrations/2026_11_13_submissions_notes.sql
-- =====================================================================
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS submissions (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id       INT UNSIGNED NOT NULL,
  lesson_id     INT UNSIGNED NOT NULL,
  content       MEDIUMTEXT NOT NULL,
  status        ENUM('submitted','reviewed','revision') NOT NULL DEFAULT 'submitted',
  grade         TINYINT UNSIGNED NULL,
  feedback      TEXT NULL,
  reviewer_id   INT UNSIGNED NULL,
  version       SMALLINT UNSIGNED NOT NULL DEFAULT 1,
  submitted_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  reviewed_at   DATETIME NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_sub_user_lesson (user_id, lesson_id),
  KEY idx_sub_status (status, submitted_at),
  CONSTRAINT fk_sub_user     FOREIGN KEY (user_id)     REFERENCES users (id)   ON DELETE CASCADE,
  CONSTRAINT fk_sub_lesson   FOREIGN KEY (lesson_id)   REFERENCES lessons (id) ON DELETE CASCADE,
  CONSTRAINT fk_sub_reviewer FOREIGN KEY (reviewer_id) REFERENCES users (id)   ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS lesson_notes (
  user_id     INT UNSIGNED NOT NULL,
  lesson_id   INT UNSIGNED NOT NULL,
  content     TEXT NOT NULL,
  updated_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (user_id, lesson_id),
  KEY idx_notes_lesson (lesson_id),
  CONSTRAINT fk_notes_user   FOREIGN KEY (user_id)   REFERENCES users (id)   ON DELETE CASCADE,
  CONSTRAINT fk_notes_lesson FOREIGN KEY (lesson_id) REFERENCES lessons (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO permissions (slug, label) VALUES ('submissions.review', 'Corriger les travaux des apprenants');
INSERT IGNORE INTO role_permissions (role_id, permission_id)
  SELECT r.id, p.id FROM roles r JOIN permissions p ON p.slug = 'submissions.review' WHERE r.slug IN ('admin', 'instructor');
