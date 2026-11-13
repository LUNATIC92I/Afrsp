-- =====================================================================
--  AFRICA'SPY Intelligence Academy
--  Schéma relationnel MySQL 8 / MariaDB 10.4+
--  Encodage : utf8mb4, moteur : InnoDB
-- =====================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS invoices, invoice_sequences, rate_limits, lesson_notes, submissions, payments, contact_messages, settings, login_attempts, password_resets,
  audit_logs, media, announcement_reads, announcements, certificates,
  attempt_answers, exam_attempts, answers, questions, exams, lesson_progress,
  course_enrollments, lessons, chapters, courses, categories,
  role_permissions, permissions, users, roles;

SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------------------
--  Rôles & permissions (RBAC)
-- ---------------------------------------------------------------------
CREATE TABLE roles (
  id            TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
  slug          VARCHAR(40)  NOT NULL,
  name          VARCHAR(80)  NOT NULL,
  description   VARCHAR(255) NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_roles_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE permissions (
  id            SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  slug          VARCHAR(60)  NOT NULL,
  label         VARCHAR(120) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_permissions_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE role_permissions (
  role_id       TINYINT UNSIGNED  NOT NULL,
  permission_id SMALLINT UNSIGNED NOT NULL,
  PRIMARY KEY (role_id, permission_id),
  CONSTRAINT fk_rp_role FOREIGN KEY (role_id) REFERENCES roles (id) ON DELETE CASCADE,
  CONSTRAINT fk_rp_perm FOREIGN KEY (permission_id) REFERENCES permissions (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
--  Utilisateurs
-- ---------------------------------------------------------------------
CREATE TABLE users (
  id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  role_id         TINYINT UNSIGNED NOT NULL,
  first_name      VARCHAR(80)  NOT NULL,
  last_name       VARCHAR(80)  NOT NULL,
  email           VARCHAR(190) NOT NULL,
  password_hash   VARCHAR(255) NOT NULL,
  totp_secret     VARCHAR(255) NULL,
  totp_enabled    TINYINT(1) NOT NULL DEFAULT 0,
  totp_last_step  BIGINT UNSIGNED NULL,
  totp_recovery   TEXT NULL,
  country         VARCHAR(80)  NULL,
  organization    VARCHAR(150) NULL,
  bio             VARCHAR(600) NULL,
  status          ENUM('active','suspended') NOT NULL DEFAULT 'active',
  last_login_at   DATETIME NULL,
  created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_users_email (email),
  KEY idx_users_role (role_id),
  KEY idx_users_created (created_at),
  CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES roles (id) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE password_resets (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id       INT UNSIGNED NOT NULL,
  token_hash    CHAR(64) NOT NULL,
  expires_at    DATETIME NOT NULL,
  used_at       DATETIME NULL,
  created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_pr_token (token_hash),
  KEY idx_pr_user (user_id),
  CONSTRAINT fk_pr_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE login_attempts (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  email         VARCHAR(190) NOT NULL,
  ip_address    VARCHAR(45)  NOT NULL,
  success       TINYINT(1)   NOT NULL DEFAULT 0,
  attempted_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_la_email_time (email, attempted_at),
  KEY idx_la_ip_time (ip_address, attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
--  Médiathèque (images, PDF, vidéos)
-- ---------------------------------------------------------------------
CREATE TABLE media (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  kind          ENUM('image','document','video') NOT NULL,
  title         VARCHAR(190) NOT NULL,
  original_name VARCHAR(255) NOT NULL,
  stored_name   VARCHAR(120) NOT NULL,
  mime_type     VARCHAR(100) NOT NULL,
  size_bytes    INT UNSIGNED NOT NULL,
  attribution   VARCHAR(255) NULL,
  uploaded_by   INT UNSIGNED NULL,
  created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_media_stored (stored_name),
  KEY idx_media_kind (kind),
  CONSTRAINT fk_media_user FOREIGN KEY (uploaded_by) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
--  Catalogue pédagogique
-- ---------------------------------------------------------------------
CREATE TABLE categories (
  id            SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  code          VARCHAR(10)  NOT NULL,
  slug          VARCHAR(120) NOT NULL,
  name          VARCHAR(150) NOT NULL,
  description   TEXT NULL,
  accent        VARCHAR(7)   NOT NULL DEFAULT '#00C8FF',
  sort_order    SMALLINT NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  UNIQUE KEY uq_categories_slug (slug),
  UNIQUE KEY uq_categories_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE courses (
  id                INT UNSIGNED NOT NULL AUTO_INCREMENT,
  category_id       SMALLINT UNSIGNED NOT NULL,
  code              VARCHAR(20)  NOT NULL,
  slug              VARCHAR(160) NOT NULL,
  title             VARCHAR(190) NOT NULL,
  subtitle          VARCHAR(255) NULL,
  summary           TEXT NOT NULL,
  description       MEDIUMTEXT NULL,
  objectives        TEXT NULL,
  skills            TEXT NULL,
  prerequisites     TEXT NULL,
  level             ENUM('fondamental','intermediaire','avance') NOT NULL DEFAULT 'fondamental',
  duration_hours    SMALLINT UNSIGNED NOT NULL DEFAULT 10,
  price_xof         INT UNSIGNED NOT NULL DEFAULT 900000,
  promo_price_xof   INT UNSIGNED NULL DEFAULT 170000,
  cover_media_id    INT UNSIGNED NULL,
  cover_variant     TINYINT UNSIGNED NOT NULL DEFAULT 0,
  is_featured       TINYINT(1) NOT NULL DEFAULT 0,
  status            ENUM('draft','published') NOT NULL DEFAULT 'draft',
  sort_order        SMALLINT NOT NULL DEFAULT 0,
  created_by        INT UNSIGNED NULL,
  created_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_courses_slug (slug),
  UNIQUE KEY uq_courses_code (code),
  KEY idx_courses_cat (category_id),
  KEY idx_courses_status (status, sort_order),
  FULLTEXT KEY ft_courses (title, summary),
  CONSTRAINT fk_courses_cat   FOREIGN KEY (category_id)    REFERENCES categories (id) ON UPDATE CASCADE,
  CONSTRAINT fk_courses_cover FOREIGN KEY (cover_media_id) REFERENCES media (id) ON DELETE SET NULL,
  CONSTRAINT fk_courses_user  FOREIGN KEY (created_by)     REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE chapters (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  course_id     INT UNSIGNED NOT NULL,
  title         VARCHAR(190) NOT NULL,
  summary       TEXT NULL,
  sort_order    SMALLINT NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  KEY idx_chapters_course (course_id, sort_order),
  CONSTRAINT fk_chapters_course FOREIGN KEY (course_id) REFERENCES courses (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE lessons (
  id                INT UNSIGNED NOT NULL AUTO_INCREMENT,
  chapter_id        INT UNSIGNED NOT NULL,
  title             VARCHAR(190) NOT NULL,
  type              ENUM('text','video','document','exercise','case_study') NOT NULL DEFAULT 'text',
  content           MEDIUMTEXT NULL,
  video_url         VARCHAR(255) NULL,
  media_id          INT UNSIGNED NULL,
  duration_minutes  SMALLINT UNSIGNED NOT NULL DEFAULT 15,
  is_downloadable   TINYINT(1) NOT NULL DEFAULT 1,
  sort_order        SMALLINT NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  KEY idx_lessons_chapter (chapter_id, sort_order),
  CONSTRAINT fk_lessons_chapter FOREIGN KEY (chapter_id) REFERENCES chapters (id) ON DELETE CASCADE,
  CONSTRAINT fk_lessons_media   FOREIGN KEY (media_id)   REFERENCES media (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
--  Inscriptions & progression
-- ---------------------------------------------------------------------
CREATE TABLE course_enrollments (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id       INT UNSIGNED NOT NULL,
  course_id     INT UNSIGNED NOT NULL,
  status        ENUM('active','completed') NOT NULL DEFAULT 'active',
  progress      TINYINT UNSIGNED NOT NULL DEFAULT 0,
  enrolled_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  last_activity DATETIME NULL,
  completed_at  DATETIME NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_enroll (user_id, course_id),
  KEY idx_enroll_course (course_id),
  KEY idx_enroll_date (enrolled_at),
  CONSTRAINT fk_enroll_user   FOREIGN KEY (user_id)   REFERENCES users (id)   ON DELETE CASCADE,
  CONSTRAINT fk_enroll_course FOREIGN KEY (course_id) REFERENCES courses (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE lesson_progress (
  user_id       INT UNSIGNED NOT NULL,
  lesson_id     INT UNSIGNED NOT NULL,
  completed_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (user_id, lesson_id),
  KEY idx_lp_lesson (lesson_id),
  CONSTRAINT fk_lp_user   FOREIGN KEY (user_id)   REFERENCES users (id)   ON DELETE CASCADE,
  CONSTRAINT fk_lp_lesson FOREIGN KEY (lesson_id) REFERENCES lessons (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
--  Examens & banque de questions QCM
--  Une banque par formation ; chaque question peut être rattachée à un
--  chapitre (évaluation formative) : l'examen final tire au sort dans
--  toute la banque.
-- ---------------------------------------------------------------------
CREATE TABLE exams (
  id                INT UNSIGNED NOT NULL AUTO_INCREMENT,
  course_id         INT UNSIGNED NOT NULL,
  title             VARCHAR(190) NOT NULL,
  instructions      TEXT NULL,
  pass_mark         TINYINT UNSIGNED NOT NULL DEFAULT 70,
  max_attempts      TINYINT UNSIGNED NOT NULL DEFAULT 3,
  question_count    TINYINT UNSIGNED NOT NULL DEFAULT 10,
  time_limit_min    SMALLINT UNSIGNED NOT NULL DEFAULT 30,
  require_completion TINYINT(1) NOT NULL DEFAULT 1,
  is_active         TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (id),
  UNIQUE KEY uq_exams_course (course_id),
  CONSTRAINT fk_exams_course FOREIGN KEY (course_id) REFERENCES courses (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE questions (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  exam_id       INT UNSIGNED NOT NULL,
  chapter_id    INT UNSIGNED NULL,
  prompt        TEXT NOT NULL,
  explanation   TEXT NULL,
  is_active     TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (id),
  KEY idx_q_exam (exam_id),
  KEY idx_q_chapter (chapter_id),
  CONSTRAINT fk_q_exam    FOREIGN KEY (exam_id)    REFERENCES exams (id)    ON DELETE CASCADE,
  CONSTRAINT fk_q_chapter FOREIGN KEY (chapter_id) REFERENCES chapters (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE answers (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  question_id   INT UNSIGNED NOT NULL,
  label         VARCHAR(500) NOT NULL,
  is_correct    TINYINT(1) NOT NULL DEFAULT 0,
  sort_order    TINYINT NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  KEY idx_a_question (question_id),
  CONSTRAINT fk_a_question FOREIGN KEY (question_id) REFERENCES questions (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE exam_attempts (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  exam_id       INT UNSIGNED NOT NULL,
  user_id       INT UNSIGNED NOT NULL,
  question_ids  TEXT NOT NULL,
  started_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  expires_at    DATETIME NOT NULL,
  submitted_at  DATETIME NULL,
  score         DECIMAL(5,2) NULL,
  correct_count TINYINT UNSIGNED NULL,
  total_count   TINYINT UNSIGNED NOT NULL,
  passed        TINYINT(1) NULL,
  PRIMARY KEY (id),
  KEY idx_att_user_exam (user_id, exam_id),
  KEY idx_att_submitted (submitted_at),
  CONSTRAINT fk_att_exam FOREIGN KEY (exam_id) REFERENCES exams (id) ON DELETE CASCADE,
  CONSTRAINT fk_att_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE attempt_answers (
  attempt_id    INT UNSIGNED NOT NULL,
  question_id   INT UNSIGNED NOT NULL,
  answer_id     INT UNSIGNED NULL,
  is_correct    TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (attempt_id, question_id),
  KEY idx_aa_question (question_id),
  CONSTRAINT fk_aa_attempt  FOREIGN KEY (attempt_id)  REFERENCES exam_attempts (id) ON DELETE CASCADE,
  CONSTRAINT fk_aa_question FOREIGN KEY (question_id) REFERENCES questions (id)     ON DELETE CASCADE,
  CONSTRAINT fk_aa_answer   FOREIGN KEY (answer_id)   REFERENCES answers (id)       ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
--  Certificats
-- ---------------------------------------------------------------------
CREATE TABLE certificates (
  id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  certificate_no  VARCHAR(32)  NOT NULL,
  verify_hash     CHAR(64)     NOT NULL,
  user_id         INT UNSIGNED NOT NULL,
  course_id       INT UNSIGNED NOT NULL,
  attempt_id      INT UNSIGNED NULL,
  holder_name     VARCHAR(170) NOT NULL,
  course_title    VARCHAR(190) NOT NULL,
  score           DECIMAL(5,2) NOT NULL,
  issued_at       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  status          ENUM('valid','revoked') NOT NULL DEFAULT 'valid',
  revoked_reason  VARCHAR(255) NULL,
  file_name       VARCHAR(120) NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_cert_no (certificate_no),
  UNIQUE KEY uq_cert_user_course (user_id, course_id),
  KEY idx_cert_course (course_id),
  CONSTRAINT fk_cert_user    FOREIGN KEY (user_id)    REFERENCES users (id)         ON DELETE CASCADE,
  CONSTRAINT fk_cert_course  FOREIGN KEY (course_id)  REFERENCES courses (id)       ON DELETE CASCADE,
  CONSTRAINT fk_cert_attempt FOREIGN KEY (attempt_id) REFERENCES exam_attempts (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
--  Annonces
-- ---------------------------------------------------------------------
CREATE TABLE announcements (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  title         VARCHAR(190) NOT NULL,
  body          TEXT NOT NULL,
  level         ENUM('info','important','alert') NOT NULL DEFAULT 'info',
  audience      ENUM('all','learners','public') NOT NULL DEFAULT 'learners',
  is_published  TINYINT(1) NOT NULL DEFAULT 1,
  published_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  created_by    INT UNSIGNED NULL,
  PRIMARY KEY (id),
  KEY idx_ann_pub (is_published, published_at),
  CONSTRAINT fk_ann_user FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE announcement_reads (
  announcement_id INT UNSIGNED NOT NULL,
  user_id         INT UNSIGNED NOT NULL,
  read_at         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (announcement_id, user_id),
  CONSTRAINT fk_ar_ann  FOREIGN KEY (announcement_id) REFERENCES announcements (id) ON DELETE CASCADE,
  CONSTRAINT fk_ar_user FOREIGN KEY (user_id)         REFERENCES users (id)         ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
--  Journal d'audit, paramètres, messages de contact
-- ---------------------------------------------------------------------
CREATE TABLE audit_logs (
  id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id       INT UNSIGNED NULL,
  action        VARCHAR(80)  NOT NULL,
  entity_type   VARCHAR(40)  NULL,
  entity_id     INT UNSIGNED NULL,
  details       VARCHAR(500) NULL,
  ip_address    VARCHAR(45)  NULL,
  created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_audit_user (user_id),
  KEY idx_audit_time (created_at),
  KEY idx_audit_action (action),
  CONSTRAINT fk_audit_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE settings (
  setting_key   VARCHAR(60)  NOT NULL,
  setting_value TEXT NULL,
  PRIMARY KEY (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE contact_messages (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  full_name     VARCHAR(160) NOT NULL,
  email         VARCHAR(190) NOT NULL,
  subject       VARCHAR(190) NOT NULL,
  message       TEXT NOT NULL,
  ip_address    VARCHAR(45) NULL,
  is_read       TINYINT(1) NOT NULL DEFAULT 0,
  created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_contact_time (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
--  Paiements (accès payant aux formations, montants en FCFA / XOF)
-- ---------------------------------------------------------------------
CREATE TABLE payments (
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


-- ---------------------------------------------------------------------
--  Travaux rendus (exercices, études de cas) et notes personnelles
-- ---------------------------------------------------------------------
CREATE TABLE submissions (
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

CREATE TABLE lesson_notes (
  user_id     INT UNSIGNED NOT NULL,
  lesson_id   INT UNSIGNED NOT NULL,
  content     TEXT NOT NULL,
  updated_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (user_id, lesson_id),
  KEY idx_notes_lesson (lesson_id),
  CONSTRAINT fk_notes_user   FOREIGN KEY (user_id)   REFERENCES users (id)   ON DELETE CASCADE,
  CONSTRAINT fk_notes_lesson FOREIGN KEY (lesson_id) REFERENCES lessons (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ---------------------------------------------------------------------
--  Limitation de débit (anti-abus)
-- ---------------------------------------------------------------------
CREATE TABLE rate_limits (
  bucket        VARCHAR(160) NOT NULL,
  hits          INT UNSIGNED NOT NULL DEFAULT 0,
  window_start  DATETIME NOT NULL,
  PRIMARY KEY (bucket),
  KEY idx_rl_window (window_start)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ---------------------------------------------------------------------
--  Facturation : factures et avoirs (numérotation continue par année)
-- ---------------------------------------------------------------------
CREATE TABLE invoice_sequences (
  doc_type  ENUM('invoice','credit_note') NOT NULL,
  year      SMALLINT UNSIGNED NOT NULL,
  last_no   INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (doc_type, year)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE invoices (
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

