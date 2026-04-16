-- ================================================
-- VALT Academy — Student Registration Database
-- Run this in cPanel > phpMyAdmin
-- ================================================

CREATE TABLE IF NOT EXISTS `valt_students` (
  `id`                      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `student_id`              VARCHAR(20)  NOT NULL UNIQUE,
  `first_name`              VARCHAR(100) NOT NULL,
  `last_name`               VARCHAR(100) NOT NULL,
  `date_of_birth`           DATE         NOT NULL,
  `gender`                  VARCHAR(30)  NOT NULL,
  `whatsapp_number`         VARCHAR(20)  NOT NULL,
  `other_number`            VARCHAR(20)  DEFAULT NULL,
  `parent_guardian_number`  VARCHAR(20)  NOT NULL,
  `parent_guardian_name`    VARCHAR(100) NOT NULL,
  `parent_guardian_email`   VARCHAR(255) NOT NULL,
  `email`                   VARCHAR(255) NOT NULL UNIQUE,
  `grade`                   TINYINT      NOT NULL,
  `province`                VARCHAR(100) NOT NULL,
  `city`                    VARCHAR(100) NOT NULL,
  `school_name`             VARCHAR(255) NOT NULL,
  `school_other`            VARCHAR(255) DEFAULT NULL,
  `programme_interest`      VARCHAR(500) DEFAULT NULL,
  `subjects_interest`       VARCHAR(255) DEFAULT NULL,
  `how_heard`               VARCHAR(100) DEFAULT NULL,
  `registered_at`           DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_grade`     (`grade`),
  INDEX `idx_province`  (`province`),
  INDEX `idx_registered`(`registered_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Run this if the table already exists and you need to add the new columns:
-- ALTER TABLE `valt_students`
--   ADD COLUMN `parent_guardian_name`  VARCHAR(100) NOT NULL DEFAULT '' AFTER `parent_guardian_number`,
--   ADD COLUMN `parent_guardian_email` VARCHAR(255) NOT NULL DEFAULT '' AFTER `parent_guardian_name`;
